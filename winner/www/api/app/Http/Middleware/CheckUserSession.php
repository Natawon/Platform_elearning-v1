<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\Members;

class CheckUserSession
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
        // return response()->json(Members::find(1), 500);
        if (!$request->session()->exists('_user')) {
            // user value cannot be found in session
            // return redirect('/');
            return response('No input file specified.', 404);
            // return redirect(config('constants.URL.HOME'));
        }

        $_user = session()->get('_user');
        $dataMember = Members::find($_user['id']);

        if ($_user['my_session_id'] != $dataMember->my_session_id) {
            $request->session()->forget('_user');
            return response('No input file specified.', 404);
        }

        return $next($request);
    }
}
