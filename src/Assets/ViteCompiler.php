<?php

namespace TailPress\Framework\Assets;

class ViteCompiler extends Compiler
{
    public function __construct(
        public ?string $serverUrl = null,
        public ?string $directory = null,
        public bool $ssl = false,
        public bool $sslVerify = true
    ) {
        $this->serverUrl = rtrim($serverUrl ?? 'http://localhost:3000', '/');
        $this->directory = $directory ? trailingslashit($directory) : get_template_directory();
    }

    public function ssl(bool $verify = true): self
    {
        $this->ssl = true;
        $this->sslVerify = $verify;

        if (str_starts_with($this->serverUrl, 'http://')) {
            $this->serverUrl = preg_replace('/^http:/', 'https:', $this->serverUrl);
        }

        return $this;
    }

    public function isDevServerRunning(): bool
    {
        $args = [];

        if($this->ssl) {
            $args['sslverify'] = $this->sslVerify;
        }

        $response = wp_remote_get($this->serverUrl.'/@vite/client', $args);

        return ! is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }

    public function getEditorStyleFile(): ?string
    {
        if ($this->isDevServerRunning()) {
            return $this->serverUrl.'/'.ltrim($this->editorStyleFile, '/');
        }

        $manifestPath = get_theme_file_path('dist/.vite/manifest.json');
        if (! file_exists($manifestPath)) {
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $file = $manifest[$this->editorStyleFile]['file'] ?? null;

        return $file ? get_theme_file_uri('dist/'.$file) : null;
    }

    public function enqueueEditorStyle(): void
    {
        if ($this->isDevServerRunning()) {
            add_action('admin_footer', function () {
                if (get_current_screen()->base !== 'post') {
                    return;
                }
                ?>
                <script type="module">
                    const { select, subscribe } = wp.data;

                    function whenEditorIsReady() {
                        return new Promise((resolve) => {
                            const unsubscribe = subscribe(() => {
                                if (select('core/editor').isCleanNewPost() || select('core/block-editor').getBlockCount() > 0) {
                                    unsubscribe()
                                    resolve()
                                }
                            })
                        })
                    }

                    function whenEditorIframeIsReady() {
                        const editorCanvasIframeElement = document.querySelector('[name="editor-canvas"]');

                        return new Promise((resolve) => {
                            if(!editorCanvasIframeElement.loading) {
                                resolve(editorCanvasIframeElement);
                            }

                            editorCanvasIframeElement.onload = () => {
                                resolve(editorCanvasIframeElement);
                            };
                        });
                    }

                    whenEditorIsReady().then(() => {
                        const editorIframe = document.querySelector('iframe[name="editor-canvas"]');
                        let docPromise;

                        if (editorIframe) {
                            console.info('[TailPress] Injecting Vite in editor iframe')
                            docPromise = whenEditorIframeIsReady().then(() => editorIframe.contentDocument || editorIframe.contentWindow.document);
                        } else {
                            console.info('[TailPress] Injecting Vite in main document')
                            docPromise = Promise.resolve(document);
                        }

                        docPromise.then((doc) => {
                            if (doc.querySelector('script[src$="/@vite/client"]')) {
                                return;
                            }

                            const viteScript = doc.createElement('script');
                            viteScript.type = 'module';
                            viteScript.src = '<?php echo esc_url($this->serverUrl.'/@vite/client'); ?>';
                            doc.head.appendChild(viteScript);

                            const style = doc.createElement('link');
                            style.rel = 'stylesheet';
                            style.href = '<?php echo esc_url($this->serverUrl.'/'.ltrim($this->editorStyleFile, '/')); ?>';
                            doc.head.appendChild(style);
                        });
                    });
                </script>
                <?php
            });

            return;
        }

        parent::enqueueEditorStyle();
    }

    public function enqueueAssets(): void
    {
        if ($this->isDevServerRunning()) {
            wp_enqueue_script('vite-client', $this->serverUrl.'/@vite/client', [], null, false);

            foreach ($this->assets as $asset) {
                $filename = pathinfo($asset->path(), PATHINFO_FILENAME);
                $url = $this->serverUrl.'/'.ltrim($asset->path(), '/');

                if (str_ends_with($asset->path(), '.js')) {
                    wp_enqueue_script("{$this->handle}-$filename", $url, array_merge($asset->dependencies(), ['vite-client']), null, false);
                } elseif (str_ends_with($asset->path(), '.css')) {
                    wp_enqueue_style("{$this->handle}-$filename", $url, $asset->dependencies(), null);
                }
            }

            add_filter('script_loader_tag', function ($tag, $handle) {
                if (in_array($handle, ['vite-client', $this->handle], true)) {
                    $tag = str_replace('<script ', '<script type="module" ', $tag);
                }

                return $tag;
            }, 10, 2);

            return;
        }

        $manifestPath = get_theme_file_path('dist/.vite/manifest.json');
        if (! file_exists($manifestPath)) {
            return;
        }

        $themeVersion = wp_get_theme()->get('Version');
        $manifest = json_decode(file_get_contents($manifestPath), true);

        foreach ($this->assets as $asset) {
            if (! isset($manifest[$asset->path()])) {
                continue;
            }

            $file = $manifest[$asset->path()]['file'];
            $filename = pathinfo($asset->path(), PATHINFO_FILENAME);
            $uri = get_theme_file_uri('dist/'.$file);

            if (str_ends_with($file, '.js')) {
                wp_enqueue_script("{$this->handle}-$filename", $uri, $asset->dependencies(), $themeVersion, true);
            } elseif (str_ends_with($file, '.css')) {
                wp_enqueue_style("{$this->handle}-$filename", $uri, $asset->dependencies(), $themeVersion);
            }
        }
    }
}
