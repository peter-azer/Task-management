@extends('layout.auth')

@section('form')
    <div class="flex flex-col items-center justify-center w-full h-full" data-role="page-register">
        <div class="flex flex-col items-center w-full py-40 bg-white justify-cente">
            <h1 class="mb-16 text-6xl text-[#0d3858] font-bold">
                Register
            </h1>

            <div data-role="login-container" class="w-full max-w-md px-10">

                {{-- Registration Form --}}
                <form action="{{ route('doRegister') }}" method="POST" class="flex flex-col gap-28">
                    @csrf
                    <div class="flex flex-col gap-6">
                        <x-form.text placeholder="Full Name" icon="fas-id-card-clip" name="name"
                            value="{{ old('name') }}" autofocus required></x-form.text>
                        <x-form.text placeholder="Email" icon="fas-user" name="email" value="{{ old('email') }}"
                            autofocus required></x-form.text>
                        <x-form.password placeholder="Password" name="password" icon="fas-lock" required></x-form.password>
                        <x-form.password placeholder="Password Confirmation" name="password_confirmation" icon="fas-lock"
                            required></x-form.password>
                    </div>


                    <div data-role="action-message" class="flex flex-col gap-2">
                        @if ($errors->any())
                            <p class="ml-4 text-sm font-medium text-red-500">{{ $errors->first() }}</p>
                        @endif
                        <button class="bg-[#0a2436] w-full overflow-hidden border border-transparent hover:border-[#2c8bc6] transition-all duration-200 cursor-pointer select-none rounded-full">
                        <p
                            class="w-full px-6 py-2 text-white transition-all duration-200 hover:bg-[#123850] rounded-full text-center">
                            Register
                        </p>
                        </button>
                        <a href="{{ route('login') }}" class="ml-6 font-light underline">Already have an account?
                            login now</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
