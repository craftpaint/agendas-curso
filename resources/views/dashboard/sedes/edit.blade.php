<!-- Layout container -->
<div class="layout-page content_sede">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <h5 class="card-header">Editar sede: <?= $sede['nombre_sede'] ?></h5>
                <div class="card-body">
                    <form class="send_form form-repeater" action="{{url('dashboard/sedes/update')}}">
                        <input type="hidden" name="id_sede" value="<?= $sede['id_sede'] ?>">
                        <div class="row">
                            <div class="mb-4 col-md-6">
                                <label class="form-label">ID RUN <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="idrun_sede" required value="<?= $sede['idrun_sede'] ?>">
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Nombre de la sede <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="nombre_sede" required value="<?= $sede['nombre_sede'] ?>">
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Teléfono de la sede <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="tel_sede" required value="<?= $sede['tel_sede'] ?>">
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Estado de la sede <span class="required_flied">*</span></label>
                                <select class="select2 form-select" required name="estado_sede">
                                    <option <?= (($sede['estado_sede'] == 'Activo') ? 'selected' : '') ?> value="Activo">Activo</option>
                                    <option <?= (($sede['estado_sede'] == 'Inactivo') ? 'selected' : '') ?> value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Horario abreviado <span class="required_flied">*</span></label>
                                <input required class="form-control" type="text" name="horario_sede" value="<?= $sede['horario'] ?>">
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Latitud <span class="required_flied">*</span></label>
                                <input required class="form-control" type="text" name="latitud_sede" value="<?= $sede['latitud'] ?>">
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Longitud <span class="required_flied">*</span></label>
                                <input required class="form-control" type="text" name="longitud_sede" value="<?= $sede['longitud'] ?>">
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Ciudad <span class="required_flied">*</span></label>
                                <select class="select2 form-select" required name="id_ciudad">
                                    <option value="">Seleccionar ciudad</option>
                                    <?php
                                    if ($ciudades->isNotEmpty()) {
                                        foreach ($ciudades as $key => $value) {
                                            echo '<option' . (($sede['id_ciudad'] == $value->id_ciudad) ? ' selected' : '') . ' value="' . $value->id_ciudad . '">' . $value->nombre . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php if ($user->can('sede.empresa.v')) { ?>
                                <div class="mb-4 col-md-6">
                                <?php } else { ?>
                                    <div class="mb-4 col-md-12">
                                    <?php } ?>
                                    <label class="form-label">Servicios <span class="required_flied">*</span></label>
                                    <select class="select2 form-select" required name="id_servicio">
                                        <option value="">Seleccionar servicio</option>
                                        <?php
                                        if (is_array($servicios) && count($servicios) > 0) {
                                            foreach ($servicios as $key => $value) {
                                                echo '<option ' . (($sede['id_servicio'] == $value['id_servicio']) ? 'selected' : '') . ' value="' . $value['id_servicio'] . '">' . $value['tipo_servicio'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    </div>
                                    <?php
                                    if ($user->can('sede.empresa.v')) {
                                    ?>
                                        <div class="mb-4 col-md-6">
                                            <label class="form-label">Empresa <span class="required_flied">*</span></label>
                                            <select class="select2 form-select" required name="id_empresa">
                                                <option value="">Seleccionar empresa</option>
                                                <?php
                                                if ($empresas->isNotEmpty()) {
                                                    foreach ($empresas as $key => $value) {
                                                        echo '<option ' . (($sede['id_empresa'] == $value->id_empresa) ? 'selected' : '') . ' value="' . $value->id_empresa . '">' . $value->Nombre . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    <?php } ?>
                                    <div class="mb-4 col-md-12">
                                        <label class="form-label">DIRECCIÓN DE LA SEDE <span class="required_flied">*</span></label>
                                        <textarea class="form-control" rows="3" name="direccion_sede" required><?= $sede['direccion_sede'] ?></textarea>
                                    </div>
                                    <hr class="mt-0" />
                                    <div class="mb-4 col-md-12">
                                        <label class="form-label">Festivos de la sede</label>
                                        <div class="select2-primary">
                                            <select class="select2 form-select" name="festivos_sede[]" multiple>
                                                <option value="">Seleccionar festivos</option>
                                                <?php
                                                if (is_array($festivos) && count($festivos) > 0) {
                                                    $festivos_saved = @unserialize($sede['festivos_sede']);
                                                    foreach ($festivos as $key => $value) {
                                                        echo '<option ' . ((is_array($festivos_saved) && in_array($value['fecha'], $festivos_saved)) ? 'selected' : '') . ' value="' . $value['fecha'] . '">' . $value['fecha'] . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-4 col-md-12">
                                        <div class="accordion col-12 p-0" id="accordionWithIcon">
                                            <?php
                                            for ($i = 1; $i < 8; $i++) {
                                                $title = 'Lunes';
                                                if ($i == 2) {
                                                    $title = 'Martes';
                                                } else if ($i == 3) {
                                                    $title = 'Miercoles';
                                                } else if ($i == 4) {
                                                    $title = 'Jueves';
                                                } else if ($i == 5) {
                                                    $title = 'Viernes';
                                                } else if ($i == 6) {
                                                    $title = 'Sabado';
                                                } else if ($i == 7) {
                                                    $title = 'Domingo';
                                                }
                                            ?>
                                                <div class="card accordion-item">
                                                    <h1 class="accordion-header d-flex align-items-center">
                                                        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-<?= $i ?>" aria-expanded="false"><?= $title ?></button>
                                                        <?php
                                                        if ($i === 1) {
                                                        ?>
                                                            <div class="contet_full_semana_repeat">
                                                                <input type="checkbox" id="semanaFull" name="semanaFull" value="1">
                                                                <label class="form-label" for="semanaFull">Repetir de Lunes a Viernes</label>
                                                            </div>
                                                        <?php
                                                        }
                                                        ?>
                                                    </h1>
                                                    <div id="accordionWithIcon-<?= $i ?>" class="repeat_<?= $i ?> accordion-collapse collapse" style="">
                                                        <div class="accordion-body">
                                                            <div data-repeater-list="horario_<?= $i ?>">
                                                                <?php
                                                                if (is_array($sede_horarios) && !empty($sede_horarios)) {
                                                                    // Obtener los horarios en un arreglo asociativo
                                                                    $horariosMap = [];
                                                                    if (is_array($horarios) && count($horarios) > 0) {
                                                                        foreach ($horarios as $h) {
                                                                            $horariosMap[$h['id_horario']] = $h['inicio_horario'];
                                                                        }
                                                                    }

                                                                    // Ordenar $sede_horarios por 'inicio_horario'
                                                                    usort($sede_horarios, function ($a, $b) use ($horariosMap) {
                                                                        return strtotime($horariosMap[$a['id_horario']]) - strtotime($horariosMap[$b['id_horario']]);
                                                                    });
                                                                    $a_dias_save = [];
                                                                    foreach ($sede_horarios as $key => $horario_item) {
                                                                        $a_dias_save[] = $horario_item['dia_sede_horario'];
                                                                    }
                                                                    $a_dias_save = array_unique($a_dias_save);
                                                                    foreach ($sede_horarios as $key => $horario_item) {
                                                                        if ($horario_item['dia_sede_horario'] == $i) {
                                                                ?>
                                                                            <div data-repeater-item>
                                                                                <div class="content_form_horarios mb-4">
                                                                                    <div class="content_form_horarios_item">
                                                                                        <label class="form-label">Rango de horario</label>
                                                                                        <select class="form-select" name="id">
                                                                                            <option value="">Seleccionar rangos</option>
                                                                                            <?php
                                                                                            if (is_array($horarios) && count($horarios) > 0) {
                                                                                                foreach ($horarios as $key => $value) {
                                                                                                    echo '<option ' . (($horario_item['id_horario'] == $value['id_horario']) ? 'selected' : '') . ' value="' . $value['id_horario'] . '">' . $value['rango_horario'] . '</option>';
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="content_form_horarios_item">
                                                                                        <label class="form-label">Cupos disponibles</label>
                                                                                        <input class="form-control" type="number" name="cupos" value="<?= $horario_item['cupo_sede_horario'] ?>">
                                                                                    </div>
                                                                                    <button type="button" class="btn btn-label-danger" data-repeater-delete>
                                                                                        <i class="ti ti-x ti-xs me-1"></i>
                                                                                        <span class="align-middle">Borrar</span>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        <?php
                                                                        } elseif (!in_array($i, $a_dias_save)) {
                                                                            array_push($a_dias_save, $i);
                                                                        ?>
                                                                            <div data-repeater-item>
                                                                                <div class="content_form_horarios mb-4">
                                                                                    <div class="content_form_horarios_item">
                                                                                        <label class="form-label">Rango de horario</label>
                                                                                        <select class="form-select" name="id">
                                                                                            <option value="">Seleccionar rangos</option>
                                                                                            <?php
                                                                                            if (is_array($horarios) && count($horarios) > 0) {
                                                                                                foreach ($horarios as $key => $value) {
                                                                                                    echo '<option value="' . $value['id_horario'] . '">' . $value['rango_horario'] . '</option>';
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="content_form_horarios_item">
                                                                                        <label class="form-label">Cupos disponibles</label>
                                                                                        <input class="form-control" type="number" name="cupos">
                                                                                    </div>
                                                                                    <button type="button" class="btn btn-label-danger" data-repeater-delete>
                                                                                        <i class="ti ti-x ti-xs me-1"></i>
                                                                                        <span class="align-middle">Borrar</span>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                <?php
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                            <button type="button" class="btn btn-dark" data-repeater-create>Agregar más horarios</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary boton_submit">Actualizar</button>
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
<script>
    a_dias = ['1', '2', '3', '4', '5', '6', '7'];
</script>