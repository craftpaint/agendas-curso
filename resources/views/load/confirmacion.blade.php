<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Citas - Curso Comparendo</title>
    
    <!-- Incluir los mismos estilos que el formulario para consistencia -->
    <link rel="stylesheet" href="{{url('assets/vendor/css/rtl/core.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/css/rtl/theme-default.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/fonts/tabler-icons.css')}}" />
    <link rel="stylesheet" href="{{url('assets/css/demo.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/node-waves/node-waves.css')}}" />
    <link rel="stylesheet" href="{{url('assets/css/style.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
    
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Public Sans', sans-serif;
            padding: 20px;
        }
        .confirmacion-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .success-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .cita-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        .cita-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .cita-numero {
            background: #4361ee;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: 600;
            min-width: 200px;
            color: #495057;
        }
        .info-value {
            color: #212529;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .btn-primary {
            background-color: #4361ee;
            border-color: #4361ee;
            padding: 10px 30px;
            font-weight: 600;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #3a56d4;
            border-color: #3a56d4;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary-box {
            background: #e7f3ff;
            border-left: 4px solid #4361ee;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="confirmacion-container">
        <!-- Encabezado -->
        <div class="success-header">
            <div class="success-icon">
                <i class="ti ti-circle-check"></i>
            </div>
            <h1>¡Citas Agendadas Exitosamente!</h1>
            <p class="lead">Se han agendado {{ $total }} citas correctamente.</p>
        </div>

        <!-- Resumen -->
        <div class="summary-box">
            <h5><i class="ti ti-info-circle"></i> Resumen del agendamiento</h5>
            <p><strong>Total de comparendos:</strong> {{ $total }}</p>
            <p><strong>Fecha de agendamiento:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
            <p>Recibirás un correo de confirmación para cada cita agendada.</p>
        </div>

        <!-- Lista de citas -->
        @foreach($citas as $index => $cita)
        <div class="cita-item">
            <div class="cita-header">
                <h4 class="mb-0">Cita #{{ $index + 1 }}</h4>
                <span class="cita-numero">Comparendo {{ $index + 1 }}</span>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <span class="info-label"><i class="ti ti-calendar"></i> Fecha:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($cita->reserva_cita)->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="ti ti-clock"></i> Horario:</span>
                        <span class="info-value">{{ $cita->rango_horario }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="ti ti-building"></i> Sede:</span>
                        <span class="info-value">{{ $cita->nombre_sede }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="ti ti-map-pin"></i> Dirección:</span>
                        <span class="info-value">{{ $cita->direccion_sede }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    @if($cita->codigos_comparendo)
                    <div class="info-row">
                        <span class="info-label"><i class="ti ti-tag"></i> Código comparendo:</span>
                        <span class="info-value">{{ $cita->codigos_comparendo }}</span>
                    </div>
                    @endif
                    
                    @if($cita->fecha_notificacion_comparendo)
                    <div class="info-row">
                        <span class="info-label"><i class="ti ti-calendar-event"></i> Fecha notificación:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($cita->fecha_notificacion_comparendo)->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    
                    <div class="info-row">
                        <span class="info-label"><i class="ti ti-id"></i> Estado:</span>
                        <span class="info-value badge bg-success">Agendado</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label"><i class="ti ti-calendar-time"></i> ID Cita:</span>
                        <span class="info-value">#{{ $cita->id_cita }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Información importante -->
            <div class="alert alert-warning mt-3 mb-0">
                <i class="ti ti-alert-circle"></i> 
                <strong>Recuerda:</strong> Debes llegar 30 minutos antes de la cita. 
                Traer documento de identidad y licencia de conducción.
            </div>
        </div>
        @endforeach

        <!-- Acciones -->
        <div class="actions">
            <div class="alert alert-info">
                <i class="ti ti-info-circle"></i>
                Se ha enviado un correo de confirmación a tu dirección de email 
                con los detalles de todas las citas.
            </div>
            
            <div class="d-flex justify-content-center gap-3">
                <a href="/" class="btn btn-primary">
                    <i class="ti ti-home"></i> Volver al inicio
                </a>
                
                <a href="https://curso-comparendo.com" target="_blank" class="btn btn-outline-primary">
                    <i class="ti ti-external-link"></i> Ir al sitio web
                </a>
                
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="ti ti-printer"></i> Imprimir comprobante
                </button>
            </div>
            
            <!-- Información de contacto -->
            <div class="mt-4 text-muted">
                <p class="mb-1">
                    <i class="ti ti-phone"></i> 
                    <strong>Soporte:</strong> 3054511014 / 3054628258
                </p>
                <p class="mb-0">
                    <i class="ti ti-mail"></i> 
                    <strong>Email:</strong> info@curso-comparendo.com
                </p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{url('assets/vendor/libs/jquery/jquery.js')}}"></script>
    <script src="{{url('assets/vendor/js/bootstrap.js')}}"></script>
    <script src="{{url('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
    <script>
        // Mostrar mensaje de éxito
        $(document).ready(function() {
            // Guardar en localStorage que las citas fueron agendadas
            localStorage.setItem('citas_agendadas', 'true');
            
            // Opcional: Enviar evento a Google Analytics
            if (typeof gtag !== 'undefined') {
                gtag('event', 'citas_agendadas', {
                    'event_category': 'agendamiento',
                    'event_label': 'multiple',
                    'value': {{ $total }}
                });
            }
        });
    </script>
</body>
</html>