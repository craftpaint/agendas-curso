<!doctype html>

<html
    lang="en"
    class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{url('assets')}}/"
    data-template="vertical-menu-template"
    data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Crear cita</title>
    <link rel="icon" type="image/x-icon" href="{{url('assets/img/favicon/favicon.ico')}}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{url('assets/vendor/css/rtl/core.css')}}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{url('assets/vendor/css/rtl/theme-default.css')}}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{url('assets/css/demo.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/node-waves/node-waves.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/typeahead-js/typeahead.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/select2/select2.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
    <link rel="stylesheet" href="{{url('assets/css/style.css')}}" />
    <script src="{{url('assets/vendor/js/helpers.js')}}"></script>
    <script src="{{url('assets/js/config.js')}}"></script>
    <script>
        url = '<?= url('') ?>'
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
</head>

<body>

    <div class="container-xxl p-0">
        <form class="content_form" action="{{url('savecita')}}">
            <input type="hidden" name="id_sede" value="<?= $id_sede ?>">
            <div class="row">
                <div class="col-12">
                    <h5 class="mb-2">Sede: <?= $sede['nombre_sede'] ?></h5>
                    <p class="mb-1"><strong>Dirección:</strong> <?= $sede['direccion_sede'] ?></p>
                    <p><strong>Teléfono:</strong> <span class="badge rounded-pill bg-label-primary"><?= $sede['tel_sede'] ?></span></p>
                </div>
                <div class="mb-4 col-12 col-md-6">
                    <label class="form-label">Día de la cita <span class="required_flied">*</span></label>
                    <input name="reserva_cita" type="text" id="citaDia" placeholder="DD/MM/YYYY" class="form-control" readonly required />
                </div>
                <div class="mb-6 col-12 col-md-6">
                    <label class="form-label">Franja horaria <span class="required_flied">*</span></label>
                    <select id="citaHora" class="form-select select2" required name="id_sede_horario" disabled>
                        <option value="">Seleccionar horario</option>
                    </select>
                </div>
                <div class="CantCupos">
                </div>
                <hr>
                <div class="mb-4 col-6">
                    <label class="form-label">Nombre <span class="required_flied">*</span></label>
                    <input type="text" class="form-control" name="nombre_cliente" required>
                </div>
                <div class="mb-4 col-6">
                    <label class="form-label">Apellido <span class="required_flied">*</span></label>
                    <input type="text" class="form-control" name="apellido_cliente" required>
                </div>
                <div class="mb-4 col-12 col-md-6">
                    <label class="form-label">Correo <span class="required_flied">*</span></label>
                    <input type="text" class="form-control" name="email_cliente" required>
                </div>
                <div class="mb-4 col-12 col-md-6">
                    <label class="form-label">Teléfono <span class="required_flied">*</span></label><br>
                    <input type="tel" class="form-control" id="phoneCliente" name="telefono_cliente" required>
                </div>
                <div class="mb-4 col-6">
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
                <div class="mb-4 col-6">
                    <label class="form-label">Número de documento <span class="required_flied">*</span></label>
                    <input type="text" class="form-control" name="doc_cliente" required>
                </div>
                <div class="col-12 row mx-auto p-0" id="divContentVehiculo">
                    <hr>
                    <div class="mb-4 col-12 col-md-6">
                        <label class="form-label">Tipo de vehiculo <span class="required_flied">*</span></label>
                        <select class="select2 form-select" id="selectTipoVehiculo" name="tipo_vehiculo">
                            <option value="Motocicleta">Motocicleta</option>
                            <option value="Automóvil">Automóvil</option>
                        </select>
                    </div>
                    <div class="mb-4 col-6">
                        <label class="form-label">Placa de vehiculo <span class="required_flied">*</span></label>
                        <input type="text" class="form-control" id="placa_vehiculo" name="placa_vehiculo" required>
                    </div>
                    <input type="hidden" class="form-control" id="modelo_vehiculo" name="modelo_vehiculo" value=0000>
                </div>
                <div class="checkbox col-12 mx-auto my-4 px-4">
                    <input id="form-checkbox-1" name="conscentimiento_subsidio" type="checkbox" required="required">
                    <label for="form-checkbox-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 200 200">
                            <mask fill="white" id="checkbox-mask">
                                <rect height="200" width="200"></rect>
                            </mask>
                            <rect mask="url(#checkbox-mask)" stroke-width="40" height="200" width="200"></rect>
                            <path stroke-width="15" d="M52 111.018L76.9867 136L149 64"></path>
                        </svg>
                        <span class="text-center f18">Acepto las <a href="https://ciatran.com.co/assets/files/PD-DA-02-Politica-de-Tratamiento-de-Datos.pdf" target="_blank">politicas de tratamiento de datos.</a></span>
                    </label>
                </div>
                <div class="checkbox col-12 mx-auto my-4 px-4">
                    <input id="form-checkbox-2" name="conscentimiento_horario" type="checkbox" required="required">
                    <label for="form-checkbox-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 200 200">
                            <mask fill="white" id="checkbox-mask">
                                <rect height="200" width="200"></rect>
                            </mask>
                            <rect mask="url(#checkbox-mask)" stroke-width="40" height="200" width="200"></rect>
                            <path stroke-width="15" d="M52 111.018L76.9867 136L149 64"></path>
                        </svg>
                        <span class="text-center f18">Soy consciente que debo llegar 30 minutos antes de la cita, de lo contrario no podre tomar el curso.</span>
                    </label>
                </div>
            </div>
            <input type="hidden" name="utm_source" value="{{ session('utm_source', 'Desconocido') }}">
            <input type="hidden" name="url_variables" value="{{ json_encode(request()->except('utm_source')) }}">
            <button type="submit" class="btn btn-primary boton_submit">Agendar cita</button>
        </form>
    </div>
    <?php
    $festivos = @unserialize($sede['festivos_sede']);
    if ($festivos == false) {
        $festivos = [];
    }
    ?>
    <script>
        id_sede = '<?= $id_sede ?>'
        festivos = <?= json_encode($festivos) ?>;
        const phoneInputField = document.querySelector("#phoneCliente");
        const phoneInput = window.intlTelInput(phoneInputField, {
            initialCountry: "co",
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
        });
    </script>
    <script src="{{url('assets/vendor/libs/jquery/jquery.js')}}"></script>
    <script src="{{url('assets/vendor/libs/popper/popper.js')}}"></script>
    <script src="{{url('assets/vendor/js/bootstrap.js')}}"></script>
    <script src="{{url('assets/vendor/libs/node-waves/node-waves.js')}}"></script>
    <script src="{{url('assets/vendor/libs/hammer/hammer.js')}}"></script>
    <script src="{{url('assets/vendor/libs/typeahead-js/typeahead.js')}}"></script>
    <script src="{{url('assets/vendor/js/menu.js')}}"></script>
    <script src="{{url('assets/js/main.js')}}"></script>
    <script src="{{url('assets/vendor/libs/select2/select2.js')}}"></script>
    <script src="{{url('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
    <script src="{{url('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
    <script src="{{url('assets/js/createcita.js')}}?v=1.0.1"></script>
    <style>
        body {
            background: transparent;
        }

        .content_form {
            border: 1px solid #e5e9f2;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            background: #fff;
        }

        .iti {
            width: 100%;
        }

        .swal2-container {
            background: transparent !important;
        }

        .swal2-title {
            margin: auto !important;
        }
    </style>


</body>

</html>