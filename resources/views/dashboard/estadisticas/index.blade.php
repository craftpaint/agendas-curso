<!-- Layout container -->
<div class="layout-page" id="estadisticas-fechas-citas">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <h2 class="m-0">Estadisticas</h2>

                    <button class="btn btn-primary text-nowrap d-inline-block" type="button" data-bs-toggle="modal" data-bs-target="#filtrosModal">
                        <i class="ti ti-filter" id="iconBtnFiltro">
                        </i>
                        Filtros
                    </button>
                    <!-- <div class="d-flex">
                        <div class="m-3">
                            <select id="select-rango-fechas-estadisticas" class="form-select selectpicker" data-size="10">
                                <option disabled>Seleccionar rango de fechas</option>
                                <option value="1" selected>Esta semana VS semana anterior</option>
                                <option value="2">Ultimas 2 semanas VS 2 semanas anteriores</option>
                                <option value="3">Este mes VS mes anterior</option>
                                <option value="4">Este trimestre VS trimestre anterior</option>
                            </select>
                        </div>
                        <div id="dateDiv" class="m-3">
                            <input
                                type="text"
                                class="dateInput"
                                id="datePicker"
                                placeholder="Date:  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MMYYYY" />
                        </div>
                    </div> -->
                </div>
                <div class="p-4">
                    <p>Rango fechas actual: <span id="ConTextRangoFechas"></span>
                    </p>
                    <p>Rango fechas historico: <span id="ConTextRangoFechasHistorico"></span>
                    </p>
                </div>
            </div>
            <!--/ Ajax Sourced Server-side -->
        </div>
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card p-4">
                <div class="row">
                    <h4 class="card-header">Comparativa de numero de citas por fecha de creación y por fecha de reserva entre rango de fechas.</h4>

                    <!-- Ajax Sourced Server-side -->
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="m-0" id="comp-fecha-creacion-titulo">Comparativa Semanal: Fechas de Creación</h5>
                            </div>
                            <div class="card-body">
                                <div id="ChartComparacionDateCreatedLinear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="m-0" id="comp-fecha-reserva-titulo">Comparativa Semanal: Fechas de Reserva</h5>
                            </div>
                            <div class="card-body">
                                <div id="ChartComparacionDateReservaLinear"></div>
                            </div>
                        </div>
                    </div>
                    <!--/ Ajax Sourced Server-side -->
                </div>
            </div>
        </div>

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card p-4">
                <div class="row">
                    <h4 class="card-header">Comparativa de estados y estados verificados entre rango de fechas.</h4>

                    <!-- Ajax Sourced Server-side -->
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="m-0" id="comp-estado-titulo">Comparativa Estado</h5>
                            </div>
                            <div class="card-body">
                                <div id="ChartComparacionEstado"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="m-0" id="comp-estado-verificado-titulo">Comparativa Estado Verificado</h5>
                            </div>
                            <div class="card-body">
                                <div id="ChartComparacionEstadoVerificado"></div>
                            </div>
                        </div>
                    </div>
                    <!--/ Ajax Sourced Server-side -->
                </div>
            </div>
        </div>
        <!-- / Content -->
    </div>
    <!-- Content wrapper -->

    <div class="modal fade" id="filtrosModal" tabindex="-1" aria-labelledby="filtrosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filtrosModalLabel">Filtrar estadísticas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="card border-0">
                        <div class="card-body">
                            <div class="m-3">
                                <select id="select-rango-fechas-estadisticas" class="select2 form-select selectpicker" data-size="10">
                                    <option disabled>Seleccionar rango de fechas</option>
                                    <option value="1" selected>Esta semana VS semana anterior</option>
                                    <option value="2">Ultimas 2 semanas VS 2 semanas anteriores</option>
                                    <option value="3">Este mes VS mes anterior</option>
                                    <option value="4">Este trimestre VS trimestre anterior</option>
                                </select>
                            </div>
                            <div id="dateDiv" class="m-3">
                                <input
                                    type="text"
                                    class="dateInput w-100"
                                    id="datePicker"
                                    placeholder="Date:  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MMYYYY" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- Botón aplicar filtros -->
                    <button type="button" class="btn btn-outline-danger" id="btnClearFilters">
                        <i class="ti ti-filter-x"></i> Limpiar filtros
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const estados = JSON.parse('<?php echo json_encode($estados); ?>');
</script>