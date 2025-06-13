<?php

test('/ endpoint returns a 404 not found response', function () {
    $response = $this->get('/');
    
    $response->assertStatus(404);
});
