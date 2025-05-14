<?php

namespace App\Http\Requests\Barang;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_barang' => [ 
                'required',
                'unique:barangs',
                'string',
                'max:255',
            ],
            'id_kategori_barang' => [
                'required',
                'exists:kategori_barangs,id',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_barang.required' => 'Nama barang harus diisi.',
            'nama_barang.unique' => 'Nama barang yang dimasukkan sudah terdaftar.',
            'nama_barang.max' => 'Nama barang maksimal 255 karakter.',
            
            'id_kategori.exists' => 'Kategori barang yang dipilih tidak ditemukan.',
        ];
    }
}
