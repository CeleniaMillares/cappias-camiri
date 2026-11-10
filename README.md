# CAPPIAS Camiri - Sistema de Gestión

<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Logo">
</p>

## 📖 Acerca del Proyecto

**CAPPIAS Camiri** es un sistema de gestión integral desarrollado para la asociación de artesanos. Su objetivo es digitalizar y optimizar la administración de socios, ventas de productos, inscripciones a cursos, gestión de ferias y control financiero.

Este proyecto fue desarrollado como parte del Taller de Grado, aplicando **arquitectura limpia**, **principios SOLID** y el patrón **MVC** proporcionado por Laravel.

## ✨ Características Principales

*   **Gestión de Usuarios y Roles:** Control de acceso granular (Administrador, Directiva, Socio/Instructor, Alumno, Cliente).
*   **Gestión de Socios y Membresías:** Inscripción con pago de matrícula, control de mensualidades (con opción a pagos adelantados) y generación de cuotas.
*   **Punto de Venta (POS):** Registro de ventas en sede o ferias, soportando pagos en Efectivo, QR o Mixto (con cálculo automático de comisiones).
*   **Gestión de Cursos:** Programación de horarios, asignación de instructores, control de cupos e inscripciones públicas.
*   **Vitrina Pública:** Catálogo web visible para clientes y alumnos sin necesidad de iniciar sesión, con sistema de *Checkout* (registro automático).
*   **Liquidación a Socios:** Cálculo automático de ganancias netas (ventas menos comisión) con soporte para pagos parciales.
*   **Reportes y Estadísticas:** Generación de reportes financieros en PDF y Excel, con gráficos dinámicos de ingresos.
*   **Auditoría Automatizada:** Registro silencioso de todas las acciones (Crear, Editar, Eliminar) realizadas en el sistema.

## 🛠️ Tecnologías Utilizadas

*   **Backend:** Laravel 11 (PHP 8.2+)
*   **Base de Datos:** PostgreSQL (con soporte nativo para UUIDs)
*   **Frontend:** Blade, Tailwind CSS, Alpine.js (vía CDN), Chart.js
*   **Librerías Adicionales:** `barryvdh/laravel-dompdf` (Reportes PDF)
*   **Herramienta de BD:** pgAdmin

## 🏗️ Arquitectura y Patrones

*   **SOLID:** Controladores delgados; la lógica de negocio pesada está delegada a **Eventos y Listeners** (ej. `VentaConfirmada` descuenta stock y calcula comisión).
*   **Observers:** Uso del patrón Observer para la auditoría automática de modelos.
*   **UUIDs:** Todas las tablas usan identificadores únicos universales para mayor seguridad.
*   **Soft Deletes:** Implementación de borrado lógico para mantener la integridad del historial.

## 🚀 Requisitos Previos

*   PHP >= 8.2
*   Composer
*   PostgreSQL (ejecutándose en el puerto 5432)
*   Node.js & NPM (Opcional, si se compilan assets locales)

## ⚙️ Instalación y Configuración

Sigue estos pasos para desplegar el sistema en un entorno local:

1.  **Clonar el repositorio:**
    ```bash
    git clone <url-del-repositorio> cappias-app
    cd cappias-app