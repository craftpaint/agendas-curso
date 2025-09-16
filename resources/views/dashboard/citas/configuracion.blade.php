<!-- Layout container -->
<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="accordion col-12 p-0" id="accordionWithIcon">
                    <div class="card accordion-item">
                        <h1 class="accordion-header d-flex align-items-center">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-1" aria-expanded="true">
                                <i class="ti ti-tags me-2"></i>
                                Estados de las citas
                            </button>
                        </h1>
                        <div id="accordionWithIcon-1" class="accordion-collapse collapse" style="">
                            <div class="accordion-body">
                                <form class="send_form mb-4" action="{{url('dashboard/citas/add_estados')}}">
                                    <div class="content_form_horarios row">
                                        <div class="col-3 mx-auto">
                                            <label class="form-label">Estado <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="text" name="nombre_estado">
                                        </div>
                                        <div class=" col-3 mx-auto">
                                            <label class="form-label">Descripción del servicio <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="text" name="desc_estado">
                                        </div>
                                        <div class="col-3 mx-auto">
                                            <label class="form-label">Id Step Sendpulse <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="text" name="id_step_sendpulse">
                                        </div>
                                        <div class="col mx-auto">
                                            <label class="form-label">Color del estado <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="color" name="color_estado">
                                        </div>
                                        <button type="submit" class="btn btn-primary col mx-auto">Agregar</button>
                                    </div>
                                </form>
                                <label class="form-label">Estados registrados</label>
                                <table class="datatables-estados table"></table>
                            </div>
                        </div>
                    </div>
                    <div class="card accordion-item">
                        <h1 class="accordion-header d-flex align-items-center">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-2" aria-expanded="false">
                                <i class="ti ti-cards me-2"></i>
                                Servicios Liquidador
                            </button>
                        </h1>
                        <div id="accordionWithIcon-2" class="accordion-collapse collapse" style="">
                            <div class="accordion-body">
                                <form class="send_form mb-4" action="{{url('dashboard/liquidador/add_servicio_liquidador')}}">
                                    <div class="content_form_horarios row">
                                        <div class="col-3 mx-auto">
                                            <label class="form-label">Servicio <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="text" name="nombre_servicio_liquidador">
                                        </div>
                                        <div class=" col-4 mx-auto">
                                            <label class="form-label">Valor a liquidar <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="number" name="valor_servicio_liquidador">
                                        </div>
                                        <div class="col mx-auto">
                                            <label class="form-label">Color del servicio <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="color" name="color_servicio_liquidador">
                                        </div>
                                        <button type="submit" class="btn btn-primary col mx-auto">Agregar</button>
                                    </div>
                                </form>
                                <label class="form-label">Servicios registrados</label>
                                <table class="datatables-servicios-liquidador table"></table>
                            </div>
                        </div>
                    </div>
                    <?php if ($user->can('cita.Metodo scraping.e')) { ?>
                    <div class="card accordion-item">
                        <h1 class="accordion-header d-flex align-items-center">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-3" aria-expanded="false">
                                <i class="ti ti-filter-code me-2"></i>
                                Web Scraping
                            </button>
                        </h1>
                        <div id="accordionWithIcon-3" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <form>
                                    <div class="col-3 mb-4" id="contenedor-metodo-scraping">
                                        <label class="form-label" style="margin-right: 8px;">Método: </label>
                                        <select id="select-metodo-scraping" class="select2 form-select" name="select_metodo_select">
                                            <option value="0">Desactivado</option>
                                            <option value="1">Node.js</option>
                                            <option value="2">N8N</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- / Content  -->
    </div>
    <!-- Content wrapper -->
</div>
<!-- / Layout page -->
<style>
    thead {
        display: none;
    }
</style>