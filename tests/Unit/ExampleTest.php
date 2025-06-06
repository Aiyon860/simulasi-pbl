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
