<!-- Layout container -->
<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <h5 class="m-0">Usuarios</h5>
                    <a href="#" class="add_user btn btn-primary">Crear usuario</a>
                </div>
                <div class="p-4 content_users_add" style="display:none">
                    <form class="send_form mb-4 col-12" action="{{url('dashboard/usuarios/save')}}">
                        <div class="row">
                            <div class="col-12 mb-4">
                                <label class="form-label">Sede <span class="required_flied">*</span></label>
                                <select class="select2 form-select" required name="id_sede">
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
                            <div class="col-6 mb-4">
                                <label class="form-label">Nombre <span class="required_flied">*</span></label>
                                <input required class="form-control" type="text" name="name">
                            </div>
                            <div class="col-6 mb-4">
                                <label class="form-label">Correo electrónico <span class="required_flied">*</span></label>
                                <input required class="form-control" type="email" name="email">
                            </div>
                            <div class="col-6 mb-4">
                                <label class="form-label">Contraseña <span class="required_flied">*</span></label>
                                <input required class="form-control" type="text" name="password">
                            </div>
                            <div class="col-6 mb-4" id="contenedor-rol-user">
                                <label class="form-label">Rol <span class="required_flied">*</span></label>
                                <select class="form-select" id="rol-user" required name="role">
                                    <option value="">Seleccionar rol</option>
                                    <?php if ($roles->isNotEmpty()) { ?>
                                        <?php foreach ($roles as $role) {
                                            if ($role->name == 'superadmin' || $role->name == 'admin') {
                                                if ($user->can('permissions.administrar.e')) { ?>
                                                    <option value="<?= $role->name ?>"><?= $role->name ?></option>
                                                <?php }
                                            } else { ?>
                                                <option value="<?= $role->name ?>"><?= $role->name ?></option>

                                    <?php }
                                        }
                                    } ?>
                                </select>
                            </div>
                            <div class="col-3 mb-4" id="contenedor-habilitar-call" style="display:none">
                                <label class="form-label">Habilitar Call Center </label><br>
                                <label class="woow-switch">
                                    <input type="checkbox" name="callcenter_habilitado">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="col-6 mb-4" id="contenedor-id-usuario-sendpulse">
                                <label class="form-label>">ID Usuario SendPulse </label>
                                <input class="form-control" type="text" name="id_user_sendpulse">
                            </div>
                            <div class="col-6 mb-4" id="contenedor-id-chatbot-sendpulse">
                                <label class="form-label">ID Chatbot SendPulse </label>
                                <input class="form-control" type="text" name="id_chatbot_sendpulse">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Guardar usuario</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="p-4 content_users_edit" style="display:none">
                    <form class="send_form mb-4 col-12" action="{{url('dashboard/usuarios/update')}}">
                        <input type="hidden" name="id_user">
                        <div class="row">
                            <div class="col-12 mb-4">
                                <label class="form-label">Sede <span class="required_flied">*</span></label>
                                <select class="select2 form-select" required name="id_sede">
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
                            <div class="col-6 mb-4">
                                <label class="form-label">Nombre <span class="required_flied">*</span></label>
                                <input required class="form-control" type="text" name="name">
                            </div>
                            <div class="col-6 mb-4">
                                <label class="form-label">Correo electrónico <span class="required_flied">*</span></label>
                                <input required class="form-control" type="email" name="email">
                            </div>
                            <div class="col-6 mb-4">
                                <label class="form-label">Contraseña</label>
                                <input class="form-control" type="text" name="password">
                            </div>
                            <div class="col-6 mb-4" id="contenedor-rol-user-edit">
                                <label class="form-label">Rol <span class="required_flied">*</span></label>
                                <select class="form-select" id="rol-user-edit" required name="role">
                                    <option value="">Seleccionar rol</option>
                                    <?php if ($roles->isNotEmpty()) {
                                        foreach ($roles as $role) {
                                            if ($role->name == 'superadmin' || $role->name == 'admin') {
                                                if ($user->can('permissions.administrar.e')) { ?>
                                                    <option <?= ($rol == $role->name) ? 'selected' : '' ?> value="<?= $role->name ?>"><?= $role->name ?></option>
                                                <?php }
                                            } else { ?>
                                                <option <?= ($rol == $role->name) ? 'selected' : '' ?> value="<?= $role->name ?>"><?= $role->name ?></option>

                                    <?php }
                                        }
                                    } ?>
                                </select>
                            </div>
                            <div class="col-3 mb-4" id="contenedor-habilitar-call-edit" style="display:none">
                                <label class="form-label">Habilitar Call Center </label><br>
                                <label class="woow-switch">
                                    <input type="checkbox" name="callcenter_habilitado">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="col-6 mb-4" id="contenedor-id-usuario-sendpulse-edit">
                                <label class="form-label>">ID Usuario SendPulse </label>
                                <input class="form-control" type="text" name="id_user_sendpulse">
                            </div>
                            <div class="col-6 mb-4" id="contenedor-id-chatbot-sendpulse-edit">
                                <label class="form-label">ID Chatbot SendPulse </label>
                                <input class="form-control" type="text" name="id_chatbot_sendpulse">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Actualizar usuario</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-datatable text-nowrap">
                    <table class="datatables-usuarios table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo electrónico</th>
                                <th>Sede</th>
                                <th>Rol</th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <!--/ Ajax Sourced Server-side -->
        </div>
        <!-- / Content -->
    </div>
    <!-- Content wrapper -->
</div>
<!-- / Layout page -->