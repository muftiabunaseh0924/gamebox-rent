import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import intersect from '@alpinejs/intersect';

Alpine.plugin(focus);
Alpine.plugin(intersect);

// Angka menghitung naik dari 0 ke target saat terlihat di layar.
// Pemakaian: x-data="hitungNaik(1500000, 'rp')" x-intersect.once="mulai()" x-text="tampil"
Alpine.data('hitungNaik', (target, mode = 'angka', durasi = 900) => {
    const format = (n) => (mode === 'rp' ? 'Rp ' : '') + Number(n).toLocaleString('id-ID');

    return {
        tampil: format(0),

        mulai() {
            const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (kurangiGerak || !target) {
                this.tampil = format(target);
                return;
            }

            const awal = performance.now();
            const langkah = (sekarang) => {
                const p = Math.min((sekarang - awal) / durasi, 1);
                const halus = 1 - Math.pow(1 - p, 3); // melambat di akhir
                this.tampil = format(Math.round(target * halus));
                if (p < 1) {
                    requestAnimationFrame(langkah);
                }
            };
            requestAnimationFrame(langkah);
        },
    };
});

window.Alpine = Alpine;
Alpine.start();