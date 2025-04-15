<?php
// Agrupar los permisos por módulo (tomando la parte anterior al primer punto)
$groupedPermissions = [];
foreach ($permissions as $perm) {
    $parts = explode('.', $perm->name);
    $module = $parts[0];
    $groupedPermissions[$module][] = $perm;
}
?>

<!-- Layout container -->
<div class="layout-page" style="padding-top:0 !important">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-left mb-0">Administrar Permisos</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón para abrir el modal de creación -->
            <div class="mb-2 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPermissionModal">
                    <i data-feather="plus"></i> Agregar Permiso
                </button>
            </div>

            <!-- Listado de Permisos agrupados por módulo -->
            <?php if (!empty($groupedPermissions)): ?>
                <?php foreach ($groupedPermissions as $module => $perms): ?>
                    <div class="card mb-2">
                        <div class="card-header">
                            <h4 class="card-title"><?php echo ucfirst($module); ?></h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="col-1">ID</th>
                                        <th>Permiso</th>
                                        <th class="col-2">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($perms as $perm): ?>
                                        <tr>
                                            <td><?php echo $perm->id; ?></td>
                                            <td><?php echo htmlspecialchars($perm->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <!-- Botón que abre el modal de edición -->
                                                <button type="button"
                                                    class="btn btn-icon btn-label-primary waves-effect me-2 btn-edit-permission"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editPermissionModal"
                                                    data-permission-id="<?php echo $perm->id; ?>"
                                                    data-permission-name="<?php echo htmlspecialchars($perm->name, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="tf-icons ti ti-edit ti-md"></i>
                                                </button>
                                                <!-- Botón para eliminar -->
                                                <form action="<?php echo url('dashboard/permissions/destroy/' . $perm->id); ?>" method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button type="submit" class="btn btn-icon btn-label-danger waves-effect" onclick="return confirm('¿Estás seguro?');">
                                                        <i class="tf-icons ti ti-trash ti-md"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No se encontraron permisos.</p>
            <?php endif; ?>
        </div>
        <!-- / Content -->
    </div>
    <!-- Content wrapper -->
</div>

<!-- =======================
     MODAL: Crear Permiso
========================== -->
<div class="modal fade" id="createPermissionModal" tabindex="-1" aria-labelledby="createPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?php echo url('dashboard/configuracion/permissions/store'); ?>" method="POST">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPermissionModalLabel">Agregar Permiso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-1">
                        <label for="create-permission-name" class="form-label">Nombre del Permiso</label>
                        <input type="text" id="create-permission-name" name="name" class="form-control" placeholder="Ej: sede.horarios.a" required>
                        <small class="text-muted">Utiliza el formato: módulo.submódulo.operación (v, e, d, a)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i data-feather="x"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Guardar Permiso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =======================
     MODAL: Editar Permiso
========================== -->
<div class="modal fade" id="editPermissionModal" tabindex="-1" aria-labelledby="editPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- La acción del formulario se configurará dinámicamente -->
            <form id="form-edit-permission" method="POST" action="">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPermissionModalLabel">Editar Permiso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-1">
                        <label for="edit-permission-name" class="form-label">Nombre del Permiso</label>
                        <input type="text" id="edit-permission-name" name="name" class="form-control" required>
                        <small class="text-muted">Utiliza el formato: módulo.submódulo.operación (v, e, d, a)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i data-feather="x"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Actualizar Permiso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =======================
     SCRIPT PARA MODAL DE EDICIÓN
========================== -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Captura de los botones de editar permiso
        var editButtons = document.querySelectorAll('.btn-edit-permission');
        editButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                // Obtener datos del permiso desde los atributos data
                var permissionId = this.getAttribute('data-permission-id');
                var permissionName = this.getAttribute('data-permission-name');

                // Rellenar el campo del modal con la información del permiso
                document.getElementById('edit-permission-name').value = permissionName;

                // Configurar la acción del formulario de edición dinámicamente
                document.getElementById('form-edit-permission').action = "<?php echo url('dashboard/configuracion/permissions/update'); ?>" + "/" + permissionId;
            });
        });
    });
</script>