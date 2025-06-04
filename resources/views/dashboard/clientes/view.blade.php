<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<!-- Layout container -->
<div class="layout-page content_clientes">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <h5 class="card-header">Editar cliente</h5>
                <div class="card-body">
                    <div class="send_form">
                        <div class="row">
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Nombre <span class="required_flied">*</span></label>
                                <input type="text" readonly class="form-control" name="nombre_cliente" required value="<?= $cliente['nombre_cliente'] ?>">
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Apellido <span class="required_flied">*</span></label>
                                <input type="text" readonly class="form-control" name="apellido_cliente" required value="<?= $cliente['apellido_cliente'] ?>">
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Correo <span class="required_flied">*</span></label>
                                <input type="text" readonly class="form-control" name="email_cliente" required value="<?= $cliente['email_cliente'] ?>">
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Tipo de documento <span class="required_flied">*</span></label>
                                <select class="select2 form-select" disabled required name="tipo_doc_cliente">
                                    <option <?= ($cliente['tipo_doc_cliente'] == 'CC') ? 'selected' : '' ?> value="CC">Cedula de ciudadania</option>
                                    <option <?= ($cliente['tipo_doc_cliente'] == 'TI') ? 'selected' : '' ?> value="TI">Tarjeta de Identidad</option>
                                    <option <?= ($cliente['tipo_doc_cliente'] == 'CE') ? 'selected' : '' ?> value="CE">Cédula de Extranjería</option>
                                    <option <?= ($cliente['tipo_doc_cliente'] == 'Pasaporte') ? 'selected' : '' ?> value="Pasaporte">Pasaporte</option>
                                    <option <?= ($cliente['tipo_doc_cliente'] == 'PEP') ? 'selected' : '' ?> value="PEP">PEP</option>
                                    <option <?= ($cliente['tipo_doc_cliente'] == 'PPT') ? 'selected' : '' ?> value="PPT">PPT</option>
                                </select>
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Número de documento <span class="required_flied">*</span></label>
                                <input type="text" readonly class="form-control" name="doc_cliente" required value="<?= $cliente['doc_cliente'] ?>">
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Teléfono <span class="required_flied">*</span></label>
                                <input type="text" readonly class="form-control" name="telefono_cliente" id="phoneCliente" required value="<?= $cliente['telefono_cliente'] ?>">
                            </div>
                            <div class="mb-4 col-md-12">
                                <label class="form-label">Descripción <span class="required_flied">*</span></label>
                                <textarea readonly class="form-control" rows="3" name="desc_cliente" required><?= $cliente['desc_cliente'] ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php
                    if (is_array($vehiculos) && !empty($vehiculos)) {
                    ?>
                        <h5>Vehiculos</h5>
                        <div class="list-group">
                            <?php
                            foreach ($vehiculos as $key => $vehiculo) {
                            ?>
                                <a href="javascript:void(0);" class="list-group-item list-group-item-action flex-column align-items-start waves-effect">
                                    <div class="d-flex justify-content-between w-100">
                                        <h5 class="mb-1">Placa: <?= $vehiculo['placa_vehiculo'] ?></h5>
                                        <small><?= $vehiculo['tipo_vehiculo'] ?></small>
                                    </div>
                                    <small>Modelo: <?= $vehiculo['modelo_vehiculo'] ?></small>
                                </a>
                            <?php
                            }
                            ?>
                        </div>

                    <?php
                    }
                    ?>
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
    const phoneInputField = document.querySelector("#phoneCliente");
    const phoneInput = window.intlTelInput(phoneInputField, {
        initialCountry: "co",
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
    });
</script>
<style>
    .iti {
        width: 100%;
    }
</style>