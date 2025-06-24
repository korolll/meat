<html>
    <head>
        <title>{{ $title ?? config('app.name') }}</title>
        {{ $scripts ?? '' }}
    </head>
    <body>
        {{ $slot }}
    </body>
</html>
