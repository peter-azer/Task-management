<?php

namespace App\Http\Middleware;

use App\Logic\TeamLogic;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TeamMemeberMiddlerware
{
    public function __construct(protected TeamLogic $teamLogic) {}
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::find(Auth::user()->id);
        if($user->hasRole("super-admin")){

            return $next($request);
        }else{
        $team_id = intval($request->route('team_id'));

        if(!$this->teamLogic->userHasAccsess($user->id, $team_id)){
            return redirect()->route("home")->with("notif", ["Problem\nThe team is not found or you have been kicked out, please contact the owner."]);
        }
        return $next($request);
        }
    }
}
