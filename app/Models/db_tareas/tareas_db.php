<?php namespace App\Models\db_tareas; 

use CodeIgniter\Model;
// No es necesario: use App\Models\db_tareas\Usuario_db; 
// No es necesario: use App\Models\db_tareas\Colaboradores_dbs; // Parece un typo, debería ser Colaboradores_db y no se usa aquí directamente

class Tareas_db extends Model
{
    protected $table            = 'tarea';
    // Hay un espacio extra en 'id_tarea ', lo quitaré.
    protected $primaryKey       = 'id_tarea'; 
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'asunto', 'descripcion', 'prioridad', 'estado', 
        'fecha_vencimiento', 'fecha_recordatorio', 'color', 
        'id_tarea', 'id_responsable'
    ];

    protected $useTimestamps = false; 
    // protected $dateFormat    = 'datetime'; // No es necesario si useTimestamps es false
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function All_tareas()
    {
        return $this->findAll();
    }

    public function All_tareas_user($userId)
    {
        // Busca todas las tareas asignadas al usuario actual
        return $this->where('id_responsable', $userId)
                    ->orderBy('fecha_vencimiento', 'ASC') // Opcional: un orden por defecto
                    ->findAll();
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

    /**
     * Devuelve tareas de un usuario específico filtradas por estado.
     * @param string $estado
     * @param int $userId
     * @return array
     */
    public function Devolver_tarea_estado($estado, $userId)
    {
        return $this->where('id_responsable', $userId)
                    ->where('estado', $estado)
                    ->findAll();
    }

    /**
     * Devuelve tareas de un usuario específico filtradas por prioridad.
     * @param string $prioridad
     * @param int $userId
     * @return array
     */
    public function Devolver_tarea_prioridad($prioridad, $userId)
    {
        return $this->where('id_responsable', $userId)
                    ->where('prioridad', $prioridad)
                    ->findAll();
    }

    public function Devolver_tarea_fecha_vencimiento($fecha_vencimiento, $userId) // Añadido $userId
    {
        return $this->where('id_responsable', $userId)
                    ->where('fecha_vencimiento', $fecha_vencimiento)
                    ->findAll();
    }

    public function Devolver_tarea_fecha_recordatorio($fecha_recordatorio, $userId) // Añadido $userId
    {
        return $this->where('id_responsable', $userId)
                    ->where('fecha_recordatorio', $fecha_recordatorio)
                    ->findAll();
    }

    /**
     * Devuelve tareas de un usuario específico filtradas por estado de archivado.
     * @param string $estado_archivado ('archivada' o 'no_archivada')
     * @param int $userId
     * @return array
     */
    public function Devolver_tarea_archivada($estado_archivado, $userId)
    {
        if ($estado_archivado === 'archivada') {
            return $this->where('id_responsable', $userId)
                        ->where('estado', 'archivada')
                        ->findAll();
        } elseif ($estado_archivado === 'no_archivada') {
            return $this->where('id_responsable', $userId)
                        ->where('estado !=', 'archivada') // O whereNotIn si tienes más estados "no activos"
                        ->findAll();
        }
        return []; // Devolver vacío si el estado no es válido
    }

    public function borrar_tarea($id_tarea)
    {
        // Considera eliminar subtareas y colaboradores asociados aquí o en el controlador
        // usando transacciones para asegurar la integridad.
        return $this->delete($id_tarea);
    }

    public function cambiar_estado_tarea($id_tarea, array $data) // Cambiado a array $data para consistencia con update
    {
        return $this->update($id_tarea, $data); // $data debería ser ['estado' => $nuevo_estado]
    }

    public function cambiar_prioridad_tarea($id_tarea, array $data) // Cambiado a array $data
    {
        return $this->update($id_tarea, $data); // $data debería ser ['prioridad' => $nueva_prioridad]
    }

    public function archivar_tarea($id_tarea, $nuevo_estado_archivado = 'archivada')
    {
        // Este método ya actualiza el estado, la lógica de verificar si está 'completada'
        // debería estar en el controlador antes de llamar a este método.
        return $this->update($id_tarea, ['estado' => $nuevo_estado_archivado]);
    }

    /**
     * Ordena las tareas de un usuario específico según el criterio.
     * @param string $criterio Columna por la cual ordenar
     * @param int $userId
     * @param string $orden 'ASC' o 'DESC'
     * @return array
     */
    public function ordenar_tareas_usuario($criterio, $userId, $orden = 'ASC')
    {
        // Validar criterio para evitar inyección SQL si $criterio viene directo de input
        $allowed_criteria = ['prioridad', 'fecha_vencimiento', 'estado', 'fecha_creacion', 'asunto']; // Añade más si es necesario
        if (!in_array($criterio, $allowed_criteria)) {
            $criterio = 'fecha_creacion'; // Criterio por defecto seguro
        }
        $orden = strtoupper($orden);
        if ($orden !== 'ASC' && $orden !== 'DESC') {
            $orden = 'ASC'; // Orden por defecto seguro
        }

        return $this->where('id_responsable', $userId)
                    ->orderBy($criterio, $orden)
                    ->findAll();
    }

    public function obtenerMisTareas($userId)
    {
        // 'asignado_a' no está en $allowedFields, asumo que es un error y debería ser 'id_responsable'
        // o necesitas añadir 'asignado_a' a $allowedFields si es una columna diferente.
        // Por ahora, usaré 'id_responsable' para consistencia.
        return $this->where('id_responsable', $userId) 
                    ->orderBy('fecha_vencimiento', 'ASC')
                    ->findAll();
    }

    // obtenerTareaActual es igual a All_tareas_user, quizás se pueda unificar.
    public function obtenerTareaActual($userId)
    {
        return $this->where('id_responsable', $userId)->findAll();
    }

    public function obtenerTareasColaborador($userId)
    {
        // Esta función requiere un JOIN con la tabla de colaboradores.
        // Asumiendo que la tabla 'colaboradores' tiene 'id_tarea' y 'id_user'.
        return $this->select('tarea.*')
                    ->join('colaboradores', 'colaboradores.id_tarea = tarea.id_tarea')
                    ->where('colaboradores.id_user', $userId)
                    // ->where('colaboradores.estado_colaborador', 'aceptada') // Opcional: solo tareas donde la colaboración fue aceptada
                    ->orderBy('tarea.fecha_vencimiento', 'ASC')
                    ->findAll();
    }

    public function guardar_tarea($data)
    {
        // Si 'id_tarea' es autoincremental, no necesitas pasarlo en $data.
        // El modelo lo manejará si $useAutoIncrement es true.
        // El método insert() de CI4 devuelve el ID insertado si tiene éxito y $useAutoIncrement es true,
        // o true/false en otros casos. getInsertID() se usa después de una inserción exitosa.
        if ($this->insert($data)) {
            return $this->getInsertID();
        }
        return false;
    }

    public function actualizar_tarea($id_tarea, $data)
    {
        return $this->update($id_tarea, $data);
    }
}
