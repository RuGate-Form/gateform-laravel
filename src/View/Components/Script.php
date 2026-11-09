<?php

declare(strict_types=1);

namespace GateForm\Laravel\View\Components;

use GateForm\Laravel\GateFormManager;
use Illuminate\View\Component;

/**
 * Подключение client.js к формам.
 *
 * <x-gateform::script form="#contact-form" />
 */
final class Script extends Component
{
    public function __construct(
        public ?string $form = null,
        public ?string $tokenName = null,
        public ?int $timeout = null,
        public ?string $blockMessage = null
    ) {
    }

    public function render(): string
    {
        $options = [];
        if ($this->form !== null && $this->form !== '') {
            $options['form'] = $this->form;
        }
        if ($this->tokenName !== null && $this->tokenName !== '') {
            $options['token_name'] = $this->tokenName;
        }
        if ($this->timeout !== null) {
            $options['timeout'] = $this->timeout;
        }
        if ($this->blockMessage !== null && $this->blockMessage !== '') {
            $options['block_message'] = $this->blockMessage;
        }

        return (string) app(GateFormManager::class)->script($options);
    }
}
