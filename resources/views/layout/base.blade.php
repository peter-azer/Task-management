<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Taskaty</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    @stack("head")
</head>

<body>
    <x-toast-manager></x-toast-manager>
    <x-modal-manager></x-modal-manager>
    <x-page-loader></x-page-loader>
    @yield('body')
    @stack('component')
    @include("components.notification-script")
    @stack('page')
    @push('page')
    <script>
        window.addEventListener('load', () => {
            if (PageLoader?.close) {
                PageLoader.close();
            }
        });
    </script>
    @endpush
</body>

</html>