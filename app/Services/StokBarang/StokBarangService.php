<?php

namespace App\Services\StokBarang;

use App\Repositories\StokBarang\StokBarangRepository;
use Illuminate\Support\Collection;

class StokBarangService
{
    protected $stokBarangRepository;

    public function __construct(StokBarangRepository $stokBarangRepository)
    {
        $this->stokBarangRepository = $stokBarangRepository;
    }

    public function getTopTenLowestStockSuper(): Collection
    {
        return $this->stokBarangRepository->getTopTenLowestStock(true);
    }

    public function getTopTenLowestStockCabang(int $idCabang): Collection
    {
        return $this->stokBarangRepository->getTopTenLowestStock(false, $idCabang);
    }
}