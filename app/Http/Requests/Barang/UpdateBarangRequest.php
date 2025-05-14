<?php

namespace App\Http\Requests\Barang;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBarangRequest extends FormRequest
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
                'string',
                'max:255',
                Rule::unique('barangs')->ignore($this->barang),
            ],
            'id_kategori' => [
                'nullable',
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
            'nama_barang.required' => 'Nama gudang harus diisi.',
            'nama_barang.max' => 'Nama gudang maksimal 255 karakter.',
            
            'berat.required' => 'Berat barang harus diisi.',
            'berat.min' => 'Berat barang minimal 1',
        ];
    }
}
