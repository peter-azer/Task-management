@extends('layout.base')

@pushOnce('head')
@endPushOnce

@section('body')
<div class="flex items-center content-center w-screen h-screen">

    <div class="items-center content-center hidden w-1/2 h-full none bg-pattern-zig-zag lg:flex">

        <div class="flex flex-col items-center justify-center gap-4 p-6 m-auto bg-white shadow-2xl">
            <img
                src="{{ asset('image/logo_colored.png') }}"
                id="logo"
                class="flex items-center justify-center w-80 m-5 text-2xl font-extrabold tracking-widest cursor-default select-none" />
            <p class="mt-4 text-lg font-regullar"> From <span class="font-bold">To-Do</span> to <span
                    class="font-bold">Done</span> </p>
        </div>

    </div>

    <div class="flex items-center content-center flex-1 h-full bg-pattern-zig-zag lg:bg-none">
        @yield('form')
    </div>

</div>
@endsection