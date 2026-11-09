<?php

declare(strict_types=1);

namespace GateForm\Laravel;

use GateForm\Laravel\View\Components\Brand;
use GateForm\Laravel\View\Components\Callback;
use GateForm\Laravel\View\Components\Contact;
use GateForm\Laravel\View\Components\Reviews;
use GateForm\Laravel\View\Components\Script;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

final class GateFormServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/gateform.php', 'gateform');

        $this->app->singleton(GateFormManager::class, static function ($app): GateFormManager {
            return new GateFormManager($app['config']);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/gateform.php' => config_path('gateform.php'),
        ], 'gateform-config');

        $this->loadViewComponentsAs('gateform', [
            Script::class,
            Callback::class,
            Reviews::class,
            Contact::class,
            Brand::class,
        ]);

        Validator::extend('gateform', static function (
            string $attribute,
            mixed $value,
            array $parameters,
            $validator
        ): bool {
            $result = app(GateFormManager::class)->validate(is_string($value) ? $value : '');

            if ($result->isValid()) {
                return true;
            }

            $message = $result->message();
            if ($message === '') {
                $message = 'Не удалось подтвердить, что вы человек. Обновите страницу и попробуйте снова.';
            }

            $validator->errors()->add($attribute, $message);

            return false;
        });
    }
}
