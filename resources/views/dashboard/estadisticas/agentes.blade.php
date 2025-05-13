<!-- Layout container -->
<div class="layout-page" style="padding-top:0 !important" id="estadisticas-agentes">
    @php
    $agentes = $listado_agentes;
    @endphp
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <h2 class="m-0">Estadisticas por agentes</h2>
                    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFilters" aria-controls="offcanvasFilters">
                        <i class="ti ti-filter"></i>
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
    @if(!$user->can('estadisticas.Solo ver estadisticas propias.v'))
    <div class="container-xxl flex-grow-1 container-p-y ">
        <div class="card p-4">
            <div class="row">
                <h5 class="card-header">Grafica Global</h5>
                <!-- Ajax Sourced Server-side -->
                @foreach(['reserva'=>'Por reserva','creacion'=>'Por creación'] as $field => $label)
                <div class="col-12">
                    <div class="card my-2">
                        <div class="card-header">{{ $label }}</div>
                        <div class="card-body" id="Global-{{ $field }}-block">
                            <div id="ChartGlobal-{{ $field }}" style="height:300px"></div>
                            <div id="BadgesGlobal-{{ $field }}" class="mb-3 text-center"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($user->can('estadisticas.Solo ver estadisticas propias.v'))
    @php
    $ag = collect($listado_agentes)
    ->first(fn($a) => $a['id'] == $miUsuarioId);
    @endphp
    @if($ag)
    <div class="container-xxl flex-grow-1 container-p-y agente-block" id="block-{{ $ag['id'] }}">
        <div class="card p-4">
            <div class="row">
                <h5 class="card-header">Agente: {{ $ag['name'] }}</h5>
                <!-- Ajax Sourced Server-side -->
                @foreach(['reserva'=>'Por reserva','creacion'=>'Por creación'] as $field=>$label)
                <div class="col-12">
                    <div class="card my-2">
                        <div class="card-header">{{ $label }}</div>
                        <div class="card-body" id="Agent-{{ $ag['id'] }}-block">
                            <div id="ChartAgent-{{ $ag['id'] }}-{{ $field }}" style="height:250px"></div>
                            <div id="BadgesAgent-{{ $ag['id'] }}-{{ $field }}" class="mb-3 text-center"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @else
    <!-- / Content -->
    @foreach($listado_agentes as $ag)
    <div class="container-xxl flex-grow-1 container-p-y agente-block" id="block-{{ $ag['id'] }}">
        <div class="card p-4">
            <div class="row">
                <h5 class="card-header">Agente: {{ $ag['name'] }}</h5>
                <!-- Ajax Sourced Server-side -->
                @foreach(['reserva'=>'Por reserva','creacion'=>'Por creación'] as $field=>$label)
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">{{ $label }}</div>
                        <div class="card-body" id="Agent-{{ $ag['id'] }}-block">
                            <div id="ChartAgent-{{ $ag['id'] }}-{{ $field }}" style="height:250px"></div>
                            <div id="BadgesAgent-{{ $ag['id'] }}-{{ $field }}" class="mb-3 text-center"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
    @endif

</div>

<div class="offcanvas offcanvas-top" tabindex="-1" id="offcanvasFilters" aria-labelledby="offcanvasFiltersLabel">
    <div class="offcanvas-header">
        <h5 id="offcanvasFiltersLabel">Filtros</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <div class="card">
            <div class="d-flex justify-content-center">
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
                    @php
                    $estadosJson = $estados->map(fn($e)=>[
                    'text' => $e->nombre_estado,
                    'id' => $e->nombre_estado,
                    'color' => $e->color_estado
                    ]);
                    @endphp
                    <select id="filter-estados" class="form-select" multiple="multiple" style="width:100%">

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
    </div>
</div>

<!-- Content wrapper -->
</div>
<script>
    const estados = JSON.parse('<?php echo json_encode($estadosJson); ?>');
    const agentes = JSON.parse('<?php echo json_encode($listado_agentes); ?>');
    const DEFAULT_ESTADOS = estados.slice(0, 5).map(e => e.id);
</script>