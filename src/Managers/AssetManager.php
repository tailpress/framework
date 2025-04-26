<?php

namespace TailPress\Framework\Managers;

use TailPress\Framework\Assets\Compiler;

class AssetManager
{
    private ?Compiler $compiler = null;

    public function withCompiler(Compiler $compiler, ?callable $callback = null): self
    {
        $this->compiler = $compiler;

        if ($callback) {
            $callback($this->compiler);
        }

        return $this;
    }

    public function enqueueAssets(): self
    {
        if ($this->compiler) {
            add_action('wp_enqueue_scripts', fn () => $this->compiler->enqueueAssets());
        }

        if ($this->compiler->editorStyleFile) {
            add_action('after_setup_theme', fn () => $this->compiler->enqueueEditorStyle());
        }

        return $this;
    }
}
