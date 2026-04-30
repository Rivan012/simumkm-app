<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- CSS -->
     

    <!-- JS -->
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('Platform')" class="grid">
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
            </flux:sidebar.group>
            @admin
            <flux:sidebar.group :heading="__('Master Data')" class="grid">
                <flux:sidebar.item icon="tag" :href="route('kategori')" :current="request()->routeIs('kategori')"
                    wire:navigate>
                    {{ __('Kategori') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="shopping-bag" :href="route('produk')" :current="request()->routeIs('produk')"
                    wire:navigate>
                    {{ __('Produk') }}
                </flux:sidebar.item>
            </flux:sidebar.group>
            @endadmin
            <flux:sidebar.group :heading="__('Orderan')" class="grid">
                <flux:sidebar.item icon="tag" :href="route('transaksi')" :current="request()->routeIs('transaksi')"
                    wire:navigate>
                    {{ __('Transaksi') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="shopping-bag" :href="route('history')" :current="request()->routeIs('history')"
                    wire:navigate>
                    {{ __('History') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

        </flux:sidebar.nav>


        <flux:spacer />
        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @persist('toast')
    <flux:toast.group>
        <flux:toast />
    </flux:toast.group>
    @endpersist

    @fluxScripts
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('pay-with-midtrans', (event) => {

                console.log("EVENT:", event);

                let token = event.snapToken ?? event[0]?.snapToken;

                console.log("TOKEN:", token);

                if (!token) {
                    alert("Snap token tidak ditemukan!");
                    return;
                }

                window.snap.pay(token, {
                    onSuccess: function (result) {
                        console.log(result);
                        alert("Pembayaran berhasil");
                        window.location.reload();
                    },
                    onPending: function () {
                        alert("Selesaikan pembayaran!");
                    },
                    onError: function () {
                        alert("Gagal!");
                    }
                });

            });
        });

    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', ({ type, message }) => {
                toastr[type](message);
            });
        });
    </script>
</body>

</html>