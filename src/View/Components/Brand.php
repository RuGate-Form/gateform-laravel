<?php

declare(strict_types=1);

namespace GateForm\Laravel\View\Components;

use GateForm\Laravel\GateFormManager;
use Illuminate\View\Component;

/**
 * Ссылка на GateForm с логотипом.
 *
 * <x-gateform::brand />
 */
final class Brand extends Component
{
    public function render(): string
    {
        return (string) app(GateFormManager::class)->brand();
    }
}
