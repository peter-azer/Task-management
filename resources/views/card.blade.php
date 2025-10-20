@extends('layout.page')

@section('app-header')
<h1 class="text-xl font-bold">Card</h1>
@endsection

@section('app-side')


@section('app-side')
<div class="flex flex-col gap-6 px-8 pl-4 mt-2">
    <a class="w-full p-2 overflow-hidden border-2 border-[#e0edf3] cursor-pointer select-none rounded-xl"
        href="{{ route('board', ['team_id' => $team->id, 'board_id' => $board->id]) }}">
        <div class="flex items-center w-full gap-2">
            <div class="w-16 flex-shrink-0 border-2 border-black h-16 rounded-2xl bg-grad-{{ $board->pattern }}"></div>
            <article class="flex flex-col gap-2 text-sm">
                <h2 class="max-w-full overflow-hidden font-bold truncate text-[#d0d7dd]">{{ $board->name }}</h2>
                <p class="text-sm line-clamp-3 text-[#d0d7dd]">
                    {{ $team->description }}
                </p>
            </article>
        </div>
    </a>

    @can('manage-tasks')
    <section class="w-full overflow-hidden border-2 border-[#e0edf3] cursor-pointer select-none rounded-xl">
        @can("edit-task")
        <div data-role="menu-item" onclick="ModalView.show('editCard')"
            class="flex items-center w-full gap-3 px-6 py-2 text-[#fff] cursor-pointer select-none hover:bg-[#0f5490] hover:text-white">
            <x-fas-pen class="w-4 h-4 text-[#2c8bc6]" />
            <p> Edit </p>
        </div>
        <hr class="w-full border">
        @endcan

        @if ($workers->contains(Auth::user()))
        <div data-role="menu-item" onclick="ModalView.show('leaveCard')"
            class="flex items-center w-full gap-3 px-6 py-2 text-[#fff] cursor-pointer select-none hover:bg-[#0f5490] hover:text-white">
            <x-fas-right-from-bracket class="w-4 h-4 text-[#2c8bc6]" />
            <p> Quit Card </p>
        </div>
        <hr class="w-full border">
        @else
        @can('assign-tasks')
        <div data-role="menu-item" onclick="ModalView.show('assignTask')"
            class="flex items-center w-full gap-3 px-6 py-2 text-[#fff] cursor-pointer select-none hover:bg-[#0f5490] hover:text-white">
            <x-fas-plus class="w-4 h-4 text-[#2c8bc6]" />
            <p> Assign to Member </p>
        </div>
        <hr class="w-full border">
        @endcan
        @endif

        @can ('delete-task')
        <div data-role="menu-item" onclick="ModalView.show('deleteCard')"
            class="flex items-center w-full gap-3 px-6 py-2 text-red-300 cursor-pointer select-none hover:bg-[#0f5490] hover:text-white">
            <x-fas-trash class="w-4 h-4 text-red-300" />
            <p>Delete</p>
        </div>
        @endcan
    </section>
    @endcan
</div>
@endsection

@section('content')
<div class="flex flex-col w-full h-full">
    <header class="w-full h-24 flex items-center p-6 bg-pattern-{{ $team->pattern }} border-b border-gray-200">
    </header>

    {{-- page content --}}
    <div class="flex flex-grow gap-8 px-6 py-4 overflow-hidden">

        {{-- left section --}}
        <section class="flex flex-col flex-grow h-full px-2 pr-6 overflow-x-hidden overflow-y-scroll">
            <article class="flex flex-col gap-2">
                <div class="flex items-start gap-2">
                    <p>#</p>
                    <h2 class="text-2xl font-bold">{{ $card->name }}</h2>
                </div>
                <hr class="border">
                <div class="w-full h-auto p-2 px-5 mt-1 rounded bg-slate-50">
                    <div class="text-base text-left" style="white-space: pre-line;">
                        @if ($card->description && $card->description != 'N/A')
                        {{ $card->description }}
                        @else
                        <div class="flex items-center justify-center w-full h-full text-gray-500">
                            - no description -
                        </div>
                        @endif
                    </div>
                </div>
            </article>
            @if (Auth::user())
            <form class="flex items-end w-full gap-4 mt-3" rows="30" id="search-form"
                action="{{ route('commentCard', ['team_id' => $team->id, 'board_id' => $board->id, 'card_id' => $card->id]) }}"
                method="POST">
                @csrf
                <x-form.textarea name="content" placeholder="Add Comment.." required />
                <x-form.button type="submit" outline class="h-min w-min">
                    <x-fas-comment-medical class="w-4 h-4" />Post
                </x-form.button>
            </form>
            @endif
            <hr class="w-full mt-4 border">

            <div class="flex flex-col w-full h-4 gap-6 mt-4">
                @foreach ($histories as $event)
                <div class="flex flex-col items-end w-full">
                    <div class="flex items-start w-full gap-3">
                        <div class="flex-grow-0 flex-shrink-0 w-11 h-11">
                            <x-avatar name="{{ $event->user->name }}" asset="{{ $event->user->image_path }}" />
                        </div>
                        <div class="flex-grow w-full min-h-full px-4 py-2 bg-slate-100 rounded-xl ">
                            @if ($event->type == 'comment')
                            <p class="font-bold">{{ $event->user->name }}</p>
                            <p>{{ $event->content }}</p>
                            @elseif ($event->type == 'event')
                            <p class="text-base font-bold">{{ $event->user->name }}, {{ $event->content }}</p>
                            @endif
                        </div>
                    </div>
                    <p class="pr-4 text-xs text-gray-700">{{ $event->created_at }}</p>
                </div>
                @endforeach
            </div>
        </section>

        {{-- right-section --}}
        <aside class="flex flex-col h-full gap-4 w-72">
            <h2 class="ml-4 text-2xl font-bold">Wokers</h2>
            <div
                class="flex flex-col flex-grow w-full gap-2 p-4 overflow-x-hidden overflow-y-auto truncate border-2 border-gray-200 rounded-xl">
                @foreach ($workers as $worker)
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-4">
                        <x-avatar name="{{ $worker->name }}" asset="{{ $worker->image_path }}"
                            class="!flex-shrink-0 !flex-grow-0 w-12" />
                        <p class="w-40 truncate">{{ $worker->name }}</p>
                    </div>
                </div>
                @endforeach
            </div>

        </aside>
    </div>
