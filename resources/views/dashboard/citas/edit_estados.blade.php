<!-- Layout container -->
<div class="layout-page content_citas_edit_estados" style="padding-top:0 !important">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <h5 class="card-header">Editar Estado</h5>
                <div class="card-body">
                    <small class="mb-2">Creación: <strong><?= $estado->created_at ?></strong></small><br>
                    <small class="mb-2">Actualización: <strong><?= $estado->updated_at ?></strong></small>
                    <form class="send_form mt-2" action="{{url('dashboard/citas/update_estados')}}" method="POST">
                        @csrf
                        <input type="hidden" name="id_estado" value="{{ $estado->id_estado }}">
                        <div class="content_form_horarios row">
                            <div class="col-3 mx-auto">
                                <label class="form-label">Estado <span class="required_flied">*</span></label>
                                <input required class="form-control" type="text" name="nombre_estado" value="{{ $estado->nombre_estado }}">
                            </div>
                            <div class=" col-4 mx-auto">
                                <label class="form-label">Descripción del servicio <span class="required_flied">*</span></label>
                                <input required class="form-control" type="text" name="desc_estado" value="{{ $estado->desc_estado }}">
                            </div>
                            <div class="col mx-auto">
                                <label class="form-label">Color del estado <span class="required_flied">*</span></label>
                                <input required class="form-control" type="color" name="color_estado" value="{{ $estado->color_estado }}">
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
