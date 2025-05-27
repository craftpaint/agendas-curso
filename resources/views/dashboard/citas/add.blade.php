<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

<!-- Layout container -->
<div class="layout-page content_citas" style="padding-top:0 !important">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <h5 class="card-header">Agregar cita</h5>
                <div class="card-body">
                    <form class="send_form" action="{{url('dashboard/citas/save')}}">
                        @csrf
                        <div class="row">
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Sede <span class="required_flied">*</span></label>
                                <select id="selectSede" class="select2 form-select" required name="id_sede">
                                    <option value="">Seleccionar sede</option>
                                    <?php
                                    if ($sedes->isNotEmpty()) {
                                        foreach ($sedes as $key => $sede) {
                                            $a_festivos = @unserialize($sede->festivos_sede);
                                            $a_festivos = $a_festivos !== false ? $a_festivos : array();
                                            echo '<option data-festivos=' . json_encode($a_festivos) . ' value="' . $sede->id_sede . '">' . $sede->nombre_sede . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Día <span class="required_flied">*</span></label>
                                <input name="reserva_cita" type="text" id="citaDia" placeholder="DD/MM/YYYY" class="form-control" readonly disabled />
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Horario <span class="required_flied">*</span></label>
                                <select id="citaHora" class="select2 form-select" required name="id_sede_horario" disabled>
                                    <option value="">Seleccionar horario</option>
                                </select>
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Estado de la cita <span class="required_flied">*</span></label>
                                <select id="selectEstado" class="form-select" required name="id_estado">
                                    <option value="">Seleccionar estado</option>
                                    <?php
                                    if ($sedes->isNotEmpty()) {
                                        foreach ($estados as $key => $estado) {
                                            echo '<option value="' . $estado->id_estado . '">' . $estado->nombre_estado . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php if ($user->can('cita.Estado Verificado.e')) { ?>
                                <div class="mb-4 col-md-6">
                                    <label class="form-label">Estado verificado de la cita <span class="required_flied">*</span></label>
                                    <select id="selectEstadoVerificado" class="form-select" required name="id_estado_verificado">
                                        <option value="">Seleccionar estado</option>
                                        <?php
                                        if ($sedes->isNotEmpty()) {
                                            foreach ($estados as $key => $estado) {
                                                echo '<option value="' . $estado->id_estado . '">' . $estado->nombre_estado . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php } else { ?>
                                <input type="hidden" name="id_estado_verificado" value="1">
                            <?php } ?>
                            <input type="hidden" name="id_agente_callcenter" value="2">
                            <?php if ($user->can('cita.Servicio Liquidador.e')) { ?>
                                <div class="mb-4 col-md-6">
                                    <label class="form-label">Servicio Liquidador<span class="required_flied">*</span></label>
                                    <select id="selectSede" class="select2 form-select" required name="id_servicio_liquidador">
                                        <option value="">Seleccionar servicio</option>
                                        <?php
                                        if ($sedes->isNotEmpty()) {
                                            foreach ($servicios_liquidador as $key => $servicio) {
                                                echo '<option value="' . $servicio->id_servicio_liquidador . '">' . $servicio->nombre_servicio_liquidador . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php } else { ?>
                                <input type="hidden" name="id_servicio_liquidador" value="5">
                            <?php } ?>
                            <div class="mb-4 col-12 col-md-6" style="color: blue;">
                                <label class="form-label">Codigo de Comparendo</label>
                                <input id="codigo_comparendo_tagify" name="codigo_comparendo" class="form-control" placeholder="Escribe tu codigo de comparendo si lo conoces" autocomplete="off">
                            </div>
                            <label class="form-label col-12">Cliente <span class="required_flied">*</span></label>
                            <div class="mb-4 col-md-12 input-group">
                                <div class="col-10">
                                    <select id="selectCliente" class="select_search_cliente" name="id_cliente" required>
                                        <option value="">Seleccione un cliente</option>

                                    </select>
                                </div>
                                <button type="button"
                                    class="col-2 btn btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalNuevoCliente">
                                    + Nuevo cliente
                                </button>
                            </div>
                            <div class="mb-4 col-md-12" id="divSelectVehiculo" style="display: none;">
                                <label class="form-label">Vehículo</label>
                                <select id="selectVehiculo" class="select2 form-select" name="id_vehiculo">
                                    <option value="">Seleccionar vehículo</option>
                                </select>
                            </div>
                            <div class="mb-4 col-md-12">
                                <label class="form-label">Descripción de la cita <span class="required_flied">*</span></label>
                                <textarea class="form-control" rows="3" name="desc_cita" required></textarea>
                            </div>
                        </div>
                        <hr>
                        <h5>Anotaciones</h5>
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="form-label">Título del seguimiento</label>
                                <input type="text" class="form-control" name="titulo_seguimiento">
                                <label class="form-label">Nota del seguimiento</label>
                                <textarea class="form-control" rows="3" name="nota_seguimiento"></textarea>
                            </div>
                            <div class="col-6">
                                <ul class="timeline mb-0">
                                    <?php
                                    if (!empty($anotaciones)) {
                                        foreach ($anotaciones as $anotacion) {
                                    ?>
                                            <li class="timeline-item timeline-item-transparent">
                                                <span class="timeline-point timeline-point-success"></span>
                                                <div class="timeline-event">
                                                    <div class="timeline-header mb-2">
                                                        <h6 class="mb-0"><?= $anotacion->titulo_seguimiento ?></h6>
                                                        <small class="text-muted"><?= $anotacion->created_at ?></small>
                                                    </div>
                                                    <p class="m-0"><?= $anotacion->nota_seguimiento ?></p>
                                                    <p class="m-0">Autor: <strong><?= $anotacion->nombre_user ?></strong></p>
                                                </div>
                                            </li>
                                    <?php
                                        }
                                    }
                                    ?>
                                </ul>
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


    <!-- Modal “Nuevo Cliente” -->
    <div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{url('dashboard/clientes/save')}}" id="formNuevoCliente">
                <div class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Crear cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            <div class="mb-4 col-6">
                                <label class="form-label">Nombre <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="nombre_cliente" required>
                            </div>
                            <div class="mb-4 col-6">
                                <label class="form-label">Apellido <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="apellido_cliente" required>
                            </div>
                            <div class="mb-4 col-6">
                                <label class="form-label">Correo <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="email_cliente" required>
                            </div>
                            <div class="mb-4 col-6">
                                <label class="form-label">Tipo de documento <span class="required_flied">*</span></label>
                                <select class="select2 form-select" required name="tipo_doc_cliente">
                                    <option value="CC">Cedula de ciudadania</option>
                                    <option value="TI">Tarjeta de Identidad</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                    <option value="PEP">PEP</option>
                                    <option value="PPT">PPT</option>
                                </select>
                            </div>
                            <div class="mb-4 col-6">
                                <label class="form-label">Número de documento <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="doc_cliente" required>
                            </div>
                            <div class="mb-4 col-6">
                                <label class="form-label">Teléfono <span class="required_flied">*</span></label><br>
                                <input type="tel" class="form-control" id="phoneCliente" name="telefono_cliente" required>
                            </div>
                            <div class="mb-4 col-12">
                                <label class="form-label">Descripción <span class="required_flied">*</span></label>
                                <textarea class="form-control" rows="3" name="desc_cliente" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary boton_submit">Agregar</button>
                    </div>
                    <script>
                        const phoneInputField = document.querySelector("#phoneCliente");
                        const phoneInput = window.intlTelInput(phoneInputField, {
                            initialCountry: "co",
                            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
                        });
                    </script>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- / Layout page -->