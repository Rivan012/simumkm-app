<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KedaiHijau - Kantin GB V</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Import Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased selection:bg-orange-500 selection:text-white">

    <!-- Navbar -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <a href="#" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                    <img src="{{ asset('logo.png') }}" alt="">
                </div>
                <h1 class="font-bold text-2xl tracking-tight text-gray-900">Kedai<span
                        class="text-green-500">Hijau</span></h1>
            </a>

            <div class="hidden md:flex space-x-8 text-sm font-medium text-gray-600">
                <a href="#" class="hover:text-orange-500 transition-colors duration-200">Beranda</a>
                <a href="#menu" class="hover:text-orange-500 transition-colors duration-200">Menu Populer</a>
            </div>

            <a href="{{ route('login') }}"
                class="bg-green-500 hover:bg-green-600 text-white text-sm font-semibold px-6 py-2.5 rounded-full shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                Masuk
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-white overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/food.png')] opacity-5"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-orange-50 rounded-full blur-3xl opacity-50"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 relative z-10 text-center">
            <span class="text-green-500 font-semibold tracking-wider uppercase text-sm mb-4 block">Murah, Enak dan
                Nikmat</span>
            <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 mb-6 leading-tight">
                Beli Makan Murah, <br class="hidden md:block">
                Langsung ke Kedai Hijau.
            </h1>
            <p class="text-lg text-gray-500 max-w-2xl mx-auto mb-10 leading-relaxed">
                Temukan berbagai macam hidangan makanan dan minuman favoritmu. Dimasak dengan bumbu pilihan.
            </p>

            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="#menu"
                    class="w-full sm:w-auto bg-green-500 hover:bg-green-600 text-white font-semibold px-8 py-4 rounded-full shadow-lg hover:shadow-orange-500/30 transition-all transform hover:-translate-y-1">
                    Lihat Menu
                </a>
                <a href="{{ route('login') }}"
                    class="w-full sm:w-auto bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold px-8 py-4 rounded-full shadow-sm transition-all">
                    Masuk
                </a>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Menu Favorit Kami</h2>
                <div class="w-24 h-1 bg-orange-500 mx-auto rounded-full"></div>
                {{ $produk->fragment('menu')->links() }}
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">

                @forelse($produk as $pr)
                    <div
                        class="group bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="relative h-56 overflow-hidden">
                            <img src="{{ asset('storage/produk/' . $pr->foto) }}" alt="Nasi Goreng"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 ease-in-out">
                            <!-- <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-orange-600 shadow-sm">
                                Terlaris
                            </div> -->
                        </div>

                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-bold text-xl text-gray-900">{{$pr->nama_produk}}</h3>
                                <div class="flex items-center text-yellow-400 text-sm">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <span class="text-gray-600 ml-1">{{$pr->rating ?? '0'}}</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mb-6 line-clamp-2">
                                {{ $pr->deskripsi }}
                            </p>

                            <div class="flex items-center justify-between mt-auto">
                                <p class="font-bold text-2xl text-orange-600">Rp {{ $pr->harga }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>

            <!-- <div class="mt-16 text-center">
                <button class="px-8 py-3 bg-white border-2 border-gray-200 hover:border-orange-500 text-gray-700 hover:text-orange-500 font-semibold rounded-full transition-colors">
                    Lihat Semua Menu
                </button>
            </div> -->
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left mb-8">
                <div>
                    <h3 class="font-bold text-2xl text-white mb-4">Kedai<span class="text-green-500">Hijau</span></h3>
                    <p class="text-sm text-gray-400 max-w-xs mx-auto md:mx-0">Murah, Enak dan Nikmat</p>
                </div>
                <div>
                    <!-- <h4 class="font-semibold text-white mb-4">Tautan Cepat</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-orange-500 transition">Tentang Kami</a></li>
                        <li><a href="#menu" class="hover:text-orange-500 transition">Daftar Menu</a></li>
                        <li><a href="#" class="hover:text-orange-500 transition">Syarat & Ketentuan</a></li>
                    </ul> -->
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-4">Hubungi Kami</h4>
                    <ul class="space-y-2 text-sm">
                        <li>📍 Kantin GB V Universitas Bengkulu</li>
                        <li>✉️ halo@KedaiHijau.com</li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 border-t border-gray-800 text-center text-sm text-gray-500">
                © 2026 KedaiHijau. All rights reserved.
            </div>
        </div>
    </footer>

</body>

</html>