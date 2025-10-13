<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api_v1\AuthController as ApiV1AuthController;
use App\Http\Controllers\api_v1\UserController as ApiV1UserController;
use App\Http\Controllers\api_v1\TeamController as ApiV1TeamController;
use App\Http\Controllers\api_v1\BoardController as ApiV1BoardController;
use App\Http\Controllers\api_v1\CardController as ApiV1CardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| API v1 (Bearer Token via Sanctum)
|--------------------------------------------------------------------------
| Base path: /api/v1
| Notes:
| - Public routes: register, login
| - Protected routes: logout, me, users
| - Use Authorization: Bearer {token}
*/

Route::prefix('v1')->group(function () {
    /*
    |------------------------------------------------------------------
    | POST /api/v1/auth/register
    | Description: Register a new user and receive a bearer token.
    | Body: { name: string, email: string, password: string, password_confirmation: string }
    | Response: { token: string, token_type: "Bearer", user: object }
    | Access: Public
    */
    Route::post('/auth/register', [ApiV1AuthController::class, 'register'])->name('api.v1.auth.register');

    /*
    |------------------------------------------------------------------
    | POST /api/v1/auth/login
    | Description: Login and receive a bearer token.
    | Body: { email: string, password: string }
    | Response: { token: string, token_type: "Bearer", user: object }
    | Access: Public
    */
    Route::post('/auth/login', [ApiV1AuthController::class, 'login'])->name('api.v1.auth.login');

    /*
    | Protected endpoints (require Authorization: Bearer {token})
    */
    Route::middleware('auth:sanctum')->group(function () {
        /*
        |--------------------------------------------------------------
        | POST /api/v1/auth/logout
        | Description: Invalidate the current access token.
        | Access: Authenticated (bearer)
        */
        Route::post('/auth/logout', [ApiV1AuthController::class, 'logout'])->name('api.v1.auth.logout');

        /*
        |--------------------------------------------------------------
        | GET /api/v1/auth/me
        | Description: Get the authenticated user's profile.
        | Access: Authenticated (bearer)
        */
        Route::get('/auth/me', [ApiV1AuthController::class, 'me'])->name('api.v1.auth.me');

        /*
        |--------------------------------------------------------------
        | GET /api/v1/users
        | Description: List users (paginated).
        | Query: per_page (int, optional, default 15, max 100)
        | Access: Authenticated (bearer)
        */
        Route::get('/users', [ApiV1UserController::class, 'index'])
            ->middleware('permission:view-users')
            ->name('api.v1.users.index');

        /*
        |--------------------------------------------------------------
        | GET /api/v1/users/{id}
        | Description: Get a single user by id.
        | Params: id (int)
        | Access: Authenticated (bearer)
        */
        Route::get('/users/{id}', [ApiV1UserController::class, 'show'])->name('api.v1.users.show');

        /*
        |--------------------------------------------------------------
        | TEAMS
        |--------------------------------------------------------------
        */
        // List teams for current user
        Route::get('/teams', [ApiV1TeamController::class, 'index'])
            ->name('api.v1.teams.index');
        // Create a team
        Route::post('/teams', [ApiV1TeamController::class, 'store'])
            ->middleware('permission:create-team')
            ->name('api.v1.teams.store');
        // Team details
        Route::get('/teams/{team_id}', [ApiV1TeamController::class, 'show'])
            ->middleware('userInTeam')
            ->name('api.v1.teams.show');
        // Update team
        Route::put('/teams/{team_id}', [ApiV1TeamController::class, 'update'])
            ->middleware(['userInTeam','permission:update-team'])
            ->name('api.v1.teams.update');
        // Delete team
        Route::delete('/teams/{team_id}', [ApiV1TeamController::class, 'destroy'])
            ->middleware(['userInTeam','permission:delete-team'])
            ->name('api.v1.teams.destroy');
        // Upload team image
        Route::post('/teams/{team_id}/image', [ApiV1TeamController::class, 'updateImage'])
            ->middleware(['userInTeam','permission:update-team'])
            ->name('api.v1.teams.image');
        // Search teams (by name)
        Route::get('/teams/search', [ApiV1TeamController::class, 'search'])
            ->name('api.v1.teams.search');
        // Leave team
        Route::post('/teams/{team_id}/leave', [ApiV1TeamController::class, 'leave'])
            ->middleware('userInTeam')
            ->name('api.v1.teams.leave');
        // Invite members
        Route::post('/teams/{team_id}/invites', [ApiV1TeamController::class, 'inviteMembers'])
            ->middleware(['userInTeam','permission:send-invitation'])
            ->name('api.v1.teams.invites');
        // Delete members
        Route::delete('/teams/{team_id}/users', [ApiV1TeamController::class, 'deleteMembers'])
            ->middleware(['userInTeam','permission:manage-members'])
            ->name('api.v1.teams.members.delete');
        // Get invite details for current user
        Route::get('/teams/{team_id}/invite', [ApiV1TeamController::class, 'getInvite'])
            ->middleware('userInTeam')
            ->name('api.v1.teams.invite');
        // Accept invite
        Route::post('/teams/{team_id}/invite/accept', [ApiV1TeamController::class, 'acceptInvite'])
            ->middleware('userInTeam')
            ->name('api.v1.teams.invite.accept');
        // Reject invite
        Route::post('/teams/{team_id}/invite/reject', [ApiV1TeamController::class, 'rejectInvite'])
            ->middleware('userInTeam')
            ->name('api.v1.teams.invite.reject');
        // Search boards in team
        Route::get('/teams/{team_id}/boards/search', [ApiV1TeamController::class, 'searchBoard'])
            ->middleware('userInTeam')
            ->name('api.v1.teams.boards.search');

        /*
        |--------------------------------------------------------------
        | BOARDS
        |--------------------------------------------------------------
        */
        // Create board
        Route::post('/teams/{team_id}/boards', [ApiV1BoardController::class, 'store'])
            ->middleware(['userInTeam','permission:create-project'])
            ->name('api.v1.boards.store');
        // Show board
        Route::get('/teams/{team_id}/boards/{board_id}', [ApiV1BoardController::class, 'show'])
            ->middleware('boardAccess')
            ->name('api.v1.boards.show');
        // Board data
        Route::get('/teams/{team_id}/boards/{board_id}/data', [ApiV1BoardController::class, 'getData'])
            ->middleware('boardAccess')
            ->name('api.v1.boards.data');
        // Update board
        Route::put('/teams/{team_id}/boards/{board_id}', [ApiV1BoardController::class, 'update'])
            ->middleware(['boardAccess','permission:edit-project'])
            ->name('api.v1.boards.update');
        // Delete board
        Route::delete('/teams/{team_id}/boards/{board_id}', [ApiV1BoardController::class, 'destroy'])
            ->middleware(['boardAccess','permission:delete-project'])
            ->name('api.v1.boards.destroy');
        // Add column
        Route::post('/teams/{team_id}/boards/{board_id}/columns', [ApiV1BoardController::class, 'addColumn'])
            ->middleware('boardAccess')
            ->name('api.v1.columns.store');
        // Reorder columns
        Route::patch('/teams/{team_id}/boards/{board_id}/columns/reorder', [ApiV1BoardController::class, 'reorderCol'])
            ->middleware(['boardAccess','permission:edit-task'])
            ->name('api.v1.columns.reorder');
        // Update column
        Route::put('/teams/{team_id}/boards/{board_id}/columns/{column_id}', [ApiV1BoardController::class, 'updateCol'])
            ->middleware(['boardAccess','permission:edit-task'])
            ->name('api.v1.columns.update');
        // Delete column
        Route::delete('/teams/{team_id}/boards/{board_id}/columns/{column_id}', [ApiV1BoardController::class, 'deleteCol'])
            ->middleware(['boardAccess','permission:delete-task'])
            ->name('api.v1.columns.delete');
        // Add card to column
        Route::post('/teams/{team_id}/boards/{board_id}/columns/{column_id}/cards', [ApiV1BoardController::class, 'addCard'])
            ->middleware(['boardAccess','permission:create-task'])
            ->name('api.v1.cards.store');
        // Reorder cards
        Route::patch('/teams/{team_id}/boards/{board_id}/cards/reorder', [ApiV1BoardController::class, 'reorderCard'])
            ->middleware(['boardAccess','permission:edit-task'])
            ->name('api.v1.cards.reorder');

        /*
        |--------------------------------------------------------------
        | CARDS
        |--------------------------------------------------------------
        */
        // Show card
        Route::get('/teams/{team_id}/boards/{board_id}/cards/{card_id}', [ApiV1CardController::class, 'show'])
            ->middleware(['boardAccess','cardExist'])
            ->name('api.v1.cards.show');
        // Assign user to card
        Route::post('/teams/{team_id}/boards/{board_id}/cards/{card_id}/assign', [ApiV1CardController::class, 'assignTask'])
            ->middleware(['boardAccess','cardExist','permission:assign-tasks'])
            ->name('api.v1.cards.assign');
        // Unassign user from card
        Route::post('/teams/{team_id}/boards/{board_id}/cards/{card_id}/unassign', [ApiV1CardController::class, 'unassignTask'])
            ->middleware(['boardAccess','cardExist','permission:assign-tasks'])
            ->name('api.v1.cards.unassign');
        // Leave card
        Route::post('/teams/{team_id}/boards/{board_id}/cards/{card_id}/leave', [ApiV1CardController::class, 'leave'])
            ->middleware(['boardAccess','cardExist'])
            ->name('api.v1.cards.leave');
        // Delete card
        Route::delete('/teams/{team_id}/boards/{board_id}/cards/{card_id}', [ApiV1CardController::class, 'destroy'])
            ->middleware(['boardAccess','cardExist','permission:delete-task'])
            ->name('api.v1.cards.destroy');
        // Update card
        Route::put('/teams/{team_id}/boards/{board_id}/cards/{card_id}', [ApiV1CardController::class, 'update'])
            ->middleware(['boardAccess','cardExist','permission:edit-task'])
            ->name('api.v1.cards.update');
        // Mark done / not done
        Route::patch('/teams/{team_id}/boards/{board_id}/cards/{card_id}/done', [ApiV1CardController::class, 'markDone'])
            ->middleware(['boardAccess','cardExist'])
            ->name('api.v1.cards.done');
        // Add comment
        Route::post('/teams/{team_id}/boards/{board_id}/cards/{card_id}/comment', [ApiV1CardController::class, 'addComment'])
            ->middleware(['boardAccess','cardExist'])
            ->name('api.v1.cards.comment');
        // Archive / unarchive
        Route::post('/teams/{team_id}/boards/{board_id}/cards/{card_id}/archive', [ApiV1CardController::class, 'archive'])
            ->middleware(['boardAccess','cardExist','permission:archive-task'])
            ->name('api.v1.cards.archive');
        Route::post('/teams/{team_id}/boards/{board_id}/cards/{card_id}/unarchive', [ApiV1CardController::class, 'unarchive'])
            ->middleware(['boardAccess','cardExist','permission:archive-task'])
            ->name('api.v1.cards.unarchive');
    });
});
