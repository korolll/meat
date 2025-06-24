@component('mail::message')
    @foreach ($messages as $i => $message)
        <strong>{{$i+1}}) Поставщик: {{$message['groupOrganizationName']}}</strong><br/><br/>
        @foreach ($message['productPreRequests'] as $productPreRequest)
            <i>ID презаявки: "{{$productPreRequest['productPreRequestId']}}"</i><br/>
            Ошибка при автоматическом заказе "{{$productPreRequest['assortmentName']}}" поставщику "{{$productPreRequest['supplierName']}}" по заявке "{{$productPreRequest['productRequestUuid']}}" для "{{$productPreRequest['customerName']}}" от "{{$productPreRequest['dateCustomerRequest']}}"<br/><br/>
        @endforeach
        <br/>
    @endforeach
@endcomponent
