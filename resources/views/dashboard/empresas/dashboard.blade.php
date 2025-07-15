<div class="layout-page" id="dashboard-empresas"
    data-citas-consumidas="{{ $empresa['empresa_paquete_activo']->citas_consumidas ?? 0 }}"
    data-citas-faltantes="{{ $empresa['empresa_paquete_activo']->citas_faltantes ?? 0 }}"
    data-citas-agendadas-mes-actual="{{ json_encode($empresa['empresa_paquete_activo']->citasAgendadasPorMesActual ?? []) }}"
    data-citas-asistidas-mes-actual="{{ json_encode($empresa['empresa_paquete_activo']->citasAsistidasPorMesActual ?? []) }}">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        @dump($empresa)
        @if ($rol == 'superadmin' || $rol == 'admin')
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-md-12">
                    <select name="empresa" id="empresa-select-dashboard" class="form-select">
                        <option value="">Seleccione una empresa</option>
                        @foreach ($empresas as $opcion)
                        <option value="{{ $opcion->id_empresa }}">{{ $opcion->Nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row d-flex align-items-stretch">
                <!-- info paquetes -->
                <div class="mb-3 col-md-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h3 class="card-title mb-4 text-success"><i class="ti ti-box fs-1 me-2"></i> Paquete Activo
                            </h3>
                            <h5 class="card-text mb-4">
                                Nombre:
                                <strong>{{ $empresa['empresa_paquete_activo']->nombre_paquete ?? 'Ninguno' }}</strong>
                            </h5>
                            <h5 class="card-text mb-4">
                                Número de citas:
                                <strong>{{ $empresa['empresa_paquete_activo']->numero_citas ?? 0 }}</strong>
                            </h5>
                            <h5 class="card-text mb-4">
                                Estado:
                                <strong>{{ $empresa['empresa_paquete_activo']->estado ?? 'Ninguno' }}</strong>
                            </h5>
                        </div>
                    </div>
                </div>
                <!-- info citas-consumidas -->
                <div class="mb-3 col-md-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body d-flex flex-column">
                            <h4 class="card-title text-warning"><i class="ti ti-package-export fs-1 me-2"></i> Citas
                                Consumidas</h4>
                            <h1 class="card-text mb-1 text-center display-1">
                                <strong>{{ $empresa['empresa_paquete_activo']->citas_consumidas ?? '0' }}</strong>
                            </h1>
                        </div>
                    </div>
                </div>
                <!-- info citas-faltantes -->
                <div class="mb-3 col-md-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body d-flex flex-column">
                            <h3 class="card-title text-danger"><i class="ti ti-packages fs-1 me-2"></i> Citas Faltantes
                            </h3>
                            <h1 class="card-text mb-1 text-center display-1">
                                <strong>{{ $empresa['empresa_paquete_activo']->citas_faltantes ?? '0' }}</strong>
                            </h1>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h3 class="card-title text-primary"><i class="ti ti-building-skyscraper fs-1 me-2"></i>
                                Información</h3>
                            @if (!empty($empresa['empresa_user']->logo))
                            <img src="{{ asset($empresa['empresa_user']->logo) }}" alt="Logo empresa"
                                class="img-fluid mb-2" style="max-width: 100px;">
                            @endif
                            <p class="card-text mb-1">
                                Nombre: <strong>{{ $empresa['empresa_user']->Nombre ?? 'Ninguno' }}</strong>
                            </p>
                            <p class="card-text mb-1">
                                Documento: <strong>{{ $empresa['empresa_user']->tipo_documento_empresa ?? 'Ninguno' }}
                                    {{ $empresa['empresa_user']->documento_empresa ?? 'Ninguno' }}</strong>
                            </p>
                            <p class="card-text mb-1">
                                Plan: <strong>{{ $empresa['empresa_user']->plan_empresa ?? 'Ninguno' }}</strong>
                            </p>
                            @if ($empresa['empresa_user']->estado == 1)
                            <p class="card-text mb-1">
                                Estado: <strong class="text-success">Activo</strong>
                            </p>
                            @else
                            <p class="card-text mb-1">
                                Estado: <strong class="text-danger">Inactivo</strong>
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Columna 1 - Gráfico de barras -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div id="ChartDashboardEmpresasProgressBar"></div>
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><i class="ti ti-progress-check text-info"></i> Agendados: <strong>{{ $empresa['empresa_paquete_activo']->citasAgendadas ?? '0' }}</strong></h5>
                                    <h5><i class="ti ti-x text-danger"></i> Cancelados: <strong>{{ $empresa['empresa_paquete_activo']->citasCanceladas ?? '0' }}</strong></h5>
                                    <h5><i class="ti ti-user-check text-success"></i> Asistidos: <strong>{{ $empresa['empresa_paquete_activo']->citasAsistidas ?? '0' }}</strong></h5>
                                    <h5><i class="ti ti-heart-handshake text-info"></i> Confirmado: <strong>{{ $empresa['empresa_paquete_activo']->citasConfirmadas ?? '0' }}</strong></h5>
                                    <h5><i class="ti ti-user-cancel text-danger"></i> No asistió: <strong>{{ $empresa['empresa_paquete_activo']->citasNoAsistidas ?? '0' }}</strong></h5>
                                </div>
                                <div class="col-md-6">
                                    <h5><i class="ti ti-device-mobile-off text-danger"></i> No contesta: <strong>{{ $empresa['empresa_paquete_activo']->citasNoContestadas ?? '0' }}</strong></h5>
                                    <h5><i class="ti ti-copy text-info"></i> Duplicado: <strong>{{ $empresa['empresa_paquete_activo']->citasDuplicadas ?? '0' }}</strong></h5>
                                    <h5><i class="ti ti-building text-success"></i> Asistido sede: <strong>{{ $empresa['empresa_paquete_activo']->citasAsistidoSede ?? '0' }}</strong></h5>
                                    <h5><i class="ti ti-writing text-warning"></i> En seguimiento: <strong>{{ $empresa['empresa_paquete_activo']->citasSeguimiento ?? '0' }}</strong></h5>
                                    <h5><i class="ti ti-calendar-clock text-info"></i> Reprogramadas: <strong>{{ $empresa['empresa_paquete_activo']->citasReprogramadas ?? '0' }}</strong></h5>
                                    <h5><i class="ti ti-device-desktop-off text-danger"></i> No simit: <strong>{{ $empresa['empresa_paquete_activo']->citasNoSimit ?? '0' }}</strong></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna 2 - Gráfico mixto -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div id="ChartDashboardEmpresasLineBarMixed"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>