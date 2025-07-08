<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <!-- Encabezado -->
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <h2 class="mb-0">Paquetes</h2>
                </div>
                <div class="content_filtros mb-4">
                    <div class="filtros_form">
                        <form id="form_filtros">
                            <div class="form-group group-grow">
                                <input placeholder="Buscar paquete.." class="form-control" type="text"
                                    id="buscar-nombre-paquetes">
                            </div>
                            <div class="form-group group-grow">
                                <select id="filtro-tipo-paquete" class="select2 form-select "
                                    placeholder="Seleccionar tipo">
                                    <option value="">Todos los tipos</option>
                                    <option value="PREPAGO">PREPAGO</option>
                                    <option value="POSPAGO">POSPAGO</option>
                                </select>
                            </div>
                            <div class="form-group group-grow">
                                <input placeholder="Buscar valor.." class="form-control" type="number"
                                    id="buscar-valor-paquetes">
                            </div>
                        </form>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="filtro-reiniciar">Reiniciar filtros</button>
                        <?php if ($user->can('paquete.listado.a')) { ?>
                            <button type="button" class="btn btn-success" id="crear-paquete" data-bs-toggle="modal"
                                data-bs-target="#modalNuevoPaquete">Crear Paquete</button>
                        <?php } ?>
                    </div>
                </div>
                <!-- Listado de Paquetes -->
                <div class="card-datatable text-nowrap">
                    <table class="datatables-paquetes table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>N° Citas</th>
                                <th>Valor</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear paquete -->
    <div class="modal fade" id="modalNuevoPaquete" tabindex="-1" aria-labelledby="modalNuevoPaqueteLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoPaqueteLabel">Crear Paquete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ url('dashboard/paquetes/guardar') }}" id="form-nuevo-paquete">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre-paquete" class="form-label">Nombre del Paquete</label>
                            <input type="text" class="form-control" id="nombre-paquete" name="nombre-paquete"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion-paquete" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion-paquete" name="descripcion-paquete" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="numero-citas-paquete" class="form-label">Número de Citas</label>
                            <input type="number" class="form-control" id="numero-citas-paquete"
                                name="numero-citas-paquete" required>
                        </div>
                        <div class="mb-3">
                            <label for="valor-paquete" class="form-label">Valor</label>
                            <input type="number" class="form-control" id="valor-paquete" name="valor-paquete"
                                required />
                        </div>
                        <div class="mb-3">
                            <label for="tipo-paquete" class="form-label">Tipo de Paquete</label>
                            <select class="form-select" id="tipo-paquete" name="tipo-paquete" required>
                                <option value="PREPAGO">PREPAGO</option>
                                <option value="POSPAGO">POSPAGO</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success boton_submit_paquete" id="guardar-paquete">Guardar Paquete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
