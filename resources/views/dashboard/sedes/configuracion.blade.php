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
                    @if (Auth::user()->can('sede.Ciudades.v'))
                    <div class="accordion-item card">
                        <h2 class="accordion-header d-flex align-items-center">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-4" aria-expanded="false">
                                <i class="me-2 ti ti-map-2"></i>
                                Ciudades
                            </button>
                        </h2>
                        <div id="accordionWithIcon-4" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="p-4 d-flex align-items-center justify-content-end">
                                    <a href="#" class="add_ciudad btn btn-primary"> <i class="me-2 ti ti-map-plus"></i>Crear ciudad</a>
                                </div>
                                <div class="p-4 content_ciudad_add" style="display:none">
                                    <form class="send_form mb-4" action="{{url('dashboard/sedes/add_ciudad')}}">
                                        <div class="content_form_horarios row ">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label">Nombre de la ciudad <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="nombre_ciudad">
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Latitud <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="latitud_ciudad">
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Longitud <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="longitud_ciudad">
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label">Nivel de zoom <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="number" name="nivel_zoom_ciudad">
                                            </div>
                                            <div class="col-12 text-end my-2">
                                                <button type="submit" class="btn btn-primary">Agregar ciudad</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="p-4 content_ciudad_edit" style="display:none">
                                    <form class="send_form mb-4 col-12" action="{{url('dashboard/sedes/save_ciudad')}}">
                                        <div class="content_form_horarios row ">
                                            <input type="hidden" name="id_ciudad">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label">Nombre de la ciudad <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="nombre_ciudad">
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Latitud <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="latitud_ciudad">
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Longitud <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="longitud_ciudad">
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label">Nivel de zoom <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="number" name="nivel_zoom_ciudad">
                                            </div>
                                            <div class="col-12 text-end my-2">
                                                <button type="submit" class="btn btn-primary">Actualizar ciudad</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <label class="form-label">Ciudades registradas</label>
                                <table class="datatables-ciudades table"></table>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if (Auth::user()->can('sede.Localidades.v'))
                    <div class="accordion-item card">
                        <h2 class="accordion-header d-flex align-items-center">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-5" aria-expanded="false">
                                <i class="ti ti-photo-pin"></i>
                                Localidades
                            </button>
                        </h2>
                        <div id="accordionWithIcon-5" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="p-4 d-flex align-items-center justify-content-end">
                                    <a href="#" class="add_localidad btn btn-primary"> <i class="me-2 ti ti-map-pin-plus"></i>Crear localidad</a>
                                </div>
                                <div class="p-4 content_localidad_add" style="display:none">
                                    <form class="send_form mb-4" action="{{url('dashboard/sedes/add_localidad')}}">
                                        <div class="content_form_horarios row ">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label">Nombre de la localidad <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="nombre_localidad">
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Latitud <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="latitud_localidad">
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Longitud <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="longitud_localidad">
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label">Nivel de zoom <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="number" name="nivel_zoom_localidad">
                                            </div>
                                            <div class="col-12 text-end my-2">
                                                <button type="submit" class="btn btn-primary">Agregar localidad</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="p-4 content_localidad_edit" style="display:none">
                                    <form class="send_form mb-4 col-12" action="{{url('dashboard/sedes/edit_localidad')}}">
                                        <div class="content_form_horarios row ">
                                            <input type="hidden" name="id_localidad">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label">Nombre de la localidad <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="nombre_localidad">
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Latitud <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="latitud_localidad">
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Longitud <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="text" name="longitud_localidad">
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label">Nivel de zoom <span class="required_flied">*</span></label>
                                                <input required class="form-control" type="number" name="nivel_zoom_localidad">
                                            </div>
                                            <div class="col-12 text-end my-2">
                                                <button type="submit" class="btn btn-primary">Actualizar localidad</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <label class="form-label">Localidades registradas</label>
                                <table class="table datatables-localidades"></table>
                            </div>
                        </div>
                    </div>
                    @endif
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