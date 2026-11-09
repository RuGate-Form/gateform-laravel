<?php

declare(strict_types=1);

namespace GateForm\Laravel\View\Components;

use GateForm\Laravel\GateFormManager;
use Illuminate\View\Component;

/**
 * Callback-форма GateForm.
 *
 * <x-gateform::callback hash="cf_..." title="Заказать звонок" />
 */
final class Callback extends Component
{
    public function __construct(
        public string $hash,
        public ?string $title = null,
        public ?string $target = null
    ) {
    }

    public function render(): string
    {
        $options = [];
        if ($this->title !== null && $this->title !== '') {
            $options['title'] = $this->title;
        }
        if ($this->target !== null && $this->target !== '') {
            $options['target'] = $this->target;
        }

        return (string) app(GateFormManager::class)->callback($this->hash, $options);
    }
}
