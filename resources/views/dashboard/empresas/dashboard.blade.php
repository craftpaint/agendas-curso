<div class="layout-page" id="dashboard-empresas">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        @if ($rol == 'superadmin' || $rol == 'admin')
            <div class="container-xxl flex-grow-1 container-p-y">
                <div class="row">
                    <div class="col-md-12">
                        <select name="empresa" id="empresa-select-dashboard" class="form-select">
                            <option value="">Seleccione una empresa</option>
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
                            <h3 class="card-title mb-4 " style="font-size:22px;"><i class="ti ti-box fs-1 me-2"></i> Paquete Activo</h3>
                            <h4 class="card-text mb-1" style="font-size:15px;" id="nombre-paquete-activo-dashboard-empresa"></h4>
                            <h4 class="card-text mb-1" style="font-size:15px;" id="numero-citas-paquete-activo-dashboard-empresa"></h4>
                            <h4 class="card-text mb-1" style="font-size:15px;" id="estado-paquete-activo-dashboard-empresa"></h4>
                        </div>
                    </div>
                </div>
                <!-- info citas-consumidas -->
                <div class="mb-3 col-md-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body d-flex flex-column">
                            <h4 class="card-title text-info" style="font-size:22px;"><i class="ti ti-package-export fs-1 me-2"></i> Citas Consumidas</h4>
                            <h1 class="card-text mb-1 text-center display-1"
                                style="font-size:60px;"
                                id="citas-consumidas-paquete-activo-dashboard-empresa"></h1>
                        </div>
                    </div>
                </div>
                <!-- info citas-faltantes -->
                <div class="mb-3 col-md-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body d-flex flex-column">
                            <h3 class="card-title verde" style="font-size:22px;"><i class="ti ti-packages fs-1 me-2"></i> Citas Faltantes</h3>
                            <h1 class="card-text mb-1 text-center display-1"
                                style="font-size:60px;"
                                id="citas-faltantes-paquete-activo-dashboard-empresa"></h1>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h3 class="card-title " style="font-size:22px;"><i class="ti ti-building-skyscraper fs-1 me-2"></i>Información</h3>
                            <img id="logo-empresa-dashboard" src="" alt="Logo"
                                class="rounded me-2 mb-2 img-fluid" style="max-height: 70px;">
                            <p class="card-text mb-1" style="font-size:15px;" id="nombre-empresa-dashboard"></p>
                            <p class="card-text mb-1" style="font-size:15px;" id="documento-empresa-dashboard"></p>
                            <p class="card-text mb-1" style="font-size:15px;" id="plan-empresa-dashboard"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Columna 1 - Gráfico de barras -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="mb-4 text-dark" style="font-size:22px;"><i class="ti ti-progress text-info fs-1 me-2"></i> Porcentaje de progreso del paquete</h3>
                            <div id="ChartDashboardEmpresasProgressBar"></div>
                            <div class="row">
                                <div class="col-md-6 mt-5">
                                    <h5><i class="ti ti-bell text-warning"></i> Asistidos: <strong
                                            id="citas-pendientes-paquete-activo-dashboard-empresa"></strong></h5>
                                    <h5><i class="ti ti-x text-danger"></i> Errados: <strong
                                            id="citas-validacion-paquete-activo-dashboard-empresa"></strong></h5>
                                </div>
                                <div class="col-md-6 mt-5">
                                    <h5><i class="ti ti-progress-check text-success"></i> Confirmados: <strong
                                            id="citas-confirmadas-paquete-activo-dashboard-empresa"></strong></h5>
                                    <h5><i class="ti ti-search text-info"></i> En validación: <strong
                                            id="citas-erradas-paquete-activo-dashboard-empresa"></strong></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna 2 - Gráfico mixto -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                             <h3 class="mb-4 text-dark" style="font-size:22px;"><i class="ti ti-chart-infographic text-info fs-1 me-2"></i> Citas Agendadas vs Asistidas del mes actual</h3>
                            <div id="ChartDashboardEmpresasLineBarMixed"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <div class="row">
                    <!-- Columna 1 - Historial de paquetes -->
                    <div class="col-md-7">
                        <div class="card h-100">
                            <div class="card-body">
                                <h3 style="font-size:22px;"><i class="ti ti-history text-primary fs-1 me-2"></i> Historial de paquetes</h3>
                                <div class="row">
                                    <div class="col-md-12 justify-content-center align-item-center">
                                        <div id="ChartRadialProgressDashboardEmpresasHistorial" class="col-12 h-100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 style="font-size:22px;"><i class="ti ti-clipboard-list text-warning fs-1 me-2"></i> Paquetes pendientes de activación</h4>
                                <div id="ListadopaquetesPendientes"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para detalles de paquete -->
    <div class="modal fade" id="modalDetallesPaquete" tabindex="-1" aria-labelledby="modalDetallesPaqueteLabel"
        aria-hidden="true">
        <div class="modal-dialog" style="width: 80vw; height:80vw; max-width: 90vw;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoPaqueteLabel">Citas del paquete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="datatables-detalles-paquete table">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
