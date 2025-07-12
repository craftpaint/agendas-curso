<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <!-- info paquetes -->
                <div class="mb-3 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-success"><i class="ti ti-box"></i> Paquete Activo</h5>
                            <p class="card-text mb-1">
                                Nombre: <strong>{{ $empresa['empresa_paquete_activo']->nombre_paquete ?? 'Ninguno' }}</strong>
                            </p>
                            <p class="card-text mb-1">
                                Número de citas: <strong>{{ $empresa['empresa_paquete_activo']->numero_citas ?? 0 }}</strong>
                            </p>
                            <p class="card-text mb-1">
                                Tipo paquete: <strong>{{ $empresa['empresa_paquete_activo']->tipo_paquete ?? 'Ninguno' }}</strong>
                            </p>
                            <p class="card-text mb-1">
                                Estado: <strong>{{ $empresa['empresa_paquete_activo']->estado ?? 'Ninguno' }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
                <!-- info citas-consumidas -->
                <div class="mb-3 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-warning"><i class="ti ti-package-export"></i> Citas Consumidas</h5>
                            <p class="card-text mb-1">
                                Cantidad: <strong>{{ $empresa['empresa_paquete_activo']->citas_consumidas ?? 'Ninguna' }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-danger"><i class="ti ti-packages"></i> Citas Faltantes</h5>
                            <p class="card-text mb-1">
                                Cantidad: <strong>{{ $empresa['empresa_paquete_activo']->citas_faltantes ?? 'Ninguna' }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-info"><i class="ti ti-building-skyscraper"></i> Información empresa</h5>
                            <p class="card-text mb-1">
                                Nombre: <strong>{{  $empresa['empresa_user']->Nombre ?? 'Ninguno' }}</strong>
                            </p>
                            <p class="card-text mb-1">
                                Documento: <strong>{{  $empresa['empresa_user']->Nombre ?? 'Ninguno' }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
