<div data-baris class="grid gap-3 rounded-lg bg-surface-container-lowest p-3 sm:grid-cols-[1fr_2fr_auto]">
    <input type="text" name="{{ $nama }}[judul][]" value="{{ $b['judul'] }}" maxlength="40" placeholder="Judul" class="input-field" aria-label="Judul">
    <input type="text" name="{{ $nama }}[isi][]" value="{{ $b['isi'] }}" maxlength="{{ $maksIsi }}" placeholder="Isi" class="input-field" aria-label="Isi">
    <button type="button" data-hapus class="btn-secondary !px-3" aria-label="Hapus baris">
        <span class="material-symbols-outlined text-[18px]">delete</span>
    </button>
</div>