// Script para gestionar el modal de grupos: unirse a grupo, crear grupo y confirmaciones.

document.addEventListener('DOMContentLoaded', function () {
    // Referencias a elementos principales del DOM
    const botonAbrirModalPrincipalGrupo = document.getElementById('botonAbrirModalNuevoGrupo');  
    const grupoModalTemplate = document.getElementById('grupoModalTemplate'); 

    // Si el botón existe pero no el template, muestra error
    if (!grupoModalTemplate && botonAbrirModalPrincipalGrupo) {
        console.error("nuevoGrupo.js: Plantilla 'grupoModalTemplate' no encontrada.");
    }

    // Ruta al controlador PHP para peticiones AJAX
    const RUTA_CONTROLADOR_USUARIO = '../../controlador/controladorUsuario.php';

    // Variables globales para el estado del modal y grupos
    let modalGrupoElements = {};
    let grupoIdParaAccion = null;
    let gruponecesitaClaveParaAccion = false;
    let todosLosGruposCargadosGlobal = [];
    let idsGruposUsuarioActual = new Set();

    // HTML para tabs y formulario de crear grupo
    const cabeceraHtmlGrupo = `
        <div class="modalTabs">
            <button id="tabBuscarGrupo" class="tabButton active">Unirse a Grupo</button>
            <button id="tabCrearGrupo" class="tabButton">Crear Nuevo Grupo</button>
        </div>
    `;
    const pieHtmlCrearGrupo = `
        <div id="formCrearGrupo" style="display: none;">
            <h3>Crear Nuevo Grupo</h3>
            <div class="formFila">
                <label for="nombreNuevoGrupo">Nombre del Grupo:</label>
                <input type="text" id="nombreNuevoGrupo" name="nombreNuevoGrupo" required maxlength="100">
            </div>
            <div class="formFila">
                <label for="descripcionNuevoGrupo">Descripción:</label>
                <textarea id="descripcionNuevoGrupo" name="descripcionNuevoGrupo" rows="3" maxlength="200"></textarea>
            </div>
            <div class="formFila">
                <label for="claveNuevoGrupo">Contraseña (opcional):</label>
                <input type="password" id="claveNuevoGrupo" name="claveNuevoGrupo" placeholder="Dejar en blanco si es público">
                <small class="form-text text-muted" style="display:block;margin-top:4px;color:#6c757d;">
                    Mínimo 8 caracteres, al menos una letra y un número. Se permiten símbolos.
                </small>
            </div>
            <div class="formFila">
                <label for="imgNuevoGrupo">URL Imagen (opcional):</label>
                <input type="url" id="imgNuevoGrupo" name="imgNuevoGrupo" placeholder="https://ejemploImagen.com/imagen.png">
            </div>
            <button id="botonConfirmarCrearGrupo" class="botonAccion">Crear Grupo</button>
        </div>
    `;

    // Configuración para inicializar el modal usando modalComun.js
    const configModalGrupo = {
        overlayId: 'modalNuevoGrupoOverlay',
        modalId: 'modalNuevoGrupo',
        tituloModal: 'Gestionar Grupos',
        botonCerrarId: 'botonCerrarModalNuevoGrupo',
        incluirBusqueda: true,
        campoBusquedaId: 'campoBusquedaModalGrupos',
        placeholderBusqueda: 'Buscar grupos por nombre...',
        listaResultadosId: 'listaModalGruposResultados',
        claseListaResultados: 'listaGruposModal',
        cabeceraHtmlAdicional: cabeceraHtmlGrupo,
        pieHtmlAdicional: pieHtmlCrearGrupo,
        confirmacionOverlayId: 'modalConfirmacionAccionGrupoContainer',
        confirmacionContenidoId: 'modalConfirmacionAccionGrupoContenido',
        mensajeConfirmacionId: 'mensajeConfirmacionAccionGrupo',
        confirmacionHtmlAdicionalId: 'confirmacionGrupoHtmlAdicional',
        botonConfirmarId: 'botonConfirmarAccionGrupo',
        botonCancelarId: 'botonCancelarAccionGrupo'
    };

    /**
     * Inicializa todos los componentes y referencias del modal de grupo.
     * Usa la función de modalComun.js para crear/obtener los elementos.
     */
    function inicializarComponentesGrupo() {
        if (typeof inicializarModalGenerico !== 'function') {
            console.error("nuevoGrupo.js: inicializarModalGenerico no está definida.");
            return false;
        }
        const elements = inicializarModalGenerico(configModalGrupo);
        if (!elements) {
            console.error("nuevoGrupo.js: No se pudieron inicializar los elementos del modal de grupo.");
            return false;
        }
        modalGrupoElements = elements;

        // Referencias a tabs, formulario y campos del modal
        modalGrupoElements.tabBuscarGrupo = document.getElementById('tabBuscarGrupo');
        modalGrupoElements.tabCrearGrupo = document.getElementById('tabCrearGrupo');
        modalGrupoElements.formCrearGrupo = document.getElementById('formCrearGrupo');
        modalGrupoElements.botonConfirmarCrearGrupo = document.getElementById('botonConfirmarCrearGrupo');
        modalGrupoElements.nombreNuevoGrupoInput = document.getElementById('nombreNuevoGrupo');
        modalGrupoElements.descripcionNuevoGrupoInput = document.getElementById('descripcionNuevoGrupo');
        modalGrupoElements.claveNuevoGrupoInput = document.getElementById('claveNuevoGrupo');
        modalGrupoElements.imgNuevoGrupoInput = document.getElementById('imgNuevoGrupo');

        // Eventos para cerrar modal, cambiar tabs, buscar, crear grupo, confirmar/cancelar acciones
        if (modalGrupoElements.botonCerrarElement) modalGrupoElements.botonCerrarElement.addEventListener('click', cerrarModalPrincipalGrupo);
        if (modalGrupoElements.overlayElement) modalGrupoElements.overlayElement.addEventListener('click', function (event) {
            // Cierra el modal si el usuario hace click fuera del contenido
            if (event.target === modalGrupoElements.overlayElement) cerrarModalPrincipalGrupo();
        });
        if (modalGrupoElements.tabBuscarGrupo) modalGrupoElements.tabBuscarGrupo.addEventListener('click', () => cambiarTabGrupo('buscar'));
        if (modalGrupoElements.tabCrearGrupo) modalGrupoElements.tabCrearGrupo.addEventListener('click', () => cambiarTabGrupo('crear'));
        if (modalGrupoElements.campoBusquedaElement) modalGrupoElements.campoBusquedaElement.addEventListener('input', function () {
            filtrarGruposEnModalCliente(this.value);
        });
        if (modalGrupoElements.listaResultadosElement) modalGrupoElements.listaResultadosElement.addEventListener('click', manejarClicEnListaGrupos);
        if (modalGrupoElements.botonConfirmarCrearGrupo) modalGrupoElements.botonConfirmarCrearGrupo.addEventListener('click', procesarCrearNuevoGrupo);
        if (modalGrupoElements.botonConfirmarElement) modalGrupoElements.botonConfirmarElement.addEventListener('click', procesarConfirmacionAccionGrupo);
        if (modalGrupoElements.botonCancelarElement) modalGrupoElements.botonCancelarElement.addEventListener('click', () => {
            cerrarModalConfirmacionComun(modalGrupoElements.confirmacionOverlayElement, modalGrupoElements.confirmacionHtmlAdicionalElement);
            grupoIdParaAccion = null;
        });
        if (modalGrupoElements.confirmacionOverlayElement) {
            modalGrupoElements.confirmacionOverlayElement.addEventListener('click', function (event) {
                // Cierra el modal de confirmación si el usuario hace click fuera del contenido
                if (event.target === modalGrupoElements.confirmacionOverlayElement) {
                    cerrarModalConfirmacionComun(modalGrupoElements.confirmacionOverlayElement, modalGrupoElements.confirmacionHtmlAdicionalElement);
                    grupoIdParaAccion = null;
                }
            });
            if (modalGrupoElements.confirmacionContenidoElement) {
                // Previene que el click dentro del contenido cierre el modal
                modalGrupoElements.confirmacionContenidoElement.addEventListener('click', e => e.stopPropagation());
            }
        }
        return true;
    }

    /**
     * Cambia entre la pestaña de buscar grupo y crear grupo.
     * Muestra/oculta los elementos correspondientes.
     */
    function cambiarTabGrupo(tabActiva) {
        if (!modalGrupoElements.tabBuscarGrupo || !modalGrupoElements.tabCrearGrupo || !modalGrupoElements.formCrearGrupo || !modalGrupoElements.campoBusquedaElement || !modalGrupoElements.listaResultadosElement) return;

        if (tabActiva === 'buscar') {
            modalGrupoElements.tabBuscarGrupo.classList.add('active');
            modalGrupoElements.tabCrearGrupo.classList.remove('active');
            modalGrupoElements.campoBusquedaElement.style.display = 'block';
            modalGrupoElements.listaResultadosElement.style.display = 'grid'; // O 'block' si es su display por defecto
            modalGrupoElements.formCrearGrupo.style.display = 'none';
            cargarTodosLosGruposDesdeServidor();
        } else { // 'crear'
            modalGrupoElements.tabBuscarGrupo.classList.remove('active');
            modalGrupoElements.tabCrearGrupo.classList.add('active');
            modalGrupoElements.campoBusquedaElement.style.display = 'none';
            modalGrupoElements.listaResultadosElement.style.display = 'none';
            modalGrupoElements.formCrearGrupo.style.display = 'block';
        }
    }

    /**
     * Llena el set de IDs de grupos a los que pertenece el usuario actual.
     * Usa una variable global si está definida.
     */
    function popularIdsGruposUsuario() {
        idsGruposUsuarioActual.clear();
        // typeof ... !== 'undefined' es una forma segura de comprobar si existe la variable global
        if (typeof idsGruposDelUsuarioActualGlobal !== 'undefined' && Array.isArray(idsGruposDelUsuarioActualGlobal)) {
            idsGruposDelUsuarioActualGlobal.forEach(id => {
                if (id !== null && typeof id !== 'undefined') {
                    idsGruposUsuarioActual.add(id.toString());
                }
            });
        }
    }

    /**
     * Abre el modal principal de grupos, inicializando componentes si es necesario.
     * Siempre muestra la pestaña de buscar grupos al abrir.
     */
    function abrirModalPrincipalGrupo() {
        // Object.keys(obj).length === 0 comprueba si el objeto está vacío
        if (Object.keys(modalGrupoElements).length === 0) {
            if (!inicializarComponentesGrupo()) return;
        }
        popularIdsGruposUsuario();
        if (modalGrupoElements.confirmacionOverlayElement) modalGrupoElements.confirmacionOverlayElement.style.display = 'none';

        abrirModalComun(modalGrupoElements.overlayElement, modalGrupoElements.modalElement, modalGrupoElements.campoBusquedaElement, () => {
            cambiarTabGrupo('buscar');
        });
    }

    /**
     * Cierra el modal principal y limpia los campos del formulario de crear grupo.
     */
    function cerrarModalPrincipalGrupo() {
        cerrarModalComun(modalGrupoElements.overlayElement, modalGrupoElements.modalElement, modalGrupoElements.confirmacionOverlayElement);
        todosLosGruposCargadosGlobal = [];
        if (modalGrupoElements.nombreNuevoGrupoInput) modalGrupoElements.nombreNuevoGrupoInput.value = '';
        if (modalGrupoElements.descripcionNuevoGrupoInput) modalGrupoElements.descripcionNuevoGrupoInput.value = '';
        if (modalGrupoElements.claveNuevoGrupoInput) modalGrupoElements.claveNuevoGrupoInput.value = '';
        if (modalGrupoElements.imgNuevoGrupoInput) modalGrupoElements.imgNuevoGrupoInput.value = '';
    }

    /**
     * Crea un elemento DOM para mostrar un grupo en el modal.
     * Usa una plantilla HTML y rellena los datos.
     */
    function crearElementoGrupoParaModal(grupoData) {
        if (!grupoModalTemplate || !grupoModalTemplate.content) {
            console.error("nuevoGrupo.js: Plantilla 'grupoModalTemplate' no encontrada.");
            return document.createDocumentFragment();
        }
        const copia = grupoModalTemplate.content.cloneNode(true);
        const articleElement = copia.querySelector('.grupoItemModal'); 

        if (!articleElement) {
            console.error("nuevoGrupo.js: La plantilla no contiene '.grupoItemModal'.");
            return document.createDocumentFragment();
        }

        // Asigna datos al dataset del elemento para futuras acciones
        articleElement.dataset.idgrupo = grupoData.idGrupo;
        articleElement.dataset.nombregrupo = grupoData.nombreGrupo;
        // Expresión especial: operador ternario para convertir booleano a string
        articleElement.dataset.necesitaClave = grupoData.necesitaClave ? 'true' : 'false';

        // Imagen del grupo
        const imgElement = articleElement.querySelector('.grupoImg'); 
        if (imgElement) {
            imgElement.src = grupoData.imgGrupo || '../img/grupo.png';
            imgElement.alt = 'Imagen de ' + escaparHtml(grupoData.nombreGrupo);
        }
        // Nombre del grupo
        const nombreElement = articleElement.querySelector('.grupoNombreModal'); 
        if (nombreElement) nombreElement.textContent = escaparHtml(grupoData.nombreGrupo) || 'Nombre no disponible';

        // Si el usuario ya pertenece al grupo, muestra un marcador visual
        if (idsGruposUsuarioActual.has(String(grupoData.idGrupo))) {
            articleElement.classList.add('yaPerteneceVisual'); 
            // Si no existe el marcador, lo crea
            if (!articleElement.querySelector('.yaPerteneceMarker')) { 
                const yaPerteneceMarker = document.createElement('span');
                yaPerteneceMarker.className = 'yaPerteneceMarker'; 
                yaPerteneceMarker.textContent = 'Ya eres miembro';
                const infoDiv = articleElement.querySelector('.grupoInfoModal'); 
                if (infoDiv) infoDiv.appendChild(yaPerteneceMarker);
                else articleElement.appendChild(yaPerteneceMarker);
            }
            articleElement.style.cursor = 'not-allowed';
        } else {
            articleElement.classList.remove('yaPerteneceVisual'); 
            const existingMarker = articleElement.querySelector('.yaPerteneceMarker'); 
            if (existingMarker) existingMarker.remove();
            articleElement.style.cursor = 'pointer';
        }
        return copia;
    }

    /**
     * Renderiza los grupos filtrados en el modal usando la función de modalComun.js.
     */
    function renderizarGruposFiltrados(gruposAMostrar, terminoBusqueda = "") {
        if (!modalGrupoElements.listaResultadosElement) return;
        renderizarItemsEnModal(
            modalGrupoElements.listaResultadosElement,
            gruposAMostrar,
            crearElementoGrupoParaModal,
            `No se encontraron grupos que coincidan con "${escaparHtml(terminoBusqueda)}".`,
            'No hay grupos disponibles para mostrar o cargar.',
            terminoBusqueda
        );
    }

    /**
     * Función de filtrado para grupos: busca por nombre.
     * Devuelve true si el nombre del grupo incluye el término de búsqueda.
     */
    function funcionFiltradoGrupos(grupo, terminoNormalizado) {
        // .toLowerCase() asegura búsqueda insensible a mayúsculas/minúsculas
        return grupo.nombreGrupo.toLowerCase().includes(terminoNormalizado);
    }

    /**
     * Filtra los grupos en el cliente según el término de búsqueda.
     */
    function filtrarGruposEnModalCliente(terminoDeBusqueda) {
        filtrarItemsVisualmente(terminoDeBusqueda, todosLosGruposCargadosGlobal, funcionFiltradoGrupos, renderizarGruposFiltrados);
    }

    /**
     * Carga todos los grupos desde el servidor y los muestra en el modal.
     * Usa fetch y maneja errores de red o formato.
     */
    function cargarTodosLosGruposDesdeServidor() {
        if (!modalGrupoElements.listaResultadosElement) return;
        // Mensaje de carga
        modalGrupoElements.listaResultadosElement.innerHTML = '<p class="noResultadosModal">Cargando grupos...</p>'; 

        fetch(`${RUTA_CONTROLADOR_USUARIO}?accion=gruposAjax`)
            .then(response => {
                // Si la respuesta no es OK, lanza un error
                if (!response.ok) throw new Error(`Error HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                // Si la respuesta es un array, la asigna a la variable global
                if (Array.isArray(data)) {
                    todosLosGruposCargadosGlobal = data;
                    renderizarGruposFiltrados(todosLosGruposCargadosGlobal);
                } else if (data.error) {
                    // Si hay error en la respuesta, lo muestra
                    console.error("nuevoGrupo.js: Error del servidor al obtener grupos:", data.error);
                    modalGrupoElements.listaResultadosElement.innerHTML = `<p class="noResultadosModal">Error: ${escaparHtml(data.error)}</p>`; 
                    todosLosGruposCargadosGlobal = [];
                } else {
                    // Si la respuesta no es un array ni error, muestra mensaje genérico
                    console.error("nuevoGrupo.js: Respuesta de 'gruposAjax' no es un array:", data);
                    todosLosGruposCargadosGlobal = [];
                    renderizarGruposFiltrados([]);
                }
            })
            .catch(error => {
                // Si ocurre un error de red, lo muestra
                console.error('nuevoGrupo.js: Error AJAX al cargar todos los grupos:', error);
                if (modalGrupoElements.listaResultadosElement) modalGrupoElements.listaResultadosElement.innerHTML = `<p class="noResultadosModal">Error al cargar la lista de grupos. ${escaparHtml(error.message)}</p>`; 
                todosLosGruposCargadosGlobal = [];
            });
    }

    /**
     * Maneja el clic en un grupo de la lista para unirse.
     * Si requiere clave, pide la contraseña antes de confirmar.
     */
    function manejarClicEnListaGrupos(event) {
        // .closest busca el ancestro más cercano con la clase dada (útil para delegación de eventos)
        const grupoItem = event.target.closest('.grupoItemModal'); 
        if (grupoItem && grupoItem.dataset.idgrupo) {
            const idGrupo = grupoItem.dataset.idgrupo;
            const nombreGrupo = grupoItem.dataset.nombregrupo || 'este grupo';
            // Expresión especial: compara string con 'true' porque los datasets siempre son string
            const necesitaClave = grupoItem.dataset.necesitaClave === 'true';

            if (idsGruposUsuarioActual.has(idGrupo)) {
                alert('Ya eres miembro de este grupo.');
                return;
            }

            grupoIdParaAccion = idGrupo;
            gruponecesitaClaveParaAccion = necesitaClave;
            let mensajeConfirmacion = `¿Quieres unirte al grupo "${escaparHtml(nombreGrupo)}"?`;
            let htmlAdicional = '';

            if (necesitaClave) {
                mensajeConfirmacion = `El grupo "${escaparHtml(nombreGrupo)}" requiere contraseña para unirse. Ingrésala:`;
                htmlAdicional = `<input type="password" id="claveGrupoConfirmacion" class="inputConfirmacion" placeholder="Contraseña del grupo">`; 
            }

            mostrarModalConfirmacionComun(
                modalGrupoElements.confirmacionOverlayElement,
                modalGrupoElements.mensajeConfirmacionElement,
                mensajeConfirmacion,
                modalGrupoElements.confirmacionHtmlAdicionalElement,
                htmlAdicional
            );
        } else if (grupoItem) {
            // Si el grupo no tiene id válido, muestra advertencia
            console.warn("ID de grupo en dataset es inválido o no encontrado:", grupoItem.dataset.idgrupo);
            grupoIdParaAccion = null;
        }
    }

    /**
     * Procesa la confirmación para unirse a un grupo (con o sin clave).
     * Envía la petición al servidor y maneja la respuesta.
     */
    function procesarConfirmacionAccionGrupo() {
        if (!grupoIdParaAccion) {
            alert('Error: No se seleccionó ningún grupo.');
            cerrarModalConfirmacionComun(modalGrupoElements.confirmacionOverlayElement, modalGrupoElements.confirmacionHtmlAdicionalElement);
            return;
        }

        const params = new URLSearchParams();
        params.append('accion', 'unirseGrupoAjax');
        params.append('id_grupo', grupoIdParaAccion);

        if (gruponecesitaClaveParaAccion) {
            // Busca el input de clave por su ID
            const claveInput = document.getElementById('claveGrupoConfirmacion');
            if (!claveInput || !claveInput.value) {
                alert('Debes ingresar la contraseña para este grupo.');
                if (claveInput) claveInput.focus();
                return;
            }
            params.append('clave_grupo', claveInput.value);
        }

        cerrarModalConfirmacionComun(modalGrupoElements.confirmacionOverlayElement, modalGrupoElements.confirmacionHtmlAdicionalElement);

        fetch(RUTA_CONTROLADOR_USUARIO, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params
        })
        .then(response => {
            // Si la respuesta no es OK, lanza error con el texto de la respuesta
            if (!response.ok) {
                // Expresión especial: response.text().then(...) para obtener el texto del error antes de lanzar
                return response.text().then(text => { throw new Error(`Error HTTP ${response.status}: ${text}`); });
            }
            return response.json();
        })
        .then(respuesta => {
            if (respuesta.mensaje) alert(respuesta.mensaje);
            if (respuesta.success === true) window.location.reload();
            else if (respuesta.error) alert('Error: ' + escaparHtml(respuesta.error));
            else alert('Respuesta inesperada del servidor.');
        })
        .catch(error => {
            // Si el error es por clave incorrecta, muestra mensaje específico
            if (error.message.includes("Error HTTP 403")) alert("Contraseña incorrecta.");
            else alert('Error de red al intentar unirse al grupo.');
            console.error("Error en fetch unirseGrupoAjax:", error);
        })
        .finally(() => {
            grupoIdParaAccion = null;
            gruponecesitaClaveParaAccion = false;
        });
    }

    /**
     * Procesa la creación de un nuevo grupo.
     * Valida los campos y envía la petición al servidor.
     */
    function procesarCrearNuevoGrupo() {
        const nombre = modalGrupoElements.nombreNuevoGrupoInput.value.trim();
        const descripcion = modalGrupoElements.descripcionNuevoGrupoInput.value.trim();
        const clave = modalGrupoElements.claveNuevoGrupoInput.value;
        const imgUrl = modalGrupoElements.imgNuevoGrupoInput.value.trim();

        // Validaciones básicas antes de enviar
        if (!nombre) {
            alert('El nombre del grupo es obligatorio.');
            modalGrupoElements.nombreNuevoGrupoInput.focus(); return;
        }
        if (!descripcion) {
            alert('La descripción del grupo es obligatoria.');
            modalGrupoElements.descripcionNuevoGrupoInput.focus(); return;
        }

        // Validación de contraseña solo si no está en blanco (grupo privado)
        if (clave) {
            // Mínimo 8, máximo 200, al menos una letra y un número
            const patron = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]{8,200}$/;
            if (!patron.test(clave)) {
                alert('Contraseña no válida. Debe tener entre 8 y 200 caracteres, al menos una letra y un número.');
                modalGrupoElements.claveNuevoGrupoInput.focus();
                return;
            }
        }

        const params = new URLSearchParams();
        params.append('accion', 'crearGrupoAjax');
        params.append('nombre_grupo', nombre);
        params.append('descripcion_grupo', descripcion);
        if (clave) params.append('clave_grupo', clave);
        if (imgUrl) params.append('img_grupo', imgUrl);

        fetch(RUTA_CONTROLADOR_USUARIO, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params
        })
        .then(response => response.json())
        .then(respuesta => {
            if (respuesta.mensaje) {
                alert(respuesta.mensaje);
                if (respuesta.success) window.location.reload();
            } else if (respuesta.error) {
                alert('Error al crear el grupo: ' + escaparHtml(respuesta.error));
            } else {
                alert('Respuesta inesperada del servidor al crear el grupo.');
            }
        })
        .catch(error => {
            alert('Error de red al intentar crear el grupo.');
            console.error("Error en fetch crearGrupoAjax:", error);
        });
    }

    // Evento para abrir el modal principal de grupos
    if (botonAbrirModalPrincipalGrupo) {
        botonAbrirModalPrincipalGrupo.addEventListener('click', abrirModalPrincipalGrupo);
    }
});

