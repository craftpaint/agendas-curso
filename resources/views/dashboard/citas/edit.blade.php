<!-- Layout container -->
<div class="layout-page content_citas content_citas_edit">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <form class="send_form" action="{{url('dashboard/citas/update')}}">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
                <div class="card">
                    <h5 class="card-header">Editar cita</h5>
                    <div class="card-body row">
                        <div class="col-12 col-md-6">
                            <small class="mb-2">Creación: <strong><?= $cita->fecha_create ?></strong></small><br>
                            <small class="mb-2">Actualización: <strong><?= $cita->fecha_update ?></strong></small><br>
                            <?= isset($cita->origen) ? '<small class="mb-2">Origen: <strong>' . $cita->origen . '</strong></small><br>' : '' ?>
                            <?= isset($cita->creado_por) ? '<small class="mb-2">Creado por: <strong>' . $cita->creado_por . '</strong></small><br>' : '' ?>
                        </div>
                        <?php if ($user->can('cita.Agente Call Center.e')) { ?>
                            <div class="mb-4 col-12 col-md-6">
                                <label class="form-label">Agente Callcenter asginado: <span class="required_flied">*</span></label>
                                <select class="select_search_agente_callcenter select2 form-select" name="id_agente_callcenter" required>
                                    <?php
                                    if ($agentes_callcenter->count() > 0) {
                                        foreach ($agentes_callcenter as $agente) {
                                            echo '<option ' . (($agente->id == $cita->id_agente_callcenter) ? 'selected' : '') . ' value="' . $agente->id . '">' . $agente->name . '</option>';
                                        }
                                    } else if (isset($cita->id_agente_callcenter) && $cita->id_agente_callcenter != '' && $agentes_callcenter) {
                                        // echo '<option value="' . $cita->id_agente_callcenter . '">' . $cita->tipo_doc_cliente . $cita->doc_cliente . ': ' . $cita->nombre_cliente . ' ' . $cita->apellido_cliente . '</option>';
                                        echo '<option value="' . $agentes_callcenter->first()->id . '">' . $agentes_callcenter->first()->name . '</option>';
                                    } else {
                                        echo '<option value="" selected disabled>Seleccione un un agente</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="id_agente_callcenter" value="{{$cita->id_agente_callcenter}}">
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="container-xxl flex-grow-1 container-p-y">
                <!-- Ajax Sourced Server-side -->
                <div class="card">
                    <div class="card-body">
                        <input type="hidden" name="id_cita" value="{{$cita->id_cita}}">
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
                                            echo '<option ' . (($cita->id_sede == $sede->id_sede) ? 'selected' : '') . ' data-festivos=' . json_encode($a_festivos) . ' value="' . $sede->id_sede . '">' . $sede->nombre_sede . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Estado de la cita <span class="required_flied">*</span></label>
                                <select id="selectEstado" class="form-select" required name="id_estado">
                                    <option value="">Seleccionar estado</option>
                                    <?php
                                    if ($estados->isNotEmpty()) {
                                        foreach ($estados as $key => $estado) {
                                            echo '<option ' . (($cita->id_estado == $estado->id_estado) ? 'selected' : '') . ' value="' . $estado->id_estado . '">' . $estado->nombre_estado . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Estado verificado de la cita <span class="required_flied">*</span></label>
                                <select id="selectEstadoVerificado" class="form-select" required name="id_estado_verificado">
                                    <option value="">Seleccionar estado</option>
                                    <?php
                                    if ($estados->isNotEmpty()) {
                                        foreach ($estados as $key => $estado) {
                                            echo '<option ' . (($cita->id_estado_verificado == $estado->id_estado) ? 'selected' : '') . ' value="' . $estado->id_estado . '">' . $estado->nombre_estado . '</option>';
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
                            <input type="hidden" name="id_servicio_liquidador" value="2">
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Servicio Liquidador<span class="required_flied">*</span></label>
                                <select id="SelectServicioLiquidador" class="form-select" required name="id_servicio_liquidador">
                                    <option value="">Seleccionar servicio</option>
                                    <?php
                                    if ($servicios_liquidador->isNotEmpty()) {
                                        foreach ($servicios_liquidador as $key => $servicio) {
                                            echo '<option ' . (($cita->id_servicio_liquidador == $servicio->id_servicio_liquidador) ? 'selected' : '') . ' value="' . $servicio->id_servicio_liquidador . '">' . $servicio->nombre_servicio_liquidador . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-4 col-12 col-md-6">
                                <label class="form-label">Codigo de Comparendo</label>
                                <script>
                                    var codigo_comparendo_tagify;
                                </script>
                                <input id="codigo_comparendo_tagify" name="codigo_comparendo" class="form-control" placeholder="Codigo de comparendo" value='<?= $cita->codigos_comparendo ?>' />
                                <!-- <script>
                                    codigo_comparendo_tagify.addTags(<?= $cita->codigos_comparendo ?>);
                                </script> -->
                            </div>
                            <div class="mb-4 col-md-12">
                                <label class="form-label">Cliente <span class="required_flied">*</span></label>
                                <select class="select_search_cliente" name="id_cliente" required>
                                    <?php
                                    if (isset($cita->id_cliente) && $cita->id_cliente != '') {
                                        echo '<option value="' . $cita->id_cliente . '">' . $cita->tipo_doc_cliente . $cita->doc_cliente . ': ' . $cita->nombre_cliente . ' ' . $cita->apellido_cliente . '</option>';
                                    } else {
                                        echo '<option value="">Seleccione un cliente</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-4 col-md-12" id="divSelectVehiculo" style="display: none;">
                                <label class="form-label">Vehículo</label>
                                <select id="selectVehiculo" class="select2 form-select" name="id_vehiculo">
                                    <option value="">Seleccionar vehículo</option>
                                    <?php
                                    if ($vehiculos->isNotEmpty()) {
                                        foreach ($vehiculos as $key => $vehiculo) {
                                            echo '<option ' . (($cita->id_vehiculo == $vehiculo->id_vehiculo) ? 'selected' : '') . ' value="' . $vehiculo->id_vehiculo . '">' . $vehiculo->placa_vehiculo . ' - ' . $vehiculo->tipo_vehiculo . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-4 col-md-12">
                                <label class="form-label">Descripción de la cita <span class="required_flied">*</span></label>
                                <textarea class="form-control" rows="3" name="desc_cita" required><?= $cita->desc_cita ?></textarea>
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Id Whatsapp Sendpulse</label>
                                <input type="text" class="form-control" name="id_whatsapp_sendpulse" placeholder="Id de whatsapp en sendpulse" autocomplete="off" value="<?= $cita->id_whatsapp_sendpulse ?>" <?= $user->can('cita.Sendpulse.e') ? '' : 'readonly' ?>>
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Id Trato Sendpulse</label>
                                <input type="text" class="form-control" name="id_trato_sendpulse" placeholder="Id del trato en sendpulse" autocomplete="off" value="<?= $cita->id_trato_sendpulse ?>" <?= $user->can('cita.Sendpulse.e') ? '' : 'readonly' ?>>
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
                        </div>
                        <button type="submit" class="btn btn-primary boton_submit">Actualizar</button>
                    </div>
                </div>
        </form>
        <!--/ Ajax Sourced Server-side -->
    </div>
    <!-- / Content -->
</div>
<!-- Content wrapper -->
</div>
<!-- / Layout page -->
<script>
    const reserva_cita = '<?= $cita->reserva_cita ?>';
    const rango_horario = '<?= $cita->rango_horario ?>';
    const idVehiculoCita = '<?= $cita->id_vehiculo ?>';
</script>