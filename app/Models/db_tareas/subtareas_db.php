<?php namespace App\Models\db_tareas; // Asegúrate de que la ruta sea correcta
use CodeIgniter\Model;


class Subtareas_db extends Model{
     protected $table = 'subtarea';
    // No declares $primaryKey si tienes clave compuesta
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['id_subtarea', 'id_tarea', 'descripcion', 'nombre', 'prioridad', 'estado', 'fecha_vencimiento', 'comentario', 'id_responsable'];
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

     public function All_subtareas($id_tarea)
    {
        return $this->where('id_tarea', $id_tarea)->findAll();
    }

    public function All_subtareas_user($userId)
    {
        return $this->where('id_responsable', $userId)->findAll();
    }

       public function cambiar_estado_subtarea($id_tarea, $id_subtarea, $estado)
    {
        // Usa Query Builder para clave compuesta
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        return $builder
            ->where('id_tarea', $id_tarea)
            ->where('id_subtarea', $id_subtarea)
            ->update(['estado' => $estado]);
    }

    public function Devolver_numero_subtareas($id_tarea)
    {
        return $this->where('id_tarea', $id_tarea)->countAllResults();
    }

    public function Devolver_numero_subtareas_estado($id_tarea, $estado)
    {
        return $this->where(['id_tarea' => $id_tarea, 'estado' => $estado])->countAllResults();
    }

    public function Insertar_subtarea($data)
    {
        // Usa Query Builder para evitar problemas con clave compuesta
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        $result = $builder->insert($data);
        return $result ? true : false;
    }
    
     public function Actualizar_subtarea($id_tarea, $id_subtarea, $data)
    {
        // Usa Query Builder para clave compuesta
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        return $builder
            ->where('id_tarea', $id_tarea)
            ->where('id_subtarea', $id_subtarea)
            ->update($data);
    }

     public function Eliminar_subtarea($id_tarea, $id_subtarea)
    {
        // Usa Query Builder para clave compuesta
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        return $builder
            ->where('id_tarea', $id_tarea)
            ->where('id_subtarea', $id_subtarea)
            ->delete();
    }
}

