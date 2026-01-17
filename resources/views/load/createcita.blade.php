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
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-TV4D7WQ4');
    </script>
    <!-- End Google Tag Manager -->
</head>

<body>
    <div class="container-xxl p-0">
        <form class="content_form" action="{{url('savecita')}}">
            <input type="hidden" name="id_sede" value="<?= $id_sede ?>">
            <div class="row">
                <div class="col-12">
                    <!-- FILA SUPERIOR: TITULO + CIUDAD -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                        <h5 class="mb-0">
                            Sede: <?= $sede['nombre_sede'] ?>
                        </h5>
                        <!-- PILL CIUDAD -->
                        <span class="badge rounded-pill bg-label-info city-pill">
                            <i class="ti ti-map-pin"></i>
                            <?= $nombre_ciudad ?? 'Ciudad no definida' ?>
                        </span>
                    </div>
                    <!-- INFORMACIÓN DEBAJO -->
                    <p class="mb-1">
                        <strong>Dirección:</strong> <?= $sede['direccion_sede'] ?>
                    </p>
                    <p>
                        <strong>Teléfono:</strong>
                        <span class="badge rounded-pill bg-label-primary">
                            <?= $sede['tel_sede'] ?>
                        </span>
                    </p>
                </div>
                <hr>
                <!-- Sección de Agendamiento -->
                <div class="col-12">
                    <h5 class="mb-4">Sección de Agendamiento</h5>
                    <div class="accordion" id="accordionComparendos">
                        <!-- Panel inicial por defecto -->
                        <div class="accordion-item comparendo-panel" data-index="1">
                            <h2 class="accordion-header" id="heading1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                    Comparendo #1
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="heading1" data-bs-parent="#accordionComparendos">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="mb-4 col-12 col-md-6">
                                            <label class="form-label">Día de la cita <span class="required_flied">*</span></label>
                                            <input name="comparendos[0][reserva_cita]" type="text" class="form-control datepicker" placeholder="DD/MM/YYYY" readonly required />
                                        </div>
                                        <div class="mb-4 col-12 col-md-6">
                                            <label class="form-label">Franja horaria <span class="required_flied">*</span></label>
                                            <select name="comparendos[0][id_sede_horario]" class="form-select select2 horario-select" required disabled>
                                                <option value="">Seleccionar horario</option>
                                            </select>
                                        </div>
                                        <div class="mb-4 col-12 col-md-6 vehiculo-section">
                                            <label class="form-label">Tipo de vehículo <span class="required_flied">*</span></label>
                                            <select name="comparendos[0][tipo_vehiculo]" class="form-select select2" required>
                                                <option value="Motocicleta">Motocicleta</option>
                                                <option value="Automotor">Automotor</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                        <div class="mb-4 col-12 col-md-6 vehiculo-section">
                                            <label class="form-label">Placa de vehículo <span class="required_flied">*</span></label>
                                            <input name="comparendos[0][placa_vehiculo]" type="text" class="form-control" required />
                                        </div>
                                        <div class="mb-4 col-12 col-md-6">
                                            <label class="form-label">Código de comparendo</label>
                                            <input name="comparendos[0][codigo_comparendo]" type="text" class="form-control tagify-input" placeholder="Escribe tu código de comparendo si lo conoces" autocomplete="off" />
                                            <p class="text-muted mb-0">Si conoce el código del comparendo, puede ingresarlo aquí, de lo contrario puede dejarlo vacío.</p>
                                        </div>
                                        <div class="mb-4 col-12 col-md-6">
                                            <label class="form-label">Fecha de notificación del comparendo <span class="required_flied">*</span></label>
                                            <input name="comparendos[0][fecha_notificacion]" type="text" class="form-control notif-datepicker" placeholder="DD/MM/YYYY" readonly required />
                                        </div>
                                    </div>
                                    <div class="text-end mt-2">
                                        <button type="button" class="btn btn-danger removeComparendo" disabled>Eliminar este comparendo</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" id="addComparendo" class="btn btn-outline-primary" style="border-radius: 20px; background-color: #e6f2ff; color: #007bff;">+ Agregar otro curso para comparendo</button>
                        <p class="text-muted mt-2">Recuerde que por cada comparendo se debe realizar un curso. Máximo 3 comparendos.</p>
                    </div>
                </div>
                <hr>
                <!-- Datos del Solicitante -->
                <div class="col-12">
                    <h5 class="mb-4">Datos del Solicitante</h5>
                    <div class="row">
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
                        <div class="mb-4 col-12 col-md-6">
                            <label class="form-label">Tipo de documento <span class="required_flied">*</span></label>
                            <select class="select2 form-select" required name="tipo_doc_cliente">
                                <option value="CC">Cédula de ciudadanía</option>
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
                    </div>
                    <div class="resultadoCitas"></div>
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
                        <span class="text-center f18">Acepto las <a href="https://curso-comparendo.com/politica-tratamiento-de-datos/" target="_blank">políticas de tratamiento de datos.</a></span>
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
                        <span class="text-center f18">Soy consciente que debo llegar <strong>30 MINUTOS ANTES</strong> de la cita, de lo contrario no podré tomar el curso.</span>
                    </label>
                </div>
            </div>
            <input type="hidden" name="utm_source" value="{{ session('utm_source', 'Desconocido') }}">
            <input type="hidden" name="url_variables" value="{{ json_encode(request()->except('utm_source')) }}">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary boton_submit text-center">Agendar cita</button>
            </div>
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
        .accordion-item {
            border: 1px solid #e5e9f2;
            border-radius: 5px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .accordion-button {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
        }
        .accordion-button:not(.collapsed) {
            background-color: #e9ecef;
        }
        #addComparendo {
            background-color: #d9edf7;
            border-color: #bce8f1;
            color: #31708f;
            border-radius: 20px;
            padding: 8px 16px;
        }
        .accordion-body {
            padding-top: 2rem; /* Más padding superior desde "Día de la Cita" */
        }
        .removeComparendo {
            margin-top: 0.5rem; /* Espacio reducido con el p.text-muted anterior */
        }
        .tagify__dropdown {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
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
    <script>
    const ID_CIUDAD_GLOBAL = {{ (int) $id_ciudad }};
</script>
</body>
</html>