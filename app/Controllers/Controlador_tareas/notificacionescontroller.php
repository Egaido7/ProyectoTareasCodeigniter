<?php namespace App\Controllers\Controlador_tareas;

use App\Controllers\BaseController;
use App\Models\db_tareas\Notificaciones_db;
use App\Models\db_tareas\Colaboradores_db;
use App\Models\db_tareas\Colaboradores_subtareas_db;
use App\Models\db_tareas\Tareas_db;
use App\Models\db_tareas\Subtareas_db;
use App\Models\db_tareas\Usuario_db;


class Notificacionescontroller extends BaseController
{
    // Propiedades para los modelos y datos de usuario
    protected $notificaciones_db;
    protected $colaboradores_db;
    protected $colaboradores_subtareas_db;
    protected $tareas_db;
    protected $subtareas_db;
    protected $usuario_db;
    protected $user_id;
    protected $user_nombre;

    public function __construct()
    {
        helper(['url', 'form', 'text']);
        // Instanciar modelos y datos de sesión en el constructor
        $this->notificaciones_db = new Notificaciones_db();
        $this->colaboradores_db = new Colaboradores_db();
        $this->colaboradores_subtareas_db = new Colaboradores_subtareas_db();
        $this->tareas_db = new Tareas_db();
        $this->subtareas_db = new Subtareas_db();
        $this->usuario_db = new Usuario_db();
        
        // Obtener datos del usuario de la sesión
        $this->user_id = session()->get('user_id');
        $this->user_nombre = session()->get('usuario'); 
    }

