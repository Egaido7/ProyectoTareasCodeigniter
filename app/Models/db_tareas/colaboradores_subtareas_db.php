<?php namespace App\Models\db_tareas;
use CodeIgniter\Model;
use App\Models\db_tareas\Usuario_db; // Asegúrate de que la ruta sea correcta
class Colaboradores_subtareas_db extends Model

{ protected $table = 'colaboradores_subtareas'; 
    //protected $primaryKey = null;
protected $useAutoIncrement = true; protected $returnType = 'array';
protected $useSoftDeletes = false;
protected $allowedFields = ['id_subtarea', 'id_user', 'estado_colaborador'];
protected $useTimestamps = false; // Dates
protected $dateFormat = 'datetime';
protected $createdField = 'created_at';
protected $updatedField = 'updated_at';
protected $deletedField = 'deleted_at';
protected $validationRules = []; // Validation
protected $validationMessages = [];
protected $skipValidation = false;
protected $cleanValidationRules = true;



public function All_subcolaboradores($id_subtarea)
{
    return $this->select('colaboradores_subtareas.id_user, usuario.correo')
                ->join('usuario', 'usuario.id_user = colaboradores_subtareas.id_user')
                ->where('colaboradores_subtareas.id_subtarea', $id_subtarea)
                ->where('colaboradores_subtareas.estado_colaborador', 'aceptada')
                ->findAll();
}

public function existeColaborador_subtarea($id_subtarea, $correo)
{
    $usuario_db = new Usuario_db();
    $id_user = $usuario_db->Devolver_usuario($correo);
    if (!$id_user || !isset($id_user['id_user'])) {
        return false;
    }
    return $this->where('id_subtarea', $id_subtarea)
                ->where('id_user', $id_user['id_user'])
                ->countAllResults() > 0;
}

public function Insertar_subcolaborador($id_subtarea, $correo) {
    $usuario_db = new Usuario_db();
    $id_user = $usuario_db->Devolver_usuario($correo);

    if ($id_user && isset($id_user['id_user'])) {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        $data = [
            'id_subtarea' => $id_subtarea,
            'id_user' => $id_user['id_user'],
            'estado_colaborador' => 'pendiente'
        ];
        $result = $builder->insert($data);
        return $result ? true : false;
    }
    return false; // Usuario no encontrado
}
public function Eliminar_subcolaborador($id_subtarea, $correo)
{
    $usuario_db = new Usuario_db();
    $id_user = $usuario_db->Devolver_usuario($correo);
    if ($id_user && isset($id_user['id_user'])) {
        // Borra el colaborador de la tarea específica
        return $this->where('id_subtarea', $id_subtarea)
                    ->where('id_user', $id_user['id_user'])
                    ->delete();
    }
    return false; // Usuario no encontrado
}

public function getSubtareasCompartidasConUsuario($userId)
{
    return $this->select('cs.id_subtarea, s.nombre as subtarea_nombre, s.estado as subtarea_estado, s.prioridad as subtarea_prioridad, s.fecha_vencimiento as subtarea_vencimiento, s.id_tarea as tarea_padre_id, t.asunto as tarea_padre_asunto, t.color as tarea_padre_color, s.id_responsable as id_responsable_subtarea, t.id_responsable as id_responsable_tarea_padre') // Añadido id_responsable_subtarea y id_responsable_tarea_padre
                ->from($this->table . ' cs') // Alias para colaboradores_subtareas
                ->join('subtarea s', 's.id_subtarea = cs.id_subtarea') // Asumiendo que id_subtarea es único en la tabla subtarea
                ->join('tarea t', 't.id_tarea = s.id_tarea') // Join con la tabla de tareas para obtener el nombre de la tarea padre
                ->where('cs.id_user', $userId)
                ->where('cs.estado_colaborador', 'aceptada') // Solo donde la colaboración está aceptada
                ->orderBy('t.asunto', 'ASC')
                ->orderBy('s.nombre', 'ASC')
                ->findAll();
}


public function actualizarEstadoColaboradorPorUsuario($id_subtarea, $id_usuario, $nuevo_estado)
{
    return $this->where('id_subtarea', $id_subtarea)
                ->where('id_user', $id_usuario)
                ->set(['estado_colaborador' => $nuevo_estado])
                ->update();
}
}