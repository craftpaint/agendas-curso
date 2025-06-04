<!-- Layout container -->
<div class="layout-page content_citas_edit_servicios_liquidador">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <h5 class="card-header">Editar Servicio</h5>
                <div class="card-body">
                    <small class="mb-2">Creación: <strong><?= $servicio->created_at ?></strong></small><br>
                    <small class="mb-2">Actualización: <strong><?= $servicio->updated_at ?></strong></small>
                    <form class="send_form mt-2" action="{{url('dashboard/liquidador/update_servicio_liquidador')}}" method="POST">
                        @csrf
                        <input type="hidden" name="id_servicio_liquidador" value="{{ $servicio->id_servicio_liquidador }}">
                        <div class="content_form_horarios row">
                            <div class="col-3 mx-auto">
                                <label class="form-label">Servicio <span class="required_flied">*</span></label>
                                <input required class="form-control" type="text" name="nombre_servicio_liquidador" value="{{ $servicio->nombre_servicio_liquidador }}">
                            </div>
                            <div class=" col-4 mx-auto">
                                <label class="form-label">Valor a liquidar <span class="required_flied">*</span></label>
                                <input required class="form-control" type="number" name="valor_servicio_liquidador" value="{{ $servicio->valor_servicio_liquidador }}">
                            </div>
                            <div class="col mx-auto">
                                <label class="form-label">Color del servicio <span class="required_flied">*</span></label>
                                <input required class="form-control" type="color" name="color_servicio_liquidador" value="{{ $servicio->color_servicio_liquidador }}">
                            </div>
                            <button type="submit" class="btn btn-primary boton_submit col">Actualizar</button>
                        </div>
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