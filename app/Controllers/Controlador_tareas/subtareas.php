<?php

namespace App\Controllers\Controlador_tareas; // Asegúrate de que la ruta sea correcta

use Config\Services;
use CodeIgniter\Controller;
use App\Models\db_tareas\Subtareas_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\Colaboradores_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\Tareas_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\Usuario_db;
use App\Models\db_tareas\Notificaciones_db; // Asegúrate de que la ruta sea correcta
use App\Controllers\BaseController;
use App\Models\db_tareas\Colaboradores_subtareas_db;

class Subtareas extends BaseController
{

    public function __construct()
    {
        helper(['form', 'url', 'text']);
    }

    public function getCompartir_subtarea()
    {
        $id_tarea_padre = $this->request->getGet('id_tarea'); 
        $id_subtarea    = $this->request->getGet('id_subtarea');

        if (empty($id_subtarea) || empty($id_tarea_padre)) { // Se necesitan ambos IDs
            return redirect()->to(base_url('controlador_tareas/tareas'))->with('error', 'IDs de tarea o subtarea no proporcionados.');
        }

        $subtareas_db = new Subtareas_db();
        // Usar el método para clave compuesta o where() directamente
        $subtarea_actual = $subtareas_db->findSubtareaCompuesta($id_tarea_padre, $id_subtarea);
        // Alternativamente:
        // $subtarea_actual = $subtareas_db->where('id_tarea', $id_tarea_padre)
        //                                ->where('id_subtarea', $id_subtarea)
        //                                ->first();

        if (!$subtarea_actual) {
            return redirect()->to(base_url('controlador_tareas/tareas'))->with('error', 'Subtarea no encontrada.');
        }
        
        $colaboradores_subtareas_model = new Colaboradores_subtareas_db(); 
        $colaboradores_de_subtarea = $colaboradores_subtareas_model->All_subcolaboradores($id_subtarea);

        $tareas_db = new Tareas_db();
        $user_id = session()->get('user_id'); 
        $tareas = $tareas_db->All_tareas_user($user_id);

        foreach ($tareas as &$tarea_loop) { 
            if (isset($tarea_loop['id_tarea'])) {
                $tarea_loop['total_subtareas'] = $subtareas_db->Devolver_numero_subtareas($tarea_loop['id_tarea']);
                $tarea_loop['subtareas_completadas'] = $subtareas_db->Devolver_numero_subtareas_estado($tarea_loop['id_tarea'], 'completada');
                $tarea_loop['subtareas'] = $subtareas_db->All_subtareas($tarea_loop['id_tarea']);
            }
        }
        unset($tarea_loop); 

        return view('vistas_tareas/index', [
            'tareas'                      => $tareas, 
            'colaboradores_subtarea_modal'=> $colaboradores_de_subtarea, 
            'id_tarea_padre_modal'        => $id_tarea_padre, 
            'id_subtarea_modal'           => $id_subtarea,       
            'nombre_subtarea_modal'       => $subtarea_actual['nombre'] ?? 'Subtarea', 
            'abrir_modal_subtarea'        => true             
        ]);
    }

     public function postAgregar_colaborador_subtarea()
    {
        $id_tarea_padre = $this->request->getPost('id_tarea_padre'); 
        $id_subtarea    = $this->request->getPost('id_subtarea');
        $correo_invitado = $this->request->getPost('correo');
        $id_usuario_actual = session()->get('user_id');
        $nombre_usuario_actual = session()->get('usuario');

        $subtareas_db = new Subtareas_db();
        $subtarea_actual = $subtareas_db->findSubtareaCompuesta($id_tarea_padre, $id_subtarea);
        $usuario_db = new Usuario_db();
        $notificaciones_db = new Notificaciones_db();

        if (!$subtarea_actual) {
             return redirect()->to(site_url('controlador_tareas/tareas'))
                             ->with('error_subcolab', 'Subtarea no encontrada.');
        }
        // Aquí podrías añadir una lógica de permisos: ¿Quién puede invitar a una subtarea?
        // ¿El responsable de la tarea padre? ¿El responsable de la subtarea (si existe)?
        // Por ahora, asumimos que si se llega aquí, se tiene permiso.

        if (empty($id_subtarea) || empty($correo_invitado) || !filter_var($correo_invitado, FILTER_VALIDATE_EMAIL) || empty($id_tarea_padre)) {
            $redirect_url = empty($id_subtarea) || empty($id_tarea_padre) ? 
                            site_url('controlador_tareas/tareas') : 
                            site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . $id_subtarea . '&id_tarea=' . $id_tarea_padre);
            return redirect()->to($redirect_url)
                             ->with('error_subcolab', 'Datos inválidos para agregar colaborador.')
                             ->withInput();
        }

