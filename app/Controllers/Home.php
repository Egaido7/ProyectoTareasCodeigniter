<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function getIndex()
    {
        if(session()->has('usuario')){
            return redirect()->to(base_url('controlador_tareas/tareas'));
        }else{
             return redirect()->to(base_url('controlador_tareas/login'));
        }

    }
    
}
