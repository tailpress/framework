<?php

namespace TailPress\Framework\Managers;

class ThemeSupportManager
{
    private array $themeSupport = [];

    public function add(string|array $support, array $args = []): self
    {
        if (is_array($support)) {
            foreach ($support as $key => $value) {
                $this->themeSupport[] = [
                    'feature' => is_int($key) ? $value : $key,
                    'args' => is_array($value) ? $value : [],
                ];
            }
        } else {
            $this->themeSupport[] = [
                'feature' => $support,
                'args' => $args,
            ];
        }

        return $this;
    }

    public function register(): void
    {
        add_action('after_setup_theme', function () {
            foreach ($this->themeSupport as $support) {
                if ($support['args']) {
                    add_theme_support($support['feature'], $support['args']);
                } else {
                    add_theme_support($support['feature']);
                }
            }
        });
    }
}
