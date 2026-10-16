@php($M = \App\Models\Media::class)
<div class="min-h-screen">
    <header class="sticky top-0 z-30 border-b border-foam/10 bg-ink/90 backdrop-blur">
        <div class="container-x flex h-16 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-bone" target="_blank">
                <x-logo class="h-5 w-auto" /><span class="text-sm font-bold uppercase tracking-[.25em]">Админка</span>
            </a>
            <nav class="flex gap-1 overflow-x-auto text-sm">
                @foreach (['bookings' => 'Заявки', 'posts' => 'Публикации', 'highlights' => 'Актуальные', 'profile' => 'Профиль'] as $key => $label)
                    <button wire:click="$set('tab', '{{ $key }}')" @class(['chip', 'border-bone bg-bone text-ink' => $tab === $key, 'border-transparent text-fin hover:text-bone' => $tab !== $key])>
                        {{ $label }}
                        @if ($key === 'bookings' && $newBookings)<span class="rounded-full bg-blood px-1.5 text-[10px] text-white">{{ $newBookings }}</span>@endif
                    </button>
                @endforeach
            </nav>
            <button wire:click="logout" class="text-sm text-fin hover:text-bone">Выйти</button>
        </div>
    </header>

    <main class="container-x py-8">
        @if ($notice)
            <div class="mb-6 flex items-center justify-between rounded-2xl border border-foam/15 bg-white/5 px-5 py-3 text-sm text-bone">
                {{ $notice }} <button wire:click="$set('notice', null)" class="text-fin">×</button>
            </div>
        @endif

        {{-- Заявки --}}
        @if ($tab === 'bookings')
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-4xl text-bone">Заявки</h1>
                <select wire:model.live="bookingStatus" class="input w-48">
                    <option value="">Все</option>
                    @foreach (\App\Models\Booking::STATUSES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="mt-6 space-y-3">
                @forelse ($bookings as $b)
                    <div wire:key="b{{ $b->id }}" class="card grid gap-4 p-5 md:grid-cols-[1fr_auto]">
                        <div>
                            <p class="text-xs text-fin">#{{ $b->id }} · {{ $b->created_at->timezone('Asia/Yerevan')->format('d.m.Y H:i') }} · {{ \App\Models\Booking::SERVICES[$b->service] ?? $b->service }}</p>
                            <p class="mt-1 text-lg font-semibold text-bone">{{ $b->name }} · <span class="font-normal">{{ $b->contact }}</span></p>
                            @if ($b->placement || $b->size)<p class="mt-1 text-sm text-fin">{{ collect([$b->placement, $b->size])->filter()->join(' · ') }}</p>@endif
                            @if ($b->idea)<p class="mt-2 whitespace-pre-line text-sm text-foam">{{ $b->idea }}</p>@endif
                        </div>
                        <div class="flex flex-wrap items-start gap-2 md:justify-end">
                            @foreach (\App\Models\Booking::STATUSES as $k => $v)
                                <button wire:click="setBookingStatus({{ $b->id }}, '{{ $k }}')" @class(['chip !px-3 !py-1.5 text-xs', 'border-bone bg-bone text-ink' => $b->status === $k, 'border-foam/15 text-fin' => $b->status !== $k])>{{ $v }}</button>
                            @endforeach
                            <button wire:click="deleteBooking({{ $b->id }})" wire:confirm="Удалить заявку?" class="chip !px-3 !py-1.5 border-blood/40 text-xs text-blood">Удалить</button>
                        </div>
                    </div>
                @empty
                    <p class="card p-10 text-center text-fin">Заявок пока нет. Они появятся здесь, когда кто-то заполнит форму на сайте.</p>
                @endforelse
            </div>
            <div class="mt-6">{{ $bookings->links() }}</div>
        @endif

        {{-- Публикации --}}
        @if ($tab === 'posts')
            @if ($editing !== null)
                <div class="card p-6">
                    <div class="flex items-center justify-between"><h2 class="text-3xl text-bone">{{ $editing ? 'Редактировать публикацию' : 'Новая публикация' }}</h2><button wire:click="closeEditor" class="text-fin">Закрыть ×</button></div>
                    <div class="mt-6 grid gap-5 lg:grid-cols-2">
                        <div class="space-y-4">
                            <div><label class="label">Ссылка на пост или рилс в Instagram</label><input wire:model="form.instagram" class="input" placeholder="https://www.instagram.com/reel/…">
                                <p class="mt-1 text-xs text-fin">Видео по ссылке будет играть прямо на сайте через плеер Instagram.</p>
                                @error('form.instagram') <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror</div>
                            <div class="grid grid-cols-2 gap-3">
                                <div><label class="label">Тип</label><select wire:model="form.kind" class="input"><option value="photo">Фото</option><option value="reel">Видео / Reels</option><option value="carousel">Карусель</option></select></div>
                                <div><label class="label">Раздел</label><select wire:model="form.category" class="input">@foreach (\App\Models\Post::CATEGORIES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
                            </div>
                            <div><label class="label">Подпись</label><textarea wire:model="form.caption" rows="5" class="input"></textarea></div>
                            <div><label class="label">Ссылка на видео-файл mp4 (необязательно)</label><input wire:model="form.video_url" class="input" placeholder="https://…/video.mp4">@error('form.video_url') <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror</div>
                            <div class="grid grid-cols-3 gap-3">
                                <div><label class="label">Просмотры</label><input type="number" wire:model="form.views" class="input"></div>
                                <div><label class="label">Лайки</label><input type="number" wire:model="form.likes" class="input"></div>
                                <div><label class="label">Дата</label><input type="date" wire:model="form.taken_at" class="input"></div>
                            </div>
                            <div class="flex flex-wrap gap-5 text-sm">
                                <label class="flex items-center gap-2"><input type="checkbox" wire:model="form.is_visible" class="size-5 accent-white"> Показывать</label>
                                <label class="flex items-center gap-2"><input type="checkbox" wire:model="form.is_featured" class="size-5 accent-white"> Закрепить первой</label>
                                <label class="flex items-center gap-2">Порядок <input type="number" wire:model="form.sort" class="input !w-20 !py-1.5"></label>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <span class="label">Обложка</span>
                                <div class="flex items-end gap-4">
                                    <div class="aspect-[3/4] w-32 overflow-hidden rounded-xl bg-deep">@if ($form['cover'] ?? null)<img src="{{ $M::url($form['cover']) }}" class="h-full w-full object-cover">@endif</div>
                                    <label class="btn-line cursor-pointer"><span wire:loading.remove wire:target="coverUpload">Загрузить обложку</span><span wire:loading wire:target="coverUpload">Загрузка…</span><input type="file" wire:model="coverUpload" accept="image/*" class="sr-only"></label>
                                </div>
                                @error('coverUpload') <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror
                                @error('form.cover') <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <span class="label">Фото (для карусели)</span>
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach ($form['images'] ?? [] as $i => $img)
                                        <div class="group relative aspect-square overflow-hidden rounded-xl bg-deep" wire:key="img{{ $i }}">
                                            <img src="{{ $M::url($img) }}" class="h-full w-full object-cover">
                                            <button wire:click="removeImage({{ $i }})" class="absolute right-1 top-1 grid size-6 place-items-center rounded-full bg-ink/80 text-xs">×</button>
                                            <button wire:click="$set('form.cover', '{{ $img }}')" class="absolute inset-x-1 bottom-1 rounded bg-ink/80 text-[10px] opacity-0 group-hover:opacity-100">обложка</button>
                                        </div>
                                    @endforeach
                                    <label class="grid aspect-square cursor-pointer place-items-center rounded-xl border border-dashed border-foam/25 text-sm text-fin hover:text-bone">
                                        <span wire:loading.remove wire:target="uploads">+ Фото</span><span wire:loading wire:target="uploads">…</span>
                                        <input type="file" wire:model="uploads" accept="image/*" multiple class="sr-only">
                                    </label>
                                </div>
                                @error('uploads.*') <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex flex-wrap justify-between gap-3">
                        <button wire:click="savePost" class="btn-light">Сохранить</button>
                        @if ($editing)<button wire:click="deletePost({{ $editing }})" wire:confirm="Удалить публикацию с сайта?" class="btn border border-blood/40 text-blood">Удалить</button>@endif
                    </div>
                </div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h1 class="text-4xl text-bone">Публикации</h1>
                    <button wire:click="editPost" class="btn-light">+ Добавить</button>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                    @foreach ($posts as $p)
                        <div wire:key="p{{ $p->id }}" @class(['card overflow-hidden', 'opacity-40' => ! $p->is_visible])>
                            <button wire:click="editPost({{ $p->id }})" class="block aspect-[3/4] w-full bg-deep">@if ($p->coverUrl())<img src="{{ $p->coverUrl() }}" class="h-full w-full object-cover" loading="lazy">@endif</button>
                            <div class="space-y-2 p-3">
                                <p class="line-clamp-1 text-xs text-fin">{{ \App\Models\Post::CATEGORIES[$p->category] ?? '' }} · {{ $p->caption ?: 'без подписи' }}</p>
                                <div class="flex gap-1">
                                    <button wire:click="togglePost({{ $p->id }}, 'is_visible')" class="chip !px-2 !py-1 border-foam/15 text-[11px]">{{ $p->is_visible ? 'Скрыть' : 'Показать' }}</button>
                                    <button wire:click="togglePost({{ $p->id }}, 'is_featured')" @class(['chip !px-2 !py-1 text-[11px]', 'border-bone bg-bone text-ink' => $p->is_featured, 'border-foam/15' => ! $p->is_featured])>★</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $posts->links() }}</div>
            @endif
        @endif

        {{-- Актуальные --}}
        @if ($tab === 'highlights')
            @if ($editing !== null)
                <div class="card p-6">
                    <div class="flex items-center justify-between"><h2 class="text-3xl text-bone">{{ $editing ? 'Редактировать актуальное' : 'Новое актуальное' }}</h2><button wire:click="closeEditor" class="text-fin">Закрыть ×</button></div>
                    <div class="mt-6 grid gap-5 md:grid-cols-[200px_1fr]">
                        <div>
                            <span class="label">Обложка</span>
                            <div class="size-32 overflow-hidden rounded-full border border-foam/20 bg-deep">@if ($form['cover'] ?? null)<img src="{{ $M::url($form['cover']) }}" class="h-full w-full object-cover">@endif</div>
                            <label class="btn-line mt-3 cursor-pointer !px-4 !py-2"><span wire:loading.remove wire:target="coverUpload">Загрузить</span><span wire:loading wire:target="coverUpload">…</span><input type="file" wire:model="coverUpload" accept="image/*" class="sr-only"></label>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-[1fr_100px] gap-3">
                                <div><label class="label">Название</label><input wire:model="form.title" class="input" placeholder="Работы, Отзывы, Пирсинг…">@error('form.title') <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror</div>
                                <div><label class="label">Порядок</label><input type="number" wire:model="form.sort" class="input"></div>
                            </div>
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="form.is_visible" class="size-5 accent-white"> Показывать на сайте</label>
                            <div>
                                <span class="label">Слайды</span>
                                <div class="grid grid-cols-3 gap-2 sm:grid-cols-6">
                                    @foreach ($form['items'] ?? [] as $i => $it)
                                        <div class="relative aspect-[9/16] overflow-hidden rounded-xl bg-deep" wire:key="it{{ $i }}">
                                            @if (($it['type'] ?? '') === 'video')<div class="grid h-full place-items-center p-2 text-center text-[10px] text-fin">▶ видео</div>@else<img src="{{ $M::url($it['src']) }}" class="h-full w-full object-cover">@endif
                                            <div class="absolute inset-x-1 bottom-1 flex justify-between">
                                                <button wire:click="moveItem({{ $i }}, -1)" class="rounded bg-ink/80 px-1.5 text-xs">←</button>
                                                <button wire:click="removeItem({{ $i }})" class="rounded bg-ink/80 px-1.5 text-xs text-blood">×</button>
                                                <button wire:click="moveItem({{ $i }}, 1)" class="rounded bg-ink/80 px-1.5 text-xs">→</button>
                                            </div>
                                            <button wire:click="$set('form.cover', '{{ $it['src'] }}')" class="absolute inset-x-1 top-1 rounded bg-ink/70 text-[10px]">обложка</button>
                                        </div>
                                    @endforeach
                                    <label class="grid aspect-[9/16] cursor-pointer place-items-center rounded-xl border border-dashed border-foam/25 text-sm text-fin">
                                        <span wire:loading.remove wire:target="uploads">+ Фото</span><span wire:loading wire:target="uploads">…</span>
                                        <input type="file" wire:model="uploads" wire:change="$nextTick(() => $wire.addHighlightImages())" accept="image/*" multiple class="sr-only">
                                    </label>
                                </div>
                                @if ($uploads)<button wire:click="addHighlightImages" class="btn-line mt-3 !py-2">Добавить выбранные фото</button>@endif
                                <div class="mt-3 flex gap-2"><input wire:model="form.video" class="input" placeholder="Ссылка на видео mp4"><button wire:click="addHighlightVideo" class="btn-line !py-2">Добавить</button></div>
                                @error('form.video') <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex flex-wrap justify-between gap-3">
                        <button wire:click="saveHighlight" class="btn-light">Сохранить</button>
                        @if ($editing)<button wire:click="deleteHighlight({{ $editing }})" wire:confirm="Удалить актуальное?" class="btn border border-blood/40 text-blood">Удалить</button>@endif
                    </div>
                </div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h1 class="text-4xl text-bone">Актуальные</h1>
                    <button wire:click="editHighlight" class="btn-light">+ Добавить</button>
                </div>
                <div class="mt-6 flex flex-wrap gap-6">
                    @forelse ($highlights as $h)
                        <button wire:click="editHighlight({{ $h->id }})" @class(['flex w-24 flex-col items-center gap-2', 'opacity-40' => ! $h->is_visible])>
                            <span class="size-20 overflow-hidden rounded-full border border-foam/30 bg-deep">@if ($h->coverUrl())<img src="{{ $h->coverUrl() }}" class="h-full w-full object-cover">@endif</span>
                            <span class="text-xs text-fin">{{ $h->title }} · {{ count($h->items ?? []) }}</span>
                        </button>
                    @empty
                        <p class="card w-full p-10 text-center text-fin">Актуальных пока нет. Добавь, например, «Работы», «Пирсинг», «Отзывы»: они появятся кружками под первым экраном.</p>
                    @endforelse
                </div>
            @endif
        @endif

        {{-- Профиль --}}
        @if ($tab === 'profile')
            <h1 class="text-4xl text-bone">Профиль и контакты</h1>
            <div class="mt-6 grid gap-6 lg:grid-cols-[280px_1fr]">
                <div class="space-y-6">
                    <div class="card p-5">
                        <span class="label">Фото профиля</span>
                        <img src="{{ $avatar }}" class="aspect-square w-full rounded-2xl object-cover">
                        <label class="btn-line mt-3 w-full cursor-pointer !py-2"><span wire:loading.remove wire:target="avatarUpload">Заменить</span><span wire:loading wire:target="avatarUpload">…</span><input type="file" wire:model="avatarUpload" accept="image/*" class="sr-only"></label>
                    </div>
                    <div class="card p-5">
                        <span class="label">Фон первого экрана</span>
                        <img src="{{ $hero }}" class="aspect-video w-full rounded-2xl object-cover">
                        <label class="btn-line mt-3 w-full cursor-pointer !py-2"><span wire:loading.remove wire:target="heroUpload">Заменить</span><span wire:loading wire:target="heroUpload">…</span><input type="file" wire:model="heroUpload" accept="image/*" class="sr-only"></label>
                    </div>
                </div>
                <form wire:submit="saveSettings" class="card space-y-4 p-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach (\App\Livewire\Admin\Dashboard::SETTING_FIELDS as $key => $label)
                            <div @class(['md:col-span-2' => in_array($key, ['bio', 'about', 'tagline'])])>
                                <label class="label">{{ $label }}</label>
                                @if (in_array($key, ['bio', 'about']))
                                    <textarea wire:model="settings.{{ $key }}" rows="{{ $key === 'about' ? 6 : 4 }}" class="input"></textarea>
                                @else
                                    <input wire:model="settings.{{ $key }}" class="input">
                                @endif
                                @error('settings.'.$key) <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-foam/10 pt-4">
                        <p class="text-lg text-bone">Уведомления о заявках в Telegram</p>
                        <p class="mt-1 text-sm text-fin">Создай бота у @BotFather, напиши ему /start и укажи токен и свой chat id (его покажет @userinfobot).</p>
                        <div class="mt-3 grid gap-4 md:grid-cols-2">
                            <div><label class="label">Токен бота</label><input wire:model="settings.telegram_bot_token" class="input font-mono" placeholder="123456789:AA…">@error('settings.telegram_bot_token') <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror</div>
                            <div><label class="label">Chat id</label><input wire:model="settings.telegram_chat_id" class="input font-mono"></div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <button class="btn-light">Сохранить</button>
                        <button type="button" wire:click="testTelegram" class="btn-line">Проверить Telegram</button>
                    </div>
                </form>
            </div>
        @endif
    </main>
</div>
