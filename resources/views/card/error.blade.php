<h1 style="text-align: center;"><sup>Не&nbsp;удалось&nbsp;провести&nbsp;платеж.</sup></h1>
<h1><sup><code><img src="/error.png" alt="Успех" width="515" height="515" style="display: block; margin-left: auto; margin-right: auto;" /></code></sup></h1>
<x-layout>
    <x-slot name="title">
        Добавление карты оплаты
    </x-slot>

    <x-slot name="scripts">
        <script>
         const closeWindow = () => {
          window.open(location.href, "_self", "");
          window.close()
         }
        </script>
    </x-slot>
</x-layout>
