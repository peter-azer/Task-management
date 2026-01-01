<?php

namespace App\Http\Controllers\api_v1;

use App\Http\Controllers\Controller;
use App\Logic\BoardLogic;
use App\Logic\CardLogic;
use App\Logic\TeamLogic;
use App\Models\Board;
use App\Models\Column;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    public function __construct(
        protected TeamLogic $teamLogic,
        protected BoardLogic $boardLogic,
        protected CardLogic $cardLogic
    ) {}

    // POST /api/v1/teams/{team_id}/boards
    public function store(Request $request, int $team_id)
    {
        $request->validate([
            'board_name' => 'required|string',
            'board_pattern' => 'required|string',
        ]);
        $created = $this->boardLogic->createBoard(
            $team_id,
            $request->board_name,
            $request->board_pattern,
        );
        if (!$created) {
            return response()->json(['message' => 'Failed to create board'], 422);
        }
        return response()->json($created, 201);
    }

    // GET /api/v1/teams/{team_id}/boards/{board_id}
    public function show(int $team_id, int $board_id)
    {
        $board = $this->boardLogic->getData($board_id);
        $team = Team::findOrFail($board->team_id);
        $teamOwner = $this->teamLogic->getTeamOwner($board->team_id);
        $teamMembers = $this->teamLogic->getTeamMember($board->team_id);
        return response()->json([
            'team' => $team,
            'owner' => $teamOwner,
            'board' => $board,
            'team_members' => $teamMembers,
            'patterns' => BoardLogic::PATTERN,
        ]);
    }

    // GET /api/v1/teams/{team_id}/boards/{board_id}/data
    public function getData(int $team_id, int $board_id)
    {
        return response()->json($this->boardLogic->getData($board_id));
    }

    // PUT /api/v1/teams/{team_id}/boards/{board_id}
    public function update(Request $request, int $team_id, int $board_id)
    {
        $request->validate([
            'board_name' => 'required|string',
            'board_pattern' => 'required|string',
        ]);
        $board = Board::findOrFail($board_id);
        $board->name = $request->board_name;
        $board->pattern = $request->board_pattern;
        $board->save();
        return response()->json(['message' => 'updated', 'board' => $board]);
    }

    // POST /api/v1/teams/{team_id}/boards/{board_id}/columns/{column_id}/cards
    public function addCard(Request $request, int $team_id, int $board_id, int $column_id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        // permissions can be enforced via policies/middleware; mirroring simple check
        if (!auth()->user()->can('create-task')) {
            return response()->json(['message' => 'Unauthorized'], HttpResponse::HTTP_FORBIDDEN);
        }
        $newCard = $this->boardLogic->addCard($column_id, $request->name);
        $this->cardLogic->cardAddEvent($newCard->id, Auth::id(), 'Created card');
        return response()->json($newCard, 201);
    }

    // DELETE /api/v1/teams/{team_id}/boards/{board_id}
    public function destroy(int $team_id, int $board_id)
    {
        Board::where('id', $board_id)->delete();
        return response()->json(['message' => 'deleted']);
    }

    // POST /api/v1/teams/{team_id}/boards/{board_id}/do-unarchive
    public function unarchiveBoard(Request $request, $team_id, $board_id)
    {
        if (!auth()->user()->can('edit-project')) return redirect()->back()->with('notif', ['Unauthorized']);
        $board = Board::findOrFail(intval($board_id));
        $board->archive = false;
        $board->archived_at = null;
        $board->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Board restored'
        ]);
    }

    // POST /api/v1/teams/{team_id}/boards/{board_id}/columns
    public function addColumn(Request $request, int $team_id, int $board_id)
    {
        $request->validate([
            'column_name' => 'required|string|max:20',
        ]);
        $created = $this->boardLogic->addColumn($board_id, $request->column_name);
        if (!$created) {
            return response()->json(['message' => 'Failed to create column'], 422);
        }
        return response()->json($created, 201);
    }

    // PATCH /api/v1/teams/{team_id}/boards/{board_id}/columns/reorder
    public function reorderCol(Request $request, int $team_id, int $board_id)
    {
        $request->validate([
            'middle_id' => 'required|integer',
            'right_id' => 'nullable|integer',
            'left_id' => 'nullable|integer',
        ]);
        $user_id = Auth::id();
        if (!$this->boardLogic->hasAccess($user_id, $board_id)) {
            return response()->json(['message' => 'forbidden'], HttpResponse::HTTP_FORBIDDEN);
        }
        $updated = $this->boardLogic->moveCol(
            intval($request->middle_id),
            intval($request->right_id),
            intval($request->left_id)
        );
        return response()->json($updated);
    }

    // PATCH /api/v1/teams/{team_id}/boards/{board_id}/cards/reorder
    public function reorderCard(Request $request, int $team_id, int $board_id)
    {
        $request->validate([
            'column_id' => 'required|integer',
            'middle_id' => 'required|integer',
            'bottom_id' => 'nullable|integer',
            'top_id' => 'nullable|integer',
        ]);
        $updated = $this->boardLogic->moveCard(
            intval($request->middle_id),
            intval($request->column_id),
            intval($request->bottom_id),
            intval($request->top_id)
        );
        return response()->json($updated);
    }

    // PUT /api/v1/teams/{team_id}/boards/{board_id}/columns/{column_id}
    public function updateCol(Request $request, int $team_id, int $board_id, int $column_id)
    {
        $request->validate([
            'column_name' => 'required|string|max:20',
        ]);
        $column = Column::find($column_id);
        if (!$column) {
            return response()->json(['message' => 'column not found'], 404);
        }
        $column->name = $request->column_name;
        $column->save();
        return response()->json(['message' => 'updated', 'column' => $column]);
    }

    // DELETE /api/v1/teams/{team_id}/boards/{board_id}/columns/{column_id}
    public function deleteCol(int $team_id, int $board_id, int $column_id)
    {
        $this->boardLogic->deleteCol($column_id);
        return response()->json(['message' => 'deleted']);
    }
}
