<!-- Layout container -->
<div class="layout-page" style="padding-top:0 !important" id="estadisticas-Sedes">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <h2 class="m-0">Estadisticas por Sedes</h2>
                    <button class="btn btn-primary text-nowrap d-inline-block" type="button" data-bs-toggle="modal" data-bs-target="#filtrosModal">
                        <i class="ti ti-filter" id="iconBtnFiltro">
                        </i>
                        Filtros
                    </button>
                </div>
                <div class="p-4">
                    <p>Rango fechas actual: <span id="ConTextRangoFechas" class="fw-bold"></span>
                    </p>
                </div>
            </div>
        </div>
        <!--/ Ajax Sourced Server-side -->
    </div>
    <div class="container-xxl flex-grow-1 container-p-y ">
        <div class="card p-4">
            <div class="row">
                {{-- Gráfico de barras --}}
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">Citas por sede (acumulado)</div>
                        <div class="card-body">
                            <div id="barSedes" style="height:300px"></div>
                        </div>
                    </div>
                </div>
                {{-- Gráfico de pastel --}}
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">% de citas por sede</div>
                        <div class="card-body">
                            <div id="pieSedes" style="height:300px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

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
                                <option value="1" selected>Esta semana</option>
                                <option value="2">2 semanas</option>
                                <option value="3">Este mes</option>
                                <option value="4">Este trimestre</option>
                            </select>
                        </div>
                        <div class="m-3">
                            <select id="filter-sedes" class="form-select" multiple>
                                @foreach($sedes as $s)
                                <option value="{{ $s->id_sede }}" selected>
                                    {{ $s->nombre_sede }}
                                </option>
                                @endforeach
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
<!-- Content wrapper -->
</div>
<script>
    const sedes = <?php echo json_encode($sedes, JSON_UNESCAPED_UNICODE); ?>;
</script>