<?php

namespace App\Controllers\Controlador_tareas; // Asegúrate de que la ruta sea correcta

use Config\Services;
use CodeIgniter\Controller;
use App\Models\db_tareas\Tareas_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\SubTareas_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\Colaboradores_db; // Asegúrate de que la ruta sea correcta
use App\Controllers\BaseController;

class Tareas extends BaseController
{
    public function __construct()
    {
        helper('form');
        $session = \Config\Services::session();
    }

    public function getIndex()
    {
        $tareas_db = new Tareas_db();
        $subtareas_db = new Subtareas_db();
        $user = session()->get('user_id'); // Obtener el ID del usuario de la sesión

        // Obtén todas las tareas
        $tareas = $tareas_db->All_tareas_user($user);  //MANEJAR CON S

        // Agrega los datos de subtareas a cada tarea
        foreach ($tareas as &$tarea) {
            $id_tarea = $tarea['id_tarea'];

            // Calcula el total de subtareas y las completadas
            $tarea['total_subtareas'] = $subtareas_db->Devolver_numero_subtareas($id_tarea);
            $tarea['subtareas_completadas'] = $subtareas_db->Devolver_numero_subtareas_estado($id_tarea, 'completada');
            $tarea['subtareas'] = $subtareas_db->All_subtareas($id_tarea);
        }

        return view('vistas_tareas/index', ['tareas' => $tareas]);

        /*
            //return view('/vistas_tareas/index'); 
        $tareas_db = new Tareas_db();
        //$tareas = $tareas_db->All_tareas_user(session()->get('user_id'));
        $tareas = $tareas_db->All_tareas_user(1);  //MANEJAR CON SESSION
        return view('vistas_tareas/index', ['tareas' => $tareas]);
        */
    }

    public function getEditar_tarea($id_tarea)
    {
        $tareas_db = new Tareas_db();
        $subtareas_db = new SubTareas_db();
        $colaboradores_db = new Colaboradores_db();
        $tarea = $tareas_db->Devolver_tarea($id_tarea);
        $colaboradores = $colaboradores_db->All_colaboradores($id_tarea);
        $subtareas = $subtareas_db->All_subtareas($id_tarea);

        return view('vistas_tareas/editar_tarea', [
            'tarea' => $tarea,
            'colaboradores' => $colaboradores,
            'subtareas' => $subtareas
        ]);
    }

