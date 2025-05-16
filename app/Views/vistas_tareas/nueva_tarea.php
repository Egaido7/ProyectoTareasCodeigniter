<!-- filepath: c:\xampp\htdocs\projecto_ci4\app\Views\vistas_tareas\nueva-tarea.php -->
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
        <?= form_open(base_url('controlador_tareas/tareas/guardar_tarea'), ['method' => 'post']) ?>
        <div class="row mb-3">
            <div class="col-md-8">
                <?= form_label('Asunto o tema <span class="text-danger">*</span>', 'tareaAsunto', ['class' => 'form-label', 'escape' => false]) ?>
                <?= form_input('asunto', '', [
                    'class' => 'form-control',
                    'id' => 'tareaAsunto',
                    'placeholder' => 'Título de la tarea',
                    'required' => 'required'
                ], 'text') ?>
            </div>
            <div class="col-md-4">
                <?= form_label('Estado <span class="text-danger">*</span>', 'tareaEstado', ['class' => 'form-label', 'escape' => false]) ?>
                <?= form_dropdown('estado', [
                    'definida' => 'Definida',
                    'en_progreso' => 'En progreso',
                    'completada' => 'Completada'
                ], 'definida', [
                    'class' => 'form-select',
                    'id' => 'tareaEstado',
                    'required' => 'required'
                ]) ?>
            </div>
        </div>


        <div class="mb-3">
            <?= form_label('Descripción', 'tareaDescripcion', ['class' => 'form-label']) ?>
            <?= form_textarea('descripcion', '', [
                'class' => 'form-control',
                'id' => 'tareaDescripcion',
                'rows' => 3,

                'placeholder' => 'Detalle de la tarea a realizar'
            ]) ?>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <?= form_label('Prioridad <span class="text-danger">*</span>', 'tareaPrioridad', ['class' => 'form-label', 'escape' => false]) ?>
                <?= form_dropdown('prioridad', [
                    'baja' => 'Baja',
                    'normal' => 'Normal',
                    'alta' => 'Alta'
                ], 'normal', [
                    'class' => 'form-select',
                    'id' => 'tareaPrioridad',
                    'required' => 'required'
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= form_label('Fecha de vencimiento', 'tareaVencimiento', ['class' => 'form-label']) ?>

                <?= form_input('fecha_vencimiento', '', [
                    'class' => 'form-control',
                    'id' => 'tareaVencimiento',
                    'required' => 'required'
                ], 'date') ?>
            </div>
            <div class="col-md-4">
                <?= form_label('Fecha de recordatorio', 'tareaRecordatorio', ['class' => 'form-label']) ?>

                <?= form_input('fecha_recordatorio', '', [
                    'class' => 'form-control',
                    'id' => 'tareaRecordatorio',
                    'required' => 'required'
                ], 'date') ?>

            </div>
        </div>

        <div class="row mb-4 align-items-end">
            <div class="col-md-2">
                <?= form_label('Color', 'tareaColor', ['class' => 'form-label']) ?>
                <?= form_input('color', '#563d7c', [
                    'class' => 'form-control form-control-color w-100',
                    'id' => 'tareaColor',
                    'title' => 'Elige un color para identificar visualmente la tarea',
                    'required' => 'required'
                ], 'color') ?>
            </div>
            <div class="col-md-12">
                <?= form_label('Colaboradores', 'tareaColaboradores', ['class' => 'form-label']) ?>
                <div class="input-group">
                    <?= form_input('colaboradores', '', [
                        'class' => 'form-control',
                        'id' => 'tareaColaboradores',
                        'placeholder' => 'Correo electrónico'
                        //'required' => 'required'
                    ], 'email') ?>
                    <button class="btn btn-light" type="button" id="btnAgregarColaborador"><i class="bi bi-plus-lg"></i> Añadir</button>
                </div> 
                <div id="listaColaboradores" class="mt-2"></div>

                <input type="hidden" name="colaboradores" id="colaboradoresHidden">
            </div>
        </div>

        <div class="subtasks-form-section card card-body">
            <h5 class="card-title mb-3">Subtareas</h5>
            <div id="listaSubtareas"></div>

            <div class="add-subtask-form mt-3 pt-3 border-top">
                <h6>Añadir nueva subtarea</h6>
                <div class="mb-2">
                    <?= form_label('Asunto o nombre de la subtarea <span class="text-danger">*</span>', 'subtareaNombre', ['class' => 'form-label', 'escape' => false]) ?>
                    <?= form_input('subtarea_nombre', '', [
                        'class' => 'form-control form-control-sm',
                        'id' => 'subtareaNombre',
                        'placeholder' => 'Título de la subtarea',
                       // 'required' => 'required'
                    ]) ?>
                </div>
                <div class="mb-2">
                    <?= form_label('Descripción <span class="text-danger">*</span>', 'subtareaDescripcion', ['class' => 'form-label', 'escape' => false]) ?>
                    <?= form_input('subtarea_descripcion', '', [
                        'class' => 'form-control form-control-sm',
                        'id' => 'subtareaDescripcion',
                        'type' => 'text',
                        'placeholder' => 'Descripción de la subtarea',
                        //'required' => 'required'
                    ]) ?>

                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <?= form_label('Estado', 'subtareaEstado', ['class' => 'form-label']) ?>
                        <?= form_dropdown('subtarea_estado', [
                            'definida' => 'Definida',
                            'en_proceso' => 'En proceso',
                            'completada' => 'Completada'
                        ], 'definida', [
                            'class' => 'form-select form-select-sm',
                            'id' => 'subtareaEstado'
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= form_label('Vencimiento', 'subtareaVencimiento', ['class' => 'form-label']) ?>
                        <?= form_input('subtarea_fecha_vencimiento', '', [
                            'class' => 'form-control form-control-sm',
                            'id' => 'subtareaVencimiento',
                            //'required' => 'required'
                        ], 'date') ?>
                    </div>
                    <div class="col-md-4">
                        <?= form_label('Prioridad', 'subtareaPrioridad', ['class' => 'form-label']) ?>
                        <?= form_dropdown('subtarea_prioridad', [
                            '' => '(Opcional)',
                            'baja' => 'Baja',
                            'normal' => 'Normal',
                            'alta' => 'Alta'
                        ], '', [
                            'class' => 'form-select form-select-sm',
                            'id' => 'subtareaPrioridad'
                        ]) ?>
                    </div>
                </div>
                <div class="mb-2">
                    <?= form_label('Comentario', 'subtareaComentario', ['class' => 'form-label']) ?>
                    <?= form_input('subtarea_comentario', '', [
                        'class' => 'form-control form-control-sm',
                        'id' => 'subtareaComentario',
                        'type' => 'text',
                        'placeholder' => 'Añadir comentario o notas (opcional)',
                       // 'required' => 'required'
                    ]) ?>
                
                </div>
            
                  <input type="hidden" name="subtareas" id="subtareasHidden">
                
                <button type="button" id="boton-blanco2" class="btn btn-sm btn-dark" >
                    <i class="bi bi-plus-circle-fill"></i> Añadir subtarea a la lista
                </button>
            </div>
        </div>
        <?php if (isset($validation)): ?>
            <div class="alert alert-danger">
                <?= $validation->listErrors() ?>
            </div>
        <?php endif; ?>

        <div class="mt-4 d-flex justify-content-end">
            <a href="<?= base_url('controlador_tareas/tareas') ?>" class="btn btn-secondary me-2" id="boton-blanco">Cancelar</a>
            <button type="submit" class="btn btn-sm btn-dark" id="boton-negro">Crear Tarea</button>
        </div>
        <?= form_close() ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
let subtareas = [];

document.getElementById('boton-blanco2').addEventListener('click', function() {
    const nombre = document.getElementById('subtareaNombre').value.trim();
    const descripcion = document.getElementById('subtareaDescripcion').value.trim();
    const estado = document.getElementById('subtareaEstado').value;
    const vencimiento = document.getElementById('subtareaVencimiento').value;
    const prioridad = document.getElementById('subtareaPrioridad').value;
    const comentario = document.getElementById('subtareaComentario').value.trim();
   const responsable = document.getElementById('subtareaResponsable')?.value || '';

    if (!nombre || !descripcion) return;

    subtareas.push({
        nombre,
        descripcion,
        estado,
        vencimiento,
        prioridad,
        comentario,
        responsable
    });

    actualizarListaSubtareas();

    // Limpiar campos
    document.getElementById('subtareaNombre').value = '';
    document.getElementById('subtareaDescripcion').value = '';
    document.getElementById('subtareaEstado').value = 'definida';
    document.getElementById('subtareaVencimiento').value = '';
    document.getElementById('subtareaPrioridad').value = '';
    document.getElementById('subtareaComentario').value = '';
});

function actualizarListaSubtareas() {
    const lista = document.getElementById('listaSubtareas');
    lista.innerHTML = '';
    subtareas.forEach((sub, idx) => {
        const div = document.createElement('div');
        div.className = 'subtask-item border-bottom pb-2 mb-2 d-flex justify-content-between';
        div.innerHTML = `
            <div>
                <p class="mb-1"><strong>${sub.nombre}</strong></p>
                <small class="text-muted">Estado: ${sub.estado}</small>
                ${sub.vencimiento ? `<small class="text-muted"> | Vencimiento: ${sub.vencimiento}</small>` : ''}
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-subtask" onclick="eliminarSubtarea(${idx})" title="Eliminar subtarea">✕</button>
        `;
        lista.appendChild(div);
    });
    const hidden = document.getElementById('subtareasHidden');
    if (hidden) {
        hidden.value = JSON.stringify(subtareas);
           console.log(hidden.value);
    }
 
}

function eliminarSubtarea(idx) {
    subtareas.splice(idx, 1);
    actualizarListaSubtareas();
}
</script>


    <script>
let colaboradores = [];
function esEmailValido(email) {
    // Expresión regular básica para validar email
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
document.getElementById('btnAgregarColaborador').addEventListener('click', function() {
    const input = document.getElementById('tareaColaboradores');
    const email = input.value.trim();
     if (!esEmailValido(email)) {
        alert('Por favor, ingresa un correo electrónico válido.');
        return;
    }
    if (email && !colaboradores.includes(email)) {
        colaboradores.push(email);
        actualizarListaColaboradores();
        input.value = '';
    }
});

function actualizarListaColaboradores() {
    const lista = document.getElementById('listaColaboradores');
    lista.innerHTML = '';
    colaboradores.forEach((correo, idx) => {
        const div = document.createElement('div');
        div.className = 'd-flex align-items-center mb-1';
        div.innerHTML = `
            <span class="me-2">${correo}</span>
            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarColaborador(${idx})"><i class="bi bi-trash"></i></button>
        `;
        lista.appendChild(div);
    });
    // Actualiza el campo oculto con los correos separados por coma o como JSON
     const hidden = document.getElementById('colaboradoresHidden');
    if (hidden) {
        hidden.value = JSON.stringify(colaboradores);
           console.log(hidden.value);
        
    }
 
    
}

function eliminarColaborador(idx) {
    colaboradores.splice(idx, 1);
    actualizarListaColaboradores();
}
</script>
</body>

</html>