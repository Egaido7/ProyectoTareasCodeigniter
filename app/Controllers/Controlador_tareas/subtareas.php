<?php

namespace App\Controllers\Controlador_tareas; // Asegúrate de que la ruta sea correcta

use Config\Services;
use CodeIgniter\Controller;
use App\Models\db_tareas\Subtareas_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\Colaboradores_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\Tareas_db; // Asegúrate de que la ruta sea correcta
use App\Controllers\BaseController;
use App\Models\Colaboradores_subtareas_db;

class Subtareas extends BaseController{

    public function __construct(){
        helper('form');
        $session = \Config\Services::session();
    }

    public function getIndex()
    {
        //return view('/vistas_tareas/index'); 
        $subtareas_db = new Subtareas_db();
        //$tareas = $tareas_db->All_tareas_user(session()->get('user_id'));
        $subtareas = $subtareas_db->All_subtareas(1);  //MANEJAR CON SESSION
        
        return view('vistas_tareas/index', ['subtareas' => $subtareas]);
    }

    public function getTachar_subtarea($id_subtarea,$id_tarea ,$estado='completado')
    {
        
        $subtareas_db = new Subtareas_db();
        $subtareas_db->cambiar_estado_subtarea($id_subtarea,$id_tarea ,$estado);
        return redirect()->to(base_url('controlador_tareas/tareas')); // Redirige a la vista de subtareas
    }

     public function getCompartir_subtarea()
    {
        $id_tarea = $this->request->getGet('id_tarea');
        $id_subtarea = $this->request->getGet('id_subtarea');
        $colaboradores_subtareas_db = new Colaboradores_db();
        $colaboradores = $colaboradores_subtareas_db->All_subcolaboradores($id_subtarea);

        // Puedes volver a cargar las tareas si quieres que la vista siga igual
        $tareas_db = new Tareas_db();
        $tareas = $tareas_db->findAll();

        return view('vistas_tareas/index', [
            'tareas' => $tareas,
            'colaboradores' => $colaboradores,
            'id_tarea_modal' => $id_tarea,
            'id_subtarea_modal' => $id_subtarea,
            'abrir_modal' => true
        ]);
    }

     public function postAgregar_colaborador_subtarea()
    {
        $id_subtarea = $this->request->getPost('id_subtarea');
        $correo = $this->request->getPost('correo');
        $colaboradores_subtareas_db = new Colaboradores_subtareas_db();
        if ($colaboradores_subtareas_db->existeColaborador_subtarea($id_subtarea, $correo)) {
            // Ya existe, muestra mensaje de error o ignora
            return redirect()->back()->with('error', 'El usuario ya fue invitado a esta tarea.');
        } else {
            if ($colaboradores_subtareas_db->Insertar_subcolaborador($id_subtarea, $correo)) {
                redirect()->to(base_url('controlador_tareas/tareas/compartir?id_subtarea=' . $id_subtarea))
                    ->with('success', 'Colaborador agregado');
            } else {
                return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_subtarea=' . $id_subtarea))
                    ->with('error', 'No se pudo agregar el colaborador, no existe en la base de datos');
            }
        }
    }



    public function postEliminar_subcolaborador()
    {
        $id_subtarea = $this->request->getPost('id_subtarea');
        $correo = $this->request->getPost('correo');
        $colaboradores_subtareas_db = new Colaboradores_subtareas_db();

        if ($colaboradores_subtareas_db->Eliminar_subcolaborador($id_subtarea, $correo)) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartirsub?id_subtarea=' . $id_subtarea))
                ->with('success', 'Colaborador eliminado');
        } else {
            return redirect()->to(base_url('controlador_tareas/tareas/compartirsub?id_subtarea=' . $id_subtarea))
                ->with('error', 'No se pudo eliminar el colaborador');
        }
    }


      public function postEliminar_subtarea(){
            $id_tarea = $this->request->getPost('id_tarea');
    $id_subtarea = $this->request->getPost('id_subtarea');
    $subtareas_db = new Subtareas_db();
        if ($subtareas_db->Eliminar_subtarea($id_tarea, $id_subtarea)) {
        return redirect()->back()->with('success', 'Subtarea eliminada correctamente');
    } else {
        return redirect()->back()->with('error', 'No se pudo eliminar la subtarea');
    }
    }


   

  
}
    ?>