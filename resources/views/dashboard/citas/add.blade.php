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
                        <div class="row">
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Sede <span class="required_flied">*</span></label>
                                <select id="selectSede" class="select2 form-select" required name="id_sede">
                                    <option value="">Seleccionar sede</option>
                                    <?php
                                    if (is_array($sedes) && !empty($sedes)) {
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
                                <label class="form-label">Estado de la cita <span class="required_flied">*</span></label>
                                <select id="selectSede" class="form-select" required name="id_estado">
                                    <option value="">Seleccionar estado</option>
                                    <?php
                                    if (is_array($estados) && !empty($estados)) {
                                        foreach ($estados as $key => $estado) {
                                            echo '<option value="' . $estado->id_estado . '">' . $estado->nombre_estado . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'callcenter' || $rol == 'liquidador' || $rol == 'lidercallcenter') { ?>
                                <div class="mb-4 col-md-6">
                                    <label class="form-label">Estado verificado de la cita <span class="required_flied">*</span></label>
                                    <select id="selectSede" class="form-select" required name="id_estado_verificado">
                                        <option value="">Seleccionar estado</option>
                                        <?php
                                        if (is_array($estados) && !empty($estados)) {
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
                            <?php if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'callcenter' || $rol == 'liquidador' || $rol == 'lidercallcenter') { ?>
                                <div class="mb-4 col-md-6">
                                    <label class="form-label">Servicio Liquidador<span class="required_flied">*</span></label>
                                    <select id="selectSede" class="form-select" required name="id_servicio_liquidador">
                                        <option value="">Seleccionar servicio</option>
                                        <?php
                                        if (is_array($servicios_liquidador) && !empty($servicios_liquidador)) {
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
                            <div class="mb-4 col-md-12">
                                <label class="form-label">Cliente <span class="required_flied">*</span></label>
                                <select class="select_search_cliente" name="id_cliente" required>
                                    <option value="">Seleccione un cliente</option>
                                </select>
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