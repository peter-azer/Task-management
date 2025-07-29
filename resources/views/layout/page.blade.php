@extends('layout.base')

@section('body')
<div id="app" x-data="{ sidebar_is_open: true }" data-role="layout-page" class="flex w-full h-screen overflow-hidden">
    <aside class="flex flex-col h-full overflow-hidden transition-all border-r-2 border-b-[#e0edf3] bg-primary-900"
        x-bind:class="sidebar_is_open ? 'w-80' : 'w-0'">
        <img
            src="{{ asset('image/logo.png') }}"
            id="logo"
            class="flex items-center justify-center w-52 m-5 text-2xl font-extrabold tracking-widest cursor-default select-none" />

        <section class="flex flex-col items-center justify-start w-full gap-2 overflow-x-hidden overflow-y-auto">
            <div id="menu" class="flex flex-col items-center justify-start gap-2 w-full">

                <a data-role="menu-item" href="{{ route('setting') }}"
                    class="flex items-center justify-start w-full gap-3 px-6 py-2 text-sm text-white cursor-pointer select-none {{ Route::currentRouteName() == 'setting' ? 'bg-[#2c8bc6] hover:bg-[#0f5490] rounded-lg' : 'hover:bg-[#0f5490] hover:text-white' }} hover:rounded-md duration-200">
                    <x-fas-gear class="w-6 h-6
                    {{ Route::currentRouteName() == 'setting' ? 'text-white' : 'text-[#2c8bc6]' }}
                    " />
                    <p class="text-lg font-normal"> Setting </p>
                </a>

                <a data-role="menu-item" href="{{ route('home') }}"
                    class="flex items-center justify-start w-full gap-3 px-6 py-2 text-sm text-white cursor-pointer select-none {{ Route::currentRouteName() == 'home' ? 'bg-[#2c8bc6] hover:bg-[#0f5490] rounded-lg' : 'hover:bg-[#0f5490] hover:text-white' }} hover:rounded-md duration-200">
                    <x-fas-cube class="w-6 h-6
                    {{ Route::currentRouteName() == 'home' ? 'text-white' : 'text-[#2c8bc6]' }}
                    " />
                    <p class="text-lg font-normal"> Team </p>
                </a>
                @if (auth()->user()->hasRole('super-admin'))
                <a data-role="menu-item" href="{{ route('users') }}"
                    class="flex items-center justify-start w-full gap-3 px-6 py-2 text-sm text-white cursor-pointer select-none {{ Route::currentRouteName() == 'users' ? 'bg-[#2c8bc6] hover:bg-[#0f5490] rounded-lg' : 'hover:bg-[#0f5490] hover:text-white' }} hover:rounded-md duration-200">
                    <x-fas-user class="w-6 h-6
                    {{ Route::currentRouteName() == 'users' ? 'text-white' : 'text-[#2c8bc6]' }}
                    " />
                    <p class="text-lg font-normal"> Users </p>
                </a>
                @endif
            </div>

            @hasSection('app-side')
            <div class="flex-grow w-full">
                @yield('app-side')
            </div>
            @endif
        </section>
    </aside>

    <div class="flex flex-col items-center content-center flex-1 h-full overflow-y-auto">
        <header data-role="app-header" class="sticky flex items-center justify-between w-full h-16 px-6 shadow">
            <div class="flex items-center gap-4">
                <div id="sidebar-button" class="w-6 h-6" x-on:click="sidebar_is_open = !sidebar_is_open">
                    <template x-if="sidebar_is_open">
                        <x-fas-square-xmark class="text-[#2c8bc6]" />
                    </template>

                    <template x-if="!sidebar_is_open">
                        <x-fas-square-poll-horizontal class="text-[#2c8bc6]" />
                    </template>
                </div>

                <span class="text-[#0a2436]">
                    @yield('app-header')
                </span>
            </div>


            <div class="flex items-center justify-center gap-2">
                <p class="text-[#0d3858]"> <span class="font-bold ">Hello, </span> {{ Auth::user()->name }}</p>
                <x-avatar name="{{ Auth::user()->name }}" asset="{{ Auth::user()->image_path }}" class="w-12 h-12"
                    href="{{ route('setting') }}" />
            </div>
        </header>
        <div class="flex-grow w-full overflow-y-auto">
            @yield('content')
        </div>
    </div>
</div>
@endsection

@pushOnce('component')
<x-server-request-script />
@endPushOnce

@pushOnce('page')
<script>
    document.querySelectorAll("a").forEach(
        link => link.addEventListener("click", () => PageLoader.show())
    );

    document.querySelectorAll("form[action][method]").forEach(
        form => form.addEventListener("submit", () => PageLoader.show())
    );
</script>
@endPushOnce