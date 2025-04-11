<!-- Layout container -->
<div class="layout-page" style="padding-top:0 !important">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <input type="hidden" name="tipo_cita" id="tipo_cita" value="<?= isset($tipoSede) ? $tipoSede : '' ?>">
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <h5 class="m-0">Citas</h5>
                    <a href="{{ url('dashboard/citas/add') }}" class="btn btn-primary" style="margin-left: auto;margin-right: 10px;">Crear cita</a>
                    <a href="#" data-action="{{ url('dashboard/citas/dowload') }}" class="btn_descagar_cita btn btn-dark">Descargar</a>
                </div>
                <div>
                    <!-- Filtros -->
                    <div class="content_filtros mb-4">
                        <div class="filtros_botones">
                            <div class="btn-group" role="group" aria-label="Filtro Día">
                                <button id="filtro-ayer" class="btn btn-outline-primary">Ayer</button>
                                <button id="filtro-hoy" class="btn btn-outline-primary">Hoy</button>
                                <button id="filtro-manana" class="btn btn-outline-primary">Mañana</button>
                            </div>
                        </div>
                        <div class="filtros_form">
                            <form id="form_filtros">
                                <div class="form-group form-group-grow">
                                    <input type="date" class="form-control" id="filtro-fecha" name="filtro-fecha">
                                </div>
                                <div class="form-group form-group-grow">
                                    <input type="date" class="form-control" id="filtro-fecha-end" name="filtro-fecha-end">
                                </div>
                                <?php if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'callcenter' || $rol == 'liquidador' || $rol == 'lidercallcenter') { ?>
                                    <div class="form-group form-group-grow">
                                        <select id="filtro-sede" class="select2 form-select" multiple="multiple" placeholder="Seleccionar sede">
                                            <option value="">Todas las sedes</option>
                                            <?php
                                            if (is_array($sedes) && !empty($sedes)) {
                                                foreach ($sedes as $key => $sede) {
                                                    $a_festivos = @unserialize($sede->festivos_sede);
                                                    $a_festivos = $a_festivos !== false ? $a_festivos : array();
                                                    echo '<option data-festivos=' . json_encode($a_festivos) . ' value="' . $sede->id_sede . '">' . $sede->nombre_sede . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                <?php } ?>

                                <div class="form-group form-group-grow">
                                    <select id="filtro-estado" class="select2 form-select" multiple="multiple" placeholder="Seleccionar estado">
                                        <option value="">Todos los estados</option>
                                        <?php
                                        if (is_array($estados) && !empty($estados)) {
                                            foreach ($estados as $key => $estado) {
                                                echo '<option value="' . $estado->id_estado . '">' . $estado->nombre_estado . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group form-group-grow">
                                    <select id="filtro-estado-verificado" class="select2 form-select" multiple="multiple" placeholder="Seleccionar estado Verificado">
                                        <option value="">Todos los estados</option>
                                        <?php
                                        if (is_array($estados) && !empty($estados)) {
                                            foreach ($estados as $key => $estado) {
                                                echo '<option value="' . $estado->id_estado . '">' . $estado->nombre_estado . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <?php if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'callcenter' || $rol == 'liquidador' || $rol == 'lidercallcenter') { ?>
                                    <div class="form-group form-group-grow">
                                        <select id="filtro-responsable" class="select2 form-select" multiple="multiple" placeholder="Seleccionar origen">
                                            <option value="">Todos </option>
                                            <option value="Desconocido">Desconocido</option>
                                            <option value="Sede">Sede</option>
                                            <option value="Cliente">Cliente</option>
                                            <option value="Club del conductor">Club del conductor</option>
                                        </select>
                                    </div>
                                <?php } ?>
                                <?php if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'callcenter' || $rol == 'liquidador' || $rol == 'lidercallcenter') { ?>
                                    <div class="form-group form-group-grow">
                                        <select id="filtro-origen" class="select2 form-select" multiple="multiple" placeholder="Seleccionar Tag">
                                            <option value="">Todos los Tags</option>
                                            <?php
                                            if (is_array($origenes) && !empty($origenes)) {
                                                foreach ($origenes as $key => $origen) {
                                                    echo '<option value="' . $origen->origen . '">' . $origen->origen . '</option>';
                                                }
                                            }
                                            ?>
                                            <option value=null>Null</option>
                                        </select>
                                    </div>
                                <?php } ?>
                                <?php if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'lidercallcenter') { ?>
                                    <div class="form-group form-group-grow">
                                        <select id="filtro-agente" class="select2 form-select" multiple="multiple" placeholder="Seleccionar agente">
                                            <option value="">Todas los agentes</option>
                                            <?php
                                            if (is_array($listado_agentes) && !empty($listado_agentes)) {
                                                foreach ($listado_agentes as $key => $agente) {
                                                    echo '<option value="' . $agente['id'] . '">' . $agente['name'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                <?php } ?>
                                <div class="woow-input-wrapper form-group">
                                    <button class="woow-icon-search" type="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="25px" width="25px">
                                            <path stroke-linejoin="round" stroke-linecap="round" stroke-width="1.5" stroke="#fff" d="M11.5 21C16.7467 21 21 16.7467 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21Z"></path>
                                            <path stroke-linejoin="round" stroke-linecap="round" stroke-width="1.5" stroke="#fff" d="M22 22L20 20"></path>
                                        </svg>
                                    </button>
                                    <input placeholder="Buscar.." class="woow-input-search" type="text" id="woow-search-citas">
                                </div>
                                <div>
                                    <Button type="button" class="btn btn-primary" id="filtro-reset">Reiniciar filtros</Button>
                                </div>

                            </form>
                        </div>

                    </div>
                </div>
                <div class="card-datatable text-nowrap">
                    <table class="datatables-citas table">
                        <thead>
                            <tr>
                                <?php if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'callcenter' || $rol == 'liquidador' || $rol == 'lidercallcenter') { ?>
                                    <th>Cliente</th>
                                    <th></th>
                                    <th>Sede - comparendo</th>
                                    <th>Fecha Cita</th>
                                    <th>Fecha Creación</th>
                                    <th>Estado</th>
                                    <th>Estado Verificado</th>
                                    <th>Servicio</th>
                                    <th>Agente Callcenter</th>
                                    <th>Origen</th>
                                    <th>Tag</th>
                                    <th></th>
                                <?php } else { ?>
                                    <th>Cliente</th>
                                    <th></th>
                                    <th>Comparendo</th>
                                    <th>Fecha Cita</th>
                                    <th>Fecha Creación</th>
                                    <th>Estado</th>
                                    <th>Estado Verificado</th>
                                    <th>Servicio</th>
                                    <th></th>
                                <?php } ?>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <!--/ Ajax Sourced Server-side -->
        </div>
        <!-- / Content -->
    </div>
    <!-- Content wrapper -->
</div>
<script>
    const estados = JSON.parse('<?php echo json_encode($estados); ?>');
    const servicios_liquidador = JSON.parse('<?php echo json_encode($servicios_liquidador); ?>');
    const agentes = JSON.parse('<?php echo json_encode($agentes); ?>');
</script>