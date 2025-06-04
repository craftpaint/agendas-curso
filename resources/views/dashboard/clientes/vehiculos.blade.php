<!-- Layout container -->
<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <h5 class="m-0">Vehiculos</h5>
                    <a href="{{ url('dashboard/clientes/add_vehiculos') }}" class="btn btn-primary" style="margin-left: auto;margin-right: 10px;">Agregar vehiculo</a>
                    <?php if ($user->can('vehiculo.descargar.v')) { ?>
                        <a href="#" data-action="{{ url('dashboard/clientes/dowload') }}" data-type="cliente" class="btn_descagar btn btn-dark">Descargar</a>
                    <?php } ?>
                </div>
                <div class="card-datatable text-nowrap">
                    <table class="datatables-vehiculos table">
                        <thead>
                            <th>Vehiculo</th>
                            <th>Placa</th>
                            <th>Cliente</th>
                            <th></th>
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