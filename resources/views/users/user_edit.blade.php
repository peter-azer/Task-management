@extends('layout.page')

@section('content')
<div class="max-w-xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-semibold mb-6 text-gray-700">Edit User</h2>

    <form method="POST" action="{{ route('user.update', $user->id) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <!-- Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <!-- Password -->
        <div>
            <label class="block text-sm font-medium text-gray-700">password</label>
            <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Active Toggle -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="is_active" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Active Toggle -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="member" {{ $user->getRoleNames()->first() == 'member' ? 'selected' : '' }}>Member</option>
                <option value="super-admin" {{ $user->getRoleNames()->first() == 'super-admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="admin" {{ $user->getRoleNames()->first() == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="observer" {{ $user->getRoleNames()->first() == 'observer' ? 'selected' : '' }}>Observer</option>
            </select>
        </div>

        <!-- Profile Image -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Profile Image</label>
            <input type="file" name="image_path" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            @if ($user->image_path)
            <img src="{{ asset($user->image_path) }}" alt="Profile Image" class="w-20 h-20 mt-2 rounded-full object-cover">
            @endif
        </div>

        <!-- permissions group -->

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-60 overflow-y-auto border p-3 rounded-md">
                @foreach ($permissions as $group => $perms)
                    <div>
                        <h3 class="font-semibold mb-2 text-gray-800">{{ $group }}</h3>
                        @foreach ($perms as $perm)
                            <div class="flex items-center mb-1">
                                <input type="checkbox" name="permissions[]" value="{{ $perm }}" id="perm_{{ $perm }}" class="h-4 w-4 text-blue-600 border-gray-300 rounded" {{ $user->hasPermissionTo($perm) ? 'checked' : '' }}>
                                <label for="perm_{{ $perm }}" class="ml-2 block text-sm text-gray-700">{{ ucwords(str_replace('-', ' ', $perm)) }}</label>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit -->
        <div class="pt-4">
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                Update User
            </button>
        </div>
        
    </form>
</div>
@endsection