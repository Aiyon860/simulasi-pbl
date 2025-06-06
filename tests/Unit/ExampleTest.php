<?php

test('that true is true', function () {
    expect(true)->toBeTrue();
});

test('that false is false', function () {
    expect(false)->toBeFalse();
});

test('that 1 + 1 is 2', function () {
    expect(1 + 1)->toBe(2);
});

test('that 2 + 2 is 4', function () {
    expect(2 + 2)->toBe(4);
});

test('that 2 + 1 is 3', function () {
    expect(2 + 1)->toBe(3);
});
