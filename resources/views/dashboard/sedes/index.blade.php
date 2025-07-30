<!-- Layout container -->
<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <h5 class="m-0">Sedes</h5>
                    <a href="{{ url('dashboard/sedes/add') }}" class="btn btn-primary">Agregar sede</a>
                </div>
                <div class="card-datatable text-nowrap">
                    <table class="datatables-sedes table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>EMPRESA</th>
                                <th>NOMBRE</th>
                                <th>CIUDAD</th>
                                <th>ESTADO</th>
                                <th></th>
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
<!-- / Layout page -->