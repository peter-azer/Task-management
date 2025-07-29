<?php

namespace App\Http\Controllers;

use App\Logic\FileLogic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

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
        return view("users.user_edit", compact("user"));
    }
    public function show($id)
    {
        $user = User::findOrFail($id)->load(['teams', 'cards']);
        return view("users.user_show", compact("user"));
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
