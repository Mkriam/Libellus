// Utilidades para crear y manejar modales reutilizables.

/**
 * Escapa caracteres HTML para prevenir XSS (inyección de código malicioso).
 * Convierte caracteres especiales en entidades HTML.
 * @param {string} str - La cadena a escapar.
 * @returns {string} La cadena escapada.
 */
function escaparHtml(str) {
    if (str === null || typeof str === 'undefined') return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

/**
 * Inicializa un modal genérico y su modal de confirmación asociado.
 * Crea los elementos en el DOM si no existen y devuelve referencias útiles.
 * @param {object} config - Configuración para los IDs y contenido del modal.
 * @returns {object|null} Un objeto con referencias a los elementos del modal o null si falta la configuración esencial.
 */
function inicializarModalGenerico(config) {
    // Validación de configuración esencial
    if (!config.overlayId || !config.modalId || !config.confirmacionOverlayId) {
        console.error('Configuración esencial para inicializarModalGenerico no proporcionada.');
        return null;
    }

    let elements = {};

    // Crear overlay del modal principal si no existe
    elements.overlayElement = document.getElementById(config.overlayId);
    if (!elements.overlayElement) {
        // Crea el overlay y el contenido del modal principal
        elements.overlayElement = document.createElement('div');
        elements.overlayElement.className = 'modalOverlay'; 
        elements.overlayElement.id = config.overlayId;
        elements.overlayElement.style.display = 'none';

        elements.modalElement = document.createElement('div');
        elements.modalElement.className = 'modalContenido'; 
        elements.modalElement.id = config.modalId;
        // Estructura básica del modal: cabecera, botón cerrar, campo búsqueda opcional y lista de resultados
        elements.modalElement.innerHTML = `
            <div class="modalCabecera">
                <h2>${escaparHtml(config.tituloModal || 'Modal')}</h2>
                <button class="modalBotonCerrar" id="${config.botonCerrarId || 'botonCerrarModalGenerico'}" title="Cerrar">&times;</button>
            </div>
            <div class="modalCuerpo">
                ${config.cabeceraHtmlAdicional || ''}
                ${config.incluirBusqueda ? `<input type="text" id="${config.campoBusquedaId || 'campoBusquedaModalGenerico'}" placeholder="${escaparHtml(config.placeholderBusqueda || 'Buscar...')}">` : ''}
                <div id="${config.listaResultadosId || 'listaModalResultadosGenerica'}" class="${config.claseListaResultados || 'listaItemsModal'}"></div>
                ${config.pieHtmlAdicional || ''}
            </div>
        `;
        elements.overlayElement.appendChild(elements.modalElement);
        document.body.appendChild(elements.overlayElement);
    } else {
        elements.modalElement = document.getElementById(config.modalId);
    }

    // Referencias internas del modal principal
    elements.botonCerrarElement = document.getElementById(config.botonCerrarId || 'botonCerrarModalGenerico');
    if (config.incluirBusqueda) {
        elements.campoBusquedaElement = document.getElementById(config.campoBusquedaId || 'campoBusquedaModalGenerico');
    }
    elements.listaResultadosElement = document.getElementById(config.listaResultadosId || 'listaModalResultadosGenerica');

    // Crear modal de confirmación si no existe
    elements.confirmacionOverlayElement = document.getElementById(config.confirmacionOverlayId);
    if (!elements.confirmacionOverlayElement) {
        elements.confirmacionOverlayElement = document.createElement('div');
        elements.confirmacionOverlayElement.className = 'confirmacionModal'; 
        elements.confirmacionOverlayElement.id = config.confirmacionOverlayId;
        elements.confirmacionOverlayElement.style.display = 'none';
        // Estructura básica del modal de confirmación
        elements.confirmacionOverlayElement.innerHTML = `
            <div class="confirmacionModalContenido" id="${config.confirmacionContenidoId || 'modalConfirmacionContenidoGenerico'}">
                <p id="${config.mensajeConfirmacionId || 'mensajeConfirmacionGenerico'}">¿Estás seguro?</p>
                <div id="${config.confirmacionHtmlAdicionalId || 'confirmacionHtmlAdicionalGenerico'}"></div>
                <div class="confirmacionModalBotones">
                    <button id="${config.botonConfirmarId || 'botonConfirmarGenerico'}">Sí</button>
                    <button id="${config.botonCancelarId || 'botonCancelarGenerico'}">No</button>
                </div>
            </div>
        `;
        document.body.appendChild(elements.confirmacionOverlayElement);
    }
    // Referencias internas del modal de confirmación
    elements.confirmacionContenidoElement = document.getElementById(config.confirmacionContenidoId || 'modalConfirmacionContenidoGenerico');
    elements.mensajeConfirmacionElement = document.getElementById(config.mensajeConfirmacionId || 'mensajeConfirmacionGenerico');
    elements.confirmacionHtmlAdicionalElement = document.getElementById(config.confirmacionHtmlAdicionalId || 'confirmacionHtmlAdicionalGenerico');
    elements.botonConfirmarElement = document.getElementById(config.botonConfirmarId || 'botonConfirmarGenerico');
    elements.botonCancelarElement = document.getElementById(config.botonCancelarId || 'botonCancelarGenerico');

    return elements;
}

/**
 * Abre un modal genérico.
 * Muestra el overlay y el modal, limpia el campo de búsqueda si existe y ejecuta un callback opcional.
 * @param {HTMLElement} overlayElement - Overlay del modal.
 * @param {HTMLElement} modalElement - Contenido principal del modal.
 * @param {HTMLElement} campoBusquedaElement - Input de búsqueda (opcional).
 * @param {Function} callbackCargarDatos - Función a ejecutar al abrir el modal (opcional).
 */
function abrirModalComun(overlayElement, modalElement, campoBusquedaElement, callbackCargarDatos) {
    if (overlayElement) overlayElement.style.display = 'flex';
    if (modalElement) modalElement.style.display = 'flex'; // Usar flex para centrar el modalContenido
    document.body.classList.add('modalAbierta'); 
    if (campoBusquedaElement) campoBusquedaElement.value = '';
    if (typeof callbackCargarDatos === 'function') {
        callbackCargarDatos();
    }
}

/**
 * Cierra un modal genérico y su modal de confirmación asociado.
 * Oculta los overlays y elimina la clase del body.
 * @param {HTMLElement} overlayElement - Overlay del modal principal.
 * @param {HTMLElement} modalElement - Contenido principal del modal.
 * @param {HTMLElement} confirmacionOverlayElement - Overlay del modal de confirmación.
 */
function cerrarModalComun(overlayElement, modalElement, confirmacionOverlayElement) {
    if (overlayElement) overlayElement.style.display = 'none';
    if (modalElement) modalElement.style.display = 'none';
    if (confirmacionOverlayElement) confirmacionOverlayElement.style.display = 'none';
    document.body.classList.remove('modalAbierta'); 
}

/**
 * Muestra un modal de confirmación con mensaje y HTML adicional opcional.
 * @param {HTMLElement} confirmacionOverlayElement - Overlay del modal de confirmación.
 * @param {HTMLElement} mensajeConfirmacionElement - Elemento donde se muestra el mensaje.
 * @param {string} mensaje - Mensaje de confirmación.
 * @param {HTMLElement} htmlAdicionalContainer - Contenedor para HTML adicional.
 * @param {string} htmlAdicional - HTML adicional opcional.
 */
function mostrarModalConfirmacionComun(confirmacionOverlayElement, mensajeConfirmacionElement, mensaje, htmlAdicionalContainer, htmlAdicional = '') {
    if (mensajeConfirmacionElement) mensajeConfirmacionElement.innerHTML = escaparHtml(mensaje);
    if (htmlAdicionalContainer) htmlAdicionalContainer.innerHTML = htmlAdicional;
    if (confirmacionOverlayElement) confirmacionOverlayElement.style.display = 'flex'; // Usar flex para centrar
}

/**
 * Cierra el modal de confirmación y limpia el HTML adicional.
 * @param {HTMLElement} confirmacionOverlayElement - Overlay del modal de confirmación.
 * @param {HTMLElement} htmlAdicionalContainer - Contenedor para HTML adicional.
 */
function cerrarModalConfirmacionComun(confirmacionOverlayElement, htmlAdicionalContainer) {
    if (confirmacionOverlayElement) confirmacionOverlayElement.style.display = 'none';
    if (htmlAdicionalContainer) htmlAdicionalContainer.innerHTML = '';
}

/**
 * Renderiza una lista de items en un contenedor del modal.
 * Si hay items, los muestra usando la función de creación de elementos.
 * Si no hay resultados, muestra un mensaje adecuado.
 * @param {HTMLElement} listaResultadosElement - Contenedor donde se muestran los items.
 * @param {Array} itemsAMostrar - Array de items a mostrar.
 * @param {Function} crearElementoFn - Función que recibe un item y devuelve un elemento DOM.
 * @param {string} mensajeNoResultados - Mensaje si no hay coincidencias.
 * @param {string} mensajeVacio - Mensaje si no hay items para mostrar.
 * @param {string} terminoBusquedaActual - Término de búsqueda actual (para decidir qué mensaje mostrar).
 */
function renderizarItemsEnModal(listaResultadosElement, itemsAMostrar, crearElementoFn, mensajeNoResultados = "No se encontraron coincidencias.", mensajeVacio = "No hay items para mostrar.", terminoBusquedaActual = "") {
    if (!listaResultadosElement) {
        console.error("Elemento listaResultados no disponible para renderizar.");
        return;
    }
    listaResultadosElement.innerHTML = '';
    if (itemsAMostrar.length > 0) {
        // Si hay items, los añade al contenedor usando un fragmento para eficiencia.
        const fragmentoGeneral = document.createDocumentFragment();
        itemsAMostrar.forEach(item => {
            fragmentoGeneral.appendChild(crearElementoFn(item));
        });
        listaResultadosElement.appendChild(fragmentoGeneral);
    } else {
        // Si hay término de búsqueda, muestra mensaje de "no resultados", si no, mensaje de "vacío".
        if (terminoBusquedaActual.trim() !== "") {
            listaResultadosElement.innerHTML = `<p class="noResultadosModal">${escaparHtml(mensajeNoResultados)}</p>`;
        } else {
            listaResultadosElement.innerHTML = `<p class="noResultadosModal">${escaparHtml(mensajeVacio)}</p>`;
        }
    }
}

/**
 * Filtra items visualmente basándose en un término de búsqueda y una función de filtrado específica.
 * Llama a la función de renderizado con los items filtrados.
 * @param {string} terminoDeBusqueda - Texto a buscar.
 * @param {Array} todosLosItemsCargados - Todos los items disponibles.
 * @param {Function} funcionFiltradoEspecifica - Función que decide si un item coincide con el término.
 * @param {Function} renderizarFn - Función para renderizar los items filtrados.
 */
function filtrarItemsVisualmente(terminoDeBusqueda, todosLosItemsCargados, funcionFiltradoEspecifica, renderizarFn) {
    const terminoNormalizado = terminoDeBusqueda.toLowerCase().trim();
    if (!terminoNormalizado) {
        // Si no hay término, muestra todos los items.
        renderizarFn(todosLosItemsCargados, terminoNormalizado);
        return;
    }
    // Filtra los items usando la función específica y renderiza el resultado.
    const itemsFiltrados = todosLosItemsCargados.filter(item =>
        funcionFiltradoEspecifica(item, terminoNormalizado)
    );
    renderizarFn(itemsFiltrados, terminoNormalizado);
}