<?php

use App\Helpers\CodeHelpers;

test('function to generate toko ke cabang code', function () {
    $currentTime = now();
    $result = CodeHelpers::generateTokoKeCabangCode($currentTime);

    expect($result)->toStartWith("TKC-");
});

test('function to generate cabang ke pusat code', function () {
    $currentTime = now();
    $result = CodeHelpers::generateCabangKePusatCode($currentTime);

    expect($result)->toStartWith("CKP-");
});

test('function to generate cabang ke toko code', function () {
    $currentTime = now();
    $result = CodeHelpers::generateCabangKeTokoCode($currentTime);

    expect($result)->toStartWith("CKT-");
});

test('function to generate pusat ke cabang code', function () {
    $currentTime = now();
    $result = CodeHelpers::generatePusatKeCabangCode($currentTime);

    expect($result)->toStartWith("PKC-");
});

test('function to generate pusat ke supplier code', function () {
    $currentTime = now();
    $result = CodeHelpers::generatePusatKeSupplierCode($currentTime);

    expect($result)->toStartWith("PKS-");
});

test('function to generate supplier ke pusat code', function () {
    $currentTime = now();
    $result = CodeHelpers::generateSupplierKePusatCode($currentTime);

    expect($result)->toStartWith("SKP-");
});