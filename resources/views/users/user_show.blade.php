@extends('layout.page')

@section('content')
<div class="overflow-x-auto rounded-lg shadow-md border border-gray-200 m-16 p-6">
    @if(auth()->user()->hasRole('super-admin'))
    <h2 class="text-2xl font-semibold mb-6 text-gray-700">User Details</h2>
    @else
    <h2 class="text-2xl font-semibold mb-6 text-gray-700">Member Tasks ({{ $user->name }})</h2>
    @endif

    @if(auth()->user()->hasRole('super-admin'))
    <div class="space-y-5">

        <!-- Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <p class="mt-1 text-gray-900">{{ $user->name }}</p>
        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <p class="mt-1 text-gray-900">{{ $user->email }}</p>
        </div>

        <!-- Status -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Account Status</label>
            <p class="mt-1">
                <span class="inline-block px-3 py-1 text-sm font-medium rounded-full
                    {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </p>
        </div>

        <!-- Role -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Role</label>
            <p class="mt-1 text-gray-900 capitalize">{{ $user->getRoleNames()->first() }}</p>
        </div>

        <!-- Profile Image -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Profile Image</label>
            @if ($user->image_path)
            <img src="{{ asset($user->image_path) }}" alt="Profile Image" class="w-20 h-20 mt-2 rounded-full object-cover">
            @else
            <p class="mt-1 text-gray-500 italic">No image uploaded</p>
            @endif
        </div>

        <!-- Created At -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Created At</label>
            <p class="mt-1 text-gray-900">{{ $user->created_at->format('F j, Y h:i A') }}</p>
        </div>

        <!-- User Teams -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Teams</label>
            <p class="flex justify-start items-center gap-2 mt-1">
                @if ($user->teams->isEmpty())
                <span class="text-gray-500 italic">No teams assigned</span>
                @else
                @foreach ($user->teams as $team)
                <a href="{{ route('viewTeam', ['team_id' => $team->id]) }}">
                    <span class="inline-block px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                        {{ $team->name }}
                    </span>
                </a>
                @endforeach
                @endif
            </p>

            </p>
        </div>

        <!-- Back Button -->
        <div class="py-4">
            <a href="{{ route('users') }}" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md">
                Back to Users
            </a>
            <a href="{{route('user.edit', ['id' => $user->id])}}" class="inline-block bg-blue-200 hover:bg-blue-300 text-blue-800 py-2 px-4 rounded-md">
                Edit User Data
            </a>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($user->cards as $task)
        <a href="{{ route('viewCard', ['team_id' => $task->column->board->team_id, 'board_id' => $task->column->board->id, 'card_id' => $task->id]) }}" class="block">
            <div class="bg-white rounded-xl shadow p-4 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">{{ $task['name'] }}</h3>
                <p class="text-sm text-gray-500 mb-2">
                    <span class="block text-md text-black">
                        {{ $task->column->board->name }}
                    </span>
                    {{ $task['description'] ?? 'No description available' }}
                </p>

                <div class="text-sm text-gray-600 space-y-1">
                    <div>
                        <span class="font-medium text-gray-700">Start:</span>
                        {{ \Carbon\Carbon::parse($task['start_date'])->format('Y-m-d h:i A') }}
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">End:</span>
                        {{ \Carbon\Carbon::parse($task['end_date'])->format('Y-m-d h:i A') }}
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Status:</span>
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $task['is_done'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $task['is_done'] ? 'Done' : 'Pending' }}
                        </span>
                    </div>
                </div>

                <div class="mt-3 text-xs text-gray-400">
                    Card ID: {{ $task['id'] }} | Column ID: {{ $task['column_id'] }}
                </div>
            </div>
        </a>
        @endforeach
    </div>

</div>
@endsection
