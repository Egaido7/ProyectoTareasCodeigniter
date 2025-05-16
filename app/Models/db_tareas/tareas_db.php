<?php namespace App\Models\db_tareas; // Asegúrate de que la ruta sea correcta
use CodeIgniter\Model;
use App\Models\db_tareas\Usuario_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\Colaboradores_dbs; // Asegúrate de que la ruta sea correcta
class Tareas_db extends Model

{ protected $table = 'tarea'; protected $primaryKey = 'id_tarea ';
protected $useAutoIncrement = true; protected $returnType = 'array';
protected $useSoftDeletes = false;
protected $allowedFields = ['asunto', 'descripcion', 'prioridad', 'estado', 'fecha_vencimiento', 'fecha_recordatorio', 'color', 'id_tarea', 'id_responsable'];
protected $useTimestamps = false; // Dates
protected $dateFormat = 'datetime';
protected $createdField = 'created_at';
protected $updatedField = 'updated_at';
protected $deletedField = 'deleted_at';
protected $validationRules = []; // Validation
protected $validationMessages = [];
protected $skipValidation = false;
protected $cleanValidationRules = true;

public function All_tareas()
{
    return $this->findAll();
}

public function All_tareas_user($userId)
{
    // Busca todas las tareas asignadas al usuario actual
    return $this->where('id_responsable', $userId)->findAll();
}

public function Devolver_tarea($id_tarea)
{
    // Busca la tarea en la base de datos y devuelve el primer resultado
    return $this->where('id_tarea', $id_tarea)->first();
}
public function Devolver_tarea_responsable($id_responsable)
{
    // Busca la tarea en la base de datos y devuelve el primer resultado
    return $this->where('id_responsable', $id_responsable)->findAll();
}

public function Devolver_tarea_estado($estado)
{
    // Busca la tarea en la base de datos y devuelve el primer resultado
    return $this->where('estado', $estado)->findAll();
}

public function Devolver_tarea_prioridad($prioridad)
{
    // Busca la tarea en la base de datos y devuelve el primer resultado
    return $this->where('prioridad', $prioridad)->findAll();
}

public function Devolver_tarea_fecha_vencimiento($fecha_vencimiento)
{
    // Busca la tarea en la base de datos y devuelve el primer resultado
    return $this->where('fecha_vencimiento', $fecha_vencimiento)->findAll();
}

public function Devolver_tarea_fecha_recordatorio($fecha_recordatorio)
{
    // Busca la tarea en la base de datos y devuelve el primer resultado
    return $this->where('fecha_recordatorio', $fecha_recordatorio)->findAll();
}

public function Devolver_tarea_archivada($estado)
{
    // Busca la tarea en la base de datos y devuelve el primer resultado
    return $this->where('estado',$estado)->findAll();
}
public function borrar_tarea($id_tarea)
{
    return $this->delete($id_tarea);
    
}

public function cambiar_estado_tarea($id_tarea, $estado)
{
    // Cambia el estado de la tarea en la base de datos
    return $this->update($id_tarea, $estado);
}

public function cambiar_prioridad_tarea($id_tarea, $prioridad)
{
    // Cambia la prioridad de la tarea en la base de datos
    return $this->update($id_tarea, $prioridad);
}


public function archivar_tarea($id_tarea, $estado)
{
    if ($estado == 'archivada') {
        return $this->update($id_tarea, ['estado' => 'archivada']);
    } else {
        $estado = 'no_archivada';
    }
    // Archiva la tarea en la base de datos
    
}


public function ordenar_tareas($criterio, $orden = 'asc')
{
    // Ordena las tareas según el criterio y el orden
    return $this->orderBy($criterio, $orden)->findAll();
}

public function obtenerMisTareas($userId)
{
    // Obtiene las tareas asignadas al usuario actual
    return $this->where('asignado_a', $userId)->orderBy('fecha_vencimiento')->findAll();
}

public function obtenerTareasColaborador($userId)
{
    // Obtiene las tareas donde el usuario actual es colaborador
    return $this->where('colaboradores', $userId)->orderBy('fecha_vencimiento')->findAll();
}

public function guardar_tarea($data)
{
    
        $this->insert($data);
        return $this->getInsertID();
    
}

public function actualizar_tarea($id_tarea, $data)
{
    // Actualiza la tarea en la base de datos
    return $this->update($id_tarea, $data);

}
}