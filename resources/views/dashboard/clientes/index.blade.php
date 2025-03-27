<!-- Layout container -->
<div class="layout-page" style="padding-top:0 !important">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <h5 class="m-0">Clientes</h5>
                    <a href="{{ url('dashboard/clientes/add') }}" class="btn btn-primary" style="margin-left: auto;margin-right: 10px;">Agregar cliente</a>
                    <a href="#" data-action="{{ url('dashboard/clientes/dowload') }}" data-type="cliente" class="btn_descagar btn btn-dark">Descargar</a>
                </div>
                <div class="card-datatable text-nowrap">
                    <table class="datatables-clientes table"></table>
                </div>
            </div>
            <!--/ Ajax Sourced Server-side -->
        </div>
        <!-- / Content -->
    </div>
    <!-- Content wrapper -->
</div>
<!-- / Layout page -->
<style>
    thead {
        display: none;
    }
</style>