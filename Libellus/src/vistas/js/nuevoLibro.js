// Script para gestionar el modal de añadir libro a la colección o a un grupo en Libellus

document.addEventListener('DOMContentLoaded', function () {
    // Elementos principales del modal y contexto
    const botonDisparadorModal = document.getElementById('botonAbrirModalAnadirLibro');
    const libroModalTemplate = document.getElementById('modalLibro');

    // Detecta si estamos en contexto de grupo o usuario
    const hiddenNomGrupoInput = document.getElementById('hiddenNomGrupoParaModal');
    // !! convierte el valor a booleano: true si existe el input oculto (contexto grupo)
    const esContextoGrupo = !!hiddenNomGrupoInput;
    const nombreGrupoContextual = esContextoGrupo ? hiddenNomGrupoInput.value : null;

    // Define rutas y textos según el contexto (grupo o usuario)
    const RUTA_CONTROLADOR_TARGET = esContextoGrupo ?
        '../controlador/controladorInfoGrupo.php' :
        '../controlador/controladorUsuario.php';

    const ACCION_SUBMIT = esContextoGrupo ? 'addLibroGrupo' : 'guardarLibroUsu';
    const TITULO_MODAL = esContextoGrupo ? 'Añadir Libro al Grupo' : 'Añadir Libro a tu Colección';
    const MENSAJE_CONFIRMACION_BASE = esContextoGrupo ? "a este grupo" : "a tu colección";
    const MENSAJE_YA_EXISTE_BASE = esContextoGrupo ? "En este grupo" : "En tu colección";

    // Variables globales para el modal
    let modalLibroElements = {};
    let libroIdParaAnadir = null;
    let todosLosLibrosCargadosGlobal = [];
    let idsLibrosContextualesSet = new Set();

    // Configuración de IDs y clases para el modal
    const configModalLibro = {
        overlayId: 'modalAnadirLibroOverlay',
        modalId: 'modalAnadirLibro',
        tituloModal: TITULO_MODAL,
        botonCerrarId: 'botonCerrarModalAnadirLibro',
        incluirBusqueda: true,
        campoBusquedaId: 'campoBusquedaModalLibros',
        placeholderBusqueda: 'Buscar libros por título o autor...',
        listaResultadosId: 'listaModalLibrosResultados',
        claseListaResultados: 'listaLibrosModal',
        confirmacionOverlayId: 'modalConfirmacionAnadirLibroContainer',
        confirmacionContenidoId: 'modalConfirmacionAnadirLibroContenido',
        mensajeConfirmacionId: 'mensajeConfirmacionAnadirLibro',
        botonConfirmarId: 'botonConfirmarAnadirLibro',
        botonCancelarId: 'botonCancelarAnadirLibro'
    };

    /**
     * Inicializa los componentes del modal de libro.
     * Añade listeners a los botones y campos del modal.
     * @returns {boolean} true si todo fue bien, false si faltan dependencias.
     */
    function inicializarComponentesLibro() {
        if (typeof inicializarModalGenerico !== 'function') {
            console.error("nuevoLibro.js: inicializarModalGenerico no está definida.");
            return false;
        }
        const elements = inicializarModalGenerico(configModalLibro);
        if (!elements) {
            console.error("nuevoLibro.js: No se pudieron inicializar los elementos del modal de libro.");
            return false;
        }
        modalLibroElements = elements;

        // Cierra el modal principal al pulsar el botón de cerrar
        if (modalLibroElements.botonCerrarElement) modalLibroElements.botonCerrarElement.addEventListener('click', cerrarModalPrincipalLibro);
        // Cierra el modal si se hace click fuera del contenido
        if (modalLibroElements.overlayElement) modalLibroElements.overlayElement.addEventListener('click', (event) => {
            if (event.target === modalLibroElements.overlayElement) cerrarModalPrincipalLibro();
        });
        // Filtra libros al escribir en el campo de búsqueda
        if (modalLibroElements.campoBusquedaElement) modalLibroElements.campoBusquedaElement.addEventListener('input', function () {
            filtrarLibrosEnModalCliente(this.value);
        });
        // Permite seleccionar un libro de la lista
        if (modalLibroElements.listaResultadosElement) modalLibroElements.listaResultadosElement.addEventListener('click', manejarClicEnListaLibros);

        // Botones de confirmación/cancelación en el modal de confirmación
        if (modalLibroElements.botonConfirmarElement) modalLibroElements.botonConfirmarElement.addEventListener('click', procesarConfirmacionAnadirLibro);
        if (modalLibroElements.botonCancelarElement) modalLibroElements.botonCancelarElement.addEventListener('click', () => {
            cerrarModalConfirmacionComun(modalLibroElements.confirmacionOverlayElement);
            libroIdParaAnadir = null;
        });
        // Cierra el modal de confirmación si se hace click fuera del contenido
        if (modalLibroElements.confirmacionOverlayElement) {
            modalLibroElements.confirmacionOverlayElement.addEventListener('click', (event) => {
                if (event.target === modalLibroElements.confirmacionOverlayElement) {
                    cerrarModalConfirmacionComun(modalLibroElements.confirmacionOverlayElement);
                    libroIdParaAnadir = null;
                }
            });
            if (modalLibroElements.confirmacionContenidoElement) {
                // Previene que el click dentro del contenido cierre el modal
                modalLibroElements.confirmacionContenidoElement.addEventListener('click', e => e.stopPropagation());
            }
        }
        return true;
    }

    /**
     * Llena el set de IDs de libros ya presentes en el grupo o usuario.
     * Así se evita mostrar libros repetidos.
     */
    function popularIdsContextuales() {
        idsLibrosContextualesSet.clear();
        let sourceArray = [];

        // typeof ... !== 'undefined' es una forma segura de comprobar si existe la variable global
        if (esContextoGrupo) {
            if (typeof idsLibrosEnGrupoActual !== 'undefined' && Array.isArray(idsLibrosEnGrupoActual)) {
                sourceArray = idsLibrosEnGrupoActual;
            }
        } else {
            if (typeof idsLibrosGuardadosPorUsuarioGlobal !== 'undefined' && Array.isArray(idsLibrosGuardadosPorUsuarioGlobal)) {
                sourceArray = idsLibrosGuardadosPorUsuarioGlobal;
            }
        }

        // Añade cada id como string al set para comparación rápida
        sourceArray.forEach(id => {
            if (id !== null && typeof id !== 'undefined') {
                idsLibrosContextualesSet.add(id.toString());
            }
        });
    }

    /**
     * Abre el modal principal para añadir libro.
     * Inicializa componentes y carga los libros si es necesario.
     */
    function abrirModalPrincipalLibro() {
        // Object.keys(obj).length === 0 comprueba si el objeto está vacío
        if (Object.keys(modalLibroElements).length === 0) {
            if (!inicializarComponentesLibro()) {
                console.error("Fallo al inicializar componentes del modal. No se puede abrir.");
                return;
            }
        }
        popularIdsContextuales();

        if (modalLibroElements.confirmacionOverlayElement) modalLibroElements.confirmacionOverlayElement.style.display = 'none';

        if (modalLibroElements.modalElement) {
            // Cambia el título del modal según el contexto
            const tituloH2 = modalLibroElements.modalElement.querySelector('.modalCabecera h2');
            if (tituloH2) tituloH2.textContent = configModalLibro.tituloModal;
        }

        if (typeof abrirModalComun !== 'function') {
            console.error("nuevoLibro.js: abrirModalComun no está definida.");
            return;
        }
        // Llama a la función para abrir el modal y cargar los libros
        abrirModalComun(modalLibroElements.overlayElement, modalLibroElements.modalElement, modalLibroElements.campoBusquedaElement, cargarTodosLosLibrosDesdeServidor);
    }

    /**
     * Cierra el modal principal de añadir libro.
     * Limpia la lista de libros cargados.
     */
    function cerrarModalPrincipalLibro() {
        if (typeof cerrarModalComun !== 'function') {
            console.error("nuevoLibro.js: cerrarModalComun no está definida.");
            return;
        }
        cerrarModalComun(modalLibroElements.overlayElement, modalLibroElements.modalElement, modalLibroElements.confirmacionOverlayElement);
        todosLosLibrosCargadosGlobal = [];
    }

    /**
     * Crea el elemento visual de un libro para mostrarlo en el modal.
     * @param {object} libroData - Datos del libro.
     * @returns {DocumentFragment} Elemento listo para insertar.
     */
    function crearElementoLibroParaModal(libroData) {
        if (!libroModalTemplate || !libroModalTemplate.content) {
            console.error("Plantilla 'modalLibro' no encontrada.");
            return document.createDocumentFragment();
        }
        const clone = libroModalTemplate.content.cloneNode(true);
        const articleElement = clone.querySelector('.datosLibroModal');

        if (!articleElement) {
            console.error("La plantilla no contiene '.datosLibroModal'.");
            return document.createDocumentFragment();
        }

        articleElement.dataset.idlibro = libroData.idLibro;
        articleElement.dataset.titulo = libroData.titulo;

        // Imagen de portada
        const imgElement = articleElement.querySelector('.portadaLibroModal');
        if (imgElement) {
            imgElement.src = libroData.portadaUrl || '../img/portadaPorDefecto.png';
            imgElement.alt = 'Portada de ' + (libroData.titulo ? escaparHtml(libroData.titulo) : 'Libro');
        }
        // Título
        const tituloElement = articleElement.querySelector('.libroTituloModal');
        if (tituloElement) tituloElement.textContent = libroData.titulo || 'Título no disponible';

        // Autores
        const autoresElement = articleElement.querySelector('.autoresLibroModal');
        if (autoresElement) autoresElement.textContent = libroData.autores && libroData.autores.length > 0 ? libroData.autores.map(escaparHtml).join(', ') : 'Autor no disponible';

        // Géneros
        const generosElement = articleElement.querySelector('.generosLibroModal');
        if (generosElement) generosElement.textContent = libroData.generos && libroData.generos.length > 0 ? libroData.generos.map(escaparHtml).join(', ') : 'Género no disponible';

        // Marca visual si ya está guardado
        if (idsLibrosContextualesSet.has(String(libroData.idLibro))) {
            articleElement.classList.add('yaGuardadoVisual');
            const existingMarker = articleElement.querySelector('.yaGuardadoMarker');
            if (existingMarker) existingMarker.remove();

            const yaGuardadoMarker = document.createElement('span');
            yaGuardadoMarker.className = 'yaGuardadoMarker';
            yaGuardadoMarker.textContent = MENSAJE_YA_EXISTE_BASE;

            const infoDiv = articleElement.querySelector('.infoLibroModal');
            if (infoDiv) infoDiv.appendChild(yaGuardadoMarker);
            else articleElement.appendChild(yaGuardadoMarker);

            articleElement.style.cursor = 'not-allowed';
        } else {
            articleElement.classList.remove('yaGuardadoVisual');
            const existingMarker = articleElement.querySelector('.yaGuardadoMarker');
            if (existingMarker) existingMarker.remove();
            articleElement.style.cursor = 'pointer';
        }
        return clone;
    }

    /**
     * Renderiza la lista de libros filtrados en el modal.
     * @param {Array} librosAmostrar - Libros a mostrar.
     * @param {string} terminoBusqueda - Texto buscado (opcional).
     */
    function renderizarLibrosFiltrados(librosAmostrar, terminoBusqueda = "") {
        if (!modalLibroElements.listaResultadosElement) {
            console.error("listaResultadosElement no disponible para renderizar."); return;
        }
        if (typeof renderizarItemsEnModal !== 'function') {
            console.error("nuevoLibro.js: renderizarItemsEnModal no está definida."); return;
        }
        renderizarItemsEnModal(
            modalLibroElements.listaResultadosElement,
            librosAmostrar,
            crearElementoLibroParaModal,
            `No se encontraron libros que coincidan con "${escaparHtml(terminoBusqueda)}".`,
            'No hay libros disponibles para mostrar o cargar.',
            terminoBusqueda
        );
    }

    /**
     * Función de filtrado: busca coincidencias en título o autor.
     * @param {object} libro - Libro a evaluar.
     * @param {string} terminoNormalizado - Texto de búsqueda en minúsculas.
     * @returns {boolean} true si coincide.
     */
    function funcionFiltradoLibros(libro, terminoNormalizado) {
        // .some() devuelve true si algún autor coincide con el término
        return (libro.titulo && libro.titulo.toLowerCase().includes(terminoNormalizado)) ||
               (libro.autores && libro.autores.some(autor => autor.toLowerCase().includes(terminoNormalizado)));
    }

    /**
     * Filtra los libros en el modal según el término de búsqueda.
     * Llama a la función de renderizado con los resultados.
     * @param {string} terminoDeBusqueda - Texto a buscar.
     */
    function filtrarLibrosEnModalCliente(terminoDeBusqueda) {
        if (typeof filtrarItemsVisualmente !== 'function') {
            console.error("nuevoLibro.js: filtrarItemsVisualmente no está definida."); return;
        }
        filtrarItemsVisualmente(terminoDeBusqueda, todosLosLibrosCargadosGlobal, funcionFiltradoLibros, renderizarLibrosFiltrados);
    }

    /**
     * Carga todos los libros desde el servidor para mostrarlos en el modal.
     * Actualiza la variable global y renderiza la lista.
     */
    function cargarTodosLosLibrosDesdeServidor() {
        const RUTA_OBTENER_TODOS_LIBROS = '../controlador/controladorUsuario.php';

        if (!modalLibroElements.listaResultadosElement) {
            console.warn("listaResultadosElement no está listo para cargar libros."); return;
        }
        modalLibroElements.listaResultadosElement.innerHTML = `<p class="noResultadosModal">Cargando libros...</p>`;

        fetch(`${RUTA_OBTENER_TODOS_LIBROS}?accion=librosAjax`)
            .then(response => {
                // Si la respuesta no es OK, lanza un error
                if (!response.ok) throw new Error(`Error HTTP ${response.status} al cargar libros: ${response.statusText}`);
                return response.json();
            })
            .then(data => {
                if (Array.isArray(data)) {
                    // Se espera que el backend devuelva 'idLibro', 'titulo', 'portadaUrl', 'autores', 'generos'
                    todosLosLibrosCargadosGlobal = data;
                    renderizarLibrosFiltrados(todosLosLibrosCargadosGlobal);
                } else {
                    console.error("Respuesta de 'librosAjax' no es un array:", data);
                    todosLosLibrosCargadosGlobal = []; renderizarLibrosFiltrados([]);
                }
            })
            .catch(error => {
                console.error('Error AJAX al cargar todos los libros:', error);
                if (modalLibroElements.listaResultadosElement) {
                    modalLibroElements.listaResultadosElement.innerHTML = `<p class="noResultadosModal">Error al cargar la lista de libros. ${escaparHtml(error.message)}</p>`;
                }
                todosLosLibrosCargadosGlobal = [];
            });
    }

    /**
     * Maneja el clic en un libro de la lista del modal.
     * Si el libro ya está guardado, muestra un aviso.
     * Si no, abre el modal de confirmación para añadirlo.
     * @param {Event} event - Evento de clic.
     */
    function manejarClicEnListaLibros(event) {
        // .closest busca el ancestro más cercano con la clase dada (delegación de eventos)
        const libroItem = event.target.closest('.datosLibroModal');
        // Expresión especial: comprueba que el idlibro no sea vacío, null o "e"
        if (libroItem && libroItem.dataset.idlibro && libroItem.dataset.idlibro.trim() !== "" &&
            libroItem.dataset.idlibro !== "null" && libroItem.dataset.idlibro !== "e") {

            const idLibroSeleccionado = libroItem.dataset.idlibro;

            if (idsLibrosContextualesSet.has(idLibroSeleccionado)) {
                alert(`Este libro ya está ${MENSAJE_YA_EXISTE_BASE}.`);
                return;
            }

            libroIdParaAnadir = idLibroSeleccionado;
            const tituloLibro = libroItem.dataset.titulo || 'este libro';
            const mensajeConfirm = `¿Quieres añadir "${escaparHtml(tituloLibro)}" ${MENSAJE_CONFIRMACION_BASE}?`;

            if (typeof mostrarModalConfirmacionComun !== 'function') {
                console.error("nuevoLibro.js: mostrarModalConfirmacionComun no está definida."); return;
            }
            mostrarModalConfirmacionComun(
                modalLibroElements.confirmacionOverlayElement,
                modalLibroElements.mensajeConfirmacionElement,
                mensajeConfirm
            );
        } else if (libroItem) {
            // Si el libro no tiene id válido, muestra advertencia
            console.warn("ID de libro en dataset es inválido:", libroItem.dataset.idlibro);
            libroIdParaAnadir = null;
        }
    }

    /**
     * Procesa la confirmación de añadir libro.
     * Envía la petición al backend y muestra el resultado.
     */
    function procesarConfirmacionAnadirLibro() {
        if (!libroIdParaAnadir) {
            alert('Error: No se ha seleccionado ningún libro.');
            cerrarModalConfirmacionComun(modalLibroElements.confirmacionOverlayElement); return;
        }
        const idActualParaEnviar = libroIdParaAnadir;
        cerrarModalConfirmacionComun(modalLibroElements.confirmacionOverlayElement);

        const params = new URLSearchParams();
        params.append('id_libro', idActualParaEnviar);
        params.append('accion', ACCION_SUBMIT);

        // Si es contexto grupo, añade el nombre del grupo al parámetro
        if (esContextoGrupo) {
            if (nombreGrupoContextual) {
                params.append('nomGrupoAdd', nombreGrupoContextual);
            } else {
                // Busca el input oculto por si acaso
                const nomGrupoHiddenInputEl = document.getElementById('hiddenNomGrupoParaModal');
                if (nomGrupoHiddenInputEl && nomGrupoHiddenInputEl.value) {
                    params.append('nomGrupoAdd', nomGrupoHiddenInputEl.value);
                } else {
                    alert('Error crítico: No se pudo identificar el grupo para añadir el libro.');
                    console.error("Error: No se encontró #hiddenNomGrupoParaModal o su valor.");
                    libroIdParaAnadir = null; return;
                }
            }
        }

        fetch(RUTA_CONTROLADOR_TARGET, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params
        })
        .then(response => {
            // Expresión especial: intenta parsear JSON de error, si falla muestra error genérico
            if (!response.ok) {
                return response.json().then(errData => {
                    throw new Error(errData.error || `Error HTTP ${response.status} - ${response.statusText}`);
                }).catch(() => {
                    throw new Error(`Error HTTP ${response.status} - ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(respuesta => {
            if (respuesta.mensaje) {
                alert(respuesta.mensaje);
                // Si es grupo, redirige a la página del grupo; si no, recarga la página
                if (esContextoGrupo && nombreGrupoContextual) {
                    window.location.href = `infoGrupo.php?grupo=${encodeURIComponent(nombreGrupoContextual)}`;
                } else {
                    window.location.reload();
                }
            } else if (respuesta.error) {
                alert('Error del servidor: ' + escaparHtml(respuesta.error));
            } else {
                alert('Respuesta inesperada del servidor.');
            }
        })
        .catch(error => {
            console.error("Error en fetch o procesamiento:", error);
            alert('Error de comunicación: ' + escaparHtml(error.message));
        })
        .finally(() => {
            libroIdParaAnadir = null;
        });
    }

    // Evento para abrir el modal al pulsar el botón
    if (botonDisparadorModal) {
        botonDisparadorModal.addEventListener('click', abrirModalPrincipalLibro);
    }
});