<?php

namespace App\Http\Controllers\api_v1;

use App\Http\Controllers\Controller;
use App\Logic\CardLogic;
use App\Logic\TeamLogic;
use App\Models\Card;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    public function __construct(
        protected TeamLogic $teamLogic,
        protected CardLogic $cardLogic
    ) {}

    // GET /api/v1/teams/{team_id}/boards/{board_id}/cards/{card_id}
    public function show(int $team_id, int $board_id, int $card_id)
    {
        $card = Card::findOrFail($card_id);
        $team_members = $this->teamLogic->getTeamMember($team_id);
        $owner = $this->teamLogic->getTeamOwner($team_id);
        $workers = $this->cardLogic->getWorkers($card_id);
        $hist = $this->cardLogic->getHistories($card_id);
        return response()->json([
            'card' => $card,
            'team_members' => $team_members,
            'workers' => $workers,
            'histories' => $hist,
            'owner' => $owner,
        ]);
    }

    // POST /api/v1/teams/{team_id}/boards/{board_id}/cards/{card_id}/assign
    public function assignTask(Request $request, int $team_id, int $board_id, int $card_id)
    {
        $request->validate(['id' => 'required|integer|exists:users,id']);
        $user_id = intval($request->id);
        $user = User::findOrFail($user_id);
        $workers = $this->cardLogic->getWorkers($card_id);
        if ($workers->contains('id', $user_id)) {
            return response()->json(['message' => $user->name . ' is already assigned to this card'], 409);
        }
        $this->cardLogic->addUser($card_id, $user_id);
        $this->cardLogic->cardAddEvent($card_id, Auth::id(), 'Joined card.');
        return response()->json(['message' => 'assigned']);
    }

    // POST /api/v1/teams/{team_id}/boards/{board_id}/cards/{card_id}/unassign
    public function unassignTask(Request $request, int $team_id, int $board_id, int $card_id)
    {
        $request->validate(['id' => 'required|integer|exists:users,id']);
        $user_id = intval($request->id);
        $workers = $this->cardLogic->getWorkers($card_id);
        if (!$workers->contains('id', $user_id)) {
            return response()->json(['message' => 'user is not assigned to this card'], 409);
        }
        $this->cardLogic->removeUser($card_id, $user_id);
        $this->cardLogic->cardAddEvent($card_id, Auth::id(), 'Removed user from card.');
        return response()->json(['message' => 'unassigned']);
    }

    // POST /api/v1/teams/{team_id}/boards/{board_id}/cards/{card_id}/leave
    public function leave(int $team_id, int $board_id, int $card_id)
    {
        $user_id = Auth::id();
        $this->cardLogic->removeUser($card_id, $user_id);
        $this->cardLogic->cardAddEvent($card_id, $user_id, 'Left card.');
        return response()->json(['message' => 'left']);
    }

    // DELETE /api/v1/teams/{team_id}/boards/{board_id}/cards/{card_id}
    public function destroy(int $team_id, int $board_id, int $card_id)
    {
        $this->cardLogic->deleteCard($card_id);
        return response()->json(['message' => 'deleted']);
    }

    // PUT /api/v1/teams/{team_id}/boards/{board_id}/cards/{card_id}
    public function update(Request $request, int $team_id, int $board_id, int $card_id)
    {
        $request->validate([
            'card_name' => 'required|string|max:95',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'card_description' => 'nullable|string',
        ]);
        $user_id = Auth::id();
        $card = Card::findOrFail($card_id);
        $card->name = $request->card_name;
        $card->start_date = $request->start_date;
        $card->end_date = $request->end_date;
        $card->description = $request->card_description;
        $card->save();
        $this->cardLogic->cardAddEvent($card_id, $user_id, 'Updated card informations.');
        return response()->json(['message' => 'updated', 'card' => $card]);
    }

    // PATCH /api/v1/teams/{team_id}/boards/{board_id}/cards/{card_id}/done
    public function markDone(Request $request, int $team_id, int $board_id, int $card_id)
    {
        $validated = $request->validate(['is_done' => 'required|boolean']);
        $user_id = Auth::id();
        $card = Card::findOrFail($card_id);
        $card->update($validated);
        $statusText = $card->is_done ? 'marked as done' : 'marked as not done';
        $this->cardLogic->cardAddEvent($card_id, $user_id, "Card was $statusText.");
        return response()->json(['message' => 'Card status updated.', 'is_done' => $card->is_done]);
    }

    // POST /api/v1/teams/{team_id}/boards/{board_id}/cards/{card_id}/comment
    public function addComment(Request $request, int $team_id, int $board_id, int $card_id)
    {
        $request->validate(['content' => 'required|string|max:200']);
        $user_id = Auth::id();
        $this->cardLogic->cardComment($card_id, $user_id, $request->content);
        return response()->json(['message' => 'comment added']);
    }

    // POST /api/v1/teams/{team_id}/boards/{board_id}/cards/{card_id}/archive
    public function archive(int $team_id, int $board_id, int $card_id)
    {
        $this->cardLogic->archiveCard($card_id);
        return response()->json(['message' => 'archived']);
    }

    // POST /api/v1/teams/{team_id}/boards/{board_id}/cards/{card_id}/unarchive
    public function unarchive(int $team_id, int $board_id, int $card_id)
    {
        $this->cardLogic->unarchiveCard($card_id);
        return response()->json(['message' => 'unarchived']);
    }
}
