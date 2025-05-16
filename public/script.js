document.addEventListener('DOMContentLoaded', function() {

    // --- Lógica para Subtareas en index.html ---

    // Función para actualizar el contador de subtareas completadas
    function updateSubtaskCount(subtaskListElement) {
        if (!subtaskListElement) return;
        const container = subtaskListElement.closest('.subtasks-section');
        if (!container) return;

        const checkboxes = subtaskListElement.querySelectorAll('.subtask-checkbox');
        const completedCount = subtaskListElement.querySelectorAll('.subtask-checkbox:checked').length;
        const totalCount = checkboxes.length;

        const completedSpan = container.querySelector('.subtask-completed-count');
        const totalSpan = container.querySelector('.subtask-total-count');

        if (completedSpan) completedSpan.textContent = completedCount;
        if (totalSpan) totalSpan.textContent = totalCount;
    }

    // Añadir event listener a todos los checkboxes de subtareas existentes
    document.querySelectorAll('.subtask-checkbox').forEach(checkbox => {
        // Aplicar estilo inicial si ya está chequeado
        const label = checkbox.nextElementSibling;
        if (checkbox.checked && label && label.classList.contains('subtask-label')) {
            label.style.textDecoration = 'line-through';
            label.style.color = 'var(--bs-secondary)';
            label.style.opacity = '0.8';
        } else if (label && label.classList.contains('subtask-label')) {
             label.style.textDecoration = 'none';
             label.style.color = 'var(--bs-body-color)';
             label.style.opacity = '1';
        }


        checkbox.addEventListener('change', function() {
            const label = this.nextElementSibling; // El label es el siguiente hermano
            if (label && label.classList.contains('subtask-label')) {
                if (this.checked) {
                    label.style.textDecoration = 'line-through';
                    label.style.color = 'var(--bs-secondary)';
                    label.style.opacity = '0.8';
                } else {
                    label.style.textDecoration = 'none';
                    label.style.color = 'var(--bs-body-color)';
                     label.style.opacity = '1';
                }
            }
             // Actualizar contador al cambiar estado
            const subtaskList = this.closest('.subtask-list');
            updateSubtaskCount(subtaskList);

             // Aquí deberías añadir la lógica para enviar el cambio de estado al backend
             console.log(`Subtarea ${this.id} marcada como: ${this.checked}`);
        });

        // Actualizar contadores iniciales para cada lista
        const subtaskList = checkbox.closest('.subtask-list');
        if (subtaskList) {
           updateSubtaskCount(subtaskList); // Llama una vez por lista encontrada
        }
    });


    


    // --- Lógica para desplegable de Subtareas en index.html (Icono) ---
     document.querySelectorAll('.subtasks-toggle').forEach(toggle => {
        const collapseElement = document.getElementById(toggle.getAttribute('href').substring(1));
        const icon = toggle.querySelector('.bi-chevron-down, .bi-chevron-up');

        if (collapseElement && icon) {
             // Estado inicial del icono basado en si el collapse está visible
            if (collapseElement.classList.contains('show')) {
                 icon.classList.remove('bi-chevron-down');
                 icon.classList.add('bi-chevron-up');
            } else {
                 icon.classList.remove('bi-chevron-up');
                 icon.classList.add('bi-chevron-down');
            }

            collapseElement.addEventListener('show.bs.collapse', () => {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            });

            collapseElement.addEventListener('hide.bs.collapse', () => {
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            });
        }
    });

}); // Fin DOMContentLoaded