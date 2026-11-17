<div data-pasangan data-maks="{{ $maks }}" class="space-y-3">
    @foreach (collect($errors->get($nama.'.*'))->flatten()->unique() as $pesan)
        <p class="error-text">{{ $pesan }}</p>
    @endforeach

    <div data-daftar class="space-y-3">
        @foreach ($baris as $b)
            @include('admin.konten._satu', ['nama' => $nama, 'b' => $b, 'maksIsi' => $maksIsi])
        @endforeach
    </div>

    <template data-templat>
        @include('admin.konten._satu', ['nama' => $nama, 'b' => ['judul' => '', 'isi' => ''], 'maksIsi' => $maksIsi])
    </template>

    <button type="button" data-tambah class="btn-secondary !py-2 text-sm">
        <span class="material-symbols-outlined text-[18px]">add</span> Tambah baris
    </button>
</div>