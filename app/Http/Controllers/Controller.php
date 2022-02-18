<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @OA\Info(
     *     version="1.0",
     *     title="Backend developer test"
     * )
     */

    //  Custom form validation resoponse errors
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        return response([
            'errors' => $errors,
        ], 422);
    }
}
