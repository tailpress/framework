<?php

namespace TailPress\Framework\Assets;

abstract class Compiler
{
    public string $handle = 'tailpress';

    public array $assets = [];

    public array $dependencies = [];

    public bool $inFooter = false;

    public ?string $editorStyleFile = null;

    public function editorStyleFile($file): self
    {
        $this->editorStyleFile = $file;

        return $this;
    }

    public function enqueueEditorStyle()
    {
        add_theme_support('editor-styles');

        add_editor_style($this->getEditorStyleFile());
    }

    public function getEditorStyleFile(): ?string
    {
        return $this->editorStyleFile;
    }

    public function registerAsset($asset, $dependencies = []): self
    {
        $this->assets[$asset] = new Asset(path: $asset, dependencies: $dependencies);

        return $this;
    }

    public function enqueueAssets(): void {}
}
