<?php

use Livewire\Component;
use App\Models\Kategori;

new class extends Component {

    public $nama = '';
    public $kategori_id = null;
    public $isEdit = false;
    public $showForm = false;

    protected $rules = [
        'nama' => 'required|min:3'
    ];

    public function store()
    {
        $this->validate();

        Kategori::create(['nama' => $this->nama]);

        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Berhasil Menambah Kategori');

        $this->showForm = false;
    }

    public function edit($id)
    {
        $k = Kategori::findOrFail($id);

        $this->kategori_id = $id;
        $this->nama = $k->nama;
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function update()
    {
        $this->validate();

        Kategori::find($this->kategori_id)
            ->update(['nama' => $this->nama]);

        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Berhasil Mengubah Kategori');

        $this->showForm = false;
    }

    public function delete($id)
    {
        Kategori::find($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Berhasil Menghapus Produk');

    }

    public function resetForm()
    {
        $this->nama = '';
        $this->kategori_id = null;
        $this->isEdit = false;
    }

    public function getDataProperty()
    {
        return Kategori::latest()->get();
    }
};
?>

<div class="p-6 space-y-6">
    <h1 class="text-xl font-bold">Kategori</h1>
    @if($showForm)

        <!-- FORM -->
        <div class="bg-white p-4 rounded-xl shadow flex gap-2">

            <input wire:model="nama" class="border px-3 py-2 rounded-lg w-full" placeholder="Nama kategori">

            @if($isEdit)
                <button wire:click="update" class="bg-yellow-500 text-white px-4 py-2 rounded-lg">
                    Update
                </button>
            @else
                <button wire:click="store" class="bg-green-500 text-white px-4 py-2 rounded-lg">
                    Simpan
                </button>
            @endif

            <button wire:click="$set('showForm', false)" class="bg-gray-500 text-white px-4 py-2 rounded-lg">
                Kembali
            </button>

        </div>

    @else

        <!-- INDEX -->
        <div class="flex justify-between items-center">


            <button wire:click="$set('showForm', true)" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                <i class="fa fa-plus"></i> Tambah Kategori
            </button>
        </div>

        <table class="w-full border">
            <tr>
                <th class="p-2">Nama</th>
                <th class="p-2 text-center">Aksi</th>
            </tr>
            </tr>
            @foreach($this->data as $item)
                <tr class="border-t ">
                    <td class="p-2 text-center">{{ $item->nama }}</td>
                    <td width="30%" class="p-2 text-center">
                        <button wire:click="edit({{ $item->id }})" class="bg-yellow-500 text-white px-2 py-1 rounded">
                            <i class="fa fa-pen"></i>
                        </button>

                        <button wire:confirm="Delete : {{ $item->nama }}" wire:click="delete({{ $item->id }})"
                            class="bg-red-500 text-white px-2 py-1 rounded">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </table>

    @endif

</div>