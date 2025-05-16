<?php

namespace App\Controllers\Controlador_tareas; // Asegúrate de que la ruta sea correcta

use Config\Services;
use CodeIgniter\Controller;
use App\Models\db_tareas\Usuario_db; // Asegúrate de que la ruta sea correcta
use App\Controllers\BaseController; 

class Login extends BaseController
{
    public function __construct(){
        helper('form');
        $session = \Config\Services::session();
    }
    
    public function getIndex()
    {
        return view('/vistas_tareas/logearse'); 
    }

public function postLogin()
{
    $validation = \Config\Services::validation();

    // Reglas de validación
    $rules = [
        'usuario' => 'required|valid_email',
        'contrasena' => 'required|min_length[8]',
    ];

    // Mensajes personalizados
    $messages = [
        'usuario' => [
            'required' => 'El campo correo es obligatorio',
            'valid_email' => 'Debe ingresar un correo válido',
        ],
        'contrasena' => [
            'required' => 'El campo contraseña es obligatorio',
            'min_length' => 'La contraseña debe tener al menos 8 caracteres',
        ],
    ];

    // Validar los datos
    if (!$this->validate($rules, $messages)) {
        // Si la validación falla, redirige a la vista con los errores
        return view('/vistas_tareas/logearse', [
            'validation' => $this->validator,
        ]);
    }

    // Obtener los datos del formulario
    $usuario = $this->request->getPost('usuario');
    $contrasenia = $this->request->getPost('contrasena');

    // Instancia el modelo de usuario
    $usuario_db = new \App\Models\db_tareas\Usuario_db();

    // Busca el usuario en la base de datos
    $usuarioEncontrado = $usuario_db->Devolver_usuario($usuario);

    if ($usuarioEncontrado && $usuarioEncontrado['contrasenia'] === $contrasenia) {
        $session = \Config\Services::session();
        $session->set('usuario', $usuarioEncontrado['correo']);
        $session->set('user_id', $usuarioEncontrado['id_user']); // Guarda el ID del usuario en la sesión
        return redirect()->to(base_url('controlador_tareas/tareas')); // Redirige a la página de tareas
    } else {
        return view('/vistas_tareas/logearse', [
            'error' => 'Usuario o contraseña incorrectos',
        ]);
    }
}

    public function getLogout()
    {
        session_destroy();
        return redirect()->to(base_url('controlador_tareas/login'));
    }
}
