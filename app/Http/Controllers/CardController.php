<?php

namespace App\Http\Controllers;

use App\Logic\CardLogic;
use App\Logic\TeamLogic;
use App\Models\Board;
use App\Models\Card;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    public function __construct(protected TeamLogic $teamLogic, protected CardLogic $cardLogic) {}
    public function showCard(Request $request, $team_id, $board_id, $card_id)
    {
        $board_id = intval($board_id);
        $team_id = intval($team_id);

        $card = Card::find($card_id);
        $board = Board::find($board_id);
        $team = Team::find($team_id);
        $team_members = $this->teamLogic->getTeamMember($team_id);
        $owner = $this->teamLogic->getTeamOwner($team_id);
        $workers = $this->cardLogic->getWorkers($card_id);
        $hist = $this->cardLogic->getHistories($card_id);

        return view("card")
            ->with("card", $card)
            ->with("board", $board)
            ->with("team", $team)
            ->with("team_members", $team_members)
            ->with("start_date", $card->start_date)
            ->with("end_date", $card->end_date)
            ->with("workers", $workers)
            ->with("histories", $hist)
            ->with("owner", $owner);
    }

    public function assignCard(Request $request, $team_id, $board_id, $card_id)
    {
        return redirect()->back();
    }

    public function assignTask(Request $request, $team_id, $board_id, $card_id)
    {
        $user_id = $request->id;
        $user = User::find($user_id);
        $card_id = intval($card_id);
        $this->cardLogic->addUser($card_id, $user_id);
        $this->cardLogic->cardAddEvent($card_id, $user_id, "Joined card.");
        return redirect()->back()->with("notif", ["Success\nAdded {$user->name} to the card"]);
    }

    public function leaveCard(Request $request, $team_id, $board_id, $card_id)
    {
        $user_id = Auth::user()->id;
        $card_id = intval($card_id);
        $this->cardLogic->removeUser($card_id, $user_id);
        $this->cardLogic->cardAddEvent($card_id, $user_id, "Left card.");
        return redirect()
            ->route("board", ["team_id" => $team_id, "board_id" => $board_id])
            ->with("notif", ["Success\nQuit Card"]);
    }

    public function deleteCard(Request $request, $team_id, $board_id, $card_id)
    {
        $this->cardLogic->deleteCard(intval($card_id));
        return redirect()
            ->route("board", ["team_id" => $team_id, "board_id" => $board_id])
            ->with("notif", ["Success\nCard is deleted"]);
    }

    public function updateCard(Request $request, $team_id, $board_id, $card_id)
    {
        $request->validate([
            "card_name" => "required|max:95",
            "start_date" => "nullable|date",
            "end_date" => "nullable|date|after_or_equal:start_date",
        ]);
        $user_id = AUth::user()->id;
        $card_id = intval($card_id);
        $card = Card::find($card_id);
        $card->name = $request->card_name;
        $card->start_date = $request->start_date;
        $card->end_date = $request->end_date;
        $card->description = $request->card_description;
        $card->save();
        $this->cardLogic->cardAddEvent($card_id, $user_id, "Updated card informations.");
        return redirect()->back()->with("notif", ["Succss\nCard updated successfully"]);
    }

    public function markDone(Request $request, $team_id, $board_id, $card_id)
    {
        $validatedData = $request->validate([
            'is_done' => 'required|boolean',
        ]);
        // $is_done = $validatedData['is_done'] =  ? 1 : 0;
        $user_id = auth()->id();
        $card = Card::findOrFail(intval($card_id));
        $card->update($validatedData);

        $statusText = $card->is_done ? 'marked as done' : 'marked as not done';
        $this->cardLogic->cardAddEvent($card_id, $user_id, "Card was $statusText.");

        return response()->json(['message' => 'Card status updated.', 'is_done' => $card->is_done]);
    }
    public function addComment(Request $request, $team_id, $board_id, $card_id)
    {
        $request->validate(["content" => "required|max:200"]);
        $user_id = AUth::user()->id;
        $card_id = intval($card_id);
        $this->cardLogic->cardComment($card_id, $user_id, $request->content);
        return redirect()->back();
    }
}
