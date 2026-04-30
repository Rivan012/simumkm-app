@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="{{ config('app.name') }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <img src="{{ asset('logo.png') }}" alt="Logo">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Laravel Starter Kit" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <img src="{{ asset('logo.png') }}" alt="Logo">
        </x-slot>
    </flux:brand>
@endif
