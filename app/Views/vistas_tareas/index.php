<?php
// CodeIgniter 4 maneja la sesión automáticamente si está configurada.
if (session()->has('usuario')): // Usar helper de CI4
    $user_id_actual = session()->get('user_id'); // Obtener el ID del usuario actual una vez
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
                <a href="<?= base_url('controlador_tareas/tareas') ?>" class="h3 mb-0 me-md-auto" style = "text-decoration: none;">Gestión de Tareas</a>
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
                                    <?php
                                    // Contar notificaciones no leídas para un badge (opcional)
                                    // Esto requeriría cargar este conteo en el controlador Tareas::getIndex
                                    // $unread_count = 0; // $notificaciones_db->where('id_usuario_destino', $user_id_actual)->where('leida', 0)->countAllResults();
                                    // if ($unread_count > 0) {
                                    //     echo '<span class="badge bg-danger ms-auto">' . $unread_count . '</span>';
                                    // }
                                    ?>
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
                                'mis_tareas' => 'Mis Tareas',
                                'colaborador' => 'Tareas como Colaborador'
                            ], old('criterio', ''), ['class' => 'form-select', 'id' => 'ordenar', 'onchange' => 'this.form.submit()']) ?>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </section>

            <h2 class="h4 mb-3">Mis Tareas (Responsable)</h2>
            <section class="task-list row g-4">
                <?php if (!empty($tareas) && is_array($tareas)): ?>
                    <?php foreach ($tareas as $tarea): ?>
                        <?php $es_responsable_tarea = (isset($tarea['id_responsable']) && $tarea['id_responsable'] == $user_id_actual); ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card task-card" style="border-left-color: <?= esc($tarea['color'] ?? '#6c757d', 'attr') ?>;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <a href="<?= base_url('controlador_tareas/tareas/editar_tarea/' . $tarea['id_tarea']) ?>">
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
                                                </form>
                                                <li>
                                                    <a href="<?= base_url('controlador_tareas/tareas/compartir?id_tarea=' . $tarea['id_tarea']) ?>" class="dropdown-item">
                                                        <i class="bi bi-person-plus me-2"></i>Compartir Tarea
                                                    </a>
                                                </li>
                                                <?php if ($es_responsable_tarea): ?>

                                                    <form action="<?= base_url('controlador_tareas/tareas/accion_tarea') ?>" method="post" class="form-accion-tarea">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="id_tarea" value="<?= esc($tarea['id_tarea']) ?>">
                                                        <li>
                                                            <button type="submit" name="accion" value="eliminar" class="dropdown-item text-danger" onclick="return confirm('¿Seguro que deseas eliminar esta tarea?');">
                                                                <i class="bi bi-trash me-2"></i>Eliminar Tarea
                                                            </button>
                                                        </li>
                                                        <?php if (($tarea['estado'] ?? 'definida') === 'completada'): ?>
                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>
                                                            <li>
                                                                <button type="submit" name="accion" value="archivar" class="dropdown-item" onclick="return confirm('¿Seguro que deseas archivar esta tarea?');">
                                                                    <i class="bi bi-archive me-2"></i>Archivar Tarea
                                                                </button>
                                                            </li>
                                                        <?php endif; ?>
                                                    </form>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <span class="badge bg-<?= ($tarea['prioridad'] ?? 'normal') === 'alta' ? 'danger' : ((($tarea['prioridad'] ?? 'normal') === 'media' || ($tarea['prioridad'] ?? 'normal') === 'normal') ? 'warning' : 'success') ?> me-2">
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
                                                <span><i class="bi bi-list-task me-1"></i> Subtareas (<span class="subtask-completed-count"><?= esc($tarea['subtareas_completadas'] ?? 0) ?></span>/<span class="subtask-total-count"><?= esc($tarea['total_subtareas'] ?? 0) ?></span>)</span>
                                                <i class="bi bi-chevron-down"></i>
                                            </a>
                                            <div class="collapse mt-2" id="subtareas<?= esc($tarea['id_tarea']) ?>">
                                                <div class="list-group list-group-flush subtask-list">
                                                    <?php foreach ($tarea['subtareas'] as $subtarea): ?>
                                                        <?php
                                                        $es_responsable_subtarea = (isset($subtarea['id_responsable']) && $subtarea['id_responsable'] == $user_id_actual);
                                                        $puede_eliminar_subtarea = $es_responsable_tarea || $es_responsable_subtarea; // Responsable de tarea padre O responsable de subtarea
                                                        $subtarea_estado_actual = $subtarea['estado'] ?? 'pendiente';
                                                        $subtarea_completada = ($subtarea_estado_actual === 'completada');
                                                        $id_checkbox_subtarea = 'sub-' . esc($subtarea['id_subtarea']) . '-tarea-' . esc($tarea['id_tarea']);
                                                        ?>
                                                        <div class="list-group-item d-flex justify-content-between align-items-center ps-0 border-0 py-1">
                                                            <div class="form-check flex-grow-1">
                                                                <input class="form-check-input subtask-checkbox" type="checkbox"
                                                                    value=""
                                                                    id="<?= $id_checkbox_subtarea ?>"
                                                                    <?= $subtarea_completada ? 'checked' : '' ?>
                                                                    onchange="window.location.href='<?= site_url('controlador_tareas/subtareas/tachar_subtarea/' . esc($subtarea['id_subtarea']) . '/' . esc($tarea['id_tarea'])) ?>/' + (this.checked ? 'completada' : 'en_progreso')">
                                                                <label class="form-check-label subtask-label <?= $subtarea_completada ? 'text-decoration-line-through text-muted' : '' ?>" for="<?= $id_checkbox_subtarea ?>">
                                                                    <?= esc($subtarea['nombre']) ?>
                                                                    <?php if (!empty($subtarea['fecha_vencimiento'])): ?>
                                                                        <small class="text-muted">(Vence: <?= esc(date('d/m/Y', strtotime($subtarea['fecha_vencimiento']))) ?>)</small>
                                                                    <?php endif; ?>
                                                                    <span class="badge bg-<?= ($subtarea['prioridad'] ?? 'normal') === 'alta' ? 'danger' : ((($subtarea['prioridad'] ?? 'normal') === 'media' || ($subtarea['prioridad'] ?? 'normal') === 'normal') ? 'warning' : 'success') ?> ms-1">
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
                                                                        <a href="<?= site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . esc($subtarea['id_subtarea']) . '&id_tarea=' . esc($tarea['id_tarea'])) ?>" class="dropdown-item">
                                                                            <i class="bi bi-person-plus me-2"></i>Colaboradores
                                                                        </a>
                                                                    </li>
                                                                    <?php if ($puede_eliminar_subtarea): ?>
                                                                        <li>
                                                                            <hr class="dropdown-divider">
                                                                        </li>
                                                                        <li>
                                                                            <form action="<?= site_url('controlador_tareas/subtareas/eliminar_subtarea') ?>" method="post" class="form-accion-subtarea d-inline">
                                                                                <?= csrf_field() ?>
                                                                                <input type="hidden" name="id_tarea" value="<?= esc($tarea['id_tarea']) ?>">
                                                                                <input type="hidden" name="id_subtarea" value="<?= esc($subtarea['id_subtarea']) ?>">
                                                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('¿Seguro que deseas eliminar esta subtarea: \'<?= esc($subtarea['nombre']) ?>\'?');">
                                                                                    <i class="bi bi-trash me-2"></i>Eliminar Subtarea
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="subtasks-section"><span class="text-muted small"><i class="bi bi-list-task me-1"></i> Sin subtareas</span></div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer text-muted" style="--task-color: <?= esc($tarea['color'] ?? '#6c757d', 'attr') ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No hay tareas asignadas como responsable.</p>
                <?php endif; ?>
            </section>

            <hr class="my-5">

            <h2 class="h4 mb-3">Subtareas Compartidas Conmigo</h2>
            <section class="task-list row g-4">
                <?php if (!empty($subtareas_compartidas) && is_array($subtareas_compartidas)): ?>
                    <?php foreach ($subtareas_compartidas as $subcompartida):  ?>
                        <?php

                        $subcompartida_estado_actual = $subcompartida['subtarea_estado'] ?? 'definida';
                        $subcompartida_completada = ($subcompartida_estado_actual === 'completada');
                        $id_checkbox_sub_comp = 'subcomp-' . esc($subcompartida['id_subtarea']) . '-tareapadre-' . esc($subcompartida['tarea_padre_id']);

                        // Lógica de permisos para eliminar subtarea compartida:
                        // Solo el responsable de la tarea padre de esta subtarea, o el responsable de la propia subtarea.
                        //$es_responsable_tarea_padre_de_sub = (isset($subcompartida['id_responsable_tarea_padre']) && $subcompartida['id_responsable_tarea_padre'] == $user_id_actual);
                        //$es_responsable_de_esta_sub = (isset($subcompartida['id_responsable_subtarea']) && $subcompartida['id_responsable_subtarea'] == $user_id_actual);
                        //$puede_eliminar_esta_sub_compartida = $es_responsable_tarea_padre_de_sub || $es_responsable_de_esta_sub;
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card task-card task-card-shared" style="border-left-color: <?= esc($subcompartida['tarea_padre_color'] ?? '#6c757d', 'attr') ?>;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="form-check flex-grow-1">
                                            <input class="form-check-input subtask-checkbox" type="checkbox"
                                                value=""
                                                id="<?= $id_checkbox_sub_comp ?>"
                                                <?= $subcompartida_completada ? 'checked' : '' ?>
                                                onchange="window.location.href='<?= site_url('controlador_tareas/subtareas/tachar_subtarea/' . esc($subcompartida['id_subtarea']) . '/' . esc($subcompartida['tarea_padre_id'])) ?>/' + (this.checked ? 'completada' : 'en_progreso')">
                                            <label class="form-check-label <?= $subcompartida_completada ? 'text-decoration-line-through text-muted' : '' ?>" for="<?= $id_checkbox_sub_comp ?>">
                                                <h5 class="card-title mb-0 d-inline"><?= esc($subcompartida['subtarea_nombre']) ?></h5>
                                            </label>
                                            <br><small class="text-muted">De Tarea: <?= esc($subcompartida['tarea_padre_asunto'] ?? 'N/A') ?></small>
                                        </div>
                                        <?php /*OPCION PARA DARSE DE BAJA O VER COLABORADORES
                                        <div class="dropdown subtask-options ms-2">
                                            <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opciones de subtarea compartida">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a href="<?= site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . esc($subcompartida['id_subtarea']) . '&id_tarea=' . esc($subcompartida['tarea_padre_id'])) ?>" class="dropdown-item">
                                                        <i class="bi bi-people me-2"></i>Ver/Administrar Colaboradores
                                                    </a>
                                                </li>
                                                <?php //NO IMPLEMENTE POR QUE NO LO PIDE EL ENUNCIADO ?>
                                                <?php if ($puede_eliminar_esta_sub_compartida): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="<?= site_url('controlador_tareas/subtareas/eliminar_subtarea') ?>" method="post" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="id_tarea" value="<?= esc($subcompartida['tarea_padre_id']) ?>">
                                                        <input type="hidden" name="id_subtarea" value="<?= esc($subcompartida['id_subtarea']) ?>">
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('¿Seguro que deseas eliminar esta subtarea: \'<?= esc($subcompartida['subtarea_nombre']) ?>\'? (Como responsable)');">
                                                            <i class="bi bi-trash me-2"></i>Eliminar (como Resp.)
                                                        </button>
                                                    </form>
                                                </li>
                                                <?php endif; ?>
                                                
                                            </ul>
                                        </div>
                                        */
                                        ?>

                                    </div>
                                    <div class="mb-3">
                                        <span class="badge bg-<?= ($subcompartida['subtarea_prioridad'] ?? 'normal') === 'alta' ? 'danger' : ((($subcompartida['subtarea_prioridad'] ?? 'normal') === 'media' || ($subcompartida['subtarea_prioridad'] ?? 'normal') === 'normal') ? 'warning' : 'success') ?> me-2">
                                            <small><?= esc(ucfirst($subcompartida['subtarea_prioridad'] ?? 'normal')) ?></small>
                                        </span>


                                        <span class="badge bg-<?= ($subcompartida['subtarea_estado'] ?? 'definida') === 'completada' ? 'success' : (($subcompartida['subtarea_estado'] ?? 'definida') === 'en_progreso' ? 'warning' : 'info') ?>">
                                            <small><?= esc(str_replace('_', ' ', ucfirst($subcompartida['subtarea_estado'] ?? 'definida'))) ?></small>
                                        </span>
                                    </div>
                                    <?php if (!empty($subcompartida['subtarea_vencimiento'])): ?>
                                        <p class="mb-1 task-details"><i class="bi bi-calendar-check me-2 text-danger"></i> Vence: <?= esc(date('d M Y', strtotime($subcompartida['subtarea_vencimiento']))) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer text-muted" style="--task-color: <?= esc($subcompartida['tarea_padre_color'] ?? '#6c757d', 'attr') ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No tienes subtareas compartidas directamente contigo.</p>
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
                                <?php if (!empty($colaboradores_modal) && is_array($colaboradores_modal)): ?>
                                    <?php foreach ($colaboradores_modal as $colaborador): ?>
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
                            <?php if (!empty($notificaciones_usuario) && is_array($notificaciones_usuario)): ?>
                                <?php foreach ($notificaciones_usuario as $notif): ?>
                                    <div class="list-group-item list-group-item-action notification-item <?= $notif['leida'] ? 'opacity-75' : '' ?>" id="notificacion-<?= esc($notif['id_notificacion']) ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?php
                                                    $icon = 'bi-info-circle-fill text-info'; // Default
                                                    if ($notif['tipo_notificacion'] === 'invitacion_tarea' || $notif['tipo_notificacion'] === 'invitacion_subtarea') {
                                                        $icon = 'bi-person-plus-fill text-primary';
                                                    } elseif ($notif['tipo_notificacion'] === 'recordatorio_vencimiento') {
                                                        $icon = 'bi-exclamation-triangle-fill text-danger';
                                                    } elseif (str_contains($notif['tipo_notificacion'], '_aceptada')) {
                                                        $icon = 'bi-check-circle-fill text-success';
                                                    } elseif (str_contains($notif['tipo_notificacion'], '_rechazada')) {
                                                        $icon = 'bi-x-circle-fill text-danger';
                                                    }
                                                    ?>
                                                    <i class="bi <?= $icon ?> me-2"></i>
                                                    <?= esc(ucfirst(str_replace('_', ' ', $notif['tipo_notificacion']))) ?>
                                                </h6>
                                                <p class="mb-1 small"><?= esc($notif['mensaje']) ?></p>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted"><?= esc(date('d/m/Y H:i', strtotime($notif['fecha_creacion']))) ?></small>
                                                <button type="button" class="btn-close btn-sm ms-2 descartar-notificacion-btn"
                                                    data-id-notificacion="<?= esc($notif['id_notificacion']) ?>"
                                                    aria-label="Descartar" title="Descartar notificación"></button>
                                            </div>
                                        </div>

                                        <?php if (($notif['tipo_notificacion'] === 'invitacion_tarea' || $notif['tipo_notificacion'] === 'invitacion_subtarea') && !$notif['leida']): ?>
                                            <div class="mt-2">
                                                <a href="<?= site_url('controlador_tareas/notificacionescontroller/responderinvitacion/' . esc($notif['id_notificacion']) . '/aceptar') ?>" class="btn btn-sm btn-success me-2">
                                                    <i class="bi bi-check-lg"></i> Aceptar
                                                </a>
                                                <a href="<?= site_url('controlador_tareas/notificacionescontroller/responderinvitacion/' . esc($notif['id_notificacion']) . '/rechazar') ?>" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-x-lg"></i> Rechazar
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($notif['tipo_notificacion'] === 'recordatorio_vencimiento' && isset($notif['id_entidad_principal'])): ?>
                                            <a href="<?= site_url('controlador_tareas/tareas/editar_tarea/' . esc($notif['id_entidad_principal'])) ?>" class="btn btn-sm btn-outline-primary mt-1">Ver Tarea</a>
                                        <?php endif; ?>

                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item">No hay notificaciones nuevas o recientes.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary me-auto" id="btnMarcarTodasLeidas">Marcar todas como leídas</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="<?= base_url('script.js') ?>"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalTareaElement = document.getElementById('compartirTareaModal');
                if (modalTareaElement && <?= json_encode(!empty($abrir_modal) && empty($abrir_modal_subtarea) && !empty($id_tarea_modal)) ?>) {
                    var bsModalTarea = new bootstrap.Modal(modalTareaElement);
                    bsModalTarea.show();
                }

                var modalSubtareaElement = document.getElementById('compartirSubtareaModal');
                if (modalSubtareaElement && <?= json_encode(!empty($abrir_modal_subtarea) && !empty($id_subtarea_modal)) ?>) {
                    var bsModalSubtarea = new bootstrap.Modal(modalSubtareaElement);
                    bsModalSubtarea.show();
                }

                // JavaScript para Notificaciones
                const notificacionesModal = document.getElementById('notificacionesModal');
                if (notificacionesModal) {
                    // Marcar todas como leídas
                    const btnMarcarTodas = document.getElementById('btnMarcarTodasLeidas');
                    if (btnMarcarTodas) {
                        btnMarcarTodas.addEventListener('click', function() {
                            fetch('<?= site_url('controlador_tareas/notificacionescontroller/marcarTodasLeidas') ?>', {
                                    method: 'POST',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>' // Para protección CSRF
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        document.querySelectorAll('#notificacionesModal .notification-item:not(.opacity-75)').forEach(item => {
                                            item.classList.add('opacity-75');
                                            // Opcional: quitar botones de acción de invitaciones
                                            item.querySelectorAll('.btn-success, .btn-outline-danger').forEach(btn => btn.remove());
                                        });
                                        // alert(data.message); // O un feedback más sutil
                                    } else {
                                        // alert(data.message || 'Error al marcar como leídas.');
                                    }
                                })
                                .catch(error => console.error('Error:', error));
                        });
                    }

                    // Descartar notificación individual
                    notificacionesModal.addEventListener('click', function(event) {
                        if (event.target.closest('.descartar-notificacion-btn')) {
                            event.preventDefault();
                            const button = event.target.closest('.descartar-notificacion-btn');
                            const notifId = button.dataset.idNotificacion;
                            if (!notifId || !confirm('¿Seguro que deseas descartar esta notificación?')) return;

                            fetch(`<?= site_url('controlador_tareas/notificacionescontroller/descartar/') ?>${notifId}`, {
                                    method: 'POST', // O GET si tu ruta lo permite y es seguro
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        document.getElementById(`notificacion-${notifId}`)?.remove();
                                    } else {
                                        // alert(data.message || 'Error al descartar.');
                                    }
                                })
                                .catch(error => console.error('Error:', error));
                        }
                    });
                }
            });
        </script>
    </body>

    </html>
<?php
else:
    // El usuario no ha iniciado sesión, redirige a la página de inicio de sesión
    header('Location: ' . base_url('controlador_tareas/login'));
    exit();
endif;
?>