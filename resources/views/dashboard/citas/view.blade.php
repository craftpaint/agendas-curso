<!-- Layout container -->
<div class="layout-page content_citas content_citas_edit">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <h5 class="card-header">Editar cita</h5>
                <div class="card-body">
                    <small class="mb-2">Creación: <strong><?= $cita->fecha_create ?></strong></small><br>
                    <small class="mb-2">Actualización: <strong><?= $cita->fecha_update ?></strong></small><br>
                    <small class="mb-2">Creado por: <strong><?= $cita->creado_por ?></strong></small><br>
                    <small class="mb-2">Origen: <strong><?= $cita->origen ?></strong></small>
                    <div class="send_form mt-2">
                        <input type="hidden" name="id_cita" value="{{$cita->id_cita}}">
                        <div class="row">
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Sede <span class="required_flied">*</span></label>
                                <select disabled id="selectSede" class="select2 form-select" required name="id_sede">
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
                                <select disabled id="selectSede" class="form-select" required name="id_estado">
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
                                <select disabled id="selectSede" class="form-select" required name="id_estado_verificado">
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
                                <input readonly name="reserva_cita" type="text" id="citaDia" placeholder="DD/MM/YYYY" class="form-control" readonly disabled />
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Horario <span class="required_flied">*</span></label>
                                <select id="citaHora" class="select2 form-select" required name="id_sede_horario" disabled>
                                    <option value="">Seleccionar horario</option>
                                </select>
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Servicio Liquidador<span class="required_flied">*</span></label>
                                <select id="SelectServicioLiquidador" class="form-select" required name="id_servicio_liquidador" disabled>
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
                                <input id="codigo_comparendo_tagify" name="codigo_comparendo" class="form-control" placeholder="Codigo de comparendo" disabled value='<?= $cita->codigos_comparendo ?>' />
                            </div>
                            <div class="mb-4 col-md-12">
                                <label class="form-label">Cliente <span class="required_flied">*</span></label>
                                <select disabled class="select_search_cliente" name="id_cliente" required>
                                    <?php
                                    if (isset($cita->id_cliente) && $cita->id_cliente != '') {
                                        echo '<option value="' . $cita->id_cliente . '">' . $cita->tipo_doc_cliente . $cita->doc_cliente . ': ' . $cita->nombre_cliente . ' ' . $cita->apellido_cliente . '</option>';
                                    } else {
                                        echo '<option value="">Seleccione un cliente</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-4 col-md-12">
                                <label class="form-label">Descripción de la cita <span class="required_flied">*</span></label>
                                <textarea readonly class="form-control" rows="3" name="desc_cita" required disabled><?= $cita->desc_cita ?></textarea>
                            </div>
                            <hr>
                            <h5>Anotaciones</h5>
                            <div class="row mb-4">
                                <div class="col-12">
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
                    </div>
                </div>
            </div>
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
</script>