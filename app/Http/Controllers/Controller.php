<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Controller base de la aplicación.
 *
 * - AuthorizesRequests: habilita $this->authorize()
 * - ValidatesRequests: habilita $request->validate()
 *
 * Todos tus controladores deben extender de esta clase.
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
