<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16/7/2558
 * Time: 19:05 น.
 */

namespace App\Http\Middleware;

use Closure;
// use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Http\Response;

class CORS /*implements Middleware*/ {

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $http_origin = "";
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            $http_origin = $_SERVER['HTTP_ORIGIN'];
        }

        $allowed_http_origins = array(
            "https://elearning.set.or.th",
            "https://test.elearning.set.or.th",
            "https://test.set.or.th",
            "https://www.set.or.th",
            // "https://scm.set.or.th",
        );

        if (in_array($http_origin, $allowed_http_origins)) {
            return $next($request)->header('Access-Control-Allow-Origin' , $http_origin)
                ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE')
                ->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, X-CSRF-Token, Content-Type, Accept', 'Authorization')
                ->header('Access-Control-Allow-Credentials', 'true');
        } else {
            return $next($request);
        }

    }
}
