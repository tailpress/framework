<?php

namespace TailPress\Framework;

use TailPress\Framework\Managers\AssetManager;
use TailPress\Framework\Managers\CommandManager;
use TailPress\Framework\Managers\FeatureManager;
use TailPress\Framework\Managers\MenuManager;
use TailPress\Framework\Managers\ThemeSupportManager;

class Theme
{
    private static $instance = null;

    private CommandManager $commandManager;

    private FeatureManager $featureManager;

    private MenuManager $menuManager;

    private ThemeSupportManager $themeSupportManager;

    private AssetManager $assetManager;

    private function __construct()
    {
        $this->commandManager = new CommandManager($this);
        $this->featureManager = new FeatureManager;
        $this->themeSupportManager = new ThemeSupportManager;
        $this->menuManager = new MenuManager;
        $this->assetManager = new AssetManager;

        $this->commandManager->discoverCommands();
        $this->themeSupportManager->register();
        $this->menuManager->register();
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function withTextdomain($textdomain, $path = null): self
    {
        add_action('after_setup_theme', fn () => load_theme_textdomain($textdomain, $path ?? get_template_directory().'/languages'));

        return $this;
    }

    public function assets(?callable $callback = null): AssetManager|self
    {
        if (! $callback) {
            return $this->assetManager;
        }

        $callback($this->assetManager);

        return $this;
    }

    public function commands(?callable $callback = null): CommandManager|self
    {
        if (! $callback) {
            return $this->commandManager;
        }

        $callback($this->commandManager);

        return $this;
    }

    public function features(?callable $callback = null): FeatureManager|self
    {
        if (! $callback) {
            return $this->featureManager;
        }

        $callback($this->featureManager);

        return $this;
    }

    public function menus(?callable $callback = null): MenuManager|self
    {
        if (! $callback) {
            return $this->menuManager;
        }

        $callback($this->menuManager);

        return $this;
    }

    public function themeSupport(?callable $callback = null): ThemeSupportManager|self
    {
        if (! $callback) {
            return $this->themeSupportManager;
        }

        $callback($this->themeSupportManager);

        return $this;
    }
}
