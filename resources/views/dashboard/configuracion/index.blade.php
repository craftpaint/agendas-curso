<!-- Layout container -->
<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="accordion col-12 p-0" id="accordionWithIcon">
                    <div class="card accordion-item">
                        <h1 class="accordion-header d-flex align-items-center">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-1" aria-expanded="false">
                                <i class="ti ti-settings-star"></i>
                                General (tb_config)
                            </button>
                        </h1>
                        <div id="accordionWithIcon-1" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <?php if ($user->can('configuracion.General.a')) { ?>
                                    <div class="p-4 d-flex align-items-center justify-content-end">
                                        <a href="#" class="btn btn-primary" id="crear-configuracion-general"><i class="ti ti-settings-plus"></i>Crear valor configuración general</a>
                                    </div>
                                <?php } ?>
                                <div class="card-datatable text-nowrap">
                                    <table class="table datatables-configuracion-general">
                                        <thead>
                                            <tr>
                                                <th>Clave</th>
                                                <th>Valor</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear configuración general -->
    <div class="modal fade" id="modalNuevaConfiguracionGeneral" tabindex="-1" aria-labelledby="modalNuevaConfiguracionGeneralLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevaConfiguracionGeneralLabel">Crear configuración General</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ url('dashboard/configuracion/guardar_configuracion_general') }}" id="form-nueva-configuracion-general">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="clave-configuracion-general" class="form-label">Clave: </label>
                            <input type="text" class="form-control" id="clave-configuracion-general" name="clave-configuracion-general" required>
                        </div>
                        <div class="mb-3">
                            <label for="valor-configuracion-general" class="form-label">Valor: </label>
                            <textarea class="form-control" id="valor-configuracion-general" name="valor-configuracion-general" rows="6" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success boton_submit_configuracion_general" id="guardar-configuracion-general">Guardar Configuración General</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>