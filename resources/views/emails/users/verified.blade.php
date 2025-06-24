@component('mail::message')
@if ($success_verified)
Здравствуйте, {{ $email }}

Ваша учетная запись подтверждена администрацией сервиса. Добро пожаловать!
@else
Здравствуйте, ваша заявка отклонена

Причина отклонения: {{ $comment }}

С уважением, команда {{ config('app.name') }}.
@endif
@endcomponent
