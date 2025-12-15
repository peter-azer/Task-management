<?php

namespace App\Http\Controllers;

use App\Logic\FileLogic;
use App\Models\User;
use App\Models\UserTeam;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function __construct(protected FileLogic $fileLogic) {}

    public function index()
    {
        $users = User::all();
        return view("users.users_table", compact("users"));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $permissions = [

            // user management
            'User Permissions' => [
                'manage-users',
                'create-user',
                'edit-user',
                'update-user',
                'delete-user',
                'view-user',
                'assign-role',
                'assign-permission',
                'view-users'
            ],

            // team management
            'Team Permissions' => [
                'manage-permissions',
                'manage-members',
                'create-team',
                'edit-team',
                'update-team',
                'delete-team',
                'send-invitation',
                'manage-settings'
            ],

            // project management
            'Board Permissions' => [
                'manage-projects',
                'create-project',
                'edit-project',
                'delete-project',
                'view-projects'
            ],

            // task management
            'Task Permissions' => [
                'manage-tasks',
                'create-task',
                'edit-task',
                'delete-task',
                'view-tasks',
                'assign-tasks',
                'archive-task'
            ],
        ];

        return view("users.user_edit", compact("user", "permissions"));
    }

    public function show($id)
    {
        if ((!Auth::user()->hasPermissionTo('view-user')) && (Auth::user()->id != $id)) {
            abort(403);
        }
        $user = User::with([
            'teams',
            'cards' => function ($query) {
                $query->orderBy('is_done');
            }
        ])->findOrFail($id);
        return view("users.user_show", compact("user"));
    }
    public function showCalendar()
    {
        $user = auth()->user();

        if ($user->hasRole('super-admin')) {
            // Super admin sees all non-archived tasks
            $tasks = Card::where('archive', false)->whereNull('archived_at')->get();
        } elseif ($user->hasRole('admin')) {
            // Admin sees tasks from teams they own
            $teamIds = $user->teamRelations()
                ->where('status', 'Owner')
                ->pluck('team_id');

            $tasks = Card::where('archive', false)->whereNull('archived_at')
                ->where(function ($q) use ($teamIds, $user) {
                    $q->whereHas('column.board', function ($query) use ($teamIds) {
                        $query->whereIn('team_id', $teamIds);
                    })->orWhereHas('users', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                })->get();
        } else {
            // Regular users see their own tasks and team tasks
            $teamIds = $user->teams()->pluck('teams.id');

            $tasks = Card::where('archive', false)->whereNull('archived_at')
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->get();
        }

        // Convert to calendar events
        $userTasks = $tasks->load(['column.board.team'])->map(function ($task) {
            return [
                'id'    => $task->id,
                'title' => $task->name ?? 'No Title',
                'start' => $task->start_date ?? $task->created_at->toDateString(),
                'end' => $task->end_date ?? $task->created_at,
                'status' => $task->is_done,
                'url'   => route('viewCard', [
                    'team_id' => $task->column->board->team->id,
                    'board_id' => $task->column->board_id,
                    'card_id' => $task->id
                ]),
            ];
        });

        return view("task_calendar", compact("userTasks"));
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                "name" => "required|min:1|max:35",
                "email" => 'unique:users,email|required|email',
                "image_path" => "nullable",
                "password" => "required|min:8",
                "role" => "required",
                'permissions' => 'array',
                'permissions.*' => 'sometimes|string|exists:permissions,name',
            ]);

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->is_active = true;
            if ($request->hasFile('image_path')) {
                $coverImagePath = $request->file('image_path')->store('users', 'public');
                $user->image_path = URL::to(Storage::url($coverImagePath));
            }
            $user->password = bcrypt($request->password);
            $user->save();

            // Assign role if provided
            if ($request->filled('role')) {
                $user->assignRole($request->role);
            }
            // Assign permissions if provided
            if ($request->filled('permissions')) {
                $user->syncPermissions($request->permissions);
            }

            return redirect()->back()->with("notif", ["Success: User created successfully"]);
        } catch (\Exception $error) {
            return redirect()->back()->with("notif", [$error->getMessage()]);
        }
    }
    public function update(Request $request, $id)
    {
        try {

            $user = User::findOrFail($id);
            $request->validate([
                "name" => "required|min:1|max:35",
                "email" => 'unique:users,email,' . $user->id . '|required|email',
                "image_path" => "nullable|mimes:jpg,jpeg,png|max:10240",
                "password" => "nullable|min:8",
                "is_active" => "required|boolean",
                "role" => "required|in:super-admin,admin,member,observer",
                'permissions' => 'array',
                'permissions.*' => 'sometimes|string|exists:permissions,name',
            ]);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->is_active = $request->is_active;
            if ($request->hasFile('image_path')) {
                // Delete the old cover image if it exists
                if ($user->image_path) {
                    $oldCoverImagePath = str_replace(URL::to('/storage'), '', $user->image_path);
                    Storage::disk('public')->delete($oldCoverImagePath);
                }
                $coverImagePath = $request->file('image_path')->store('usersCoverImages', 'public');
                $user->image_path = URL::to(Storage::url($coverImagePath));
            }
            if ($request->filled("password")) {
                $user->password = bcrypt($request->password);
            }
            $user->save();

            // Update role if provided
            if ($request->filled('role')) {
                $user->syncRoles($request->role);
            }
            // Update permissions if provided
            if ($request->filled('permissions')) {
                $user->syncPermissions($request->permissions);
            } else {
                $user->syncPermissions([]); // Remove all permissions if none are provided
            }

            return redirect()->back()->with("notif", ["Success: Profile updated successfully"]);
        } catch (\Exception $error) {
            return redirect()->back()->with("notif", [$error->getMessage()]);
        }
    }

    public function showSetting()
    {
        return view("setting");
    }

    public function updateImage(Request $request)
    {
        $userId = Auth::user()->id;
        $validator = Validator::make($request->all(), ['image' => "required|mimes:jpg,jpeg,png|max:10240"]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), HttpResponse::HTTP_BAD_REQUEST);
        }

        $this->fileLogic->storeUserImage($userId, $request, "image");
        return response()->json(["message" => "success"]);
    }

    public function updateData(Request $request)
    {
        $userId = Auth::user()->id;
        $request->validate([
            "name" => "required|min:1|max:35",
            "email" => 'unique:users,email,' . $userId . '|required|email',
        ]);

        $user = User::find($userId);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return redirect()->back()->with("notif", ["Success\nProfile updated successfully"]);
    }

    public function updatePassword(Request $request)
    {
        $userId = Auth::user()->id;
        $request->validate([
            "current_password" => "required",
            "new_password" => "required|confirmed|min:8|max:30",
            "new_password_confirmation" => "required"
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors("Wrong password please try again");
        }

        $user = User::find($userId);
        $user->password = bcrypt($request->new_password);
        $user->save();

        Auth::attempt(
            [
                "email" => $user->email,
                "password" => $request->new_password
            ],
            Auth::viaRemember()
        );

        return redirect()->back()->with("notif", ["Success\nPassword changed successfully"]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route("login");
    }

    public function deactivate(Request $request)
    {
        $user = User::find($request->id);
        $user->is_active = false;
        $user->save();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route("login")->with("notif", ["Success\nAccount Successfully Deleted!"]);
    }
}
