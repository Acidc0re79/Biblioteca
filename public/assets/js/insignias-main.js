// Se ejecuta cuando todo el contenido de la página se ha cargado.
document.addEventListener('DOMContentLoaded', function() {

    // Obtener una referencia al elemento del modal de Bootstrap.
    const viewInsigniaModalElement = document.getElementById('viewInsigniaModal');

    // Asegurarnos de que el modal existe en la página antes de añadirle eventos.
    if (viewInsigniaModalElement) {
        
        // Escuchamos el evento 'show.bs.modal', que se dispara justo ANTES de que el modal se haga visible.
        viewInsigniaModalElement.addEventListener('show.bs.modal', function(event) {
            
            // 1. IDENTIFICAR QUÉ INSIGNIA SE CLICKEÓ
            // 'event.relatedTarget' es el elemento que disparó el modal (en nuestro caso, el div de la insignia).
            const insigniaCard = event.relatedTarget;

            // 2. EXTRAER LA INFORMACIÓN DE LA INSIGNIA
            // Obtenemos todos los datos que guardamos en los atributos 'data-*' de la insignia.
            const nombre = insigniaCard.dataset.nombre;
            const descripcion = insigniaCard.dataset.descripcion;
            const imagenUrl = insigniaCard.dataset.imagenUrl;
            // Convertimos el string 'true'/'false' a un verdadero booleano.
            const ganada = insigniaCard.dataset.ganada === 'true'; 

            // 3. SELECCIONAR LOS ELEMENTOS DENTRO DEL MODAL
            // Obtenemos las referencias a los elementos del modal que vamos a rellenar.
            const modalTitulo = document.getElementById('insignia-modal-nombre');
            const modalImagen = document.getElementById('insignia-modal-imagen');
            const modalDescripcion = document.getElementById('insignia-modal-descripcion');
            const modalFooterContent = document.getElementById('insignia-modal-footer-content');

            // 4. RELLENAR EL MODAL CON LA INFORMACIÓN
            modalTitulo.textContent = nombre;
            modalImagen.src = imagenUrl;
            modalDescripcion.textContent = descripcion;

            // 5. LÓGICA CONDICIONAL PARA EL BOTÓN DE DESCARGA
            // Limpiamos el contenido anterior del pie de página.
            modalFooterContent.innerHTML = ''; 

            if (ganada) {
                // Si la insignia fue ganada, creamos un botón de descarga.
                const downloadButton = document.createElement('a');
                downloadButton.href = imagenUrl.replace('/thumbs/', '/'); // Asegurarnos de descargar la imagen original.
                downloadButton.textContent = 'Descargar';
                downloadButton.className = 'btn btn-primary';
                downloadButton.setAttribute('download', nombre.replace(/\s+/g, '_') + '.png'); // ej: 'Mi_Logro.png'
                
                // Añadimos el botón de descarga al pie del modal.
                modalFooterContent.appendChild(downloadButton);
            }

            // Siempre añadimos el botón de "Cerrar".
            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'btn btn-secondary';
            closeButton.dataset.bsDismiss = 'modal';
            closeButton.textContent = 'Cerrar';
            modalFooterContent.appendChild(closeButton);
        });
    }
});