<!-- Layout container -->
<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <h5 class="m-0">Liquidador</h5>
                    <?php if ($user->can('liquidador.descargar.v')) { ?>
                        <a href="#" data-action="{{ url('dashboard/liquidador/dowloadLiquidador') }}" class="btn_descagar_cita btn btn-dark">Descargar</a>
                    <?php } ?>
                </div>
                <div>
                    <!-- Filtros -->
                    <div class="content_filtros mb-4">
                        <div class="filtros_botones">
                            <div class="btn-group" role="group" aria-label="Filtro Día">
                                <button id="filtro-cuatro-meses-anteriores" class="btn btn-outline-primary"></button>
                                <button id="filtro-tres-meses-anteriores" class="btn btn-outline-primary"></button>
                                <button id="filtro-dos-meses-anteriores" class="btn btn-outline-primary"></button>
                                <button id="filtro-mes-anterior" class="btn btn-outline-primary"></button>
                                <button id="filtro-mes-actual" class="btn btn-outline-primary"></button>
                            </div>
                        </div>
                        <div class="filtros_form_liquidador col-8">
                            <form id="form_filtros">
                                @csrf
                                <input type="hidden" name="tipo_cita" id="tipo_cita" value="<?= isset($tipoSede) ? $tipoSede : '' ?>">
                                <div class="form-group form-group-grow">
                                    <input type="date" class="form-control" id="filtro-fecha" name="filtro-fecha">
                                </div>
                                <div class="form-group form-group-grow">
                                    <input type="date" class="form-control" id="filtro-fecha-end" name="filtro-fecha-end">
                                </div>
                                <div class="form-group form-group-grow">
                                    <select id="filtro-sede" class="select2 form-select" placeholder="Selecciona una sede">
                                        <option value="">Todas las sedes</option>
                                        <?php
                                            if ($sedes->isNotEmpty()) {
                                                foreach ($sedes as $key => $sede) {
                                                    $a_festivos = @unserialize($sede->festivos_sede);
                                                    $a_festivos = $a_festivos !== false ? $a_festivos : array();
                                                    echo '<option data-festivos=' . json_encode($a_festivos) . ' value="' . $sede->id_sede . '">' . $sede->nombre_sede . '</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group form-group-grow">
                                    <select id="filtro-servicios_liquidador" class="select2 form-select" placeholder="Seleccionar un servicio">
                                        <option value="">Todos los servicios liquidador</option>
                                        <?php
                                        if (is_array($servicios_liquidador) && !empty($servicios_liquidador)) {
                                            foreach ($servicios_liquidador as $key => $servicio) {
                                                echo '<option value="' . $servicio->id_servicio_liquidador . '">' . $servicio->nombre_servicio_liquidador . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group form-group-grow">
                                    <select id="filtro-estado-validacion-liquidador" class="select2 form-select" placeholder="Seleccionar un estado de liquidación">
                                        <option value="">Seleccionar un estado de liquidación</option>
                                        <option value="Confirmado">Confirmado</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Errado">Errado</option>
                                        <option value="En validación">En validación</option>
                                    </select>
                                </div>
                                <div class="form-group  ">
                                    <select id="filtro-estado-pago-liquidador" class="select2 form-select" placeholder="Seleccionar un estado de pago">
                                        <option value="">Seleccionar un estado de pago</option>
                                        <option value="pagado">Pagado</option>
                                        <option value="pendiente">Pendiente</option>
                                    </select>
                                </div>
                                <?php if ($user->can('liquidador.Ver Tipo Paquete.v')) { ?>
                                    <div class="form-group form-group-grow">
                                        <select id="filtro-tipo-paquete-cita-liquidador" class="select2 form-select" multiple="multiple" placeholder="Seleccionar el tipo paquete">
                                            <option value="">Todas los tipos de paquete</option>
                                            <option value="PREPAGO">PREPAGO</option>
                                            <option value="POSPAGO">POSPAGO</option>
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
            </div>

            <div class="my-4 col-12 mx-auto row justify-content-between">
                <div class="col-3 card">
                    <div class="my-auto">
                        <div class="card-header d-flex align-items-center justify-content-center">
                            <div class="badge rounded bg-label-primary me-4 p-2"><i class="ti ti-calendar-dollar icon-xl"></i></div>
                            <h5 class="my-auto">Rango Fecha:</h5>
                        </div>
                        <div class="card-info d-flex align-items-center justify-content-center">
                            <h5><strong><span id="rango-fecha"></span></strong> al <strong><span id="rango-fecha-fin"></span></strong></h5>
                        </div>
                    </div>
                </div>
                <?php
                if ($user->can('liquidador.Valores Totales.v')) {
                ?>
                    <div class="col-4 card">
                        <div class="my-auto">
                            <div class="card-header d-flex align-items-center justify-content-center">
                                <div class="badge rounded bg-label-success me-4 p-2"><i class="ti ti-currency-dollar icon-xl"></i></div>
                                <h5 class="my-auto">Valor a Liquidar / Valor confirmado</h5>
                            </div>
                            <div class="card-info d-flex align-items-center justify-content-center">
                                <h4><strong>$<span id="lblTotalValorALiquidar">0</span></strong> / <strong>$<span id="lblTotalValorLiquidado">0</span></strong></h4>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>

                <div class="col-2 card">
                    <div class="my-auto py-3">
                        <div class="card-header d-flex align-items-center  p-0">
                            <div class="badge rounded bg-label-success me-4 p-2"><i class="ti ti-eye-check icon-xl"></i></div>
                            <p><strong class="ml-3">Confirmados: </strong> <span id="lblTotalConfirmados">0</span></p>
                        </div>
                        <div class="card-header d-flex align-items-center p-0 mt-3">
                            <div class="badge rounded bg-label-danger me-4 p-2"><i class="ti ti-eye-x icon-xl"></i></div>
                            <p><strong class="ml-3">Errados: </strong> <span id="lblTotalErrados">0</span></p>
                        </div>
                        <div class="card-header d-flex align-items-center p-0 my-3">
                            <div class="badge rounded bg-label-info me-4 p-2"><i class="ti ti-eye-cog icon-xl"></i></div>
                            <p><strong class="ml-3">En validación: </strong> <span id="lblTotalValidacion">0</span></p>
                        </div>
                        <div class="card-header d-flex align-items-center p-0">
                            <div class="badge rounded bg-label-warning me-4 p-2"><i class="ti ti-eye-exclamation icon-xl"></i></div>
                            <p><strong class="ml-3">Pendientes: </strong> <span id="lblTotalPendientes">0</span></p>
                        </div>
                    </div>
                </div>
                <div class="col-2 card ">
                    <div class="my-auto">
                        <div class="card-header d-flex align-items-center  p-0">
                            <div class="badge rounded bg-label-warning me-4 p-2"><i class="ti ti-coin-off icon-xl"></i></div>
                            <p><strong class="ml-3">Pago Pendiente: </strong> <span id="lblPagoPendiente">0</span></p>
                        </div>
                        <div class="card-header d-flex align-items-center p-0 my-3">
                            <div class="badge rounded bg-label-success me-4 p-2"><i class="ti ti-coin icon-xl"></i></div>
                            <p><strong class="ml-3">Pago Realizado: </strong> <span id="lblPagoPagado">0</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 p-4" style="display:none !important;" id="checkbox-selected-div-contador-pago">
                <div class="">
                    <button type="button"
                        class="btn btn-primary "
                        id="btnChangePago"
                        style="display:none !important;">Cambiar estado de pago</button>
                </div>
                <div class="mt-3">
                    <p>Citas seleccionadas: <span id="checkbox-selected-contador-citas-pago">0</span> - Valor total de las citas seleccionadas: $<span id="checkbox-selected-valor-total-pago">0</span></p>
                </div>
            </div>

            <div class="card">

                <div class="card-datatable text-nowrap">
                    <table class="datatables-citas-liquidador table">
                        <thead>
                            <tr>
                                <th>
                                    <?php
                                    if ($user->can('liquidador.Pago Masivo Realizado.v')) {
                                    ?>
                                        <label for="checkAll">todos</label>
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                    <?php
                                    }
                                    ?>
                                </th>
                                <th>Cliente</th>
                                <th>Sede</th>
                                <th>Fecha Cita</th>
                                <th>Fecha Creación</th>
                                <th>Validación</th>
                                <th>Comentario</th>
                                <th>Valor</th>
                                <th>Pago Realizado</th>
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

<!-- Modal para editar comentario -->
<div class="modal fade" id="modalComentarioLiquidador" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formComentarioLiquidador">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar comentario: <span id="comentario_nombre_cliente"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="mx-5 px-5">
                    <span id="comentario_tipo_doc_cliente"></span> <span id="comentario_doc_cliente"></span>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_cita" id="comentario-id-cita">
                    <input type="hidden" name="id_liquidador" id="comentario-id-liquidador">
                    <div class="mb-3">
                        <label for="comentario-text" class="form-label">Comentario:</label>
                        <textarea class="form-control"
                            name="comentario_liquidador"
                            id="comentario-text"
                            rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    const estados = JSON.parse('<?php echo json_encode($estados); ?>');
    const servicios_liquidador = JSON.parse('<?php echo json_encode($servicios_liquidador); ?>');
</script>