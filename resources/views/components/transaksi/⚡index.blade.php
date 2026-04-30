<?php
use App\Models\Keranjang;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Midtrans\Config;
use Midtrans\Snap;

new #[Lazy] class extends Component {
    public $cart = [];
    public $user_id = null;
    public $bayar = '';
    public $lastOrder = null;
    public $search = '';

    // Properti Baru
    public $metode_pembayaran = 'tunai';
    public $snapToken = null;

    // ======================
    // DATA & COMPUTED
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

    public function getUserListProperty()
    {
        return User::all();
    }

    public function getTotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['qty'] * $item['harga']);
    }

    public function getKembalianProperty()
    {
        $bayar = (int) preg_replace('/[^0-9]/', '', $this->bayar);
        return max(0, $bayar - $this->total);
    }

    public function getBayarCleanProperty()
    {
        return (int) preg_replace('/[^0-9]/', '', $this->bayar);
    }

    // ======================
    // KONTROL KERANJANG
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

        $this->syncCartFromDB(); // 🔥 penting
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

        $this->syncCartFromDB();
    }
    public function hapus($id)
    {
        Keranjang::where('user_id', Auth::id())
            ->where('produk_id', $id)
            ->delete();

        $this->syncCartFromDB();
    }
    public function syncCartFromDB()
    {
        $this->cart = Keranjang::with('produk')
            ->where('user_id', Auth::id())
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->produk_id => [
                        'nama' => $item->produk->nama_produk,
                        'harga' => $item->produk->harga,
                        'qty' => $item->qty,
                    ]
                ];
            })
            ->toArray();
    }
    public function mount()
    {
        $this->syncCartFromDB();
    }
    // ======================
    // PROSES CHECKOUT
    // ======================
    public function checkout()
    {
        if (empty($this->cart))
            return;

        // 🔥 VALIDASI TUNAI
        if ($this->metode_pembayaran === 'tunai' && $this->bayarClean < $this->total) {
            session()->flash('error', 'Uang kurang!');
            return;
        }
        // Validasi Barang
        foreach ($this->cart as $item) {
            $produk = Produk::find($item['produk_id']);

            if (!$produk) {
                session()->flash('error', 'Produk tidak ditemukan!');
                return;
            }

            if ($produk->stok < $item['qty']) {
                session()->flash(
                    'error',
                    "Stok {$produk->nama_produk} tidak cukup! Sisa stok: {$produk->stok}"
                );
                return;
            }
        }
        // 🔥 TENTUKAN USER ID
        if (Auth::user()->role == 'admin' || Auth::user()->role == 'kasir') {

            if (!$this->user_id) {
                session()->flash('error', 'Silakan pilih member terlebih dahulu!');
                return;
            }

            $uid = $this->user_id;

        } else {
            $uid = Auth::user()->id;
        }

        // 🔥 SIMPAN ORDER
        $order = Order::create([
            'user_id' => $uid,
            'tanggal_pemesanan' => now(),
            'total' => $this->total,
            'status' => $this->metode_pembayaran === 'tunai' ? 'Pembayaran Berhasil' : 'pending',
            'metode' => $this->metode_pembayaran
        ]);

        // 🔥 SIMPAN DETAIL
        foreach ($this->cart as $id => $item) {
            if ($item['qty'] > Produk::find($id)->stok) {
                session()->flash('error', 'Stok tidak cukup!');
                return;
            }
            OrderDetail::create([
                'order_id' => $order->id,
                'produk_id' => $id,
                'qty' => $item['qty'],
                'harga' => $item['harga'],
                'subtotal' => $item['qty'] * $item['harga'],
            ]);

            // 🔥 POTONG STOK (TUNAI)
            if ($this->metode_pembayaran === 'tunai') {
                Produk::find($id)->decrement('stok', $item['qty']);
            }
        }

        // 🔥 CLEAR KERANJANG
        // 🔥 CLEAR KERANJANG
        if ($this->metode_pembayaran === 'tunai') {

            // hapus dari database
            Keranjang::where('user_id', Auth::id())->delete();
            // $this->dispatch('notify', type: 'success', message: 'Berhasil Melakukan Pesanan');

            // reset UI
            $this->cart = [];
            $this->bayar = '';
        }

        if ($this->metode_pembayaran === 'tunai') {
            $this->dispatch('notify', type: 'success', message: 'Berhasil Melakukan Pesanan');

        } else {
            $this->dispatch('notify', type: 'success', message: 'Gagal Melakukan Pesanan');

        }

        // ======================
        // MIDTRANS
        // ======================
        if ($this->metode_pembayaran === 'midtrans') {

            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = false;
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $orderId = 'INV-' . $order->id . '-' . time();

            $order->update([
                'midtrans_order_id' => $orderId
            ]);

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $this->total,
                ],
                'enabled_payments' => [
                    'qris',
                    'gopay',
                    'shopeepay',
                    'bank_transfer'
                ],
                'customer_details' => [
                    'first_name' => optional(auth()->user())->name ?? 'Guest',
                ],
                'callbacks' => [
                    'finish' => route('history'),
                ],
            ];

            try {
                $this->snapToken = Snap::getSnapToken($params);
                $this->dispatch('pay-with-midtrans', [
                    'snapToken' => $this->snapToken
                ]);
            } catch (\Exception $e) {
                $this->dispatch('notify', type: 'success', message: 'Error tidak diketahui');

            }
        }
    }
};
?>
<div class="p-4 md:p-6 grid grid-cols-1 md:grid-cols-3 gap-6">

    <div class="md:col-span-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                Katalog Produk
            </h2>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" wire:model.live="search" placeholder="Cari produk..."
                    class="pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all w-full sm:w-64 shadow-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($this->produkList as $p)
                    <div @if($p->stok > 0) wire:click="tambah({{ $p->id }})" @endif
                        class="group bg-white border border-gray-100 rounded-2xl p-3 shadow-sm transition-all duration-200 
                                                                                                                                                                                                                                {{ $p->stok == 0
                ? 'opacity-60 cursor-not-allowed bg-gray-50'
                : 'cursor-pointer hover:shadow-md hover:border-indigo-300 active:scale-95' 
                                                                                                                                                                                                                                }}">
                        <div class="relative aspect-square mb-3 rounded-xl overflow-hidden bg-gray-100">
                            @if($p->foto)
                                <img src="{{ asset('storage/produk/' . $p->foto) }}" alt="{{ $p->nama_produk }}"
                                    onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($p->nama_produk) }}&color=7F9CF5&background=EBF4FF';"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.587-1.587a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                            @endif

                            @if($p->stok == 0)
                                <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center">
                                    <span
                                        class="text-white text-[10px] font-black uppercase tracking-tighter bg-red-600 px-2 py-1 rounded">Habis</span>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-1">
                            <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest leading-none">
                                {{ $p->kategori->nama ?? 'Umum' }}
                            </div>
                            <div class="font-bold text-gray-800 text-sm truncate" title="{{ $p->nama_produk }}">
                                {{ $p->nama_produk }}
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <span class="font-extrabold text-gray-900 text-sm">
                                    {{ number_format($p->harga / 1000, 0) }}k
                                </span>
                                <span
                                    class="text-[10px] font-medium px-1.5 py-0.5 rounded {{ $p->stok <= 5 ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-500' }}">
                                    Sisa {{ $p->stok }}
                                </span>
                            </div>
                        </div>
                    </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-xl font-bold mb-4">Keranjang</h2>

        @foreach($cart as $id => $item)
            <div class="flex justify-between items-center mb-2 text-sm">

                <div class="flex items-center gap-2">
                    <!-- Tombol kurang -->


                    <span>{{ $item['nama'] }}</span>
                    <button wire:click="kurang({{ $id }})" class="px-2 py-1 small">
                        <i class="fa fa-minus"></i>
                    </button>
                    <span>{{ $item['qty'] }}</span>
                    <button wire:click="tambah({{ $id }})" class="px-2 py-1">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>

                <span>Rp {{ number_format($item['qty'] * $item['harga']) }}</span>
            </div>
        @endforeach

        <div class="border-t mt-4 pt-4">
            <div class="flex justify-between font-bold text-lg">
                <span>Total</span>
                <span>Rp {{ number_format($this->total) }}</span>
            </div>
        </div>
        @if(auth()->user()->role == 'admin' || auth()->user()->role == 'kasir')

            <select wire:model.live="user_id" class="border w-full mt-3 p-2">
                <option value="">-- Pilih Member (Opsional) --</option>
                @foreach($this->userList as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        @endif
        <div class="mt-4 bg-gray-50 p-3 rounded-lg">
            <label class="block text-sm font-bold mb-2">Metode Pembayaran:</label>
            <div class="flex flex-col gap-2">
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'kasir')

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="metode_pembayaran" value="tunai"> 💵 Tunai
                    </label>
                @endif
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" wire:model.live="metode_pembayaran" value="midtrans"> 📱 QRIS / E-Wallet
                </label>
            </div>
        </div>
        @if(auth()->user()->role == 'admin' || auth()->user()->role == 'kasir')

            @if($metode_pembayaran === 'tunai')

                <div class="mt-3">
                    <input type="text" wire:model.live="bayar"
                        oninput="let val = this.value.replace(/[^0-9]/g, ''); this.value = new Intl.NumberFormat('id-ID').format(val);"
                        class="border w-full p-2 rounded" placeholder="Jumlah Bayar">
                    <div class="text-right mt-2 text-sm font-semibold text-blue-600">
                        Kembalian: Rp {{ number_format($this->kembalian) }}
                    </div>
                </div>
            @endif
        @endif

        <button wire:click="checkout"
            class="w-full mt-4 p-3 rounded-lg font-bold text-white transition {{ empty($cart) || ($metode_pembayaran === 'tunai' && $this->bayarClean < $this->total) ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700' }}"
            {{ empty($cart) || ($metode_pembayaran === 'tunai' && $this->bayarClean < $this->total) ? 'disabled' : '' }}>
            {{ $metode_pembayaran === 'tunai' ? 'PROSES TRANSAKSI' : 'BAYAR VIA MIDTRANS' }}
        </button>

        @if(session()->has('error'))
        <div class="text-red-500 text-sm mt-2">{{ session('error') }}</div> @endif
        @if(session()->has('success'))
        <div class="text-green-500 text-sm mt-2">{{ session('success') }}</div> @endif
    </div>


</div>