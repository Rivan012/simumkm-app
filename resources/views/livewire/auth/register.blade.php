<x-layouts::auth :title="__('Register')">

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

        <!-- Header -->
        <div class="text-center space-y-3">
            <h2 class="text-3xl font-extrabold bg-gradient-to-r 
                       from-blue-600 via-indigo-600 to-purple-600 
                       bg-clip-text text-transparent">
                Buat Akun Baru
            </h2>

            <p class="text-sm text-zinc-500">
                Isi data di bawah untuk mulai menggunakan aplikasi
            </p>
        </div>

        <!-- Status -->
        <x-auth-session-status 
            class="text-center text-sm bg-emerald-50 text-emerald-600 p-3 rounded-xl border border-emerald-200" 
            :status="session('status')" 
        />

        <!-- Form -->
        <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
            @csrf

            <flux:input
                name="name"
                :label="__('Nama Lengkap')"
                :value="old('name')"
                type="text"
                required
                autofocus
                placeholder="Nama lengkap"
                class="input-style"
            />

            <flux:input
                name="email"
                :label="__('Email')"
                :value="old('email')"
                type="email"
                required
                placeholder="nama@email.com"
                class="input-style"
            />

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                viewable
                placeholder="••••••••"
                class="input-style"
            />

            <flux:input
                name="password_confirmation"
                :label="__('Konfirmasi Password')"
                type="password"
                required
                viewable
                placeholder="••••••••"
                class="input-style"
            />

            <!-- Button -->
            <button type="submit"
                class="w-full py-3 rounded-xl text-white font-bold 
                       bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600
                       hover:opacity-90 transition shadow-lg">
                Daftar Sekarang
            </button>
        </form>

        <!-- Login -->
        <p class="text-center text-sm text-zinc-500 border-t pt-4">
            Sudah punya akun?
            <flux:link :href="route('login')" wire:navigate
                class="font-bold text-transparent bg-clip-text 
                       bg-gradient-to-r from-blue-600 to-pink-600 hover:underline">
                Masuk
            </flux:link>
        </p>

    </div>

</x-layouts::auth>