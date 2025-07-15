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
                            <h3 class="card-title mb-4 text-success"><i class="ti ti-box fs-1 me-2"></i> Paquete Activo
                            </h3>
                            <h5 class="card-text mb-4">
                                Nombre:
                                <strong></strong>
                            </h5>
                            <h5 class="card-text mb-4">
                                Número de citas:
                                <strong></strong>
                            </h5>
                            <h5 class="card-text mb-4">
                                Estado:
                                <strong></strong>
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
                                <strong></strong>
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
                                <strong></strong>
                            </h1>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h3 class="card-title text-primary"><i class="ti ti-building-skyscraper fs-1 me-2"></i>
                                Información</h3>
                            <p class="card-text mb-1">
                                Nombre: <strong></strong>
                            </p>
                            <p class="card-text mb-1">
                                Documento: <strong></strong>
                            </p>
                            <p class="card-text mb-1">
                                Plan: <strong></strong>
                            </p>
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
                                    <h5><i class="ti ti-progress-check text-info"></i> Agendados:
                                        <strong></strong>
                                    </h5>
                                    <h5><i class="ti ti-x text-danger"></i> Cancelados:
                                        <strong></strong>
                                    </h5>
                                    <h5><i class="ti ti-user-check text-success"></i> Asistidos:
                                        <strong></strong>
                                    </h5>
                                    <h5><i class="ti ti-heart-handshake text-info"></i> Confirmado:
                                        <strong></strong>
                                    </h5>
                                    <h5><i class="ti ti-user-cancel text-danger"></i> No asistió:
                                        <strong></strong>
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <h5><i class="ti ti-device-mobile-off text-danger"></i> No contesta:
                                        <strong></strong>
                                    </h5>
                                    <h5><i class="ti ti-copy text-info"></i> Duplicado:
                                        <strong></strong>
                                    </h5>
                                    <h5><i class="ti ti-building text-success"></i> Asistido sede:
                                        <strong></strong>
                                    </h5>
                                    <h5><i class="ti ti-writing text-warning"></i> En seguimiento:
                                        <strong></strong>
                                    </h5>
                                    <h5><i class="ti ti-calendar-clock text-info"></i> Reprogramadas:
                                        <strong></strong>
                                    </h5>
                                    <h5><i class="ti ti-device-desktop-off text-danger"></i> No simit:
                                        <strong></strong>
                                    </h5>
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
            <div class="mt-3">
                <div class="row">
                    <!-- Columna 1 - Historial de paquetes -->
                    <div class="col-md-7">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4><i class="ti ti-history text-primary fs-1 me-2"></i> Historial de paquetes</h4>
                                <div id="ChartRadialProgressDashboardEmpresasHistorial"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4><i class="ti ti-clipboard-list text-warning fs-1 me-2"></i> Paquetes pendientes de activación</h4>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
