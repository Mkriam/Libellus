// Script para buscar libros en tiempo real y mostrar resultados dinámicamente.

//Espera a que el DOM esté completamente cargado antes de ejecutar el script.

document.addEventListener('DOMContentLoaded', () => {
    // Obtiene referencias a los elementos principales del DOM.
    const campoBusqueda = document.getElementById('campoBusqueda'); // Input de búsqueda de libros
    const listaResultados = document.getElementById('listaLibrosResult'); // Contenedor donde se muestran los resultados
    const libroTemplate = document.getElementById('libroTemplate'); // Template HTML para un libro
    let debounceTimeout; // Variable para controlar el "debounce" (espera antes de buscar)

    // Validación: Si no existe el campo de búsqueda, muestra un error en consola.
    if (!campoBusqueda) {
        console.error("buscarLibro.js: El campo de búsqueda 'campoBusqueda' no fue encontrado.");
        // No se retorna porque la página podría funcionar sin búsqueda.
    }
    // Validación: Si no existe el contenedor de resultados, muestra un error y detiene el script.
    if (!listaResultados) {
        console.error("buscarLibro.js: El contenedor de resultados 'listaLibrosResult' no fue encontrado.");
        return; // Esencial para mostrar resultados
    }
    // Validación: Si no existe el template o su contenido, muestra un error y detiene el script.
    if (!libroTemplate?.content) {
        // El operador ?. (encadenamiento opcional) evita error si libroTemplate es null/undefined.
        console.error("buscarLibro.js: La plantilla 'libroTemplate' o su contenido no fueron encontrados.");
        return; // Esencial para crear elementos
    }

    /**
     * Escapa caracteres especiales de HTML para evitar inyección de código.
     * @param {string} str - Texto a escapar.
     * @returns {string} - Texto seguro para insertar en HTML.
     */
    const escaparHtml = str => {
        const div = document.createElement('div');
        div.textContent = str ?? ''; // El operador ?? devuelve '' si str es null o undefined.
        return div.innerHTML;
    };

    /**
     * Crea un elemento DOM de libro a partir de los datos recibidos.
     * Usa la plantilla HTML y rellena los campos.
     * @param {Object} libro - Datos del libro.
     * @returns {DocumentFragment} - Elemento listo para insertar.
     */
    const crearElementoLibro = ({ id_libro, titulo, portada, autores_nombres, generos_nombres, estadoGuardado }) => {
        const copia = libroTemplate.content.cloneNode(true); // Clona la plantilla
        const articleElement = copia.querySelector('.datosLibro'); // Elemento raíz del libro

        if (!articleElement) {
            console.error("buscarLibro.js: Plantilla 'libroTemplate' incompleta, falta '.datosLibro'.");
            return document.createElement('div');
        }

        // Asigna el ID del libro al dataset para futuras acciones
        Object.assign(articleElement.dataset, { idlibro: id_libro || '' });

        // Rellena la portada del libro
        const imgPortada = articleElement.querySelector('.imgPortada');
        if (imgPortada) imgPortada.src = portada || '../img/portadaPorDefecto.png';
        else console.warn("buscarLibro.js: '.imgPortada' no encontrado en template");

        // Rellena el título
        const tituloLibroEl = articleElement.querySelector('.tituloLibro');
        if (tituloLibroEl) tituloLibroEl.textContent = titulo || 'Título no disponible';
        else console.warn("buscarLibro.js: '.tituloLibro' no encontrado en template");

        // Rellena los autores
        const autoresLibroEl = articleElement.querySelector('.autoresLibro');
        // El operador ?. verifica que autores_nombres exista antes de llamar a map.
        if (autoresLibroEl) autoresLibroEl.textContent = autores_nombres?.map(escaparHtml).join(', ') || 'No disponible';
        else console.warn("buscarLibro.js: '.autoresLibro' no encontrado en template");

        // Rellena los géneros
        const generosLibroEl = articleElement.querySelector('.generosLibro');
        if (generosLibroEl) generosLibroEl.textContent = generos_nombres?.map(escaparHtml).join(', ') || 'No disponible';
        else console.warn("buscarLibro.js: '.generosLibro' no encontrado en template");

        // Estado del libro (guardado, leído, etc.)
        const estadoLibroEl = articleElement.querySelector('.estadoLibro');
        if (estadoLibroEl) estadoLibroEl.textContent = escaparHtml(estadoGuardado) || 'No especificado';
        else console.warn("buscarLibro.js: '.estadoLibro' no encontrado en template");

        // Hace que al hacer click en el libro se abra su área de detalle
        articleElement.addEventListener('click', () => {
            // Verifica que el idlibro exista y no sea vacío
            if (articleElement.dataset.idlibro && articleElement.dataset.idlibro.trim()) {
                window.location.href = `areaLibro.php?libro=${articleElement.dataset.idlibro}`;
            } else {
                console.warn('buscarLibro.js: El libro seleccionado no tiene un ID válido en dataset.', articleElement);
            }
        });

        return copia;
    };

    /**
     * Realiza la búsqueda AJAX de libros y muestra los resultados.
     * Filtra por título o autor usando el término de búsqueda.
     * @param {string} termino - Texto a buscar.
     */
    const buscarLibros = termino => {
        listaResultados.innerHTML = '<p class="infoCarga">Buscando...</p>'; // Mensaje de carga

        // Realiza la petición AJAX usando fetch.
        fetch(`../controlador/controladorUsuario.php?accion=buscarLibrosAjax&buscar=${encodeURIComponent(termino)}`)
            .then(res => {
                // Si la respuesta no es OK (código 200), lanza un error.
                if (!res.ok) {
                    throw new Error(`Error HTTP ${res.status} - ${res.statusText}`);
                }
                return res.json(); // Convierte la respuesta a JSON
            })
            .then(data => {
                listaResultados.innerHTML = '';
                // Si hay un error en la respuesta, lo muestra.
                if (data.error) {
                    listaResultados.innerHTML = `<div class="mensajeError">${escaparHtml(data.error)}</div>`;
                    return;
                }
                // Expresión moderna: 
                // data.libros?.length devuelve undefined si libros no existe, o el número de libros si existe.
                // El ! niega el valor: si es 0 (array vacío) o undefined (no existe), entra en el if.
                if (!data.libros?.length) {
                    listaResultados.innerHTML = `<div class="noResultados">No se encontraron resultados para "${escaparHtml(termino)}".</div>`;
                    return;
                }
                // --- FILTRO POR TÍTULO O AUTOR ---
                // Normaliza el término de búsqueda a minúsculas y sin espacios extra.
                const terminoNormalizado = termino.trim().toLowerCase();
                // Filtra los libros para mostrar solo los que coinciden en título o autor.
                const filtrados = data.libros.filter(libro => {
                    const titulo = (libro.titulo || '').toLowerCase();
                    // Si autores_nombres es un array, lo convierte a minúsculas.
                    const autores = Array.isArray(libro.autores_nombres) ? libro.autores_nombres.map(a => a.toLowerCase()) : [];
                    // Devuelve true si el término está en el título o en algún autor.
                    return titulo.includes(terminoNormalizado) ||
                        autores.some(autor => autor.includes(terminoNormalizado));
                });
                // Si no hay libros filtrados, muestra mensaje de no resultados.
                if (!filtrados.length) {
                    listaResultados.innerHTML = `<div class="noResultados">No se encontraron resultados para "${escaparHtml(termino)}".</div>`;
                    return;
                }
                // Crea y añade los elementos de libro filtrados al DOM.
                const fragment = document.createDocumentFragment();
                filtrados.forEach(libro => {
                    fragment.appendChild(crearElementoLibro(libro));
                });
                listaResultados.appendChild(fragment);
            });
    };

    // Evento input con debounce: espera 300ms tras dejar de escribir para buscar.
    if (campoBusqueda) {
        campoBusqueda.addEventListener('input', () => {
            clearTimeout(debounceTimeout); // Limpia el timeout anterior si el usuario sigue escribiendo.
            const terminoDeBusqueda = campoBusqueda.value.trim();
            // Espera 300ms después de que el usuario deja de escribir antes de buscar.
            debounceTimeout = setTimeout(() => buscarLibros(terminoDeBusqueda), 300);
        });
    }
});