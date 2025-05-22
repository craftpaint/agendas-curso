(function () {
    const dom_content = document.currentScript.getAttribute("dom_content") ? document.currentScript.getAttribute("dom_content") : false;
    const dom_listener = document.currentScript.getAttribute("dom_listener") ? document.currentScript.getAttribute("dom_listener") : false;
    const url_widget = document.currentScript.getAttribute("url") ? document.currentScript.getAttribute("url") : false;
    const redirect = document.currentScript.getAttribute("redirect") ? document.currentScript.getAttribute("redirect") : false;
    const altura = (window.innerWidth <= 760) ? '850px' : '80vh';

    if (dom_content && dom_listener && url_widget) {
        const dom_content_element = document.querySelector(dom_content);

        if (!dom_content_element) {
            alert("No se encontró el elemento con el selector: " + dom_content);
            return;
        }

        // Función para asignar eventos a los elementos escuchadores
        function addListenersToElements() {
            const dom_listener_elements = document.querySelectorAll(dom_listener);
            dom_listener_elements.forEach(function (element) {
                if (!element.hasAttribute('data-listener-added')) {
                    element.addEventListener("click", function (e) {
                        const sede = e.currentTarget.getAttribute("sede");
                        const queryString = window.location.search;
                        const urlParams = new URLSearchParams(queryString);
                        let utm_source = urlParams.get('utm_source');
                        if (!utm_source) {
                            utm_source = "Desconocido";
                        }
                        let iframeUrl = `${url_widget}/create-cita/${sede}?utm_source=${utm_source}`;

                        // Agregar todos los parámetros de la URL
                        const params = new URLSearchParams(window.location.search);
                        params.forEach((value, key) => {
                            if (key !== 'utm_source') {
                                iframeUrl += `&${key}=${encodeURIComponent(value)}`;
                            }
                        });
                        let iframe_element = `<iframe src="${iframeUrl}" frameBorder="0" style="overflow-y: hidden;width:100%;height: ${altura};"></iframe>`;
                        dom_content_element.innerHTML = iframe_element;
                    });
                    // Marcamos el elemento para evitar agregar múltiples listeners
                    element.setAttribute('data-listener-added', 'true');
                }
            });
        }

        // Llamamos inicialmente a la función
        addListenersToElements();

        // Observador de cambios en el DOM
        const observer = new MutationObserver(() => {
            addListenersToElements();
        });

        observer.observe(document.body, { childList: true, subtree: true });

        // Escuchamos el mensaje del iframe
        window.addEventListener('message', function (e) {
            if (e.data.tipo === 'cita_agendada') {
                window.location.href = redirect  + "?citaConfirm=" + e.data.idcita;
            }
        });
    } else {
        alert("El atributo dom_content, dom_listener y url son requeridos en loadclub.js");
    }
})();
