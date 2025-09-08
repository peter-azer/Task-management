@extends('layout.page')

@section('content')
<div class="overflow-x-auto rounded-lg shadow-md border border-gray-200 m-16 p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700">Archived Tasks ({{ $board[0]->name }})</h2>
        <a href="{{ route('board', ['team_id' => $board[0]->team_id, 'board_id' => $board[0]->id]) }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Board
        </a>
    </div>

    @if($board[0]->cards->isEmpty())
    <p class="text-gray-500">No archived tasks available.</p>
    @else
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($board[0]->cards as $task)
        <a href="{{ route('viewCard', ['team_id' => $task->column->board->team_id, 'board_id' => $task->column->board->id, 'card_id' => $task->id]) }}" class="block">
            <div class="bg-white rounded-xl shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $task['name'] }}</h3>
                    <div class="flex space-x-2">
                        <form method="POST" action="{{ route('doUnarchiveCard', ['team_id' => $task->column->board->team_id, 'board_id' => $task->column->board->id, 'card_id' => $task->id]) }}">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm">Unarchive</button>
                        </form>
                        <form method="POST" action="{{ route('deleteCard', ['team_id' => $task->column->board->team_id, 'board_id' => $task->column->board->id, 'card_id' => $task->id]) }}">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                        </form>

                    </div>
                </div>
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
    @endif
</div>
@endsection