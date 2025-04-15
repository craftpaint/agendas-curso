<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <h2 class="content-header-title">Editar Rol: <?php echo htmlspecialchars($role->name, ENT_QUOTES, 'UTF-8'); ?></h2>
            </div>
        </div>

        <div class="content-body">
            <section id="app-access-roles">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Actualizar Rol</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo url('dashboard/roles/update/' . $role->id); ?>" method="POST">
                            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="_method" value="PUT">

                            <div class="mb-1">
                                <label for="role-name" class="form-label">Nombre del Rol:</label>
                                <input type="text" id="role-name" name="name" class="form-control" value="<?php echo htmlspecialchars($role->name, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="mb-1">
                                <label for="role-name" class="form-label">Nombre del Rol:</label>
                                <input type="text" id="role-name" name="name" class="form-control" value="<?php echo htmlspecialchars($role->name, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>

                            <div class="mb-1">
                                <label for="role-icon" class="form-label">Icono (clase o URL):</label>
                                <input type="text" id="role-icon" name="icon" class="form-control" placeholder="Ej: feather icon-user" value="<?php echo htmlspecialchars($role->icon, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>


                            <div class="mb-1">
                                <label class="form-label">Permisos:</label>
                                <div class="row">
                                    <?php if (!empty($permissions)): ?>
                                        <?php foreach ($permissions as $permission): ?>
                                            <div class="col-md-4 col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $permission->id; ?>" id="perm_<?php echo $permission->id; ?>"
                                                        <?php echo (in_array($permission->id, $rolePermissions)) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="perm_<?php echo $permission->id; ?>">
                                                        <?php echo htmlspecialchars($permission->name, ENT_QUOTES, 'UTF-8'); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>No hay permisos disponibles.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-2">
                                <button type="submit" class="btn btn-success">
                                    <i data-feather="save"></i> Actualizar Rol
                                </button>
                                <a href="<?php echo url('dashboard/roles'); ?>" class="btn btn-secondary">
                                    <i data-feather="arrow-left"></i> Volver
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>