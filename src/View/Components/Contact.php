<?php

declare(strict_types=1);

namespace GateForm\Laravel\View\Components;

use GateForm\Laravel\GateFormManager;
use Illuminate\View\Component;

/**
 * Виджет связи GateForm.
 *
 * <x-gateform::contact id="w_xxxxxxxx" />
 */
final class Contact extends Component
{
    public function __construct(public string $id)
    {
    }

    public function render(): string
    {
        return (string) app(GateFormManager::class)->contact($this->id);
    }
}
