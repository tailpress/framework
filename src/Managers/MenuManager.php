<?php

namespace TailPress\Framework\Managers;

class MenuManager
{
    private array $menus = [];

    public function add(string $location, string $name): self
    {
        $this->menus[$location] = $name;

        return $this;
    }

    public function register(): void
    {
        add_action('after_setup_theme', fn () => register_nav_menus($this->menus));
    }
}
