# GateForm для Laravel

[GateForm](https://gateform.ru) — капча и защита форм от спама и ботов.
Пакет добавляет в Laravel:

- правило валидации `gateform`;
- middleware `VerifyGateFormToken`;
- Blade-компоненты формы, отзывов, виджета связи и брендинга;
- `GateFormManager` для сборки HTML из кода.

- Сайт: <https://gateform.ru>
- Документация: <https://gateform.ru/docs>
- Поддержка: <info@gateform.ru>

## Требования

- PHP **8.1+**
- Laravel **10, 11 или 12**
- Пакет `gateform/sdk`

## Установка

```bash
composer require gateform/laravel:^1.0
```

Laravel автоматически зарегистрирует `GateForm\Laravel\GateFormServiceProvider`.

Опубликуйте конфигурацию:

```bash
php artisan vendor:publish --tag=gateform-config
```

## Настройка

`.env`:

```env
GATEFORM_SITE_KEY=gf_site_...
GATEFORM_SECRET_KEY=gf_sec_...
GATEFORM_BASE_URL=https://gateform.ru
GATEFORM_FAIL_OPEN=false
```

`config/gateform.php`:

```php
return [
    'site_key' => env('GATEFORM_SITE_KEY', ''),
    'secret_key' => env('GATEFORM_SECRET_KEY', ''),
    'base_url' => env('GATEFORM_BASE_URL', 'https://gateform.ru'),
    'fail_open' => env('GATEFORM_FAIL_OPEN', false),
    'token_field' => 'gf_token',
    'script' => [
        'form' => 'form[data-gateform]',
    ],
    'middleware' => [
        'methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],
        'header' => 'X-GateForm-Token',
    ],
];
```

## Форма и client.js

```blade
<form action="/contact" method="post" data-gateform>
    @csrf
    <input type="email" name="email" required>
    <textarea name="message" required></textarea>
    <button type="submit">Отправить</button>
</form>

<x-gateform::script form="form[data-gateform]" />
```

`client.js` соберёт поведенческие сигналы, получит одноразовый токен и добавит
в форму скрытое поле `gf_token`.

## Правило валидации

```php
use GateForm\Laravel\Rules\GateFormRule;

$request->validate([
    'name' => ['required', 'string', 'max:120'],
    'email' => ['required', 'email'],
    'gf_token' => ['required', 'string', new GateFormRule()],
]);
```

Можно использовать строковое правило `gateform`:

```php
$request->validate([
    'gf_token' => ['required', 'gateform'],
]);
```

По умолчанию пустой или неверный токен отклоняется. При включённом
`GATEFORM_FAIL_OPEN=true` форма принимается, если сервис проверки недоступен.

## Middleware

```php
use GateForm\Laravel\Http\Middleware\VerifyGateFormToken;

Route::post('/contact', ContactController::class)
    ->middleware(VerifyGateFormToken::class);
```

Middleware проверяет только методы из `gateform.middleware.methods`. При отказе
возвращает `400` с сообщением. Результат проверки доступен в атрибуте запроса:

```php
$result = $request->attributes->get('gateform');
```

## Blade-компоненты

### Callback-форма

```blade
<x-gateform::callback hash="cf_0123456789abcdef0123456789abcdef" title="Заказать звонок" />
```

### Отзывы товара

```blade
<x-gateform::reviews
    hash="rv_0123456789abcdef0123456789abcdef"
    product-key="SKU-123"
    product-name="Название товара"
    product-url="https://example.ru/product/123"
/>
```

### Виджет связи

```blade
<x-gateform::contact id="w_xxxxxxxx" />
```

### Брендинг

```blade
<x-gateform::brand />
```

Компонент выводит логотип GateForm и ссылку на <https://gateform.ru>.

## Из кода

```php
use GateForm\Laravel\GateFormManager;

final class ContactController
{
    public function __invoke(GateFormManager $gateForm)
    {
        $result = $gateForm->validate((string) request('gf_token'));

        if (!$result->isValid()) {
            return back()->withErrors(['gf_token' => $result->message()]);
        }

        // ...
    }
}
```

Сборка HTML:

```php
echo $gateForm->script(['form' => '#contact-form']);
echo $gateForm->callback('cf_...', ['title' => 'Заказать звонок']);
echo $gateForm->reviews('rv_...', ['product_key' => 'SKU-123']);
echo $gateForm->contact('w_xxxxxxxx');
echo $gateForm->brand();
```

## Fail-open

По умолчанию пакет работает **fail-closed**: при недоступности сервиса форма
отклоняется. Чтобы не терять заявки:

```env
GATEFORM_FAIL_OPEN=true
```

Неверный секретный ключ (`INVALID_CREDENTIALS`) никогда не приводит к fail-open.

## Лицензия

MIT. См. [LICENSE](LICENSE).

Разработчик: **GateForm** — <https://gateform.ru>
