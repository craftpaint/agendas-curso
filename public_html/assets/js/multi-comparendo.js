$(document).ready(function() {
    // ============================================
    // CONFIGURACIÓN GLOBAL
    // ============================================
    const CONFIG = {
        maxComparendos: 3,
        currentComparendo: 0,
        totalComparendos: 1
    };

    // ============================================
    // INICIALIZACIÓN
    // ============================================
    function init() {
        // Ocultar navegación inicialmente
        $('#nav-prev, #nav-next').hide();
        
        // Ocultar contador del primer comparendo
        $('#title-comparendo-0 .comparendo-counter').hide();
        
        // Inicializar datepickers del primer comparendo
        initDatePickers(0);
        
        // Inicializar eventos
        initEvents();
        
        // Cargar horarios
        loadHorarios();
        
        // Inicializar teléfono
        initPhoneInput();
    }

    // ============================================
    // DATEPICKERS
    // ============================================
    function initDatePickers(comparendoIndex) {
        // Datepicker para fecha de cita
        $(`#comparendo-${comparendoIndex} .comparendo-date`).datepicker({
            format: 'dd/mm/yyyy',
            language: 'es',
            startDate: new Date(),
            todayHighlight: true,
            autoclose: true,
            daysOfWeekDisabled: [0, 6], // Deshabilitar sábados y domingos
            beforeShowDay: function(date) {
                // Verificar festivos (si tienes la variable festivos)
                if (typeof festivos !== 'undefined' && festivos.length > 0) {
                    const fechaString = date.toISOString().split('T')[0];
                    if (festivos.includes(fechaString)) {
                        return {
                            enabled: false,
                            classes: 'disabled-date',
                            tooltip: 'Fecha festiva'
                        };
                    }
                }
                return true;
            }
        }).on('changeDate', function(e) {
            const fechaSeleccionada = e.format('dd/mm/yyyy');
            const index = $(this).closest('.comparendo-section').data('index');
            loadHorariosDisponibles(index, fechaSeleccionada);
        });

        // Datepicker para fecha de notificación
        $(`#comparendo-${comparendoIndex} .datepicker-notification`).datepicker({
            format: 'dd/mm/yyyy',
            language: 'es',
            autoclose: true
        });
    }

    // ============================================
    // EVENTOS
    // ============================================
    function initEvents() {
        // Botón agregar comparendo
        $('#add-comparendo').click(addComparendo);
        
        // Navegación
        $('#nav-prev').click(() => navigate(-1));
        $('#nav-next').click(() => navigate(1));
        
        // Cambio en horario
        $(document).on('change', '.comparendo-time', function() {
            const index = $(this).closest('.comparendo-section').data('index');
            updateCuposAlert(index);
        });
        
        // Validación de documento
        $('input[name="doc_cliente"]').on('blur', validarCitaExistente);
        
        // Envío del formulario
        $('#multiComparendoForm').on('submit', submitForm);
    }

    // ============================================
    // AGREGAR NUEVO COMPARENDO
    // ============================================
    function addComparendo() {
        if (CONFIG.totalComparendos >= CONFIG.maxComparendos) {
            Swal.fire({
                title: 'Límite alcanzado',
                text: 'Solo puede agendar máximo 3 comparendos',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        const newIndex = CONFIG.totalComparendos;
        
        // Clonar template
        const $template = $('#comparendo-0').clone();
        
        // Actualizar IDs y datos
        $template.attr('id', `comparendo-${newIndex}`);
        $template.data('index', newIndex);
        $template.removeClass('active');
        
        // Actualizar título - MOSTRAR NÚMERO SOLO DESDE EL SEGUNDO
        const counterHtml = '<span class="comparendo-counter">' + (newIndex + 1) + '</span>';
        $template.find('.comparendo-title').html(counterHtml + 'Comparendo #' + (newIndex + 1));
        
        // Mostrar contador en el primer comparendo también
        if (newIndex === 1) {
            $('#title-comparendo-0').html('<span class="comparendo-counter">1</span>Comparendo #1');
        }
        
        // Actualizar nombres de campos
        $template.find('[name]').each(function() {
            const name = $(this).attr('name');
            if (name && name.includes('[0]')) {
                $(this).attr('name', name.replace('[0]', `[${newIndex}]`));
            }
        });
        
        // Limpiar valores
        $template.find('input').val('');
        $template.find('select').val('');
        $template.find('.comparendo-time').html('<option value="">Seleccionar horario</option>').prop('disabled', true);
        $template.find('.cupos-alert').hide();
        
        // Agregar al contenedor
        $('#comparendos-container').append($template);
        
        // Inicializar datepickers para el nuevo comparendo
        initDatePickers(newIndex);
        
        // Actualizar contadores
        CONFIG.totalComparendos++;
        
        // Mostrar navegación si hay más de 1 comparendo
        if (CONFIG.totalComparendos > 1) {
            $('#nav-prev, #nav-next').show();
        }
        
        // Deshabilitar botón si llegamos al máximo
        if (CONFIG.totalComparendos >= CONFIG.maxComparendos) {
            $('#add-comparendo').prop('disabled', true).addClass('disabled');
        }
        
        // Mostrar el nuevo comparendo
        showComparendo(newIndex);
    }

    // ============================================
    // NAVEGACIÓN
    // ============================================
    function navigate(direction) {
        const newIndex = CONFIG.currentComparendo + direction;
        
        if (newIndex >= 0 && newIndex < CONFIG.totalComparendos) {
            showComparendo(newIndex);
        }
    }

    function showComparendo(index) {
        // Ocultar todos
        $('.comparendo-section').removeClass('active').hide();
        
        // Mostrar seleccionado
        $(`#comparendo-${index}`).addClass('active').show();
        
        // Actualizar índice actual
        CONFIG.currentComparendo = index;
        
        // Actualizar navegación
        updateNavigation();
    }

    function updateNavigation() {
        $('#nav-prev').toggle(CONFIG.totalComparendos > 1 && CONFIG.currentComparendo > 0);
        $('#nav-next').toggle(CONFIG.totalComparendos > 1 && CONFIG.currentComparendo < CONFIG.totalComparendos - 1);
    }

    // ============================================
    // HORARIOS
    // ============================================
    function loadHorarios() {
        $.ajax({
            url: window.appConfig.url + '/get-horarios',
            type: 'POST',
            data: { id: window.appConfig.id_sede },
            dataType: 'json',
            success: function(response) {
                // Guardar horarios globalmente si es necesario
                window.horarios = response.horarios || [];
            },
            error: function(error) {
                console.error('Error cargando horarios:', error);
            }
        });
    }

    function loadHorariosDisponibles(comparendoIndex, fecha) {
        $('#vuexy-loading').removeClass('d-none').addClass('d-flex');
        
        const data = {
            fecha_seleccionada: fecha,
            sede: window.appConfig.id_sede,
        };
        
        $.ajax({
            url: window.appConfig.url + '/verificar-cupos-horario',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                let html = '<option value="">Seleccionar horario</option>';
                
                if (Array.isArray(response) && response.length > 0) {
                    response.forEach(function(horario) {
                        if (horario.disponible) {
                            html += `<option value="${horario.id_sede_horario}">${horario.rango_horario}</option>`;
                        } else {
                            html += `<option value="${horario.id_sede_horario}" disabled>${horario.rango_horario} (Sin cupo)</option>`;
                        }
                    });
                }
                
                $(`#comparendo-${comparendoIndex} .comparendo-time`).html(html).prop('disabled', false);
                $('#vuexy-loading').addClass('d-none').removeClass('d-flex');
            },
            error: function() {
                $(`#comparendo-${comparendoIndex} .comparendo-time`).html('<option value="">Error cargando horarios</option>');
                $('#vuexy-loading').addClass('d-none').removeClass('d-flex');
            }
        });
    }

    // ============================================
    // VALIDACIONES
    // ============================================
    function updateCuposAlert(index) {
        const $select = $(`#comparendo-${index} .comparendo-time`);
        if ($select.val()) {
            const palabras = ["¡Date prisa!", "¡Apúrate!", "¡No pierdas tu lugar!", "¡Últimos cupos!", "¡Reserva ya!"];
            const palabra = palabras[Math.floor(Math.random() * palabras.length)];
            const cupos = Math.floor(Math.random() * 3) + 1;
            
            $(`#cupos-alert-${index}`).html(
                `<span class="morado">${palabra}</span> Solo quedan <span class="morado">${cupos} cupos.</span>`
            ).show();
        } else {
            $(`#cupos-alert-${index}`).hide();
        }
    }

    function validarCitaExistente() {
        const cc = $(this).val();
        if (!cc) return;
        
        $.ajax({
            url: window.appConfig.url + '/get-citas-agendadas',
            type: 'POST',
            data: { cc: cc },
            dataType: 'json',
            success: function(response) {
                if (response) {
                    $('.resultadoCitas').html(`
                        <div class="alert alert-danger">
                            <i class="ti ti-alert-triangle"></i> Ya tienes una cita agendada. 
                            Si deseas modificarla escríbenos aquí 
                            (<a href="https://wa.me/3054628258">3054511014</a> / 
                            <a href="https://wa.me/3054628258">3054628258</a>).
                        </div>
                    `);
                } else {
                    $('.resultadoCitas').html('');
                }
            },
            error: function() {
                $('.resultadoCitas').html('');
            }
        });
    }

    // ============================================
    // ENVÍO DEL FORMULARIO
    // ============================================
    function submitForm(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        // Validar campos requeridos
        let valid = true;
        form.find('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                valid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!valid) {
            Swal.fire({
                title: '¡Error!',
                text: 'Por favor complete todos los campos requeridos',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        
        // Actualizar teléfono
        if (window.phoneInput) {
            const fullPhoneNumber = window.phoneInput.getNumber();
            $('#phoneCliente').val(fullPhoneNumber);
        }
        
        // Mostrar loader
        submitBtn.prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Procesando...
        `);
        
        // Enviar datos
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html('<i class="ti ti-calendar"></i> Agendar cita');
                
                if (response.validate) {
                    // Éxito
                    window.parent.postMessage({
                        tipo: 'cita_agendada',
                        idcita: response.id,
                        comparendos: CONFIG.totalComparendos
                    }, '*');
                    
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.text,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Redireccionar a confirmación
                        if (response.id) {
                            window.location.href = window.appConfig.url + '/citas/confirmacion/' + response.id;
                        }
                    });
                } else {
                    Swal.fire({
                        title: '¡Error!',
                        text: response.text,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function(error) {
                console.error('Error:', error);
                submitBtn.prop('disabled', false).html('<i class="ti ti-calendar"></i> Agendar cita');
                Swal.fire({
                    title: '¡Error!',
                    text: 'Error de conexión. Por favor intente nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    }

    // ============================================
    // TELÉFONO
    // ============================================
    function initPhoneInput() {
        const phoneInputField = document.querySelector("#phoneCliente");
        if (phoneInputField) {
            window.phoneInput = window.intlTelInput(phoneInputField, {
                initialCountry: "co",
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
            });
        }
    }

    // ============================================
    // INICIAR TODO
    // ============================================
    init();
});