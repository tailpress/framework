<?php

namespace TailPress\Framework\Managers;

use TailPress\Framework\Theme;

class CommandManager
{
    private array $commands = [];

    public function __construct(public Theme $app) {}

    public function add($command): self
    {
        $this->commands[] = $command;

        return $this;
    }

    public function all(): array
    {
        return array_map(fn ($command) => new $command, $this->commands);
    }

    public function discoverCommands(): void
    {
        $installedJson = get_template_directory().'/vendor/composer/installed.json';

        if (! file_exists($installedJson)) {
            return;
        }

        $packages = json_decode(file_get_contents($installedJson), true);
        $packages = isset($packages['packages']) ? $packages['packages'] : $packages;

        foreach ($packages as $package) {
            if (isset($package['extra']['tailpress']['providers'])) {
                foreach ($package['extra']['tailpress']['providers'] as $provider) {
                    $provider = new $provider($this->app);

                    if (method_exists($provider, 'boot')) {
                        $provider->boot();
                    }

                    if (method_exists($provider, 'register')) {
                        $provider->register();
                    }
                }
            }
        }
    }
}
