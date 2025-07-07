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
                                <input placeholder="Buscar paquete.." class="form-control" type="text" id="buscar-nombre-paquetes">
                            </div>
                            <div class="form-group group-grow">
                                <select id="filtro-tipo-paquete" class="select2 form-select " placeholder="Seleccionar tipo">
                                    <option value="">Todos los tipos</option>
                                    <option value="PREPAGO">PREPAGO</option>
                                    <option value="POSPAGO">POSPAGO</option>
                                </select>
                            </div>
                            <div class="form-group group-grow">
                                <input placeholder="Buscar valor.." class="form-control" type="number" id="buscar-valor-paquetes">
                            </div>
                        </form>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="filtro-reiniciar">Reiniciar filtros</button>
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
</div>
