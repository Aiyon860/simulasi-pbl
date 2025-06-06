<?php

use App\Helpers\TimeHelpers;

it('should receive hours from 00.00 until now', function () {
    $result = TimeHelpers::getHoursUntilNow();
    
    expect($result)->toBeArray()
        ->each()
        ->toBeString();
});

it('should receive dates from yesterday until 7 days backwards', function () {
    $result = TimeHelpers::getLastSevenDays();
    
    expect($result)->toBeObject()
        ->toHaveLength(7)
        ->each()
        ->toBeString();
});

it('should receive 4 dates from yesterday until 1 month backwards', function () {
    $result = TimeHelpers::getFourDatesFromLastMonth();
    
    expect($result)->toBeObject()
        ->toHaveLength(4)
        ->each()
        ->toBeString();
});

test('get the indonesian month name in short function', function () {
    // invalid input params
    for ($i = 0; $i > 0 - 5; --$i) {
        $result = TimeHelpers::getIndonesianMonthShort($i);
        
        expect($result)->toEqual('');
    }
    
    // valid input params
    for ($i = 1; $i <= 12; ++$i) {
        $result = TimeHelpers::getIndonesianMonthShort($i);
        
        expect($result)->toBeString();
    }
    
    // invalid input params
    for ($i = 13; $i > 13 + 5; ++$i) {
        $result = TimeHelpers::getIndonesianMonthShort($i);
        
        expect($result)->toEqual('');
    }
});

it('should receive a short form of today\'s date', function () {
    $result = TimeHelpers::hariInterval(now());
    
    expect($result)->toBeString();
});

it('should receive 4 week intervals', function () {
    $result = TimeHelpers::getMingguanIntervals(4);
    
    expect($result)->toBeArray();
    
    foreach ($result as $r) {
        expect($r)->toHaveKeys(['label', 'start', 'end']);

        expect($r['label'])->toBeString();
        expect($r['start'])->toBeObject();
        expect($r['end'])->toBeObject();
    }
});

it('should receive hour intervals from 00.00 until now', function () {
    $result = TimeHelpers::getHourlyIntervals();
    
    expect($result)->toBeArray();

    foreach ($result as $r) {
        expect($r)->toHaveKeys(['label', 'start', 'end']);

        expect($r['label'])->toBeString();
        expect($r['start'])->toBeObject();
        expect($r['end'])->toBeObject();
    }
});

it('should receive daily intervals from yesterday until 7 days backwards', function () {
    $result = TimeHelpers::getDailyIntervals();
    
    expect($result)->toBeArray();

    foreach ($result as $r) {
        expect($r)->toHaveKeys(['label', 'start', 'end']);

        expect($r['label'])->toBeString();
        expect($r['start'])->toBeObject();
        expect($r['end'])->toBeObject();
    }
});