<!-- filepath: c:\xampp\htdocs\projecto_ci4\app\Views\vistas_tareas\nueva-tarea.php -->
 <?php
// CodeIgniter 4 maneja la sesión automáticamente si está configurada.
if (session()->has('usuario')): // Usar helper de CI4
    $user_id_actual = session()->get('user_id'); // Obtener el ID del usuario actual una vez
?>


<?php
else:
    // El usuario no ha iniciado sesión, redirige a la página de inicio de sesión
    header('Location: ' . base_url('controlador_tareas/login'));
    exit();
endif;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Tarea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('style.css') ?>">
</head>

<body>
    <div class="container mt-4 mb-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('controlador_tareas/tareas') ?>">Gestión de Tareas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nueva Tarea</li>
            </ol>
        </nav>
        <h1 class="h3 mb-4">Crear Nueva Tarea</h1>
        <p class="text-muted">Completa el siguiente formulario para crear una nueva tarea. Los campos marcados con <span class="text-danger">*</span> son obligatorios.</p>
        
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

        <?= form_open(base_url('controlador_tareas/tareas/crear_tareaprincipal'), ['method' => 'post']) ?>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <?= form_label('Asunto o tema <span class="text-danger">*</span>', 'tareaAsunto', ['class' => 'form-label', 'escape' => false]) ?>
                <?= form_input('asunto', set_value('asunto'), [
                    'class' => 'form-control' . (isset($validation) && $validation->hasError('asunto') ? ' is-invalid' : ''),
                    'id' => 'tareaAsunto',
                    'placeholder' => 'Título de la tarea',
                    'required' => 'required' // Mantenemos required para UX, la validación del server es la clave
                ]) ?>
                <?php if (isset($validation) && $validation->hasError('asunto')): ?>
                    <div class="invalid-feedback"><?= $validation->getError('asunto') ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <?= form_label('Estado <span class="text-danger">*</span>', 'tareaEstado', ['class' => 'form-label', 'escape' => false]) ?>
                <?= form_dropdown('estado', [
                    'definida' => 'Definida',
                    'en_progreso' => 'En progreso',
                    'completada' => 'Completada'
                ], set_value('estado', 'definida'), [
                    'class' => 'form-select',
                    'id' => 'tareaEstado',
                ]) ?>
            </div>
        </div>

        <div class="mb-3">
            <?= form_label('Descripción <span class="text-danger">*</span>', 'tareaDescripcion', ['class' => 'form-label', 'escape' => false]) ?>
            <?= form_textarea('descripcion', set_value('descripcion'), [
                'class' => 'form-control' . (isset($validation) && $validation->hasError('descripcion') ? ' is-invalid' : ''),
                'id' => 'tareaDescripcion',
                'rows' => 3,
                'placeholder' => 'Detalle de la tarea a realizar',
                'required' => 'required'
            ]) ?>
             <?php if (isset($validation) && $validation->hasError('descripcion')): ?>
                <div class="invalid-feedback"><?= $validation->getError('descripcion') ?></div>
            <?php endif; ?>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <?= form_label('Prioridad <span class="text-danger">*</span>', 'tareaPrioridad', ['class' => 'form-label', 'escape' => false]) ?>
                <?= form_dropdown('prioridad', [
                    'baja' => 'Baja',
                    'normal' => 'Normal',
                    'alta' => 'Alta'
                ], set_value('prioridad', 'normal'), [
                    'class' => 'form-select',
                    'id' => 'tareaPrioridad',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= form_label('Fecha de vencimiento <span class="text-danger">*</span>', 'tareaVencimiento', ['class' => 'form-label', 'escape' => false]) ?>
                <?= form_input('fecha_vencimiento', set_value('fecha_vencimiento'), [
                    'class' => 'form-control' . (isset($validation) && $validation->hasError('fecha_vencimiento') ? ' is-invalid' : ''),
                    'id' => 'tareaVencimiento',
                    'required' => 'required'
                ], 'date') ?>
                <?php if (isset($validation) && $validation->hasError('fecha_vencimiento')): ?>
                    <div class="invalid-feedback"><?= $validation->getError('fecha_vencimiento') ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <?= form_label('Fecha de recordatorio <span class="text-danger">*</span>', 'tareaRecordatorio', ['class' => 'form-label', 'escape' => false]) ?>
                <?= form_input('fecha_recordatorio', set_value('fecha_recordatorio'), [
                    'class' => 'form-control' . (isset($validation) && $validation->hasError('fecha_recordatorio') ? ' is-invalid' : ''),
                    'id' => 'tareaRecordatorio',
                    'required' => 'required'
                ], 'date') ?>
                 <?php if (isset($validation) && $validation->hasError('fecha_recordatorio')): ?>
                    <div class="invalid-feedback"><?= $validation->getError('fecha_recordatorio') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mb-4 align-items-end">
            <div class="col-md-2"> <?= form_label('Color', 'tareaColor', ['class' => 'form-label']) ?>
                <?= form_input('color', set_value('color', '#563d7c'), [ // Valor por defecto como en tu vista original
                    'class' => 'form-control form-control-color w-100',
                    'id' => 'tareaColor',
                    'title' => 'Elige un color para identificar visualmente la tarea',
                ], 'color') ?>
            </div>
        </div>
        
        <?php if (isset($validation) && !empty($validation->getErrors()) && !$validation->hasError('asunto') && !$validation->hasError('descripcion') && !$validation->hasError('fecha_vencimiento') && !$validation->hasError('fecha_recordatorio') ): ?>
            <div class="alert alert-danger">
                <p>Por favor corrige los siguientes errores:</p>
                <?= $validation->listErrors() ?>
            </div>
        <?php endif; ?>

        <div class="mt-4 d-flex justify-content-end">
            <a href="<?= base_url('controlador_tareas/tareas') ?>" class="btn btn-secondary me-2" id="boton-blanco">Cancelar</a>
            <button type="submit" class="btn btn-dark" id="boton-negro">Crear Tarea y Continuar</button>
        </div>
        <?= form_close() ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>