        $info_invitado = $usuario_db->Devolver_usuario($correo_invitado);
        if (!$info_invitado || !isset($info_invitado['id_user'])) {
            return redirect()->to(site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . $id_subtarea . '&id_tarea=' . $id_tarea_padre))
                             ->with('error_subcolab', 'El usuario invitado no existe.');
        }
        $id_usuario_invitado = $info_invitado['id_user'];

        if ($id_usuario_invitado == $id_usuario_actual) {
             return redirect()->to(site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . $id_subtarea . '&id_tarea=' . $id_tarea_padre))
                             ->with('error_subcolab', 'No puedes invitarte a ti mismo a la subtarea.');
        }

        $colaboradores_subtareas_model = new Colaboradores_subtareas_db();
        
        if ($colaboradores_subtareas_model->existeColaborador_subtarea($id_subtarea, $correo_invitado)) {
            return redirect()->to(site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . $id_subtarea . '&id_tarea=' . $id_tarea_padre))
                             ->with('error_subcolab', 'El usuario ya es colaborador de esta subtarea.');
        }
        
        if ($notificaciones_db->existeInvitacionPendiente($id_usuario_invitado, 'invitacion_subtarea', $id_subtarea)) {
            return redirect()->to(site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . $id_subtarea . '&id_tarea=' . $id_tarea_padre))
                             ->with('info', 'Ya existe una invitación pendiente para este usuario en esta subtarea.');
        }

        if ($colaboradores_subtareas_model->Insertar_subcolaborador($id_subtarea, $correo_invitado, 'pendiente')) { // Asumiendo que Insertar_subcolaborador acepta estado
            $mensaje_notif = esc($nombre_usuario_actual) . " te ha invitado a colaborar en la subtarea: \"" . esc($subtarea_actual['nombre']) . "\".";
            $notificaciones_db->crearNotificacion([
                'id_usuario_destino' => $id_usuario_invitado,
                'tipo_notificacion' => 'invitacion_subtarea',
                'mensaje' => $mensaje_notif,
                'id_entidad_principal' => $id_subtarea,
                'tipo_entidad_principal' => 'subtarea',
                'id_entidad_relacionada' => $id_usuario_actual, // Quién invita
                'tipo_entidad_relacionada' => 'usuario',
                'datos_adicionales' => ['nombre_subtarea' => $subtarea_actual['nombre'], 'nombre_invitador' => $nombre_usuario_actual, 'id_tarea_padre' => $id_tarea_padre]
            ]);
            return redirect()->to(site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . $id_subtarea . '&id_tarea=' . $id_tarea_padre))
                             ->with('success_subcolab', 'Invitación enviada al colaborador de la subtarea.');
        } else {
            return redirect()->to(site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . $id_subtarea . '&id_tarea=' . $id_tarea_padre))
                             ->with('error_subcolab', 'No se pudo agregar el colaborador a la subtarea.');
        }
    }
    public function postEliminar_subcolaborador()
    {
        $id_tarea_padre = $this->request->getPost('id_tarea_padre'); 
        $id_subtarea    = $this->request->getPost('id_subtarea');
        $correo         = $this->request->getPost('correo');
        if (empty($id_subtarea) || empty($correo) || empty($id_tarea_padre)) {
            $redirect_url = empty($id_subtarea) || empty($id_tarea_padre) ? 
                            site_url('controlador_tareas/tareas') : 
                            site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . $id_subtarea . '&id_tarea=' . $id_tarea_padre);
            return redirect()->to($redirect_url)
                             ->with('error_subcolab', 'Datos incompletos para eliminar colaborador.');
        }

        $colaboradores_subtareas_model = new Colaboradores_subtareas_db();

        if ($colaboradores_subtareas_model->Eliminar_subcolaborador($id_subtarea, $correo)) { 
            return redirect()->to(site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . $id_subtarea . '&id_tarea=' . $id_tarea_padre))
                             ->with('success_subcolab', 'Colaborador eliminado de la subtarea.');
        } else {
            return redirect()->to(site_url('controlador_tareas/subtareas/compartir_subtarea?id_subtarea=' . $id_subtarea . '&id_tarea=' . $id_tarea_padre))
                             ->with('error_subcolab', 'No se pudo eliminar el colaborador de la subtarea.');
        }
    }

    public function postEliminar_subtarea()
    {
        $id_tarea_padre = $this->request->getPost('id_tarea'); 
        $id_subtarea    = $this->request->getPost('id_subtarea');
        $user_id        = session()->get('user_id');

        if (empty($id_subtarea) || empty($id_tarea_padre)) {
            return redirect()->back()->with('error', 'ID de tarea o subtarea no proporcionado.');
        }

        $subtareas_db = new Subtareas_db();
        $tareas_db    = new Tareas_db();

        $subtarea_actual = $subtareas_db->findSubtareaCompuesta($id_tarea_padre, $id_subtarea);
        $tarea_padre_actual = $tareas_db->find($id_tarea_padre);

        if (!$subtarea_actual || !$tarea_padre_actual) {
            return redirect()->back()->with('error', 'Tarea o subtarea no encontrada.');
        }

        // Verificar permisos:
        // 1. El usuario es el responsable de la tarea padre
        // 2. O el usuario es el responsable de la subtarea
        $es_responsable_tarea_padre = ($tarea_padre_actual['id_responsable'] == $user_id);
        $es_responsable_subtarea = ($subtarea_actual['id_responsable'] == $user_id);

        if (!$es_responsable_tarea_padre && !$es_responsable_subtarea) {
            return redirect()->back()->with('error', 'No tienes permiso para eliminar esta subtarea.');
        }
        
        $colaboradores_subtareas_model = new Colaboradores_subtareas_db();
        // $this->db->transStart(); // Descomentar si usas transacciones explícitas

        $colaboradores_subtareas_model->where('id_subtarea', $id_subtarea)->delete(); 

        if ($subtareas_db->Eliminar_subtarea_compuesta($id_tarea_padre, $id_subtarea)) { 
            // $this->db->transComplete();
            // if ($this->db->transStatus() === false) { /* Manejar error de transacción */ }
            
            // Después de eliminar, verificar si la tarea padre debe cambiar de estado
            $total_subtareas_restantes = $subtareas_db->Devolver_numero_subtareas($id_tarea_padre);
            $subtareas_completadas_restantes = $subtareas_db->Devolver_numero_subtareas_estado($id_tarea_padre, 'completada');

            if ($total_subtareas_restantes > 0 && $total_subtareas_restantes == $subtareas_completadas_restantes) {
                if ($tarea_padre_actual['estado'] !== 'completada') {
                    $tareas_db->update($id_tarea_padre, ['estado' => 'completada']);
                }
            } elseif ($tarea_padre_actual['estado'] === 'completada' && $total_subtareas_restantes == 0) {
                // Si se eliminó la última subtarea y estaba completada, la tarea padre sigue completada.
                // Si se eliminó la última subtarea y NO estaba completada, la tarea padre podría pasar a 'en_progreso' o 'definida' si no tiene más subtareas.
                // O si era la última subtarea y estaba completada, la tarea padre sigue completada.
                // Si no hay subtareas, y la tarea estaba completada por sus subtareas, podría necesitarse una lógica adicional.
                // Por ahora, si no hay subtareas, no cambiamos el estado de la tarea padre basado en esto.
            } elseif ($tarea_padre_actual['estado'] === 'completada' && $total_subtareas_restantes > 0 && $total_subtareas_restantes != $subtareas_completadas_restantes) {
                 $tareas_db->update($id_tarea_padre, ['estado' => 'en_progreso']);
            }


            return redirect()->back()->with('success', 'Subtarea eliminada correctamente.');
        } else {
            // $this->db->transRollback();
            $errors = $subtareas_db->errors(); 
            log_message('error', 'Error al eliminar subtarea (T:' . $id_tarea_padre . ', S:' . $id_subtarea . '): ' . print_r($errors, true));
            return redirect()->back()->with('error', 'No se pudo eliminar la subtarea.');
        }
    }
    
    // ... (resto de tus métodos como getTachar_subtarea)
    public function getTachar_subtarea($id_subtarea, $id_tarea, $nuevo_estado_subtarea = 'completada')
    {
        $subtareas_db = new Subtareas_db();
        $tareas_db = new Tareas_db(); // Instanciar el modelo de Tareas

        // 1. Cambiar el estado de la subtarea
        if ($subtareas_db->cambiar_estado_subtarea($id_subtarea, $id_tarea, $nuevo_estado_subtarea)) {
            
            // 2. Verificar si todas las subtareas de la tarea padre están completadas
            $total_subtareas = $subtareas_db->Devolver_numero_subtareas($id_tarea);
            $subtareas_completadas = $subtareas_db->Devolver_numero_subtareas_estado($id_tarea, 'completada');

            $tarea_actual = $tareas_db->find($id_tarea); // Obtener estado actual de la tarea padre

            if ($total_subtareas > 0 && $total_subtareas == $subtareas_completadas) {
                // Todas las subtareas están completadas, marcar la tarea principal como 'completada'
                if ($tarea_actual && $tarea_actual['estado'] !== 'completada') {
                    $tareas_db->update($id_tarea, ['estado' => 'completada']);
                    session()->setFlashdata('info', 'Estado de subtarea actualizado. ¡Todas las subtareas completadas, tarea principal marcada como completada!');
                } else {
                     session()->setFlashdata('info', 'Estado de subtarea actualizado.');
                }
            } elseif ($nuevo_estado_subtarea !== 'completada' && $tarea_actual && $tarea_actual['estado'] === 'completada') {
                // Si una subtarea se desmarcó (ya no está 'completada') y la tarea padre estaba 'completada',
                // se podría cambiar el estado de la tarea padre a 'en_progreso' o 'definida'.
                // Ajusta 'en_progreso' al estado que consideres apropiado.
                $tareas_db->update($id_tarea, ['estado' => 'en_progreso']);
                session()->setFlashdata('info', 'Estado de subtarea actualizado. Tarea principal actualizada a "en progreso".');
            } else {
                // No todas las subtareas están completadas o no hay subtareas
                session()->setFlashdata('info', 'Estado de subtarea actualizado.');
            }
            
            return redirect()->to(base_url('controlador_tareas/tareas'));
        } else {
            return redirect()->to(base_url('controlador_tareas/tareas'))->with('error', 'No se pudo actualizar el estado de la subtarea.');
        }
    }

}