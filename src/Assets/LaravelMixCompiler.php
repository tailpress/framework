<?php

namespace TailPress\Framework\Assets;

class LaravelMixCompiler extends Compiler
{
    public function getEditorStyleFile(): ?string
    {
        return str_replace('resources', 'dist', $this->editorStyleFile);
    }

    public function enqueueAssets(): void
    {
        $themeVersion = wp_get_theme()->get('Version');

        $manifestPath = get_theme_file_path('mix-manifest.json');

        if (! file_exists($manifestPath)) {
            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        foreach ($this->assets as $asset) {
            $assetInDist = str_replace('resources', '/dist', $asset->path());

            $filename = pathinfo($asset->path(), PATHINFO_FILENAME);

            $uri = get_theme_file_uri($manifest[$assetInDist]);

            if (str_ends_with($asset->path(), '.js')) {
                wp_enqueue_script("{$this->handle}-$filename", $uri, $asset->dependencies(), $themeVersion, false);
            } elseif (str_ends_with($asset->path(), '.css')) {
                wp_enqueue_style("{$this->handle}-$filename", $uri, $asset->dependencies(), $themeVersion);
            }
        }
    }
}
