<?php

namespace App\Http\Middleware;

use Closure;

class setLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $arr = ['fr', 'en'];

        if(in_array($request->segment(1), $arr)){
            app()->setLocale($request->segment(1));
            return $next($request);
        }else{
            $msg = __('content.alertLangue');
            $data =  array(
                "status" => "NOK",
                "data" => array(
                    "errNo" => 15,
                    "errMsg" => $msg
                )
                );
            return response()->json($data, 404);
        }
    }
}