    // Método para marcar una notificación como leída (espera POST)
    public function postMarcarleida($id_notificacion)
    {
        // Solo procesar si es una petición AJAX o un POST directo
        if ($this->request->isAJAX() || $this->request->getMethod() === 'post') {
            if ($this->notificaciones_db->marcarComoLeida($id_notificacion, $this->user_id)) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['status' => 'success', 'message' => 'Notificación marcada como leída.']);
                }
                session()->setFlashdata('success_notif', 'Notificación marcada como leída.');
                return redirect()->back(); // Redirigir a la página anterior
            }
            // Si falla en marcar como leída
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo marcar la notificación como leída.']);
            }
            session()->setFlashdata('error_notif', 'No se pudo marcar la notificación como leída.');
            return redirect()->back();
        }
        // Si no es AJAX ni POST, redirigir a la página principal de tareas
        return redirect()->to(base_url('controlador_tareas/tareas')); 
    }

    // Método para marcar todas las notificaciones como leídas (espera POST)
    public function postMarcartodasleidas()
    {
         if ($this->request->isAJAX() || $this->request->getMethod() === 'post') {
            if ($this->notificaciones_db->marcarTodasComoLeidas($this->user_id)) {
                 if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['status' => 'success', 'message' => 'Todas las notificaciones marcadas como leídas.']);
                }
                session()->setFlashdata('success_notif', 'Todas las notificaciones marcadas como leídas.');
                return redirect()->back();
            }
             if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudieron marcar todas las notificaciones como leídas.']);
            }
            session()->setFlashdata('error_notif', 'Error al marcar todas las notificaciones como leídas.');
            return redirect()->back();
        }
        return redirect()->to(base_url('controlador_tareas/tareas'));
    }

    // Método para descartar una notificación (espera POST)
    public function postDescartar($id_notificacion)
    {
        if ($this->request->isAJAX() || $this->request->getMethod() === 'post') {
            if ($this->notificaciones_db->descartarNotificacion($id_notificacion, $this->user_id)) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['status' => 'success', 'message' => 'Notificación descartada.']);
                }
                session()->setFlashdata('success_notif', 'Notificación descartada.');
                return redirect()->back();
            }
             if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo descartar la notificación.']);
            }
            session()->setFlashdata('error_notif', 'No se pudo descartar la notificación.');
            return redirect()->back();
        }
         return redirect()->to(base_url('controlador_tareas/tareas'));
    }

    // Método para responder a una invitación (espera GET, ya que se accede por enlace)
    public function getResponderinvitacion($id_notificacion, $accion)
    {
        if (!in_array($accion, ['aceptar', 'rechazar'])) {
            session()->setFlashdata('error_notif', 'Acción no válida.');
            return redirect()->back();
        }

        // Asegurarse que user_id esté disponible
        if (!$this->user_id) {
            session()->setFlashdata('error_notif', 'Error de sesión, por favor inicie sesión nuevamente.');
            return redirect()->to(base_url('controlador_tareas/login')); // O a donde corresponda
        }

        $notificacion = $this->notificaciones_db->where('id_usuario_destino', $this->user_id)->find($id_notificacion);

        if (!$notificacion) {
            session()->setFlashdata('error_notif', 'Notificación no encontrada o no te pertenece.');
            return redirect()->back();
        }

        $nuevo_estado_colaborador = ($accion === 'aceptar') ? 'aceptada' : 'rechazada';
        $mensaje_respuesta = ($accion === 'aceptar') ? 'aceptó' : 'rechazó';
        $notif_respuesta_tipo = '';
        $entidad_principal_nombre = '';

        $id_entidad = $notificacion['id_entidad_principal'];
        $tipo_entidad = $notificacion['tipo_entidad_principal'];
        $id_invitador = $notificacion['id_entidad_relacionada']; 

        $datos_adicionales = json_decode($notificacion['datos_adicionales'] ?? '{}', true);
        $actualizacion_exitosa = false;

        if ($tipo_entidad === 'tarea' && $notificacion['tipo_notificacion'] === 'invitacion_tarea') {
            // Asegúrate que el método actualizarEstadoColaboradorPorUsuario exista en Colaboradores_db
            $actualizacion_exitosa = $this->colaboradores_db->actualizarEstadoColaboradorPorUsuario($id_entidad, $this->user_id, $nuevo_estado_colaborador);
            $entidad_principal_nombre = $datos_adicionales['nombre_tarea'] ?? 'una tarea';
            $notif_respuesta_tipo = ($accion === 'aceptar') ? 'invitacion_tarea_aceptada' : 'invitacion_tarea_rechazada';

        } elseif ($tipo_entidad === 'subtarea' && $notificacion['tipo_notificacion'] === 'invitacion_subtarea') {
            // Asegúrate que el método actualizarEstadoColaboradorPorUsuario exista en Colaboradores_subtareas_db
            $actualizacion_exitosa = $this->colaboradores_subtareas_db->actualizarEstadoColaboradorPorUsuario($id_entidad, $this->user_id, $nuevo_estado_colaborador);
            $entidad_principal_nombre = $datos_adicionales['nombre_subtarea'] ?? 'una subtarea';
            $notif_respuesta_tipo = ($accion === 'aceptar') ? 'invitacion_subtarea_aceptada' : 'invitacion_subtarea_rechazada';
        }

        if ($actualizacion_exitosa) {
            $this->notificaciones_db->marcarComoLeida($id_notificacion, $this->user_id); 

            if ($id_invitador && $id_invitador != $this->user_id) {
                $mensaje_para_invitador = esc($this->user_nombre) . " ha {$mensaje_respuesta} tu invitación para colaborar en " . esc($entidad_principal_nombre) . ".";
                $this->notificaciones_db->crearNotificacion([
                    'id_usuario_destino' => $id_invitador,
                    'tipo_notificacion' => $notif_respuesta_tipo,
                    'mensaje' => $mensaje_para_invitador,
                    'id_entidad_principal' => $id_entidad,
                    'tipo_entidad_principal' => $tipo_entidad,
                    'id_entidad_relacionada' => $this->user_id, 
                    'tipo_entidad_relacionada' => 'usuario',
                    'datos_adicionales' => json_encode($datos_adicionales) // Asegurar que sea JSON
                ]);
            }
            session()->setFlashdata('success_notif', 'Invitación ' . $mensaje_respuesta . '.');
        } else {
            session()->setFlashdata('error_notif', 'No se pudo procesar la respuesta a la invitación. Es posible que ya no exista la colaboración o el estado ya haya sido cambiado.');
        }
        return redirect()->back();
    }
}
