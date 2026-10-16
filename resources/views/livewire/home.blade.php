@php
    $avatar = \App\Models\Media::url($s('avatar', '/ig/avatar.jpg'));
    $hero = \App\Models\Media::url($s('hero', '/ig/reels/10.jpg'));
    $insta = $s('instagram', 'https://www.instagram.com/_shark__tattoo/');
    $tg = $s('telegram');
    $fmt = fn ($n) => $n >= 1000 ? rtrim(rtrim(number_format($n / 1000, 1, '.', ''), '0'), '.').'K' : (string) $n;
    $bioLines = array_filter(array_map('trim', explode("\n", (string) $s('bio', ''))));
@endphp
<div x-data="{ open: null, menu: false, story: null, slide: 0 }" @keydown.escape.window="open = null; story = null">

    {{-- Шапка --}}
    <header class="fixed inset-x-0 top-0 z-40 border-b border-foam/5 bg-ink/70 backdrop-blur-xl">
        <div class="container-x flex h-16 items-center justify-between gap-6">
            <a href="#top" class="flex items-center gap-3 text-bone">
                <x-logo class="h-6 w-auto" />
                <span class="text-sm font-bold uppercase tracking-[.32em]">{{ $s('name', 'Shark Tattoo') }}</span>
            </a>
            <nav class="hidden items-center gap-8 text-sm text-fin md:flex">
                <a href="#works" class="hover:text-bone">{{ __('Работы') }}</a>
                <a href="#about" class="hover:text-bone">{{ __('О мастере') }}</a>
                <a href="#services" class="hover:text-bone">{{ __('Услуги') }}</a>
                <a href="#contacts" class="hover:text-bone">{{ __('Контакты') }}</a>
            </nav>
            <div class="flex items-center gap-2">
                <div class="flex rounded-full border border-foam/15 p-0.5 text-xs font-semibold">
                    @foreach (['hy' => 'ՀԱՅ', 'ru' => 'РУС', 'en' => 'ENG'] as $code => $label)
                        <a href="{{ $code === 'hy' ? url('/') : url('/'.$code) }}" hreflang="{{ $code }}" @class(['rounded-full px-2.5 py-1.5 transition', 'bg-bone text-ink' => $lang === $code, 'text-fin hover:text-bone' => $lang !== $code])>{{ $label }}</a>
                    @endforeach
                </div>
                <a href="#booking" class="btn-light hidden !px-5 !py-2.5 sm:inline-flex">{{ __('Записаться') }}</a>
                <button @click="menu = !menu" class="grid size-10 place-items-center rounded-full border border-foam/15 md:hidden" aria-label="{{ __('Меню') }}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 8h16M4 16h16"/></svg>
                </button>
            </div>
        </div>
        <div x-cloak x-show="menu" x-transition class="border-t border-foam/5 bg-ink md:hidden">
            <nav class="container-x flex flex-col py-4 text-lg" @click="menu = false">
                <a href="#works" class="py-2">{{ __('Работы') }}</a>
                <a href="#about" class="py-2">{{ __('О мастере') }}</a>
                <a href="#services" class="py-2">{{ __('Услуги') }}</a>
                <a href="#contacts" class="py-2">{{ __('Контакты') }}</a>
                <a href="#booking" class="btn-light mt-3">{{ __('Записаться') }}</a>
            </nav>
        </div>
    </header>

    {{-- Первый экран --}}
    <section id="top" class="grain relative overflow-hidden pt-16">
        <div class="pointer-events-none absolute inset-0">
            <img src="{{ $hero }}" alt="" class="h-full w-full scale-110 object-cover opacity-25 blur-2xl">
            <div class="absolute inset-0 bg-gradient-to-b from-ink/40 via-ink/80 to-ink"></div>
        </div>
        <div class="container-x relative grid items-center gap-12 py-16 lg:grid-cols-[1.15fr_.85fr] lg:py-28">
            <div>
                <p class="eyebrow">{{ $s('title_am', 'Տատու Երևան') }} · {{ $t('city', __('Ереван, Армения')) }}</p>
                <h1 class="mt-6 text-5xl leading-[.95] text-bone sm:text-7xl lg:text-[5.5rem]">
                    {{ $t('tagline', __('Fine line тату в Ереване')) }}
                </h1>
                <div class="mt-8 hairline"></div>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-fin">
                    {{ __('Тонкие линии, чистая графика и эскиз под тебя. В студии работает профессиональный пирсер.') }}
                </p>
                <div class="mt-10 flex flex-wrap gap-3">
                    <a href="#booking" class="btn-light">{{ __('Записаться на сеанс') }}</a>
                    <a href="#works" class="btn-line">{{ __('Смотреть работы') }}</a>
                </div>
                <dl class="mt-14 grid max-w-md grid-cols-3 gap-6 border-t border-foam/10 pt-8">
                    <div><dt class="eyebrow !tracking-[.2em]">{{ __('Подписчики') }}</dt><dd class="mt-2 font-display text-4xl text-bone">{{ $fmt((int) $s('followers', 0)) }}</dd></div>
                    <div><dt class="eyebrow !tracking-[.2em]">{{ __('Публикации') }}</dt><dd class="mt-2 font-display text-4xl text-bone">{{ $s('posts_count', '—') }}</dd></div>
                    <div><dt class="eyebrow !tracking-[.2em]">{{ __('Стиль') }}</dt><dd class="mt-2 font-display text-4xl text-bone">Fine</dd></div>
                </dl>
            </div>
            <div class="relative mx-auto w-full max-w-sm">
                <div class="absolute -inset-6 rounded-[3rem] border border-foam/10"></div>
                <div class="relative aspect-[4/5] overflow-hidden rounded-[2.5rem] bg-deep">
                    <img src="{{ $avatar }}" alt="{{ $s('name') }}" class="h-full w-full object-cover grayscale">
                </div>
                <a href="{{ $insta }}" target="_blank" rel="noopener" class="absolute -bottom-5 -left-5 flex items-center gap-3 rounded-2xl border border-foam/10 bg-ink/90 px-4 py-3 backdrop-blur">
                    <img src="{{ $avatar }}" alt="" class="size-10 rounded-full object-cover ring-2 ring-foam/20">
                    <span class="text-sm"><span class="block font-semibold text-bone">{{ '@'.$s('handle', '_shark__tattoo') }}</span><span class="text-fin">Instagram</span></span>
                </a>
            </div>
        </div>
    </section>

    {{-- Актуальные --}}
    @if ($highlights->isNotEmpty())
        <section class="border-y border-foam/5 bg-abyss/50">
            <div class="container-x flex gap-6 overflow-x-auto py-8">
                @foreach ($highlights as $h)
                    @php($slides = $h->slides())
                    <button @click="story = {{ Js::from(['title' => $h->title, 'slides' => $slides]) }}; slide = 0" class="group flex w-20 shrink-0 flex-col items-center gap-2 text-center">
                        <span class="rounded-full border border-foam/30 p-1 transition group-hover:border-bone">
                            <img src="{{ $h->coverUrl() ?? $avatar }}" alt="" class="size-16 rounded-full object-cover">
                        </span>
                        <span class="line-clamp-1 text-xs text-fin group-hover:text-bone">{{ $h->title }}</span>
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Работы --}}
    <section id="works" class="py-24">
        <div class="container-x">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <p class="eyebrow">{{ __('Портфолио') }}</p>
                    <h2 class="mt-3 text-5xl text-bone sm:text-6xl">{{ __('Работы и видео') }}</h2>
                </div>
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <button wire:click="setFilter('all')" @class(['chip', 'border-bone bg-bone text-ink' => $filter === 'all', 'border-foam/15 text-fin hover:text-bone' => $filter !== 'all'])>{{ __('Все') }}</button>
                    @foreach (\App\Models\Post::CATEGORIES as $key => $label)
                        @if ($counts[$key] ?? 0)
                            <button wire:click="setFilter('{{ $key }}')" @class(['chip', 'border-bone bg-bone text-ink' => $filter === $key, 'border-foam/15 text-fin hover:text-bone' => $filter !== $key])>
                                {{ __($label) }} <span class="text-xs opacity-60">{{ $counts[$key] }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4" wire:loading.class="opacity-60">
                @forelse ($posts as $post)
                    @php($payload = ['embed' => $post->video_url ? null : $post->embedUrl(), 'video' => $post->video_url, 'images' => $post->imageUrls(), 'caption' => $post->caption, 'link' => $post->instagramUrl(), 'kind' => $post->kind])
                    <button wire:key="post-{{ $post->id }}" @click="open = {{ Js::from($payload) }}; slide = 0"
                        class="group relative aspect-[3/4] overflow-hidden rounded-2xl bg-deep text-left">
                        @if ($post->coverUrl())
                            <img src="{{ $post->coverUrl() }}" alt="{{ \Illuminate\Support\Str::limit($post->caption, 60) }}" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-ink/90 via-ink/0 to-ink/0 opacity-80 transition group-hover:opacity-100"></div>
                        @if ($post->kind === 'reel')
                            <span class="absolute right-3 top-3 grid size-8 place-items-center rounded-full bg-ink/60 backdrop-blur">
                                <svg class="size-3.5 translate-x-px" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4v16l13-8z"/></svg>
                            </span>
                        @elseif ($post->kind === 'carousel')
                            <span class="absolute right-3 top-3 rounded-full bg-ink/60 px-2 py-1 text-[10px] font-bold backdrop-blur">1/{{ count($post->images ?? []) }}</span>
                        @endif
                        <div class="absolute inset-x-0 bottom-0 p-4">
                            <p class="line-clamp-2 text-sm text-bone">{{ $post->caption ?: ' ' }}</p>
                            @if ($post->views || $post->likes)
                                <p class="mt-1 text-xs text-fin">
                                    @if ($post->views) ▶ {{ $fmt($post->views) }} @endif
                                    @if ($post->likes) &nbsp;♥ {{ $fmt($post->likes) }} @endif
                                </p>
                            @endif
                        </div>
                    </button>
                @empty
                    <p class="col-span-full py-16 text-center text-fin">{{ __('Здесь скоро появятся работы.') }}</p>
                @endforelse
            </div>

            <div class="mt-12 flex flex-wrap items-center justify-center gap-3">
                @if ($total > $posts->count())
                    <button wire:click="more" class="btn-line">{{ __('Показать ещё') }}</button>
                @endif
                <a href="{{ $insta }}" target="_blank" rel="noopener" class="btn-line">{{ __('Все :n публикаций в Instagram', ['n' => $s('posts_count', '')]) }} ↗</a>
            </div>
        </div>
    </section>

    {{-- О мастере --}}
    <section id="about" class="border-t border-foam/5 bg-abyss/40 py-24">
        <div class="container-x grid items-center gap-14 lg:grid-cols-2">
            <div class="grid grid-cols-2 gap-3">
                @foreach ($posts->where('category', 'tattoo')->take(4)->values() as $i => $p)
                    <img src="{{ $p->coverUrl() }}" alt="" loading="lazy" @class(['aspect-[3/4] w-full rounded-2xl object-cover', 'translate-y-8' => $i % 2])>
                @endforeach
            </div>
            <div>
                <p class="eyebrow">{{ __('О мастере') }}</p>
                <h2 class="mt-3 text-5xl text-bone sm:text-6xl">{{ $s('name', 'Shark Tattoo') }}</h2>
                <div class="mt-6 hairline"></div>
                <div class="mt-6 space-y-4 text-lg leading-relaxed text-fin">
                    @foreach (preg_split('/\n\s*\n/', (string) $t('about', '')) as $para)
                        <p>{{ $para }}</p>
                    @endforeach
                </div>
                @if ($bioLines)
                    <ul class="mt-8 space-y-2 border-l border-foam/15 pl-5 text-sm text-foam/80">
                        @foreach ($bioLines as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                @endif
                @if ($s('brand_instagram'))
                    <a href="{{ $s('brand_instagram') }}" target="_blank" rel="noopener" class="mt-8 inline-flex items-center gap-2 text-sm text-bone underline decoration-foam/30 underline-offset-4 hover:decoration-bone">
                        {{ __('Основатель') }} {{ $s('brand_name', '@sharp_by_shark') }} ↗
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- Услуги --}}
    <section id="services" class="py-24">
        <div class="container-x">
            <p class="eyebrow">{{ __('Услуги') }}</p>
            <h2 class="mt-3 text-5xl text-bone sm:text-6xl">{{ __('Что мы делаем') }}</h2>
            <div class="mt-12 grid gap-4 md:grid-cols-3">
                @foreach ([
                    [__('Fine line тату'), __('Тонкие линии, микро-тату, надписи и графика. Рисуем эскиз под тебя и место на теле.')],
                    [__('Пирсинг'), __('Профессиональный пирсер в студии. Стерильные одноразовые иглы и качественные украшения.')],
                    [__('Эскиз и консультация'), __('Обсудим идею, размер и место. Подскажем, как тату будет выглядеть через годы.')],
                ] as $i => [$title, $desc])
                    <div class="card p-8">
                        <span class="font-display text-5xl text-foam/30">0{{ $i + 1 }}</span>
                        <h3 class="mt-6 text-3xl text-bone">{{ $title }}</h3>
                        <p class="mt-3 leading-relaxed text-fin">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-16 grid gap-px overflow-hidden rounded-[1.75rem] border border-foam/10 bg-foam/10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([[__('Напиши'), __('Оставь заявку или напиши в Direct / Telegram')], [__('Эскиз'), __('Обсуждаем идею и готовим эскиз')], [__('Сеанс'), __('Спокойная атмосфера и стерильность')], [__('Уход'), __('Даём памятку по заживлению')]] as $i => [$title, $desc])
                    <div class="bg-ink p-6">
                        <p class="eyebrow">{{ __('Шаг') }} {{ $i + 1 }}</p>
                        <p class="mt-3 text-xl font-semibold text-bone">{{ $title }}</p>
                        <p class="mt-1 text-sm text-fin">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Запись и контакты --}}
    <section id="booking" class="border-t border-foam/5 bg-abyss/40 py-24">
        <div class="container-x grid gap-12 lg:grid-cols-[.9fr_1.1fr]">
            <div id="contacts">
                <p class="eyebrow">{{ __('Запись') }}</p>
                <h2 class="mt-3 text-5xl text-bone sm:text-6xl">{{ __('Записаться на сеанс') }}</h2>
                <p class="mt-6 max-w-md text-lg text-fin">{{ __('Оставь заявку, и мы ответим, чтобы обсудить эскиз и время. Или напиши напрямую.') }}</p>
                <div class="mt-10 space-y-3">
                    <a href="{{ $insta }}" target="_blank" rel="noopener" class="card flex items-center justify-between p-5 hover:border-foam/30">
                        <span><span class="eyebrow block">Instagram Direct</span><span class="mt-1 block text-lg text-bone">{{ '@'.$s('handle', '_shark__tattoo') }}</span></span><span class="text-fin">↗</span>
                    </a>
                    @if ($tg)
                        <a href="{{ $tg }}" target="_blank" rel="noopener" class="card flex items-center justify-between p-5 hover:border-foam/30">
                            <span><span class="eyebrow block">Telegram</span><span class="mt-1 block text-lg text-bone">{{ '@'.\Illuminate\Support\Str::afterLast(rtrim($tg, '/'), '/') }}</span></span><span class="text-fin">↗</span>
                        </a>
                    @endif
                    @if ($s('phone'))
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $s('phone')) }}" class="card flex items-center justify-between p-5 hover:border-foam/30">
                            <span><span class="eyebrow block">{{ __('Телефон') }}</span><span class="mt-1 block text-lg text-bone">{{ $s('phone') }}</span></span><span class="text-fin">↗</span>
                        </a>
                    @endif
                    <div class="card p-5">
                        <span class="eyebrow block">{{ __('Студия') }}</span>
                        <span class="mt-1 block text-lg text-bone">{{ $t('address') ?: $t('city', __('Ереван, Армения')) }}</span>
                        <span class="text-sm text-fin">{{ $t('hours', __('По записи')) }}</span>
                    </div>
                </div>
            </div>
            <livewire:booking-form :lang="$lang" />
        </div>
    </section>

    <footer class="border-t border-foam/5 py-10">
        <div class="container-x flex flex-wrap items-center justify-between gap-4 text-sm text-fin">
            <span class="flex items-center gap-3"><x-logo class="h-5 w-auto text-bone" /> © {{ date('Y') }} {{ $s('name', 'Shark Tattoo') }} · {{ $t('city', __('Ереван, Армения')) }}</span>
            <span class="flex gap-5"><a href="{{ $insta }}" target="_blank" rel="noopener" class="hover:text-bone">Instagram</a>@if ($tg)<a href="{{ $tg }}" target="_blank" rel="noopener" class="hover:text-bone">Telegram</a>@endif</span>
        </div>
    </footer>

    {{-- Просмотр публикации --}}
    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-ink/95 p-4 backdrop-blur" @click.self="open = null">
        <button @click="open = null" class="absolute right-4 top-4 grid size-11 place-items-center rounded-full border border-foam/20 text-2xl text-bone" aria-label="{{ __('Закрыть') }}">×</button>
        <template x-if="open">
            <div class="flex max-h-full w-full max-w-5xl flex-col items-center gap-6 lg:flex-row lg:items-start">
                <div class="w-full max-w-[420px] shrink-0 overflow-hidden rounded-2xl bg-deep">
                    <template x-if="open.video"><video :src="open.video" controls autoplay playsinline class="max-h-[80vh] w-full"></video></template>
                    <template x-if="!open.video && open.embed"><iframe :src="open.embed" class="h-[75vh] max-h-[760px] w-full bg-white" allow="autoplay; encrypted-media" allowfullscreen loading="lazy"></iframe></template>
                    <template x-if="!open.video && !open.embed && open.images.length">
                        <div class="relative">
                            <img :src="open.images[slide]" alt="" class="max-h-[80vh] w-full object-contain">
                            <template x-if="open.images.length > 1">
                                <div class="absolute inset-x-0 bottom-3 flex justify-center gap-2">
                                    <template x-for="(img, i) in open.images" :key="i"><button @click="slide = i" class="size-2 rounded-full" :class="i === slide ? 'bg-bone' : 'bg-bone/30'"></button></template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                <div class="w-full max-w-md overflow-y-auto lg:max-h-[75vh]">
                    <p class="whitespace-pre-line leading-relaxed text-foam" x-text="open.caption"></p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#booking" @click="open = null" class="btn-light">{{ __('Хочу такое же') }}</a>
                        <template x-if="open.link"><a :href="open.link" target="_blank" rel="noopener" class="btn-line">{{ __('Открыть в Instagram ↗') }}</a></template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Просмотр актуального --}}
    <div x-cloak x-show="story" x-transition.opacity class="fixed inset-0 z-50 grid place-items-center bg-ink/95 p-4" @click.self="story = null">
        <template x-if="story">
            <div class="relative aspect-[9/16] h-[85vh] max-w-full overflow-hidden rounded-2xl bg-deep">
                <div class="absolute inset-x-3 top-3 z-10 flex gap-1">
                    <template x-for="(s, i) in story.slides" :key="i"><span class="h-0.5 flex-1 rounded" :class="i <= slide ? 'bg-bone' : 'bg-bone/30'"></span></template>
                </div>
                <p class="absolute left-4 top-6 z-10 text-sm font-semibold text-bone" x-text="story.title"></p>
                <template x-if="story.slides[slide]?.type === 'video'"><video :src="story.slides[slide].src" autoplay playsinline controls class="h-full w-full object-cover"></video></template>
                <template x-if="story.slides[slide]?.type !== 'video'"><img :src="story.slides[slide]?.src" class="h-full w-full object-cover"></template>
                <button class="absolute inset-y-0 left-0 w-1/3" @click="slide = Math.max(0, slide - 1)" aria-label="{{ __('Назад') }}"></button>
                <button class="absolute inset-y-0 right-0 w-1/3" @click="slide < story.slides.length - 1 ? slide++ : story = null" aria-label="{{ __('Дальше') }}"></button>
            </div>
        </template>
    </div>
</div>
