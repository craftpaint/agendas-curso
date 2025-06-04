<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">

        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Encabezado -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="mb-0">Empresas</h2>
                <!-- Botón para abrir el modal de creación -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEmpresaModal">
                    <i class="ti ti-plus"></i> Agregar Empresa
                </button>
            </div>

            <!-- Listado de Empresas en Cards -->
            <div class="row">
                @if($empresas->isNotEmpty())
                @foreach($empresas as $empresa)
                <?php
                // Se asume que en el controlador ya se calcularon:
                // $empresa->sedes_count  y $empresa->usuarios_count
                ?>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                @if(!empty($empresa->logo))
                                <img src="{{ asset($empresa->logo) }}" alt="Logo" class="rounded me-2" style="max-height: 50px;">
                                @endif
                                <div>
                                    <h5 class="mb-0">{{ $empresa->Nombre }}</h5>
                                    <small class="text-muted">{{ $empresa->Descripcion }}</small>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <p class="mb-1">Sedes Asociadas: <strong>{{ $empresa->sedes_count }}</strong></p>
                                <p class="mb-1">Usuarios Asociados: <strong>{{ $empresa->usuarios_count }}</strong></p>
                            </div>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-empresa"
                                    data-bs-toggle="modal" data-bs-target="#editEmpresaModal"
                                    data-empresa-id="{{ $empresa->id_empresa }}"
                                    data-empresa-name="{{ $empresa->Nombre }}"
                                    data-empresa-description="{{ $empresa->Descripcion }}"
                                    data-empresa-logo="{{ $empresa->logo }}">
                                    <i class="ti ti-edit"></i> Editar
                                </button>
                                <form action="{{ route('empresas.destroy', $empresa->id_empresa) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('¿Estás seguro de eliminar esta empresa?');">
                                        <i class="ti ti-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div class="col-12">
                    <p>No se encontraron empresas.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Modal: Crear Empresa -->
        <div class="modal fade" id="createEmpresaModal" tabindex="-1" aria-labelledby="createEmpresaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <form action="{{ route('empresas.store') }}" method="POST" enctype="multipart/form-data" id="createEmpresaForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createEmpresaModalLabel">Crear Empresa</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Nombre -->
                                <div class="mb-3 col-md-6">
                                    <label for="create-empresa-name" class="form-label">Nombre</label>
                                    <input type="text" id="create-empresa-name" name="name" class="form-control" required>
                                </div>
                                <!-- Descripción -->
                                <div class="mb-3 col-md-6">
                                    <label for="create-empresa-description" class="form-label">Descripción</label>
                                    <textarea id="create-empresa-description" name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <!-- Logo Empresa -->
                                <div class="mb-3 col-md-12">
                                    <label class="form-label">Logo Empresa</label>
                                    <!-- Usamos un input file con ID específico -->
                                    <input type="file" id="create-empresa-file" name="file">
                                    <!-- Campo oculto para almacenar la URL del logo subido -->
                                    <input type="hidden" name="logo" id="create-empresa-logo">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i data-feather="x"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Guardar Empresa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal: Editar Empresa -->
        <div class="modal fade" id="editEmpresaModal" tabindex="-1" aria-labelledby="editEmpresaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <form id="form-edit-empresa" method="POST" action="" enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editEmpresaModalLabel">Editar Empresa</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Nombre -->
                                <div class="mb-3 col-md-6">
                                    <label for="edit-empresa-name" class="form-label">Nombre</label>
                                    <input type="text" id="edit-empresa-name" name="name" class="form-control" required>
                                </div>
                                <!-- Descripción -->
                                <div class="mb-3 col-md-6">
                                    <label for="edit-empresa-description" class="form-label">Descripción</label>
                                    <textarea id="edit-empresa-description" name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <!-- Logo Empresa -->
                                <div class="mb-3 col-md-12">
                                    <label class="form-label">Logo Empresa</label>
                                    <!-- Aquí usamos un input file para FilePond y un campo oculto -->
                                    <input type="file" id="edit-empresa-file" name="file" class="form-control">
                                    <input type="hidden" name="logo" id="edit-empresa-logo">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i data-feather="x"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Actualizar Empresa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inicialización de Dropzone para Crear Empresa -->
<script>
    FilePond.registerPlugin(FilePondPluginImagePreview);

    // Inicializar FilePond para el input de crear empresa
    const inputCreate = document.querySelector('#create-empresa-file');
    const pondCreate = FilePond.create(inputCreate, {
        name: 'file',
        maxFiles: 1,
        credits: false,
        labelIdle: 'Arrastra y suelta el logo o <span class="filepond--label-action">clic para seleccionar</span>',
        server: {
            process: {
                url: '{{ route("empresas.uploadLogo") }}',
                method: 'POST',
                withCredentials: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                timeout: 7000,
                onload: (response) => {
                    // Se espera que la respuesta sea un JSON con filePath.
                    try {
                        const data = JSON.parse(response);
                        document.getElementById("create-empresa-logo").value = data.filePath;
                    } catch (error) {
                        console.error("Error parseando respuesta", error);
                    }
                    return response;
                },
                onerror: (response) => response.data,
            },
            revert: null,
        }
    });

    // Inicializar FilePond para el input de editar empresa
    const inputEdit = document.querySelector('#edit-empresa-file');
    const pondEdit = FilePond.create(inputEdit, {
        name: 'file',
        maxFiles: 1,
        credits: false,
        labelIdle: 'Arrastra y suelta el logo o <span class="filepond--label-action">clic para seleccionar</span>',
        server: {
            process: {
                url: '{{ route("empresas.uploadLogo") }}',
                method: 'POST',
                withCredentials: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                timeout: 7000,
                onload: (response) => {
                    try {
                        const data = JSON.parse(response);
                        document.getElementById("edit-empresa-logo").value = data.filePath;
                    } catch (error) {
                        console.error("Error parseando respuesta", error);
                    }
                    return response;
                },
                onerror: (response) => response.data,
            },
            revert: null,
        }
    });

    // Configurar el modal de edición para precargar los datos
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.btn-edit-empresa');
        editButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const idEmpresa = this.getAttribute('data-empresa-id');
                const nombre = this.getAttribute('data-empresa-name');
                const descripcion = this.getAttribute('data-empresa-description');
                const logo = this.getAttribute('data-empresa-logo');

                document.getElementById('edit-empresa-name').value = nombre;
                document.getElementById('edit-empresa-description').value = descripcion;
                document.getElementById('edit-empresa-logo').value = logo; // Guarda el URL en el campo oculto

                // // Si existe logo, precargarlo en FilePond como mock file:
                // if (logo) {
                //     // Primero, eliminamos cualquier archivo previo en pondEdit
                //     pondEdit.removeFiles();
                //     var mockFile = {
                //         name: "Logo actual",
                //         size: 12345,
                //         type: "image/png"
                //     };
                //     // Simulamos la adición del archivo
                //     pondEdit.emit("addedfile", mockFile);
                //     pondEdit.emit("thumbnail", mockFile, logo);
                //     pondEdit.emit("complete", mockFile);
                // } else {
                //     pondEdit.removeFiles();
                // }

                // Actualiza la acción del formulario de edición
                document.getElementById('form-edit-empresa').setAttribute('action', "{{ url('dashboard/empresas/update') }}/" + idEmpresa);
            });
        });
    });
</script>