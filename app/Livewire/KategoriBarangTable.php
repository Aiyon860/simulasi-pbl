<?php

namespace App\Livewire;

use App\Models\KategoriBarang;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\ButtonGroupColumn;

class KategoriBarangTable extends DataTableComponent
{
    protected $model = KategoriBarang::class;

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
            Column::make("ID", "id")
                ->sortable(),

            Column::make("Nama Kategori", "nama_kategori_barang")
                ->sortable(),

            Column::make("Status", "flag")
                ->format(fn($value, $row, Column $column) => view('components.badge-status')->with("flag", $row->flag)),

            Column::make("Action")
                ->label(fn($row, Column $column) => view('components.table-actions')->with([
                    'row' => $row,
                    'rute_lihat' => route('kategori-barangs.show', $row->id),
                    'rute_edit' => route('kategori-barangs.edit', $row->id),
                    'rute_deactivate' => route('kategori-barangs.deactivate', $row->id),
                    'rute_activate' => route('kategori-barangs.activate', $row->id),
                ])),
            
            // ButtonGroupColumn::make('Actions')
            //     ->attributes(function($row) {
            //         return [
            //             'class' => 'space-x-2',
            //         ];
            //     })
            //     ->buttons([
            //         LinkColumn::make('View')
            //             ->title(fn($row) => "View 2")
            //             ->location(fn($row) => route('kategori-barangs.show', $row))
            //             ->attributes(function($row) {
            //                 return [
            //                     'class' => 'underline text-blue-500 hover:no-underline',
            //                 ];
            //             }),
            //         LinkColumn::make('Edit')
            //             ->title(fn($row) => "Edit 2")
            //             ->location(fn($row) => route('kategori-barangs.edit', $row))
            //             ->attributes(function($row) {
            //                 return [
            //                     'class' => 'underline text-blue-500 hover:no-underline',
            //                 ];
            //             }),
            //             Column::make('My one off column')
            //             ->label(
            //                 fn($row, Column $column)  => '<strong>'.'aaa'.'</strong>'
            //             )
            //             ->html(),
            //     ])
        ];
    }
}
