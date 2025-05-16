<?php namespace App\Models\db_tareas; // Asegúrate de que la ruta sea correcta
use CodeIgniter\Model;
class Usuario_db extends Model

{ protected $table = 'usuario'; protected $primaryKey = 'id_user';
protected $useAutoIncrement = true; protected $returnType = 'array';
protected $useSoftDeletes = false;
protected $allowedFields = ['nombre', 'apellido', 'correo', 'id_user', 'contrasenia'];
protected $useTimestamps = false; // Dates
protected $dateFormat = 'datetime';
protected $createdField = 'created_at';
protected $updatedField = 'updated_at';
protected $deletedField = 'deleted_at';
protected $validationRules = []; // Validation
protected $validationMessages = [];
protected $skipValidation = false;
protected $cleanValidationRules = true;

public function All_usuarios()
{
    return $this->findAll();
}

public function Devolver_usuario($usuario)
{
    // Busca el usuario en la base de datos y devuelve el primer resultado
    return $this->where('correo', $usuario)->first();
}

public function Devolver_contraseña($contrasenia)
{
    // Busca la contraseña en la base de datos y devuelve el primer resultado
    return $this->where('contrasenia', $contrasenia)->first();
}

public function Guardar_usuario($data)
{
    // Guarda el nuevo usuario en la base de datos
    return $this->insert($data);
}



}