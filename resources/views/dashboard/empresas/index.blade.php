<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">

        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Encabezado -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="mb-0">Empresas</h2>
                @if ($user->can('empresa.Empresa.a'))
                    <!-- Botón para abrir el modal de creación -->
                    <button type="button" class="btn btn-primary" id="btn-crear-empresa" data-bs-toggle="modal" data-bs-target="#createEmpresaModal">
                        <i class="ti ti-plus"></i> Agregar Empresa
                    </button>
                @endif
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
                                <p class="mb-1"><strong>{{ $empresa->tipo_documento_empresa . " " . $empresa->documento_empresa }}</strong></p>
                                <p class="mb-1">Plan: <strong>{{ $empresa->plan_empresa }}</strong></p>
                                <p class="mb-1">Plantilla Correo: <strong>{{ $empresa->id_plantilla }}</strong></p>
                                <p class="mb-1">
                                    Estado:
                                    @if($empresa->estado)
                                        <strong class="text-success">Activo</strong>
                                    @else
                                        <strong class="text-danger">Inactivo</strong>
                                    @endif
                                </p>
                                <p class="mb-1">Sedes Asociadas: <strong>{{ $empresa->sedes_count }}</strong></p>
                                <p class="mb-1">Usuarios Asociados: <strong>{{ $empresa->usuarios_count }}</strong></p>
                            </div>
                            <div class="text-end mt-2">
                                @if ($user->can('empresa.Empresa.e'))    
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-empresa"
                                        data-empresa-id="{{ $empresa->id_empresa }}"
                                        data-empresa-nombre="{{ $empresa->Nombre }}"
                                        data-empresa-descripcion="{{ $empresa->Descripcion }}"
                                        data-empresa-tipo-documento="{{ $empresa->tipo_documento_empresa }}"
                                        data-empresa-documento="{{ $empresa->documento_empresa }}"
                                        data-empresa-plan="{{ $empresa->plan_empresa }}"
                                        data-empresa-logo="{{ $empresa->logo }}"
                                        data-empresa-plantilla="{{ $empresa->id_plantilla }}">
                                        <i class="ti ti-edit"></i> Editar
                                    </button>
                                @endif
                                @if ($user->can('empresa.Empresa.d'))
                                    @if($empresa->estado)
                                        <button class="btn btn-sm btn-outline-danger btn-estado-empresa" data-empresa-id="{{ $empresa->id_empresa }}">
                                            <i class="ti ti-trash"></i> Inactivar
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-outline-success btn-estado-empresa" data-empresa-id="{{ $empresa->id_empresa }}">
                                            <i class="ti ti-check"></i> Activar
                                        </button>
                                    @endif
                                @endif
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Nombre -->
                                <div class="mb-3 col-md-6">
                                    <label for="create-empresa-name" class="form-label">Nombre</label>
                                    <input type="text" id="create-empresa-name" name="nombre-empresa" class="form-control" required>
                                </div>
                                <!-- Plan Empresa -->
                                <div class="mb-3 col-md-6">
                                    <label for="create-empresa-plan" class="form-label">Plan de Empresa</label>
                                    <select id="create-empresa-plan" name="plan-empresa" class="form-select" required>
                                        <option value="PREPAGO">PREPAGO</option>
                                        <option value="POSPAGO">POSPAGO</option>
                                    </select>
                                </div>
                                <!-- tipo documento empresa -->
                                <div class="mb-3 col-md-6">
                                    <label for="create-empresa-document-type" class="form-label">Tipo de Documento</label>
                                    <select id="create-empresa-document-type" name="tipo-documento" class="form-select" required>
                                        <option value="NIT">NIT</option>
                                        <option value="CC">RUT</option>
                                    </select>
                                </div>
                                <!-- Número de Documento -->
                                <div class="mb-3 col-md-6">
                                    <label for="create-empresa-document-number" class="form-label">Número de Documento</label>
                                    <input type="text" id="create-empresa-document-number" name="numero-documento" class="form-control" required>
                                </div>
                                <!-- plantilla Empresa -->
                                <div class="mb-3 col-md-12">
                                    <label for="create-empresa-template" class="form-label">ID de plantilla de correo en SendPulse</label>
                                    <input type="text" id="create-empresa-template" name="plantilla-empresa" class="form-control">
                                </div>
                                <!-- Logo Empresa -->
                                <div class="mb-3 col-md-6">
                                    <label for="create-empresa-file" class="form-label">Logo Empresa</label>
                                    <input type="file" id="create-empresa-file" name="logo-empresa" accept="image/*" class="form-control">
                                    <div id="preview-container" class="mt-2 container" style="display:none;">
                                        <div class="row justify-content-center">
                                            <div class="col-12">
                                                <img id="preview-image" src="" class="w-100 img-fluid" alt="Vista previa" style="max-width: 600px; max-height: 500px; border-radius: 8px;">
                                            </div>
                                            <div class="col-4">
                                                <button type="button" id="btn-cancel-image" class="btn btn-sm btn-outline-danger mt-2 mx-auto">Quitar imagen</button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="logo-url" name="logo-url" id="create-empresa-logo">
                                </div>
                                <!-- Descripción -->
                                <div class="mb-3 col-md-6">
                                    <label for="create-empresa-description" class="form-label">Descripción</label>
                                    <textarea id="create-empresa-description" name="descripcion-empresa" class="form-control" rows="3"></textarea>
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