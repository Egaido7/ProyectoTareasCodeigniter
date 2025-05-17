<?php

namespace App\Controllers\Controlador_tareas; // Asegúrate de que la ruta sea correcta

use Config\Services;
use CodeIgniter\Controller;
use App\Models\db_tareas\Tareas_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\SubTareas_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\Colaboradores_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\Colaboradores_subtareas_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\Notificaciones_db; // Asegúrate de que la ruta sea correcta
use App\Models\db_tareas\Usuario_db; // Asegúrate de que la ruta sea correcta
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
        $colaboradores_subtareas_db = new Colaboradores_subtareas_db();
        $notificaciones_db = new Notificaciones_db(); // Instanciar
        $user_id = session()->get('user_id'); 

        $tareas = $tareas_db->All_tareas_user($user_id); 

        if (is_array($tareas)) { 
            foreach ($tareas as &$tarea) { 
                if (isset($tarea['id_tarea'])) {
                    $id_tarea_actual = $tarea['id_tarea'];
                    $tarea['total_subtareas'] = $subtareas_db->Devolver_numero_subtareas($id_tarea_actual);
                    $tarea['subtareas_completadas'] = $subtareas_db->Devolver_numero_subtareas_estado($id_tarea_actual, 'completada');
                    $tarea['subtareas'] = $subtareas_db->All_subtareas($id_tarea_actual);
                }
            }
            unset($tarea); 
        }

        $subtareas_compartidas = $colaboradores_subtareas_db->getSubtareasCompartidasConUsuario($user_id);
        $notificaciones_usuario = $notificaciones_db->getAllNotificacionesUsuario($user_id, 30); // Obtener últimas 30, por ejemplo

        // Generar notificaciones de recordatorio de vencimiento (ejemplo simple)
        $this->generarNotificacionesDeRecordatorio($user_id);


        return view('vistas_tareas/index', [
            'tareas' => $tareas,
            'subtareas_compartidas' => $subtareas_compartidas,
            'notificaciones_usuario' => $notificaciones_usuario, // Pasar notificaciones a la vista
            
            'abrir_modal' => session()->getFlashdata('abrir_modal'),
            'id_tarea_modal' => session()->getFlashdata('id_tarea_modal'),
            'colaboradores_modal' => session()->getFlashdata('colaboradores_modal'),
            'abrir_modal_subtarea' => session()->getFlashdata('abrir_modal_subtarea'),
            'id_subtarea_modal' => session()->getFlashdata('id_subtarea_modal'),
            'id_tarea_padre_modal' => session()->getFlashdata('id_tarea_padre_modal'),
            'nombre_subtarea_modal' => session()->getFlashdata('nombre_subtarea_modal'),
            'colaboradores_subtarea_modal' => session()->getFlashdata('colaboradores_subtarea_modal')
        ]);
    }

     public function getEditar_tarea($id_tarea)
    {
        $tareas_db = new Tareas_db();
        $subtareas_db = new Subtareas_db();
        $colaboradores_db = new Colaboradores_db();
        
        $tarea = $tareas_db->Devolver_tarea($id_tarea);
        if (!$tarea) {
            return redirect()->to(base_url('controlador_tareas/tareas'))->with('error', 'Tarea no encontrada.');
        }

        $user_id = session()->get('user_id');
        $es_responsable = ($tarea['id_responsable'] == $user_id);
        // Asumo que tienes el correo del usuario en sesión para verificar si es colaborador
        $correo_usuario_actual = session()->get('correo'); // Asegúrate de tener 'correo' en sesión
        $es_colaborador = false;
        if($correo_usuario_actual){
            $es_colaborador = $colaboradores_db->existeColaborador($id_tarea, $correo_usuario_actual);
        }


        if (!$es_responsable && !$es_colaborador) {
            return redirect()->to(base_url('controlador_tareas/tareas'))->with('error', 'No tienes permiso para editar esta tarea.');
        }

        $colaboradores = $colaboradores_db->All_colaboradores($id_tarea);
        $subtareas = $subtareas_db->All_subtareas($id_tarea);

        return view('vistas_tareas/editar_tarea', [
            'tarea' => $tarea,
            'colaboradores' => $colaboradores,
            'subtareas' => $subtareas,
            'validation' => session()->getFlashdata('validation')
        ]);
    }
    
    public function postEditar_tarea($id_tarea)
    {
        $tareas_db = new Tareas_db();
        $tarea_actual = $tareas_db->find($id_tarea); // Usar find() si id_tarea es PK
        $user_id = session()->get('user_id');
        $colaboradores_db = new Colaboradores_db();

        if (!$tarea_actual) {
            return redirect()->to(base_url('controlador_tareas/tareas'))->with('error', 'Tarea no encontrada.');
        }

        $es_responsable = ($tarea_actual['id_responsable'] == $user_id);
        $correo_usuario_actual = session()->get('correo');
        $es_colaborador = false;
        if($correo_usuario_actual){
             $es_colaborador = $colaboradores_db->existeColaborador($id_tarea, $correo_usuario_actual);
        }

        if (!$es_responsable && !$es_colaborador) {
             return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea))
                              ->with('error', 'No tienes permiso para modificar esta tarea.');
        }
        
        $rules = [
            'asunto' => 'required|min_length[4]',
            'descripcion' => 'required|min_length[4]|max_length[255]',
            'fecha_vencimiento' => 'required|valid_date',
            'fecha_recordatorio' => 'required|valid_date',
        ];
        // $messages = [ /* tus mensajes */ ];

        if (!$this->validate($rules /*, $messages */)) {
            return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea))
                             ->withInput()
                             ->with('validation', $this->validator);
        }

        $data = [
            'asunto' => $this->request->getPost('asunto') ?? $tarea_actual['asunto'],
            'descripcion' => $this->request->getPost('descripcion') ?? $tarea_actual['descripcion'],
            'prioridad' => $this->request->getPost('prioridad') ?? $tarea_actual['prioridad'],
            'estado' => $this->request->getPost('estado') ?? $tarea_actual['estado'],
            'fecha_vencimiento' => $this->request->getPost('fecha_vencimiento') ?? $tarea_actual['fecha_vencimiento'],
            'fecha_recordatorio' => $this->request->getPost('fecha_recordatorio') ?? $tarea_actual['fecha_recordatorio'],
            'color' => $this->request->getPost('color') ?? $tarea_actual['color'],
        ];

        if ($tareas_db->actualizar_tarea($id_tarea, $data)) { 
             return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea))
                              ->with('success', 'Tarea actualizada con éxito');
        } else {
             return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea))
                              ->with('error', 'No se pudo actualizar la tarea.');
        }
    }

    public function postAgregar_tarea_editar()
    {
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
            'id_responsable' => session()->get('user_id')

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
        $user_id = session()->get('user_id');

        if (!$id_tarea || !$accion) {
            return redirect()->back()->with('error', 'Datos incompletos para la acción.');
        }

        $tarea_actual = $tareas_db->find($id_tarea);

        if (!$tarea_actual) {
            return redirect()->back()->with('error', 'Tarea no encontrada.');
        }

        $es_responsable = ($tarea_actual['id_responsable'] == $user_id);

        switch ($accion) {
            case 'editar':
                // La verificación de permisos para editar se hace en getEditar_tarea y postEditar_tarea
                return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea));

            case 'archivar':
                if (!$es_responsable) {
                    return redirect()->back()->with('error', 'No tienes permiso para archivar esta tarea.');
                }
                if ($tarea_actual['estado'] === 'completada') {
                    if ($tareas_db->archivar_tarea($id_tarea, 'archivada')) {
                        return redirect()->back()->with('success', 'Tarea archivada correctamente.');
                    } else {
                        return redirect()->back()->with('error', 'No se pudo archivar la tarea.');
                    }
                } else {
                    return redirect()->back()->with('error', 'Solo se pueden archivar tareas que estén completadas.');
                }

            case 'eliminar':
                if (!$es_responsable) {
                    return redirect()->back()->with('error', 'No tienes permiso para eliminar esta tarea.');
                }

                // Lógica para eliminar dependencias (subtareas, colaboradores) antes de eliminar la tarea
                $subtareas_db = new Subtareas_db();
                $colaboradores_db = new Colaboradores_db();
                $colaboradores_subtareas_db = new Colaboradores_subtareas_db();

                $subtareas_a_eliminar = $subtareas_db->where('id_tarea', $id_tarea)->findAll();
                foreach ($subtareas_a_eliminar as $sub) {
                    $colaboradores_subtareas_db->where('id_subtarea', $sub['id_subtarea'])->delete();
                }
                $subtareas_db->where('id_tarea', $id_tarea)->delete();
                $colaboradores_db->where('id_tarea', $id_tarea)->delete();


                if ($tareas_db->borrar_tarea($id_tarea)) {
                    return redirect()->to(base_url('controlador_tareas/tareas'))->with('success', 'Tarea y sus elementos asociados eliminados correctamente.');
                } else {
                    return redirect()->back()->with('error', 'No se pudo eliminar la tarea.');
                }
            default:
                return redirect()->back()->with('error', 'Acción no reconocida.');
        }
    }


     public function postAgregar_colaborador()
    {
        $id_tarea_a_compartir = $this->request->getPost('id_tarea'); // ID de la tarea a la que se invita
        $correo_invitado = $this->request->getPost('correo');
        $id_usuario_actual = session()->get('user_id');
        $nombre_usuario_actual = session()->get('usuario'); // Nombre del usuario que invita

        $tareas_db = new Tareas_db();
        $tarea_actual = $tareas_db->find($id_tarea_a_compartir);
        $usuario_db = new Usuario_db(); // Para obtener el ID del invitado
        $notificaciones_db = new Notificaciones_db();

        if (!$tarea_actual) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('error', 'Tarea no encontrada.');
        }
        if ($tarea_actual['id_responsable'] != $id_usuario_actual) {
             return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('error', 'No tienes permiso para agregar colaboradores a esta tarea.');
        }
        
        if (empty($correo_invitado) || !filter_var($correo_invitado, FILTER_VALIDATE_EMAIL)) {
             return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('error', 'Correo inválido.')
                             ->withInput();
        }

        $info_invitado = $usuario_db->Devolver_usuario($correo_invitado);
        if (!$info_invitado || !isset($info_invitado['id_user'])) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('error', 'El usuario invitado no existe en la base de datos.');
        }
        $id_usuario_invitado = $info_invitado['id_user'];

        // Evitar auto-invitación
        if ($id_usuario_invitado == $id_usuario_actual) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('error', 'No puedes invitarte a ti mismo.');
        }

        $colaboradores_db = new Colaboradores_db();
        if ($colaboradores_db->existeColaborador($id_tarea_a_compartir, $correo_invitado)) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('error', 'El usuario ya es colaborador de esta tarea.');
        }
        
        // Verificar si ya hay una invitación pendiente
        if ($notificaciones_db->existeInvitacionPendiente($id_usuario_invitado, 'invitacion_tarea', $id_tarea_a_compartir)) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('info', 'Ya existe una invitación pendiente para este usuario en esta tarea.');
        }

        // Insertar colaborador con estado 'pendiente' (o directamente 'aceptada' si no hay flujo de aceptación)
        if ($colaboradores_db->Insertar_colaborador($id_tarea_a_compartir, $correo_invitado, 'pendiente')) { // Asumiendo que Insertar_colaborador acepta un tercer parámetro para el estado
            
            // Crear notificación para el usuario invitado
            $mensaje_notif = esc($nombre_usuario_actual) . " te ha invitado a colaborar en la tarea: \"" . esc($tarea_actual['asunto']) . "\".";
            $notificaciones_db->crearNotificacion([
                'id_usuario_destino' => $id_usuario_invitado,
                'tipo_notificacion' => 'invitacion_tarea',
                'mensaje' => $mensaje_notif,
                'id_entidad_principal' => $id_tarea_a_compartir,
                'tipo_entidad_principal' => 'tarea',
                'id_entidad_relacionada' => $id_usuario_actual, // Quién invita
                'tipo_entidad_relacionada' => 'usuario',
                'datos_adicionales' => ['nombre_tarea' => $tarea_actual['asunto'], 'nombre_invitador' => $nombre_usuario_actual]
            ]);

            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('success', 'Invitación enviada al colaborador.');
        } else {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('error', 'No se pudo agregar el colaborador.');
        }
    }

    public function postEliminar_colaborador()
    {
        $id_tarea = $this->request->getPost('id_tarea');
        $correo = $this->request->getPost('correo');
        $user_id = session()->get('user_id');
        $tareas_db = new Tareas_db();
        $tarea_actual = $tareas_db->find($id_tarea);

        if (!$tarea_actual) {
             return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea))
                             ->with('error', 'Tarea no encontrada.');
        }
        if ($tarea_actual['id_responsable'] != $user_id) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea))
                             ->with('error', 'No tienes permiso para eliminar colaboradores de esta tarea.');
        }
        
        if (empty($correo)) {
             return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea))
                             ->with('error', 'Correo no proporcionado.');
        }
        $colaboradores_db = new Colaboradores_db();
        if ($colaboradores_db->Eliminar_colaborador($id_tarea, $correo)) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea))
                ->with('success', 'Colaborador eliminado');
        } else {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea))
                ->with('error', 'No se pudo eliminar el colaborador');
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
    public function postEliminar_subtarea()
    {
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
            'abrir_modal' => true,
            'abrir_modal_subtarea' => false // Asegurarse que el modal de subtarea no se abra

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

      private function generarNotificacionesDeRecordatorio($user_id)
    {
        $tareas_db = new Tareas_db();
        $notificaciones_db = new Notificaciones_db();

        // Obtener tareas del usuario con fecha_recordatorio en los próximos X días (ej. 3 días)
        // y que aún no estén completadas o archivadas.
        $fecha_limite_recordatorio = date('Y-m-d H:i:s', strtotime('+3 days'));
        $fecha_actual = date('Y-m-d H:i:s');

        $tareas_a_recordar = $tareas_db->where('id_responsable', $user_id)
                                    ->where('fecha_recordatorio <=', $fecha_limite_recordatorio)
                                    ->where('fecha_recordatorio >=', $fecha_actual) // Solo recordatorios futuros o de hoy
                                    ->whereNotIn('estado', ['completada', 'archivada'])
                                    ->findAll();
        
        foreach ($tareas_a_recordar as $tarea) {
            // Verificar si ya existe una notificación de recordatorio reciente para esta tarea para evitar duplicados
            $existe_notif = $notificaciones_db->where('id_usuario_destino', $user_id)
                                            ->where('tipo_notificacion', 'recordatorio_vencimiento')
                                            ->where('id_entidad_principal', $tarea['id_tarea'])
                                            ->where('fecha_creacion >=', date('Y-m-d H:i:s', strtotime('-1 day'))) // Ej: no más de una por día
                                            ->first();
            if (!$existe_notif) {
                $mensaje = "Recordatorio: La tarea \"" . esc($tarea['asunto']) . "\" vence pronto (el " . date('d/m/Y', strtotime($tarea['fecha_vencimiento'])) . ").";
                $notificaciones_db->crearNotificacion([
                    'id_usuario_destino' => $user_id,
                    'tipo_notificacion' => 'recordatorio_vencimiento',
                    'mensaje' => $mensaje,
                    'id_entidad_principal' => $tarea['id_tarea'],
                    'tipo_entidad_principal' => 'tarea',
                    'datos_adicionales' => ['nombre_tarea' => $tarea['asunto'], 'fecha_vencimiento' => $tarea['fecha_vencimiento']]
                ]);
            }
        }
    }
}
