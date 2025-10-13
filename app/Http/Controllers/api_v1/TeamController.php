<?php

namespace App\Http\Controllers\api_v1;

use App\Http\Controllers\Controller;
use App\Logic\api_v1\TeamApiLogic;
use App\Logic\TeamLogic;
use App\Logic\FileLogic;
use App\Models\Team;
use App\Models\User;
use App\Models\UserTeam;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    public function __construct(
        protected TeamApiLogic $apiLogic,
        protected TeamLogic $teamLogic,
        protected FileLogic $fileLogic,
    ) {}

    // GET /api/v1/teams
    public function index(Request $request)
    {
        $data = $this->apiLogic->listForUser(Auth::id());
        return response()->json($data);
    }

    // GET /api/v1/teams/{team_id}
    public function show(int $team_id)
    {
        return response()->json($this->apiLogic->teamDetails($team_id));
    }

    // GET /api/v1/teams/search?team_name=
    public function search(Request $request)
    {
        $request->validate(['team_name' => 'required|string']);
        $user = User::findOrFail(Auth::id());
        $teams = $this->teamLogic->getUserTeams($user->id, ["Member", "Owner"], $request->team_name);
        $invites = $this->teamLogic->getUserTeams($user->id, ["Pending"], $request->team_name);
        return response()->json(['teams' => $teams, 'invites' => $invites]);
    }

    // POST /api/v1/teams
    public function store(Request $request)
    {
        $request->validate([
            'team_name' => 'required|min:5|max:20',
            'team_description' => 'required|min:5|max:90',
            'team_pattern' => 'sometimes',
        ]);
        $created = $this->teamLogic->createTeam(
            Auth::id(),
            $request->team_name,
            $request->team_description,
            $request->team_pattern,
        );
        return response()->json($created, 201);
    }

    // PUT /api/v1/teams/{team_id}
    public function update(Request $request, int $team_id)
    {
        $request->validate([
            'team_name' => 'required|min:5|max:20',
            'team_description' => 'required|min:8|max:90',
            'team_pattern' => 'required',
        ]);
        $team = Team::findOrFail($team_id);
        $team->name = $request->team_name;
        $team->description = $request->team_description;
        $team->pattern = $request->team_pattern;
        $team->save();
        return response()->json(['message' => 'updated', 'team' => $team]);
    }

    // POST /api/v1/teams/{team_id}/image
    public function updateImage(Request $request, int $team_id)
    {
        $request->validate([
            'image' => 'required|mimes:jpg,jpeg,png|max:10240',
        ]);
        $team = Team::findOrFail($team_id);
        $this->fileLogic->storeTeamImage($team->id, $request, 'image');
        return response()->json(['message' => 'success']);
    }

    // DELETE /api/v1/teams/{team_id}
    public function destroy(int $team_id)
    {
        $this->teamLogic->deleteTeam($team_id);
        return response()->json(['message' => 'deleted']);
    }

    // POST /api/v1/teams/{team_id}/leave
    public function leave(int $team_id)
    {
        $user_email = Auth::user()->email;
        $this->teamLogic->deleteMembers($team_id, [$user_email]);
        return response()->json(['message' => 'left']);
    }

    // POST /api/v1/teams/{team_id}/invites
    public function inviteMembers(Request $request, int $team_id)
    {
        $request->validate(['emails' => 'required|array', 'emails.*' => 'email']);
        $emails = $request->emails;
        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            if (!$user) continue;
            $existing = UserTeam::where('user_id', $user->id)->where('team_id', $team_id)->first();
            if ($existing) continue;
            UserTeam::create(['user_id' => $user->id, 'team_id' => $team_id, 'status' => 'Pending']);
        }
        return response()->json(['message' => 'invites sent']);
    }

    // DELETE /api/v1/teams/{team_id}/users
    public function deleteMembers(Request $request, int $team_id)
    {
        $request->validate(['emails' => 'required|array', 'emails.*' => 'email']);
        $this->teamLogic->deleteMembers($team_id, $request->emails);
        return response()->json(['message' => 'deleted']);
    }

    // GET /api/v1/teams/{team_id}/invite
    public function getInvite(int $team_id)
    {
        $user_id = Auth::id();
        $owner = $this->teamLogic->getTeamOwner($team_id);
        $team = Team::findOrFail($team_id);
        return response()->json([
            'owner_name' => $owner->name,
            'owner_image' => $owner->image_path,
            'team_name' => $team->name,
            'team_description' => $team->description,
            'team_image' => $team->image_path,
            'team_pattern' => $team->pattern,
        ]);
    }

    // POST /api/v1/teams/{team_id}/invite/accept
    public function acceptInvite(int $team_id)
    {
        $user_id = Auth::id();
        $invite = UserTeam::where('user_id', $user_id)->where('team_id', $team_id)->first();
        if (!$invite) return response()->json(['message' => 'invite not found'], 404);
        $invite->status = 'Member';
        $invite->save();
        return response()->json(['message' => 'accepted']);
    }

    // POST /api/v1/teams/{team_id}/invite/reject
    public function rejectInvite(int $team_id)
    {
        $user_id = Auth::id();
        $invite = UserTeam::where('user_id', $user_id)->where('team_id', $team_id)->first();
        if (!$invite) return response()->json(['message' => 'invite not found'], 404);
        $invite->delete();
        return response()->json(['message' => 'rejected']);
    }

    // GET /api/v1/teams/{team_id}/boards/search?board_name=
    public function searchBoard(Request $request, int $team_id)
    {
        $request->validate(['board_name' => 'required|string']);
        $team = Team::findOrFail($team_id);
        $boards = $this->teamLogic->getBoards($team->id, $request->board_name);
        return response()->json(['team' => $team, 'boards' => $boards]);
    }
}
