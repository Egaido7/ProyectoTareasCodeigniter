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

     public function findSubtareaCompuesta($id_tarea, $id_subtarea)
    {
        return $this->where('id_tarea', $id_tarea)
                    ->where('id_subtarea', $id_subtarea)
                    ->first();
    }

    public function All_subtareas($id_tarea)
    {
        return $this->where('id_tarea', $id_tarea)->orderBy('id_subtarea', 'ASC')->findAll();
    }

    public function All_subtareas_user($userId)
    {
        return $this->where('id_responsable', $userId)->findAll();
    }

    public function cambiar_estado_subtarea($id_subtarea, $id_tarea, $estado)
    {
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

    /**
     * Inserta una nueva subtarea.
     * Si 'id_subtarea' es AUTO_INCREMENT en la DB, no debería pasarse en $data.
     * El modelo intentará llenarlo si está en $allowedFields y $useAutoIncrement es true (pero lo quitamos).
     * Es más seguro omitir 'id_subtarea' de $data si es autoincremental en la DB.
     * Si 'id_subtarea' lo calculas tú (ej. max + 1 por id_tarea), entonces inclúyelo en $data.
     */

     /*
    public function Insertar_subtarea($data)
    {
        // Si id_subtarea es AUTO_INCREMENT en la base de datos,
        // y no lo estás generando tú mismo, no lo incluyas en el array $data.
        // La base de datos se encargará de asignarle un valor.
        // Ejemplo: si $data viene con id_subtarea, y este es autoincrement, podría dar error o ignorarse.
        // Si lo gestionas tú, asegúrate que la combinación (id_tarea, id_subtarea_calculada) sea única.
        return $this->insert($data); // Devuelve el ID insertado (si es PK simple y AI) o true/false
    }
*/
public function Insertar_subtarea($data){
     // Usa Query Builder para evitar problemas con clave compuesta
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        $result = $builder->insert($data);
        return $result ? true : false;
}
    
    
    /**
     * Actualiza una subtarea usando su clave compuesta.
     * @param int $id_tarea
     * @param int $id_subtarea
     * @param array $data
     * @return bool
     */
    public function Actualizar_subtarea_compuesta($id_tarea, $id_subtarea, $data) 
    {
        return $this->where('id_tarea', $id_tarea)
                    ->where('id_subtarea', $id_subtarea)
                    ->set($data)
                    ->update();
    }

    // El método Actualizar_subtarea que tenías antes podría dar problemas si $id_subtarea no es único globalmente.
    // public function Actualizar_subtarea($id_subtarea, $data) // Asumiendo id_subtarea es la PK para actualizar
    // {
    //     return $this->update($id_subtarea, $data); 
    // }


    /**
     * Elimina una subtarea usando su clave compuesta.
     * @param int $id_tarea
     * @param int $id_subtarea
     * @return mixed
     */
    public function Eliminar_subtarea_compuesta($id_tarea, $id_subtarea)
    {
        return $this->where('id_tarea', $id_tarea)
                    ->where('id_subtarea', $id_subtarea)
                    ->delete();
    }

}

