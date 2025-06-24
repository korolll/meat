@component('mail::message')
Здравствуйте, {{ $email }}

Чтобы продолжить регистрацию, нажмите кнопку ниже для подтверждения вашего адреса электронной почты:

@component('mail::button', ['url' => url_frontend('confirm', $token)])
Подтвердить почту
@endcomponent

Если кнопка выше не работает, скопируйте в ваш браузер следующий адрес:
<pre>{{ url_frontend('confirm', $token) }}</pre>
@endcomponent
