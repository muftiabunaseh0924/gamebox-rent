<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dijaga middleware auth + role:user
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'jumlah_stik' => $this->input('jumlah_stik') === null || $this->input('jumlah_stik') === ''
                ? 0
                : $this->input('jumlah_stik'),
        ]);
    }

    public function rules(): array
    {
        return [
            'console_id' => ['required', 'integer', 'exists:consoles,id'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam_mulai' => ['required', 'integer', 'between:0,23'],
            'durasi_jam' => ['required', 'integer', 'min:1', 'max:48'],
            'jumlah_stik' => ['required', 'integer', 'min:0', 'max:20'],
            'metode_serah' => ['required', 'in:Ambil Sendiri,Diantar'],
            'alamat_pengiriman' => ['nullable', 'required_if:metode_serah,Diantar', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi bila memilih Diantar.',
            'integer' => ':attribute tidak valid.',
            'date_format' => 'Format :attribute tidak valid.',
            'exists' => ':attribute tidak ditemukan.',
            'in' => ':attribute tidak valid.',
            'min' => ':attribute terlalu pendek atau kecil.',
            'max' => ':attribute terlalu panjang atau besar.',
            'between' => ':attribute tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'console_id' => 'Unit',
            'tanggal' => 'Tanggal',
            'jam_mulai' => 'Jam mulai',
            'durasi_jam' => 'Durasi',
            'jumlah_stik' => 'Jumlah stik tambahan',
            'metode_serah' => 'Cara penerimaan',
            'alamat_pengiriman' => 'Alamat pengiriman',
        ];
    }
}