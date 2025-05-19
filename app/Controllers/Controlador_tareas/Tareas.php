<?php

namespace App\Controllers\Controlador_tareas;

use App\Models\db_tareas\Tareas_db; 
use App\Models\db_tareas\Subtareas_db; 
use App\Models\db_tareas\Colaboradores_db;
use App\Models\db_tareas\Colaboradores_subtareas_db;
use App\Models\db_tareas\Notificaciones_db;
use App\Models\db_tareas\Usuario_db; 
use App\Controllers\BaseController;

class Tareas extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url', 'text']); 
    }

    // Método helper para cargar datos comunes para la vista index
    private function _prepareIndexData(array $tareas_filtradas_ordenadas = null)
    {
        $tareas_db = new Tareas_db();
        $subtareas_db = new Subtareas_db();
        $colaboradores_subtareas_db = new Colaboradores_subtareas_db();
        $notificaciones_db = new Notificaciones_db(); 
        $user_id = session()->get('user_id'); 

        // Si no se pasan tareas filtradas, obtener todas las del usuario
        $tareas_a_procesar = $tareas_filtradas_ordenadas ?? $tareas_db->All_tareas_user($user_id); 

        if (is_array($tareas_a_procesar)) { 
            foreach ($tareas_a_procesar as &$tarea) { 
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
        
       
        $notificaciones_usuario = $notificaciones_db->getAllNotificacionesUsuario($user_id, 30); 

        // Generar notificaciones de recordatorio de vencimiento
        $this->generarNotificacionesDeRecordatorio($user_id);

        return [
            'tareas' => $tareas_a_procesar,
            'subtareas_compartidas' => $subtareas_compartidas,
            'notificaciones_usuario' => $notificaciones_usuario, 
            
            // Datos para modales (se recuperan de flashdata si existen)
            'abrir_modal' => session()->getFlashdata('abrir_modal'),
            'id_tarea_modal' => session()->getFlashdata('id_tarea_modal'),
            'colaboradores_modal' => session()->getFlashdata('colaboradores_modal'),
            'abrir_modal_subtarea' => session()->getFlashdata('abrir_modal_subtarea'),
            'id_subtarea_modal' => session()->getFlashdata('id_subtarea_modal'),
            'id_tarea_padre_modal' => session()->getFlashdata('id_tarea_padre_modal'),
            'nombre_subtarea_modal' => session()->getFlashdata('nombre_subtarea_modal'),
            'colaboradores_subtarea_modal' => session()->getFlashdata('colaboradores_subtarea_modal')
        ];
    }

    public function getIndex()
    {
        $data = $this->_prepareIndexData();
        return view('vistas_tareas/index', $data);
    }

    public function getFiltro_estado()
    {
        $estado = $this->request->getGet('estado'); 
        $user_id = session()->get('user_id');
        $tareas_db = new Tareas_db();
        $tareas_filtradas = [];

        if (!$estado || $estado == 'todas') {
            $tareas_filtradas = $tareas_db->All_tareas_user($user_id);
        } else {
            $tareas_filtradas = $tareas_db->Devolver_tarea_estado($estado, $user_id); 
        }
        
        $data = $this->_prepareIndexData($tareas_filtradas);
        return view('vistas_tareas/index', $data);
    }

    public function getFiltro_prioridad()
    {
        $prioridad = $this->request->getGet('prioridad'); 
        $user_id = session()->get('user_id');
        $tareas_db = new Tareas_db();
        $tareas_filtradas = [];

        if (!$prioridad || $prioridad == 'todas') {
            $tareas_filtradas = $tareas_db->All_tareas_user($user_id);
        } else {
            $tareas_filtradas = $tareas_db->Devolver_tarea_prioridad($prioridad, $user_id); 
        }

        $data = $this->_prepareIndexData($tareas_filtradas);
        return view('vistas_tareas/index', $data);
    }
    
    public function getOrdenar_tareas()
    {
        $criterio = $this->request->getGet('criterio'); 
        $user_id = session()->get('user_id');
        $tareas_db = new Tareas_db();
        $tareas_ordenadas = [];

        if (!$criterio) {
            $tareas_ordenadas = $tareas_db->All_tareas_user($user_id); 
        } else {
            switch ($criterio) {
                case 'prioridad':
                case 'fecha_vencimiento':
                case 'estado':
                case 'fecha_creacion':
                    $tareas_ordenadas = $tareas_db->ordenar_tareas_usuario($criterio, $user_id); 
                    break;
                case 'mis_tareas':
                    $tareas_ordenadas = $tareas_db->obtenerMisTareas($user_id); 
                    break;
                case 'colaborador':
                    $tareas_ordenadas = $tareas_db->obtenerTareasColaborador($user_id); 
                    break;
                default:
                    $tareas_ordenadas = $tareas_db->All_tareas_user($user_id);
                    break;
            }
        }
        $data = $this->_prepareIndexData($tareas_ordenadas);
        return view('vistas_tareas/index', $data);
    }

    public function getFiltro_archivar()
    {
        $estado_archivado = $this->request->getGet('estado_archivado'); 
        $user_id = session()->get('user_id');
        $tareas_db = new Tareas_db();
        $tareas_filtradas = [];

        if (!$estado_archivado || !in_array($estado_archivado, ['archivada', 'no_archivada'])) { 
            $tareas_filtradas = $tareas_db->Devolver_tarea_archivada('no_archivada', $user_id); // Mostrar no archivadas por defecto
        } elseif ($estado_archivado === 'archivada') {
            $tareas_filtradas = $tareas_db->Devolver_tarea_archivada('archivada', $user_id); 
        } else { 
            $tareas_filtradas = $tareas_db->Devolver_tarea_archivada('no_archivada', $user_id); 
        }
        
        $data = $this->_prepareIndexData($tareas_filtradas);
        return view('vistas_tareas/index', $data);
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
        $correo_usuario_actual = session()->get('correo'); 
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
        $tarea_actual = $tareas_db->find($id_tarea); 
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

        if (!$this->validate($rules /*, $messages (opcional) */)) {
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

    public function postAgregar_tarea_editar(){
        $validation = \Config\Services::validation();
        $rules = [
            'subtarea_nombre' => 'permit_empty|min_length[4]', 
            'subtarea_descripcion' => 'permit_empty|min_length[4]',
            'subtarea_fecha_vencimiento' => 'permit_empty|valid_date',
            'subtarea_comentario' => 'permit_empty|min_length[4]',
        ];
        // $messages = [ /* tus mensajes */ ]; 

        $id_tarea = $this->request->getPost('id_tarea');
        
        if (!$this->validate($rules /*, $messages */)) {
            $tareas_db = new Tareas_db();
            $subtareas_db = new Subtareas_db(); // Corregido a Subtareas_db
            $colaboradores_db = new Colaboradores_db();
            return view('vistas_tareas/editar_tarea', [
                'tarea' => $tareas_db->Devolver_tarea($id_tarea),
                'colaboradores' => $colaboradores_db->All_colaboradores($id_tarea),
                'subtareas' => $subtareas_db->All_subtareas($id_tarea),
                'validation' => $this->validator 
            ]);
        }

        $nombre = $this->request->getPost('subtarea_nombre');
        if (!empty($nombre)) {
            $datasubtarea = [
                'id_tarea' => $id_tarea,
                'nombre' => $nombre,
                'descripcion' => $this->request->getPost('subtarea_descripcion') ?? '',
                'estado' => $this->request->getPost('subtarea_estado') ?? 'definida',
                'fecha_vencimiento' => $this->request->getPost('subtarea_fecha_vencimiento') ?: null,
                'prioridad' => $this->request->getPost('subtarea_prioridad') ?? 'normal',
                'comentario' => $this->request->getPost('subtarea_comentario') ?? '',
                'id_responsable' => session()->get('user_id')
            ];

            $subtareas_db = new Subtareas_db(); // Corregido a Subtareas_db
            if ($subtareas_db->Insertar_subtarea($datasubtarea)) {
                return redirect()->back()->with('success', 'Subtarea agregada');
            } else {
                return redirect()->back()->with('error', 'No se pudo agregar la subtarea');
            }
        } else {
            return redirect()->back()->with('info', 'No se ingresó nombre para la nueva subtarea.');
        }
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
                
                $subtareas_db = new Subtareas_db();
                $colaboradores_db = new Colaboradores_db();
                $colaboradores_subtareas_db = new Colaboradores_subtareas_db();

                $subtareas_a_eliminar = $subtareas_db->where('id_tarea', $id_tarea)->findAll();
                if(is_array($subtareas_a_eliminar)){
                    foreach ($subtareas_a_eliminar as $sub) {
                        if(isset($sub['id_subtarea'])){
                             $colaboradores_subtareas_db->where('id_subtarea', $sub['id_subtarea'])->delete();
                        }
                    }
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
    
    public function getCompartir()
    {
        $id_tarea_a_compartir = $this->request->getGet('id_tarea');
        $user_id = session()->get('user_id');

        if (empty($id_tarea_a_compartir)) {
            return redirect()->to(base_url('controlador_tareas/tareas'))->with('error', 'ID de tarea no proporcionado.');
        }

        $tareas_db = new Tareas_db();
        $tarea_actual = $tareas_db->find($id_tarea_a_compartir);

        if (!$tarea_actual) {
             return redirect()->to(base_url('controlador_tareas/tareas'))->with('error', 'Tarea no encontrada.');
        }
        
        if ($tarea_actual['id_responsable'] != $user_id) {
           return redirect()->to(base_url('controlador_tareas/tareas'))->with('error', 'No tienes permiso para compartir esta tarea.');
        }

        $colaboradores_db = new Colaboradores_db();
        $colaboradores_de_la_tarea = $colaboradores_db->All_colaboradores($id_tarea_a_compartir); 

        $data_para_vista_fondo = $this->_prepareIndexData(); // Carga todos los datos comunes, incluyendo notificaciones

        $data_para_vista_modal = [
            'colaboradores_modal' => $colaboradores_de_la_tarea, 
            'id_tarea_modal' => $id_tarea_a_compartir,    
            'abrir_modal' => true, 
            'abrir_modal_subtarea' => false 
        ];
        
        $data_final_para_vista = array_merge($data_para_vista_fondo, $data_para_vista_modal);
        
        return view('vistas_tareas/index', $data_final_para_vista);
    }

    public function postAgregar_colaborador()
    {
        $id_tarea_a_compartir = $this->request->getPost('id_tarea'); 
        $correo_invitado = $this->request->getPost('correo');
        $id_usuario_actual = session()->get('user_id');
        $nombre_usuario_actual = session()->get('usuario'); 

        $tareas_db = new Tareas_db();
        $tarea_actual = $tareas_db->find($id_tarea_a_compartir);
        $usuario_db = new Usuario_db(); 
        $notificaciones_db = new Notificaciones_db();
        $subtareas_db = new Subtareas_db(); // Para obtener las subtareas
        $colaboradores_subtareas_db = new Colaboradores_subtareas_db(); // Para agregar a subtareas

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

        if ($id_usuario_invitado == $id_usuario_actual) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('error', 'No puedes invitarte a ti mismo.');
        }

        $colaboradores_db = new Colaboradores_db();
        if ($colaboradores_db->existeColaborador($id_tarea_a_compartir, $correo_invitado)) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('error', 'El usuario ya es colaborador de esta tarea.');
        }
        
        if ($notificaciones_db->existeInvitacionPendiente($id_usuario_invitado, 'invitacion_tarea', $id_tarea_a_compartir)) {
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('info', 'Ya existe una invitación pendiente para este usuario en esta tarea.');
        }

        // Iniciar transacción si vas a hacer múltiples inserciones
        $db = \Config\Database::connect();
        $db->transStart();

        $colaborador_agregado_a_tarea = $colaboradores_db->Insertar_colaborador($id_tarea_a_compartir, $correo_invitado, 'pendiente');

        if ($colaborador_agregado_a_tarea) { 
            $mensaje_notif_tarea = esc($nombre_usuario_actual) . " te ha invitado a colaborar en la tarea: \"" . esc($tarea_actual['asunto']) . "\".";
            $notificaciones_db->crearNotificacion([
                'id_usuario_destino' => $id_usuario_invitado,
                'tipo_notificacion' => 'invitacion_tarea',
                'mensaje' => $mensaje_notif_tarea,
                'id_entidad_principal' => $id_tarea_a_compartir,
                'tipo_entidad_principal' => 'tarea',
                'id_entidad_relacionada' => $id_usuario_actual, 
                'tipo_entidad_relacionada' => 'usuario',
                'datos_adicionales' => json_encode(['nombre_tarea' => $tarea_actual['asunto'], 'nombre_invitador' => $nombre_usuario_actual])
            ]);

            // NUEVA LÓGICA: Agregar como colaborador a todas las subtareas existentes de esta tarea
            $subtareas_de_la_tarea = $subtareas_db->All_subtareas($id_tarea_a_compartir);

            if (!empty($subtareas_de_la_tarea) && is_array($subtareas_de_la_tarea)) {
                foreach ($subtareas_de_la_tarea as $subtarea) {
                    if (isset($subtarea['id_subtarea'])) {
                        $id_subtarea_actual = $subtarea['id_subtarea'];

                        // Verificar si ya es colaborador o tiene invitación pendiente para ESTA subtarea
                        if (!$colaboradores_subtareas_db->existeColaborador_subtarea($id_subtarea_actual, $correo_invitado) &&
                            !$notificaciones_db->existeInvitacionPendiente($id_usuario_invitado, 'invitacion_subtarea', $id_subtarea_actual)) {
                            
                            if ($colaboradores_subtareas_db->Insertar_subcolaborador($id_subtarea_actual, $correo_invitado, 'pendiente')) {
                                $mensaje_notif_sub = esc($nombre_usuario_actual) . " también te ha invitado a la subtarea: \"" . esc($subtarea['nombre']) . "\" (de la tarea \"" . esc($tarea_actual['asunto']) . "\").";
                                $notificaciones_db->crearNotificacion([
                                    'id_usuario_destino' => $id_usuario_invitado,
                                    'tipo_notificacion' => 'invitacion_subtarea',
                                    'mensaje' => $mensaje_notif_sub,
                                    'id_entidad_principal' => $id_subtarea_actual,
                                    'tipo_entidad_principal' => 'subtarea',
                                    'id_entidad_relacionada' => $id_usuario_actual,
                                    'tipo_entidad_relacionada' => 'usuario',
                                    'datos_adicionales' => json_encode([
                                        'nombre_subtarea' => $subtarea['nombre'], 
                                        'nombre_tarea_padre' => $tarea_actual['asunto'],
                                        'id_tarea_padre' => $id_tarea_a_compartir,
                                        'nombre_invitador' => $nombre_usuario_actual
                                    ])
                                ]);
                            } else {
                                log_message('error', "No se pudo agregar colaborador a la subtarea ID: {$id_subtarea_actual} para el correo: {$correo_invitado}");
                            }
                        }
                    }
                }
            }
            
            $db->transComplete(); // Completar la transacción
            if ($db->transStatus() === false) {
                // Si la transacción falló, manejar el error
                log_message('error', "Falló la transacción al agregar colaborador a tarea y subtareas. Tarea ID: {$id_tarea_a_compartir}, Correo: {$correo_invitado}");
                return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                                 ->with('error', 'Ocurrió un error al procesar las invitaciones.');
            }

            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('success', 'Invitación enviada al colaborador para la tarea y sus subtareas existentes.');
        } else {
            $db->transRollback(); // Revertir si la inserción inicial falló
            return redirect()->to(base_url('controlador_tareas/tareas/compartir?id_tarea=' . $id_tarea_a_compartir))
                             ->with('error', 'No se pudo agregar el colaborador a la tarea principal.');
        }
    }

   public function postAgregar_colaborador_editar()
{
    $id_tarea_a_compartir = $this->request->getPost('id_tarea'); 
    $correo_invitado = $this->request->getPost('correo');
    $id_usuario_actual = session()->get('user_id');
    $nombre_usuario_actual = session()->get('usuario'); 

    $tareas_db = new \App\Models\db_tareas\Tareas_db();
    $tarea_actual = $tareas_db->find($id_tarea_a_compartir); 
    $usuario_db = new \App\Models\db_tareas\Usuario_db(); 
    $notificaciones_db = new \App\Models\db_tareas\Notificaciones_db();

    if (!$tarea_actual) {
        // CORREGIDO: Usar segmento en la URL
        return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea_a_compartir))
                         ->with('error', 'Tarea no encontrada.');
    }
    if ($tarea_actual['id_responsable'] != $id_usuario_actual) {
        // CORREGIDO: Usar segmento en la URL
        return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea_a_compartir))
                         ->with('error', 'No tienes permiso para agregar colaboradores a esta tarea.');
    }
    
    if (empty($correo_invitado) || !filter_var($correo_invitado, FILTER_VALIDATE_EMAIL)) {
        // CORREGIDO: Usar segmento en la URL
        return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea_a_compartir))
                         ->with('error', 'Correo inválido.')
                         ->withInput(); // Mantener withInput si es necesario para repoblar el formulario
    }

    $info_invitado = $usuario_db->Devolver_usuario($correo_invitado);
    if (!$info_invitado || !isset($info_invitado['id_user'])) {
        // CORREGIDO: Usar segmento en la URL
        return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea_a_compartir))
                         ->with('error', 'El usuario invitado no existe en la base de datos.');
    }
    $id_usuario_invitado = $info_invitado['id_user'];

    if ($id_usuario_invitado == $id_usuario_actual) {
        // CORREGIDO: Usar segmento en la URL
        return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea_a_compartir))
                         ->with('error', 'No puedes invitarte a ti mismo.');
    }

    $colaboradores_db = new \App\Models\db_tareas\Colaboradores_db();
    if ($colaboradores_db->existeColaborador($id_tarea_a_compartir, $correo_invitado)) {
        // CORREGIDO: Usar segmento en la URL
        return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea_a_compartir))
                         ->with('error', 'El usuario ya es colaborador de esta tarea.');
    }
    
    if ($notificaciones_db->existeInvitacionPendiente($id_usuario_invitado, 'invitacion_tarea', $id_tarea_a_compartir)) {
        // CORREGIDO: Usar segmento en la URL
        return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea_a_compartir))
                         ->with('info', 'Ya existe una invitación pendiente para este usuario en esta tarea.');
    }

    if ($colaboradores_db->Insertar_colaborador($id_tarea_a_compartir, $correo_invitado, 'pendiente')) { 
        
        $mensaje_notif = esc($nombre_usuario_actual) . " te ha invitado a colaborar en la tarea: \"" . esc($tarea_actual['asunto']) . "\".";
        $notificaciones_db->crearNotificacion([
            'id_usuario_destino' => $id_usuario_invitado,
            'tipo_notificacion' => 'invitacion_tarea',
            'mensaje' => $mensaje_notif,
            'id_entidad_principal' => $id_tarea_a_compartir,
            'tipo_entidad_principal' => 'tarea',
            'id_entidad_relacionada' => $id_usuario_actual, 
            'tipo_entidad_relacionada' => 'usuario',
            'datos_adicionales' => json_encode(['nombre_tarea' => $tarea_actual['asunto'], 'nombre_invitador' => $nombre_usuario_actual])
        ]);

        // CORREGIDO: Usar segmento en la URL
        return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea_a_compartir))
                         ->with('success', 'Invitación enviada al colaborador.');
    } else {
        // CORREGIDO: Usar segmento en la URL
        return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea_a_compartir))
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
    
    public function getNueva_tarea()
    {
        
        $data = [
            'validation' => session()->getFlashdata('validation')
        ];
        return view('vistas_tareas/nueva_tarea', $data);
    }

    public function postCrear_tareaprincipal()
    {
        $validation = \Config\Services::validation();
        // Reglas de validación solo para los campos de la tarea principal
        $rules = [
            'asunto'             => 'required|min_length[4]',
            'descripcion'        => 'required|min_length[4]|max_length[255]',
            'fecha_vencimiento'  => 'required|valid_date',
            'fecha_recordatorio' => 'required|valid_date',
            // 'estado' y 'prioridad' usualmente tienen valores por defecto y no necesitan 'required' si el select lo maneja
            // 'color' también tiene un valor por defecto
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('controlador_tareas/tareas/nueva_tarea'))
                             ->withInput()
                             ->with('validation', $this->validator);
        }

        $data_tarea = [
            'asunto'            => $this->request->getPost('asunto'),
            'descripcion'       => $this->request->getPost('descripcion'),
            'prioridad'         => $this->request->getPost('prioridad') ?? 'normal',
            'estado'            => $this->request->getPost('estado') ?? 'definida',
            'fecha_vencimiento' => $this->request->getPost('fecha_vencimiento'),
            'fecha_recordatorio'=> $this->request->getPost('fecha_recordatorio'),
            'color'             => $this->request->getPost('color') ?? '#563d7c', // Usando el default de tu vista
            'id_responsable'    => session()->get('user_id'),
            // 'fecha_creacion' debería manejarse automáticamente por la base de datos (timestamp) o tu modelo.
        ];

        $tareas_db = new Tareas_db();
        $id_tarea_insertada = $tareas_db->insert($data_tarea, true); // true para que devuelva el ID insertado

        if ($id_tarea_insertada) {
            // Redirigir a la página de edición de la tarea recién creada
            return redirect()->to(base_url('controlador_tareas/tareas/editar_tarea/' . $id_tarea_insertada))
                             ->with('success', 'Tarea principal creada con éxito. Ahora puedes agregar colaboradores y subtareas.');
        } else {
            return redirect()->to(base_url('controlador_tareas/tareas/nueva_tarea'))
                             ->with('error', 'No se pudo crear la tarea principal.')
                             ->withInput();
        }
    }

    // Método privado para generar notificaciones de recordatorio
    private function generarNotificacionesDeRecordatorio($user_id)
    {
        $tareas_db = new Tareas_db();
        $notificaciones_db = new Notificaciones_db();

        $fecha_limite_recordatorio = date('Y-m-d H:i:s', strtotime('+3 days'));
        $fecha_actual = date('Y-m-d H:i:s');

        $tareas_a_recordar = $tareas_db->where('id_responsable', $user_id)
                                    ->where('fecha_recordatorio <=', $fecha_limite_recordatorio)
                                    ->where('fecha_recordatorio >=', $fecha_actual) 
                                    ->whereNotIn('estado', ['completada', 'archivada'])
                                    ->findAll();
        
        if (is_array($tareas_a_recordar)) { // Asegurarse que es un array
            foreach ($tareas_a_recordar as $tarea) {
                if (isset($tarea['id_tarea'])) {
                    $existe_notif = $notificaciones_db->where('id_usuario_destino', $user_id)
                                                    ->where('tipo_notificacion', 'recordatorio_vencimiento')
                                                    ->where('id_entidad_principal', $tarea['id_tarea'])
                                                    ->where('fecha_creacion >=', date('Y-m-d H:i:s', strtotime('-1 day'))) 
                                                    ->first();
                    if (!$existe_notif) {
                        $mensaje = "Recordatorio: La tarea \"" . esc($tarea['asunto']) . "\" vence pronto (el " . date('d/m/Y', strtotime($tarea['fecha_vencimiento'])) . ").";
                        $notificaciones_db->crearNotificacion([
                            'id_usuario_destino' => $user_id,
                            'tipo_notificacion' => 'recordatorio_vencimiento',
                            'mensaje' => $mensaje,
                            'id_entidad_principal' => $tarea['id_tarea'],
                            'tipo_entidad_principal' => 'tarea',
                            'datos_adicionales' => json_encode(['nombre_tarea' => $tarea['asunto'], 'fecha_vencimiento' => $tarea['fecha_vencimiento']])
                        ]);
                    }
                }
            }
        }
    }
}
