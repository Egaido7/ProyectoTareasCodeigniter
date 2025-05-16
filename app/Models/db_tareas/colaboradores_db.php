<?php namespace App\Models\db_tareas;
use CodeIgniter\Model;
use App\Models\db_tareas\Usuario_db; // Asegúrate de que la ruta sea correcta
class Colaboradores_db extends Model

{ protected $table = 'colaboradores'; 
    //protected $primaryKey = null;
protected $useAutoIncrement =false; protected $returnType = 'array';
protected $useSoftDeletes = false;
protected $allowedFields = ['id_tarea', 'id_user', 'estado_colaborador'];
protected $useTimestamps = false; // Dates
protected $dateFormat = 'datetime';
protected $createdField = 'created_at';
protected $updatedField = 'updated_at';
protected $deletedField = 'deleted_at';
protected $validationRules = []; // Validation
protected $validationMessages = [];
protected $skipValidation = false;
protected $cleanValidationRules = true;

public function All_colaboradores($id_tarea)
{
    return $this->select('colaboradores.id_user, usuario.correo')
                ->join('usuario', 'usuario.id_user = colaboradores.id_user')
                ->where('colaboradores.id_tarea', $id_tarea)
                ->where('colaboradores.estado_colaborador', 'aceptada')
                ->findAll();
}

public function existeColaborador($id_tarea, $correo)
{
    $usuario_db = new Usuario_db();
    $id_user = $usuario_db->Devolver_usuario($correo);
    if (!$id_user || !isset($id_user['id_user'])) {
        return false;
    }
    return $this->where('id_tarea', $id_tarea)
                ->where('id_user', $id_user['id_user'])
                ->countAllResults() > 0;
}

public function Insertar_colaborador($id_tarea, $correo) {
    $usuario_db = new Usuario_db();
    $id_user = $usuario_db->Devolver_usuario($correo);

    if ($id_user && isset($id_user['id_user'])) {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        $data = [
            'id_tarea' => $id_tarea,
            'id_user' => $id_user['id_user'],
            'estado_colaborador' => 'pendiente'
        ];
        $result = $builder->insert($data);
        return $result ? true : false;
    }
    return false; // Usuario no encontrado
}
public function Eliminar_colaborador($id_tarea, $correo)
{
    $usuario_db = new Usuario_db();
    $id_user = $usuario_db->Devolver_usuario($correo);
    if ($id_user && isset($id_user['id_user'])) {
        // Borra el colaborador de la tarea específica
        return $this->where('id_tarea', $id_tarea)
                    ->where('id_user', $id_user['id_user'])
                    ->delete();
    }
    return false; // Usuario no encontrado
}

}