</div>

@can ('delete-task')
<template is-modal="deleteCard">
    <form class="flex flex-col items-center justify-center w-full h-full gap-6 p-4" method="POST"
        action="{{ route('deleteCard', ['team_id' => $team->id, 'board_id' => $board->id, 'card_id' => $card->id]) }}">
        @csrf
        <input type="hidden" name="id" value="{{ Auth::user()->id }}">
        <p class="mb-6 text-lg text-center"> Are you sure you want to delete this card?</p>
        <div class="flex gap-6">
            <x-form.button type="submit">Yes</x-form.button>
            <x-form.button type="button" action="ModalView.close()" primary>No</x-form.button>
        </div>
    </form>
</template>
@endcan
@can("manage-tasks")
<template is-modal="assignTask">
    <form class="flex flex-col items-center justify-center w-full h-full gap-6 p-4" method="POST"
        action="{{ route('assignTask', ['team_id' => $team->id, 'board_id' => $board->id, 'card_id' => $card->id]) }}">
        @csrf
        <p class="mb-6 text-lg text-center"> Assign this task to member</p>
        <select name="id" id="id">
            @foreach ($team_members as $team_member)
            <option value="{{ $team_member->id }}" @if (Auth::user()->id == $team_member->id) selected @endif>
                {{ $team_member->name }}
            </option>
            @endforeach
        </select>
        <div class="flex gap-6">
            <x-form.button type="submit">Assign</x-form.button>
            <x-form.button type="button" action="ModalView.close()">Cancel</x-form.button>
        </div>
    </form>
</template>
@endcan

<template is-modal="leaveCard">
    <form class="flex flex-col items-center justify-center w-full h-full gap-6 p-4" method="POST"
        action="{{ route('leaveCard', ['team_id' => $team->id, 'board_id' => $board->id, 'card_id' => $card->id]) }}">
        @csrf
        <input type="hidden" name="id" value="{{ Auth::user()->id }}">
        <p class="mb-6 text-lg text-center"> Are you sure you want to qit this card?</p>
        <div class="flex gap-6">
            <x-form.button type="submit">Yes</x-form.button>
            <x-form.button type="button" action="ModalView.close()">No</x-form.button>
        </div>
    </form>
</template>

@can("edit-task")
<template is-modal="editCard">
    <div class="flex flex-col w-full gap-4 p-4">
        <h1 class="text-3xl font-bold">Edit Card</h1>
        <hr>
        <form
            action="{{ route('updateCard', ['team_id' => $team->id, 'board_id' => $board->id, 'card_id' => $card->id]) }}"
            method="POST" class="flex flex-col gap-4">
            @csrf
            <x-form.textarea name="card_name" label="Card's Title" required value="{{ $card->name }}" />
            <div class="mb-4">
                <label for="start_date" class="block mb-1 font-semibold text-gray-700">Start Date & Time</label>
                <input type="datetime-local" name="start_date" id="start_date"
                    class="w-full p-2 border-2 border-gray-200 rounded"
                    value="{{ \Carbon\Carbon::parse($card->start_date)->format('Y-m-d\TH:i') }}"
                    placeholder="Start Date & Time">
            </div>

            <div class="mb-4">
                <label for="end_date" class="block mb-1 font-semibold text-gray-700">End Date & Time</label>
                <input type="datetime-local" name="end_date" id="end_date"
                    class="w-full p-2 border-2 border-gray-200 rounded"
                    value="{{ \Carbon\Carbon::parse($card->end_date)->format('Y-m-d\TH:i') }}"
                    placeholder="End Date & Time">
            </div>
            <x-form.textarea name="card_description" label="Card's Description" value="{{ $card->description }}" />
            <x-form.button class="mt-4" type="submit" primary>Submit</x-form.button>
        </form>
    </div>
</template>
@endcan

@endsection

@pushOnce('page')
<script>
    ModalView.onShow('editCard', (modal) => {
        modal.querySelectorAll("form[action][method]").forEach(
            form => form.addEventListener("submit", () => PageLoader.show())
        );
    });

    ModalView.onShow('leaveCard', (modal) => {
        modal.querySelectorAll("form[action][method]").forEach(
            form => form.addEventListener("submit", () => PageLoader.show())
        );
    });

    ModalView.onShow('assignTask', (modal) => {
        modal.querySelectorAll("form[action][method]").forEach(
            form => form.addEventListener("submit", () => PageLoader.show())
        );
    });

    @if(Auth::user()->id == $owner->id)
    ModalView.onShow('deleteCard', (modal) => {
        modal.querySelectorAll("form[action][method]").forEach(
            form => form.addEventListener("submit", () => PageLoader.show())
        );
    });
    @endif
</script>
@endPushOnce