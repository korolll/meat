@component('mail::message')
Подана заявка на регистрацию номенклатуры {{ $assortment->name }}.
<br/>Для просмотра подробной информации перейдите по ссылке:
<pre>{{ url_frontend("admin/#/operation/assortments/{$assortment->uuid}") }}</pre>
@endcomponent
