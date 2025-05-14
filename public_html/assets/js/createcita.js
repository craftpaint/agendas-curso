$(function () {
    //Obtnemos los horarios
    function get_hours() {
        let id = id_sede;
        let dias_fijos = [0, 1, 2, 3, 4, 5, 6];
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
        $.ajax({
            url: url + '/get-horarios',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (response) {
                horarios = response.horarios;
                let dias_disponibles = [];
                let dias_disponibles_datapicker = [];
                horarios.forEach(function (item, index) {
                    let dia_sede_horario = parseInt(item.dia_sede_horario);
                    let dia_sede_horario_datapicker = parseInt(item.dia_sede_horario);
                    if (dia_sede_horario_datapicker == 7) {
                        dia_sede_horario_datapicker = 0;
                    }
                    dias_disponibles.push(dia_sede_horario);
                    dias_disponibles_datapicker.push(dia_sede_horario_datapicker);
                });
                dias_disponibles = [...new Set(dias_disponibles)];
                dias_disponibles_datapicker = [...new Set(dias_disponibles_datapicker)];
                let dias_no_disponibles = dias_fijos.filter(elemento => !dias_disponibles_datapicker.includes(elemento));
                // Destruye el calendario antes de volver a inicializarlo
                $('#citaDia').datepicker('destroy');
                $('#citaDia').attr('disabled', false);
                $('#citaDia').datepicker({
                    todayHighlight: true,
                    daysOfWeekDisabled: dias_no_disponibles,
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
                        let dia_semana = e.date.getDay();
                        if (dia_semana == 0) {
                            dia_semana = 7;
                        }
                        let horarios_disponibles = [];
                        // Verificamos si el día seleccionado es hoy
                        let hoy = new Date();
                        let dia = hoy.getDate().toString().padStart(2, '0'); // Agregar cero inicial si es necesario
                        let mes = (hoy.getMonth() + 1).toString().padStart(2, '0'); // Agregar cero inicial
                        let anio = hoy.getFullYear();
                        let fecha_hoy = `${dia}/${mes}/${anio}`;
                        let fecha_seleccionada = e.format();
                        if (fecha_hoy == fecha_seleccionada) {
                            // Obtener la hora actual y sumarle 40 minutos
                            let ahora = new Date();
                            let horaLimite = new Date(ahora.getTime() + 40 * 60000); // Sumar 40 minutos

                            horarios_disponibles = horarios.filter(function (item) {
                                let hora_inicio = item.inicio_horario; // Formato "HH:mm:ss"

                                // Crear objetos Date con la misma fecha base para comparar correctamente
                                let [hh, mm, ss] = hora_inicio.split(':').map(Number);
                                let horaInicioDate = new Date();
                                horaInicioDate.setHours(hh, mm, ss, 0); // Asignar la hora del horario

                                return item.dia_sede_horario == dia_semana && horaInicioDate > horaLimite;
                            });
                        } else {
                            horarios_disponibles = horarios.filter(function (item) {
                                return item.dia_sede_horario == dia_semana;
                            });
                        }
                        let html_select = '<option value="">Seleccione una opción</option>';
                        horarios_disponibles.forEach(function (item, index) {
                            html_select += '<option value="' + item.id_sede_horario + '">' + item.rango_horario + '</option>';
                        });
                        $('#citaHora').html(html_select);
                        $('#citaHora').attr('disabled', false);
                        //EDITAR CITA
                        if ($('.content_citas_edit').length) {
                            $('#citaHora').val(id_sede_horario).trigger('change');
                        }

                    }
                });
                //EDITAR CITA
                if ($('.content_citas_edit').length) {
                    $('#citaDia').datepicker('setDate', new Date(reserva_cita)).trigger('changeDate');
                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    }
    get_hours();

    let currentServiceType = null;
    // Ocultar y limpiar el select de vehículos (si lo tienes)
    hideVehiculoSelect();

    // Llamada para obtener el servicio asociado a la sede en una función separada
    obtenerServicioSede(id_sede);

    function obtenerServicioSede(idSede) {
        $.ajax({
            url: url + '/get-servicio-by-id-sede', // Endpoint a crear en el backend
            type: 'POST',
            data: { id_sede: idSede },
            dataType: 'json',
            success: function (response) {
                if (response.validate) {
                    currentServiceType = response.tipo_servicio[0].tipo_servicio;
                    console.log('Tipo de servicio actual:', currentServiceType);
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

    function checkAndLoadVehiculos() {
        console.log('Tipo de servicio actual:', currentServiceType);
        // Condiciones:
        // 1. currentServiceType debe existir y ser "CIA"
        if (currentServiceType && currentServiceType == 'CIA') {
            $('#divContentVehiculo').show();
            $('#selectTipoVehiculo').attr('required', true);
            $('#placa_vehiculo').attr('required', true);
            $('#modelo_vehiculo').attr('required', true);
        }

        // 2. currentServiceType debe existir y ser "CIA"
        if (currentServiceType && currentServiceType == 'CIA') {
            $('#divContentVehiculo').show();
            $('#selectTipoVehiculo').attr('required', true);
            $('#placa_vehiculo').attr('required', true);
            $('#modelo_vehiculo').attr('required', true);
        }

        else {
            hideVehiculoSelect();
        }
    }

    // Función para ocultar y limpiar el select de vehículos
    function hideVehiculoSelect() {
        $('#divContentVehiculo').hide();
        $('#selectTipoVehiculo').attr('required', false);
        $('#placa_vehiculo').attr('required', false);
        $('#modelo_vehiculo').attr('required', false);
    }


    // Agrega el listener para cuando se seleccione una franja horaria
    $('#citaHora').on('change', function () {
        let selectedOption = $(this).val();
        if (selectedOption) {
            // Array de frases de urgencia
            let palabrasRandom = ["¡Date prisa!", "¡Apúrate!", "¡No pierdas tu lugar!", "¡Últimos cupos!", "¡Reserva ya!"];
            // Selecciona una frase aleatoria
            let palabra = palabrasRandom[Math.floor(Math.random() * palabrasRandom.length)];
            // Genera un número aleatorio entre 1 y 3
            let nRandom = Math.floor(Math.random() * 3) + 1;
            // Muestra el mensaje en el contenedor CantCupos
            $('.CantCupos').html('<p class=" fw-bold">  <span class="morado">' + palabra + '</span> Solo quedan <span class="morado">' + nRandom + ' cupos.</span></p>');
        } else {
            // Si se deselecciona, limpia el mensaje
            $('.CantCupos').html('');
        }
    });


    //Validamos el envio del formulario
    content_form = $('.content_form');
    content_form.on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr('action');
        let submitBtn = form.find('button[type="submit"]');
        let valid = true;

        form.find('input, select').each(function (index, element) {
            if ($(element).prop('required') && $(element).val() == '') {
                valid = false;
            }
        });
        if (!valid) {
            Swal.fire({
                title: '¡Error!',
                text: 'Todos los campos son requeridos',
                icon: 'error',
                confirmButtonText: 'Aceptar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ml-1 d-none'
                }
            });
            return false;
        } else {
            // Mostrar loader y deshabilitar botón
            submitBtn.prop('disabled', true);
            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...');

            //Tipo de telefono
            if ($('#phoneCliente').length) {
                const fullPhoneNumber = phoneInput.getNumber();
                $('#phoneCliente').val(fullPhoneNumber);
            }
            let data = form.serialize();
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (response) {
                    // Revertir botón
                    submitBtn.prop('disabled', false);
                    submitBtn.html('Agendar');

                    if (response.validate) {
                        // Swal.fire({
                        //     title: '¡Cita agendada!',
                        //     text: response.text,
                        //     icon: 'success',
                        //     confirmButtonText: 'Aceptar',
                        //     customClass: {
                        //         confirmButton: 'btn btn-primary',
                        //         cancelButton: 'btn btn-outline-danger ml-1 d-none'
                        //     }
                        // }).then((result) => {
                        //     if (result.isConfirmed) {
                        //     }
                        // });
                        window.parent.postMessage('cita_agendada', '*');
                    } else {
                        Swal.fire({
                            title: '¡Error!',
                            text: response.text,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary',
                                cancelButton: 'btn btn-outline-danger ml-1 d-none'
                            }
                        });
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
    });

    if ($('.select2').length) {
        var $this = select2;
        $('.select2').select2();
    }

    $('input[name="doc_cliente"]').on('blur', function () {
        let cc = $(this).val();

        $.ajax({
            url: url + '/get-citas-agendadas',
            type: 'POST',
            data: { cc: cc},
            dataType: 'json',
            success: function (response) {
                if (response) {
                    $('.resultadoCitas').html('<p class="text-danger fw-bold text-end">  <span class="morado">' + '</span>Ya tienes una cita agendada, si deseas modificarla escríbenos aquí (<a href="https://wa.me/3054628258">3054511014</a> / <a href="https://wa.me/3054628258">3054628258</a> ).</p>');
                }
            },
            error: function () {
                $('.resultadoCitas').html('');
                console.log("No se pudo validar la información del cliente.");
            }
        })
    });
});