    public function postEditar_tarea($id_tarea)
    {
        $validation = \Config\Services::validation();
        // Reglas y mensajes igual que en postGuardar_tarea()

        // Reglas de validación
        $rules = [
            'asunto' => 'required|min_length[4]',
            'descripcion' => 'required|min_length[4] |max_length[255]',
            'fecha_vencimiento' => 'required|valid_date',
            'fecha_recordatorio' => 'required|valid_date',
           // 'colaboradores' => 'valid_email',
            //'subtarea_nombre' => 'min_length[4]',
            //'subtarea_descripcion' => 'min_length[4]',
            //'subtarea_fecha_vencimiento' => 'valid_date',
            //'subtarea_comentario' => 'min_length[4]',
        ];

        // Mensajes personalizados
        $messages = [
            'asunto' => [
                'required' => 'El campo correo es obligatorio',
                'min_length' => 'El tema debe tener al menos 4 caracteres',
            ],
            'descripcion' => [
                'required' => 'El campo contraseña es obligatorio',
                'min_length' => 'La descripcion debe tener al menos 8 caracteres',
                'max_length' => 'La descripcion no puede tener más de 255 caracteres',
            ],
            'fecha_vencimiento' => [
                'required' => 'El campo fecha_vencimiento es obligatorio',
                'valid_date' => 'La fecha de vencimiento no es válida',
            ],
            'fecha_recordatorio' => [
                'required' => 'El campo fecha_recibido es obligatorio',
                'valid_date' => 'La fecha de recibido no es válida',
            ],

        ];

        if (!$this->validate($rules, $messages)) {
            // Recarga la vista con los errores
            $tareas_db = new Tareas_db();
            $subtareas_db = new SubTareas_db();
            $colaboradores_db = new Colaboradores_db();
            return view('vistas_tareas/editar_tarea', [
                'tarea' => $tareas_db->Devolver_tarea($id_tarea),
                'colaboradores' => $colaboradores_db->All_colaboradores($id_tarea),
                'subtareas' => $subtareas_db->All_subtareas($id_tarea),
                'validation' => $this->validator
            ]);
        }

        // Actualizar tarea principal
        // Si la validación pasa, guarda los datos en la base de datos
        $asunto = $this->request->getPost('asunto') ?? '';
        $estado = $this->request->getPost('estado') ?? '';
        $descripcion = $this->request->getPost('descripcion') ?? '';
        $prioridad = $this->request->getPost('prioridad') ?? '';
        $fecha_vencimiento = $this->request->getPost('fecha_vencimiento') ?? '';
        $fecha_recordatorio = $this->request->getPost('fecha_recordatorio') ?? '';
        $color = $this->request->getPost('color') ?? '';
       // $subtarea_nombre = $this->request->getPost('subtarea_nombre') ?? '';
        //$subtarea_descripcion = $this->request->getPost('subtarea_descripcion') ?? '';
        //$subtarea_fecha_vencimiento = $this->request->getPost('subtarea_fecha_vencimiento') ?? '';
        //$subtarea_comentario = $this->request->getPost('subtarea_comentario') ?? '';
        //$subtarea_estado = $this->request->getPost('subtarea_estado') ?? '';
        //$subtarea_prioridad = $this->request->getPost('subtarea_prioridad') ?? '';
        //$subtarea_responsable = $this->request->getPost('subtarea_responsable') ?? '';
        $id_responsable = session()->get('user_id');


        $data = [
            'asunto' => $asunto,
            'descripcion' => $descripcion,
            'prioridad' => $prioridad,
            'estado' => $estado,
            'fecha_vencimiento' => $fecha_vencimiento,
            'fecha_recordatorio' => $fecha_recordatorio,
            'color' => $color,
            'id_responsable' => $id_responsable,
        ];

        $tareas_db = new Tareas_db();
        $tareas_db->actualizar_tarea($id_tarea, $data);

        // Si quieres actualizar colaboradores o subtareas, puedes hacerlo aquí

        /*$colaboradores_json = $this->request->getPost('colaboradores');
    $colaboradores = [];
    if ($colaboradores_json) {
        $colaboradores = json_decode($colaboradores_json, true);
        if (!is_array($colaboradores)) {
            $colaboradores = [];
        }
    }

    // Inserta cada colaborador (ajusta tu método Insertar_colaborador si es necesario)
    $colaboradores_db = new Colaboradores_db();
    foreach ($colaboradores as $correo) {
        $colaboradores_db->Insertar_colaborador($id_tarea, $correo);
    }

    */
    /*
        // Recibe el JSON de subtareas
        $subtareas_json = $this->request->getPost('subtareas');
        $subtareas = [];
        if ($subtareas_json) {
            $subtareas = json_decode($subtareas_json, true);
            if (!is_array($subtareas)) {
                $subtareas = [];
            }
        }


         $subtareas_db = new SubTareas_db();
        foreach ($subtareas as $sub) {
            $datasubtarea = [
                'id_tarea' => $id_tarea,
                'nombre' => $sub['nombre'],
                'descripcion' => $sub['descripcion'],
                'prioridad' => $sub['prioridad'],
                'estado' => $sub['estado'],
                'fecha_vencimiento' => $sub['vencimiento'],
                'comentario' => $sub['comentario'],
                'id_responsable' => $id_responsable,
            ];

            $subtareas_db->Insertar_subtarea($datasubtarea);
        } */
       


        return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea))
            ->with('success', 'Tarea actualizada con éxito');
    }

    public function postAgregar_tarea_editar(){
        $validation = \Config\Services::validation();
         // Reglas de validación
       
        $rules = [
            'subtarea_nombre' => 'min_length[4]',
            'subtarea_descripcion' => 'min_length[4]',
            'subtarea_fecha_vencimiento' => 'valid_date',
            'subtarea_comentario' => 'min_length[4]',
        ];

        // Mensajes personalizados
        $messages = [
            'subtarea_nombre' => [
                'min_length' => 'El nombre de la subtarea debe tener al menos 4 caracteres',
            ],
            'subtarea_descripcion' => [
                'min_length' => 'La descripcion de la subtarea debe tener al menos 4 caracteres',
            ],
            'subtarea_fecha_vencimiento' => [
                'valid_date' => 'La fecha de vencimiento de la subtarea no es válida',
            ],
            'subtarea_comentario' => [
                'min_length' => 'El comentario de la subtarea debe tener al menos 4 caracteres',
            ],

        ];

        $id_tarea = $this->request->getPost('id_tarea');
        $nombre = $this->request->getPost('subtarea_nombre');
        $descripcion = $this->request->getPost('subtarea_descripcion');
        $estado = $this->request->getPost('subtarea_estado');
        $fecha_vencimiento = $this->request->getPost('subtarea_fecha_vencimiento');
        $prioridad = $this->request->getPost('subtarea_prioridad');
        $comentario = $this->request->getPost('subtarea_comentario');

         if (!$this->validate($rules, $messages)) {
        // Recarga la vista con los errores
        $tareas_db = new Tareas_db();
        $subtareas_db = new SubTareas_db();
        $colaboradores_db = new Colaboradores_db();
        return view('vistas_tareas/editar_tarea', [
            'tarea' => $tareas_db->Devolver_tarea($id_tarea),
            'colaboradores' => $colaboradores_db->All_colaboradores($id_tarea),
            'subtareas' => $subtareas_db->All_subtareas($id_tarea),
            'validation' => $this->validator
        ]);
         }

          $subtareas_db = new SubTareas_db();
          $data = [
        'id_tarea' => $id_tarea,
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'estado' => $estado,
        'fecha_vencimiento' => $fecha_vencimiento,
        'prioridad' => $prioridad,
        'comentario' => $comentario,
        'id_responsable' =>session()->get('user_id')

         ];

         if ($subtareas_db->Insertar_subtarea($data)) {
        return redirect()->back()->with('success', 'Subtarea agregada');
    } else {
        return redirect()->back()->with('error', 'No se pudo agregar la subtarea');
    }
    

    }


    public function getMostrar_tareas()
    {
        $tareas_db = new Tareas_db();
        $subtareas_db = new Subtareas_db();

        // Obtén todas las tareas
        $tareas = $tareas_db->All_tareas_user(1);  //MANEJAR CON S

        // Agrega los datos de subtareas a cada tarea
        foreach ($tareas as &$tarea) {
            $id_tarea = $tarea['id_tarea'];

            // Calcula el total de subtareas y las completadas
            $tarea['total_subtareas'] = $subtareas_db->Devolver_numero_subtareas($id_tarea);
            $tarea['subtareas_completadas'] = $subtareas_db->Devolver_numero_subtareas_estado($id_tarea, 'completada');
        }

        return view('vistas_tareas/index', ['tareas' => $tareas]);
    }

    //Metodo para filtrar tareas 
    public function getFiltro_estado()
    {
        $estado = $this->request->getGet('estado'); // Obtén el valor del parámetro 'estado'

        if (!$estado || $estado == 'todas') {
            return redirect()->to(base_url('controlador_tareas/tareas')); // Redirige si no hay estado
        }

        $tareas_db = new Tareas_db();
        $tareas = $tareas_db->Devolver_tarea_estado($estado);

        return view('vistas_tareas/index', ['tareas' => $tareas]);
    }


    public function getFiltro_prioridad()
    {
        $prioridad = $this->request->getGet('prioridad'); // Obtén el valor del parámetro 'prioridad'
        if (!$prioridad || $prioridad == 'todas') {
            return redirect()->to(base_url('controlador_tareas/tareas')); // Redirige si no hay prioridad
        }
        $tareas_db = new Tareas_db();
        $tareas = $tareas_db->Devolver_tarea_prioridad($prioridad);

        return view('vistas_tareas/index', ['tareas' => $tareas]);
    }

    public function getOrdenar_tareas()
    {
        $criterio = $this->request->getGet('criterio'); // Obtén el valor del parámetro 'criterio'
        if (!$criterio) {
            return redirect()->to(base_url('controlador_tareas/tareas')); // Redirige si no hay criterio
        }
        $tareas_db = new Tareas_db();
        $tareas = [];

        switch ($criterio) {
            case 'prioridad':
                $tareas = $tareas_db->ordenar_tareas('prioridad');
                break;
            case 'fecha_vencimiento':
                $tareas = $tareas_db->ordenar_tareas('fecha_vencimiento');
                break;
            case 'estado':
                $tareas = $tareas_db->ordenar_tareas('estado');
                break;
            case 'fecha_creacion':
                $tareas = $tareas_db->ordenar_tareas('fecha_creacion');
                break;
            case 'mis_tareas':
                $tareas = $tareas_db->obtenerMisTareas(session()->get('user_id'));
                break;
            case 'colaborador':
                $tareas = $tareas_db->obtenerTareasColaborador(session()->get('user_id'));
                break;
            default:
                $tareas = $tareas_db->findAll();
                break;
        }

        return view('vistas_tareas/index', ['tareas' => $tareas]);
    }

    public function getFiltro_archivar()
    {
        $estado_archivado = $this->request->getGet('estado_archivado'); // Obtén el valor del parámetro 'estado_archivado'
        if (!$estado_archivado || $estado_archivado != 'archivada') {
            return redirect()->to(base_url('controlador_tareas/tareas')); // Redirige si no hay estado_archivado
        }
        $tareas_db = new Tareas_db();
        $tareas = $tareas_db->Devolver_tarea_archivada($estado_archivado);
        return view('vistas_tareas/index', ['tareas' => $tareas]);
    }


    public function postAccion_tarea()
    {
        $id_tarea = $this->request->getPost('id_tarea');
        $accion = $this->request->getPost('accion');
        $tareas_db = new Tareas_db();

        if (!$id_tarea || !$accion) {
            return redirect()->back()->with('error', 'Datos incompletos');
        }

        switch ($accion) {
            case 'archivar':
                $tareas_db->archivar_tarea($id_tarea, 'archivada');
                return redirect()->back()->with('success', 'Tarea archivada');
            case 'eliminar':
                $tareas_db->borrar_tarea($id_tarea);
                return redirect()->back()->with('success', 'Tarea eliminada');
            default:
                return redirect()->back();
        }
    }


    public function postAgregar_colaborador()
    {
        $id_tarea = $this->request->getPost('id_tarea');
        $correo = $this->request->getPost('correo');
        $colaboradores_db = new Colaboradores_db();
        if ($colaboradores_db->existeColaborador($id_tarea, $correo)) {
            // Ya existe, muestra mensaje de error o ignora
            return redirect()->back()->with('error', 'El usuario ya fue invitado a esta tarea.');
        } else {
            if ($colaboradores_db->Insertar_colaborador($id_tarea, $correo)) {
                redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea))
                    ->with('success', 'Colaborador agregado');
            } else {
                return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea))
                    ->with('error', 'No se pudo agregar el colaborador, no existe en la base de datos');
            }
        }
    }



    public function postAgregar_colaborador_editar()
    {
        $id_tarea = $this->request->getPost('id_tarea');
        $correo = $this->request->getPost('correo');
        $colaboradores_db = new Colaboradores_db();
        if ($colaboradores_db->existeColaborador($id_tarea, $correo)) {
            return redirect()->back()->with('error', 'El usuario ya fue invitado a esta tarea.');
        } else {
            if ($colaboradores_db->Insertar_colaborador($id_tarea, $correo)) {
                return redirect()->back()->with('success', 'Colaborador agregado');
            } else {
                return redirect()->back()->with('error', 'No se pudo agregar el colaborador porque no existe en la base de datos');
            }
        }
    }

    public function postEliminar_colaborador()
    {
        $id_tarea = $this->request->getPost('id_tarea');
        $correo = $this->request->getPost('correo');
        $colaboradores_db = new Colaboradores_db();

        if ($colaboradores_db->Eliminar_colaborador($id_tarea, $correo)) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea))
                ->with('success', 'Colaborador eliminado');
        } else {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea))
                ->with('error', 'No se pudo eliminar el colaborador');
        }
    }

    public function postEliminar_subtarea(){
            $id_tarea = $this->request->getPost('id_tarea');
    $id_subtarea = $this->request->getPost('id_subtarea');
    $subtareas_db = new Subtareas_db();
        if ($subtareas_db->Eliminar_subtarea($id_tarea, $id_subtarea)) {
        return redirect()->back()->with('success', 'Subtarea eliminada correctamente');
    } else {
        return redirect()->back()->with('error', 'No se pudo eliminar la subtarea');
    }
    }

    public function getCompartir()
    {
        $id_tarea = $this->request->getGet('id_tarea');
        $colaboradores_db = new Colaboradores_db();
        $colaboradores = $colaboradores_db->All_colaboradores($id_tarea);

        // Puedes volver a cargar las tareas si quieres que la vista siga igual
        $tareas_db = new Tareas_db();
        $tareas = $tareas_db->findAll();

        return view('vistas_tareas/index', [
            'tareas' => $tareas,
            'colaboradores' => $colaboradores,
            'id_tarea_modal' => $id_tarea,
            'abrir_modal' => true
        ]);
    }

    public function getNueva_tarea()
    {
        return view('vistas_tareas/nueva_tarea');
    }

    public function postGuardar_tarea()
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        $rules = [
            'asunto' => 'required|min_length[4]',
            'descripcion' => 'required|min_length[4] |max_length[255]',
            'fecha_vencimiento' => 'required|valid_date',
            'fecha_recordatorio' => 'required|valid_date',
            'colaboradores' => 'valid_email',
            'subtarea_nombre' => 'min_length[4]',
            'subtarea_descripcion' => 'min_length[4]',
            'subtarea_fecha_vencimiento' => 'valid_date',
            'subtarea_comentario' => 'min_length[4]',
        ];

        // Mensajes personalizados
        $messages = [
            'asunto' => [
                'required' => 'El campo correo es obligatorio',
                'min_length' => 'El tema debe tener al menos 4 caracteres',
            ],
            'descripcion' => [
                'required' => 'El campo contraseña es obligatorio',
                'min_length' => 'La descripcion debe tener al menos 8 caracteres',
                'max_length' => 'La descripcion no puede tener más de 255 caracteres',
            ],
            'fecha_vencimiento' => [
                'required' => 'El campo fecha_vencimiento es obligatorio',
                'valid_date' => 'La fecha de vencimiento no es válida',
            ],
            'fecha_recordatorio' => [
                'required' => 'El campo fecha_recibido es obligatorio',
                'valid_date' => 'La fecha de recibido no es válida',
            ],
            'colaboradores' => [
                'valid_email' => 'El correo no es válido',
            ],
            'subtarea_nombre' => [
                'min_length' => 'El nombre de la subtarea debe tener al menos 4 caracteres',
            ],
            'subtarea_descripcion' => [
                'min_length' => 'La descripcion de la subtarea debe tener al menos 4 caracteres',
            ],
            'subtarea_fecha_vencimiento' => [
                'valid_date' => 'La fecha de vencimiento de la subtarea no es válida',
            ],
            'subtarea_comentario' => [
                'min_length' => 'El comentario de la subtarea debe tener al menos 4 caracteres',
            ],

        ];

        // Validar los datos
        if (!$this->validate($rules, $messages)) {
            // Si la validación falla, redirige a la vista con los errores
            return view('/vistas_tareas/nueva_tarea', [
                'validation' => $this->validator,
            ]);
        }

        // Si la validación pasa, guarda los datos en la base de datos
        $asunto = $this->request->getPost('asunto') ?? '';
        $estado = $this->request->getPost('estado') ?? '';
        $descripcion = $this->request->getPost('descripcion') ?? '';
        $prioridad = $this->request->getPost('prioridad') ?? '';
        $fecha_vencimiento = $this->request->getPost('fecha_vencimiento') ?? '';
        $fecha_recordatorio = $this->request->getPost('fecha_recordatorio') ?? '';
        $color = $this->request->getPost('color') ?? '';
        $subtarea_nombre = $this->request->getPost('subtarea_nombre') ?? '';
        $subtarea_descripcion = $this->request->getPost('subtarea_descripcion') ?? '';
        $subtarea_fecha_vencimiento = $this->request->getPost('subtarea_fecha_vencimiento') ?? '';
        $subtarea_comentario = $this->request->getPost('subtarea_comentario') ?? '';
        $subtarea_estado = $this->request->getPost('subtarea_estado') ?? '';
        $subtarea_prioridad = $this->request->getPost('subtarea_prioridad') ?? '';
        $subtarea_responsable = $this->request->getPost('subtarea_responsable') ?? '';
        $id_responsable = session()->get('user_id');


        $data = [
            'asunto' => $asunto,
            'descripcion' => $descripcion,
            'prioridad' => $prioridad,
            'estado' => $estado,
            'fecha_vencimiento' => $fecha_vencimiento,
            'fecha_recordatorio' => $fecha_recordatorio,
            'color' => $color,
            'id_responsable' => $id_responsable,
        ];

        $tareas_db = new Tareas_db();
        $tareas_db->guardar_tarea($data);
        $id_tarea = $tareas_db->insertID();

        $colaboradores_json = $this->request->getPost('colaboradores');
        $colaboradores = [];
        if ($colaboradores_json) {
            $colaboradores = json_decode($colaboradores_json, true);
            if (!is_array($colaboradores)) {
                $colaboradores = [];
            }
        }
        // Inserta cada colaborador (ajusta tu método Insertar_colaborador si es necesario)
        $colaboradores_db = new Colaboradores_db();
        foreach ($colaboradores as $correo) {
            $colaboradores_db->Insertar_colaborador($id_tarea, $correo);
        }

        // Recibe el JSON de subtareas
        $subtareas_json = $this->request->getPost('subtareas');
        $subtareas = [];
        if ($subtareas_json) {
            $subtareas = json_decode($subtareas_json, true);
            if (!is_array($subtareas)) {
                $subtareas = [];
            }
        }

        $subtareas_db = new SubTareas_db();
        foreach ($subtareas as $sub) {
            $datasubtarea = [
                'id_tarea' => $id_tarea,
                'nombre' => $sub['nombre'],
                'descripcion' => $sub['descripcion'],
                'prioridad' => $sub['prioridad'],
                'estado' => $sub['estado'],
                'fecha_vencimiento' => $sub['vencimiento'],
                'comentario' => $sub['comentario'],
                'id_responsable' => $id_responsable,
            ];

            $subtareas_db->Insertar_subtarea($datasubtarea);
        }

        return redirect()->to(base_url('controlador_tareas/tareas'))
            ->with('success', 'Tarea actualizada con éxito');
    }

    
}
