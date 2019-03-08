<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Response;

use App\Models\Admins;

class AuthenticateToken
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            // dd($this->auth->guard());
            if ($this->auth->guard($guard)->check()) {

                $_user = $request->session()->get('_admin_session');
                $data = Admins::find($_user['id']);

                if (!isset($data->my_session_id) || $_user['my_session_id'] != $data->my_session_id) {
                    $this->auth->guard($guard)->logout();
                    $request->session()->forget('_admin_session');
                    return response('Unauthorized.', 401);
                }

                $this->auth->shouldUse($guard);
                return $next($request);
            }
        }

        $request->session()->forget('_admin_session');
        return response('Unauthorized.', 401);
    }
}
