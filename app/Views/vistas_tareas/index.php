<?php
// Inicia la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica si la sesión 'usuario' está definida
if (isset($_SESSION['usuario'])):
    // El usuario ha iniciado sesión, muestra el contenido de la página
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de Tareas</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="<?= base_url('style.css') ?>">
    </head>

    <body>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>
        <div class="container-fluid mt-4">
            <header class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom gap-2">
                <h1 class="h3 mb-0 me-md-auto">Gestión de Tareas</h1>

                <div class="d-flex align-items-center">
                    <a href="<?= base_url('controlador_tareas/tareas/nueva_tarea') ?>" class="btn btn-primary me-3" id="boton-negro">
                        <i class="bi bi-plus-lg"></i> Nueva Tarea
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" id="dropdownMenuUsuario" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-fill me-2"></i>
                            <?= $_SESSION['usuario'] ?> </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuUsuario">

                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#notificacionesModal">
                                    <i class="bi bi-bell-fill me-2"></i>Notificaciones
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="<?= base_url('controlador_tareas/login/logout') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <section class="filters-section card card-body mb-4">
                <h5 class="card-title mb-3">Filtros</h5>
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <?= form_open(base_url('controlador_tareas/tareas/filtro_estado'), ['method' => 'get', 'class' => 'form-inline']) ?>
                        <div class="form-group">
                            <label for="estado" class="form-label">Estado</label>
                            <?= form_dropdown('estado', [
                                'todas' => 'Seleccionar Estado',
                                'definida' => 'Definida',
                                'en_progreso' => 'En Progreso',
                                'completada' => 'Completado'
                            ], '', ['class' => 'form-select', 'id' => 'estado', 'onchange' => 'this.form.submit()']) ?>
                        </div>
                        <?= form_close() ?>
                    </div>
                    <!-- Formulario para filtrar por prioridad -->
                    <div class="col-md-3">
                        <?= form_open(base_url('controlador_tareas/tareas/filtro_prioridad'), ['method' => 'get', 'class' => 'form-inline']) ?>
                        <div class="form-group">
                            <label for="prioridad" class="form-label">Prioridad</label>
                            <?= form_dropdown('prioridad', [
                                'todas' => 'Seleccionar Prioridad',
                                'alta' => 'Alta',
                                'media' => 'Media',
                                'baja' => 'Baja'
                            ], '', ['class' => 'form-select', 'id' => 'prioridad', 'onchange' => 'this.form.submit()']) ?>
                        </div>
                        <?= form_close() ?>
                    </div>
                    <!-- Formulario para filtrar por tareas archivadas -->
                    <div class="col-md-3">
                        <?= form_open(base_url('controlador_tareas/tareas/filtro_archivar'), ['method' => 'get', 'class' => 'form-inline']) ?>
                        <div class="form-group">
                            <label for="archivadas" class="form-label">Tareas Archivadas</label>
                            <?= form_dropdown('estado_archivado', [
                                '' => 'Seleccionar',
                                'archivada' => 'Archivadas',
                                'no_archivada' => 'No Archivadas'
                            ], '', ['class' => 'form-select', 'id' => 'archivadas', 'onchange' => 'this.form.submit()']) ?>
                        </div>
                        <?= form_close() ?>
                    </div>
                    <div class="col-md-3">
                        <?= form_open(base_url('controlador_tareas/tareas/ordenar_tareas'), ['method' => 'get', 'class' => 'form-inline']) ?>
                        <div class="form-group">
                            <label for="ordenar" class="form-label">Ordenar Por</label>
                            <?= form_dropdown('criterio', [
                                '' => 'Seleccionar Criterio',
                                'prioridad' => 'Prioridad',
                                'fecha_vencimiento' => 'Fecha de Vencimiento',
                                'estado' => 'Estado',
                                'fecha_creacion' => 'Fecha de Creación',
                                'mis_tareas' => 'Mis Tareas',
                                'colaborador' => 'Tareas como Colaborador'
                            ], '', ['class' => 'form-select', 'id' => 'ordenar', 'onchange' => 'this.form.submit()']) ?>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </section>

            <section class="task-list row g-4">
                <?php if (!empty($tareas) && is_array($tareas)): ?>
                    <?php foreach ($tareas as $tarea): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card task-card" style="border-left-color: <?= esc($tarea['color']) ?>;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <a href="<?= base_url('controlador_tareas/tareas/editar_tarea/' . $tarea['id_tarea']) ?>" class="text-decoration-none text-dark stretched-link-task">
                                                <?= esc($tarea['asunto']) ?>
                                            </a>
                                        </h5>
                                        <div class="dropdown task-options">
                                            <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a href="<?= base_url('controlador_tareas/tareas/compartir?id_tarea=' . $tarea['id_tarea']) ?>"
                                                        class="dropdown-item">
                                                        <i class="bi bi-person-plus me-2"></i>Compartir
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="<?= base_url('controlador_tareas/tareas/accion_tarea') ?>" method="post" class="form-accion-tarea">
                                                        <input type="hidden" name="id_tarea" value="<?= esc($tarea['id_tarea']) ?>">

                                                        <button type="submit"
                                                            name="accion"
                                                            value="eliminar"
                                                            class="dropdown-item text-danger"
                                                            onclick="return confirm('¿Seguro que deseas eliminar esta tarea?');">
                                                            <i class="bi bi-trash me-2"></i>Eliminar
                                                        </button>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <button type="submit"
                                                        name="accion"
                                                        value="archivar"
                                                        class="dropdown-item"
                                                        onclick="return confirm('¿Seguro que deseas archivar esta tarea?');">
                                                        <i class="bi bi-archive me-2"></i>Archivar
                                                    </button>
                                                </li>
                                                </form>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <span class="badge bg-<?= $tarea['prioridad'] === 'alta' ? 'danger' : ($tarea['prioridad'] === 'normal' ? 'primary' : 'success') ?> me-2">
                                            <?= esc($tarea['prioridad']) ?>
                                        </span>
                                        <span class="badge bg-<?= $tarea['estado'] === 'completada' ? 'success' : ($tarea['estado'] === 'en_progreso' ? 'warning' : ($tarea['estado'] === 'definida' ? 'info' : 'secondary')) ?>"> <?= esc($tarea['estado']) ?></span>

                                    </div>
                                    <p class="card-text text-muted mb-2"><?= esc($tarea['descripcion']) ?></p>
                                    <div class="task-details mb-3">
                                        <p class="mb-1"><i class="bi bi-calendar-check me-2 text-danger"></i> Vence: <?= date('d M Y', strtotime($tarea['fecha_vencimiento'])) ?></p>
                                        <p class="mb-1"><i class="bi bi-bell me-2 text-warning"></i> Recordatorio: <?= date('d M Y', strtotime($tarea['fecha_recordatorio'])) ?></p>
                                        <?php
                                        /*
                         <p class="mb-1"><i class="bi bi-people me-2 text-secondary"></i> <?= esc($tarea['colaboradores']) ?> colaborador(es)</p>
                        */
                                        ?>
                                    </div>

                                    <?php if (!empty($tarea['subtareas']) && is_array($tarea['subtareas'])): ?>

                                        <div class="subtasks-section">
                                            <a class="subtasks-toggle d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#subtareas<?= esc($tarea['id_tarea']) ?>" role="button" aria-expanded="false" aria-controls="subtareas<?= esc($tarea['id_tarea']) ?>">
                                                <span>
                                                    <i class="bi bi-list-task me-1"></i> Subtareas (
                                                    <span class="subtask-completed-count"><?= esc($tarea['subtareas_completadas']) ?></span>/
                                                    <span class="subtask-total-count"><?= esc($tarea['total_subtareas']) ?></span>)
                                                </span>
                                                <i class="bi bi-chevron-down"></i>
                                            </a>
                                            <div class="collapse mt-2" id="subtareas<?= esc($tarea['id_tarea']) ?>">
                                                <div class="list-group list-group-flush subtask-list">
                                                    <?php foreach ($tarea['subtareas'] as $subtarea): ?>
                                                        <div class="list-group-item d-flex align-items-center ps-0 border-0">
                                                            <div class="form-check">
                                                                <input class="form-check-input subtask-checkbox" type="checkbox" value="" id="sub<?= esc($subtarea['id_subtarea']) ?>" <?= $subtarea['estado'] === 'completada' ? 'checked' : '' ?>>
                                                                <label class="form-check-label subtask-label" for="sub<?= esc($subtarea['id_subtarea']) ?>">
                                                                    <?= esc($subtarea['nombre']) ?>
                                                                    <span class="badge bg-<?= $subtarea['prioridad'] === 'alta' ? 'danger' : ($subtarea['prioridad'] === 'normal' ? 'primary' : ($subtarea['prioridad'] === 'baja' ? 'success' : 'secondary')) ?> me-2">
                                                                        <?= esc($subtarea['prioridad']) ?>
                                                                    </span>
                                                                    <span class="badge bg-<?= $subtarea['estado'] === 'completada' ? 'success' : ($subtarea['estado'] === 'en_progreso' ? 'warning' : ($subtarea['estado'] === 'definida' ? 'info' : 'secondary')) ?>"> <?= esc($subtarea['estado']) ?></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="subtasks-section">
                                            <span class="text-muted small"><i class="bi bi-list-task me-1"></i> Sin subtareas</span>
                                        </div>
                                    <?php endif; ?>

                                </div>
                                 <div class="card-footer text-muted" style="--task-color: <?= esc($tarea['color']) ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No hay tareas disponibles.</p>
                <?php endif; ?>
            </section>
        </div>
        <!-- Modal para compartir tarea -->
        <?php if (!empty($abrir_modal) && !empty($id_tarea_modal)): ?>
            <div class="modal fade" id="compartirModal" tabindex="-1" aria-labelledby="compartirModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="compartirModalLabel">Compartir Tarea</h5>
                            <a href="<?= base_url('controlador_tareas/tareas') ?>" class="btn-close"></a>
                        </div>
                        <div class="modal-body">
                            <form action="<?= base_url('controlador_tareas/tareas/agregar_colaborador') ?>" method="post">
                                <input type="hidden" name="id_tarea" value="<?= esc($id_tarea_modal) ?>">
                                <div class="mb-3">
                                    <label for="compartirEmail" class="form-label">Compartir con (email):</label>
                                    <input type="email" class="form-control" name="correo" id="compartirEmail" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Confirmar</button>
                            </form>
                            <hr>
                            <h6>Lista de Colaboradores</h6>
                            <ul class="list-group">

                                <?php if (!empty($colaboradores) && is_array($colaboradores)): ?>
                                    <?php foreach ($colaboradores as $colaborador): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= esc($colaborador['correo']) ?>
                                        <form action="<?= base_url('controlador_tareas/tareas/eliminar_colaborador') ?>" method="post" style="display:inline;">
                                            <input type="hidden" name="id_tarea" value="<?= esc($id_tarea_modal) ?>">
                                            <input type="hidden" name="correo" value="<?= esc($colaborador['correo']) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </form>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="list-group-item text-muted">No hay colaboradores asignados.</li>
                                <?php endif; ?>
                            </ul>

                        </div>
                        <div class="modal-footer">


                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = function() {
                    var modal = document.getElementById('compartirModal');
                    if (modal) {
                        modal.classList.add('show');
                        modal.style.display = 'block';
                        modal.setAttribute('aria-modal', 'true');
                        modal.removeAttribute('aria-hidden');
                    }
                }
            </script>
        <?php endif; ?>
        <div class="modal fade" id="notificacionesModal" tabindex="-1" aria-labelledby="notificacionesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notificacionesModalLabel"><i class="bi bi-bell-fill me-2"></i>Notificaciones</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group list-group-flush">

                            <div class="list-group-item list-group-item-action alert alert-danger-subtle notification-item" aria-current="true">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>Vencimiento Próximo</h6>
                                    <small class="text-muted">Hace 5 min</small>
                                </div>
                                <p class="mb-1 small">La tarea "<strong>Preparar presentación para cliente</strong>" vence mañana (20 may 2025).</p>
                                <a href="editar-tarea.html?id=2" class="btn btn-sm btn-outline-danger mt-1">Ver Tarea</a>
                            </div>

                            <div class="list-group-item list-group-item-action notification-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tarea Compartida</h6>
                                    <small class="text-muted">Hace 1 hora</small>
                                </div>
                                <p class="mb-1 small">El usuario '<strong>colaborador@ejemplo.com</strong>' te ha compartido la tarea "<strong>Revisar Informe Trimestral</strong>".</p>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-success me-2 btn-accept-notification" data-notification-id="notif123">Aceptar</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-reject-notification" data-notification-id="notif123">Rechazar</button>
                                </div>
                            </div>

                            <div class="list-group-item list-group-item-action notification-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="bi bi-info-circle-fill me-2 text-info"></i>Actualización</h6>
                                    <small class="text-muted">Ayer</small>
                                </div>
                                <p class="mb-1 small">Se ha completado la subtarea "<strong>Escribir manual usuario</strong>" en la tarea "<strong>Completar documentación del proyecto</strong>".</p>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary me-auto" id="btnMarcarLeidas">Marcar todas como leídas</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script src="<?= base_url('script.js') ?>"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Listener para botones Aceptar/Rechazar (simulación)
                document.querySelectorAll('.btn-accept-notification, .btn-reject-notification').forEach(button => {
                    button.addEventListener('click', function(e) {
                        const notificationId = this.getAttribute('data-notification-id');
                        const action = this.classList.contains('btn-accept-notification') ? 'aceptada' : 'rechazada';

                        console.log(`Notificación ${notificationId} ${action}.`);
                        // Aquí iría la llamada al backend para registrar la acción

                        // Opcional: Deshabilitar botones o cambiar estilo después de la acción
                        const parentItem = this.closest('.notification-item');
                        if (parentItem) {
                            parentItem.style.opacity = '0.6'; // Atenuar la notificación
                            parentItem.querySelectorAll('button').forEach(btn => btn.disabled = true);
                        }

                        // Evitar que se cierre el modal si se hace click dentro (si es necesario)
                        // e.stopPropagation();
                    });
                });

                // Listener para "Marcar todas como leídas" (simulación)
                const btnMarcarLeidas = document.getElementById('btnMarcarLeidas');
                if (btnMarcarLeidas) {
                    btnMarcarLeidas.addEventListener('click', function() {
                        console.log('Marcando todas las notificaciones como leídas...');
                        // Aquí iría la llamada al backend

                        // Opcional: Actualizar UI (ej: quitar badges, atenuar todas)
                        document.querySelectorAll('#notificacionesModal .notification-item').forEach(item => {
                            item.style.opacity = '0.6';
                        });
                        alert('Todas las notifications marcadas como leídas (simulación).');
                    });
                }
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Listener para eliminar colaborador
                document.querySelectorAll('.btn-eliminar-colaborador').forEach(button => {
                    button.addEventListener('click', function() {
                        const colaboradorId = this.getAttribute('data-colaborador-id');
                        console.log(`Eliminando colaborador con ID: ${colaboradorId}`);

                        // Aquí puedes hacer una llamada AJAX para eliminar el colaborador
                        // Ejemplo:
                        /*
                        fetch(`/controlador_tareas/colaboradores/eliminar/${colaboradorId}`, {
                            method: 'DELETE'
                        }).then(response => {
                            if (response.ok) {
                                alert('Colaborador eliminado correctamente.');
                                location.reload(); // Recargar la página para actualizar la lista
                            } else {
                                alert('Error al eliminar el colaborador.');
                            }
                        });
                        */
                    });
                });
            });
        </script>
        <script>
            const BASE_URL = "<?= base_url() ?>";
        </script>

        <script>
            // --- Lógica para Modal Compartir en index.html ---
            const compartirModal = document.getElementById('compartirModal');
            if (compartirModal) {
                compartirModal.addEventListener('show.bs.modal', function(event) {
                    // Botón que activó el modal
                    const button = event.relatedTarget;
                    // Extraer info desde data-bs-* attributes
                    const taskId = button.getAttribute('data-task-id');
                    // Actualizar el contenido del modal (si es necesario, como poner el ID en un campo oculto)
                    document.getElementById('compartirTaskId').value = taskId;

                    // Almacena el ID de la tarea en la sesión mediante una solicitud AJAX



                });

                const btnConfirmarCompartir = document.getElementById('btnConfirmarCompartir');
                if (btnConfirmarCompartir) {
                    btnConfirmarCompartir.addEventListener('click', function() {
                        const email = document.getElementById('compartirEmail').value;
                        const taskId = document.getElementById('compartirTaskId').value;
                        if (email && taskId) {
                            // Aquí iría la lógica para enviar la petición de compartir al backend
                            console.log(`Compartir tarea ${taskId} con ${email}`);
                            // Cerrar el modal (opcional, o esperar respuesta del backend)
                            const modalInstance = bootstrap.Modal.getInstance(compartirModal);
                            modalInstance.hide();
                            alert(`Tarea ${taskId} compartida con ${email} (simulación)`); // Feedback temporal
                            document.getElementById('compartirEmail').value = ''; // Limpiar campo
                        } else {
                            alert("Por favor, introduce un correo electrónico.");
                        }
                    });
                }
            }
        </script>


    </body>

    </html>


<?php
else:
    // El usuario no ha iniciado sesión, redirige a la página de inicio de sesión
    header('Location: ' . base_url('controlador_tareas/login'));
    exit(); // Asegura que el script se detenga después de la redirección
endif;
?>