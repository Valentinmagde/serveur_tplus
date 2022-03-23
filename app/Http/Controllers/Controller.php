<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
 * @OA\Swagger(
 *     schemes={"http"},
 *     host=API_HOST,
 *     basePath="/",
 *     @OA\Info(
 *         version="2.0.0",
 *         title="Tontine Plus",
 *         description="application de gestion des associations",
 *         termsOfService="",
 *         @OA\Contact(
 *             email="support@tontine.plus"
 *         ),
 *     ),
 * )
 * 
*      @OA\Response(
*          response=200,
*          description="the request was successfully complete"
*      )
*     )

*      @OA\Response(
*          response=201,
*          description="a new resources was successfully created"
*      )
*     )

*      @OA\Response(
*          response=400,
*          description="the request was invalid"
*      )
*     )
*      @OA\Response(
*          response=401,
*          description="Unauthenticated",
*      ),
*      @OA\Response(
*          response=403,
*          description="Forbidden"
*      ),

*      @OA\Response(
*          response=404,
*          description="Not Found"
*      )

*      @OA\Response(
*          response=405,
*          description="http method in the request was not suported by the resource"
*      )
*     )

*      @OA\Response(
*          response=409,
*          description="the request could not be complete due to conflict"
*      )
*     )

*      @OA\Response(
*          response=500,
*          description="Internal server error"
*      )
*     )
 */


}
