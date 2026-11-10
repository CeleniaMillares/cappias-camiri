<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Socio;
use App\Models\Rol;
use App\Models\CuotaSocio;
use App\Models\Parametro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SocioController extends Controller
{
    public function index(Request $request) {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();
        if ($user->hasRole('Socio/Instructor')) {
            return redirect('/admin/mi-perfil-socio');
        }

        $buscar = $request->input('buscar');
        $socios = Socio::with(['usuario'])
            ->when($buscar, function($q) use ($buscar) {
                $q->whereHas('usuario', fn($q2) => $q2->where('nombre', 'LIKE', '%' . $buscar . '%'))
                  ->orWhere('ci', 'LIKE', '%' . $buscar . '%');
            })->get();

        $mesActual = now()->format('Y-m');
        
        foreach ($socios as $socio) {
            $socio->pago_mes_actual = $socio->cuotas()
                ->where('tipo', 'MENSUALIDAD')
                ->where('periodo', $mesActual)
                ->where('estado', 'CONFIRMADO')
                ->exists();

            $socio->cuotasPendientes = $socio->cuotas()
                ->where('estado', 'PENDIENTE')
                ->get();
            $socio->totalPendiente = $socio->cuotasPendientes
                ->sum(fn ($cuota) => $cuota->monto - $cuota->monto_efectivo);
            $socio->totalAbonado = $socio->cuotasPendientes->sum('monto_efectivo');
            $socio->conceptosPendientes = [];

            if ($socio->cuotasPendientes->contains('tipo', 'MATRICULA')) {
                $socio->conceptosPendientes[] = 'Matrícula';
            }

            $periodosPendientes = $socio->cuotasPendientes
                ->where('tipo', 'MENSUALIDAD')
                ->pluck('periodo')
                ->filter()
                ->unique()
                ->sort()
                ->values();

            foreach ($periodosPendientes as $periodo) {
                $socio->conceptosPendientes[] = ucfirst(
                    Carbon::createFromFormat('Y-m', $periodo)->translatedFormat('F Y')
                );
            }
        }

        return view('admin.socios.index', compact('socios', 'buscar'));
    }

    public function create() {
        $montoInscripcion = Parametro::where('clave', 'monto_inscripcion')->value('valor') ?? 100;
        $qrImage = Parametro::where('clave', 'qr_image_url')->value('valor');
        return view('admin.socios.create', compact('montoInscripcion', 'qrImage'));
    }

    public function store(Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'correo' => 'required|email|unique:usuarios,correo',
            'contrasena' => 'required|min:6',
            'ci' => 'required|string|max:20|unique:socios,ci',
            'telefono' => 'required|string|max:20',
            'metodo_pago' => 'required|in:EFECTIVO,QR,MIXTO',
            'monto_efectivo' => 'required_if:metodo_pago,MIXTO|nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $rolSocio = Rol::where('nombre', 'Socio/Instructor')->first();

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'contrasena_hash' => Hash::make($request->contrasena),
                'activo' => true,
            ]);
            $usuario->roles()->attach($rolSocio->id_rol);

            $socio = Socio::create([
                'id_usuario' => $usuario->id_usuario,
                'ci' => $request->ci,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'fecha_inscripcion' => now()->toDateString(),
                'estado' => 'ACTIVO'
            ]);

            $estado = 'PENDIENTE';
            if ($request->metodo_pago === 'EFECTIVO' || $request->has('confirmar_qr')) {
                $estado = 'CONFIRMADO';
            }

            $montoInscripcion = Parametro::where('clave', 'monto_inscripcion')->value('valor') ?? 100;
            
            CuotaSocio::create([
                'id_socio' => $socio->id_socio,
                'tipo' => 'MATRICULA', // 🔥 Cambiado
                'metodo' => $request->metodo_pago,
                'estado' => $estado,
                'monto' => $montoInscripcion,
                'fecha_pago' => now()->toDateString(),
            ]);
        });

        return redirect('/admin/socios')->with('success', 'Socio inscrito correctamente.');
    }
    public function createPagoMensual($id_socio) {
        $socio = Socio::with('usuario')->findOrFail($id_socio);
        $montoMensualidad = Parametro::where('clave', 'monto_mensualidad')->value('valor') ?? 50;
        $qrImage = Parametro::where('clave', 'qr_image_url')->value('valor');
        
        // Meses ya confirmados
        $mesesPagados = CuotaSocio::where('id_socio', $id_socio)
            ->where('tipo', 'MENSUALIDAD')
            ->where('estado', 'CONFIRMADO')
            ->pluck('periodo')
            ->toArray();
        
        // Saldo pendiente total (monto - monto_efectivo de cada cuota pendiente)
        $saldoPendienteTotal = CuotaSocio::where('id_socio', $id_socio)
            ->where('estado', 'PENDIENTE')
            ->get()
            ->sum(function($c) { return $c->monto - $c->monto_efectivo; });
        
        // Primer mes pendiente
        $fechaInscripcion = Carbon::parse($socio->fecha_inscripcion)->startOfMonth();
        $hoy = now()->startOfMonth();
        $primerMesPendiente = null;
        $cursor = $fechaInscripcion->copy();
        
        while ($cursor->lte($hoy)) {
            if (!in_array($cursor->format('Y-m'), $mesesPagados)) {
                $primerMesPendiente = $cursor->format('Y-m');
                break;
            }
            $cursor->addMonth();
        }
        
        if (!$primerMesPendiente) {
            $primerMesPendiente = now()->addMonth()->format('Y-m');
        }
        
        // Contar meses adeudados
        $mesesAdeudados = 0;
        $cursor = $fechaInscripcion->copy();
        while ($cursor->lte($hoy)) {
            if (!in_array($cursor->format('Y-m'), $mesesPagados)) {
                $mesesAdeudados++;
            }
            $cursor->addMonth();
        }
        
        // Timeline (desde inscripción hasta 12 meses futuro)
        $timeline = [];
        $cursor = $fechaInscripcion->copy();
        $fin = now()->copy()->addMonths(12);
        
        while ($cursor->lte($fin)) {
            $periodo = $cursor->format('Y-m');
            $timeline[] = [
                'periodo' => $periodo,
                'label' => ucfirst($cursor->translatedFormat('M Y')),
                'pagado' => in_array($periodo, $mesesPagados),
                'es_actual' => $cursor->format('Y-m') === now()->format('Y-m'),
                'es_futuro' => $cursor->gt($hoy),
            ];
            $cursor->addMonth();
        }
        
        return view('admin.socios.pago_mensual', compact(
            'socio', 'montoMensualidad', 'qrImage', 
            'mesesPagados', 'primerMesPendiente', 'timeline', 
            'mesesAdeudados', 'saldoPendienteTotal'
        ));
    }

    public function storePagoMensual(Request $request, $id_socio) {
        $request->validate([
            'periodo_inicio' => 'required|date_format:Y-m',
            'meses' => 'required|integer|min:1', // 🔥 SIN límite máximo
            'metodo_pago' => 'required|in:EFECTIVO,QR,MIXTO',
            'monto_efectivo' => 'required_if:metodo_pago,MIXTO|nullable|numeric|min:0',
        ]);

        $socio = Socio::findOrFail($id_socio);
        $montoMensualidad = Parametro::where('clave', 'monto_mensualidad')->value('valor') ?? 50;
        $meses = $request->meses;
        
        $esPagoConfirmado = ($request->metodo_pago === 'EFECTIVO' || $request->has('confirmar_qr'));
        $estado = $esPagoConfirmado ? 'CONFIRMADO' : 'PENDIENTE';
        $fechaInicio = Carbon::createFromFormat('Y-m', $request->periodo_inicio);
        
        // 🔥 Calcular cuánto es efectivo y cuánto QR por cada mes
        $efectivoTotal = 0;
        if ($request->metodo_pago === 'MIXTO') {
            $efectivoTotal = (float) $request->monto_efectivo;
        } elseif ($request->metodo_pago === 'EFECTIVO') {
            $efectivoTotal = $montoMensualidad * $meses;
        }
        // El efectivo se distribuye equitativamente entre los meses que se van a crear
        
        $creados = [];
        $saltados = [];

        DB::transaction(function () use ($socio, $fechaInicio, $meses, $montoMensualidad, $request, $estado, $efectivoTotal, $esPagoConfirmado, &$creados, &$saltados) {
            $cursor = $fechaInicio->copy();
            $contador = 0;
            $safety = 0;
            
            while ($contador < $meses && $safety < 60) {
                $periodoActual = $cursor->format('Y-m');
                
                $existe = CuotaSocio::where('id_socio', $socio->id_socio)
                    ->where('tipo', 'MENSUALIDAD')
                    ->where('periodo', $periodoActual)
                    ->where('estado', 'CONFIRMADO')
                    ->exists();
                
                if ($existe) {
                    $saltados[] = $periodoActual;
                } else {
                    // Distribuir el efectivo entre los meses restantes
                    $efectivoPorMes = 0;
                    if ($efectivoTotal > 0) {
                        $efectivoPorMes = min($efectivoTotal / $meses, $montoMensualidad);
                        $efectivoTotal -= $efectivoPorMes;
                    }
                    
                    CuotaSocio::create([
                        'id_socio' => $socio->id_socio,
                        'tipo' => 'MENSUALIDAD',
                        'metodo' => $request->metodo_pago,
                        'estado' => $estado,
                        'periodo' => $periodoActual,
                        'monto' => $montoMensualidad,
                        'monto_efectivo' => $efectivoPorMes, // 🔥 Guardamos el efectivo
                        'fecha_pago' => now()->toDateString(),
                    ]);
                    $creados[] = $periodoActual;
                    $contador++;
                }
                
                $cursor->addMonth();
                $safety++;
            }
        });

        $mensaje = count($creados) . ' mes(es) registrado(s).';
        if (count($saltados) > 0) {
            $mensaje .= ' Se omitieron ' . count($saltados) . ' ya pagados.';
        }
        if ($estado === 'PENDIENTE') {
            $mensaje .= ' Quedan pendientes de verificación QR.';
        }

        return redirect('/admin/socios')->with('success', $mensaje);
    }

    

    public function confirmarPago($id_cuota) {
        $cuota = CuotaSocio::findOrFail($id_cuota);
        
        if ($cuota->estado === 'CONFIRMADO') {
            return back()->with('error', 'Este pago ya estaba confirmado.');
        }

        $cuota->update(['estado' => 'CONFIRMADO']);

        return back()->with('success', 'Pago QR confirmado correctamente.');
    }

    public function confirmarPendientes($id_socio) {
        $socio = Socio::findOrFail($id_socio);

        $cantidadConfirmada = DB::transaction(function () use ($socio) {
            $cuotasPendientes = $socio->cuotas()
                ->where('estado', 'PENDIENTE')
                ->lockForUpdate()
                ->get();

            if ($cuotasPendientes->isEmpty()) {
                return 0;
            }

            foreach ($cuotasPendientes as $cuota) {
                $cuota->update(['estado' => 'CONFIRMADO']);
            }

            return $cuotasPendientes->count();
        });

        if ($cantidadConfirmada === 0) {
            return back()->with('error', 'El socio no tiene pagos pendientes por confirmar.');
        }

        return redirect('/admin/socios')->with(
            'success',
            $cantidadConfirmada . ' pago(s) pendiente(s) confirmado(s) correctamente.'
        );
    }

    public function historial($id_socio) {
        $socio = Socio::with(['usuario'])->findOrFail($id_socio);
        $cuotas = $socio->cuotas()->orderBy('fecha_pago', 'desc')->get();
        return view('admin.socios.historial', compact('socio', 'cuotas'));
    }
}