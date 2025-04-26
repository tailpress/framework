<?php

namespace TailPress\Framework;

abstract class ServiceProvider
{
    public function __construct(public $app)
    {
        //
    }

    public function register() {}

    public function boot() {}
}
