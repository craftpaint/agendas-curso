// createcita.js refactored
$(function () {
    let currentIndex = 0;
    let maxComparendos = 3;
    let comparendosCount = 1;

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

    // Inicializar el primer comparendo
    initComparendoSection($('.comparendo-section').first());

    // Evento para agregar nuevo comparendo
    $('#add-comparendo').on('click', function () {
        if (comparendosCount >= maxComparendos) return;

        comparendosCount++;
        const newSection = $('.comparendo-section').first().clone(true, true);
        newSection.attr('data-index', comparendosCount);
        newSection.find('input, select').val('');
        newSection.find('.citaHora').html('<option value="">Seleccionar horario</option>').prop('disabled', true);
        newSection.find('.CantCupos').html('');

        // Reinicializar componentes
        newSection.find('.select2').select2();
        newSection.find('.citaDia').datepicker('destroy');
        newSection.find('.fechaNotificacion').datepicker('destroy');
        newSection.find('.codigo_comparendo_tagify').replaceWith('<input class="codigo_comparendo_tagify form-control" name="codigo_comparendo[]" placeholder="Escribe tu código de comparendo si lo conoces" autocomplete="off" maxlength="3">');

        initComparendoSection(newSection);

        $('#comparendos-slider').append(newSection);
        updateTitles();
        updateNavigation();
        goToSlide(comparendosCount - 1);

        if (comparendosCount >= maxComparendos) {
            $(this).prop('disabled', true);
        }
    });

    // Navegación
    $('#prev-comparendo').on('click', function () {
        if (currentIndex > 0) goToSlide(currentIndex - 1);
    });

    $('#next-comparendo').on('click', function () {
        if (currentIndex < comparendosCount - 1) goToSlide(currentIndex + 1);
    });

    function goToSlide(index) {
        currentIndex = index;
        const translateX = -index * 100;
        $('#comparendos-slider').css('transform', `translateX(${translateX}%)`);
        updateNavigation();
    }

    function updateNavigation() {
        $('#prev-comparendo').toggleClass('d-none', currentIndex === 0 || comparendosCount === 1);
        $('#next-comparendo').toggleClass('d-none', currentIndex === comparendosCount - 1 || comparendosCount === 1);
    }

    function updateTitles() {
        if (comparendosCount > 1) {
            $('.comparendo-section').each(function (i) {
                $(this).find('.comparendo-title').text(`Comparendo #${i + 1}`);
            });
        } else {
            $('.comparendo-section').find('.comparendo-title').text('Comparendo');
        }
    }

    function initComparendoSection(section) {
        // Inicializar datepickers
        initDatePicker(section.find('.citaDia'));
        initDatePicker(section.find('.fechaNotificacion'));

        // Inicializar tagify
        new Tagify(section.find('.codigo_comparendo_tagify')[0], {
            whitelist: whitelist,
            maxTags: 5,
            pattern: /^.{0,3}$/,
            dropdown: {
                maxItems: 20,
                classname: "tags-inline",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Cargar horarios al cambiar fecha
        section.find('.citaDia').on('changeDate', function (e) {
            if (e.date) loadHorarios($(this).closest('.comparendo-section'), e.format());
        });

        // Mensaje de cupos
        section.find('.citaHora').on('change', function () {
            const cuposDiv = $(this).closest('.comparendo-section').find('.CantCupos');
            if ($(this).val()) {
                const palabras = ["¡Date prisa!", "¡Apúrate!", "¡No pierdas tu lugar!", "¡Últimos cupos!", "¡Reserva ya!"];
                const palabra = palabras[Math.floor(Math.random() * palabras.length)];
                const nRandom = Math.floor(Math.random() * 3) + 1;
                cuposDiv.html(`<p class="fw-bold"><span class="morado">${palabra}</span> Solo quedan <span class="morado">${nRandom} cupos.</span></p>`);
            } else {
                cuposDiv.html('');
            }
        });
    }

    function initDatePicker(input) {
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

        input.datepicker({
            todayHighlight: true,
            orientation: 'auto left',
            language: 'es',
            startDate: new Date(),
            beforeShowDay: function (date) {
                const fechaString = date.toISOString().split('T')[0];
                if (festivos.includes(fechaString)) {
                    return { enabled: false, classes: 'disabled-date', tooltip: 'Fecha no disponible' };
                }
                return true;
            }
        });
    }

    function loadHorarios(section, fechaSeleccionada) {
        $('#vuexy-loading').removeClass('d-none').addClass('d-flex');
        const diaSemana = new Date(fechaSeleccionada.split('/').reverse().join('-')).getDay() + 1; // Ajuste para domingo=7
        $.ajax({
            url: url + '/get-horarios',
            type: 'POST',
            data: { id: id_sede },
            dataType: 'json',
            success: function (response) {
                let horariosDisponibles = response.horarios.filter(item => parseInt(item.dia_sede_horario) === diaSemana);
                // Lógica para hoy: filtrar horarios futuros
                if (isToday(fechaSeleccionada)) {
                    const ahora = new Date();
                    const horaLimite = new Date(ahora.getTime() + 40 * 60000);
                    horariosDisponibles = horariosDisponibles.filter(item => {
                        const [hh, mm] = item.inicio_horario.split(':').map(Number);
                        const horaInicio = new Date().setHours(hh, mm, 0, 0);
                        return horaInicio > horaLimite;
                    });
                }
                verificarCupos(section, horariosDisponibles, fechaSeleccionada);
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los horarios.' });
                $('#vuexy-loading').addClass('d-none');
            }
        });
    }

    function isToday(fecha) {
        const hoy = new Date();
        const [d, m, y] = fecha.split('/');
        return `${d}/${m}/${y}` === `${String(hoy.getDate()).padStart(2, '0')}/${String(hoy.getMonth() + 1).padStart(2, '0')}/${hoy.getFullYear()}`;
    }

    function verificarCupos(section, horariosDisponibles, fechaSeleccionada) {
        $.ajax({
            url: url + '/verificar-cupos-horario',
            type: 'POST',
            data: { horarios_disponibles: horariosDisponibles, fecha_seleccionada: fechaSeleccionada, sede: id_sede },
            dataType: 'json',
            success: function (response) {
                let html = '<option value="">Seleccione una opción</option>';
                response.forEach(horario => {
                    html += `<option value="${horario.id_sede_horario}" ${horario.disponible ? '' : 'disabled'}>${horario.rango_horario}</option>`;
                });
                section.find('.citaHora').html(html).prop('disabled', false);
                $('#vuexy-loading').addClass('d-none');
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al verificar cupos.' });
                $('#vuexy-loading').addClass('d-none');
            }
        });
    }

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

    // Validación de documento para citas existentes
    $('input[name="doc_cliente"]').on('blur', function () {
        let cc = $(this).val();
        $.ajax({
            url: url + '/get-citas-agendadas',
            type: 'POST',
            data: { cc: cc },
            dataType: 'json',
            success: function (response) {
                if (response) {
                    $('.resultadoCitas').html('<p class="text-danger fw-bold">Ya tienes una cita agendada, si deseas modificarla escríbenos aquí (<a href="https://wa.me/3054628258">3054511014</a> / <a href="https://wa.me/3054628258">3054628258</a>).</p>');
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
        let valid = true;
        form.find('input:visible, select:visible').each(function () {
            if ($(this).prop('required') && !$(this).val()) valid = false;
        });
        if (!valid) {
            Swal.fire({ title: '¡Error!', text: 'Todos los campos son requeridos', icon: 'error', confirmButtonText: 'Aceptar' });
            return;
        }

        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Enviando...');

        if ($('#phoneCliente').length) {
            $('#phoneCliente').val(phoneInput.getNumber());
        }

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).html('Agendar cita');
                if (response.validate) {
                    window.parent.postMessage({ tipo: 'cita_agendada', idcita: response.id }, '*');
                } else {
                    Swal.fire({ title: '¡Error!', text: response.text, icon: 'error', confirmButtonText: 'OK' });
                }
            },
            error: function () {
                submitBtn.prop('disabled', false).html('Agendar cita');
                Swal.fire({ title: '¡Error!', text: 'Error al agendar la cita', icon: 'error' });
            }
        });
    });

    if ($('.select2').length) {
        $('.select2').select2();
    }
});