@extends('layout.page')

@section('app-header')
<h1 class="text-xl font-bold">Delayed Tasks</h1>
@endsection

@section('content')
<div class="w-full h-full p-4">
    <div class="max-w-6xl mx-auto space-y-8">
        @php($groups = $groups ?? [])
        @if(empty($groups))
        <div class="p-8 text-center rounded-xl bg-slate-50 border border-slate-200">
            <p class="text-slate-600">No tasks found.</p>
        </div>
        @endif

        @foreach($groups as $group)
        @php($team = $group['team'])
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold">{{ $team->name }}</h2>
            </div>

            {{-- Delayed --}}
            @if(!empty($group['delayed']))
            <div>
                <h3 class="mb-2 text-lg font-semibold text-red-700">Delayed</h3>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($group['delayed'] as $card)
                    <a href="{{ url('team/'.optional(optional($card->column)->board)->team_id.'/board/'.optional($card->column)->board_id.'/card/'.$card->id.'/view') }}"
                        class="block p-3 border rounded-xl bg-red-50 border-red-200 hover:bg-red-100">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-medium truncate">{{ $card->name }}</p>
                            <span class="text-xs px-2 py-0.5 rounded bg-red-600 text-white">Late</span>
                        </div>
                        <div class="mt-2 text-xs text-red-700">
                            Due {{ \Carbon\Carbon::parse($card->end_date)->diffForHumans(null, true) }} ago
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Near deadline --}}
            @if(!empty($group['near_deadline']))
            <div>
                <h3 class="mb-2 text-lg font-semibold text-orange-700">Near Deadline (≤ 6 days)</h3>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($group['near_deadline'] as $card)
                    <a href="{{ url('team/'.optional(optional($card->column)->board)->team_id.'/board/'.optional($card->column)->board_id.'/card/'.$card->id.'/view') }}"
                        class="block p-3 border rounded-xl bg-orange-50 border-orange-200 hover:bg-orange-100">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-medium truncate">{{ $card->name }}</p>
                            <span class="text-xs px-2 py-0.5 rounded bg-orange-500 text-white">Soon</span>
                        </div>
                        <div class="mt-2 text-xs text-orange-700">
                            Due {{ \Carbon\Carbon::parse($card->end_date)->diffForHumans() }}
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Normal --}}
            @if(!empty($group['normal']))
            <div>
                <h3 class="mb-2 text-lg font-semibold text-slate-700">Other Tasks</h3>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($group['normal'] as $card)
                    <a href="{{ url('team/'.optional(optional($card->column)->board)->team_id.'/board/'.optional($card->column)->board_id.'/card/'.$card->id.'/view') }}"
                        class="block p-3 border rounded-xl bg-white border-slate-200 hover:bg-slate-50">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-medium truncate">{{ $card->name }}</p>
                            @if($card->end_date)
                            <span class="text-xs px-2 py-0.5 rounded bg-slate-200 text-slate-700">{{ \Carbon\Carbon::parse($card->end_date)->toFormattedDateString() }}</span>
                            @endif
                        </div>
                        @if($card->is_done)
                        <div class="mt-2 text-xs text-green-700">Completed</div>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </section>

        <hr class="my-6">
        @endforeach
    </div>
</div>
@endsection