$(function () {
    // Configuración en español para el datepicker
    $.fn.datepicker.dates['es'] = {
        days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"],
        daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
        daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sá"],
        months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
        monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
        today: "Hoy",
        clear: "Limpiar",
        format: "dd/mm/yyyy",
        titleFormat: "MM yyyy",
        weekStart: 0
    };

    // Lista completa de códigos de comparendos para Tagify whitelist
    const comparendoCodes = [
        'A01', 'A02', 'A03', 'A04', 'A05', 'A06', 'A07', 'A08', 'A09', 'A10', 'A11', 'A12',
        'B01', 'B02', 'B03', 'B04', 'B05', 'B06', 'B07', 'B08', 'B09', 'B10', 'B11', 'B12', 'B13', 'B14', 'B15', 'B16', 'B17', 'B18', 'B19', 'B20', 'B21', 'B22', 'B23',
        'C01', 'C02', 'C03', 'C04', 'C05', 'C06', 'C07', 'C08', 'C09', 'C10', 'C11', 'C12', 'C13', 'C14', 'C15', 'C16', 'C17', 'C18', 'C19', 'C20', 'C21', 'C22', 'C23', 'C24', 'C25', 'C26', 'C27', 'C28', 'C29', 'C30', 'C31', 'C32', 'C33', 'C34', 'C35', 'C36', 'C37', 'C38', 'C39', 'C40',
        'D01', 'D02', 'D03', 'D04', 'D05', 'D06', 'D07', 'D08', 'D09', 'D10', 'D11', 'D12', 'D13', 'D14', 'D15', 'D16', 'D17',
        'E01', 'E02', 'E04', 'E05',
        'F01', 'F02', 'F03', 'F04', 'F05', 'F06', 'F07', 'F08', 'F09', 'F10', 'F11', 'F12',
        'G01', 'G02',
        'H01', 'H02', 'H03', 'H04', 'H05', 'H06', 'H07', 'H08', 'H09', 'H10', 'H11', 'H12', 'H13'
    ];

    // Inicializar datepickers para citas futuras
    initDatepickers();

    // Inicializar Tagify
    initTagify();

    // Función para datepickers futuros (reserva_cita)
    function initDatepickers() {
        $('.datepicker').each(function () {
            if (!$(this).hasClass('hasDatepicker')) {
                $(this).datepicker({
                    todayHighlight: true,
                    orientation: isRtl ? 'auto right' : 'auto left',
                    language: 'es',
                    startDate: new Date(),
                    beforeShowDay: function (date) {
                        const fechaString = date.toISOString().split('T')[0];
                        if (festivos.includes(fechaString)) {
                            return { enabled: false, classes: 'disabled-date', tooltip: 'Fecha no disponible' };
                        }
                        return true;
                    }
                }).on('changeDate', function (e) {
                    if (e.date !== undefined) {
                        loadHorarios($(this).closest('.accordion-body'), e);
                    }
                });
            }
        });
    }

    // Función para datepickers pasados (fecha_notificacion)
    function initPastDatepickers() {
        $('.notif-datepicker').each(function () {
            if (!$(this).hasClass('hasDatepicker')) {
                $(this).datepicker({
                    todayHighlight: true,
                    orientation: isRtl ? 'auto right' : 'auto left',
                    language: 'es',
                    startDate: '-100y',
                    endDate: '0d' // Hasta hoy
                });
            }
        });
    }

    // Función para Tagify
    function initTagify() {
        $('.tagify-input').each(function () {
            if (!$(this).hasClass('tagify--initialized')) {
                new Tagify(this, {
                    whitelist: comparendoCodes,
                    maxTags: Infinity, // O 1 si solo uno por campo
                    dropdown: {
                        enabled: 0,
                        classname: 'tagify__dropdown',
                        searchKeys: ['value'],
                        maxItems: 98 // Como en la imagen
                    }
                });
                $(this).addClass('tagify--initialized');
            }
        });
    }

    // Obtener horarios para una fecha específica
    function loadHorarios(container, e) {
        let dia_semana = e.date.getDay();
        if (dia_semana == 0) dia_semana = 7;
        let horarios_disponibles = [];
        let hoy = new Date();
        let fecha_hoy = `${hoy.getDate().toString().padStart(2, '0')}/${(hoy.getMonth() + 1).toString().padStart(2, '0')}/${hoy.getFullYear()}`;
        let fecha_seleccionada = e.format();
        if (fecha_hoy == fecha_seleccionada) {
            let ahora = new Date();
            let horaLimite = new Date(ahora.getTime() + 40 * 60000);
            horarios_disponibles = horarios.filter(item => item.dia_sede_horario == dia_semana);
        } else {
            horarios_disponibles = horarios.filter(item => item.dia_sede_horario == dia_semana);
        }
        $('#vuexy-loading').removeClass('d-none').addClass('d-flex');
        const data = {
            horarios_disponibles: horarios_disponibles,
            fecha_seleccionada: fecha_seleccionada,
            sede: id_sede,
        };
        $.ajax({
            url: url + '/verificar-cupos-horario',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (response) {
                let html_select = VerificarCupoHorario(response);
                container.find('.horario-select').html(html_select).attr('disabled', false).select2();
            },
            error: function (error) {
                console.log("ERROR AL CARGAR HORARIOS: ", error);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los horarios.', confirmButtonText: 'Entendido' });
            },
            complete: function () {
                $('#vuexy-loading').addClass('d-none').removeClass('d-flex');
            }
        });
    }

    function VerificarCupoHorario(response) {
        if (!Array.isArray(response)) {
            return '<option value="">No hay horarios disponibles</option>';
        } else {
            let html_select = '<option value="">Seleccione una opción</option>';
            response.forEach(horario => {
                if (horario.disponible) {
                    html_select += '<option value="' + horario.id_sede_horario + '">' + horario.rango_horario + '</option>';
                } else {
                    html_select += '<option value="' + horario.id_sede_horario + '" disabled>' + horario.rango_horario + '</option>';
                }
            });
            return html_select;
        }
    }

    // Obtener horarios iniciales
    function get_hours() {
        let id = id_sede;
        $.ajax({
            url: url + '/get-horarios',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (response) {
                horarios = response.horarios;
            },
            error: function (error) {
                console.log(error);
            }
        });
    }
    get_hours();

    // Cargar ciudad
    cargarCiudad(ID_CIUDAD_GLOBAL);
    function cargarCiudad(idCiudad) {
        $.ajax({
            url: url + '/ciudad/obtener',
            type: 'POST',
            data: { id_ciudad: idCiudad },
            dataType: 'json',
            success: function (response) {
                if (response.success && response.ciudad) {
                    $('.city-pill .nombre-ciudad').text(response.ciudad.nombre_ciudad);
                    $('.city-pill').removeClass('d-none');
                }
            },
            error: function () {
                console.error('No se pudo cargar la ciudad');
            }
        });
    }

    // Ocultar sección de vehículo si no aplica (basado en servicio de sede)
    let currentServiceType = null;
    hideVehiculoSelect();
    obtenerServicioSede(id_sede);
    function obtenerServicioSede(idSede) {
        $.ajax({
            url: url + '/get-servicio-by-id-sede',
            type: 'POST',
            data: { id_sede: idSede },
            dataType: 'json',
            success: function (response) {
                if (response.validate) {
                    currentServiceType = response.tipo_servicio[0].tipo_servicio;
                } else {
                    currentServiceType = null;
                }
                checkAndLoadVehiculos();
            },
            error: function () {
                currentServiceType = null;
                checkAndLoadVehiculos();
            }
        });
    }
    function checkAndLoadVehiculos() {
        if (currentServiceType && currentServiceType === 'CIA') {
            $('.vehiculo-section').show();
            $('[name$="[tipo_vehiculo]"]').attr('required', true);
            $('[name$="[placa_vehiculo]"]').attr('required', true);
        } else {
            hideVehiculoSelect();
        }
    }
    function hideVehiculoSelect() {
        $('.vehiculo-section').hide();
        $('[name$="[tipo_vehiculo]"]').attr('required', false);
        $('[name$="[placa_vehiculo]"]').attr('required', false);
    }

    // Adición de comparendos
    let comparendoCount = 1;
    $('#addComparendo').on('click', function () {
        if (comparendoCount >= 3) return;
        comparendoCount++;
        let newIndex = comparendoCount - 1;
        let newPanel = `
            <div class="accordion-item comparendo-panel" data-index="${comparendoCount}">
                <h2 class="accordion-header" id="heading${comparendoCount}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${comparendoCount}" aria-expanded="false" aria-controls="collapse${comparendoCount}">
                        Comparendo #${comparendoCount}
                    </button>
                </h2>
                <div id="collapse${comparendoCount}" class="accordion-collapse collapse" aria-labelledby="heading${comparendoCount}" data-bs-parent="#accordionComparendos">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="mb-4 col-12 col-md-6">
                                <label class="form-label">Día de la cita <span class="required_flied">*</span></label>
                                <input name="comparendos[${newIndex}][reserva_cita]" type="text" class="form-control datepicker" placeholder="DD/MM/YYYY" readonly required />
                            </div>
                            <div class="mb-4 col-12 col-md-6">
                                <label class="form-label">Franja horaria <span class="required_flied">*</span></label>
                                <select name="comparendos[${newIndex}][id_sede_horario]" class="form-select select2 horario-select" required disabled>
                                    <option value="">Seleccionar horario</option>
                                </select>
                            </div>
                            <div class="mb-4 col-12 col-md-6 vehiculo-section">
                                <label class="form-label">Tipo de vehículo <span class="required_flied">*</span></label>
                                <select name="comparendos[${newIndex}][tipo_vehiculo]" class="form-select select2">
                                    <option value="Motocicleta">Motocicleta</option>
                                    <option value="Automotor">Automotor</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="mb-4 col-12 col-md-6 vehiculo-section">
                                <label class="form-label">Placa de vehículo <span class="required_flied">*</span></label>
                                <input name="comparendos[${newIndex}][placa_vehiculo]" type="text" class="form-control" />
                            </div>
                            <div class="mb-4 col-12 col-md-6">
                                <label class="form-label">Código de comparendo</label>
                                <input name="comparendos[${newIndex}][codigo_comparendo]" type="text" class="form-control tagify-input" placeholder="Escribe tu código de comparendo si lo conoces" autocomplete="off" />
                                <p class="text-muted mb-0">Si conoce el código del comparendo, puede ingresarlo aquí, de lo contrario puede dejarlo vacío.</p>
                            </div>
                            <div class="mb-4 col-12 col-md-6">
                                <label class="form-label">Fecha de notificación del comparendo <span class="required_flied">*</span></label>
                                <input name="comparendos[${newIndex}][fecha_notificacion]" type="text" class="form-control notif-datepicker" placeholder="DD/MM/YYYY" readonly required />
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-danger removeComparendo">Eliminar este comparendo</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#accordionComparendos').append(newPanel);

        // Inicializar en nuevo panel
        initDatepickers();
        initPastDatepickers();
        $('.select2').select2();
        initTagify();
        checkAndLoadVehiculos();

        // Abrir el nuevo panel (cierra previos automáticamente por data-bs-parent)
        $('#collapse' + comparendoCount).collapse('show');

        // Habilitar eliminar si >1
        if (comparendoCount > 1) {
            $('.removeComparendo').prop('disabled', false);
        }

        if (comparendoCount >= 3) {
            $('#addComparendo').prop('disabled', true).addClass('disabled');
        }
    });

    // Manejar eliminación de comparendos
    $(document).on('click', '.removeComparendo', function () {
        if (comparendoCount <= 1) return; // No eliminar si solo queda uno

        let panel = $(this).closest('.comparendo-panel');
        panel.remove();
        comparendoCount--;

        // Renumerar los panels restantes dinámicamente
        $('.comparendo-panel').each(function (index) {
            let newIndex = index + 1;
            let arrayIndex = index; // Para names: comparendos[0], [1], etc.

            $(this).attr('data-index', newIndex);
            $(this).find('.accordion-header').attr('id', `heading${newIndex}`);
            $(this).find('.accordion-button').text(`Comparendo #${newIndex}`).attr({
                'data-bs-target': `#collapse${newIndex}`,
                'aria-controls': `collapse${newIndex}`
            });
            $(this).find('.accordion-collapse').attr({
                'id': `collapse${newIndex}`,
                'aria-labelledby': `heading${newIndex}`
            });

            // Actualizar names de inputs con nuevo arrayIndex
            $(this).find('input[name^="comparendos"], select[name^="comparendos"]').each(function () {
                let name = $(this).attr('name').replace(/\[\d+\]/, `[${arrayIndex}]`);
                $(this).attr('name', name);
            });
        });

        // Deshabilitar botones de eliminar si ahora count == 1
        if (comparendoCount === 1) {
            $('.removeComparendo').prop('disabled', true);
        }

        // Habilitar botón agregar si count < 3
        if (comparendoCount < 3) {
            $('#addComparendo').prop('disabled', false).removeClass('disabled');
        }
    });

    // Validación de citas duplicadas al blur del documento
    $('input[name="doc_cliente"]').on('blur', function () {
        let cc = $(this).val();
        $.ajax({
            url: url + '/get-citas-agendadas',
            type: 'POST',
            data: { cc: cc },
            dataType: 'json',
            success: function (response) {
                if (response) {
                    $('.resultadoCitas').html('<p class="text-danger fw-bold text-end">Ya tienes una cita agendada, si deseas modificarla escríbenos aquí (<a href="https://wa.me/3054628258">3054511014</a> / <a href="https://wa.me/3054628258">3054628258</a> ).</p>');
                } else {
                    $('.resultadoCitas').html('');
                }
            },
            error: function () {
                $('.resultadoCitas').html('');
                console.log("No se pudo validar la información del cliente.");
            }
        });
    });

    // Envío del formulario
    $('.content_form').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        let submitBtn = form.find('button[type="submit"]');
        let valid = true;
        form.find('input[required], select[required]').each(function () {
            if ($(this).val() === '') valid = false;
        });
        if (!valid) {
            Swal.fire({ title: '¡Error!', text: 'Todos los campos requeridos deben estar llenos.', icon: 'error', confirmButtonText: 'Aceptar' });
            return false;
        }
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Enviando...');
        if ($('#phoneCliente').length) {
            $('#phoneCliente').val(phoneInput.getNumber());
        }
        let data = form.serialize();
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).html('Agendar cita');
                if (response.validate) {
                    window.parent.postMessage({ tipo: 'cita_agendada', idcita: response.id }, '*');
                } else {
                    Swal.fire({ title: '¡Error!', text: response.text, icon: 'error', confirmButtonText: 'OK' });
                }
            },
            error: function (error) {
                submitBtn.prop('disabled', false).html('Agendar cita');
                console.log(error);
            }
        });
    });

    if ($('.select2').length) {
        $('.select2').select2();
    }
});