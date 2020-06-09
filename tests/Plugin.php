<?php

it('has installation')
    ->browse('/')
    ->clickLink('Get started')
    ->assertSee('composer require pestphp/pest');
