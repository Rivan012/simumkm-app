<?php

use App\Models\Kategori;
use App\Models\Produk;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;
    public $produk_id = null;
    public $isEdit = false;
    public $showForm = false;
    public $search = '';

    public $nama_produk, $kategori_id, $harga, $stok, $deskripsi, $foto;

    protected $rules = [
        'nama_produk' => 'required|min:3',
        'kategori_id' => 'required',
        'harga' => 'required|numeric',
        'stok' => 'required|numeric',
        'deskripsi' => 'required',
    ];

    public function getKategoriListProperty()
    {
        return Kategori::all();

    }

    public function store()
    {
        $this->validate();

        $namaFile = null; // ← ini kuncinya

        if ($this->foto) {
            $namaFile = time() . '_' . Str::slug($this->nama_produk) . '.' . $this->foto->getClientOriginalExtension();

            $this->foto->storeAs('produk', $namaFile, 'public');
        }

        Produk::create([
            'nama_produk' => $this->nama_produk,
            'kategori_id' => $this->kategori_id,
            'harga' => $this->harga,
            'stok' => $this->stok,
            'deskripsi' => $this->deskripsi,
            'foto' => $namaFile,
        ]);

        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Berhasil Menambah Produk');

        $this->showForm = false;
    }

    public function edit($id)
    {
        $p = Produk::findOrFail($id);

        $this->produk_id = $id;
        $this->nama_produk = $p->nama_produk;
        $this->kategori_id = $p->kategori_id;
        $this->harga = $p->harga;
        $this->stok = $p->stok;
        $this->deskripsi = $p->deskripsi;

        $this->isEdit = true;
        $this->showForm = true;
    }

    public function update()
    {
        // dd($this->foto);
        $this->validate();

        $produk = Produk::findOrFail($this->produk_id);

        $namaFile = $produk->foto; // default pakai foto lama

        // kalau upload foto baru
        if ($this->foto) {

            // 🔥 hapus foto lama
            if ($produk->foto && Storage::disk('public')->exists('produk/' . $produk->foto)) {
                Storage::disk('public')->delete('produk/' . $produk->foto);
            }

            // 🔥 rename file baru
            $namaFile = time() . '_' . Str::slug($this->nama_produk) . '.' . $this->foto->extension();

            $this->foto->storeAs('produk', $namaFile, 'public');
        }

        // 🔥 update database
        $produk->update([
            'nama_produk' => $this->nama_produk,
            'kategori_id' => $this->kategori_id,
            'harga' => $this->harga,
            'stok' => $this->stok,
            'deskripsi' => $this->deskripsi,
            'foto' => $namaFile,
        ]);

        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Berhasil Mengubah Produk');
        $this->showForm = false;
    }
    public function delete($id)
    {
        Produk::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Berhasil Menghapus Produk');

    }

    public function resetForm()
    {
        $this->reset([
            'nama_produk',
            'kategori_id',
            'harga',
            'stok',
            'deskripsi',
            'produk_id',
            'isEdit',
            'foto',
        ]);
    }

    public function getDataProperty()
    {
        return Produk::with('kategori')
            ->when($this->search, function ($q) {
                $q->where('nama_produk', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();
    }
};
?>
<div class="p-6 space-y-6">
    <h1 class="text-xl font-bold">Produk - {{ config('app.name')  }}</h1>

    @if($showForm)

        <!-- FORM -->
        <div class="bg-white p-4 rounded-xl shadow space-y-3">
            <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" enctype="multipart/form-data">
                <input wire:model="nama_produk" class="border px-3 py-2 rounded-lg w-full" placeholder="Nama Produk">
                <select wire:model="kategori_id" class="border px-3 py-2 rounded-lg w-full">
                    <option value="">-- pilih kategori --</option>

                    @foreach($this->kategoriList as $k)
                        <option value="{{ $k->id }}">
                            {{ $k->nama }}
                        </option>
                    @endforeach

                </select>
                <input wire:model="harga" type="number" class="border px-3 py-2 rounded-lg w-full" placeholder="Harga">
                <input wire:model="stok" type="number" class="border px-3 py-2 rounded-lg w-full" placeholder="Stok">
                <input wire:model="foto" type="file" accept="image/*" class="border px-3 py-2 rounded-lg w-full"
                    placeholder="Foto">
                <div wire:loading wire:target="foto">Uploading...</div>
                <textarea wire:model="deskripsi" class="border px-3 py-2 rounded-lg w-full"
                    placeholder="Deskripsi"></textarea>

                <div class="flex gap-2">
                    @if($isEdit)
                        <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded-lg">
                            Update
                        </button>
                    @else
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg">
                            Simpan
                        </button>
                    @endif

                    <button wire:click="$set('showForm', false)" class="bg-gray-500 text-white px-4 py-2 rounded-lg">
                        Kembali
                    </button>
                </div>
            </form>
        </div>

    @else

        <!-- INDEX -->
        <div class="flex justify-between items-center">
            <button wire:click="$set('showForm', true)" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                <i class="fa fa-plus"></i> Tambah Data
            </button>
        </div>

        <input type="text" wire:model.live="search" placeholder="Cari produk..." class="w-full mb-4 border p-2 rounded">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
            @foreach($this->data as $item)

                <div
                    class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                    <div class="relative h-48 w-full overflow-hidden bg-gray-100">
                        @if($item->foto)
                            <img src="{{ asset('storage/produk/' . $item->foto) }}" alt="{{ $item->nama_produk }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <div class="flex items-center justify-center h-full text-gray-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.587-1.587a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        @endif

                        <div class="absolute top-3 left-3">
                            <span
                                class="bg-white/90 backdrop-blur-sm text-gray-700 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-lg shadow-sm">
                                {{ $item->kategori->nama ?? 'Umum' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-gray-800 text-lg leading-tight truncate flex-1 mr-2">
                                {{ $item->nama_produk }}
                            </h3>
                            <span
                                class="text-xs font-medium px-2 py-1 rounded-full {{ $item->stok == 0 ? 'bg-red-100 text-red-600' : 'bg-blue-50 text-blue-600' }}">
                                Stok: {{ $item->stok }}
                            </span>
                        </div>

                        <div class="text-xl font-extrabold text-emerald-600 mb-4">
                            Rp {{ number_format($item->harga, 0, ',', '.') }}
                        </div>

                        <div class="flex gap-2 pt-4 border-t border-gray-50">
                            <button wire:click="edit({{ $item->id }})"
                                class="flex-1 inline-flex justify-center items-center gap-2 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white px-4 py-2 rounded-xl text-sm font-bold transition-colors duration-200">
                                <span><i class="fa fa-pen"></i></span> Edit
                            </button>

                            <button wire:click="delete({{ $item->id }})"
                                wire:confirm="Apakah Anda yakin ingin menghapus produk ini?"
                                class="flex-1 inline-flex justify-center items-center gap-2 bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white px-4 py-2 rounded-xl text-sm font-bold transition-colors duration-200">
                                <span><i class="fa fa-trash"></i></span> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

    @endif

</div>