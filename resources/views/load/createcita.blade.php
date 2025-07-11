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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Crear cita</title>
    <link rel="icon" type="image/x-icon" href="{{url('assets/img/favicon/favicon.ico')}}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{url('assets/vendor/css/rtl/core.css')}}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{url('assets/vendor/css/rtl/theme-default.css')}}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{url('assets/vendor/fonts/tabler-icons.css')}}" />
    <link rel="stylesheet" href="{{url('assets/css/demo.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/node-waves/node-waves.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/typeahead-js/typeahead.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/select2/select2.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
    <link rel="stylesheet" href="{{url('assets/css/style.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/tagify/tagify.css')}}" />
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
                <div class="mb-4 col-12 col-md-6">
                    <label class="form-label">Nombre <span class="required_flied">*</span></label>
                    <input type="text" class="form-control" name="nombre_cliente" required>
                </div>
                <div class="mb-4 col-12 col-md-6">
                    <label class="form-label">Apellido <span class="required_flied">*</span></label>
                    <input type="text" class="form-control" name="apellido_cliente" required>
                </div>
                <div class="mb-4 col-12 col-md-6">
                    <label class="form-label">Correo <span class="required_flied">*</span></label>
                    <input type="email" class="form-control" name="email_cliente" required>
                </div>
                <div class="mb-4 col-12 col-md-6">
                    <label class="form-label">Teléfono <span class="required_flied">*</span></label><br>
                    <input type="tel" class="form-control" id="phoneCliente" name="telefono_cliente" required minlength="10" maxlength="10">
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
                <div class="mb-4 col-12 col-md-6">
                    <label class="form-label">Número de documento <span class="required_flied">*</span></label>
                    <input type="text" class="form-control" name="doc_cliente" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="resultadoCitas">
                </div>
                <div class="mb-4 col-12 col-md-6">
                    <label class="form-label">Numero de Comparendo <span class="required_flied">*</span></label>
                    <select class="select2 form-select" id="selectServicioLiquidador" required name="servicio_liquidador">
                        <option value="" selected disabled>Seleccione una opción</option>
                        <?php
                        if (is_array($servicios_liquidador) && !empty($servicios_liquidador)) {
                            foreach ($servicios_liquidador as $key => $servicio) {
                                if ($servicio->nombre_servicio_liquidador == "1 comparendo") {
                                    echo '<option value="' . $servicio->id_servicio_liquidador . '"> 1 comparendo = 1 curso</option>';
                                } elseif ($servicio->nombre_servicio_liquidador == "2 comparendos") {
                                    echo '<option value="' . $servicio->id_servicio_liquidador . '"> 2 comparendos = 2 cursos</option>';
                                } elseif ($servicio->nombre_servicio_liquidador == "3 comparendos") {
                                    echo '<option value="' . $servicio->id_servicio_liquidador . '"> 3 comparendos = 3 cursos</option>';
                                } elseif ($servicio->nombre_servicio_liquidador == "+3 comparendos") {
                                    echo '<option value="' . $servicio->id_servicio_liquidador . '"> Más de 3 comparendos</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                    <p class="text-muted">Recuerde que por cada comparendo se debe realizar un curso.</p>
                </div>
                <div class="mb-4 col-12 col-md-6">
                    <label class="form-label">Codigo de Comparendo</label>
                    <input id="codigo_comparendo_tagify" name="codigo_comparendo" class="form-control" placeholder="Escribe tu codigo de comparendo si lo conoces" autocomplete="off" maxlength="3">
                    <p class="text-muted">Si conoce el codigo del Comparendo, puede ingresarlo aquí. de lo contrario puedes dejarlo vacio.</p>
                </div>
                <div class="col-12 row mx-auto p-0" id="divContentVehiculo">
                    <hr>
                    <div class="mb-4 col-12 col-md-6">
                        <label class="form-label">Tipo de vehiculo <span class="required_flied">*</span></label>
                        <select class="select2 form-select" id="selectTipoVehiculo" name="tipo_vehiculo">
                            <option value="Motocicleta">Motocicleta</option>
                            <option value="Automotor">Automotor</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-4 col-12 col-md-6">
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
                        <span class="text-center f18">Soy consciente que debo llegar <strong>30 MINUTOS ANTES</strong> de la cita, de lo contrario no podre tomar el curso.</span>
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
        const servicios_liquidador = JSON.parse('<?php echo json_encode($servicios_liquidador); ?>');
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
    <script src="{{url('assets/vendor/libs/tagify/tagify.js')}}"></script>
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
    <script>
        const codigo_comparendo = document.querySelector("#codigo_comparendo_tagify");

        const whitelist = [
            "A01", "A02", "A03", "A04", "A05", "A06", "A07", "A08", "A09", "A10", "A11", "A12",
            "B01", "B02", "B03", "B04", "B05", "B06", "B07", "B08", "B09", "B10", "B11", "B12",
            "B13", "B14", "B15", "B16", "B17", "B18", "B19", "B20", "B21", "B22", "B23",
            "C01", "C02", "C03", "C04", "C05", "C06", "C07", "C08", "C09", "C10", "C11", "C12",
            "C13", "C14", "C15", "C16", "C17", "C18", "C19", "C20", "C21", "C22", "C23", "C24",
            "C25", "C26", "C27", "C28", "C29", "C30", "C31", "C32", "C33", "C34", "C35", "C36",
            "C37", "C38", "C39", "C40",
            "D01", "D02", "D03", "D04", "D05", "D06", "D07", "D08", "D09", "D10", "D11", "D12",
            "D13", "D14", "D15", "D16", "D17",
            "E01", "E02", "E04",
            "F01", "F02", "F03", "F04", "F05", "F06", "F07",
            "G01", "G02",
            "H01", "H02", "H03", "H04", "H05", "H06", "H07", "H08", "H09", "H10", "H11", "H12"
        ];

        // Inline
        let codigo_comparendo_tagify = new Tagify(codigo_comparendo, {
            whitelist: whitelist,
            maxTags: 5, // allows to select max items
            pattern: /^.{0,3}$/,
            dropdown: {
                maxItems: 20, // display max items
                classname: "tags-inline", // Custom inline class
                enabled: 0,
                closeOnSelect: false
            }
        });
    </script>
    <div id="vuexy-loading" class="d-none" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(140, 140, 141, 0.9);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    ">
        <div class="spinner-border text-white" style="width: 3rem; height: 3rem;"></div>
        <p class="text-white mt-2">Cargando horarios...</p>
    </div>
</body>

</html>
