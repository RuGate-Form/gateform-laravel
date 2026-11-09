<?php

declare(strict_types=1);

namespace GateForm\Laravel;

use GateForm\Client;
use GateForm\ValidationResult;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\HtmlString;

/**
 * Сборка HTML для публичных виджетов GateForm и проверка токенов.
 */
final class GateFormManager
{
    public function __construct(
        private Config $config,
        private ?Client $client = null
    ) {
    }

    public function client(): Client
    {
        if ($this->client === null) {
            $this->client = Client::create([
                'site_key' => (string) $this->config->get('gateform.site_key', ''),
                'secret_key' => (string) $this->config->get('gateform.secret_key', ''),
                'base_url' => (string) $this->config->get('gateform.base_url', 'https://gateform.ru'),
                'timeout' => (float) $this->config->get('gateform.timeout', 5.0),
                'connect_timeout' => (float) $this->config->get('gateform.connect_timeout', 3.0),
                'fail_open' => (bool) $this->config->get('gateform.fail_open', false),
                'token_field' => (string) $this->config->get('gateform.token_field', 'gf_token'),
            ]);
        }

        return $this->client;
    }

    public function validate(string $token): ValidationResult
    {
        return $this->client()->validate($token);
    }

    /**
     * Подключает client.js к формам на странице.
     *
     * @param array{form?: string, token_name?: string, timeout?: int|null, block_message?: string|null, brand?: string|null} $options
     */
    public function script(array $options = []): HtmlString
    {
        $config = (array) $this->config->get('gateform.script', []);
        $options = array_merge($config, $options);

        $attributes = [
            'src' => $this->baseUrl() . '/client.js',
            'data-site-key' => (string) $this->config->get('gateform.site_key', ''),
            'data-form' => (string) ($options['form'] ?? 'form[data-gateform]'),
            'data-token-name' => (string) ($options['token_name'] ?? 'gf_token'),
            'data-brand' => (string) ($options['brand'] ?? 'GateForm'),
            'defer' => true,
        ];

        if (isset($options['timeout']) && $options['timeout'] !== null) {
            $attributes['data-timeout'] = (string) (int) $options['timeout'];
        }

        if (isset($options['block_message']) && $options['block_message'] !== null) {
            $attributes['data-block-message'] = (string) $options['block_message'];
        }

        return new HtmlString($this->tag('script', $attributes));
    }

    /**
     * Callback-форма GateForm.
     *
     * @param array{title?: string, target?: string} $options
     */
    public function callback(string $hash, array $options = []): HtmlString
    {
        $hash = $this->validHash($hash, 'cf_');
        if ($hash === '') {
            return new HtmlString('');
        }

        $attributes = [
            'src' => $this->baseUrl() . '/callback-embed.js',
            'data-callback-form' => $hash,
            'defer' => true,
        ];

        $target = trim((string) ($options['target'] ?? ''));
        if (preg_match('/^#[A-Za-z][A-Za-z0-9_-]{0,63}$/', $target) === 1) {
            $attributes['data-target'] = $target;
        }

        $html = $this->tag('script', $attributes);
        $title = trim((string) ($options['title'] ?? ''));
        if ($title !== '') {
            $html = '<h3 class="gateform-callback-title">' . $this->escape($title) . '</h3>' . $html;
        }

        return new HtmlString('<div class="gateform-callback">' . $html . '</div>');
    }

    /**
     * Отзывы товара.
     *
     * @param array{product_key?: string, product_name?: string, product_url?: string, source?: string, target?: string} $options
     */
    public function reviews(string $hash, array $options = []): HtmlString
    {
        $hash = $this->validHash($hash, 'rv_');
        if ($hash === '') {
            return new HtmlString('');
        }

        $attributes = [
            'src' => $this->baseUrl() . '/review-embed.js',
            'data-gateform-reviews' => $hash,
            'data-source' => (string) ($options['source'] ?? 'laravel'),
            'defer' => true,
        ];

        foreach ([
            'data-product-key' => (string) ($options['product_key'] ?? ''),
            'data-product-name' => (string) ($options['product_name'] ?? ''),
            'data-product-url' => (string) ($options['product_url'] ?? ''),
            'data-target' => (string) ($options['target'] ?? ''),
        ] as $name => $value) {
            if ($value !== '') {
                $attributes[$name] = $value;
            }
        }

        return new HtmlString(
            '<div class="gateform-reviews">' . $this->tag('script', $attributes) . '</div>'
        );
    }

    /**
     * Виджет связи.
     */
    public function contact(string $id): HtmlString
    {
        $id = trim($id);
        if (preg_match('/^[A-Za-z0-9_-]{3,64}$/', $id) !== 1) {
            return new HtmlString('');
        }

        return new HtmlString($this->tag('script', [
            'src' => $this->baseUrl() . '/widget.js',
            'data-widget-id' => $id,
            'defer' => true,
        ]));
    }

    /**
     * Ссылка на сайт с логотипом GateForm.
     */
    public function brand(): HtmlString
    {
        $baseUrl = $this->baseUrl();

        return new HtmlString(
            '<a class="gateform-brand" href="' . $this->escape($baseUrl . '/') . '"'
            . ' target="_blank" rel="noopener noreferrer"'
            . ' style="display:inline-flex;align-items:center;gap:6px;font:13px/1.4 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#64748b;text-decoration:none">'
            . '<img src="' . $this->escape($baseUrl . '/logo-mark.svg') . '" alt="" width="16" height="16" style="border-radius:4px">'
            . '<span>GateForm</span>'
            . '</a>'
        );
    }

    private function baseUrl(): string
    {
        $url = rtrim((string) $this->config->get('gateform.base_url', 'https://gateform.ru'), '/');

        return preg_match('#^https?://#i', $url) === 1 ? $url : 'https://gateform.ru';
    }

    private function validHash(string $hash, string $prefix): string
    {
        $hash = strtolower(trim($hash));

        return preg_match('/^' . preg_quote($prefix, '/') . '[a-f0-9]{32}$/', $hash) === 1
            ? $hash
            : '';
    }

    /**
     * @param array<string, string|bool> $attributes
     */
    private function tag(string $name, array $attributes): string
    {
        $parts = [];
        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $parts[] = $key;
                continue;
            }
            if ($value === false || $value === '') {
                continue;
            }
            $parts[] = $key . '="' . $this->escape((string) $value) . '"';
        }

        return '<' . $name . ($parts !== [] ? ' ' . implode(' ', $parts) : '') . '></' . $name . '>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
