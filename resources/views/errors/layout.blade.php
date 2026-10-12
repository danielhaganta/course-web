<!DOCTYPE html>
<html lang="id" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0C0D0E">
        <meta name="robots" content="noindex">

        <title>@yield('title') - {{ config('app.name') }}</title>

        <style>
            html {
                background-color: #0c0d0e;
            }
        </style>

        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-background font-sans text-foreground antialiased">
        <main class="flex min-h-svh flex-col items-center justify-center px-6 py-16 text-center">
            <a href="{{ url('/') }}" class="flex items-baseline gap-2">
                <span class="font-display text-3xl font-black tracking-[0.08em]">POEIMA</span>
                <span class="text-xs tracking-[0.14em] text-muted-foreground uppercase">Academy</span>
            </a>

            <p class="mt-14 font-display text-[clamp(96px,18vw,160px)] leading-none font-black text-foreground/20">@yield('code')</p>
            <h1 class="mt-2 font-display text-[clamp(40px,6vw,64px)] leading-[0.92] font-extrabold uppercase">@yield('title')</h1>
            <p class="mt-5 max-w-md leading-relaxed text-foreground-soft">@yield('message')</p>

            <a href="{{ url('/') }}" class="mt-9 inline-flex h-12 items-center rounded-[3px] bg-primary px-7 text-[15px] font-semibold text-primary-foreground transition-colors hover:bg-primary/85">
                Kembali ke beranda
            </a>
        </main>
    </body>
</html>
