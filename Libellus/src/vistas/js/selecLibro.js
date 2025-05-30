// Script para manejar la selección de un libro en la lista y navegar a su área de detalle.

// Selecciona todos los elementos <article> con la clase 'datosLibro' (cada tarjeta de libro)
document.querySelectorAll('article.datosLibro').forEach(articulo => {
    // Añade un listener de click a cada libro
    articulo.addEventListener('click', function () {
        // Obtiene el ID del libro desde el atributo data-idlibro del artículo
        const idLibro = articulo.dataset.idlibro;

        // Verifica que el ID exista y no sea una cadena vacía o solo espacios.
        if (idLibro && idLibro.trim() !== '') {
            // Si el ID es válido, navega a la página de detalle del libro usando el ID en la URL
            window.location.href = `AreaLibro.php?libro=${idLibro}`;
        } else {
            // Si el ID no es válido, muestra una advertencia en consola
            console.warn('El libro seleccionado no tiene un ID válido.', articulo);
        }
    });
});