<?php

namespace App\Logic\api_v1;

use App\Logic\BoardLogic;
use App\Logic\TeamLogic;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TeamApiLogic
{
    public function __construct(
        protected TeamLogic $teamLogic,
        protected BoardLogic $boardLogic,
    ) {}

    public function listForUser(int $userId): array
    {
        $user = User::findOrFail($userId);
        if ($user->hasRole('super-admin')) {
            $teams = Team::with('users')->get();
            $invites = $this->teamLogic->getUserTeams($user->id, ["Pending"]);
        } else {
            $teams = $this->teamLogic->getUserTeams($user->id, ["Member", "Owner"]);
            $invites = $this->teamLogic->getUserTeams($user->id, ["Pending"]);
        }
        foreach ($teams as $team) {
            $owner = $team->users->firstWhere('pivot.status', 'Owner');
            $team->owner_name = $owner?->name ?? 'N/A';
        }
        return [
            'teams' => $teams,
            'invites' => $invites,
        ];
    }

    public function teamDetails(int $teamId): array
    {
        $team = Team::findOrFail($teamId);
        return [
            'team' => $team,
            'owner' => $this->teamLogic->getTeamOwner($team->id),
            'members' => $this->teamLogic->getTeamMember($team->id),
            'patterns' => TeamLogic::PATTERN,
            'backgrounds' => \App\Logic\BoardLogic::PATTERN,
            'boards' => $this->teamLogic->getBoards($team->id),
        ];
    }
}
