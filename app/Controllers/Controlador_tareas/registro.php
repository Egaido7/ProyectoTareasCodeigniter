<?php

namespace App\Controllers\Controlador_tareas; // Asegúrate de que la ruta sea correcta

use Config\Services;
use CodeIgniter\Controller;
use App\Models\db_tareas\Usuario_db; // Asegúrate de que la ruta sea correcta
use App\Controllers\BaseController; 


class Registro extends BaseController
{
    public function __construct(){
        helper('form');
        $session = \Config\Services::session();
    }

    public function getIndex()
    {
        return view('/vistas_tareas/registro'); 
    }

    public function postGetregistrarse(){
        $validation = \Config\Services::validation();
        // Reglas de validación
    $rules = [
        'email' => 'required|valid_email',
        'password' => 'required|min_length[8]',
        'c_password' => 'required|min_length[8]|matches[password]',
        'nombre' => 'required|min_length[2]|max_length[25]',
        'apellido' => 'required|min_length[2]|max_length[25]',
    ];

    // Mensajes personalizados
    $messages = [
        'email' => [
            'required' => 'El campo correo es obligatorio',
            'valid_email' => 'Debe ingresar un correo válido',
        ],
        'password' => [
            'required' => 'El campo contraseña es obligatorio',
            'min_length' => 'La contraseña debe tener al menos 8 caracteres',
        ],
        'c_password' => [
            'required' => 'El campo contraseña es obligatorio',
            'min_length' => 'La contraseña debe tener al menos 8 caracteres',
            'matches' => 'Las contraseñas no coinciden',
        ],
        'nombre' => [
            'required' => 'El campo nombre es obligatorio',
            'min_length' => 'La contraseña debe tener al menos 2 caracteres',
            'max_length' => 'La contraseña debe tener al menos 25 caracteres',
        ],
        'apellido' => [
            'required' => 'El campo apellido es obligatorio',
            'min_length' => 'La contraseña debe tener al menos 2 caracteres',
            'max_length' => 'La contraseña debe tener al menos 25 caracteres',
        ],

    ];
     // Validar los datos
       if (!$this->validate($rules, $messages)) {
       // Si la validación falla, redirige a la vista con los errores
         return view('/vistas_tareas/registro', [
           'validation' => $this->validator,
       ]);
       }

        // Obtener los datos del formulario
        $correo = $this->request->getPost('email');
        $contrasenia = $this->request->getPost('password');
        $contrasenia2 = $this->request->getPost('c_password');
        $nombre = $this->request->getPost('nombre');
        $apellido = $this->request->getPost('apellido');
        // Instancia el modelo de usuario
        $usuario_db = new \App\Models\db_tareas\Usuario_db();

        // Guarda el nuevo usuario en la base de datos
        $data = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'correo' => $correo,
            'contrasenia' => $contrasenia,
        ];

        if ($usuario_db->Devolver_usuario($correo)) {
            return view('/vistas_tareas/registro', [
                'error' => 'El correo ya está registrado',
            ]);
        }
        
        if ($usuario_db->Guardar_usuario($data)) {
            echo "<h1>Usuario registrado correctamente</h1>";
            $session = \Config\Services::session();
            $session->set('usuario', $correo);
            header('Location: ' . base_url('controlador_tareas/tareas'));
        } else {
            echo "<h1>Error al registrar el usuario</h1>";
        }
        // Redirige a la vista de inicio de sesión o muestra un mensaje de éxito
    }
    
}

?>