<!-- Layout container -->
<div class="layout-page" style="padding-top:0 !important">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="accordion col-12 p-0" id="accordionWithIcon">
                    <div class="card accordion-item">
                        <h1 class="accordion-header d-flex align-items-center">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-1" aria-expanded="false">
                                <i class="ti ti-clock-hour-5 me-2"></i>
                                Horarios de atención de las sedes
                            </button>
                        </h1>
                        <div id="accordionWithIcon-1" class="accordion-collapse collapse" style="">
                            <div class="accordion-body">
                                <form class="send_form mb-4" action="{{url('dashboard/sedes/add_horarios')}}">
                                    <div class="content_form_horarios">
                                        <div class="content_form_horarios_item">
                                            <label class="form-label">Horario de inicio <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="time" name="inicio_horario">
                                        </div>
                                        <div class="content_form_horarios_item">
                                            <label class="form-label">Horario de finalización <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="time" name="fin_horario">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Agregar</button>
                                    </div>
                                </form>
                                <label class="form-label">Horarios registrados</label>
                                <table class="datatables-horarios table">
																	<thead>
																		<tr>
																				<th>Rango horario</th>
																				<th>opt</th>
																		</tr>
																	</thead>
																</table>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item card">
                        <h2 class="accordion-header d-flex align-items-center">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-2" aria-expanded="false">
                                <i class="me-2 ti ti-calendar"></i>
                                Días festivos de las sedes
                            </button>
                        </h2>
                        <div id="accordionWithIcon-2" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <form class="send_form mb-4" action="{{url('dashboard/sedes/add_festivos')}}">
                                    <div class="content_form_horarios">
                                        <div class="content_form_horarios_item_full">
                                            <label class="form-label">Festivo <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="date" name="festivo">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Agregar</button>
                                    </div>
                                </form>
                                <label class="form-label">Festivos registrados</label>
                                <table class="datatables-festivos table"></table>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item card">
                        <h2 class="accordion-header d-flex align-items-center">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-3" aria-expanded="false">
                                <i class="me-2 ti ti-briefcase"></i>
                                Servicios de las sedes
                            </button>
                        </h2>
                        <div id="accordionWithIcon-3" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <form class="send_form mb-4" action="{{url('dashboard/sedes/add_servicio')}}">
                                    <div class="content_form_horarios">
                                        <div class="content_form_horarios_item">
                                            <label class="form-label">Tipo del servicio <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="text" name="tipo_servicio">
                                        </div>
                                        <div class="content_form_horarios_item">
                                            <label class="form-label">Descripción del servicio <span class="required_flied">*</span></label>
                                            <input required class="form-control" type="text" name="desc_servicio">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Agregar</button>
                                    </div>
                                </form>
                                <label class="form-label">Servicios registrados</label>
                                <table class="datatables-servicios table"></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
