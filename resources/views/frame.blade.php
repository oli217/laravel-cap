<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('vendor/cap/cap-widget.css') }}">
    <script type="module" src="{{ asset('vendor/cap/cap-widget.js') }}"></script>
    <style>body{margin:0;padding:0;background:transparent;}</style>
</head>
<body>
    <cap-widget data-cap-api-endpoint="{{ config('cap.endpoint') }}"></cap-widget>
    <script type="module">
    const widget = document.querySelector('cap-widget');

    widget.addEventListener('solve', (e) => {
        window.parent.postMessage({ type: 'cap:token', token: e.detail.token }, '*');
    });

    window.addEventListener('message', (e) => {
        if (e.origin !== window.location.origin) return;
        if (!e.data || e.data.type !== 'cap:start') return;
        widget.solve();
    });
    </script>
</body>
</html>
