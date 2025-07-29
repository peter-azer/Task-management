@extends('layout.page')

@section('app-header')
<h1 class="text-xl font-bold">Users</h1>
@endsection

@section('app-side')
<div class="flex flex-col gap-6 px-8 pl-4 mt-2">

    <section class="bg-[#0a2436] w-full overflow-hidden border border-transparent hover:border-[#2c8bc6] transition-all duration-200 cursor-pointer select-none rounded-xl">
        <div data-role="menu-item" onclick="ModalView.show('createUser')"
            class="flex items-center w-full gap-3 px-6 py-2 text-white transition-all duration-200 hover:bg-[#123850] rounded-xl">
            <x-fas-user-plus class="w-4 h-4 text-[#2c8bc6] transition-colors duration-200" />
            <p> Add User </p>
        </div>
    </section>

</div>
@endsection

@section('content')

<!-- create user modal -->
<template is-modal="createUser">
    <div class="flex flex-col w-full gap-4 p-4">
        <h1 class="text-3xl font-bold">Create User</h1>
        <hr>
        <form action="{{ route('user.store') }}" method="POST" class="flex flex-col gap-4">
            @csrf
            <x-form.text name="name" label="User Name" required />
            <x-form.text name="email" label="User E-mail" required />
            <x-form.password name="password" label="Password" required />
            <x-form.file name="image_path" label="Image" required />

            <!-- Active Toggle -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="member" selected>Member</option>
                    <option value="super-admin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="observer">Observer</option>
                </select>
            </div>
            <x-form.button class="mt-4" type="submit" primary>Submit</x-form.button>
        </form>
    </div>
</template>


<section class="overflow-x-auto rounded-lg shadow-md border border-gray-200 m-16">
    <table class="min-w-full divide-y divide-gray-200 bg-white">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Created</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $counter = 1; ?>
            @foreach ($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-500"><?php echo $counter; ?> </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900 flex items-center gap-4">
                    @if ($user->is_active)
                    <x-fas-circle class="text-green-500 w-2 h-2" />
                    @else
                    <x-fas-circle class="text-red-500 w-2 h-2" />
                    @endif
                    {{$user->name}}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{$user->email}}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{$user->getRoleNames()->first()}}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{optional($user->created_at)->format('Y-m-d')}}</td>
                <td class="px-6 py-4 text-sm text-right flex items-center justify-end gap-4">
                    <button class="text-gray-600 hover:text-gray-900 font-medium">
                        <a href="{{route('user.show', ['id' => $user->id])}}">
                            show
                        </a>
                    </button>
                    <button class="text-blue-600 hover:text-blue-900 font-medium">
                        <a href="{{route('user.edit', ['id' => $user->id])}}">
                            Edit
                        </a>
                    </button>
                </td>
            </tr>
            <?php $counter++; ?>
            @endforeach
            @if ($users->isEmpty())
            <tr>
                <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No users found.</td>
            </tr>
            @endif
        </tbody>
    </table>
</section>

@endsection



@pushOnce('page')
<script>
    ModalView.onShow("createUser", (modal) => {
        modal.querySelectorAll("form[method][action]").forEach(
            form => form.addEventListener("submit", (e) => {
                console.log("Form values:", Object.fromEntries(new FormData(form)));
                console.log("Form errors:", form.querySelectorAll(".error"));
                PageLoader.show();
            })
        );
    });

    ModalView.onShow("acceptInvite", async (modal, payload) => {
        PageLoader.show();
        const header = modal.querySelector("#header-overlay");
        const teamImage = modal.querySelector("#team-image");
        const ownerImage = modal.querySelector("#owner-image");
        const teamInitial = modal.querySelector("#team-initial");
        const teamDescription = modal.querySelector("#team-description");
        const ownerInitial = modal.querySelector("#owner-initial");
        const teamName = modal.querySelector("#team-name");
        const ownerName = modal.querySelector("#owner-name");
        const btnYes = modal.querySelector("#btn-yes");
        const btnNo = modal.querySelector("#btn-no");

        const response = await ServerRequest.get(
            `{{ url('team/${payload.team_id}/invite/' . Auth::user()->id) }}`)

        header.classList.add(`bg-pattern-${response.data.team_pattern}`);
        teamDescription.textContent = response.data.team_description;
        teamName.textContent = response.data.team_name;
        ownerName.textContent = response.data.owner_name;
        teamInitial.textContent = response.data.team_initial;
        ownerInitial.textContent = response.data.owner_initial;
        teamImage.src = response.data.team_image;
        ownerImage.src = response.data.owner_image;
        btnYes.formAction = response.data.accept_url;
        btnNo.formAction = response.data.reject_url;
        if (!response.data.team_image) teamImage.style.display = "none";
        if (!response.data.owner_image) ownerImage.style.display = "none";
        modal.querySelectorAll("a").forEach(
            link => link.addEventListener("click", () => PageLoader.show())
        );

        modal.querySelectorAll("button[type='submit']").forEach(
            form => form.addEventListener("click", () => PageLoader.show())
        );

        PageLoader.close();
    });
</script>
@endPushOnce