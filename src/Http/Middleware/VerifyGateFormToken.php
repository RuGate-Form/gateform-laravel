<?php

declare(strict_types=1);

namespace GateForm\Laravel\Http\Middleware;

use Closure;
use GateForm\Laravel\GateFormManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Проверяет токен GateForm до обработчика запроса.
 *
 * Регистрация в маршруте:
 *
 *   Route::post('/contact', ContactController::class)
 *       ->middleware(\GateForm\Laravel\Http\Middleware\VerifyGateFormToken::class);
 */
final class VerifyGateFormToken
{
    public function __construct(private GateFormManager $manager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $methods = array_map(
            'strtoupper',
            (array) config('gateform.middleware.methods', ['POST', 'PUT', 'PATCH', 'DELETE'])
        );

        if (!in_array($request->getMethod(), $methods, true)) {
            return $next($request);
        }

        $field = (string) config('gateform.token_field', 'gf_token');
        $token = (string) $request->input($field, '');

        if ($token === '') {
            $header = (string) config('gateform.middleware.header', 'X-GateForm-Token');
            $token = (string) $request->header($header, '');
        }

        $result = $this->manager->validate($token);

        if (!$result->isValid()) {
            $message = $result->message();
            if ($message === '') {
                $message = (string) config(
                    'gateform.middleware.message',
                    'Не удалось подтвердить, что вы человек. Обновите страницу и попробуйте снова.'
                );
            }

            abort(400, $message);
        }

        $request->attributes->set('gateform', $result);

        return $next($request);
    }
}
