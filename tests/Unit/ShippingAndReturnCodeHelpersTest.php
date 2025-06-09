<?php

use App\Helpers\ShippingAndReturnCodeHelpers;

test('function to generate toko ke cabang code', function () {
    $currentTime = now();
    $result = ShippingAndReturnCodeHelpers::generateTokoKeCabangCode($currentTime);

    expect($result)->toStartWith("TKC-");
});

test('function to generate cabang ke pusat code', function () {
    $currentTime = now();
    $result = ShippingAndReturnCodeHelpers::generateCabangKePusatCode($currentTime);

    expect($result)->toStartWith("CKP-");
});

test('function to generate cabang ke toko code', function () {
    $currentTime = now();
    $result = ShippingAndReturnCodeHelpers::generateCabangKeTokoCode($currentTime);

    expect($result)->toStartWith("CKT-");
});

test('function to generate pusat ke cabang code', function () {
    $currentTime = now();
    $result = ShippingAndReturnCodeHelpers::generatePusatKeCabangCode($currentTime);

    expect($result)->toStartWith("PKC-");
});

test('function to generate pusat ke supplier code', function () {
    $currentTime = now();
    $result = ShippingAndReturnCodeHelpers::generatePusatKeSupplierCode($currentTime);

    expect($result)->toStartWith("PKS-");
});

test('function to generate supplier ke pusat code', function () {
    $currentTime = now();
    $result = ShippingAndReturnCodeHelpers::generateSupplierKePusatCode($currentTime);

    expect($result)->toStartWith("SKP-");
});