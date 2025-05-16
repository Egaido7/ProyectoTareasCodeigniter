<?php
// CodeIgniter 4 maneja la sesión automáticamente si está configurada.
// No es necesario session_start() manual si usas el servicio de sesión de CI4.

// Verifica si la sesión 'usuario' está definida usando el helper de sesión de CI4
if (session()->has('usuario')):
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
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>
        
        <?php /* Mensajes específicos para colaboradores de subtareas */ ?>
        <?php if (session()->getFlashdata('success_subcolab')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= esc(session()->getFlashdata('success_subcolab')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error_subcolab')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= esc(session()->getFlashdata('error_subcolab')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                            <?= esc(session()->get('usuario')) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuUsuario">
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#notificacionesModal">
                                    <i class="bi bi-bell-fill me-2"></i>Notificaciones
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
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
                            ], old('estado', 'todas'), ['class' => 'form-select', 'id' => 'estado', 'onchange' => 'this.form.submit()']) ?>
                        </div>
                        <?= form_close() ?>
                    </div>
                    <div class="col-md-3">
                        <?= form_open(base_url('controlador_tareas/tareas/filtro_prioridad'), ['method' => 'get', 'class' => 'form-inline']) ?>
                        <div class="form-group">
                            <label for="prioridad" class="form-label">Prioridad</label>
                            <?= form_dropdown('prioridad', [
                                'todas' => 'Seleccionar Prioridad',
                                'alta' => 'Alta',
                                'media' => 'Media',
                                'baja' => 'Baja'
                            ], old('prioridad', 'todas'), ['class' => 'form-select', 'id' => 'prioridad', 'onchange' => 'this.form.submit()']) ?>
                        </div>
                        <?= form_close() ?>
                    </div>
                    <div class="col-md-3">
                        <?= form_open(base_url('controlador_tareas/tareas/filtro_archivar'), ['method' => 'get', 'class' => 'form-inline']) ?>
                        <div class="form-group">
                            <label for="archivadas" class="form-label">Tareas Archivadas</label>
                            <?= form_dropdown('estado_archivado', [
                                '' => 'Seleccionar',
                                'archivada' => 'Archivadas',
                                'no_archivada' => 'No Archivadas'
                            ], old('estado_archivado', ''), ['class' => 'form-select', 'id' => 'archivadas', 'onchange' => 'this.form.submit()']) ?>
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
                            ], old('criterio', ''), ['class' => 'form-select', 'id' => 'ordenar', 'onchange' => 'this.form.submit()']) ?>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </section>

            <section class="task-list row g-4">
                <?php if (!empty($tareas) && is_array($tareas)): ?>
                    <?php foreach ($tareas as $tarea): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card task-card" style="border-left-color: <?= esc($tarea['color'] ?? '#6c757d', 'attr') ?>;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <a href="<?= base_url('controlador_tareas/tareas/editar_tarea/' . $tarea['id_tarea']) ?>" >
                                                <?= esc($tarea['asunto']) ?>
                                            </a>
                                            
                                        </h5>
                                        <div class="dropdown task-options">
                                            <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                               
                                                <form action="<?= base_url('controlador_tareas/tareas/accion_tarea') ?>" method="post" class="form-accion-tarea">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id_tarea" value="<?= esc($tarea['id_tarea']) ?>">
                                                    <li>
                                                        <button type="submit" name="accion" value="editar" class="dropdown-item">
                                                            <i class="bi bi-pencil me-2"></i>Editar Tarea
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <a href="<?= base_url('controlador_tareas/tareas/compartir?id_tarea=' . $tarea['id_tarea']) ?>" class="dropdown-item">
                                                            <i class="bi bi-person-plus me-2"></i>Compartir Tarea
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button type="submit" name="accion" value="eliminar" class="dropdown-item text-danger" onclick="return confirm('¿Seguro que deseas eliminar esta tarea?');">
                                                            <i class="bi bi-trash me-2"></i>Eliminar Tarea
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button type="submit" name="accion" value="archivar" class="dropdown-item" onclick="return confirm('¿Seguro que deseas archivar esta tarea?');">
                                                            <i class="bi bi-archive me-2"></i>Archivar Tarea
                                                        </button>
                                                    </li>
                                                </form>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <span class="badge bg-<?= ($tarea['prioridad'] ?? 'normal') === 'alta' ? 'danger' : (($tarea['prioridad'] ?? 'normal') === 'media' ? 'warning' : 'success') ?> me-2">
                                            <?= esc(ucfirst($tarea['prioridad'] ?? 'normal')) ?>
                                        </span>
                                        <span class="badge bg-<?= ($tarea['estado'] ?? 'definida') === 'completada' ? 'success' : (($tarea['estado'] ?? 'definida') === 'en_progreso' ? 'warning' : (($tarea['estado'] ?? 'definida') === 'definida' ? 'info' : 'secondary')) ?>"> 
                                            <?= esc(str_replace('_', ' ', ucfirst($tarea['estado'] ?? 'definida'))) ?>
                                        </span>
                                    </div>
                                    <p class="card-text text-muted mb-2 task-description"><?= esc($tarea['descripcion']) ?></p>
                                    <div class="task-details mb-3">
                                        <p class="mb-1"><i class="bi bi-calendar-check me-2 text-danger"></i> Vence: <?= esc(date('d M Y', strtotime($tarea['fecha_vencimiento']))) ?></p>
                                        <p class="mb-1"><i class="bi bi-bell me-2 text-warning"></i> Recordatorio: <?= esc(date('d M Y', strtotime($tarea['fecha_recordatorio']))) ?></p>
                                    </div>

                                    <?php if (!empty($tarea['subtareas']) && is_array($tarea['subtareas'])): ?>
                                        <div class="subtasks-section">
                                            <a class="subtasks-toggle d-flex justify-content-between align-items-center text-decoration-none" data-bs-toggle="collapse" href="#subtareas<?= esc($tarea['id_tarea']) ?>" role="button" aria-expanded="false" aria-controls="subtareas<?= esc($tarea['id_tarea']) ?>">
                                                <span>
                                                    <i class="bi bi-list-task me-1"></i> Subtareas (
                                                    <span class="subtask-completed-count"><?= esc($tarea['subtareas_completadas'] ?? 0) ?></span>/
                                                    <span class="subtask-total-count"><?= esc($tarea['total_subtareas'] ?? 0) ?></span>)
                                                </span>
                                                <i class="bi bi-chevron-down"></i>
                                            </a>
                                            <div class="collapse mt-2" id="subtareas<?= esc($tarea['id_tarea']) ?>">
                                                <div class="list-group list-group-flush subtask-list">
                                                    <?php foreach ($tarea['subtareas'] as $subtarea): ?>
                                                        <div class="list-group-item d-flex justify-content-between align-items-center ps-0 border-0 py-1">
                                                            <div class="form-check flex-grow-1">
                                                                <input class="form-check-input subtask-checkbox" type="checkbox" 
                                                                       value="" 
                                                                       id="sub<?= esc($subtarea['id_subtarea']) ?>" 
                                                                       <?= ($subtarea['estado'] ?? 'pendiente') === 'completada' ? 'checked' : '' ?>
                                                                       onchange="window.location.href='<?= site_url('controlador_tareas/subtareas/tachar_subtarea/' . esc($subtarea['id_subtarea']) . '/' . esc($tarea['id_tarea']) . '/' . (($subtarea['estado'] ?? 'pendiente') === 'completada' ? 'pendiente' : 'completada')) ?>'">
                                                                <label class="form-check-label subtask-label <?= ($subtarea['estado'] ?? 'pendiente') === 'completada' ? 'text-decoration-line-through text-muted' : '' ?>" for="sub<?= esc($subtarea['id_subtarea']) ?>">
                                                                    <?= esc($subtarea['nombre']) ?>
                                                                    <?php if(!empty($subtarea['fecha_vencimiento'])): ?>
                                                                        <small class="text-muted">(Vence: <?= esc(date('d/m/Y', strtotime($subtarea['fecha_vencimiento']))) ?>)</small>
                                                                    <?php endif; ?>
                                                                     <span class="badge bg-<?= ($subtarea['prioridad'] ?? 'normal') === 'alta' ? 'danger' : (($subtarea['prioridad'] ?? 'normal') === 'media' ? 'warning' : 'success') ?> ms-1">
                                                                        <small><?= esc(ucfirst($subtarea['prioridad'] ?? 'normal')) ?></small>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <div class="dropdown subtask-options ms-2">
                                                                <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opciones de subtarea">
                                                                    <i class="bi bi-three-dots-vertical"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li>
                                                                        <a href="<?= site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . esc($subtarea['id_subtarea']) . '&id_tarea=' . esc($tarea['id_tarea'])) ?>"
                                                                           class="dropdown-item">
                                                                            <i class="bi bi-person-plus me-2"></i>Colaboradores
                                                                        </a>
                                                                    </li>
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li>
                                                                        <form action="<?= site_url('controlador_tareas/subtareas/eliminar_subtarea') ?>" method="post" class="form-accion-subtarea d-inline">
                                                                            <?= csrf_field() ?>
                                                                            <input type="hidden" name="id_tarea" value="<?= esc($tarea['id_tarea']) ?>">
                                                                            <input type="hidden" name="id_subtarea" value="<?= esc($subtarea['id_subtarea']) ?>">
                                                                            <button type="submit"
                                                                                    class="dropdown-item text-danger"
                                                                                    onclick="return confirm('¿Seguro que deseas eliminar esta subtarea: \'<?= esc($subtarea['nombre']) ?>\'?');">
                                                                                <i class="bi bi-trash me-2"></i>Eliminar Subtarea
                                                                            </button>
                                                                        </form>
                                                                    </li>
                                                                </ul>
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
                                 <div class="card-footer text-muted" style="--task-color: <?= esc($tarea['color'] ?? '#6c757d', 'attr') ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No hay tareas disponibles.</p>
                <?php endif; ?>
            </section>
        </div>

        <?php if (!empty($abrir_modal) && !empty($id_tarea_modal) && empty($abrir_modal_subtarea)): ?>
            <div class="modal fade" id="compartirTareaModal" tabindex="-1" aria-labelledby="compartirTareaModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="compartirTareaModalLabel">Compartir Tarea</h5>
                            <a href="<?= site_url('controlador_tareas/tareas') ?>" class="btn-close" aria-label="Close"></a>
                        </div>
                        <div class="modal-body">
                            <form action="<?= site_url('controlador_tareas/tareas/agregar_colaborador') ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id_tarea" value="<?= esc($id_tarea_modal) ?>">
                                <div class="mb-3">
                                    <label for="compartirTareaEmail" class="form-label">Compartir con (email):</label>
                                    <input type="email" class="form-control" name="correo" id="compartirTareaEmail" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Confirmar</button>
                            </form>
                            <hr>
                            <h6>Lista de Colaboradores de Tarea</h6>
                            <ul class="list-group">
                                <?php if (!empty($colaboradores) && is_array($colaboradores)): ?>
                                    <?php foreach ($colaboradores as $colaborador): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?= esc($colaborador['correo']) ?>
                                            <form action="<?= site_url('controlador_tareas/tareas/eliminar_colaborador') ?>" method="post" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id_tarea" value="<?= esc($id_tarea_modal) ?>">
                                                <input type="hidden" name="correo" value="<?= esc($colaborador['correo']) ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar colaborador">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="list-group-item text-muted">No hay colaboradores asignados a esta tarea.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($abrir_modal_subtarea) && !empty($id_subtarea_modal)): ?>
            <div class="modal fade" id="compartirSubtareaModal" tabindex="-1" aria-labelledby="compartirSubtareaModalLabel" aria-hidden="true">
                 <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="compartirSubtareaModalLabel">Colaboradores de Subtarea: <?= esc($nombre_subtarea_modal ?? 'Subtarea') ?></h5>
                            <a href="<?= site_url('controlador_tareas/tareas') ?>" class="btn-close" aria-label="Close"></a>
                        </div>
                        <div class="modal-body">
                            <form action="<?= site_url('controlador_tareas/subtareas/agregar_colaborador_subtarea') ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id_subtarea" value="<?= esc($id_subtarea_modal) ?>">
                                <input type="hidden" name="id_tarea_padre" value="<?= esc($id_tarea_padre_modal ?? '') ?>">
                                <div class="mb-3">
                                    <label for="compartirSubtareaEmail" class="form-label">Agregar colaborador (email):</label>
                                    <input type="email" class="form-control" name="correo" id="compartirSubtareaEmail" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Agregar a Subtarea</button>
                            </form>
                            <hr>
                            <h6>Colaboradores Actuales de la Subtarea</h6>
                            <ul class="list-group">
                                <?php if (!empty($colaboradores_subtarea_modal) && is_array($colaboradores_subtarea_modal)): ?>
                                    <?php foreach ($colaboradores_subtarea_modal as $colaborador_sub): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?= esc($colaborador_sub['correo'] ?? 'Correo no disponible') ?>
                                            <form action="<?= site_url('controlador_tareas/subtareas/eliminar_subcolaborador') ?>" method="post" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id_subtarea" value="<?= esc($id_subtarea_modal) ?>">
                                                <input type="hidden" name="id_tarea_padre" value="<?= esc($id_tarea_padre_modal ?? '') ?>">
                                                <input type="hidden" name="correo" value="<?= esc($colaborador_sub['correo'] ?? '') ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar colaborador de subtarea">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="list-group-item text-muted">No hay colaboradores asignados a esta subtarea.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
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
                            
                            <div class="list-group-item">No hay notificaciones nuevas.</div>
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
                // Script para mostrar el modal de TAREA PRINCIPAL
                var modalTareaElement = document.getElementById('compartirTareaModal');
                if (modalTareaElement && <?= json_encode(!empty($abrir_modal) && empty($abrir_modal_subtarea)) ?>) {
                    var bsModalTarea = new bootstrap.Modal(modalTareaElement);
                    bsModalTarea.show();
                }

                // Script para mostrar el modal de SUBTAREA
                var modalSubtareaElement = document.getElementById('compartirSubtareaModal');
                if (modalSubtareaElement && <?= json_encode(!empty($abrir_modal_subtarea)) ?>) {
                    var bsModalSubtarea = new bootstrap.Modal(modalSubtareaElement);
                    bsModalSubtarea.show();
                }
                
                // Tu JavaScript existente para notificaciones, etc.
                // Asegúrate de que no haya conflictos con los nuevos elementos o scripts.
            });
        </script>
    </body>
    </html>
<?php
else:
    // El usuario no ha iniciado sesión, redirige a la página de inicio de sesión
    // Es mejor usar la función redirect() de CodeIgniter si estás dentro de un controlador.
    // Como esto está al principio de una vista, header() puede funcionar, pero considera
    // manejar la lógica de autenticación en un BaseController o filtro.
    header('Location: ' . base_url('controlador_tareas/login'));
    exit();
endif;
?>
