<!-- Layout container -->
<div class="layout-page content_vehiculos" style="padding-top:0 !important">

    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <h5 class="card-header">Agregar vehiculo</h5>
                <div class="card-body">
                    <form class="send_form" action="{{url('dashboard/clientes/save_vehiculo')}}">
                        <div class="row">
                            <div class="mb-4 col-md-12">
                                <label class="form-label">Cliente <span class="required_flied">*</span></label>
                                <select class="select_search_cliente" name="id_cliente" required>
                                    <option value="">Seleccione un cliente</option>
                                </select>
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Tipo de vehiculo <span class="required_flied">*</span></label>
                                <select class="select2 form-select" required name="tipo_vehiculo">
                                    <option value="Motocicleta">Motocicleta</option>
                                    <option value="Automóvil">Automóvil</option>
                                    <option value="Publico">Publico</option>
                                </select>
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Placa de vehiculo <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="placa_vehiculo" required>
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Modelo de vehiculo <span class="required_flied">*</span></label>
                                <input type="number" class="form-control" name="modelo_vehiculo" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary boton_submit">Agregar</button>
                    </form>
                </div>
            </div>
            <!--/ Ajax Sourced Server-side -->
        </div>
        <!-- / Content -->
    </div>
    <!-- Content wrapper -->
</div>
<!-- / Layout page -->