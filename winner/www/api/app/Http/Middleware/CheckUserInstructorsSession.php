<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\Instructors;

class CheckUserInstructorsSession
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
        // return response('No input file specified.', 404);
        if (!$request->session()->exists('_user_instructors')) {
            // user value cannot be found in session
            // return redirect('/');
            return response('No input file specified.', 404);
            // return redirect(config('constants.URL.HOME'));
        }

        return $next($request);
    }
}
