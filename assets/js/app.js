document.addEventListener('DOMContentLoaded', function () {
    // 1. Manejo de selección en Evaluaciones (Imagen 4)
    initEvaluacionOptions();

    // 2. Vista previa en carga de multimedia (Profesor)
    initMediaUploadPreview();

    // 3. Integración con API externa (YouTube oEmbed)
    initOEmbedPreview();

    // 4. Búsqueda en vivo (Diccionario de señas)
    initLiveSearch();
});

/**
 * Resalta visualmente las tarjetas de opción A, B, C, D al hacer clic (Imagen 4)
 */
function initEvaluacionOptions() {
    const optionBoxes = document.querySelectorAll('.option-box');
    const radios = document.querySelectorAll('.option-radio');

    if (radios.length === 0) return;

    radios.forEach(radio => {
        // Inicializar estado previo
        if (radio.checked) {
            const box = radio.closest('.option-box');
            if (box) box.classList.add('selected');
        }

        radio.addEventListener('change', function () {
            optionBoxes.forEach(b => b.classList.remove('selected'));
            if (this.checked) {
                const box = this.closest('.option-box');
                if (box) box.classList.add('selected');
            }
        });
    });

    optionBoxes.forEach(box => {
        box.addEventListener('click', function () {
            const radio = this.querySelector('.option-radio');
            if (radio && !radio.checked) {
                radio.checked = true;
                optionBoxes.forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
            }
        });
    });
}

/**
 * Previsualización de archivos antes de subir (Profesor)
 */
function initMediaUploadPreview() {
    const inputArchivo = document.getElementById('archivo');
    if (!inputArchivo) return;

    inputArchivo.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const maxSize = Number(inputArchivo.dataset.maxBytes || 0);

        if (maxSize > 0 && file.size > maxSize) {
            alert(`El archivo seleccionado excede el tamaño máximo permitido (${Math.floor(maxSize / 1024 / 1024)} MB).`);
            this.value = '';
            return;
        }
    });
}

/**
 * Integración con API externa YouTube oEmbed (Sección 9.7)
 */
function initOEmbedPreview() {
    const inputEnlace = document.getElementById('enlace_externo');
    if (!inputEnlace) return;

    let timeoutId;
    inputEnlace.addEventListener('input', function () {
        clearTimeout(timeoutId);
        const url = this.value.trim();

        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            timeoutId = setTimeout(() => {
                fetch(`index.php?c=api&a=oembed&url=${encodeURIComponent(url)}`)
                    .then(res => res.json())
                    .then(data => {
                        let previewContainer = document.getElementById('oembed-preview');
                        if (!previewContainer) {
                            previewContainer = document.createElement('div');
                            previewContainer.id = 'oembed-preview';
                            previewContainer.className = 'mt-3 p-3 bg-light border rounded text-center';
                            inputEnlace.parentNode.appendChild(previewContainer);
                        }

                        if (data.title) {
                            previewContainer.innerHTML = `
                                <div class="text-success small mb-2"><i class="bi bi-check-circle-fill"></i> Video verificado vía API externa (oEmbed)</div>
                                <strong>${data.title}</strong>
                                <div class="mt-2 text-muted small">Autor: ${data.author_name || 'YouTube'}</div>
                            `;
                        }
                    })
                    .catch(err => {
                        console.warn('Error al consultar oEmbed:', err);
                    });
            }, 600);
        }
    });
}

/**
 * Búsqueda interactiva en vivo mediante API propia (GET /api/senas?q=...)
 */
function initLiveSearch() {
    const inputBuscar = document.getElementById('buscar-sena');
    const resultadosBox = document.getElementById('resultados-busqueda');

    if (!inputBuscar || !resultadosBox) return;

    let debounce;
    inputBuscar.addEventListener('input', function () {
        clearTimeout(debounce);
        const query = this.value.trim();

        if (query.length < 2) {
            resultadosBox.innerHTML = '';
            resultadosBox.classList.add('d-none');
            return;
        }

        debounce = setTimeout(() => {
            fetch(`index.php?c=api&a=senas&q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success' && response.data.length > 0) {
                        let html = '<ul class="list-group shadow-sm">';
                        response.data.forEach(item => {
                            html += `
                                <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${item.titulo}</strong>
                                        <small class="text-muted d-block">${item.curso}</small>
                                    </div>
                                    <a href="index.php?c=leccion&a=ver&id=${item.id_leccion}" class="btn btn-sm btn-outline-primary">Ver seña</a>
                                </li>
                            `;
                        });
                        html += '</ul>';
                        resultadosBox.innerHTML = html;
                        resultadosBox.classList.remove('d-none');
                    } else {
                        resultadosBox.innerHTML = '<div class="p-2 text-muted small bg-light border">No se encontraron señas con esa palabra.</div>';
                        resultadosBox.classList.remove('d-none');
                    }
                })
                .catch(err => console.error('Error en búsqueda de señas:', err));
        }, 300);
    });
}

/**
 * Marca o desmarca una lección como completada sin recargar la página (POST /api/progreso)
 */
function toggleCompletarLeccion(idLeccion, boton) {
    fetch('index.php?c=api&a=progreso', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_leccion: idLeccion, completada: 1 })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            boton.outerHTML = '<span class="badge bg-success p-2 fs-6"><i class="bi bi-check-circle-fill"></i> Completada</span>';
        }
    })
    .catch(e => console.error(e));
}
