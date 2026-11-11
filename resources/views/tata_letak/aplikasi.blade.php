<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="@yield('description', 'OBSIDIAN SIGNAL portfolio and product systems work.')" />
    <title>@yield('title', 'OBSIDIAN SIGNAL')</title>

    <script>
        (function () {
            try {
                var stored = localStorage.getItem('os-theme');
                if (stored === 'light' || stored === 'dark') {
                    document.documentElement.setAttribute('data-theme', stored);
                } else {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="os-root">
    <a class="os-skip-link" href="#main">Skip to content</a>
    @yield('content')
</body>
</html>
