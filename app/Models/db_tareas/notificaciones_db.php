<?php

namespace App\Models\db_tareas;

use CodeIgniter\Model;

class Notificaciones_db extends Model
{
    protected $table            = 'notificaciones';
    protected $primaryKey       = 'id_notificacion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'id_usuario_destino',
        'tipo_notificacion',
        'mensaje',
        'id_entidad_principal',
        'tipo_entidad_principal',
        'id_entidad_relacionada',
        'tipo_entidad_relacionada',
        'datos_adicionales',
        'leida',
        // 'fecha_creacion' es manejada por la DB
    ];

    protected $useTimestamps = true; // Para que CI maneje fecha_creacion si no lo hace la DB
    protected $createdField  = 'fecha_creacion';
    protected $updatedField  = ''; // No necesitamos updated_at para notificaciones simples

    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Crea una nueva notificación.
     * @param array $data Datos de la notificación.
     * @return int|false ID insertado o false en error.
     */
    public function crearNotificacion(array $data)
    {
        // Asegurarse que datos_adicionales sea JSON si se provee
        if (isset($data['datos_adicionales']) && is_array($data['datos_adicionales'])) {
            $data['datos_adicionales'] = json_encode($data['datos_adicionales']);
        }
        return $this->insert($data);
    }

    /**
     * Obtiene las notificaciones no leídas para un usuario, ordenadas por fecha.
     * @param int $id_usuario_destino
     * @param int $limite
     * @return array
     */
    public function getNotificacionesNoLeidas($id_usuario_destino, $limite = 20)
    {
        return $this->where('id_usuario_destino', $id_usuario_destino)
                    ->where('leida', 0)
                    ->orderBy('fecha_creacion', 'DESC')
                    ->limit($limite)
                    ->findAll();
    }
    
    /**
     * Obtiene todas las notificaciones para un usuario, ordenadas por fecha.
     * @param int $id_usuario_destino
     * @param int $limite
     * @return array
     */
    public function getAllNotificacionesUsuario($id_usuario_destino, $limite = 50)
    {
        return $this->where('id_usuario_destino', $id_usuario_destino)
                    ->orderBy('fecha_creacion', 'DESC')
                    ->limit($limite)
                    ->findAll(); // Podrías añadir paginación aquí si son muchas
    }


    /**
     * Marca una notificación específica como leída.
     * @param int $id_notificacion
     * @param int $id_usuario_destino (para seguridad, asegurarse que el usuario es el dueño)
     * @return bool
     */
    public function marcarComoLeida($id_notificacion, $id_usuario_destino)
    {
        return $this->where('id_notificacion', $id_notificacion)
                    ->where('id_usuario_destino', $id_usuario_destino)
                    ->set(['leida' => 1])
                    ->update();
    }

    /**
     * Marca todas las notificaciones de un usuario como leídas.
     * @param int $id_usuario_destino
     * @return bool
     */
    public function marcarTodasComoLeidas($id_usuario_destino)
    {
        return $this->where('id_usuario_destino', $id_usuario_destino)
                    ->where('leida', 0) // Solo marcar las no leídas
                    ->set(['leida' => 1])
                    ->update();
    }

    /**
     * Elimina (descarta) una notificación específica.
     * @param int $id_notificacion
     * @param int $id_usuario_destino (para seguridad)
     * @return bool
     */
    public function descartarNotificacion($id_notificacion, $id_usuario_destino)
    {
        return $this->where('id_notificacion', $id_notificacion)
                    ->where('id_usuario_destino', $id_usuario_destino)
                    ->delete();
    }

    /**
     * Verifica si ya existe una notificación de invitación pendiente para una entidad y usuario.
     * @param int $id_usuario_destino
     * @param string $tipo_notificacion ('invitacion_tarea' o 'invitacion_subtarea')
     * @param int $id_entidad_principal (ID de la tarea o subtarea)
     * @return bool
     */
    public function existeInvitacionPendiente($id_usuario_destino, $tipo_notificacion, $id_entidad_principal)
    {
        return $this->where('id_usuario_destino', $id_usuario_destino)
                    ->where('tipo_notificacion', $tipo_notificacion)
                    ->where('id_entidad_principal', $id_entidad_principal)
                    ->where('leida', 0) // O un estado específico para invitaciones pendientes si lo tienes
                    ->countAllResults() > 0;
    }
}
