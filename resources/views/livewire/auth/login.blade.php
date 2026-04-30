<x-layouts::auth :title="__('Log in')">

    <!-- Background Blur -->
    <div class="absolute inset-0 z-0 pointer-events-none flex justify-center items-center">
        <div class="blob bg-purple-400 top-0 -left-10"></div>
        <div class="blob bg-blue-400 top-0 -right-10 delay-2000"></div>
        <div class="blob bg-pink-400 -bottom-10 left-20 delay-4000"></div>
    </div>

    <!-- Card -->
    <div class="relative z-10 w-full max-w-md mx-auto p-8 sm:p-10 
                rounded-3xl backdrop-blur-2xl 
                bg-white/70 dark:bg-zinc-900/70 
                border border-white/50 dark:border-zinc-700/50 
                shadow-2xl flex flex-col gap-6">

        <!-- Logo & Title -->
        <div class="text-center space-y-4">
            <a href="/" class="group inline-block relative">
                <div class="absolute -inset-3 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 
                            blur-xl opacity-40 group-hover:opacity-70 transition"></div>

                <img src="{{ asset('logo.png') }}"
                     class="relative h-16 mx-auto transition group-hover:scale-110"
                     alt="Logo">
            </a>

            <div>
                <h2 class="text-3xl font-extrabold bg-gradient-to-r 
                           from-blue-600 via-indigo-600 to-purple-600 
                           bg-clip-text text-transparent">
                    Selamat Datang
                </h2>

                <p class="text-sm text-zinc-500 mt-1">
                    Masuk ke akun Anda untuk melanjutkan
                </p>
            </div>
        </div>

        <!-- Status -->
        @if (session('status'))
            <div class="p-3 text-sm text-center rounded-xl 
                        bg-emerald-50 text-emerald-600 border border-emerald-200">
                <x-auth-session-status :status="session('status')" />
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf

            <flux:input
                name="email"
                :label="__('Email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                placeholder="nama@email.com"
                class="input-style"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    viewable
                    placeholder="••••••••"
                    class="input-style"
                />
            </div>

            <flux:checkbox
                name="remember"
                :label="__('Ingat saya')"
                :checked="old('remember')"
                class="text-purple-600"
            />

            <!-- Button -->
            <button type="submit"
                class="w-full py-3 rounded-xl text-white font-bold 
                       bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600
                       hover:opacity-90 transition shadow-lg">
                Masuk
            </button>
        </form>

        <!-- Register -->
        @if (Route::has('register'))
            <p class="text-center text-sm text-zinc-500 border-t pt-4">
                Belum punya akun?
                <flux:link :href="route('register')" wire:navigate
                    class="font-bold text-transparent bg-clip-text 
                           bg-gradient-to-r from-blue-600 to-pink-600 hover:underline">
                    Daftar
                </flux:link>
            </p>
        @endif

    </div>

</x-layouts::auth>