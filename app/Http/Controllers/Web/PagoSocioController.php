<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Venta;
use App\Models\Inscripcion;
use App\Models\PagoSocio;
use App\Models\Parametro;
use App\Support\InsensitiveSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoSocioController extends Controller
{
    // 1. Lista de socios con su saldo pendiente e historial de pagos
    public function index(Request $request) {
        /** @var \App\Models\Usuario $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        // Si es Socio, solo ve su historial de pagos recibidos
        if ($user->hasRole('Socio/Instructor')) {
            $pagos = PagoSocio::where('id_usuario_socio', $user->id_usuario)
                ->orderBy('created_at', 'desc')
                ->get();
            return view('admin.pagos_socios.socio_index', compact('pagos'));
        }

        // ==== VISTA PARA LA DIRECTIVA ====
        
        // Capturamos los filtros
        $buscar = $request->input('buscar');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        // 1. Calculamos los saldos pendientes de cada socio (Deuda global, no se filtra por fecha)
        $socios = Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'Socio/Instructor'))
            ->when($buscar, function($q) use ($buscar) {
                InsensitiveSearch::whereContains($q, 'nombre', $buscar);
            })->get();

        $saldos = [];
        foreach ($socios as $socio) {
            $ingresos = $this->calcularIngresosSocio($socio->id_usuario);
            $saldo = $ingresos['saldo_pendiente'];

            // Solo mostramos al socio si se le debe dinero (saldo > 0.01)
            if ($saldo > 0) {
                $saldos[] = [
                    'socio' => $socio,
                    'saldo' => $saldo,
                    'ventas_saldo' => $ingresos['ventas_saldo'],
                    'cursos_saldo' => $ingresos['cursos_saldo'],
                ];
            }
        }

        // 2. Historial de pagos realizados (Filtrable por socio y por fechas)
        $historial = PagoSocio::with('socio')
            ->whereIn('estado', ['PAGADA', 'ANULADA'])
            ->when($buscar, function($q) use ($buscar) {
                $q->whereHas('socio', function($q2) use ($buscar) {
                    InsensitiveSearch::whereContains($q2, 'nombre', $buscar);
                });
            })
            ->when($fechaInicio, function($q) use ($fechaInicio) {
                $q->whereDate('created_at', '>=', $fechaInicio);
            })
            ->when($fechaFin, function($q) use ($fechaFin) {
                $q->whereDate('created_at', '<=', $fechaFin);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pagos_socios.index', compact('saldos', 'historial', 'buscar', 'fechaInicio', 'fechaFin'));
    }

    // 2. Formulario para registrar un pago (total o parcial)
    public function create($id_usuario_socio) {
        $socio = Usuario::findOrFail($id_usuario_socio);
        $ingresos = $this->calcularIngresosSocio($id_usuario_socio);
        $saldoPendiente = $ingresos['saldo_pendiente'];
        $ventas = $ingresos['ventas'];
        $inscripciones = $ingresos['inscripciones'];
        $bruto = $ingresos['ventas_bruto'];
        $brutoCursos = $ingresos['cursos_bruto'];
        $comisionVentas = $ingresos['ventas_comision'];
        $comisionCursos = $ingresos['cursos_comision'];
        $brutoTotal = $ingresos['bruto_total'];
        $comisionTotal = $ingresos['comision_total'];
        $netoGenerado = $ingresos['neto_generado'];
        $comisionPorc = $ingresos['comision_venta'];
        $comisionCursoPorc = $ingresos['comision_curso'];
        $montoPagado = $ingresos['neto_pagado'];

        return view('admin.pagos_socios.create', compact(
            'socio',
            'saldoPendiente',
            'ventas',
            'inscripciones',
            'bruto',
            'brutoCursos',
            'comisionVentas',
            'comisionCursos',
            'brutoTotal',
            'comisionTotal',
            'comisionPorc',
            'comisionCursoPorc',
            'netoGenerado',
            'montoPagado'
        ));
    }

    // 3. Guardar el pago (parcial o total)
    public function store(Request $request, $id_usuario_socio) {
        $request->validate([
            'monto_pagar' => 'required|numeric|decimal:0,2|min:0.01',
            'metodo' => 'required|in:EFECTIVO,QR',
        ]);

        $resultado = DB::transaction(function () use ($request, $id_usuario_socio) {
            $socio = Usuario::whereKey($id_usuario_socio)->lockForUpdate()->firstOrFail();
            $ingresos = $this->calcularIngresosSocio($socio->id_usuario, true);
            $saldoPendiente = $ingresos['saldo_pendiente'];
            $montoNetoAbono = round((float) $request->monto_pagar, 2);

            if ($montoNetoAbono > $saldoPendiente) {
                return ['saldo' => $saldoPendiente];
            }

            if ($saldoPendiente <= 0) {
                return ['saldo' => $saldoPendiente];
            }

            if ($saldoPendiente <= 0) {
                return ['saldo' => $saldoPendiente];
            }

            $netoVentasAbonado = $ingresos['ventas_saldo'] > 0
                ? min(
                    $ingresos['ventas_saldo'],
                    round($montoNetoAbono * $ingresos['ventas_saldo'] / $saldoPendiente, 2)
                )
                : 0;
            $netoCursosAbonado = round($montoNetoAbono - $netoVentasAbonado, 2);
            if ($netoCursosAbonado > $ingresos['cursos_saldo']) {
                $netoCursosAbonado = $ingresos['cursos_saldo'];
                $netoVentasAbonado = round($montoNetoAbono - $netoCursosAbonado, 2);
            }

            $montoBrutoVentas = $netoVentasAbonado > 0 && $ingresos['ventas_saldo'] > 0
                ? round($netoVentasAbonado * $ingresos['ventas_bruto_saldo'] / $ingresos['ventas_saldo'], 2)
                : 0;
            $comisionVentasAbono = round($montoBrutoVentas - $netoVentasAbonado, 2);
            $montoBrutoCursos = $netoCursosAbonado > 0 && $ingresos['cursos_saldo'] > 0
                ? round($netoCursosAbonado * $ingresos['cursos_bruto_saldo'] / $ingresos['cursos_saldo'], 2)
                : 0;
            $comisionCursosAbono = round($montoBrutoCursos - $netoCursosAbonado, 2);
            $montoBrutoAbono = round($montoBrutoVentas + $montoBrutoCursos, 2);
            $comisionAbono = round($montoBrutoAbono - $montoNetoAbono, 2);
            $origen = $netoVentasAbonado > 0 && $netoCursosAbonado > 0
                ? 'MIXTO'
                : ($netoVentasAbonado > 0 ? 'VENTAS' : 'CURSOS');

            PagoSocio::create([
                'id_usuario_socio' => $socio->id_usuario,
                'periodo_inicio' => now()->startOfMonth(),
                'periodo_fin' => now(),
                'monto_bruto' => $montoBrutoAbono,
                'comision' => $comisionAbono,
                'monto_neto' => $montoNetoAbono,
                'origen' => $origen,
                'monto_bruto_ventas' => $montoBrutoVentas,
                'comision_ventas' => $comisionVentasAbono,
                'monto_bruto_cursos' => $montoBrutoCursos,
                'comision_cursos' => $comisionCursosAbono,
                'metodo' => $request->metodo,
                'estado' => 'PAGADA',
            ]);

            return ['saldo' => null];
        });

        if ($resultado['saldo'] !== null) {
            return back()->withErrors([
                'monto_pagar' => 'El monto no puede ser mayor al saldo pendiente. Saldo actual: Bs. '
                    . number_format(max(0, $resultado['saldo']), 2),
            ])->withInput();
        }

        return redirect('/admin/pagos-socios')->with('success', 'Pago registrado correctamente.');
    }

    private function calcularIngresosSocio(string $idUsuarioSocio, bool $bloquearFilas = false): array
    {
        $comisionVenta = (float) (Parametro::where('clave', 'comision_venta')->value('valor') ?? 5);
        $comisionCurso = (float) (Parametro::where('clave', 'comision_curso')->value('valor') ?? 20);

        $ventasQuery = Venta::where('estado', 'PAGADA')
            ->whereHas('detalles.producto', fn($query) => $query->where('id_usuario_socio', $idUsuarioSocio))
            ->with('detalles.producto');
        if ($bloquearFilas) {
            $ventasQuery->lockForUpdate();
        }
        $ventas = $ventasQuery->get();

        $ventasBruto = 0;
        foreach ($ventas as $venta) {
            foreach ($venta->detalles as $detalle) {
                if ((string) $detalle->producto->id_usuario_socio === (string) $idUsuarioSocio) {
                    $ventasBruto += (float) $detalle->subtotal;
                }
            }
        }
        $ventasBruto = round($ventasBruto, 2);
        $ventasComision = round($ventasBruto * $comisionVenta / 100, 2);
        $ventasNetas = max(0, round($ventasBruto - $ventasComision, 2));

        $inscripcionesQuery = Inscripcion::where('estado', 'CONFIRMADA')
            ->whereHas('horarioCurso.curso', fn($query) => $query->where('id_usuario_instructor', $idUsuarioSocio))
            ->with([
                'alumno',
                'horarioCurso.curso',
                'pagos' => fn($query) => $query->where('estado', 'CONFIRMADO'),
            ]);
        if ($bloquearFilas) {
            $inscripcionesQuery->lockForUpdate();
        }
        $inscripciones = $inscripcionesQuery->get();

        $cursosBruto = 0;
        $cursosComision = 0;
        foreach ($inscripciones as $inscripcion) {
            $cursosBruto += (float) $inscripcion->pagos->sum('monto');
            $comisionInscripcion = $inscripcion->comision_calculada
                ? (float) $inscripcion->comision_cappias
                : round((float) $inscripcion->horarioCurso->curso->costo * $comisionCurso / 100, 2);
            $cursosComision += $comisionInscripcion;
        }
        $cursosBruto = round($cursosBruto, 2);
        $cursosComision = round($cursosComision, 2);
        $cursosNetos = max(0, round($cursosBruto - $cursosComision, 2));

        $brutoTotal = round($ventasBruto + $cursosBruto, 2);
        $comisionTotal = round($ventasComision + $cursosComision, 2);
        $netoGenerado = max(0, round($ventasNetas + $cursosNetos, 2));
        $pagosQuery = PagoSocio::where('id_usuario_socio', $idUsuarioSocio)
            ->where('estado', 'PAGADA');
        if ($bloquearFilas) {
            $pagosQuery->lockForUpdate();
        }
        $pagos = $pagosQuery->get([
            'monto_bruto',
            'comision',
            'monto_neto',
            'monto_bruto_ventas',
            'comision_ventas',
            'monto_bruto_cursos',
            'comision_cursos',
        ]);

        $ventasBrutoPagado = 0;
        $ventasComisionPagada = 0;
        $cursosBrutoPagado = 0;
        $cursosComisionPagada = 0;
        $netoPagado = 0;
        foreach ($pagos as $pago) {
            $ventasBrutoPagado += (float) ($pago->monto_bruto_ventas ?? $pago->monto_bruto);
            $ventasComisionPagada += (float) ($pago->comision_ventas ?? $pago->comision);
            $cursosBrutoPagado += (float) ($pago->monto_bruto_cursos ?? 0);
            $cursosComisionPagada += (float) ($pago->comision_cursos ?? 0);
            $netoPagado += (float) $pago->monto_neto;
        }

        $ventasNetasPagadas = round($ventasBrutoPagado - $ventasComisionPagada, 2);
        $cursosNetosPagados = round($cursosBrutoPagado - $cursosComisionPagada, 2);
        $ventasSaldo = max(0, round($ventasNetas - $ventasNetasPagadas, 2));
        $cursosSaldo = max(0, round($cursosNetos - $cursosNetosPagados, 2));
        $ventasBrutoSaldo = max(0, round($ventasBruto - $ventasBrutoPagado, 2));
        $cursosBrutoSaldo = max(0, round($cursosBruto - $cursosBrutoPagado, 2));
        $saldoPendiente = round($ventasSaldo + $cursosSaldo, 2);

        return [
            'ventas' => $ventas,
            'inscripciones' => $inscripciones,
            'ventas_bruto' => $ventasBruto,
            'ventas_comision' => $ventasComision,
            'ventas_netas' => $ventasNetas,
            'cursos_bruto' => $cursosBruto,
            'cursos_comision' => $cursosComision,
            'cursos_netos' => $cursosNetos,
            'bruto_total' => $brutoTotal,
            'comision_total' => $comisionTotal,
            'neto_generado' => $netoGenerado,
            'neto_pagado' => round($netoPagado, 2),
            'ventas_saldo' => $ventasSaldo,
            'cursos_saldo' => $cursosSaldo,
            'ventas_bruto_saldo' => $ventasBrutoSaldo,
            'cursos_bruto_saldo' => $cursosBrutoSaldo,
            'saldo_pendiente' => $saldoPendiente,
            'comision_venta' => $comisionVenta,
            'comision_curso' => $comisionCurso,
        ];
    }

    public function anular($id) {
        $anulada = DB::transaction(function () use ($id) {
            $pago = PagoSocio::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($pago->estado !== 'PAGADA') {
                return false;
            }

            $pago->update(['estado' => 'ANULADA']);
            return true;
        });

        if (!$anulada) {
            return back()->with('error', 'Solo se pueden anular pagos a socios pagados.');
        }

        return back()->with('success', 'Pago a socio anulado correctamente.');
    }
}