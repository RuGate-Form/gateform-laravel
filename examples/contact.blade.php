```blade
{{-- resources/views/contact.blade.php --}}
<form action="{{ route('contact.send') }}" method="post" data-gateform>
    @csrf
    <input type="text" name="name" required>
    <input type="email" name="email" required>
    <textarea name="message" required></textarea>
    <button type="submit">Отправить</button>
</form>

<x-gateform::script form="form[data-gateform]" />
<x-gateform::brand />
```
