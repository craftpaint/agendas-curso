<?php
// Agrupar permisos por módulo y submódulo.
// Se espera que $permissions esté definido y contenga objetos con al menos la propiedad 'name' y 'id'.
// La nomenclatura debe ser: modulo.submodulo.operacion (por ejemplo, sede.horarios.v).
$permissionsByModule = [];
$allOperations = [];

if (!empty($permissions)) {
    foreach ($permissions as $perm) {
        $parts = explode('.', $perm->name);
        if (count($parts) < 3) {
            continue; // Salta permisos que no cumplan con el formato
        }
        list($module, $submodule, $operation) = $parts;

        // Agrupar
        $permissionsByModule[$module][$submodule][$operation] = $perm->id;

        // Registrar operación en el arreglo global si no existe todavía
        if (!in_array($operation, $allOperations)) {
            $allOperations[] = $operation;
        }
    }
    // Ordena las operaciones; se puede personalizar el orden si se requiere (por defecto alfabeticamente)
    sort($allOperations);
}
$opLabels = [
    'v' => 'View',
    'e' => 'Edit',
    'd' => 'Delete',
    'a' => 'Add'
];

?>

<!-- Layout container -->
<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Encabezado -->
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-left mb-0">Roles y Permisos</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listado de Roles -->
            <div class="content-body row">
                <?php if (!empty($roles)) : ?>
                    <?php foreach ($roles as $role): ?>
                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="card role-card my-1">
                                <div class="card-body">
                                    <p class="Text_muted mb-0"><?php echo $role->users()->count(); ?> Usuarios</p>
                                    <h5 class="mb-75">
                                        <?php if ($role->icon): ?>
                                            <i class="ti ti-<?php echo $role->icon; ?>"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($role->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </h5>
                                    <div class="text-end">
                                        <!-- Botón para editar: se abre el modal de edición y se pasan datos vía data-* -->
                                        <a href="javascript:void(0);"
                                            class="mr-50 btn-edit-role text-left"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editRoleModal"
                                            data-role-id="<?php echo $role->id; ?>"
                                            data-role-name="<?php echo htmlspecialchars($role->name, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-role-icon="<?php echo htmlspecialchars($role->icon, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-role-perms='<?php echo json_encode($role->permissions->pluck("id")->toArray()); ?>'>
                                            <i class="ti ti-edit"></i> Editar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- Botón para agregar un nuevo rol (abre el modal de creación) -->
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="card role-card my-1">
                        <div class="card-body text-center">
                            <a href="javascript:void(0);" class="btn btn-primary"
                                data-bs-toggle="modal" data-bs-target="#createRoleModal">
                                <i data-feather="plus"></i> Agregar Rol
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fin de listado -->

            <div class="card"></div>
            <!--/ Ajax Sourced Server-side -->
        </div>
        <!-- / Content -->
    </div>
    <!-- Content wrapper -->
</div>

<!-- =======================
     MODAL: Crear Rol
========================== -->
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <form action="<?php echo url('dashboard/roles/store'); ?>" method="POST">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="createRoleModalLabel">Crear Nuevo Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row ">
                        <div class="mb-1 col-12 col-md-6">
                            <label for="create-role-name" class="form-label">Nombre del Rol:</label>
                            <input type="text" id="create-role-name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-1 col-12 col-md-6">
                            <label for="create-role-icon" class="form-label">Nombre icono (Tabler icon)</label>
                            <input type="text" id="create-role-icon" name="icon" class="form-control" placeholder="Ej: icon-user">
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Permisos:</label>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <?php foreach ($permissionsByModule as $module => $submodules): ?>
                                        <!-- Fila para el módulo, sin cabecera; se muestra en negrita -->
                                        <tr>
                                            <td colspan="<?php echo count($allOperations); ?>">
                                                <strong><?php echo ucfirst($module); ?></strong>
                                            </td>
                                        </tr>
                                        <?php foreach ($submodules as $submodule => $operations): ?>
                                            <tr>
                                                <!-- Primera celda: nombre del submódulo -->
                                                <td><?php echo ucfirst($submodule); ?></td>
                                                <?php foreach ($allOperations as $op): ?>
                                                    <td class="text-center">
                                                        <?php if (isset($operations[$op])):
                                                            $permId = $operations[$op];
                                                            // En el modal de edición, si se dispone de $rolePermissions, se marca el checkbox.
                                                            $checked = (isset($rolePermissions) && in_array($permId, $rolePermissions)) ? 'checked' : '';
                                                        ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="permissions[]"
                                                                    value="<?php echo $permId; ?>"
                                                                    id="perm_<?php echo $permId; ?>"
                                                                    <?php echo $checked; ?>>
                                                                <label class="form-check-label" for="perm_<?php echo $permId; ?>">
                                                                    <?php echo isset($opLabels[$op]) ? $opLabels[$op] : strtoupper($op); ?>
                                                                </label>
                                                            </div>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i data-feather="x"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Guardar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =======================
     MODAL: Editar Rol
========================== -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <!-- La acción del formulario se configurará dinámicamente -->
            <form id="form-edit-role" method="POST" action="">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleModalLabel">Editar Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Datos del rol -->
                    <div class="row ">
                        <div class="mb-1 col-12 col-md-6">
                            <label for="edit-role-name" class="form-label">Nombre del Rol:</label>
                            <input type="text" id="edit-role-name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-1 col-12 col-md-6">
                            <label for="edit-role-icon" class="form-label">Nombre icono (Tabler icon)</label>
                            <input type="text" id="edit-role-icon" name="icon" class="form-control" placeholder="Ej: feather icon-user">
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Permisos:</label>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <?php foreach ($permissionsByModule as $module => $submodules): ?>
                                        <!-- Fila para el módulo, sin cabecera; se muestra en negrita -->
                                        <tr>
                                            <td colspan="<?php echo count($allOperations); ?>">
                                                <strong><?php echo ucfirst($module); ?></strong>
                                            </td>
                                        </tr>
                                        <?php foreach ($submodules as $submodule => $operations): ?>
                                            <tr>
                                                <!-- Primera celda: nombre del submódulo -->
                                                <td><?php echo ucfirst($submodule); ?></td>
                                                <?php foreach ($allOperations as $op): ?>
                                                    <td class="text-center">
                                                        <?php if (isset($operations[$op])):
                                                            $permId = $operations[$op];
                                                            // En el modal de edición, si se dispone de $rolePermissions, se marca el checkbox.
                                                            $checked = (isset($rolePermissions) && in_array($permId, $rolePermissions)) ? 'checked' : '';
                                                        ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="permissions[]"
                                                                    value="<?php echo $permId; ?>"
                                                                    id="perm_<?php echo $permId; ?>"
                                                                    <?php echo $checked; ?>>
                                                                <label class="form-check-label" for="perm_<?php echo $permId; ?>">
                                                                    <?php echo isset($opLabels[$op]) ? $opLabels[$op] : strtoupper($op); ?>
                                                                </label>
                                                            </div>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i data-feather="x"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Actualizar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =======================
     SCRIPT PARA CONFIGURAR MODAL DE EDICIÓN
========================== -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const editButtons = document.querySelectorAll('.btn-edit-role');

        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Obtener permisos desde el botón
                const perms = JSON.parse(this.getAttribute('data-role-perms'));

                // Limpiar todos los checkboxes
                document.querySelectorAll('#editRoleModal input[type="checkbox"][name="permissions[]"]').forEach(cb => {
                    cb.checked = false;
                });

                // Marcar los que correspondan
                perms.forEach(permId => {
                    const checkbox = document.querySelector(`#editRoleModal input#perm_${permId}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });

                // Llenar campos de nombre e ícono
                document.getElementById('edit-role-name').value = this.getAttribute('data-role-name');
                document.getElementById('edit-role-icon').value = this.getAttribute('data-role-icon');

                // Actualizar la acción del formulario
                const form = document.getElementById('form-edit-role');
                form.setAttribute('action', `<?php echo url('dashboard/roles/update'); ?>/${this.getAttribute('data-role-id')}`);
            });
        });
    });
</script>