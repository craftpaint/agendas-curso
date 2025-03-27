<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<!-- Layout container -->
<div class="layout-page content_clientes" style="padding-top:0 !important">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <h5 class="card-header">Agregar cliente</h5>
                <div class="card-body">
                    <form class="send_form" action="{{url('dashboard/clientes/save')}}">
                        <div class="row">
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Nombre <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="nombre_cliente" required>
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Apellido <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="apellido_cliente" required>
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Correo <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="email_cliente" required>
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Tipo de documento <span class="required_flied">*</span></label>
                                <select class="select2 form-select" required name="tipo_doc_cliente">
                                    <option value="CC">Cedula de ciudadania</option>
                                    <option value="TI">Tarjeta de Identidad</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                    <option value="PEP">PEP</option>
                                    <option value="PPT">PPT</option>
                                </select>
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Número de documento <span class="required_flied">*</span></label>
                                <input type="text" class="form-control" name="doc_cliente" required>
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label">Teléfono <span class="required_flied">*</span></label><br>
                                <input type="tel" class="form-control" id="phoneCliente" name="telefono_cliente" required>
                            </div>
                            <div class="mb-4 col-md-12">
                                <label class="form-label">Descripción <span class="required_flied">*</span></label>
                                <textarea class="form-control" rows="3" name="desc_cliente" required></textarea>
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