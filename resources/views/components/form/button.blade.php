@props(['primary', 'action', 'type', 'id', 'form', 'accept', 'outline'])

<button @isset($action) onclick="{{ $action }}" @endisset
    @isset($form) form="{{ $form }}" @endisset
    @isset($type) type="{{ $type }}" @endisset
    @isset($id) id="{{ $id }}" @endisset
    @if (isset($primary))
    {{ $attributes->merge(['class' => 'flex items-center justify-center w-full gap-2 px-6 py-1 text-base font-bold border-4 border-[#0a2436] rounded-full bg-[#0a2436] text-white hover:bg-white hover:text-[#0a2436]']) }}>
    @elseif (isset($outline))
    {{ $attributes->merge(['class' => 'flex items-center justify-center w-full gap-2 px-6 py-1 text-base font-bold border-4 border-[#0a2436] rounded-full bg-white text-[#0a2436] hover:bg-[#0a2436] hover:text-white']) }}>
    @else
    {{ $attributes->merge(['class' => 'flex items-center justify-center w-full gap-2 px-6 py-1 text-base font-bold border-4 rounded-full text-[#0a2436] bg-slate-300 border-slate-300 hover:bg-[#0a2436] hover:text-white hover:border-[#0a2436] ']) }}>
    @endif
    {{ $slot }}
</button>


