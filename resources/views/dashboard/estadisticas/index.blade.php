<!-- Layout container -->
<div class="layout-page" style="padding-top:0 !important" id="estadisticas-fechas-citas">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <h5 class="m-0">Estadisticas</h5>
                    <div class="d-flex">
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
                    </div>
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
            <div class="row">

                <!-- Ajax Sourced Server-side -->

                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="p-4 d-flex align-items-center justify-content-between">
                            <h5 class="m-0" id="comp-fecha-creacion-titulo">Comparativa Semanal: Fechas de Creación</h5>
                        </div>
                        <div class="card-datatable text-nowrap">
                            <div class="chart-container">
                                <div id="ChartComparacionDateCreatedLinear"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="p-4 d-flex align-items-center justify-content-between">
                            <h5 class="m-0" id="comp-fecha-reserva-titulo">Comparativa Semanal: Fechas de Reserva</h5>
                        </div>
                        <div class="card-datatable text-nowrap">
                            <div class="chart-container">
                                <div id="ChartComparacionDateReservaLinear"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/ Ajax Sourced Server-side -->
            </div>
        </div>
        <!-- / Content -->
    </div>
    <!-- Content wrapper -->
</div>
<script>
    const estados = JSON.parse('<?php echo json_encode($estados); ?>');
</script>