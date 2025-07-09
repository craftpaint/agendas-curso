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
                    <form action="{{ url('dashboard/empresas/guardar') }}" method="POST" enctype="multipart/form-data" id="createEmpresaForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createEmpresaModalLabel">Crear Empresa</h5>
                            <button type="button" class="btn btn-close btn-danger" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                                <!-- tipo documento empresa -->
                                <div class="mb-3 col-md-6">
                                    <label for="create-empresa-document-type" class="form-label">Tipo de Documento</label>
                                    <select id="create-empresa-document-type" name="document_type" class="form-select" required>
                                        <option value="NIT">NIT</option>
                                        <option value="CC">RUT</option>
                                    </select>
                                </div>
                                <!-- Número de Documento -->
                                <div class="mb-3 col-md-6">
                                    <label for="create-empresa-document-number" class="form-label">Número de Documento</label>
                                    <input type="text" id="create-empresa-document-number" name="document_number" class="form-control" required>
                                </div>
                                <!-- Plan Empresa -->
                                <div class="mb-3 col-md-6">
                                    <label for="create-empresa-plan" class="form-label">Plan de Empresa</label>
                                    <select id="create-empresa-plan" name="plan" class="form-select" required>
                                        <option value="free">PREPAGO</option>
                                        <option value="basic">POSPAGO</option>
                                    </select>
                                </div>
                                <!-- Logo Empresa -->
                                <div class="mb-3 col-md-12">
                                    <label for="create-empresa-file" class="form-label">Logo Empresa</label>
                                    <input type="file" id="create-empresa-file" name="file" accept="image/*" class="form-control">
                                    <div id="preview-container" class="mt-2" style="display:none;">
                                        <img id="preview-image" src="" alt="Vista previa" style="max-width: 600px; max-height: 500px; border-radius: 8px;">
                                        <button type="button" id="btn-cancel-image" class="btn btn-sm btn-outline-danger ms-2">Quitar imagen</button>
                                    </div>
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
    </div>
</div>