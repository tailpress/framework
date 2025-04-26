<?php

namespace TailPress\Framework\Assets;

class Asset
{
    public function __construct(public string $path, public array $dependencies = [])
    {
        //
    }

    public function path()
    {
        return $this->path;
    }

    public function dependencies()
    {
        return $this->dependencies;
    }
}
