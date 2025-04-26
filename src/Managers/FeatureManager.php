<?php

namespace TailPress\Framework\Managers;

class FeatureManager
{
    private array $features = [];

    public function add(string $feature): self
    {
        if (! isset($this->features[$feature])) {
            $this->features[$feature] = new $feature;
        }

        return $this;
    }
}
