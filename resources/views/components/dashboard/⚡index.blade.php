<?php

use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Models\Produk;
use App\Models\Keranjang;
use Illuminate\Support\Facades\Auth;

new #[Lazy] class extends Component {

    public $search = '';
    public $showCart = false;

    // ======================
    // PRODUK + SEARCH
    // ======================
    public function getProdukListProperty()
    {
        return Produk::with('kategori')
            ->when($this->search, function ($q) {
                $q->where('nama_produk', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();
    }
    public function add($id)
    {
        $produk = Produk::find($id);

        if (!$produk || $produk->stok <= 0)
            return;

        $cart = Keranjang::where('user_id', Auth::id())
            ->where('produk_id', $id)
            ->first();

        if ($cart) {
            if ($cart->qty >= $produk->stok)
                return;
            $cart->increment('qty');
        } else {
            Keranjang::create([
                'user_id' => Auth::id(),
                'produk_id' => $id,
                'qty' => 1
            ]);
        }

        $this->dispatch('$refresh');// 🔥 penting
    }

    public function kurang($id)
    {
        $cart = Keranjang::where('user_id', Auth::id())
            ->where('produk_id', $id)
            ->first();

        if (!$cart)
            return;

        if ($cart->qty > 1) {
            $cart->decrement('qty');
        } else {
            $cart->delete();
        }

        $this->dispatch('$refresh');
    }
    // ======================
    // CART (DB)
    // ======================
    public function getCartProperty()
    {
        return Keranjang::with('produk')
            ->where('user_id', Auth::id())
            ->get();
    }

    // ======================
    // TAMBAH
    // ======================
    public function tambah($id)
    {
        $produk = Produk::find($id);

        if (!$produk || $produk->stok <= 0)
            return;

        $cart = Keranjang::where('user_id', Auth::id())
            ->where('produk_id', $id)
            ->first();

        if ($cart) {
            if ($cart->qty >= $produk->stok)
                return;
            $cart->increment('qty');
        } else {
            Keranjang::create([
                'user_id' => Auth::id(),
                'produk_id' => $id,
                'qty' => 1
            ]);
        }

        $this->dispatch('$refresh');
    }

    // ======================
    // HAPUS
    // ======================
    public function hapus($id)
    {
        Keranjang::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        $this->dispatch('$refresh');
    }

    // ======================
    // TOTAL
    // ======================
    public function getTotalProperty()
    {
        return $this->cart->sum(
            fn($item) =>
            $item->qty * $item->produk->harga
        );
    }
};
?>

<div wire:init>

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-4">

        <h1 class="text-xl font-bold">Dashboard - Kedai Hijau</h1>

        <!-- CART ICON -->
        <div class="relative">

            <button wire:click="$toggle('showCart')"
                class="relative bg-white border p-2 rounded-full shadow hover:bg-gray-100">

                <i class="fa-solid fa-cart-shopping"></i>

                @if($this->cart->count() > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 rounded-full">
                        {{ $this->cart->count() }}
                    </span>
                @endif
            </button>

            <!-- DROPDOWN -->
            @if($showCart)
                <div class="absolute right-0 mt-2 w-72 bg-white border rounded-xl shadow-lg z-50 p-4">

                    <h2 class="font-bold mb-3">Keranjang</h2>

                    @forelse($this->cart as $item)
                        <div class="flex justify-between items-center text-sm mb-2">
                            <span>
                                {{ $item->produk->nama_produk }}

                            </span>
                            <span>{{ $item['nama'] }}</span>
                            <button wire:click="kurang({{ $item->produk_id }})" class="px-2 py-1 small">
                                <i class="fa fa-minus"></i>
                            </button>

                            <span>{{ $item->qty }}</span>

                            <button wire:click="add({{ $item->produk_id }})" class="px-2 py-1">
                                <i class="fa fa-plus"></i>
                            </button>

                            <div class="flex items-center gap-2">
                                <span>
                                    Rp {{ number_format($item->qty * $item->produk->harga) }}
                                </span>

                                <button wire:click="hapus({{ $item->id }})" class="text-red-500 text-xs">✖</button>
                            </div>
                        </div>
                    @empty
                        <div class="text-gray-500 text-sm">Keranjang kosong</div>
                    @endforelse

                    <div class="border-t mt-3 pt-3 font-bold flex justify-between">
                        <span>Total</span>
                        <span>Rp {{ number_format($this->total) }}</span>
                    </div>

                </div>
            @endif

        </div>
    </div>

    <!-- SEARCH -->
    <input type="text" wire:model.live="search" placeholder="Cari produk..." class="w-full mb-4 border p-2 rounded">

    <!-- PRODUK -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        @foreach($this->produkList as $p)

            <div @if($p->stok > 0) wire:click="tambah({{ $p->id }})" @endif class="group bg-white border border-gray-100 p-3 rounded-2xl shadow-sm flex flex-col h-full transition-all duration-300
                        {{ $p->stok > 0
            ? 'cursor-pointer hover:shadow-lg hover:border-indigo-300 active:scale-95'
            : 'opacity-50 cursor-not-allowed' }}">

                <div class="relative w-full aspect-square bg-gray-50 rounded-xl overflow-hidden mb-3">
                    @if($p->stok == 0)
                        <div class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                            Habis
                        </div>
                    @endif
                    @if($p->foto)
                        <img src="{{ asset('storage/produk/' . $p->foto) }}" alt="{{ $p->nama_produk }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.587-1.587a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col flex-grow">
                    <div class="text-[11px] font-semibold text-indigo-500 uppercase tracking-widest mb-1">
                        {{ $p->kategori->nama ?? 'Umum' }}
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="font-bold text-gray-800 truncate">
                            {{ $p->nama_produk }}
                        </div>

                        <div class="text-sm text-gray-500 whitespace-nowrap">
                            Stok: {{ $p->stok }}
                        </div>
                    </div>
                    <div class="mt-auto pt-3 flex items-center justify-between">
                        <div class="font-extrabold text-emerald-600 text-sm sm:text-base">
                            Rp {{ number_format($p->harga, 0, ',', '.') }}
                        </div>

                        <div
                            class="w-7 h-7 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-indigo-600 group-hover:text-white transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>