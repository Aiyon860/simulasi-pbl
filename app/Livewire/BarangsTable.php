<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Barang;

class BarangsTable extends DataTableComponent
{
    protected $model = Barang::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id');

        // Tabel
        $this->setTableAttributes([
            'class' => 'w-full text-sm text-left rtl:text-right text-[#111827] bg-white',
        ]);

        // Header kolom
        $this->setTheadAttributes([
            'class' => 'text-xs uppercase bg-[#fcfcfc] text-[#687588] text-center',
        ]);        
    }


    public function columns(): array
    {
        return [
            Column::make("Id", "id")
                ->sortable(),
            Column::make("Nama barang", "nama_barang")
                ->sortable(),
            Column::make("Id kategori barang", "id_kategori_barang")
                ->sortable(),
            Column::make("Status", "flag")
                ->label(fn($row) => view('components.badge-status', ['flag' => $row->flag])),

            Column::make("Action")
                ->label(fn($row) => view('components.table-actions', ['row' => $row])),
        ];
    }

    public function builder(): Builder
    {
        return Barang::query()
            ->where('flag', 1)
            ->get(); // Hanya ambil data barang yang aktif
    }
}
