<?php
    // Inicia la sesión si no está iniciada
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Verifica si la sesión 'usuario' está definida
    if (!isset($_SESSION['usuario'])) {
        // El usuario ha iniciado sesión, muestra el contenido de la página
?>
        <!-- Aquí va todo el contenido de la vista index.php -->
        <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Gestión de Tareas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('style.css') ?>">
</head>
<body class="d-flex align-items-center py-4 bg-body-tertiary">

<main class="form-signin w-100 m-auto">

  <?= form_open(base_url('controlador_tareas/login/login'), ['class' => 'text-center']) ?>
    <div class="text-center">
        <i class="bi bi-check2-square" style="font-size: 4rem; color: var(--bs-primary);"></i>
        <h1 class="h3 mb-3 fw-normal">Iniciar Sesión</h1>
    </div>

    <div class="form-floating mb-2">
      <?= form_input('usuario', '', [
          'class' => 'form-control',
          'id' => 'floatingInput',
          'placeholder' => 'nombre@ejemplo.com',
          'type' => 'email',
          'required' => 'required'
      ]) ?>
      <label for="floatingInput">Correo Electrónico</label>
    </div>
    <div class="form-floating mb-3">
      <?= form_password('contrasena', '', [
          'class' => 'form-control',
          'id' => 'floatingPassword',
          'placeholder' => 'Contraseña',
          'required' => 'required'
      ]) ?>
      <label for="floatingPassword">Contraseña</label>
    </div>

    <?= form_submit('login', 'Iniciar Sesión', [
        'class' => 'btn btn-primary w-100 py-2',
        'id' => 'boton-negro'
    ]) ?>

<?php if (isset($validation)): ?>
    <div class="alert alert-danger">
        <?= $validation->listErrors() ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?= $error ?>
    </div>
<?php endif; ?>

    <p class="mt-4 mb-3 text-body-secondary text-center">
        ¿No tienes cuenta?<a href="<?= base_url('controlador_tareas/registro') ?>">Regístrate</a>
    </p>
    <p class="mt-5 mb-3 text-body-secondary text-center">&copy; 2025 - Emiliano Gaido - Metodologías de desarrollo</p>
  <?= form_close() ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    } else {
        // El usuario ha iniciado sesión, redirige a la página de tareas
        header('Location: ' . base_url('controlador_tareas/tareas'));
        exit(); // Asegura que el script se detenga después de la redirección
    }
?>