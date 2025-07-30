$(function () {

    //===================================================================================================================================================================================================================================================================================================================================================================================================================
    //                   FUNCIONES GENERALES
    //===================================================================================================================================================================================================================================================================================================================================================================================================================

    // Ajustes globales de Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    /**
     * Copia al portapapeles el texto que recibe como parámetro.
     * @param {string} texto - Lo que queremos copiar.
     */
    window.copiarContenido = async function (texto) {
        try {
            await navigator.clipboard.writeText(texto);
            // Toast de éxito con Toastr
            toastr.info(`Documento copiado: ${texto}`, 'Copiado');

        } catch (err) {
            console.error('Error al copiar:', err);
            // Toast de error con Toastr
            toastr.error('No se pudo copiar el documento.', 'Error');
        }
    };

    //Variables Globales
    let table_horarios = false;
    let table_festivos = false;
    let table_servicios = false;
    let table_estados = false;
    let table_usuarios = false;
    // Variables para los filtros
    let filtroDia = '';
    let filtroDiaEnd = '';
    let filtroSede = '';
    let filtroEstado = '';
    let filtroEstadoVerificado = '';
    let filtroResponsable = '';
    let filtroServicioLiquidador = '';
    let filtroEstadoValidacionLiquidador = '';
    let filtroEstadoPagoLiquidador = '';
    let filtroOrigen = '';
    let filtroSearch = '';
    let tipoCita = '';
    let filtroAgente = '';

    //Filtros de paquetes
    let filtroNombre = '';
    let filtroTipo = '';
    let filtroValor = '';

    let MATERIAL3 = {
        primary: getComputedStyle(document.documentElement).getPropertyValue('--color-azul-500').trim(),
        secondary: getComputedStyle(document.documentElement).getPropertyValue('--color-verde').trim(),
        tertiary: getComputedStyle(document.documentElement).getPropertyValue('--color-azul-300').trim(),
        neutral: getComputedStyle(document.documentElement).getPropertyValue('--color-gris-oscuro').trim(),
    };

    // (Opcional) función para generar tonos (0–100).
    // Aquí podrías integrar una librería como TonalPalette de Material3
    function tone(color, shade) {
        // Implementa tu conversión HSL/CAM16 → tono M3
        // Por simplicidad usamos siempre el mismo color:
        return color;
    }

    $('#formNuevoCliente').on('submit', function (event) {
        event.preventDefault(); // Evita que el formulario se envíe inmediatamente
        let isValid = true;
        let action = $(this).attr('action');
        let form = $(this);
        // Recorrer todos los elementos con el atributo 'required'
        form.find('[required]').each(function () {
            if ($(this).val() === "" || (this.type === "checkbox" && !$(this).is(':checked'))) {
                isValid = false;
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '');
            }
        });
        // Si todos los campos son válidos, proceder con el envío por AJAX
        if (isValid) {
            if ($('#phoneCliente').length) {
                const fullPhoneNumber = phoneInput.getNumber();
                $('#phoneCliente').val(fullPhoneNumber);
            }
            $.ajax({
                url: action,
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    if (response.validate) {

                        // 2) Mostrar un SweetAlert sencillo
                        Swal.fire({
                            icon: 'success',
                            title: '¡Cliente creado!',
                            text: response.text,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        }).then(() => {
                            // 3) Cerrar modal
                            $('#modalNuevoCliente').modal('hide');
                            // 4) Limpiar el formulario
                            $('#formNuevoCliente')[0].reset();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.text
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo guardar el cliente'
                    });
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '¡Por favor, completa todos los campos requeridos!'
            });
        }
    });
    $('#formNuevoVehiculo').on('submit', function (event) {
        event.preventDefault(); // Evita que el formulario se envíe inmediatamente
        let isValid = true;
        let action = $(this).attr('action');
        let form = $(this);
        // Recorrer todos los elementos con el atributo 'required'
        form.find('[required]').each(function () {
            if ($(this).val() === "" || (this.type === "checkbox" && !$(this).is(':checked'))) {
                isValid = false;
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '');
            }
        });
        // Si todos los campos son válidos, proceder con el envío por AJAX
        if (isValid) {
            $.ajax({
                url: action,
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    if (response.validate) {

                        // 2) Mostrar un SweetAlert sencillo
                        Swal.fire({
                            icon: 'success',
                            title: '¡El vehiculo ha sido agregado!',
                            text: response.text,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        }).then(() => {
                            checkAndLoadVehiculos();

                            // 3) Cerrar modal
                            $('#modalNuevoVehiculo').modal('hide');
                            // 4) Limpiar el formulario
                            $('#formNuevoVehiculo')[0].reset();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.text
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo agregar el vehiculo'
                    });
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '¡Por favor, completa todos los campos requeridos!'
            });
        }
    });

    $('.send_form').on('submit', function (event) {
        event.preventDefault(); // Evita que el formulario se envíe inmediatamente
        let isValid = true;
        let action = $(this).attr('action');
        let form = $(this);
        $('.boton_submit').prop('disabled', true); // Deshabilitar el botón de envío
        // Recorrer todos los elementos con el atributo 'required'
        form.find('[required]').each(function () {
            if ($(this).val() === "" || (this.type === "checkbox" && !$(this).is(':checked'))) {
                isValid = false;
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '');
            }
        });
        // Si todos los campos son válidos, proceder con el envío por AJAX
        if (isValid) {
            if ($('#phoneCliente').length) {
                const fullPhoneNumber = phoneInput.getNumber();
                $('#phoneCliente').val(fullPhoneNumber);
            }
            $.ajax({
                url: action,
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    if (response.validate) {
                        if (response.reload) {
                            alertNotify('¡Éxito!', response.text, 'success', response, true); ('¡Éxito!', response.text, 'success', response);

                        } else {
                            alertNotify('¡Éxito!', response.text, 'success', response); ('¡Éxito!', response.text, 'success', response);
                        }
                        form[0].reset();
                        if (table_horarios !== false) {
                            table_horarios.ajax.reload();
                        }
                        if (table_festivos !== false) {
                            table_festivos.ajax.reload();
                        }
                        if (table_servicios !== false) {
                            table_servicios.ajax.reload();
                        }
                        if (table_estados !== false) {
                            table_estados.ajax.reload();
                        }
                        if (table_usuarios !== false) {
                            table_usuarios.ajax.reload();
                        }

                    } else {
                        alertNotify('¡Error!', response.text, 'error');
                    }
                },
                error: function (error) {
                    alert('Error en el envío del formulario');
                }
            });
        } else {
            alertNotify('¡Error!', '¡Por favor, completa todos los campos requeridos!', 'error');
            $('.boton_submit').prop('disabled', false); // habilitar el botón de envíob
        }
    });
    //Funciones Generales
    function alertNotify(title, text, icon, response = false, reload = false) {
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ml-1 d-none'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (icon === 'success') {
                    if ($('.content_sede').length) {
                        if (response?.id) {
                            // Si el usuario tiene permiso para editar sedes, lo redirige a la edición;
                            // de lo contrario, lo redirige a la vista.
                            if (typeof canEditSedes !== 'undefined' && canEditSedes) {
                                window.location = url + '/dashboard/sedes/edit/' + response.id;
                            } else {
                                window.location = url + '/dashboard/sedes/view/' + response.id;
                            }
                        }
                    }
                    if ($('.content_clientes').length) {
                        if (response?.id) {
                            if (typeof canEditClientes !== 'undefined' && canEditClientes) {
                                window.location = url + '/dashboard/clientes/edit/' + response.id;
                            } else {
                                window.location = url + '/dashboard/clientes/view/' + response.id;
                            }
                        }
                    }
                    if ($('.content_vehiculos').length) {
                        if (response?.id) {
                            if (typeof canEditVehiculos !== 'undefined' && canEditVehiculos) {
                                window.location = url + '/dashboard/clientes/edit_vehiculos/' + response.id;
                            } else {
                                window.location = url + '/dashboard/clientes/get_vehiculo/' + response.id;
                            }
                        }
                    }
                    if ($('.content_citas').length) {
                        if (response?.id) {
                            if (typeof canEditCitas !== 'undefined' && canEditCitas) {
                                window.location = url + '/dashboard/citas/edit/' + response.id;
                            } else {
                                window.location = url + '/dashboard/citas/view/' + response.id;
                            }
                        }
                    }
                    if ($('.content_citas_edit_estados').length) {
                        if (response?.id) {
                            // Por ejemplo: si el usuario tiene permiso para editar estados, se lo redirige a la configuración de citas;
                            // de lo contrario, regresa al listado de citas.
                            if (typeof canEditEstadoCitas !== 'undefined' && canEditEstadoCitas) {
                                window.location = url + '/dashboard/citas/configuracion';
                            } else {
                                window.location = url + '/dashboard/citas';
                            }
                        }
                    }
                    if (reload) {
                        location.reload(); // Recarga la página
                    }
                }
            }
        });
    }
    //INICIO: FORMULARIO PRINCIPAL AJAX------------------------------------------------------------------------
    //Selectes con funcionaliades
    if ($('.select2').length) {
        let texto = 'Seleccione una opción';
        $('.select2').each(function () {
            var $this = $(this);
            var placeholder = $this.attr('placeholder');
            if (placeholder) {
                texto = placeholder;
            }
            $this.wrap('<div class="position-relative"></div>').select2({
                placeholder: placeholder,
                dropdownParent: $this.parent()
            });
        });
    }

    //===================================================================================================================================================================================================================================================================================================================================================================================================================
    //                   FUNCIONES ESPECIFICAS
    //===================================================================================================================================================================================================================================================================================================================================================================================================================

    //INICIO: SEDES------------------------------------------------------------------------
    //TABLAS DE SEDES
    if ($('.datatables-sedes').length) {
        let table = $('.datatables-sedes').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: url + '/dashboard/sedes/get_sedes',
                type: "POST"
            },
            column: [{ data: '' }],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return '<span class="badge bg-label-dark">' + full.id_sede + '</span>';
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, full, meta) {
                        return full.idrun_sede;
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, full, meta) {
                        return full.nombre_sede;
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, full, meta) {
                        return full.tel_sede;
                    }
                },
                {
                    targets: 4,
                    render: function (data, type, full, meta) {
                        if (full.estado_sede == 'Activo') {
                            return '<span class="badge bg-label-success">' + full.estado_sede + '</span>';
                        }
                        return '<span class="badge bg-label-danger">' + full.estado_sede + '</span>';
                    }
                },
                {
                    targets: 5,
                    render: function (data, type, full, meta) {
                        return `
                        <div class="d-flex justify-content-end">
                            ${(canEditSedes) ? `
                            <a href="${url}/dashboard/sedes/edit/${full.id_sede}" class="btn btn-icon btn-label-primary waves-effect me-2">
                                <i class="tf-icons ti ti-edit ti-md"></i>
                            </a>` : ``}
                            ${(canDeleteSedes) ? `
                            <button type="button" data-id="${full.id_sede}" class="btn_delete_sede btn btn-icon btn-label-danger waves-effect">
                                <i class="tf-icons ti ti-trash ti-md"></i>
                            </button>` : ``}
                        </div>`	;
                    }
                },
            ],
            pagingType: "simple"
        });
        //ELIMINAR SEDE
        $('.datatables-sedes').on('click', '.btn_delete_sede', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: '¿Estas seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '¡Sí, bórralo!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ml-1'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url + '/dashboard/sedes/delete_sede',
                        type: 'POST',
                        data: { id: id },
                        success: function (data) {
                            if (data.validate) {
                                table.ajax.reload();
                            } else {
                                alertNotify('¡Error!', data.text, 'error');
                            }
                        }
                    });
                }
            });
        });
    }
    //Tabla de horarios
    if ($('.datatables-horarios').length) {
        table_horarios = $('.datatables-horarios').DataTable({
            lengthChange: true,
            searching: false,
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: url + '/dashboard/sedes/get_horarios',
                type: "POST"
            },
            column: [{ data: '' }],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return full.rango_horario;
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, full, meta) {
                        return `
                        <div class="d-flex justify-content-end">
                            ${(canDeleteHorarios) ? `
                            <button type="button" data-id="${full.id_horario}" class="btn_delete_horario btn btn-icon btn-label-danger waves-effect">
                                <i class="tf-icons ti ti-trash ti-md"></i>
                            </button>` : ``}
                        </div>`	;
                    }
                },
            ],
            pagingType: "simple"
        });
        //ELIMINAR SEDE
        $('.datatables-horarios').on('click', '.btn_delete_horario', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: '¿Estas seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '¡Sí, bórralo!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ml-1'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url + '/dashboard/sedes/delete_horario',
                        type: 'POST',
                        data: { id: id },
                        success: function (data) {
                            if (data.validate) {
                                table_horarios.ajax.reload();
                            } else {
                                alertNotify('¡Error!', data.text, 'error');
                            }
                        }
                    });
                }
            });
        });
    }
    //Tabla de festiuvos
    if ($('.datatables-festivos').length) {
        table_festivos = $('.datatables-festivos').DataTable({
            lengthChange: false,
            searching: false,
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: url + '/dashboard/sedes/get_festivos',
                type: "POST"
            },
            column: [{ data: '' }],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return full.fecha;
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, full, meta) {
                        return `
                        <div class="d-flex justify-content-end">
                            ${(canDeleteFestivos) ? `
                            <button type="button" data-id="${full.id}" data-fecha="${full.fecha}" class="btn_delete_festivo btn btn-icon btn-label-danger waves-effect">
                                <i class="tf-icons ti ti-trash ti-md"></i>
                            </button>` : ``}
                        </div>`	;
                    }
                },
            ],
            pagingType: "simple"
        });
        //ELIMINAR SEDE
        $('.datatables-festivos').on('click', '.btn_delete_festivo', function () {
            let id = $(this).data('id');
            let fecha = $(this).data('fecha');
            Swal.fire({
                title: '¿Estas seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '¡Sí, bórralo!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ml-1'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url + '/dashboard/sedes/delete_festivos',
                        type: 'POST',
                        data: { id: id, fecha: fecha },
                        success: function (data) {
                            if (data.validate) {
                                ;
                                table_festivos.ajax.reload();
                            } else {
                                alertNotify('¡Error!', data.text, 'error');
                            }
                        }
                    });
                }
            });
        });
    }
    //Tabla de festiuvos
    if ($('.datatables-servicios').length) {
        table_servicios = $('.datatables-servicios').DataTable({
            lengthChange: false,
            searching: false,
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: url + '/dashboard/sedes/get_servicio',
                type: "POST"
            },
            column: [{ data: '' }],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return full.tipo_servicio;
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, full, meta) {
                        return full.desc_servicio;
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, full, meta) {
                        return `
                        <div class="d-flex justify-content-end">
                            ${(canDeleteServicios) ? `
                            <button type="button" data-id="${full.id_servicio}" class="btn_delete_servicio btn btn-icon btn-label-danger waves-effect">
                                <i class="tf-icons ti ti-trash ti-md"></i>
                            </button>` : ``}
                        </div>`	;
                    }
                },
            ],
            pagingType: "simple"
        });
        //ELIMINAR SEDE
        $('.datatables-servicios').on('click', '.btn_delete_servicio', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: '¿Estas seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '¡Sí, bórralo!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ml-1'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url + '/dashboard/sedes/delete_servicio',
                        type: 'POST',
                        data: { id: id },
                        success: function (data) {
                            if (data.validate) {
                                table_servicios.ajax.reload();
                            } else {
                                alertNotify('¡Error!', data.text, 'error');
                            }
                        }
                    });
                }
            });
        });
    }
    //FIN: HORARIOS------------------------------------------------------------------------
    if ($('.form-repeater').length) {
        a_dias.forEach(function (item, index) {
            $('.repeat_' + item).repeater({
                show: function () {
                    $(this).slideDown();
                },
                hide: function (deleteElement) {
                    if (confirm('¿Estás seguro de que quieres eliminar este elemento?')) {
                        $(this).slideUp(deleteElement);
                    }
                }
            });


        });
    }
    //FIN: SEDES------------------------------------------------------------------------
    //INICIO: CLIENTES------------------------------------------------------------------------
    if ($('.datatables-clientes').length) {
        let table_clientes = $('.datatables-clientes').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            pageLength: 50, // Cambiar la paginación a 50 entradas
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json' // Configuración de idioma español
            },
            ajax: {
                url: url + '/dashboard/clientes/get_clientes',
                type: "POST"
            },
            column: [{ data: '' }],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return full.nombre_cliente + ' ' + full.apellido_cliente;
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, full, meta) {
                        return '<span class="badge bg-label-dark">' + full.tipo_doc_cliente + ': ' + full.doc_cliente + '</span>';
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, full, meta) {
                        return full.telefono_cliente;
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, full, meta) {
                        return '<a href="mailto:' + full.email_cliente + '">' + full.email_cliente + '</a>';
                    }
                },
                {
                    targets: 4,
                    render: function (data, type, full, meta) {
                        return `
                        <div class="d-flex justify-content-end">
                            ${(canEditClientes) ? `
                                <a href="${url}/dashboard/clientes/edit/${full.id_cliente}" class="btn btn-icon btn-label-primary waves-effect me-2">
                                    <i class="tf-icons ti ti-edit ti-md"></i>
                                </a>` : ` <a href="${url}/dashboard/clientes/view/${full.id_cliente}" class="btn btn-icon btn-label-primary waves-effect me-2">
                                    <i class="tf-icons ti ti-search ti-md"></i>
                                </a>`}
                             ${(canDeleteClientes) ? `
                                <button type="button" data-id="${full.id_cliente}" class="btn_delete_cliente btn btn-icon btn-label-danger waves-effect">
                                    <i class="tf-icons ti ti-trash ti-md"></i>
                                </button>` : ``}
                        </div>`	;
                    }
                },
            ],
            pagingType: "simple"
        });
        $('.datatables-clientes').on('click', '.btn_delete_cliente', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: '¿Estas seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '¡Sí, bórralo!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ml-1'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url + '/dashboard/clientes/delete_cliente',
                        type: 'POST',
                        data: { id: id },
                        success: function (data) {
                            if (data.validate) {
                                table_clientes.ajax.reload();
                            } else {
                                alertNotify('¡Error!', data.text, 'error');
                            }
                        }
                    });
                }
            });
        });
    }
    //Vehiculos
    if ($('.datatables-vehiculos').length) {
        let table_vehiculos = $('.datatables-vehiculos').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: url + '/dashboard/clientes/get_vehiculos',
                type: "POST"
            },
            column: [{ data: '' }],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return '<strong>' + full.tipo_vehiculo + '</strong> (' + full.modelo_vehiculo + ')';
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, full, meta) {
                        return '<span class="badge bg-label-dark">' + full.placa_vehiculo + '</span>';
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, full, meta) {
                        return '<span class="badge bg-label-primary">' + full.tipo_doc_cliente + ': ' + full.doc_cliente + '</span>';
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, full, meta) {
                        return `
                        <div class="d-flex justify-content-end">
                            ${(canEditVehiculos) ? `
                            <a href="${url}/dashboard/clientes/edit_vehiculos/${full.id_vehiculo}" class="btn btn-icon btn-label-primary waves-effect me-2">
                                <i class="tf-icons ti ti-edit ti-md"></i>
                            </a>` : `
                            <a href="${url}/dashboard/clientes/get_vehiculo/${full.id_vehiculo}" class="btn btn-icon btn-label-primary waves-effect me-2">
                                <i class="tf-icons ti ti-search ti-md"></i>
                            </a>`}
                            ${(canDeleteVehiculos) ? `
                            <button type="button" data-id="${full.id_vehiculo}" class="btn_delete_vehiculo btn btn-icon btn-label-danger waves-effect">
                                <i class="tf-icons ti ti-trash ti-md"></i>
                            </button>` : ``}
                        </div>`	;
                    }
                }
            ],
            pagingType: "simple"
        });
        $('.datatables-vehiculos').on('click', '.btn_delete_vehiculo', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: '¿Estas seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '¡Sí, bórralo!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ml-1'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url + '/dashboard/clientes/delete_vehiculo',
                        type: 'POST',
                        data: { id: id },
                        success: function (data) {
                            if (data.validate) {
                                table_vehiculos.ajax.reload();
                            } else {
                                alertNotify('¡Error!', data.text, 'error');
                            }
                        }
                    });
                }
            });
        });
    }
    //Select para buscar cliente
    if ($('.select_search_cliente').length) {
        $('.select_search_cliente').select2({
            ajax: {
                url: url + '/dashboard/clientes/get_clientes_in_vehicle',
                dataType: 'json'
            }
        });

    }
    if ($('.select_search_cliente_modal').length) {
        $('.select_search_cliente_modal').select2({
            dropdownParent: $('#modalNuevoVehiculo'),
            ajax: {
                url: url + '/dashboard/clientes/get_clientes_in_vehicle',
                dataType: 'json'
            },
        });
    }
    //INICIO: CITAS------------------------------------------------------------------------
    let currentServiceType = null;
    if ($('.content_citas').length) {
        let horarios = [];
        const $selectSede = $('#selectSede');
        const $inputDia = $('#citaDia');
        const $selectHora = $('#citaHora');

        // 1) Localización de datepicker — se define una vez al cargar la página
        $.fn.datepicker.dates['es'] = {
            days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"],
            daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
            daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sá"],
            months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
            today: "Hoy", clear: "Limpiar",
            format: "dd/mm/yyyy", titleFormat: "MM yyyy", weekStart: 0
        };

        // Función para “resetear” el pickers y selects
        function resetDateAndTime() {
            // Destruyo cualquier datepicker existente y vuelvo a dejarlo en blanco y deshabilitado
            $inputDia.datepicker('destroy').val('').prop('disabled', true);

            // Limpio el select de horas y lo deshabilito
            $selectHora
                .html('<option value="">Seleccione una opción</option>')
                .prop('disabled', true);
        }

        // Al cambiar de sede:
        $selectSede.on('change', function () {
            const sedeId = $(this).val();
            const festivos = $(this).find(':selected').data('festivos') || [];
            const diasFijos = [0, 1, 2, 3, 4, 5, 6];

            // 2) Reinicio el flujo: limpio fecha y hora
            resetDateAndTime();

            // 3) (Opcional) Ocultar o limpiar select de vehículo
            hideVehiculoSelect();

            // 4) Cargo parámetros extra si tienes lógica aparte
            obtenerServicioSede(sedeId);

            // 5) Pido horarios vía AJAX
            $.ajax({
                url: `${url}/dashboard/citas/get_horarios`,
                type: 'POST',
                data: { id: sedeId },
                dataType: 'json',
                success: function (response) {
                    horarios = response.horarios || [];

                    // Construyo los arrays de días habilitados para el datepicker
                    let disponibles = new Set();
                    let disponiblesDP = new Set();

                    horarios.forEach(item => {
                        let d = parseInt(item.dia_sede_horario, 10);
                        disponibles.add(d);
                        // Para datepicker el domingo es 0, tu API usa 7
                        disponiblesDP.add(d === 7 ? 0 : d);
                    });

                    // Días que el datepicker debe deshabilitar
                    const diasNo = diasFijos.filter(d => !disponiblesDP.has(d));

                    // 6) Inicializo el datepicker ya limpio
                    $inputDia
                        .prop('disabled', false)
                        .datepicker({
                            todayHighlight: true,
                            daysOfWeekDisabled: diasNo,
                            orientation: isRtl ? 'auto right' : 'auto left',
                            language: 'es',
                            startDate: canEditCualquierFecha ? null : new Date(),
                            beforeShowDay: date => {
                                let iso = date.toISOString().slice(0, 10);
                                if (festivos.includes(iso)) {
                                    return { enabled: false, classes: 'disabled-date', tooltip: 'No disponible' };
                                }
                                return true;
                            }
                        })
                        .off('changeDate')  // aseguro no duplicar handlers
                        .on('changeDate', function (e) {
                            if (!e.date) return;

                            // Calculo si el día es hoy para filtrar horarios pasados
                            const hoy = new Date();
                            const sel = e.date;
                            const mismoDia = (
                                hoy.getFullYear() === sel.getFullYear() &&
                                hoy.getMonth() === sel.getMonth() &&
                                hoy.getDate() === sel.getDate()
                            );

                            const diaSemana = sel.getDay() === 0 ? 7 : sel.getDay();

                            // Filtro horarios según si ya pasó la hora
                            let disp = horarios.filter(item => {
                                if (item.dia_sede_horario != diaSemana) return false;
                                if (!mismoDia) return true;

                                // Si es hoy, solo los que inician después de la hora actual
                                const [h, m, s] = item.inicio_horario.split(':').map(Number);
                                const inicio = new Date(); inicio.setHours(h, m, s, 0);
                                return inicio > hoy;
                            });

                            // 7) Pinto el select de horas
                            let opts = '<option value="">Seleccione una opción</option>';
                            disp.forEach(i => {
                                opts += `<option value="${i.id_sede_horario}">${i.rango_horario}</option>`;
                            });
                            $selectHora.html(opts).prop('disabled', false);

                            // 8) Si estamos en edición, selecciono el rango guardado
                            if ($('.content_citas_edit').length) {
                                $selectHora.find(`option:contains("${rango_horario.trim()}")`)
                                    .prop('selected', true)
                                    .trigger('change');
                            }
                        });

                    // 9) Si es edición, precargo fecha y disparo changeDate
                    if ($('.content_citas_edit').length) {
                        $inputDia.datepicker('setDate', new Date(reserva_cita))
                            .trigger('changeDate');
                    }
                },
                error: console.error
            });
        });

        // Si estamos editando, disparo el cambio inicial
        if ($('.content_citas_edit').length) {
            $selectSede.trigger('change');
        }
    }


    function obtenerServicioSede(idSede) {
        $.ajax({
            url: url + '/dashboard/sedes/get_servicio_by_id_sede', // Endpoint a crear en el backend
            type: 'POST',
            data: { id_sede: idSede },
            dataType: 'json',
            success: function (response) {
                if (response.validate) {
                    currentServiceType = response.tipo_servicio[0].tipo_servicio;
                } else {
                    currentServiceType = null;
                }
                // Verificar si se deben cargar los vehículos
                checkAndLoadVehiculos();
            },
            error: function () {
                currentServiceType = null;
                checkAndLoadVehiculos();
            }
        });
    }

    // Cuando cambia el cliente, volvemos a verificar si se deben cargar vehículos
    $('.select_search_cliente').change(function () {
        checkAndLoadVehiculos();
        // console.log('Se cambió el cliente');
    });


    function checkAndLoadVehiculos() {
        let clientId = $('.select_search_cliente').val();
        // console.log('Cliente seleccionado:', clientId);
        // console.log('Tipo de servicio actual:', currentServiceType);
        // Condiciones:
        // 1. currentServiceType debe existir y ser "CIA"
        // 2. Debe haber un cliente seleccionado
        if (clientId) {
            loadVehiculos(clientId);
        } else {
            hideVehiculoSelect();
        }
    }

    // Función para ocultar y limpiar el select de vehículos
    function hideVehiculoSelect() {
        $('#divSelectVehiculo').hide();
        $('#selectVehiculo').html('<option value="">Seleccionar vehículo</option>');
    }


    // Función para cargar los vehículos asociados a un cliente
    function loadVehiculos(clientId) {
        $.ajax({
            url: url + '/dashboard/clientes/get_vehiculos_by_id_cliente',
            type: 'POST',
            data: { id_cliente: clientId },
            dataType: 'json',
            success: function (response) {
                if (response.validate) {
                    let html = '<option value="">Seleccionar vehículo</option>';

                    response.vehiculos.forEach(function (vehiculo) {
                        html += '<option value="' + vehiculo.id_vehiculo + '">' +
                            vehiculo.placa_vehiculo + ' - ' + vehiculo.tipo_vehiculo +
                            '</option>';
                    });
                    $('#selectVehiculo').html(html);
                    $('#divSelectVehiculo').show();
                    // Inicializa select2 (o refrescalo)
                    $('#selectVehiculo').select2();

                    // Si estamos en modo edición y tenemos un vehículo asignado, lo seleccionamos
                    if ($('.content_citas_edit').length && typeof idVehiculoCita !== 'undefined' && idVehiculoCita) {
                        $('#selectVehiculo option').each(function () {
                            if ($(this).val() == idVehiculoCita) {
                                $(this).prop('selected', true);
                                $('#selectVehiculo').trigger('change');
                                return false; // Sale del each
                            }
                        });
                    }
                } else {
                    hideVehiculoSelect();
                }
            },
            error: function () {
                hideVehiculoSelect();
            }
        });
    }

    // Función para calcular si el color es claro u oscuro
    function getContrastingTextColor(bgColor) {

        // Validar si bgColor es un valor válido
        if (!bgColor || typeof bgColor !== 'string') {
            return '#000000'; // Texto negro por defecto si el color es inválido
        }
        // Quitar el símbolo '#' si está presente
        const hex = bgColor.replace('#', '');

        // Convertir el color HEX a valores RGB
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);

        // Calcular la luminancia relativa según el estándar WCAG
        const luminance = 0.2126 * r + 0.7152 * g + 0.0722 * b;

        // Determinar si el fondo es claro u oscuro
        return luminance > 128 ? '#000000' : '#FFFFFF'; // Texto negro si es claro, blanco si es oscuro
    }
    //TABLA DE ESTADOS DE CITAS
    if ($('.datatables-estados').length) {
        table_estados = $('.datatables-estados').DataTable({
            lengthChange: false,
            searching: false,
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: url + '/dashboard/citas/get_estados',
                type: "POST"
            },
            column: [{ data: '' }],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, full, meta) {
                        const bgColor = full.color_estado; // Color de fondo del estado
                        const textColor = getContrastingTextColor(bgColor); // Color de texto calculado
                        return '<span class="badge bg-label-dark" style="background-color: ' + bgColor + ' !important; color: ' + textColor + '!important;">' + full.nombre_estado + '</span>';
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, full, meta) {
                        return full.desc_estado;
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, full, meta) {
                        return `
                        <div class="d-flex justify-content-end">
                            ${(canEditEstadoCitas) ? `
								<a href="${url}/dashboard/citas/edit_estados/${full.id_estado}" class="btn btn-icon btn-label-primary waves-effect me-2">
                                    <i class="tf-icons ti ti-edit ti-md"></i>
                                </a>` : ``}
                            ${(canDeleteEstadoCitas) ? `
                                <button type="button" data-id="${full.id_estado}" class="btn_delete_estado btn btn-icon btn-label-danger waves-effect">
                                    <i class="tf-icons ti ti-trash ti-md"></i>
                                </button>` : ``}
                        </div>`	;
                    }
                },
            ],
            pagingType: "simple"
        });
        //ELIMINAR SEDE
        $('.datatables-estados').on('click', '.btn_delete_estado', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: '¿Estas seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '¡Sí, bórralo!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ml-1'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url + '/dashboard/citas/delete_estados',
                        type: 'POST',
                        data: { id: id },
                        success: function (data) {
                            if (data.validate) {
                                table_estados.ajax.reload();
                            } else {
                                alertNotify('¡Error!', data.text, 'error');
                            }
                        }
                    });
                }
            });
        });
    }
    var table_citas;
    //TABLAS DE CITAS
    if ($('.datatables-citas').length) {
        tipoCita = $('#tipo_cita').val();
        table_citas = $('.datatables-citas').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            searching: false,
            info: true,
            pageLength: 50, // Cambiar la paginación a 50 entradas
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json', // Configuración de idioma español
                info: "Mostrando _START_ a _END_ de _MAX_ registros",
                infoEmpty: "No hay datos disponibles",
                infoFiltered: "(filtrados de un total de _MAX_ registros)"
            },
            dom: '<"top px-4"fli>rt<"bottom"p><"clear">',
            ajax: {
                url: url + '/dashboard/citas/get_citas',
                type: "POST",
                data: function (d) {
                    d.filtro_dia = filtroDia;
                    d.filtro_dia_end = filtroDiaEnd;
                    d.filtro_sede = filtroSede;
                    d.filtro_estado = filtroEstado;
                    d.filtro_estado_verificado = filtroEstadoVerificado;
                    d.filtro_responsable = filtroResponsable;
                    d.filtro_origen = filtroOrigen;
                    d.filtro_search = filtroSearch;
                    d.filtro_agente = filtroAgente;
                    d.tipo_cita = tipoCita;
                    // Parámetros necesarios para ordenamiento
                    d.order = d.order;
                    d.columns = d.columns;
                    d.search = d.search;
                },
            },
            columns: [
                { data: 'nombre_cliente' },                 // Columna 0
                { data: null },                             // Columna 1
                {
                    data: 'nombre_sede',
                },                    // Columna 2
                { data: 'reserva_cita' },                   // Columna 3
                { data: 'fecha_create' },                   // Columna 4
                { data: 'estado_actual_nombre' },           // Columna 5
                { data: 'estado_verificado_nombre' },       // Columna 6
                {
                    data: 'id_agente_callcenter',
                    visible: canViewCallCenter
                },           // Columna 7
                {
                    data: 'nombre_servicio_liquidador',
                    visible: canViewServiciosLiquidador
                },     // Columna 8
                {
                    data: 'responsable_origen',
                    visible: canViewOrigen
                },              // Columna 9
                {
                    data: 'origen',
                    visible: canViwTag
                },                         // Columna 10
                { data: null }                              // Columna 11 (botones)
            ],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, full, meta) {
                        if (full.id_vehiculo) {
                            texto = '<h6 class="m-0">' + full.nombre_cliente + ' ' + full.apellido_cliente + '</h6><small><a href="https://www.fcm.org.co/simit/#/estado-cuenta?numDocPlacaProp=' + full.doc_cliente + '" target="_blank" rel="noreferrer">' + full.tipo_doc_cliente + full.doc_cliente + '</a> - Telf: <a href="https://wa.me/' + full.telefono_cliente + '" target="_blank">' + full.telefono_cliente + '</a></small><br><small class="text-muted">' + full.email_cliente + '</small><br><a href="https://www.fcm.org.co/simit/#/estado-cuenta?numDocPlacaProp=' + full.placa_vehiculo + '" target="_blank"> <span class="badge bg-label-dark">' + full.placa_vehiculo + '</span> </a> <small class="text-muted ml-2"> ' + full.tipo_vehiculo + '</small>';

                            // texto = '<h6 class="m-0">' + full.nombre_cliente + $full.apellido_cliente + '</h6><small>' + full.tipo_doc_cliente + ' - Tipo: ' + full.tipo_vehiculo + ' - Modelo: ' + full.modelo_vehiculo + '</small>';
                        } else {
                            texto = '<h6 class="m-0">' + full.nombre_cliente + ' ' + full.apellido_cliente + '</h6><small><a href="https://www.fcm.org.co/simit/#/estado-cuenta?numDocPlacaProp=' + full.doc_cliente + '" target="_blank" rel="noreferrer">' + full.tipo_doc_cliente + full.doc_cliente + '</a> - Telf: <a href="https://wa.me/' + full.telefono_cliente + '" target="_blank">' + full.telefono_cliente + '</a></small><br><small class="text-muted">' + full.email_cliente + '</small>';
                        }
                        return texto;
                    }
                },
                {
                    targets: 1, // Comentario Liquidador
                    orderable: false,
                    render: function (data, type, full, meta) {
                        // Si existen anotaciones, mostramos también la cantidad en un badge
                        let badgeAnotaciones = '';
                        iconColorAnotaciones = 'text-muted';
                        if (full.total_anotaciones && parseInt(full.total_anotaciones) > 0) {
                            badgeAnotaciones = `<span class="badge rounded-pill text-bg-danger badge-notifications px-1">${full.total_anotaciones}</span>`;
                            iconColorAnotaciones = 'text-success';
                        }

                        return `
                        <div style="position:relative; display:inline-block;">
                            <!-- Botón para ver el seguimiento -->
                            <button type="button"
                                class="btn btn-sm btn-light btn-open-seguimiento-modal ${iconColorAnotaciones}"
                                data-id-cita="${full.id_cita}"
                                title="Ver seguimiento">
                                <i class="ti ti-eye"></i>
                            </button>
                            ${badgeAnotaciones}
                        </div>
                      `;
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, full, meta) {

                        // Luego, actualiza el encabezado de la columna deseada:
                        var headerCell = table_citas.column(2).header(); // Cambia 7 al índice correcto
                        let html = '';
                        // Verificamos si el usuario tiene permiso para ver la sede
                        if (canViewSedeCita) {
                            headerCell.innerHTML = "Sede - Comparendo";
                            html = `<span class="badge bg-label-dark mb-1">${full.nombre_sede}</span>`;
                        } else {
                            headerCell.innerHTML = "comparendo";
                        }

                        // Badge con el nombre de la sede.

                        if (full.codigos_comparendo) {
                            let tags = [];
                            try {
                                tags = JSON.parse(full.codigos_comparendo);
                            } catch (e) {
                                tags = full.codigos_comparendo;
                            }
                            if (Array.isArray(tags) && tags.length > 0) {
                                html += '<br>';
                                // Iteramos para imprimir en grupos de 3
                                tags.forEach((tag, index) => {
                                    html += `<span class="badge bg-label-primary me-1 mb-1">${tag.value}</span>`;
                                    // Insertar salto de línea después de cada 3 badges,
                                    // pero si no es el último badge
                                    if ((index + 1) % 3 === 0 && index !== tags.length - 1) {
                                        html += '<br>';
                                    }
                                });
                            }
                        }

                        return html;
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, full, meta) {
                        let fecha = full.reserva_cita.split(" ")[0];
                        return `<h6 class="m-0">${fecha} ${full.rango_horario}</h6>`;
                    }
                },
                {
                    targets: 4,
                    render: function (data, type, full, meta) {
                        return `<h6 class="m-0">${full.fecha_create}</h6>`;
                    }
                },
                {
                    targets: 5,
                    render: function (data, type, full, meta) {
                        const bgColor = full.estado_actual_color; // Color de fondo del estado
                        const textColor = getContrastingTextColor(bgColor); // Color de texto calculado
                        return `
                    <div class="btn-group">
                    ${(canEditEstadoCitas) ? `
                        <button type="button" data-nombre_estado="${full.estado_actual_nombre}" class="btn btn-label-primary dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  style="background-color:${bgColor} !important; color: ${textColor}!important;">${full.estado_actual_nombre}</button>
                        <ul class="dropdown-menu" style="">` +
                                estados.map(estado => {
                                    return `<li><a class="dropdown-item waves-effect change_estado_cita" data-id_cita="${full.id_cita}" data-id_estado="${estado.id_estado}">${estado.nombre_estado}</a></li>`;
                                }).join('')
                                + `</ul>` : `
                        <span class="badge" style="background-color:${bgColor} !important; color: ${textColor}!important;">${full.estado_actual_nombre}</span>
                        `}
                    </div>`;
                    }
                },
                {
                    targets: 6,
                    render: function (data, type, full, meta) {
                        const bgColor = full.estado_verificado_color; // Color de fondo del estado
                        const textColor = getContrastingTextColor(bgColor); // Color de texto calculado
                        return `
                    <div class="btn-group">
                    ${(canEditEstadoCitasVerificado) ? `
                        <button type="button" data-nombre_estado="${full.estado_verificado_nombre}" class="btn btn-label-primary dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  style="background-color:${bgColor} !important; color: ${textColor}!important;">${full.estado_verificado_nombre}</button>
                        <ul class="dropdown-menu" style="">` +
                                estados.map(estado => {
                                    return `<li><a class="dropdown-item waves-effect change_estado_cita_verificado" data-id_cita="${full.id_cita}" data-id_estado="${estado.id_estado}">${estado.nombre_estado}</a></li>`;
                                }).join('')
                                + `</ul>` : `
                        <span class="badge" style="background-color:${bgColor} !important; color: ${textColor}!important;">${full.estado_verificado_nombre}</span>
                        `}
                    </div>`;
                    }
                },
                {
                    // Columna 6 -> Agente Call Center
                    targets: 7,
                    render: function (data, type, full, meta) {
                        return `
                        <div class="btn-group">
                        ${(canEditCallCenter) ? `
                            <button type="button" data-nombre_agente="${full.id_agente_callcenter}" class="btn btn-label-primary dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false" >${full.agente_callcenter}</button>
                            <ul class="dropdown-menu" style="">` +
                                agentes.map(agente => {
                                    return `<li><a class="dropdown-item waves-effect change_agente_call" data-id_cita="${full.id_cita}" data-id_agente="${agente.id}">${agente.name}</a></li>`;
                                }).join('')
                                + `</ul>` : `
                            <span class="badge bg-label-dark">${full.agente_callcenter}</span>
                            `}
                        </div>`;

                    }
                },
                {
                    targets: 8, // Servicio Liquidador
                    render: function (data, type, full, meta) {
                        var bgColor = "#e5e5e5"; // Color de fondo del servicio por defecto
                        if (full.color_servicio_liquidador) {
                            bgColor = full.color_servicio_liquidador; // Color de fondo del servicio
                        }
                        const textColor = getContrastingTextColor(bgColor); // Color de texto calculado
                        // Si no tiene servicio, mostrará “Selecciona un servicio”
                        let currentServiceName = full.nombre_servicio_liquidador
                            ? full.nombre_servicio_liquidador
                            : 'Sin servicio seleccionado';
                        return `
                        <div class="btn-group">
                        ${(canEditServiciosLiquidador) ? `
                            <button type="button" data-nombre_estado="${currentServiceName}" class="btn btn-label-primary dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false" style="background-color:${bgColor} !important; color: ${textColor}!important;">${currentServiceName}</button>
                            <ul class="dropdown-menu">` +
                                servicios_liquidador.map(serv => {
                                    return `<li><a class="dropdown-item waves-effect change_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_servicio_liquidador="${serv.id_servicio_liquidador}">${serv.nombre_servicio_liquidador}</a></li>`;
                                }).join('')
                                + `</ul>` : `
                            <span class="badge" style="background-color:${bgColor} !important; color: ${textColor}!important;">${currentServiceName}</span>
                            `}
                        </div>
                      `;
                    }
                },
                {
                    targets: 9,
                    render: function (data, type, full, meta) {
                        if (full.responsable_origen == 'Desconocido') {
                            return `<span class="badge bg-label-dark">${full.responsable_origen}</span>`;
                        } else if (full.responsable_origen == 'Sede') {
                            return `<span class="badge bg-label-info">${full.responsable_origen}</span>`;
                        } else {
                            return `<span class="badge bg-label-primary">${full.responsable_origen}</span>`;
                        }
                    }
                },
                {
                    targets: 10,
                    render: function (data, type, full, meta) {
                        if (full.origen == null || full.origen == 'null' || full.origen == 'Desconocido') {
                            return `<span class="badge bg-label-secondary">${full.origen}</span> <br>
                        <small class="text-muted">${full.creado_por}</small>`;
                        } else if (full.origen == 'QR' || full.origen == 'qr' || full.origen == 'Qr' || full.origen == 'QRCode' || full.origen == 'qrcode') {
                            return `<span class="badge bg-label-info">${full.origen}</span> <br>
                        <small class="text-muted">${full.creado_por}</small>`;
                        } else if (full.origen == 'Curso Comparendo') {
                            return `<span class="badge bg-label-primary">${full.origen}</span> <br>
                        <small class="text-muted">${full.creado_por}</small>`;
                        } else {
                            return `<span class="badge bg-label-success">${full.origen}</span> <br>
                        <small class="text-muted">${full.creado_por}</small>`;
                        }
                    }
                },
                {
                    orderable: false,
                    targets: 11,
                    render: function (data, type, full, meta) {
                        return `
                    <div class="d-flex justify-content-end">
                        ${(canEditCitas) ? `
                        <a href="${url}/dashboard/citas/edit/${full.id_cita}" class="btn btn-icon btn-label-primary waves-effect me-2">
                            <i class="tf-icons ti ti-edit ti-md"></i>
                        </a>` :
                                `<a href="${url}/dashboard/citas/view/${full.id_cita}" class="btn btn-icon btn-label-primary waves-effect me-2">
                                    <i class="tf-icons ti ti-search ti-md"></i>
                                </a>`}

                        ${(canDeleteCitas) ? `
                        <button type="button" data-id="${full.id_cita}" class="btn_delete_cita btn btn-icon btn-label-danger waves-effect">
                            <i class="tf-icons ti ti-trash ti-md"></i>
                        </button>` : ''}

                    </div>`	;
                    }
                }
            ],
            createdRow: function (row, data) {
                if (data.estado_actual_nombre == "Duplicado") {
                    $(row).css('background-color', 'rgba(255, 224, 224, 0.5)');
                }
            },
            pagingType: "simple"
        });

        // Eventos para los filtros
        $('#filtro-ayer').on('click', function () {
            const ayer = new Date();
            ayer.setDate(ayer.getDate() - 1);
            filtroDia = formatDate(ayer);
            table_citas.ajax.reload();
        });
        $('#filtro-hoy').on('click', function () {
            const hoy = new Date();
            filtroDia = formatDate(hoy);
            table_citas.ajax.reload();
        });
        $('#filtro-manana').on('click', function () {
            const manana = new Date();
            manana.setDate(manana.getDate() + 1);
            filtroDia = formatDate(manana);
            table_citas.ajax.reload();
        });
        $('#filtro-fecha').on('change', function () {
            filtroDia = $(this).val();
            table_citas.ajax.reload();
        });
        $('#filtro-fecha-end').on('change', function () {
            filtroDiaEnd = $(this).val();
            table_citas.ajax.reload();
        });
        $('#filtro-sede').on('change', function () {
            filtroSede = $(this).val();
            table_citas.ajax.reload();
        });
        $('#filtro-estado').on('change', function () {
            filtroEstado = $(this).val();
            table_citas.ajax.reload();
        });
        $('#filtro-estado-verificado').on('change', function () {
            filtroEstadoVerificado = $(this).val();
            table_citas.ajax.reload();
        });
        $('#filtro-responsable').on('change', function () {
            filtroResponsable = $(this).val();
            table_citas.ajax.reload();
        });
        $('#filtro-agente').on('change', function () {
            filtroAgente = $(this).val();
            table_citas.ajax.reload();
        });
        $('#filtro-origen').on('change', function () {
            filtroOrigen = $(this).val();
            table_citas.ajax.reload();
        });
        $('#woow-search-citas').on('keyup', function () {
            filtroSearch = $(this).val();
            table_citas.ajax.reload();
        });
        $('#filtro-reset').on('click', function () {
            filtroDia = '';
            filtroDiaEnd = '';
            filtroSede = '';
            filtroEstado = '';
            filtroEstadoVerificado = '';
            filtroResponsable = '';
            filtroOrigen = '';
            filtroSearch = '';
            $('#filtro-dia').val("").trigger('input');
            $('#filtro-dia-end').val("").trigger('input');
            $('#filtro-sede').val("").trigger('change');
            $('#filtro-estado').val("").trigger('change');
            $('#filtro-responsable').val("").trigger('change');
            $('#filtro-estado-verificado').val("").trigger('change');
            $('#filtro-origen').val("").trigger('change');
            $('#woow-search-citas').val("").trigger('input');
            table_citas.ajax.reload();
        });
        // Cuando se hace clic en el botón para abrir el modal de seguimiento
        $('.datatables-citas').on('click', '.btn-open-seguimiento-modal', function () {
            // Se obtiene el ID de la cita desde el atributo data-id-cita del botón
            let idCita = $(this).data('id-cita');

            // Se realiza la petición AJAX para obtener el HTML del seguimiento y el formulario
            $.ajax({
                url: url + '/dashboard/citas/get_seguimiento_cita_con_actualizacion', // Asegúrate de que 'url' está definida con la ruta base
                method: 'POST',
                data: { id_cita: idCita },
                success: function (response) {
                    if (!response.validate) {
                        return Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.text || 'No se pudo obtener el seguimiento.',
                            confirmButtonText: 'OK',
                            customClass: { confirmButton: 'btn btn-primary' }
                        });
                    }

                    Swal.fire({
                        title: 'Seguimiento de la Cita',
                        html: response.html,
                        width: '80%',
                        showCancelButton: true,
                        cancelButtonText: 'Cerrar',
                        showConfirmButton: false,
                        customClass: { cancelButton: 'btn btn-outline-danger ml-1' }
                    });

                    // Espera un tick para que SweetAlert pinte el modal en el DOM
                    setTimeout(() => {
                        const $form = $('#form-seguimiento-modal');
                        const $submit = $form.find('button[type="submit"]');

                        // 1) Quita handlers previos
                        $form.off('submit');

                        // 2) Ata uno nuevo, pero con bloqueo de reenvío
                        $form.on('submit', function (e) {
                            e.preventDefault();

                            // Si ya se envió, cortamos aquí
                            if ($submit.data('submitted')) {
                                return;
                            }

                            // Bloqueamos reenvíos
                            $submit.data('submitted', true);
                            $submit.prop('disabled', true);

                            // Serializar y enviar
                            const formData = $form.serialize();
                            $.ajax({
                                url: url + '/dashboard/citas/save_seguimiento',
                                method: 'POST',
                                data: formData,
                                success: function (resp) {
                                    if (resp.validate) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Éxito',
                                            text: resp.text,
                                            confirmButtonText: 'OK',
                                            customClass: { confirmButton: 'btn btn-primary' }
                                        });
                                        table_citas.ajax.reload();
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: resp.text,
                                            confirmButtonText: 'OK',
                                            customClass: { confirmButton: 'btn btn-primary' }
                                        });
                                    }
                                },
                                error: function () {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'No se pudo conectar con el servidor.',
                                        confirmButtonText: 'OK',
                                        customClass: { confirmButton: 'btn btn-primary' }
                                    });
                                }
                            });
                        });
                    }, 0);
                },
                error: function () {
                    // Manejo de error en caso de no poder conectar con el servidor
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo conectar con el servidor.',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-outline-danger ml-1'
                        }
                    });
                }
            });
        });

        // Función para formatear fecha en formato 'YYYY-MM-DD'
        function formatDate(date) {
            const d = new Date(date);
            let month = '' + (d.getMonth() + 1);
            let day = '' + d.getDate();
            const year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }
        //ELIMINAR SEDE
        $('.datatables-citas').on('click', '.btn_delete_cita', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: '¿Estas seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '¡Sí, bórralo!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ml-1'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url + '/dashboard/citas/delete',
                        type: 'POST',
                        data: { id: id },
                        success: function (data) {
                            if (data.validate) {
                                table_citas.ajax.reload();
                            } else {
                                alertNotify('¡Error!', data.text, 'error');
                            }
                        }
                    });
                }
            });
        });
        //Cambiamos el estado
        $('.datatables-citas').on('click', '.change_estado_cita', function () {
            let id_estado = $(this).data('id_estado');
            let id_cita = $(this).data('id_cita');
            $.ajax({
                url: url + '/dashboard/citas/change_estado',
                type: 'POST',
                data: {
                    id_estado: id_estado,
                    id_cita: id_cita
                },
                success: function (data) {
                    table_citas.ajax.reload();
                }
            });
        });
        //Cambiamos el estado verificado
        $('.datatables-citas').on('click', '.change_estado_cita_verificado', function () {
            let id_estado = $(this).data('id_estado');
            let id_cita = $(this).data('id_cita');
            $.ajax({
                url: url + '/dashboard/citas/change_estado_verificado',
                type: 'POST',
                data: {
                    id_estado: id_estado,
                    id_cita: id_cita
                },
                success: function (data) {
                    table_citas.ajax.reload();
                }
            });
        });
        //Cambiamos el estado verificado
        $('.datatables-citas').on('click', '.change_agente_call', function () {
            let id_agente = $(this).data('id_agente');
            let id_cita = $(this).data('id_cita');
            $.ajax({
                url: url + '/dashboard/citas/change_agente_call',
                type: 'POST',
                data: {
                    id_agente: id_agente,
                    id_cita: id_cita
                },
                success: function (data) {
                    table_citas.ajax.reload();
                }
            });
        });
        //Cambiamos el servicio
        $('.datatables-citas').on('click', '.change_servicio_liquidador', function () {
            let id_servicio_liquidador = $(this).data('id_servicio_liquidador');
            let id_cita = $(this).data('id_cita');
            $.ajax({
                url: url + '/dashboard/liquidador/change_servicio_liquidador',
                type: 'POST',
                data: {
                    id_servicio_liquidador: id_servicio_liquidador,
                    id_cita: id_cita
                },
                success: function (data) {
                    table_citas.ajax.reload();
                }
            });
        });
    }
    //USUARIOS
    if ($('.datatables-usuarios').length) {
        table_usuarios = $('.datatables-usuarios').DataTable({
            lengthChange: false,
            searching: false,
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: url + '/dashboard/usuarios/get',
                type: "POST"
            },
            column: [{ data: '' }],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return '<h6 class="m-0">' + full.name + '</h6>';
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, full, meta) {
                        return '<span class="badge bg-label-dark">' + full.email + '</span>';
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, full, meta) {
                        let sede = (full.nombre_sede != undefined) ? full.nombre_sede : 'Sin asignar';
                        return '<h6 class="m-0">' + sede + '</h6>';
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, full, meta) {
                        return '<span class="badge bg-label-dark">' + full.role + '</span>';
                    }
                },
                {
                    targets: 4,
                    render: function (data, type, full, meta) {
                        return `
                        <div class="d-flex justify-content-end">
                            ${(canEditUsuarios) ? `
                                <a href="#" data-id="${full.id}" class="btn_edit_usuario btn btn-icon btn-label-primary waves-effect me-2">
                                    <i class="tf-icons ti ti-edit ti-md"></i>
                                </a>` : ``}
                            ${(canDeleteUsuarios) ? `
                                <a href="#" data-id="${full.id}" class="btn_delete_usuario btn btn-icon btn-label-danger waves-effect">
                                    <i class="tf-icons ti ti-trash ti-md"></i>
                                </a>` : ``}
                        </div>`	;
                    }
                },
            ],
            pagingType: "simple"
        });
        //ELIMINAR SEDE
        $('.datatables-usuarios').on('click', '.btn_delete_usuario', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: '¿Estas seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '¡Sí, bórralo!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ml-1'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url + '/dashboard/usuarios/delete',
                        type: 'POST',
                        data: { id: id },
                        success: function (data) {
                            if (data.validate) {
                                table_usuarios.ajax.reload();
                            } else {
                                alertNotify('¡Error!', data.text, 'error');
                            }
                        }
                    });
                }
            });
        });

        //Editar usuario
        $('.datatables-usuarios').on('click', '.btn_edit_usuario', function () {
            let id = $(this).data('id');
            $.ajax({
                url: url + '/dashboard/usuarios/get_user',
                type: 'POST',
                data: { id: id },
                success: function (response) {
                    $('.content_users_edit input[name="id_user"]').val(id);
                    $('.content_users_edit select[name="id_sede"]').val(response.usuario.id_sede).trigger('change');
                    $('.content_users_edit input[name="name"]').val(response.usuario.name);
                    $('.content_users_edit input[name="email"]').val(response.usuario.email);
                    $('.content_users_edit input[name="password"]').val('');
                    $('.content_users_edit select[name="role"]').val(response.usuario.role);
                    if (response.usuario.role == 'callcenter' || response.usuario.role == 'lidercallcenter') {
                        $('#contenedor-rol-user-edit').addClass('col-3');
                        $('#contenedor-rol-user-edit').removeClass('col-6');
                        $('#contenedor-habilitar-call-edit').show();
                    } else {
                        $('#contenedor-rol-user-edit').addClass('col-6');
                        $('#contenedor-rol-user-edit').removeClass('col-3');
                        $('#contenedor-habilitar-call-edit').hide();
                    }
                    if (response.usuario.callcenter_habilitado == 1) {
                        $('.content_users_edit input[name="callcenter_habilitado"]').prop('checked', true);
                    } else {
                        $('.content_users_edit input[name="callcenter_habilitado"]').prop('checked', false);
                    }
                    $('.content_users_add').hide();
                    $('.content_users_edit').fadeIn(200);

                }
            });
        });
        $('.add_user').click(function () {
            $('.content_users_edit').hide();
            $('.content_users_add').fadeIn(200);
        });
        $('#rol-user').change(function () {
            if ($(this).val() == 'callcenter' || $(this).val() == 'lidercallcenter') {
                $('#contenedor-rol-user').addClass('col-3');
                $('#contenedor-rol-user').removeClass('col-6');
                $('#contenedor-habilitar-call').show();
            } else {
                $('#contenedor-rol-user').addClass('col-6');
                $('#contenedor-rol-user').removeClass('col-3');
                $('#contenedor-habilitar-call').hide();
            }
        }); $('#rol-user-edit').change(function () {
            if ($(this).val() == 'callcenter' || $(this).val() == 'lidercallcenter') {
                $('#contenedor-rol-user-edit').addClass('col-3');
                $('#contenedor-rol-user-edit').removeClass('col-6');
                $('#contenedor-habilitar-call-edit').show();
            } else {
                $('#contenedor-rol-user-edit').addClass('col-6');
                $('#contenedor-rol-user-edit').removeClass('col-3');
                $('#contenedor-habilitar-call-edit').hide();
            }
        });
    }
    //Descargar boton
    //Cambiamo el estado
    function dowloadFileCita(action, start, filter) {
        $.ajax({
            url: action,
            type: 'POST',
            data: {
                start: start,
                filtro_dia: filter.filtro_dia,
                filtro_dia_end: filter.filtro_dia_end,
                filtro_estado_pago_liquidador: filter.filtro_estado_pago_liquidador,
                filtro_estado_validacion_liquidador: filter.filtro_estado_validacion_liquidador,
                filtro_estado: filter.filtro_estado,
                filtro_sede: filter.filtro_sede,
                tipo_cita: filter.tipo_cita,
                filtro_servicios_liquidador: filter.filtro_servicios_liquidador,
            },
            success: function (data) {
                console.log("Data: " + data);
                if (data.status === 'in_progress') {
                    // Si aún hay más datos por procesar, llama a la función con el siguiente bloque
                    dowloadFileCita(action, data.nextStart, filter);
                } else if (data.status === 'completed') {
                    let url_file = data.url;
                    let a = document.createElement('a');
                    a.href = url_file;
                    a.download = url_file.split('/').pop();
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
            },
            error: function (error) {
                console.error("Error en la exportación:", error);
            }
        });
    }
    $('.btn_descagar_cita').click(function () {
        let action = $(this).data('action');
        let filter = {
            'filtro_dia': filtroDia,
            'filtro_dia_end': filtroDiaEnd,
            'filtro_sede': filtroSede,
            'filtro_estado': filtroEstado,
            'tipo_cita': $('#tipo_cita').val(),
            'filtro_servicios_liquidador': filtroServicioLiquidador,
            'filtro_estado_validacion_liquidador': filtroEstadoValidacionLiquidador,
            'filtro_estado_pago_liquidador': filtroEstadoPagoLiquidador,
        };
        dowloadFileCita(action, 0, filter); // Inicia con el primer bloque de datos
        console.log(filter);
    });
    //Descargar boton
    function dowloadFile(action, start, type) {
        $.ajax({
            url: action,
            type: 'POST',
            data: {
                start: start,
                type: type
            },
            success: function (data) {
                if (data.status === 'in_progress') {
                    dowloadFile(action, data.nextStart, type);
                } else if (data.status === 'completed') {
                    let url_file = data.url;
                    let a = document.createElement('a');
                    a.href = url_file;
                    a.download = url_file.split('/').pop();
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
            },
            error: function (error) {
                console.error("Error en la exportación:", error);
            }
        });
    }
    $('.btn_descagar').click(function () {
        let action = $(this).data('action');
        let type = $(this).data('type');
        dowloadFile(action, 0, type);
    });


    let timerId;

    // function checkNewRecords() {
    //     fetch('citas/get_new_records', {
    //         method: 'GET',
    //         headers: {
    //             'X-Requested-With': 'XMLHttpRequest'
    //         }
    //     })
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.validate) {
    //                 // Mostrar notificación si hay nuevos registros
    //                 if (data.nuevos_registros > 0) {
    //                     const notification = new Notification(`¡Nueva Registros!`, {
    //                         body: `${data.nuevos_registros} registros nuevos en los últimos 5 minutos en ${data.sede}.`,
    //                         icon: '../assets/img/favicon/favicon.ico',
    //                     });

    //                     notification.addEventListener('click', () => {
    //                         window.open('citas', '_blank');
    //                     });
    //                 } else {
    //                     console.log('No hay nuevos registros');
    //                 }

    //                 console.log(`Próxima consulta en ${data.time_remaining} segundos.`);
    //                 // Sincronizar temporizador con el servidor
    //                 clearTimeout(timerId);
    //                 timerId = setTimeout(checkNewRecords, data.time_remaining * 1000);
    //                 console.log(timerId)
    //             }
    //         })
    //         .catch(error => console.error('Error al obtener registros nuevos:', error));
    // }

    // if (Notification.permission !== "granted") {
    //     Notification.requestPermission().then(permission => {
    //         if (permission === "granted") {
    //             console.log("Permiso de notificaciones concedido.");
    //             checkNewRecords();
    //         } else {
    //             console.error("Permiso de notificaciones denegado.");
    //         }
    //     });
    // } else {
    //     checkNewRecords();
    // }

    //----------  Dashboard Estadisticas --------------------

    if ($('#estadisticas-fechas-citas').length) {
        // Inicializar el gráfico
        var chart = new ApexCharts(document.querySelector("#ChartComparacionDateCreatedLinear"), {
            chart: {
                type: "line",
                height: 400,
                stacked: false,
            },
            series: [
                {
                    name: "Semana Actual",
                    type: "column",
                    data: [],
                },
                {
                    name: "Semana Anterior",
                    type: "line",
                    data: [],
                }
            ], // Los datos se cargarán dinámicamente
            xaxis: {
                categories: [], // Las categorías se cargarán dinámicamente
            },
            stroke: {
                width: [0, 2]
            },
            yaxis: [
                {
                    axisTicks: {
                        show: false
                    },
                    axisBorder: {
                        show: false,
                    },
                },
            ],
            tooltip: {
                shared: true,
                intersect: false,
                custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                    // Array de los días de la semana
                    const daysOfWeek = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];


                    // Obtener las fechas asociadas al rango actual e histórico
                    const currentDates = w.config.series[0].data;
                    const historicalDates = w.config.series[1].data;

                    // Identificar el tipo de comparación según las fechas
                    const currentDate = currentDates[dataPointIndex]?.x || 'N/A';
                    const historicalDate = historicalDates[dataPointIndex]?.x || 'N/A';

                    // Obtener las fechas iniciales y finales del rango actual
                    const startDate = new Date(currentDates[0]?.x);
                    const endDate = new Date(currentDates[currentDates.length - 1]?.x);

                    // Calcular la diferencia en días entre las fechas
                    const timeDiff = Math.abs(endDate - startDate);
                    const dayDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

                    // Identificar el tipo de comparación según la duración
                    const isWeeklyComparison = dayDiff <= 7; // Si el rango abarca 7 días o menos, es semanal
                    const isTowWeeklyComparison = dayDiff > 7 && dayDiff <= 15; // Entre 8 y 15 días, es bisemanal
                    const isMonthlyComparison = dayDiff > 15 && dayDiff <= 31; // Entre 16 y 31 días, es mensual
                    const isTreeMonthlyComparison = dayDiff > 31; // desde 32 días, es trimestral

                    // Formato del día según el tipo de comparación
                    let dayLabel;
                    let tipoComparison;
                    if (isWeeklyComparison) {
                        dayLabel = daysOfWeek[dataPointIndex % daysOfWeek.length]; // Día de la semana
                        tipoComparison = 'Semana';
                    } else if (isTowWeeklyComparison) {
                        dayLabel = daysOfWeek[dataPointIndex % daysOfWeek.length];
                        tipoComparison = 'Quincena';
                    } else if (isMonthlyComparison) {
                        dayLabel = `Día ${dataPointIndex + 1}`; // Día numérico
                        tipoComparison = 'Mes';
                    } else if (isTreeMonthlyComparison) {
                        dayLabel = `Día ${dataPointIndex + 1}`; // Día numérico
                        tipoComparison = 'Trimestre';
                    }
                    // Obtener los datos de la serie actual e histórica
                    const currentValue = series[0][dataPointIndex] ?? 0; // Semana/Mes actual
                    const historicalValue = series[1][dataPointIndex] ?? 0; // Semana/Mes histórica


                    // Construir el HTML del tooltip
                    return `
                        <div class="apexcharts-tooltip-custom" >
                            <div class="tooltip-header" style="padding: 10px; font-weight: bold; margin-bottom: 8px; background-color: var(--color-gris); color: var(--color-morado);">
                                ${dayLabel}
                            </div>
                             <div class="tooltip-body" style="padding: 12px;">
                                <div style="margin-bottom: 8px;">
                                    <span style="display: inline-block; width: 10px; height: 10px; background-color: ${w.config.colors[0]}; border-radius: 50%; margin-right: 5px;"></span>
                                    <strong> ${tipoComparison} Actual</strong><br>
                                    Fecha: ${currentDate}<br>
                                    Registros: ${currentValue}
                                </div>
                                <div>
                                    <span style="display: inline-block; width: 10px; height: 10px; background-color: ${w.config.colors[1]}; border-radius: 50%; margin-right: 5px;"></span>
                                    <strong>${tipoComparison} anterior</strong><br>
                                    Fecha: ${historicalDate}<br>
                                    Registros: ${historicalValue}
                                </div>
                            </div>
                        </div>
                    `;
                }
            },
            markers: {
                strokeWidth: 1,
                strokeOpacity: 1,
                style: 'hollow',
                strokeColors: [config.colors.info],
                colors: [config.colors.primary],
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '30%',
                    borderRadius: 40,
                    borderRadiusApplication: 'around',
                    borderRadiusWhenStacked: 'all',

                },
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical', // Horizontal o vertical
                    gradientToColors: [config.colors.primary], // Segundo color del gradiente
                    stops: [0, 100], // Inicia y termina en 0% y 100%
                }
            },
            colors: ['#7367f0', '#00e396'],

        });


        // Renderizar el gráfico vacío inicialmente
        chart.render();

        // Función para cargar datos desde el backend
        function fetchData(endpoint, chart, dateSelected, selectedRange) {
            $.ajax({
                url: endpoint,
                method: "GET",
                data: {
                    start_date: dateSelected,
                    range: selectedRange
                },
                success: function (response) {
                    // Procesar la respuesta del backend
                    const TotalDatesActual = response.TotalDatesActual;
                    const TotalDatesHistorico = response.TotalDatesHistorico;
                    const currrentDateStart = response.currrentDateStart;
                    const currrentDateEnd = response.currrentDateEnd;
                    const TotalDatesHistoricoStart = response.TotalDatesHistoricoStart;
                    const TotalDatesHistoricoEnd = response.TotalDatesHistoricoEnd;
                    const peiodoSelecionado = response.peiodoSelecionado;

                    $("#ConTextRangoFechas").html(currrentDateStart + ' - ' + currrentDateEnd);
                    $("#ConTextRangoFechasHistorico").html(TotalDatesHistoricoStart + ' - ' + TotalDatesHistoricoEnd);
                    if (peiodoSelecionado == "Semana") {
                        $("#comp-fecha-creacion-titulo").html("Comparativa Semanal: Fechas de Creación");
                        $("#comp-fecha-reserva-titulo").html("Comparativa Semanal: Fechas de Reserva");
                    } else if (peiodoSelecionado == "Quincena") {
                        $("#comp-fecha-creacion-titulo").html("Comparativa Quincenal: Fechas de Creación");
                        $("#comp-fecha-reserva-titulo").html("Comparativa Quincenal: Fechas de Reserva");
                    } else if (peiodoSelecionado == "Mes") {
                        $("#comp-fecha-creacion-titulo").html("Comparativa Mensual: Fechas de Creación");
                        $("#comp-fecha-reserva-titulo").html("Comparativa Mensual: Fechas de Reserva");
                    } else if (peiodoSelecionado == "Trimestre") {
                        $("#comp-fecha-creacion-titulo").html("Comparativa Trimestral: Fechas de Creación");
                        $("#comp-fecha-reserva-titulo").html("Comparativa Trimestral: Fechas de Reserva");
                    } else {
                        $("#comp-fecha-creacion-titulo").html("Comparativa Semanal: Fechas de Creación");
                        $("#comp-fecha-reserva-titulo").html("Comparativa Semanal: Fechas de Reserva");
                    }

                    // Calcular totales
                    const totalActual = TotalDatesActual.reduce((sum, item) => sum + item.count, 0);
                    const totalHistorico = TotalDatesHistorico.reduce((sum, item) => sum + item.count, 0);


                    // Actualizar las series con fechas y valores
                    const currentData = TotalDatesActual.map(item => ({ x: item.date, y: item.count }));
                    const previousData = TotalDatesHistorico.map(item => ({ x: item.date, y: item.count }));
                    const $etxtTotalActual = peiodoSelecionado + " Actual: " + totalActual.toLocaleString('es-ES') + " Citas creadas";
                    const $etxtTotalHistorico = peiodoSelecionado + " Anterior: " + totalHistorico.toLocaleString('es-ES') + " Citas creadas";

                    chart.updateSeries([
                        {
                            name: $etxtTotalActual,
                            type: "column",
                            data: currentData,
                        },
                        {
                            name: $etxtTotalHistorico,
                            type: "line",
                            data: previousData,
                        },
                    ]);

                    // Extraer las categorías (días de la semana) y los datos
                    const categories = TotalDatesActual.map((item) => {
                        const day = moment(item.date).locale("es").format("dddd");
                        return day.charAt(0).toUpperCase() + day.slice(1);
                    });

                    // Actualizar las categorías y las series del gráfico
                    chart.updateOptions({
                        xaxis: {
                            categories: categories,
                        },
                    });

                },
                error: function (error) {
                    console.error("Error al cargar los datos:", error);
                },
            });
        }

        // Llamar a la función para cargar los datos
        fetchData("estadisticas/creaciones", chart);

        // Inicializar el gráfico
        var chart2 = new ApexCharts(document.querySelector("#ChartComparacionDateReservaLinear"), {
            chart: {
                type: "line",
                height: 400,
            },
            series: [
                {
                    name: "Semana Actual",
                    type: "column",
                    data: [],
                },
                {
                    name: "Semana Anterior",
                    type: "line",
                    data: [],
                }
            ], // Los datos se cargarán dinámicamente
            xaxis: {
                categories: [], // Las categorías se cargarán dinámicamente
            },
            stroke: {
                width: [0, 2]
            },
            yaxis: [
                {
                    axisTicks: {
                        show: false
                    },
                    axisBorder: {
                        show: false,
                    },
                },
            ],
            tooltip: {
                shared: true,
                intersect: false,
                custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                    // Array de los días de la semana
                    const daysOfWeek = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

                    // Obtener las fechas asociadas al rango actual e histórico
                    const currentDates = w.config.series[0].data;
                    const historicalDates = w.config.series[1].data;

                    // Identificar el tipo de comparación según las fechas
                    const currentDate = currentDates[dataPointIndex]?.x || 'N/A';
                    const historicalDate = historicalDates[dataPointIndex]?.x || 'N/A';

                    // Obtener las fechas iniciales y finales del rango actual
                    const startDate = new Date(currentDates[0]?.x);
                    const endDate = new Date(currentDates[currentDates.length - 1]?.x);

                    // Calcular la diferencia en días entre las fechas
                    const timeDiff = Math.abs(endDate - startDate);
                    const dayDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

                    // Identificar el tipo de comparación según la duración
                    const isWeeklyComparison = dayDiff <= 7; // Si el rango abarca 7 días o menos, es semanal
                    const isTowWeeklyComparison = dayDiff > 7 && dayDiff <= 15; // Entre 8 y 15 días, es bisemanal
                    const isMonthlyComparison = dayDiff > 15 && dayDiff <= 31; // Entre 16 y 31 días, es mensual
                    const isTreeMonthlyComparison = dayDiff > 31; // desde 32 días, es trimestral

                    // Formato del día según el tipo de comparación
                    let dayLabel;
                    let tipoComparison;
                    if (isWeeklyComparison) {
                        dayLabel = daysOfWeek[dataPointIndex % daysOfWeek.length]; // Día de la semana
                        tipoComparison = 'Semana';
                    } else if (isTowWeeklyComparison) {
                        dayLabel = daysOfWeek[dataPointIndex % daysOfWeek.length];
                        tipoComparison = 'Quincena';
                    } else if (isMonthlyComparison) {
                        dayLabel = `Día ${dataPointIndex + 1}`; // Día numérico
                        tipoComparison = 'Mes';
                    } else if (isTreeMonthlyComparison) {
                        dayLabel = `Día ${dataPointIndex + 1}`; // Día numérico
                        tipoComparison = 'Trimestre';
                    }
                    // Obtener los datos de la serie actual e histórica
                    const currentValue = series[0][dataPointIndex] ?? 0; // Semana/Mes actual
                    const historicalValue = series[1][dataPointIndex] ?? 0; // Semana/Mes histórica

                    // Construir el HTML del tooltip
                    return `
                        <div class="apexcharts-tooltip-custom" >
                            <div class="tooltip-header" style="padding: 10px; font-weight: bold; margin-bottom: 8px; background-color: var(--color-gris); color: var(--color-morado);">
                                ${dayLabel}
                            </div>
                            <div class="tooltip-body" style="padding: 12px;">
                                <div style="margin-bottom: 8px;">
                                    <span style="display: inline-block; width: 10px; height: 10px; background-color: ${w.config.colors[0]}; border-radius: 50%; margin-right: 5px;"></span>
                                    <strong> ${tipoComparison} Actual</strong><br>
                                    Fecha: ${currentDate}<br>
                                    Registros: ${currentValue}
                                </div>
                                <div>
                                    <span style="display: inline-block; width: 10px; height: 10px; background-color: ${w.config.colors[1]}; border-radius: 50%; margin-right: 5px;"></span>
                                    <strong>${tipoComparison} anterior</strong><br>
                                    Fecha: ${historicalDate}<br>
                                    Registros: ${historicalValue}
                                </div>
                            </div>
                        </div>
                    `;
                }
            },
            markers: {
                strokeWidth: 1,
                strokeOpacity: 1,
                strokeColors: [config.colors.info],
                colors: [config.colors.primary],
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '30%',
                    borderRadius: 40,
                    borderRadiusApplication: 'around',
                    borderRadiusWhenStacked: 'all',

                },
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical', // Horizontal o vertical
                    gradientToColors: [config.colors.primary], // Segundo color del gradiente
                    stops: [0, 100], // Inicia y termina en 0% y 100%
                }
            },
            colors: ['#7367f0', '#00e396'],

        });


        // Renderizar el gráfico vacío inicialmente
        chart2.render();

        // Función para cargar datos desde el backend
        function fetchData2(endpoint, chart2, dateSelected, selectedRange) {
            $.ajax({
                url: endpoint,
                method: "GET",
                data: {
                    start_date: dateSelected,
                    range: selectedRange

                },
                success: function (response) {
                    // Procesar la respuesta del backend
                    const TotalDatesActual = response.TotalDatesActual;
                    const TotalDatesHistorico = response.TotalDatesHistorico;
                    const peiodoSelecionado = response.peiodoSelecionado;


                    // Actualizar las series con fechas y valores
                    const currentData = TotalDatesActual.map(item => ({ x: item.date, y: item.count }));
                    const previousData = TotalDatesHistorico.map(item => ({ x: item.date, y: item.count }));

                    // Calcular totales
                    const totalActual = TotalDatesActual.reduce((sum, item) => sum + item.count, 0);
                    const totalHistorico = TotalDatesHistorico.reduce((sum, item) => sum + item.count, 0);
                    const $etxtTotalActual = peiodoSelecionado + " Actual: " + totalActual.toLocaleString('es-ES') + " Citas reservada";
                    const $etxtTotalHistorico = peiodoSelecionado + " Anterior: " + totalHistorico.toLocaleString('es-ES') + " Citas reservada";


                    chart2.updateSeries([
                        {
                            name: $etxtTotalActual,
                            type: "column",
                            data: currentData,
                        },
                        {
                            name: $etxtTotalHistorico,
                            type: "line",
                            data: previousData,
                        },
                    ]);

                    // Extraer las categorías (días de la semana) y los datos
                    const categories = TotalDatesActual.map((item) => {
                        const day = moment(item.date).locale("es").format("dddd");
                        return day.charAt(0).toUpperCase() + day.slice(1);
                    });

                    // Actualizar las categorías y las series del gráfico
                    chart2.updateOptions({
                        xaxis: {
                            categories: categories,
                        },
                    });

                },
                error: function (error) {
                    console.error("Error al cargar los datos:", error);
                },
            });
        }

        // Llamar a la función para cargar los datos
        fetchData2("estadisticas/citas", chart2);

        // Inicializar Flatpickr
        const today = new Date();

        // Función para obtener el primer día de la semana
        function getFirstDayOfWeek(date) {
            const day = date.getDay(); // Día de la semana (0 = domingo, 1 = lunes, ...)
            const diff = (day === 0 ? -6 : 1) - day; // Ajuste para que el lunes sea el primer día
            return new Date(date.getFullYear(), date.getMonth(), date.getDate() + diff);
        }

        const firstDayOfWeek = getFirstDayOfWeek(today);

        flatpickr("#datePicker", {
            dateFormat: "Y-m-d", // Formato de fecha (Año-Mes-Día)
            defaultDate: firstDayOfWeek, // Fecha por defecto
            maxDate: new Date().fp_incr(1), // Fecha máxima (1 día después de hoy)
            enable: [
                function (date) {
                    // Permitir solo los primeros días de la semana (lunes)
                    return date.getDay() === 1; // 1 = Lunes
                }
            ],
            onChange: function (selectedDates, dateStr, instance) {
                // Aquí puedes filtrar los datos de tu gráfico según las fechas seleccionadas
                updateCharts(dateStr, null);
            }
        });
        // Función para actualizar los gráficos
        function updateCharts(selectedDate, selectedRange) {


            if (selectedDate) {
                var RangoFechas = $("#select-rango-fechas-estadisticas").val();
                // Actualizar el gráfico de creaciones
                fetchData(`estadisticas/creaciones`, chart, selectedDate, RangoFechas);
                // Actualizar el gráfico de reservas
                fetchData2(`estadisticas/citas`, chart2, selectedDate, RangoFechas);
            } else if (selectedRange) {
                var fechaPick = $("#datePicker").val();
                // Actualizar el gráfico de creaciones
                fetchData(`estadisticas/creaciones`, chart, fechaPick, selectedRange);
                // Actualizar el gráfico de reservas
                fetchData2(`estadisticas/citas`, chart2, fechaPick, selectedRange);

            }
        }
        $("#select-rango-fechas-estadisticas").change(function () {
            updateCharts(null, $(this).val());
        });

        // 1) Crear instancias
        var chartEstado = new ApexCharts(
            document.querySelector("#ChartComparacionEstado"), {
            chart: { type: 'bar', height: 350, stacked: false },
            series: [
                { name: 'Actual', data: [] },
                { name: 'Histórico', data: [] }
            ],
            xaxis: { categories: [] },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    borderRadiusApplication: 'end',
                    horizontal: true,
                }
            },
            legend: {
                fontFamily: 'Roboto, sans-serif',
                markers: { radius: 12, width: 12, height: 12 },
                position: 'top'
            },
            tooltip: {
                theme: 'light',
                style: { fontFamily: 'Roboto, sans-serif' },
                onDatasetHover: { highlightDataSeries: true },
            },
            theme: {
                mode: 'light',
                palette: 'palette1' // puedes elegir palette1–5 para probar presets
            },
        }
        );
        var chartVerificado = new ApexCharts(
            document.querySelector("#ChartComparacionEstadoVerificado"), {
            chart: { type: 'bar', height: 350, stacked: false },
            series: [
                { name: 'Actual', data: [] },
                { name: 'Histórico', data: [] }
            ],
            xaxis: { categories: [] },
            plotOptions: { bar: { horizontal: true } },
            legend: {
                fontFamily: 'Roboto, sans-serif',
                markers: { radius: 12, width: 12, height: 12 },
                position: 'top'
            },
            tooltip: {
                theme: 'light',
                style: { fontFamily: 'Roboto, sans-serif' },
                onDatasetHover: { highlightDataSeries: true },
            },
            theme: {
                mode: 'light',
                palette: 'palette4' // puedes elegir palette1–5 para probar presets
            },
        }
        );

        // 2) Render vacío
        chartEstado.render();
        chartVerificado.render();

        // 3) Función genérica para cargar comparativa de estados
        function fetchComparativa(endpoint, chart, tituloSelector) {
            const fecha = $('#datePicker').val();
            const range = +$('#select-rango-fechas-estadisticas').val();
            $.getJSON(endpoint, { start_date: fecha, range: range })
                .done(resp => {
                    // labels y series
                    chart.updateOptions({ xaxis: { categories: resp.labels } });
                    chart.updateSeries([
                        { name: resp.periodo + ' Actual', data: resp.serieActual },
                        { name: resp.periodo + ' Histórico', data: resp.serieHist }
                    ]);
                    // título dinámico
                    $(tituloSelector).text(`Comparativa ${resp.periodo}: Estados`);
                })
                .fail(err => console.error('fetchComparativa', endpoint, err));
        }

        // 4) Llamar a las dos comparativas
        fetchComparativa(
            'estadisticas/estado/comparativa',
            chartEstado,
            '#comp-estado-titulo'
        );
        fetchComparativa(
            'estadisticas/estado-verificado/comparativa',
            chartVerificado,
            '#comp-estado-verificado-titulo'
        );

        // 5) Volver a cargar al cambiar filtros
        $('#select-rango-fechas-estadisticas, #datePicker').on('change', function () {
            fetchComparativa('estadisticas/estado/comparativa', chartEstado, '#comp-estado-titulo');
            fetchComparativa('estadisticas/estado-verificado/comparativa', chartVerificado, '#comp-estado-verificado-titulo');
        });

    };

    if ($('#estadisticas-agentes').length) {


        // 1) Factory mejorado
        function crearChart(sel) {
            const el = document.querySelector(sel);
            if (!el) return null;
            return new ApexCharts(el, {
                chart: { type: 'line', height: 350, stacked: false },
                series: [],
                xaxis: { categories: [] },
                stroke: { curve: 'smooth', width: 2 },
                fill: { opacity: 0.8 },
                markers: { size: 0 },
                dataLabels: { enabled: false },
                tooltip: { shared: true, intersect: false }
            });
        }

        // Declaramos la lista maestra una sola vez
        const ESTADOS = () => {
            const sel = $('#filter-estados').val() || [];
            // dejamos en el mismo orden que el select. Luego añadimos 'Otros'
            return [...sel, 'Otros'];
        };


        function fetchAndRender(cfg) {
            const r = +$('#select-rango-fechas-estadisticas').val();
            const fecha = $('#datePicker').val();         // ej: "2025-05-12"
            const m = moment(fecha, 'YYYY-MM-DD');

            let startMoment, rangeDays;

            switch (r) {
                case 1: // Semana actual: lunes → domingo
                    startMoment = m.clone().startOf('isoWeek');
                    rangeDays = 6;
                    break;

                case 2: // Últimas 2 semanas: lunes semana anterior → domingo semana actual
                    startMoment = m.clone().startOf('isoWeek').subtract(7, 'days');
                    rangeDays = 13;
                    break;

                case 3: // Mes actual: 1ro mes → fin mes
                    startMoment = m.clone().startOf('month');
                    rangeDays = startMoment.daysInMonth() - 1;
                    break;

                case 4: // Trimestre: primer día del mes -2 meses → fin del mes actual
                    startMoment = m.clone().startOf('month').subtract(2, 'months');
                    // diferencia en días entre start y fin de mes actual
                    const endOfCurrent = m.clone().endOf('month');
                    rangeDays = endOfCurrent.diff(startMoment, 'days');
                    break;

                default:
                    startMoment = m.clone().startOf('isoWeek');
                    rangeDays = 6;
            }

            const start_date = startMoment.format('YYYY-MM-DD');
            const end_date = startMoment.clone().add(rangeDays, 'days').format('YYYY-MM-DD');
            const selectedEstados = $('#filter-estados').val() || [];

            // para mostrar rango en el UI
            $('#ConTextRangoFechas').text(`${start_date} — ${end_date}`);

            const params = {
                start_date,
                rangeDays: rangeDays,
                date_field: cfg.dateField,
                estados: selectedEstados
            };
            if (cfg.agentId) params.agent_id = cfg.agentId;

            // Agregar estados seleccionados

            $.getJSON('agentes/getStatsPorEstadoAgentes', params)
                .done(resp => {
                    const cats = Array.isArray(resp.categories) ? resp.categories : [];
                    let series = Array.isArray(resp.series) ? resp.series : [];

                    // 1) Filtramos cualquier "Total" que venga del backend
                    series = series.filter(s => s.name !== 'Total');

                    if (!cats.length || !series.length) {
                        document.querySelector(cfg.wrapper)?.remove();
                        return;
                    }

                    // 2) Mapear en orden y asegurar todos los estados
                    const ordenadas = ESTADOS().map(estado => {
                        const s = series.find(x => x.name === estado);
                        return s
                            ? s
                            : { name: estado, data: Array(cats.length).fill(0), color: '#adb5bd' };
                    });

                    // 1.1) Forzar color de Otros
                    const idxOtros = ordenadas.findIndex(s => s.name === 'Otros');
                    if (idxOtros !== -1) {
                        ordenadas[idxOtros].color = '#FF8F8F';      // <-- tu color fijo para Otros
                    }

                    // 3) Calcular el nuevo Total
                    const totalData = cats.map((_, i) =>
                        ordenadas.reduce((sum, s) => sum + (s.data[i] || 0), 0)
                    );

                    // 4) Series finales para Apex: todas las áreas + línea Total
                    const allSeries = [
                        ...ordenadas,
                        { name: 'Total', data: totalData, color: '#00bbe3' }
                    ];

                    // 5) Actualizar opciones (colores, opacidades, grosores)
                    cfg.chart.updateOptions({
                        xaxis: { categories: cats.map(d => moment(d).format('DD MMM')) },
                        colors: [...ordenadas.map(s => s.color), '#adb5bd'],
                        fill: { opacity: [...Array(ordenadas.length).fill(0.6), 0.8] },
                        stroke: { width: [...Array(ordenadas.length).fill(2), 3] }
                    });

                    // 6) Inyectar datos en la gráfica
                    cfg.chart.updateSeries(allSeries);

                    // 7) Renderizar badges usando solo las series de estados (sin Total)
                    const bc = document.querySelector(cfg.badgeSel);
                    if (bc) {
                        bc.innerHTML = ordenadas.map(s => {
                            const tot = s.data.reduce((a, v) => a + v, 0);
                            return `<span class="badge m-1" style="background-color:${s.color};">
                    ${s.name}: ${tot}
                  </span>`;
                        }).join('')
                            // y al final el badge de Total
                            + `<span class="badge" style="background-color:#00bbe3;">
             Total: ${totalData.reduce((a, v) => a + v, 0)}
           </span>`;
                    }

                })
                .fail(() => {
                    document.querySelector(cfg.wrapper)?.remove();
                });
        }

        // 4) Inicialización
        $(function () {
            const configs = [];

            ['reserva', 'creacion'].forEach(f => {
                configs.push({
                    wrapper: `#Global-${f}-block`,
                    chart: crearChart(`#ChartGlobal-${f}`),
                    badgeSel: `#BadgesGlobal-${f}`,
                    agentId: null,
                    dateField: f
                });
            });

            agentes.forEach(a => {
                ['reserva', 'creacion'].forEach(f => {
                    configs.push({
                        wrapper: `#block-${a.id}`,
                        chart: crearChart(`#ChartAgent-${a.id}-${f}`),
                        badgeSel: `#BadgesAgent-${a.id}-${f}`,
                        agentId: a.id,
                        dateField: f
                    });
                });
            });

            configs.forEach(cfg => {
                if (!cfg.chart) {
                    document.querySelector(cfg.wrapper)?.remove();
                    return;
                }
                cfg.chart
                    .render()
                    .then(() => fetchAndRender(cfg))
                    .catch(error => {
                        console.error('[ERROR] al renderizar chart', cfg.wrapper, error);
                        // opcionalmente ocultar container:
                        document.querySelector(cfg.wrapper)?.remove();
                    });
            });

            $('#select-rango-fechas-estadisticas, #datePicker, #filter-estados').on('change', () => {
                $('#iconBtnFiltro').html('<span class="badge rounded-pill bg-danger badge-dot badge-notifications"></span>');
                $('#iconBtnFiltro').removeClass('ti-filter');
                $('#iconBtnFiltro').addClass('ti-filter-search');

                configs.forEach(cfg => {
                    if (cfg.chart) {
                        fetchAndRender(cfg);
                    }
                });
            });
        });

        $('#btnClearFilters').on('click', () => {
            location.reload();
        });
        // Inicializar Flatpickr
        const today = new Date();

        // Función para obtener el primer día de la semana
        function getFirstDayOfWeek(date) {
            const day = date.getDay(); // Día de la semana (0 = domingo, 1 = lunes, ...)
            const diff = (day === 0 ? -6 : 1) - day; // Ajuste para que el lunes sea el primer día
            return new Date(date.getFullYear(), date.getMonth(), date.getDate() + diff);
        }

        const firstDayOfWeek = getFirstDayOfWeek(today);

        flatpickr("#datePicker", {
            dateFormat: "Y-m-d", // Formato de fecha (Año-Mes-Día)
            defaultDate: firstDayOfWeek, // Fecha por defectoS
            enable: [
                function (date) {
                    // Permitir solo los primeros días de la semana (lunes)
                    return date.getDay() === 1; // 1 = Lunes
                }
            ],
        });

        $('#filter-estados').select2({
            data: estados,
            placeholder: 'Selecciona estados…',
            width: 'resolve'
        })

            .val(DEFAULT_ESTADOS)
            .trigger('change');


    }

    if ($('#estadisticas-Sedes').length) {


        // factory Apex
        function crearBarChart() {
            return new ApexCharts(document.querySelector("#barSedes"), {
                chart: { type: 'bar', height: 300 },
                xaxis: { categories: [] },
                series: [{ name: 'Citas', data: [] }]
            });
        }
        function crearPieChart() {
            return new ApexCharts(document.querySelector("#pieSedes"), {
                chart: { type: 'pie', height: 300 },
                series: [],
                labels: []
            });
        }

        const barChart = crearBarChart(), pieChart = crearPieChart();
        barChart.render(); pieChart.render();

        function recargarSedes() {
            // calculamos fechas igual que en agentes...
            const r = +$('#select-rango-fechas-estadisticas').val();
            const fecha = $('#datePicker').val();
            const m = moment(fecha, 'YYYY-MM-DD');
            let start, days;
            switch (r) {
                case 1: start = m.clone().startOf('isoWeek'); days = 6; break;
                case 2: start = m.clone().startOf('isoWeek').subtract(7, 'days'); days = 13; break;
                case 3: start = m.clone().startOf('month'); days = start.daysInMonth() - 1; break;
                case 4:
                    start = m.clone().startOf('month').subtract(2, 'months');
                    days = m.clone().endOf('month').diff(start, 'days');
                    break;
            }
            const start_date = start.format('YYYY-MM-DD');
            const end_date = start.clone().add(days, 'days').format('YYYY-MM-DD');

            $('#ConTextRangoFechas').text(`${start_date} — ${end_date}`);


            const params = {
                start_date,
                rangeDays: days,
                sedes: $('#filter-sedes').val() || []
            };

            $.getJSON('sedes/getStatsPorSede', params)
                .done(resp => {
                    // 1) barras
                    barChart.updateOptions({ xaxis: { categories: resp.categories } });
                    barChart.updateSeries([{ name: 'Citas', data: resp.dataBar }]);
                    // 2) pastel
                    pieChart.updateSeries(resp.dataPie.map(x => x.y));
                    pieChart.updateOptions({ labels: resp.dataPie.map(x => x.name) });
                });
        }

        // disparadores
        $('#select-rango-fechas-estadisticas,#datePicker,#filter-sedes').on('change', recargarSedes);
        $(function () { recargarSedes(); });



        $('#btnClearFilters').on('click', () => {
            location.reload();
        });
        // Inicializar Flatpickr
        const today = new Date();

        // Función para obtener el primer día de la semana
        function getFirstDayOfWeek(date) {
            const day = date.getDay(); // Día de la semana (0 = domingo, 1 = lunes, ...)
            const diff = (day === 0 ? -6 : 1) - day; // Ajuste para que el lunes sea el primer día
            return new Date(date.getFullYear(), date.getMonth(), date.getDate() + diff);
        }

        const firstDayOfWeek = getFirstDayOfWeek(today);

        flatpickr("#datePicker", {
            dateFormat: "Y-m-d", // Formato de fecha (Año-Mes-Día)
            defaultDate: firstDayOfWeek, // Fecha por defectoS
            enable: [
                function (date) {
                    // Permitir solo los primeros días de la semana (lunes)
                    return date.getDay() === 1; // 1 = Lunes
                }
            ],
        });

        $('#filter-sedes').select2({
            placeholder: 'Selecciona Sedes...',
            width: 'resolve'
        })


    }


    //----------------------------------------------------------------------------
    //----------------------  Dashboard Liquidador -------------------------------
    //----------------------------------------------------------------------------

    //TABLAS DE CITAS
    if ($('.datatables-citas-liquidador').length) {
        tipoCita = $('#tipo_cita').val();
        let table = $('.datatables-citas-liquidador').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            searching: false,
            info: true,
            pageLength: 50, // Cambiar la paginación a 50 entradas
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json', // Configuración de idioma español
                info: "Mostrando _START_ a _END_ de _MAX_ registros",
                infoEmpty: "No hay datos disponibles",
                infoFiltered: "(filtrados de un total de _MAX_ registros)"
            },
            dom: '<"top px-4"fli>rt<"bottom"p><"clear">',
            ajax: {
                url: url + '/dashboard/liquidador/get_citas',
                type: "POST",
                data: function (d) {
                    d.filtro_dia = filtroDia;
                    d.filtro_dia_end = filtroDiaEnd;
                    d.filtro_sede = filtroSede;
                    d.filtro_servicio_liquidador = filtroServicioLiquidador;
                    d.filtro_estado_validacion_liquidador = filtroEstadoValidacionLiquidador;
                    d.filtro_estado_pago_liquidador = filtroEstadoPagoLiquidador;
                    d.filtro_search = filtroSearch;
                    d.tipo_cita = tipoCita;
                    // Parámetros necesarios para ordenamiento
                    d.order = d.order;
                    d.columns = d.columns;
                    d.search = d.search;
                }
            },
            columns: [
                { data: 'nombre_cliente' },                // Columna 0
                { data: 'nombre_sede' },                   // Columna 1
                { data: 'reserva_cita' },                  // Columna 2
                { data: 'fecha_create' },                  // Columna 3
                { data: 'estado_liquidador' },             // Columna 4
                { data: 'comentario_liquidador' },         // Columna 5
                { data: 'valor_servicio_liquidador' },     // Columna 6
                { data: 'pago_liquidador' }                // Columna 7
            ],
            columnDefs: [
                {
                    targets: 0, // Checkbox select cita
                    orderable: false,
                    render: function (data, type, full, meta) {
                        // Solo superadmin ve el checkbox
                        if (canViewPagoMasivo) {
                            return `<input type="checkbox" class="check-cita form-check-input"
                                        data-id-cita="${full.id_cita}"
                                        data-id-liquidador="${full.id_liquidador}"
                                        data-valor="${full.valor_servicio_liquidador}">`;
                        } else {
                            return '';
                        }
                    }
                },
                {
                    targets: 1, // Cliente
                    render: function (data, type, full, meta) {

                        const bgColor = full.estado_verificado_color; // Color de fondo del estado
                        const textColor = getContrastingTextColor(bgColor); // Color de texto calculado
                        if (full.id_vehiculo) {
                            texto = `
                            <h6 class="m-0">${full.nombre_cliente} ${full.apellido_cliente}</h6>
                            <small>${full.tipo_doc_cliente} ${full.doc_cliente} - Telf: <a href="tel:${full.telefono_cliente}">${full.telefono_cliente}</a></small> <br>
                            <span class="badge bg-label-dark">${full.placa_vehiculo}</span><small class="text-muted ml-2">${full.tipo_vehiculo} </small><br>
                            <span class="badge mt-1" style="background-color:${bgColor} !important; color: ${textColor}!important;">${full.estado_verificado_nombre}</span>`;
                        } else {
                            texto = `
                            <h6 class="m-0">${full.nombre_cliente} ${full.apellido_cliente}</h6>
                            <small>${full.tipo_doc_cliente} ${full.doc_cliente} - Telf: <a href="tel:${full.telefono_cliente}">${full.telefono_cliente}</a></small> <br>
                            <span class="badge" style="background-color:${bgColor} !important; color: ${textColor}!important;">${full.estado_verificado_nombre}</span>`
                        }
                        return texto;
                    }
                },
                {
                    targets: 2, // Sede
                    render: function (data, type, full, meta) {
                        return `<span class="badge bg-label-dark">${full.nombre_sede}</span>`;
                    }
                },
                {
                    targets: 3, // Fecha Cita
                    render: function (data, type, full, meta) {
                        let fecha = full.reserva_cita.split(" ")[0];
                        return `<h6 class="m-0">${fecha} ${full.rango_horario}</h6>`;
                    }
                },
                {
                    targets: 4, // Fecha Creación
                    render: function (data, type, full, meta) {
                        return `<h6 class="m-0">${full.fecha_create}</h6>`;
                    }
                },
                {
                    targets: 5, // Servicio liquidador
                    render: function (data, type, full, meta) {
                        var bgColor = "#e5e5e5"; // Color de fondo del servicio por defecto
                        if (full.color_servicio_liquidador) {
                            bgColor = full.color_servicio_liquidador; // Color de fondo del servicio
                        }
                        const textColor = getContrastingTextColor(bgColor); // Color de texto calculado
                        // Si no tiene servicio, mostrará “Selecciona un servicio”
                        let currentEstadoServiceName = full.estado_liquidador
                            ? full.estado_liquidador
                            : 'Sin servicio seleccionado';

                        if (currentEstadoServiceName == "Confirmado") {
                            if (canEditValidacionOpConfirmado) {
                                return `
                                <div class="btn-group">
                                    <button type="button" data-nombre_estado="${currentEstadoServiceName}" class="btn btn-label-success dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  >${currentEstadoServiceName}</button>
                                    <ul class="dropdown-menu">
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Confirmado">Confirmado</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Pendiente">Pendiente</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="En validación">En validación</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Errado">Errado</a></li>
                                    </ul>
                                </div>`;
                            } else {
                                return `
                                <div class="btn-group">
                                    <button type="button" data-nombre_estado="${currentEstadoServiceName}" class="btn btn-label-success dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  >${currentEstadoServiceName}</button>
                                    <ul class="dropdown-menu">
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Pendiente">Pendiente</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="En validación">En validación</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Errado">Errado</a></li>
                                    </ul>
                                </div>`;
                            }
                        } else if (currentEstadoServiceName == "Pendiente" || currentEstadoServiceName == "pendiente") {
                            if (canEditValidacionOpConfirmado) {
                                return `
                                <div class="btn-group">
                                    <button type="button" data-nombre_estado="${currentEstadoServiceName}" class="btn btn-label-warning dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  >${currentEstadoServiceName}</button>
                                    <ul class="dropdown-menu">
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Confirmado">Confirmado</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Pendiente">Pendiente</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="En validación">En validación</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Errado">Errado</a></li>
                                    </ul>
                                </div>`;
                            } else {
                                return `
                                <div class="btn-group">
                                    <button type="button" data-nombre_estado="${currentEstadoServiceName}" class="btn btn-label-warning dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  >${currentEstadoServiceName}</button>
                                    <ul class="dropdown-menu">
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Pendiente">Pendiente</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="En validación">En validación</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Errado">Errado</a></li>
                                    </ul>
                                </div>`;
                            }
                        } else if (currentEstadoServiceName == "Errado") {
                            if (canEditValidacionOpConfirmado) {
                                return `
                            <div class="btn-group">
                                <button type="button" data-nombre_estado="${currentEstadoServiceName}" class="btn btn-label-danger dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  >${currentEstadoServiceName}</button>
                                <ul class="dropdown-menu">
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Confirmado">Confirmado</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Pendiente">Pendiente</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="En validación">En validación</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Errado">Errado</a></li>
                                </ul>
                            </div>`;
                            } else {
                                return `
                            <div class="btn-group">
                                <button type="button" data-nombre_estado="${currentEstadoServiceName}" class="btn btn-label-danger dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  >${currentEstadoServiceName}</button>
                                <ul class="dropdown-menu">
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Pendiente">Pendiente</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="En validación">En validación</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Errado">Errado</a></li>
                                </ul>
                            </div>`;
                            }
                        } else if (currentEstadoServiceName == "En validación") {
                            if (canEditValidacionOpConfirmado) {
                                return `
                            <div class="btn-group">
                                <button type="button" data-nombre_estado="${currentEstadoServiceName}" class="btn btn-label-info dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  >${currentEstadoServiceName}</button>
                                <ul class="dropdown-menu">
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Confirmado">Confirmado</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Pendiente">Pendiente</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="En validación">En validación</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Errado">Errado</a></li>
                                </ul>
                            </div>`;
                            } else {
                                return `
                                <div class="btn-group">
                                    <button type="button" data-nombre_estado="${currentEstadoServiceName}" class="btn btn-label-info dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  >${currentEstadoServiceName}</button>
                                    <ul class="dropdown-menu">
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Pendiente">Pendiente</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="En validación">En validación</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Errado">Errado</a></li>
                                    </ul>
                                </div>`;
                            }
                        } else {
                            if (canEditValidacionOpConfirmado) {
                                return `
                            <div class="btn-group">
                                <button type="button" data-nombre_estado="${currentEstadoServiceName}" class="btn btn-label-secondary dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  >${currentEstadoServiceName}</button>
                                <ul class="dropdown-menu">
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Confirmado">Confirmado</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Pendiente">Pendiente</a></li>
                                    <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="En validación">En validación</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Errado">Errado</a></li>
                                </ul>
                            </div>`;
                            } else {
                                return `
                            <div class="btn-group">
                                <button type="button" data-nombre_estado="${currentEstadoServiceName}" class="btn btn-label-secondary dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false"  >${currentEstadoServiceName}</button>
                                <ul class="dropdown-menu">
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Pendiente">Pendiente</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="En validación">En validación</a></li>
                                <li><a class="dropdown-item waves-effect change_estado_servicio_liquidador" data-id_cita="${full.id_cita}" data-id_liquidador="${full.id_liquidador}" data-estado_liquidador="Errado">Errado</a></li>
                                </ul>
                            </div>`;
                            }
                        }
                    }
                },
                {
                    targets: 6, // Comentario Liquidador
                    orderable: false,
                    render: function (data, type, full, meta) {
                        // data = full.comentario_liquidador
                        let hasComment = (full.comentario_liquidador && full.comentario_liquidador.trim() !== '');
                        let iconColor = hasComment ? 'text-success' : 'text-muted';
                        let iconNotify = hasComment ? `<i class="ti ti-circle-filled text-danger" style="font-size:10px; position:absolute; right:0; top:0;"></i>` : '';

                        // Si existen anotaciones, mostramos también la cantidad en un badge
                        let badgeAnotaciones = '';
                        iconColorAnotaciones = 'text-muted';
                        if (full.total_anotaciones && parseInt(full.total_anotaciones) > 0) {
                            badgeAnotaciones = `<span class="badge rounded-pill text-bg-danger badge-notifications px-1">${full.total_anotaciones}</span>`;
                            iconColorAnotaciones = 'text-success';
                        }

                        return `
                        <div style="position:relative; display:inline-block;">
                            <button type="button"
                                class="btn btn-sm btn-light btn-open-comment-modal ${iconColor}"
                                data-id-cita="${full.id_cita}"
                                data-id-liquidador="${full.id_liquidador}"
                                data-comentario="${full.comentario_liquidador || ''}"
                                data-nombre-cliente="${full.nombre_cliente || ''}"
                                data-doc-cliente="${full.doc_cliente || ''}"
                                data-tipo-doc-cliente="${full.tipo_doc_cliente || ''}"

                                 >
                                <i class="ti ti-message-circle"></i>
                            </button>
                            ${iconNotify}
                        </div>
                        <div style="position:relative; display:inline-block;">
                            <!-- Botón para ver el seguimiento -->
                            <button type="button"
                                class="btn btn-sm btn-light btn-open-seguimiento-modal ${iconColorAnotaciones}"
                                data-id-cita="${full.id_cita}"
                                title="Ver seguimiento">
                                <i class="ti ti-eye"></i>
                            </button>
                            ${badgeAnotaciones}
                        </div>
                      `;
                    }
                },
                {
                    targets: 7,
                    render: function (data, type, full, meta) {
                        var bgColor = "#e5e5e5"; // Color de fondo del servicio por defecto
                        if (full.color_servicio_liquidador) {
                            bgColor = full.color_servicio_liquidador; // Color de fondo del servicio
                        }
                        const textColor = getContrastingTextColor(bgColor); // Color de texto calculado
                        // Si no tiene servicio, mostrará “Selecciona un servicio”
                        let currentServiceName = full.nombre_servicio_liquidador
                            ? full.nombre_servicio_liquidador
                            : 'Sin servicio seleccionado';
                        // Aseguramos que el valor sea un número y lo formateamos con separadores de miles
                        let valor = parseFloat(full.valor_servicio_liquidador) || 0;
                        let formattedValor = valor.toLocaleString('es-ES'); // Ejemplo: 50000 -> "50.000"
                        return `
                        <h4>$${formattedValor}</h4>
                        <span class="badge" style="background-color:${bgColor} !important; color: ${textColor}!important;">${currentServiceName}</span>`;

                    }
                },
                {
                    targets: 8,
                    render: function (data, type, full, meta) {
                        if (full.pago_liquidador == null || full.pago_liquidador == 'null' || full.pago_liquidador == 'Pendiente' || full.pago_liquidador == 'pendiente') {
                            return `<span class="badge bg-label-secondary">${full.pago_liquidador}</span>`;
                        } else {
                            return `<span class="badge bg-label-success">${full.pago_liquidador}</span>`;
                        }
                    }
                }
            ],
            initComplete: function (settings, json) {
                if (json && json.extra) {
                    updateTotales(json.extra);
                }

            },
            pagingType: "simple"
        });
        // Evento que se dispara cada vez que se recibe una respuesta AJAX
        table.on('xhr.dt', function (e, settings, json, xhr) {
            if (json && json.extra) {
                updateTotales(json.extra);
            }
        });

        // Eventos para los filtros
        $('#filtro-cuatro-meses-anteriores').on('click', function () {
            const ahora = new Date();
            const inicioMes = new Date(ahora.getFullYear(), ahora.getMonth() - 4, 1);
            const finMes = new Date(ahora.getFullYear(), ahora.getMonth() - 3, 0);
            filtroDia = formatDate(inicioMes);
            filtroDiaEnd = formatDate(finMes);
            table.ajax.reload();
        });

        $('#filtro-tres-meses-anteriores').on('click', function () {
            const ahora = new Date();
            const inicioMes = new Date(ahora.getFullYear(), ahora.getMonth() - 3, 1);
            const finMes = new Date(ahora.getFullYear(), ahora.getMonth() - 2, 0);
            filtroDia = formatDate(inicioMes);
            filtroDiaEnd = formatDate(finMes);
            table.ajax.reload();
        });

        $('#filtro-dos-meses-anteriores').on('click', function () {
            const ahora = new Date();
            const inicioMes = new Date(ahora.getFullYear(), ahora.getMonth() - 2, 1);
            const finMes = new Date(ahora.getFullYear(), ahora.getMonth() -1, 0);
            filtroDia = formatDate(inicioMes);
            filtroDiaEnd = formatDate(finMes);
            table.ajax.reload();
        });

        $('#filtro-mes-anterior').on('click', function () {
            const ahora = new Date();
            const inicioMes = new Date(ahora.getFullYear(), ahora.getMonth() - 1, 1);
            const finMes = new Date(ahora.getFullYear(), ahora.getMonth(), 0);
            filtroDia = formatDate(inicioMes);
            filtroDiaEnd = formatDate(finMes);
            table.ajax.reload();
        });

        $('#filtro-mes-actual').on('click', function () {
            const ahora = new Date();
            const inicioMes = new Date(ahora.getFullYear(), ahora.getMonth(), 1);
            const finMes = new Date(ahora.getFullYear(), ahora.getMonth() + 1, 0);
            filtroDia = formatDate(inicioMes);
            filtroDiaEnd = formatDate(finMes);
            table.ajax.reload();
        });

        // Eventos para los filtros
        $('#filtro-fecha').on('change', function () {
            filtroDia = $(this).val();
            table.ajax.reload();
        });
        $('#filtro-fecha-end').on('change', function () {
            filtroDiaEnd = $(this).val();
            table.ajax.reload();
        });
        $('#filtro-sede').on('change', function () {
            filtroSede = $(this).val();
            table.ajax.reload();
        });
        $('#filtro-servicios_liquidador').on('change', function () {
            filtroServicioLiquidador = $(this).val();
            table.ajax.reload();
        });
        $('#filtro-estado-validacion-liquidador').on('change', function () {
            filtroEstadoValidacionLiquidador = $(this).val();
            table.ajax.reload();
        });
        $('#filtro-estado-pago-liquidador').on('change', function () {
            filtroEstadoPagoLiquidador = $(this).val();
            table.ajax.reload();
        });
        $('#woow-search-citas').on('keyup', function () {
            filtroSearch = $(this).val();
            table.ajax.reload();
        });
        $('#filtro-reset').on('click', function () {
            filtroDia = '';
            filtroDiaEnd = '';
            filtroSede = '';
            filtroServicioLiquidador = '';
            filtroEstadoValidacionLiquidador = '';
            filtroEstadoPagoLiquidador = '';
            filtroEstado = '';
            filtroEstadoVerificado = '';
            filtroResponsable = '';
            filtroOrigen = '';
            filtroSearch = '';
            $('#filtro-dia').val("").trigger('input');
            $('#filtro-dia-end').val("").trigger('input');
            $('#filtro-sede').val("").trigger('change');
            $('#filtro-servicios_liquidador').val("").trigger('change');
            $('#filtro-estado-validacion-liquidador').val("").trigger('change');
            $('#filtro-estado-pago-liquidador').val("").trigger('change');
            $('#filtro-estado').val("").trigger('change');
            $('#filtro-responsable').val("").trigger('change');
            $('#filtro-estado-verificado').val("").trigger('change');
            $('#filtro-origen').val("").trigger('change');
            $('#woow-search-citas').val("").trigger('input');
            table.ajax.reload();
        });
        // Función para formatear fecha en formato 'YYYY-MM-DD'
        function formatDate(date) {
            const d = new Date(date);
            let month = '' + (d.getMonth() + 1);
            let day = '' + d.getDate();
            const year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }
        //Cambiamos el estado
        $('.datatables-citas').on('click', '.change_estado_cita_verificado', function () {
            let id_estado = $(this).data('id_estado');
            let id_cita = $(this).data('id_cita');
            $.ajax({
                url: url + '/dashboard/citas/change_estado_verificado',
                type: 'POST',
                data: {
                    id_estado: id_estado,
                    id_cita: id_cita
                },
                success: function (data) {
                    table.ajax.reload();
                }
            });
        });

        //Cambiamo el estado
        $('.datatables-citas-liquidador').on('click', '.change_estado_servicio_liquidador', function () {
            let id_liquidador = $(this).data('id_liquidador');
            let id_cita = $(this).data('id_cita');
            let estado_liquidador = $(this).data('estado_liquidador');
            $.ajax({
                url: url + '/dashboard/liquidador/change_estado_servicio_liquidador',
                type: 'POST',
                data: {
                    id_liquidador: id_liquidador,
                    id_cita: id_cita,
                    estado_liquidador: estado_liquidador
                },
                success: function (data) {
                    table.ajax.reload();
                }
            });
        });



        // Función para actualizar los contadores en la interfaz
        function updateTotales(extra) {
            $('#rango-fecha').html(extra.fecha_inicio);
            $('#rango-fecha-fin').html(extra.fecha_fin);
            $('#lblTotalValorALiquidar').html(parseFloat(extra.total_valor_a_liquidar).toLocaleString('es-ES'));
            $('#lblTotalValorLiquidado').html(parseFloat(extra.total_valor_liquidado).toLocaleString('es-ES'));
            $('#lblTotalConfirmados').html(extra.total_confirmados);
            $('#lblTotalErrados').html(extra.total_errados);
            $('#lblTotalPendientes').html(extra.total_pendientes);
            $('#lblTotalValidacion').html(extra.total_validacion);
            $('#lblPagoPendiente').html(extra.total_pago_pendiente);
            $('#lblPagoPagado').html(extra.total_pago_pagado);
        }

        const fechaActual = new Date();
        const mesActual = fechaActual.getMonth();
        const meses = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        function ajustarIndiceMes(offset) {
            return (mesActual - offset + 12) % 12;
        }

        $('#filtro-mes-actual').text(meses[fechaActual.getMonth()]);
        $('#filtro-mes-anterior').text(meses[ajustarIndiceMes(1)]);
        $('#filtro-dos-meses-anteriores').text(meses[ajustarIndiceMes(2)]);
        $('#filtro-tres-meses-anteriores').text(meses[ajustarIndiceMes(3)]);
        $('#filtro-cuatro-meses-anteriores').text(meses[ajustarIndiceMes(4)]);
    }

    //TABLA DE SERVICIO LIQUIDADOR DE CITAS
    if ($('.datatables-servicios-liquidador').length) {
        table_estados = $('.datatables-servicios-liquidador').DataTable({
            lengthChange: false,
            searching: false,
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: url + '/dashboard/liquidador/get_servicio_liquidador',
                type: "POST"
            },
            column: [{ data: '' }],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, full, meta) {
                        const bgColor = full.color_servicio_liquidador; // Color de fondo del estado
                        const textColor = getContrastingTextColor(bgColor); // Color de texto calculado
                        return '<span class="badge bg-label-dark" style="background-color: ' + bgColor + ' !important; color: ' + textColor + '!important;">' + full.nombre_servicio_liquidador + '</span>';
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, full, meta) {
                        return '<span class="badge bg-label-primary"> $' + full.valor_servicio_liquidador + '</span>';
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, full, meta) {
                        return `
                        <div class="d-flex justify-content-end">
                            ${(canEditServiciosLiquidador) ? `
                                <a href="${url}/dashboard/liquidador/edit_servicio_liquidador/${full.id_servicio_liquidador}" class="btn btn-icon btn-label-primary waves-effect me-2">
                                    <i class="tf-icons ti ti-edit ti-md"></i>
                                </a>` : ``}
                            ${(canDeleteServiciosLiquidador) ? `
                                <button type="button" data-id="${full.id_servicio_liquidador}" class="btn_delete_estado btn btn-icon btn-label-danger waves-effect">
                                    <i class="tf-icons ti ti-trash ti-md"></i>
                                </button>` : ``}
                        </div>`	;
                    }
                },
            ],
            pagingType: "simple"
        });
        //ELIMINAR SERVICIO
        $('.datatables-servicios-liquidador').on('click', '.btn_delete_estado', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: '¿Estas seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '¡Sí, bórralo!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ml-1'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url + '/dashboard/citas/delete_estados',
                        type: 'POST',
                        data: { id: id },
                        success: function (data) {
                            if (data.validate) {
                                table_estados.ajax.reload();
                            } else {
                                alertNotify('¡Error!', data.text, 'error');
                            }
                        }
                    });
                }
            });
        });
    }

    // Modal para editar comentario de liquidador
    $('.datatables-citas-liquidador').on('click', '.btn-open-comment-modal', function () {
        let idCita = $(this).data('id-cita');
        let idLiquidador = $(this).data('id-liquidador');
        let comentario = $(this).data('comentario');
        let nombreCliente = $(this).data('nombre-cliente');
        let docCliente = $(this).data('doc-cliente');
        let tipoDocCliente = $(this).data('tipo-doc-cliente');

        $('#comentario-id-cita').val(idCita);
        $('#comentario-id-liquidador').val(idLiquidador);
        $('#comentario-text').val(comentario);
        $('#comentario_nombre_cliente').text(nombreCliente);
        $('#comentario_tipo_doc_cliente').text(tipoDocCliente);
        $('#comentario_doc_cliente').text(docCliente);


        $('#modalComentarioLiquidador').modal('show');
    });

    $('.datatables-citas-liquidador').on('click', '.btn-open-seguimiento-modal', function () {
        let idCita = $(this).data('id-cita');

        $.ajax({
            url: url + '/dashboard/citas/get_seguimiento_cita', // Asegúrate de tener este endpoint en tu backend
            method: 'POST',
            data: { id_cita: idCita },
            success: function (response) {
                if (response.validate) {
                    Swal.fire({
                        title: 'Seguimiento de la Cita',
                        html: response.html,
                        width: '600px',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-outline-danger ml-1 d-none'
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.text || 'No se pudo obtener el seguimiento.',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-outline-danger ml-1 d-none'
                        }
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo conectar con el servidor.',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ml-1 d-none'
                    }
                });
            }
        });
    });


    // Función para actualizar el comentario de liquidador
    $('#formComentarioLiquidador').on('submit', function (e) {
        e.preventDefault();
        let formData = $(this).serialize();

        $.ajax({
            url: url + '/dashboard/liquidador/updateComentario',
            method: 'POST',
            data: formData,
            success: function (resp) {
                if (resp.validate) {
                    // Cerrar modal
                    $('#modalComentarioLiquidador').modal('hide');
                    // Recargar tabla
                    $('.datatables-citas-liquidador').DataTable().ajax.reload(null, false);
                } else {
                    alert(resp.text || 'Error al guardar comentario');
                }
            },
            error: function () {
                alert('Error en la solicitud');
            }
        });
    });


    // Escucha el evento change en los checkboxes de la tabla
    $('.datatables-citas-liquidador').on('change', '.check-cita', function () {
        // Obtener todos los checkboxes seleccionados
        let seleccionados = $('.check-cita:checked');
        let contador = seleccionados.length;
        let sumaTotal = 0;

        // Recorrer cada checkbox seleccionado y sumar el valor
        seleccionados.each(function () {
            let valor = parseFloat($(this).data('valor'));
            if (!isNaN(valor)) {
                sumaTotal += valor;
            }
        });

        // Actualizar los elementos de la vista
        $('#checkbox-selected-contador-citas-pago').text(contador);
        $('#checkbox-selected-valor-total-pago').text(sumaTotal.toLocaleString('es-ES'));

        // Mostrar o esconder el div y botón según la cantidad de elementos seleccionados
        if (contador > 0) {
            // solo se muestra el botón de cambio de pago si el rol es superadmin
            if (canViewPagoMasivo) {
                // Mostrar el botón de cambio de pago
                $('#checkbox-selected-div-contador-pago').show();
                $('#btnChangePago').show();
            }
        } else {
            // solo se muestra el botón de cambio de pago si el rol es superadmin
            if (canViewPagoMasivo) {
                // Mostrar el botón de cambio de pago
                $('#checkbox-selected-div-contador-pago').hide();
                $('#btnChangePago').hide();
            }
        }
    });

    // Función para marcar todos los registros
    $('#checkAll').on('change', function () {
        // Cambiar el estado de todos los registros
        let checked = $(this).is(':checked');
        // Marcar todos los registros
        $('.check-cita').prop('checked', checked).trigger('change');
    });



    // Función para cambiar el estado de pago de los registros
    $('#btnChangePago').on('click', function () {
        // Array de objetos con los IDs de cita y liquidador
        let seleccionados = [];
        $('.check-cita:checked').each(function () {
            seleccionados.push({
                id_cita: $(this).data('id-cita'),
                id_liquidador: $(this).data('id-liquidador')
            });
        });

        if (seleccionados.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Sin selección',
                text: 'No has seleccionado citas para actualizar.',
                showDenyButton: false,
                showCancelButton: false,
            });
            return;
        }

        // Preguntamos con un select en SweetAlert2 o con un prompt
        Swal.fire({
            title: 'Cambiar estado de pago',
            text: '¿Deseas marcar como "pagado" o "pendiente"?',
            icon: 'question',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Pagado',
            denyButtonText: 'Pendiente',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            let nuevoEstado = '';
            if (result.isConfirmed) {
                // Usuario presionó "Pagado"
                nuevoEstado = 'pagado';
            } else if (result.isDenied) {
                // Usuario presionó "Pendiente"
                nuevoEstado = 'pendiente';
            } else {
                // Cualquier otro caso (cerrar sin elegir)
                return;
            }

            // Enviar la solicitud AJAX
            $.ajax({
                url: url + '/dashboard/liquidador/updatePagoMasivo',
                method: 'POST',
                data: {
                    citas: seleccionados,
                    pago_liquidador: nuevoEstado
                },
                success: function (resp) {
                    if (resp.validate) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: resp.text || 'Estado de pago actualizado correctamente.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        // Recargar la tabla sin cambiar de página
                        $('.datatables-citas-liquidador').DataTable().ajax.reload(null, false);
                        $('#checkAll').prop('checked', false).trigger('change');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: resp.text || 'Ocurrió un error al cambiar el estado de pago.'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo conectar con el servidor.'
                    });
                }
            });
        });
    });

    if ($('#codigo_comparendo_tagify').length) {

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
        codigo_comparendo_tagify = new Tagify(codigo_comparendo, {
            whitelist: whitelist,
            maxTags: 5, // allows to select max items
            dropdown: {
                maxItems: 20, // display max items
                classname: "tags-inline", // Custom inline class
                enabled: 0,
                closeOnSelect: false
            }
        });
    }

    // VISTA DE EMPRESAS
    // Previsualización de la imagen seleccionada
    $('#create-empresa-file').on('change', function () {
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#preview-image').attr('src', e.target.result);
                $('#preview-container').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#preview-image').attr('src', '');
            $('#preview-container').hide();
        }
    });

    // Botón para quitar la imagen seleccionada
    $('#btn-cancel-image').on('click', function () {
        $('#create-empresa-file').val('');
        $('#preview-image').attr('src', '');
        $('#preview-container').hide();
        $('#logo-url').val('');
    });

    $('#btn-crear-empresa').on('click', function () {
        $('#createEmpresaModalLabel').text('Crear Empresa');
        $('#createEmpresaForm').attr('action', url + '/dashboard/empresas/guardar');
        $('#createEmpresaForm')[0].reset();
        $('#create-empresa-plan').val('').trigger('change');
        $('#create-empresa-file').val('');
        $('#preview-image').attr('src', '');
        $('#preview-container').hide();
        $('#createEmpresaModal').modal('show');
    });

    // Formulario para crear una nueva empresa
    $('#createEmpresaForm').on('submit', function (event) {
        event.preventDefault();
        let action = $(this).attr('action');
        let form = $(this);
        let fileInput = $('#create-empresa-file')[0];
        let file = fileInput.files[0];

        function guardarEmpresa() {
            let formData = new FormData(form[0]);
            $.ajax({
                url: action,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.Success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Exito!',
                            text: response.Message,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary',
                            }
                        }).then(() => {
                            $('#createEmpresaModal').modal('hide');
                            $('#createEmpresaForm')[0].reset();
                            $('#preview-image').attr('src', '');
                            $('#preview-container').hide();
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '¡Error!',
                            text: response.Message,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary',
                            }
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al enviar los datos de la empresa.',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                        }
                    });
                }
            });
        }

        if (file) {
            let imgData = new FormData();
            imgData.append('file', file);

            $.ajax({
                url: 'empresas/guardar-logo',
                type: 'POST',
                data: imgData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.Success) {
                        $('#logo-url').val(response.Data);
                        guardarEmpresa();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '¡Error!',
                            text: response.Message,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary',
                            }
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo subir el logo de la empresa.',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                        }
                    });
                }
            });
        } else {
            guardarEmpresa();
        }
    });

    //Botón para abrir el modal de editar empresa
    $('.btn-edit-empresa').on('click', function () {
        const id = $(this).data('empresa-id');
        const nombre = $(this).data('empresa-nombre');
        const descripcion = $(this).data('empresa-descripcion');
        const tipo_documento = $(this).data('empresa-tipo-documento');
        const numero_documento = $(this).data('empresa-documento');
        const plan = $(this).data('empresa-plan');
        const logo = $(this).data('empresa-logo');
        const plantilla = $(this).data('empresa-plantilla');

        $('#createEmpresaModalLabel').text('Editar Empresa');
        $('#createEmpresaForm').attr('action', url + '/dashboard/empresas/actualizar/' + id);
        $('#create-empresa-name').val(nombre);
        $('#create-empresa-description').val(descripcion);
        $('#create-empresa-document-type').val(tipo_documento);
        $('#create-empresa-document-number').val(numero_documento);
        $('#create-empresa-plan').val(plan);
        $('#create-empresa-template').val(plantilla);

        if (logo && logo !== '') {
            $('#preview-image').attr('src', logo.startsWith('http') ? logo : (url + '/' + logo.replace(/^\/+/, '')));
            $('#preview-container').show();
            $('#logo-url').val(logo);
        } else {
            $('#preview-image').attr('src', '');
            $('#preview-container').hide();
            $('#logo-url').val('');
        }

        $('#createEmpresaModal').modal('show');
    });

    // Botón para cambiar de estado de la empresa
    $('.btn-estado-empresa').on('click', function () {
        const id = $(this).data('empresa-id');

        $.ajax({
            url: url + '/dashboard/empresas/cambiar_estado',
            type: 'POST',
            data: {
                id_empresa: id
            },
            success: function (response) {
                if (response.Success) {
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: response.Message,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                        }
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cambiar el estado de la empresa.',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                    }
                });
            }
        });
    });

    // VISTA DE DASHBOARD EMPRESAS
    function consultarTodasEmpresas() {
        $.ajax({
            url: url + '/dashboard/empresa/consultar_todas_empresas',
            type: 'GET',
            success: function (response) {
                if (response.Success) {
                    if (response.Data) {
                        $('#empresa-select-dashboard option:not(:first)').remove();
                        response.Data.forEach(empresa => {
                            $('#empresa-select-dashboard').append(
                                $('<option>', {
                                    value: empresa.id_empresa,
                                    text: empresa.Nombre,
                                })
                            );
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: response.Message,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: "Ocurrió un error al obtener los datos de las empresas.",
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            }
        });
    }

    function consultarEmpresaUsuario(id_sede = sede) {
        $.ajax({
            url: url + '/dashboard/empresa/consultar_empresa_por_sede/' + id_sede,
            type: 'GET',
            success: function (response) {
                if (response.Success) {
                    if (response.Data) {
                        $('#logo-empresa-dashboard').attr('src', `${url}/${response.Data.logo}`);
                        $('#nombre-empresa-dashboard').html(`Nombre: <strong>${response.Data.Nombre}</strong>`);
                        $('#documento-empresa-dashboard').html(`Documento: <strong>${response.Data.tipo_documento_empresa} ${response.Data.documento_empresa}</strong>`);
                       if (rol == 'superadmin' || rol == 'admin') {
                            $('#plan-empresa-dashboard').html(`Plan: <strong>${response.Data.plan_empresa}</strong>`);
                        }
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: response.Message,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: "Ocurrió un error al obtener los datos de la empresa.",
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            }
        });
    }

    function consultarEmpresa(id_empresa) {
        $.ajax({
            url: url + '/dashboard/empresa/consultar_empresa/' + id_empresa,
            type: 'GET',
            success: function (response) {
                if (response.Success) {
                    if (response.Data) {
                        $('#logo-empresa-dashboard').attr('src', `${url}/${response.Data.logo}`);
                        $('#nombre-empresa-dashboard').html(`Nombre: <strong>${response.Data.Nombre}</strong>`);
                        $('#documento-empresa-dashboard').html(`Documento: <strong>${response.Data.tipo_documento_empresa} ${response.Data.documento_empresa}</strong>`);
                        if (rol == 'superadmin' || rol == 'admin') {
                            $('#plan-empresa-dashboard').html(`Plan: <strong>${response.Data.plan_empresa}</strong>`);
                        }
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: response.Message,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: "Ocurrió un error al obtener los datos de la empresa.",
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            }
        });
    }

    function obtenerDatosProgressBarDashboardEmpresa(endpoint, chart, id_empresa = null) {
        $.ajax({
            url: url + endpoint,
            type: 'POST',
            data: {
                id_empresa: id_empresa
            },
            success: function (response) {
                if (response.Success) {
                    if (response.Data) {
                        $('#nombre-paquete-activo-dashboard-empresa').html(`Nombre: <strong>${response.Data.nombre_paquete}</strong>`);
                        $('#numero-citas-paquete-activo-dashboard-empresa').html(`Número de citas: <strong>${response.Data.numero_citas}</strong>`);
                        $('#estado-paquete-activo-dashboard-empresa').html(`Estado: <strong>${response.Data.estado}</strong>`);
                        $('#citas-consumidas-paquete-activo-dashboard-empresa').html(`<strong>${response.Data.citas_consumidas}</strong>`);
                        $('#citas-faltantes-paquete-activo-dashboard-empresa').html(`<strong>${response.Data.citas_faltantes}</strong>`);
                        $('#citas-confirmadas-paquete-activo-dashboard-empresa').text(`${response.Data.citasConfirmadas}`);
                        $('#citas-erradas-paquete-activo-dashboard-empresa').text(`${response.Data.citasErradas}`);
                        $('#citas-validacion-paquete-activo-dashboard-empresa').text(`${response.Data.citasEnValidacion}`);
                        $('#citas-pendientes-paquete-activo-dashboard-empresa').text(`${response.Data.citasPendientes}`);
                        chart.updateSeries([
                            {
                                data: [response.Data.citas_consumidas]
                            },
                            {
                                data: [response.Data.citas_faltantes]
                            }
                        ]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: response.Message,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: "Ocurrió un error al obtener los datos de la barra de progreso.",
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            }
        });
    }

    function obtenerDatosLineBarMixedDashboardEmpresa(endpoint, chart, id_empresa = null) {
        $.ajax({
            url: url + endpoint,
            type: 'POST',
            data: {
                id_empresa: id_empresa
            },
            success: function (response) {
                if (response.Success) {
                    if (response.Data) {
                        chart.updateSeries([
                            {
                                data: response.Data.citasAgendadasPorMesActual
                            },
                            {
                                data: response.Data.citasAsistidasPorMesActual
                            }
                        ]);
                        chart.updateOptions({
                            yaxis: [{
                                max: response.Data.numero_citas
                            }]
                        })
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: response.Message,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: "Ocurrió un error al obtener los datos de la barra de progreso.",
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            }
        });
    }

    function actualizarGraficosHistorial(datos) {
        const container = document.getElementById('ChartRadialProgressDashboardEmpresasHistorial');

        // Limpiar contenedor
        container.innerHTML = '';

        // Crear un gráfico por cada paquete
        datos.forEach((paquete, index) => {
            const chartId = `chart${index + 1}`;

            // Crear contenedor para el gráfico
            const chartContainer = document.createElement('div');
            chartContainer.id = chartId;
            chartContainer.className = 'py-auto h-50';
            container.appendChild(chartContainer);

            // Configuración del gráfico
            const chartOptions = {
                series: [paquete.porcentaje],
                chart: {
                    height: 130,
                    type: 'radialBar'
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 135,
                        dataLabels: {
                            name: {
                                fontSize: '14px',
                                offsetY: 90
                            },
                            value: {
                                offsetY: 50,
                                fontSize: '18px',
                                formatter: (val) => `${val}%`
                            }
                        }
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        shadeIntensity: 0.15,
                        inverseColors: false,
                        stops: [0, 50, 65, 91]
                    }
                },
                stroke: {
                    dashArray: 4
                },
                labels: [''],
                colors: ['#b5ba30']
            };

            // Renderizar gráfico
            const chart = new ApexCharts(document.querySelector(`#${chartId}`), chartOptions);
            chart.render();
        });
    }

    function actualizarInfoHistorial(datos) {
        const container = document.getElementById('ChartRadialProgressDashboardEmpresasHistorialInfo');

        // Limpiar contenedor
        container.innerHTML = '';

        //Crea la informmación para cada paquete
        datos.forEach((paquete, index) => {
            const infoId = `info${index + 1}`;

            const infoContainer = document.createElement('div');
            infoContainer.id = infoId;
            infoContainer.className = 'mb-5';
            container.appendChild(infoContainer);
            paquete.fecha_inicio = paquete.fecha_inicio.split(' ')[0];

            if (!paquete.fecha_fin) {
                paquete.fecha_fin = "ACTIVO";
            } else {
                paquete.fecha_fin = paquete.fecha_fin.split(' ')[0];
            }

            infoContainer.innerHTML = `
                <h5 class="text-info pt-5 mb-2">Nombre: <strong class="text-dark">${paquete.nombre_paquete}</strong></h5>
                <h6 class="text-info mb-2"><strong>${paquete.fecha_inicio} - ${paquete.fecha_fin}</strong></h6>
                <h6 class="text-info mb-2">Número de citas: <strong class="text-dark">${paquete.numero_citas}</strong></h6>
                <h6 class="text-info mb-5">Precio: <strong class="text-dark">${paquete.valor}</strong></h6>
            `;
        });
    }

    function actualizarBotonesHistorial(datos) {
        const container = document.getElementById('ChartRadialProgressDashboardEmpresasHistorialBoton');

        // Limpiar contenedor
        container.innerHTML = '';

        datos.forEach((paquete, index) => {
            const botonId = `boton${index + 1}`;

            const botonContainer = document.createElement('div');
            botonContainer.id = botonId;
            botonContainer.className = 'mt-5';
            botonContainer.style.marginBottom = '100px';
            container.appendChild(botonContainer);

            botonContainer.innerHTML = `
                <button class="btn btn-icon btn-lg waves-effect btn-detalles-paquete w-100 text-center" 
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    data-bs-custom-class="tooltip-info" 
                    title="Editar"
                    data-id-empresa-paquete="${paquete.id_empresa_paquete}"
                    data-bs-target="#modalDetallesPaquete">
                    <i class="ti ti-checkup-list me-2 text-info" style="font-size:50px;"></i>
                </button>
            `;
        });
    }

    function obtenerDatosHistorialPaquetes(endpoint, id_empresa = null) {
        $.ajax({
            url: url + endpoint,
            type: 'POST',
            data: {
                id_empresa: id_empresa
            },
            success: function (response) {
                if (response.Success) {
                    if (response.Data) {
                        actualizarGraficosHistorial(response.Data);
                        actualizarInfoHistorial(response.Data);
                        actualizarBotonesHistorial(response.Data);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: response.Message,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: "Ocurrió un error al obtener los datos del historial de paquetes.",
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            }
        });
    }

    function actualizarListadoPaquetesPendientes(datos) {
        const container = document.getElementById('ListadopaquetesPendientes');

        // Limpiar contenedor
        container.innerHTML = '';

        datos.forEach((paquete, index) => {
            const paquetePendienteId = `paquetePendiente${index + 1}`;

            const paquetePendienteContainer = document.createElement('div');
            paquetePendienteContainer.id = paquetePendienteId;
            paquetePendienteContainer.className = 'row';
            container.appendChild(paquetePendienteContainer);

            paquetePendienteContainer.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h4 style="color: #b5ba30;"><i class="ti ti-package-import display-1"></i></h4>
                    <div>
                        <h5 class="mb-1 text-info">Nombre: <strong class="text-dark">${paquete.nombre_paquete}</strong></h5>
                        <h5 class="mb-1 text-info">Fecha de compra: <strong class="text-dark">${paquete.created_at.split(' ')[0]}</strong></h5>
                        <h5 class="mb-1 text-info">Número de citas: <strong class="text-dark">${paquete.numero_citas}</strong></h5>
                    </div>
                </div>
            `;
        });
    }

    function obtenerListadoPaquetesPendientes(endpoint, id_empresa = null) {
        $.ajax({
            url: url + endpoint,
            type: 'POST',
            data: {
                id_empresa: id_empresa
            },
            success: function (response) {
                if (response.Success) {
                    if (response.Data) {
                        actualizarListadoPaquetesPendientes(response.Data);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: response.Message,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: "Ocurrió un error al obtener los paquetes pendientes.",
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            }
        });
    }

    function validarPaqueteActivo (id_empresa = null) {
        $.ajax({
            url: url + '/dashboard/empresa/obtener_datos_barra_progreso',
            type: 'POST',
            data: {
                id_empresa: id_empresa
            },
            success: function (response) {
                if (response.Success) {
                    if (response.Data) {
                        const citas_restantes = response.Data.numero_citas - response.Data.citas_consumidas;
                        // CUANDO NO TIENE UN PAQUETE ACTIVO
                        if (rol == "Admin Empresa") {
                            if (citas_restantes <= 15 && citas_restantes > 0) {
                                $('#img-pop-up-paquetes').attr('src', `${url}/assets/img/paquetes/aviso_paquetes.jpg`);
                                $('#aviso-url').attr('href', `${page_wp_aliados}`);
                                $('#popupPaquetes').modal({
                                    backdrop: 'static',
                                    keyboard: false
                                });
                                $('#popupPaquetes').modal('show');
                            } else if (citas_restantes == 0 || response.Data.tipo_paquete == "AUXILIAR") {
                                $('#img-pop-up-paquetes').attr('src', `${url}/assets/img/paquetes/fin_paquetes.jpg`);
                                $('#aviso-url').attr('href', `${page_wp_aliados}`);
                                 $('#popupPaquetes').find('.btn-close').hide();
                                $('#popupPaquetes').modal({
                                    backdrop: 'static',
                                    keyboard: false
                                });
                                $('#popupPaquetes').modal('show');
                            }
                        }
                    } else {
                        // PARA CUANDO NO TIENE UN PAQUETE ACTIVO
                        if ((rol == "Admin Empresa" && response.Data.tipo_paquete == "AUXILIAR") || (rol == "Admin Empresa")) {
                            $('#img-pop-up-paquetes').attr('src', `${url}/assets/img/paquetes/fin_paquetes.jpg`);
                            $('#aviso-url').attr('href', `${page_wp_aliados}`);
                            $('#popupPaquetes').find('.btn-close').hide();
                            $('#popupPaquetes').modal({
                                    backdrop: 'static',
                                    keyboard: false
                                });
                            $('#popupPaquetes').modal('show');
                        }
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: response.Message,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: "Ocurrió un error al obtener los datos de la barra de progreso.",
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            }
        });
    }

    // SE VALIDA EL POP-UP DE PAQUETES
    if ($('#popupPaquetes').length) {
        validarPaqueteActivo();
    }

    if ($('#dashboard-empresas').length) {
        // BARRA DE PROGRESO DE CONSUMO DE PAQUETE ACTIVO
        var optionsChartDashboardEmpresasProgressBar = {
            series: [{
                name: 'Citas consumidas',
                data: []
            }, {
                name: 'Citas faltantes',
                data: []
            }],
            chart: {
                type: 'bar',
                height: 200,
                stacked: true,
                stackType: '100%'
            },
            colors: ['#00d2ff', '#b5ba30'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 5,
                },
            },
            stroke: {
                width: 1,
                colors: ['#fff']
            },
            title: {
                text: '% de progreso del paquete',
            },
            xaxis: {
                categories: [''],
                labels: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                axisBorder: {
                    show: false
                }
            },
            grid: {
                show: false,
                padding: {
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " Citas"
                    }
                }
            },
            fill: {
                opacity: 1
            },
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                offsetX: 0
            }
        };

        var ChartDashboardEmpresasProgressBar = new ApexCharts(document.querySelector("#ChartDashboardEmpresasProgressBar"), optionsChartDashboardEmpresasProgressBar);
        ChartDashboardEmpresasProgressBar.render();

        // GRAFICA MIXED DE LAS CITAS EXISTENTES VS LAS ASISTIDAS
        var optionsChartDashboardEmpresasLineBarMixed = {
            series: [{
                name: 'Agendados',
                type: 'column',
                data: []
            }, {
                name: 'Asistidos',
                type: 'line',
                data: []
            }],
            chart: {
                height: 500,
                type: 'line',
            },
            stroke: {
                width: [0, 4]
            },
            title: {
                text: 'Citas Agendadas vs Asistidas del mes actual',
            },
            colors: ['#cecece', '#00d2ff'],
            dataLabels: {
                enabled: true,
                enabledOnSeries: [1]
            },
            labels: [],
            yaxis: [{
                min: 0,
                max: 150,

            }, {
                opposite: true,
                show: false,
                title: {
                    text: 'Asistidos'
                },
                min: 0,
                max: 150,
            }]
        };

        var ChartDashboardEmpresasLineBarMixed = new ApexCharts(document.querySelector("#ChartDashboardEmpresasLineBarMixed"), optionsChartDashboardEmpresasLineBarMixed);
        ChartDashboardEmpresasLineBarMixed.render();

        // Datos de las empresas para el select
        if (rol == 'superadmin' || rol == 'admin') {
            consultarTodasEmpresas();
        }

        // Se hacen todas las consultas de la vista
        consultarEmpresaUsuario();
        obtenerDatosProgressBarDashboardEmpresa('/dashboard/empresa/obtener_datos_barra_progreso', ChartDashboardEmpresasProgressBar);
        obtenerDatosLineBarMixedDashboardEmpresa('/dashboard/empresa/obtener_datos_linea_mezclada', ChartDashboardEmpresasLineBarMixed);
        obtenerDatosHistorialPaquetes('/dashboard/empresa/obtener_datos_historial_paquetes');
        obtenerListadoPaquetesPendientes('/dashboard/empresa/obtener_datos_paquetes_pendientes');
    }

    $('#empresa-select-dashboard').on('change', function () {
        // Se limpian los datos del dashboard
        $('#logo-empresa-dashboard').attr('src', ``);
        $('#nombre-empresa-dashboard').html(``);
        $('#documento-empresa-dashboard').html(``);
        $('#plan-empresa-dashboard').html(``);
        $('#nombre-paquete-activo-dashboard-empresa').html(``);
        $('#numero-citas-paquete-activo-dashboard-empresa').html(``);
        $('#estado-paquete-activo-dashboard-empresa').html(``);
        $('#citas-consumidas-paquete-activo-dashboard-empresa').html(``);
        $('#citas-faltantes-paquete-activo-dashboard-empresa').html(``);
        $('#citas-confirmadas-paquete-activo-dashboard-empresa').text(``);
        $('#citas-erradas-paquete-activo-dashboard-empresa').text(``);
        $('#citas-validacion-paquete-activo-dashboard-empresa').text(``);
        $('#citas-pendientes-paquete-activo-dashboard-empresa').text(``);

        // Se consulta la nueva empresa
        const empresaSeleccionada = $(this).val();
        consultarEmpresa(empresaSeleccionada);
        obtenerDatosProgressBarDashboardEmpresa('/dashboard/empresa/obtener_datos_barra_progreso', ChartDashboardEmpresasProgressBar, empresaSeleccionada);
        obtenerDatosLineBarMixedDashboardEmpresa('/dashboard/empresa/obtener_datos_linea_mezclada', ChartDashboardEmpresasLineBarMixed, empresaSeleccionada);
        obtenerDatosHistorialPaquetes('/dashboard/empresa/obtener_datos_historial_paquetes', empresaSeleccionada);
        obtenerListadoPaquetesPendientes('/dashboard/empresa/obtener_datos_paquetes_pendientes', empresaSeleccionada);
    });

    // Boton de detalles del paquete
    $(document).on('click', '.btn-detalles-paquete', function() {
    const id_empresa_paquete = $(this).data('id-empresa-paquete');
    const $table = $('.datatables-detalles-paquete');

    // Destruir la tabla existente si ya está inicializada
    if ($.fn.DataTable.isDataTable($table)) {
        $table.DataTable().destroy();
        $table.empty();
    }

    // Reconstruir la estructura básica de la tabla
    $table.html('<thead><tr>'
        + '<th>Cliente</th>'
        + '<th>Documento</th>'
        + '<th>Sede</th>'
        + '<th>Fecha Reserva</th>'
        + '<th>Horario</th>'
        + '<th>Estado</th>'
        + '</tr></thead><tbody></tbody>');

    // Inicializar la nueva instancia de DataTable
    const table_detalles_paquete = $table.DataTable({
        ordering: true,
        processing: true,
        serverSide: true,
        searching: false,
        info: false,
        pageLength: 10,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json',
            infoEmpty: "No hay datos disponibles",
        },
        dom: '<"top px-4"fli>rt<"bottom"p><"clear">',
        ajax: {
            url: url + '/dashboard/empresa/consultar_citas_empresa_paquete/' + id_empresa_paquete,
            type: 'GET',
            dataSrc: function (json) {
                return json.Data;
            }
        },
        columns: [
            {
                data: null,
                render: function (data) {
                    return data.nombre_cliente + ' ' + data.apellido_cliente;
                }
            },
            {
                data: null,
                render: function (data) {
                    return data.tipo_doc_cliente + ' ' + data.doc_cliente;
                }
            },
            { data: 'nombre_sede'},
            { 
                data: null,
                render: function (data) {
                    return data.reserva_cita.split(' ')[0];
                }
            },
            { data: 'rango_horario' },
            { data: 'nombre_estado' }
        ],
        pagingType: "simple"
    });

    $('#modalDetallesPaquete').modal('show');
});

    // TABLAS DE PAQUETES
    var table_paquetes;

    if ($('.datatables-paquetes').length) {
        table_paquetes = $('.datatables-paquetes').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            searching: false,
            info: true,
            pageLength: 10,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json', // Configuración de idioma español
                info: "Mostrando _START_ a _END_ de _MAX_ registros",
                infoEmpty: "No hay datos disponibles",
                infoFiltered: "(filtrados de un total de _MAX_ registros)"
            },
            dom: '<"top px-4"fli>rt<"bottom"p><"clear">',
            ajax: {
                url: url + '/dashboard/paquetes/obtener_paquetes',
                type: 'POST',
                data: function (d) {
                    d.filtro_nombre = filtroNombre;
                    d.filtro_valor = filtroValor;
                    d.filtro_tipo = filtroTipo;
                },
                dataSrc: function (json) {
                    // Se extraen los datos para la información de la tabla
                    json.draw = json.Data.draw;
                    json.recordsTotal = json.Data.recordsTotal;
                    json.recordsFiltered = json.Data.recordsFiltered;

                    // Se retorna los datos de los paquetes
                    return json.Data.paquetes;
                }
            },
            columns: [
                { data: 'nombre_paquete' },
                { data: 'descripcion_paquete' },
                { data: 'numero_citas' },
                { data: 'valor' },
                { data: 'tipo_paquete' },
                { data: null },
                { data: null, visible: (canEditPaquetes || canDeletePaquetes) ? true : false }
            ],
            columnDefs: [
                {
                    targets: '_all',
                    className: 'dt-center'
                },
                {
                    targets: 5,
                    render: function (data) {
                        if (data.deleted_at == null) {
                            return `<span class="badge bg-label-success">ACTIVO</span>`;
                        } else {
                            return `<span class="badge bg-label-danger">INACTIVO</span>`;
                        }
                    }
                },
                {
                    targets: 6,
                    orderable: false,
                    render: function (data) {
                        return `
                            ${(canEditPaquetes) ? `
                                <button class="btn btn-icon btn-label-info waves-effect btn-editar-paquete" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-info" title="Editar"
                                    data-id-paquete="${data.id_paquete}"
                                    data-nombre-paquete="${data.nombre_paquete}"
                                    data-descripcion-paquete="${data.descripcion_paquete}"
                                    data-tipo-paquete="${data.tipo_paquete}"
                                    data-numero-citas="${data.numero_citas}"
                                    data-valor="${data.valor}">
                                    <i class="tf-icons ti ti-pencil-cog ti-md"></i>
                                </button>` : ''}
                            ${(canDeletePaquetes) ? `
                                ${(data.deleted_at == null) ? `
                                    <button type="button" class="btn btn-icon btn-label-danger waves-effect cambio_estado_paquete" data-id_paquete="${data.id_paquete}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-danger" title="Desactivar">
                                        <i class="tf-icons ti ti-box-off ti-md"></i>
                                    </button>
                                ` : `
                                    <button type="button" class="btn btn-icon btn-label-success waves-effect cambio_estado_paquete" data-id_paquete="${data.id_paquete}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-success" title="Activar">
                                        <i class="tf-icons ti ti-package ti-md"></i>
                                    </button>
                                `}
                            `: ''}
                        `;
                    }
                }
            ],
            createdRow: function (row, data) {
                if (data.deleted_at == null) {
                    $(row).css('background-color', 'rgba(225, 247, 222, 0.5)');
                } else {
                    $(row).css('background-color', 'rgba(255, 224, 224, 0.5)');
                }
            },
            drawCallback: function (settings) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            },
            pagingType: "simple"
        });
    }

    // Cambio de estado de paquete
    $('.datatables-paquetes').on('click', '.cambio_estado_paquete', function () {
        let id_paquete = $(this).data('id_paquete');

        $.ajax({
            url: url + '/dashboard/paquetes/cambio_estado',
            type: 'POST',
            data: {
                id_paquete: id_paquete
            },
            success: function () {
                // Destruir todos los tooltips activos antes de recargar la tabla
                var tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                tooltips.forEach(function (el) {
                    var instance = bootstrap.Tooltip.getInstance(el);
                    if (instance) {
                        instance.dispose();
                    }
                });

                table_paquetes.ajax.reload();
            }
        });
    });

    // Eventos para los filtros de paquetes
    $('#buscar-nombre-paquetes').on('keyup', function () {
        filtroNombre = $(this).val();
        table_paquetes.ajax.reload();
    });

    $('#filtro-tipo-paquete').on('change', function () {
        filtroTipo = $(this).val();
        table_paquetes.ajax.reload();
    });

    $('#buscar-valor-paquetes').on('keyup', function () {
        filtroValor = $(this).val();
        table_paquetes.ajax.reload();
    });

    $('#filtro-reiniciar').on('click', function () {
        filtroNombre = '';
        filtroTipo = '';
        filtroValor = '';
        $('#buscar-nombre-paquetes').val("").trigger('input');
        $('#filtro-tipo-paquete').val("").trigger('change');
        $('#buscar-valor-paquetes').val("").trigger('input');
    });

    $('#crear-paquete').on('click', function () {
        $('#modalNuevoPaqueteLabel').text('Crear Paquete');
        $('#form-nuevo-paquete').attr('action', url + '/dashboard/paquetes/guardar');
        $('#form-nuevo-paquete')[0].reset();
        $('#tipo-paquete').val('').trigger('change');
        $('#modalNuevoPaquete').modal('show');
    });

    $('.datatables-paquetes').on('click', '.btn-editar-paquete', function () {
        const id = $(this).data('id-paquete');
        const nombre = $(this).data('nombre-paquete');
        const descripcion = $(this).data('descripcion-paquete');
        const numeroCitas = $(this).data('numero-citas');
        const tipo = $(this).data('tipo-paquete');
        const valor = $(this).data('valor');

        $('#modalNuevoPaqueteLabel').text('Editar Paquete');
        $('#form-nuevo-paquete').attr('action', url + '/dashboard/paquetes/actualizar/' + id);
        $('#nombre-paquete').val(nombre);
        $('#descripcion-paquete').val(descripcion);
        $('#numero-citas-paquete').val(numeroCitas);
        $('#valor-paquete').val(valor);
        $('#tipo-paquete').val(tipo);

        $('#modalNuevoPaquete').modal('show');
    });

    //Formulario para crear un nuevo paquete
    $('#form-nuevo-paquete').on('submit', function (event) {
        event.preventDefault();
        let action = $(this).attr('action');
        let formData = $(this).serialize();

        $.ajax({
            url: action,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.Success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Exito!',
                        text: response.Message,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                        }
                    }).then(() => {
                        table_paquetes.ajax.reload();
                        $('#modalNuevoPaquete').modal('hide');
                        $('#form-nuevo-paquete')[0].reset();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: response.Message
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al enviar los datos.'
                });
            }
        });
    });
});

