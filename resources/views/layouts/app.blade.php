@php($siteName = \App\Models\Setting::get('name', 'Shark Tattoo'))
<!DOCTYPE html>
<html lang="ru" class="bg-ink">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $siteName }}</title>
    <meta name="description" content="{{ $description ?? 'Shark Tattoo — fine line тату и пирсинг в Ереване. Индивидуальные эскизы, запись онлайн.' }}">
    <meta name="theme-color" content="#0a0c0f">
    <meta property="og:title" content="{{ $siteName }} — fine line тату в Ереване">
    <meta property="og:description" content="Fine line тату и пирсинг в Ереване. Запись онлайн.">
    <meta property="og:image" content="{{ url(\App\Models\Media::url(\App\Models\Setting::get('avatar', '/ig/avatar.jpg'))) }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Manrope:wght@400;500;600;700&family=Noto+Sans+Armenian:wght@400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-ink font-sans text-foam antialiased">
    {{ $slot }}
    @livewireScripts
</body>
</html>
