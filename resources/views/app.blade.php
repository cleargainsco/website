<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class([
    'theme-admin' => ! str($page['component'])->startsWith(['marketing/', 'account/', 'auth/']),
    'dark' => ($appearance ?? 'system') == 'dark',
])>
    <head>
        <meta charset="utf-8">
        @head

        {{-- Dark mode only takes effect inside the admin theme; the public site is always light --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Matches each area's page background so there is no flash before the CSS loads --}}
        <style>
            html {
                background-color: #F7F3EC;
            }

            html.theme-admin {
                background-color: oklch(0.9711 0.0034 145.5494);
            }

            html.theme-admin.dark {
                background-color: oklch(0.2002 0 0);
            }
        </style>

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head />
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
