<?php

namespace TailPress\Framework;

use TailPress\Framework\Theme;

abstract class ServiceProvider
{
    public function __construct(public Theme $app)
    {
        //
    }

    public function register() {}

    public function boot() {}
}
