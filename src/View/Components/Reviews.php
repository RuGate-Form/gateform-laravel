<?php

declare(strict_types=1);

namespace GateForm\Laravel\View\Components;

use GateForm\Laravel\GateFormManager;
use Illuminate\View\Component;

/**
 * Отзывы товара GateForm.
 *
 * <x-gateform::reviews hash="rv_..." product-key="SKU-123" product-name="Товар" />
 */
final class Reviews extends Component
{
    public function __construct(
        public string $hash,
        public ?string $productKey = null,
        public ?string $productName = null,
        public ?string $productUrl = null,
        public ?string $source = null,
        public ?string $target = null
    ) {
    }

    public function render(): string
    {
        $options = [];
        foreach ([
            'product_key' => $this->productKey,
            'product_name' => $this->productName,
            'product_url' => $this->productUrl,
            'source' => $this->source,
            'target' => $this->target,
        ] as $key => $value) {
            if ($value !== null && $value !== '') {
                $options[$key] = $value;
            }
        }

        return (string) app(GateFormManager::class)->reviews($this->hash, $options);
    }
}
