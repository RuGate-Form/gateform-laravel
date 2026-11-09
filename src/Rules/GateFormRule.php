<?php

declare(strict_types=1);

namespace GateForm\Laravel\Rules;

use Closure;
use GateForm\Laravel\GateFormManager;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Правило валидации токена GateForm.
 *
 * Использование:
 *
 *   use GateForm\Laravel\Rules\GateFormRule;
 *
 *   $request->validate([
 *       'name' => ['required', 'string'],
 *       'gf_token' => ['required', new GateFormRule()],
 *   ]);
 */
final class GateFormRule implements ValidationRule
{
    public function __construct(
        private ?GateFormManager $manager = null,
        private ?string $message = null
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $manager = $this->manager ?? app(GateFormManager::class);
        $result = $manager->validate(is_string($value) ? $value : '');

        if ($result->isValid()) {
            return;
        }

        $message = trim((string) ($this->message ?? ''));
        if ($message === '') {
            $message = $result->message();
        }
        if ($message === '') {
            $message = 'Не удалось подтвердить, что вы человек. Обновите страницу и попробуйте снова.';
        }

        $fail($message);
    }
}
