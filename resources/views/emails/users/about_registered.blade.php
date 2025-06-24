@component('mail::message')
Подана заявка на регистрацию {{ $typeName }} {{ $user->organization_name }}.
<br/>Для просмотра подробной информации перейдите по ссылке:
<pre>{{ url_frontend("admin/#/operation/users/{$user->uuid}") }}</pre>
@endcomponent
