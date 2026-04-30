<?php

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    public $selectedOrder = null;
    public $query = '';
    public $showProses = false;
    public $tampil;
    public $status;
    protected $paginationTheme = 'tailwind';

    // ======================
    // LIST ORDER + SEARCH
    // ======================

    public function getOrderListProperty()
    {
        $data = Order::with('user');

        // 🔍 SEARCH
        if ($this->query !== '') {
            $search = strtolower($this->query);

            $data->where(function ($q) use ($search) {
                $q->whereHas('user', function ($qq) use ($search) {
                    $qq->whereRaw('LOWER(name) LIKE ?', ["%$search%"]);
                })
                    ->orWhereRaw('LOWER(tanggal_pemesanan) LIKE ?', ["%$search%"])
                    ->orWhereRaw('CAST(total AS CHAR) LIKE ?', ["%$search%"]);
            });
        }

        // 🔒 FILTER ROLE
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'kasir') {
            $data->where('user_id', Auth::user()->id);
        }

        return $data->latest()->paginate(10);
    }

    // reset page kalau search berubah
    public function updatingQuery()
    {
        $this->resetPage();
    }

    // ======================
    // DETAIL
    // ======================
    public function showDetail($id)
    {
        $this->selectedOrder = Order::with('detail.produk', 'user')
            ->findOrFail($id);
    }

    public function closeDetail()
    {
        $this->selectedOrder = null;
    }

    public function proses($id)
    {
        $this->tampil = Order::findOrFail($id);

        $this->showProses = true;
    }
    public function update()
    {
        $this->tampil->update(['status' => $this->status]);
        $this->dispatch('notify', type: 'success', message: 'Transaksi berhasil di ubah ');
        $this->showProses = false;
    }
    public function close()
    {
        $this->showProses = false;
    }
    public function delete($id)
    {
        Order::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Transaksi berhasil dihapus');
    }
};
?>
<div class="p-4 md:p-6 space-y-6">

    <h1 class="text-xl font-bold">History Transaksi </h1>

    <!-- 🔍 SEARCH -->
    <input type="text" wire:model.live="query" placeholder="Cari nama / tanggal / total..."
        class="mb-3 border p-2 rounded w-full">
    @if ($showProses)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col max-h-[calc(100vh-8rem)] sticky top-24">

            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-white rounded-t-2xl">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-indigo-600 rounded-full"></span>
                    Detail Pesanan
                </h2>
                <span
                    class="bg-indigo-50 text-indigo-600 text-[11px] font-bold px-2.5 py-1 rounded-lg border border-indigo-100">
                    #{{ $this->tampil->midtrans_order_id ?? 'INV-000' }}
                </span>
            </div>

            <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </span>
                    <input type="text" wire:model="nama_pelanggan"
                        placeholder="{{ $this->tampil->user->name ?? 'Nama Pelanggan' }}"
                        class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all bg-white shadow-sm">
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-3 bg-white">
                @foreach ($this->tampil->detail as $dt)
                    <div class="flex justify-between items-center group pb-3 border-b border-gray-50 last:border-0 last:pb-0">
                        <div class="flex-1 pr-3">
                            <h4 class="text-sm font-bold text-gray-800 leading-tight mb-1">{{ $dt->produk->nama_produk }}</h4>
                            <div class="text-[11px] text-gray-500 font-medium">
                                {{ $dt->qty }}x @ Rp {{ number_format($dt->produk->harga, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="text-sm font-black text-gray-800 bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-100">
                            Rp {{ number_format($dt->produk->harga * $dt->qty, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-gray-50 rounded-b-2xl p-5 border-t border-gray-200 border-dashed">

                <div class="flex justify-between items-center mb-5">
                    <span class="text-sm font-bold text-gray-500">Total Tagihan</span>
                    <span class="text-lg font-black text-indigo-600">
                        Rp {{ number_format($this->tampil->detail->sum('subtotal'), 0, ',', '.') }}
                    </span>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-600 mb-1.5">Ubah Status Pesanan</label>
                    <div class="relative">
                        <select wire:model="status"
                            class="w-full pl-3 pr-10 py-2.5 appearance-none border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all bg-white shadow-sm cursor-pointer">
                            <option value="pending">Pending</option>
                            <option value="Proses">Proses Pesanan</option>
                            <option value="berhasil">Pembayaran Berhasil</option>
                            <option value="selesai">Selesai</option>
                            <option value="batal">Batal</option>
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </span>
                    </div>
                </div>

                <button wire:click="update"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-200 transition-all active:scale-95 flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    SIMPAN PERUBAHAN
                </button>

                <button wire:click="close"
                    class="w-full mt-3 text-xs font-bold text-gray-400 hover:text-gray-700 py-2 transition-colors">
                    Tutup Jendela
                </button>
            </div>
        </div>
    @else
        <!-- TABLE -->
        <div class="bg-white rounded-xl shadow overflow-x-auto">

            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">Tanggal</th>
                        <th class="p-2 text-left">Member</th>
                        <th class="p-2 text-left">Total</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($this->orderList as $o)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-2">{{ $o->tanggal_pemesanan }}</td>

                            <td class="p-2">
                                {{ $o->user->name ?? '-' }}
                            </td>

                            <td class="p-2">
                                Rp {{ number_format($o->total) }}
                            </td>
                            <td class="p-2">
                                {{ $o->status }}
                            </td>

                            <td class="p-2 text-center">
                                <button wire:click="showDetail({{ $o->id }})" class="bg-blue-500 text-white px-3 py-1 rounded">
                                    <i class="fa fa-eye"></i>
                                </button>
                                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'kasir')
                                    <button wire:click="proses({{ $o->id }})" class="bg-yellow-500 text-white px-3 py-1 rounded">
                                        <i class="fa-solid fa-rotate"></i>
                                    </button>
                                    <button wire:click="delete({{ $o->id }})"
                                        wire:confirm="Apakah Anda yakin ingin menghapus pesanan ini?"
                                        class="bg-red-500 text-white px-3 py-1 rounded">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    

                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center p-4 text-gray-500">
                                Data tidak ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- PAGINATION -->
            <div class="p-3">
                {{ $this->orderList->links() }}
            </div>

        </div>
    @endif

    <!-- MODAL DETAIL -->
    @if($selectedOrder)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white w-full max-w-lg rounded-xl p-4">

                <h2 class="text-lg font-bold mb-3">Detail Transaksi</h2>

                <div class="text-sm mb-3">
                    <div>Tanggal: {{ $selectedOrder->tanggal_pemesanan }}</div>
                    <div>Status: {{ $selectedOrder->status ?? '-' }}</div>
                    <div>Member: {{ $selectedOrder->user->name ?? '-' }}</div>
                </div>

                <div class="border-t pt-2 space-y-1 max-h-60 overflow-y-auto">
                    @foreach($selectedOrder->detail as $d)
                        <div class="flex justify-between text-sm">
                            <span>{{ $d->produk->nama_produk }}</span>
                            <span>{{ $d->qty }} x {{ number_format($d->harga) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t mt-3 pt-2 flex justify-between font-bold">
                    <span>Total</span>
                    <span>Rp {{ number_format($selectedOrder->total) }}</span>
                </div>

                <button wire:click="closeDetail" class="mt-4 w-full bg-gray-600 text-white py-2 rounded">
                    Tutup
                </button>

            </div>

        </div>
    @endif

</div>