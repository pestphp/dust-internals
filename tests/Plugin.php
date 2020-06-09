<?php

use function Dust\browse;

it('has installation')
    ->browse('/')
    ->clickLink('Get started')
    ->assertSee('composer require pestphp/pest');

// browse('/')->assertSee('Get started');
