<?php

namespace App\Http\Controllers\AreaLogada;

use App\Http\Controllers\Controller;

class Principal extends Controller
{
    public function index()
    {
        return $this->responderJSON(200, 'Acesso à área logada realizado com sucesso');
    }
}
