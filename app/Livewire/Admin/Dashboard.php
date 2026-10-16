<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use App\Models\Highlight;
use App\Models\Media;
use App\Models\Post;
use App\Models\Setting;
use App\Support\Telegram;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithFileUploads, WithPagination;

    public const SETTING_FIELDS = [
        'name' => 'Название',
        'title_am' => 'Подпись на армянском',
        'tagline' => 'Слоган (РУС)',
        'tagline_hy' => 'Слоган (ՀԱՅ)',
        'tagline_en' => 'Слоган (ENG)',
        'bio' => 'Био из Instagram',
        'about' => 'О студии (РУС)',
        'about_hy' => 'О студии (ՀԱՅ)',
        'about_en' => 'О студии (ENG)',
        'city' => 'Город (РУС)',
        'city_hy' => 'Город (ՀԱՅ)',
        'city_en' => 'Город (ENG)',
        'address' => 'Адрес (РУС)',
        'address_hy' => 'Адрес (ՀԱՅ)',
        'address_en' => 'Адрес (ENG)',
        'hours' => 'Часы работы (РУС)',
        'hours_hy' => 'Часы работы (ՀԱՅ)',
        'hours_en' => 'Часы работы (ENG)',
        'phone' => 'Телефон',
        'instagram' => 'Instagram (ссылка)',
        'handle' => 'Instagram (ник)',
        'telegram' => 'Telegram (ссылка)',
        'brand_name' => 'Второй бренд (название)',
        'brand_instagram' => 'Второй бренд (ссылка)',
        'followers' => 'Подписчиков',
        'posts_count' => 'Публикаций',
    ];

    #[Url]
    public string $tab = 'bookings';

    #[Url(as: 'status', except: '')]
    public string $bookingStatus = '';

    /** Редактируемая публикация / актуальное: null — форма закрыта, 0 — новая запись. */
    public ?int $editing = null;

    public array $form = [];

    public array $uploads = [];

    public $coverUpload = null;

    public array $settings = [];

    public $avatarUpload = null;

    public $heroUpload = null;

    public ?string $notice = null;

    public function mount(): void
    {
        abort_unless(session('admin'), 403);
        $this->loadSettings();
    }

    private function loadSettings(): void
    {
        foreach (array_keys(self::SETTING_FIELDS) + [] as $key) {
            $this->settings[$key] = (string) Setting::get($key, '');
        }
        $this->settings['telegram_bot_token'] = (string) Setting::get('telegram_bot_token', '');
        $this->settings['telegram_chat_id'] = (string) Setting::get('telegram_chat_id', '');
    }

    public function updatedTab(): void
    {
        $this->closeEditor();
        $this->resetPage();
    }

    public function updatedBookingStatus(): void
    {
        $this->resetPage();
    }

    public function logout()
    {
        session()->forget('admin');

        return $this->redirectRoute('home');
    }

    // ── Заявки ────────────────────────────────────────────────

    public function setBookingStatus(int $id, string $status): void
    {
        abort_unless(array_key_exists($status, Booking::STATUSES), 422);
        Booking::whereKey($id)->update(['status' => $status]);
    }

    public function deleteBooking(int $id): void
    {
        Booking::whereKey($id)->delete();
    }

    // ── Публикации ────────────────────────────────────────────

    public function editPost(int $id = 0): void
    {
        $p = $id ? Post::findOrFail($id) : new Post(['kind' => 'photo', 'category' => 'tattoo', 'is_visible' => true]);
        $this->editing = $id;
        $this->uploads = [];
        $this->coverUpload = null;
        $this->form = [
            'instagram' => (string) $p->instagramUrl(),
            'kind' => $p->kind,
            'category' => $p->category,
            'caption' => (string) $p->caption,
            'cover' => (string) $p->cover,
            'images' => $p->images ?? [],
            'video_url' => (string) $p->video_url,
            'views' => (int) $p->views,
            'likes' => (int) $p->likes,
            'taken_at' => $p->taken_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'sort' => (int) $p->sort,
            'is_visible' => (bool) $p->is_visible,
            'is_featured' => (bool) $p->is_featured,
        ];
    }

    public function updatedUploads(): void
    {
        $this->validate(['uploads.*' => 'image|max:12288']);
        foreach ($this->uploads as $file) {
            $this->form['images'][] = Media::storeUpload($file)->ref();
        }
        $this->uploads = [];
        if (! $this->form['cover'] && $this->form['images']) {
            $this->form['cover'] = $this->form['images'][0];
        }
    }

    public function updatedCoverUpload(): void
    {
        $this->validate(['coverUpload' => 'image|max:12288']);
        $this->form['cover'] = Media::storeUpload($this->coverUpload)->ref();
        $this->coverUpload = null;
    }

    public function removeImage(int $index): void
    {
        unset($this->form['images'][$index]);
        $this->form['images'] = array_values($this->form['images']);
    }

    public function savePost(): void
    {
        $this->validate([
            'form.instagram' => ['nullable', 'string', 'max:255', 'regex:#instagram\.com/(p|reel|reels|tv)/[\w-]+#'],
            'form.kind' => 'required|in:photo,reel,carousel',
            'form.category' => 'required|in:'.implode(',', array_keys(Post::CATEGORIES)),
            'form.caption' => 'nullable|string|max:5000',
            'form.video_url' => 'nullable|url|max:500',
            'form.views' => 'nullable|integer|min:0',
            'form.likes' => 'nullable|integer|min:0',
            'form.taken_at' => 'nullable|date',
            'form.sort' => 'nullable|integer',
        ], ['form.instagram.regex' => 'Нужна ссылка вида https://www.instagram.com/p/… или /reel/…']);

        $shortcode = null;
        if (preg_match('#instagram\.com/(p|reel|reels|tv)/([\w-]+)#', (string) $this->form['instagram'], $m)) {
            $shortcode = $m[2];
        }
        if (! $shortcode && ! $this->form['cover'] && ! $this->form['images']) {
            $this->addError('form.cover', 'Добавьте фото или ссылку на публикацию в Instagram.');

            return;
        }

        $data = [
            'shortcode' => $shortcode,
            'kind' => $this->form['kind'],
            'category' => $this->form['category'],
            'caption' => trim((string) $this->form['caption']) ?: null,
            'cover' => $this->form['cover'] ?: null,
            'images' => $this->form['images'] ?: null,
            'video_url' => $this->form['video_url'] ?: null,
            'views' => (int) $this->form['views'],
            'likes' => (int) $this->form['likes'],
            'taken_at' => $this->form['taken_at'] ?: null,
            'sort' => (int) $this->form['sort'],
            'is_visible' => (bool) $this->form['is_visible'],
            'is_featured' => (bool) $this->form['is_featured'],
        ];

        $dupe = $shortcode ? Post::where('shortcode', $shortcode)->where('id', '!=', $this->editing ?: 0)->exists() : false;
        if ($dupe) {
            $this->addError('form.instagram', 'Эта публикация уже есть на сайте.');

            return;
        }

        $this->editing ? Post::whereKey($this->editing)->update(array_merge($data, ['images' => $data['images'] ? json_encode($data['images']) : null])) : Post::create($data);
        $this->notice = 'Публикация сохранена.';
        $this->closeEditor();
    }

    public function togglePost(int $id, string $field): void
    {
        abort_unless(in_array($field, ['is_visible', 'is_featured'], true), 422);
        $post = Post::findOrFail($id);
        $post->update([$field => ! $post->{$field}]);
    }

    public function deletePost(int $id): void
    {
        Post::whereKey($id)->delete();
        $this->closeEditor();
    }

    // ── Актуальные ────────────────────────────────────────────

    public function editHighlight(int $id = 0): void
    {
        $h = $id ? Highlight::findOrFail($id) : new Highlight(['is_visible' => true]);
        $this->editing = $id;
        $this->uploads = [];
        $this->coverUpload = null;
        $this->form = [
            'title' => (string) $h->title,
            'cover' => (string) $h->cover,
            'items' => $h->items ?? [],
            'video' => '',
            'sort' => (int) $h->sort,
            'is_visible' => (bool) $h->is_visible,
        ];
    }

    public function addHighlightImages(): void
    {
        $this->validate(['uploads.*' => 'image|max:12288']);
        foreach ($this->uploads as $file) {
            $this->form['items'][] = ['type' => 'image', 'src' => Media::storeUpload($file)->ref()];
        }
        $this->uploads = [];
    }

    public function addHighlightVideo(): void
    {
        $this->validate(['form.video' => 'required|url|max:500']);
        $this->form['items'][] = ['type' => 'video', 'src' => $this->form['video']];
        $this->form['video'] = '';
    }

    public function removeItem(int $index): void
    {
        unset($this->form['items'][$index]);
        $this->form['items'] = array_values($this->form['items']);
    }

    public function moveItem(int $index, int $dir): void
    {
        $to = $index + $dir;
        if (isset($this->form['items'][$index], $this->form['items'][$to])) {
            [$this->form['items'][$index], $this->form['items'][$to]] = [$this->form['items'][$to], $this->form['items'][$index]];
        }
    }

    public function saveHighlight(): void
    {
        $this->validate(['form.title' => 'required|string|max:40', 'form.sort' => 'nullable|integer']);
        $data = [
            'title' => trim($this->form['title']),
            'cover' => $this->form['cover'] ?: null,
            'items' => array_values($this->form['items']),
            'sort' => (int) $this->form['sort'],
            'is_visible' => (bool) $this->form['is_visible'],
        ];
        $this->editing ? Highlight::findOrFail($this->editing)->update($data) : Highlight::create($data);
        $this->notice = 'Актуальное сохранено.';
        $this->closeEditor();
    }

    public function deleteHighlight(int $id): void
    {
        Highlight::whereKey($id)->delete();
        $this->closeEditor();
    }

    public function closeEditor(): void
    {
        $this->editing = null;
        $this->form = [];
        $this->uploads = [];
        $this->resetValidation();
    }

    // ── Профиль ───────────────────────────────────────────────

    public function updatedAvatarUpload(): void
    {
        $this->validate(['avatarUpload' => 'image|max:12288']);
        Setting::put('avatar', Media::storeUpload($this->avatarUpload)->ref());
        $this->avatarUpload = null;
        $this->notice = 'Фото профиля обновлено.';
    }

    public function updatedHeroUpload(): void
    {
        $this->validate(['heroUpload' => 'image|max:12288']);
        Setting::put('hero', Media::storeUpload($this->heroUpload)->ref());
        $this->heroUpload = null;
        $this->notice = 'Обложка обновлена.';
    }

    public function saveSettings(): void
    {
        $this->validate([
            'settings.name' => 'required|string|max:80',
            'settings.instagram' => 'nullable|url|max:255',
            'settings.telegram' => 'nullable|url|max:255',
            'settings.brand_instagram' => 'nullable|url|max:255',
            'settings.telegram_bot_token' => ['nullable', 'string', 'max:100', 'regex:/^\d+:[\w-]+$/'],
            'settings.telegram_chat_id' => 'nullable|string|max:40',
        ], ['settings.telegram_bot_token.regex' => 'Токен выглядит как 123456789:ABC-def…']);

        foreach ($this->settings as $key => $value) {
            Setting::put($key, trim((string) $value));
        }
        $this->notice = 'Настройки сохранены.';
    }

    public function testTelegram(): void
    {
        $this->saveSettings();
        $this->notice = Telegram::notify('🦈 Проверка: заявки с сайта будут приходить сюда.')
            ? 'Тестовое сообщение отправлено в Telegram.'
            : 'Не получилось: проверьте токен бота и chat id (сначала напишите боту /start).';
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'bookings' => $this->tab === 'bookings'
                ? Booking::when($this->bookingStatus, fn ($q) => $q->where('status', $this->bookingStatus))->latest()->paginate(20)
                : null,
            'newBookings' => Booking::where('status', 'new')->count(),
            'posts' => $this->tab === 'posts' ? Post::ordered()->paginate(24) : null,
            'highlights' => $this->tab === 'highlights' ? Highlight::orderBy('sort')->get() : null,
            'avatar' => Media::url(Setting::get('avatar')),
            'hero' => Media::url(Setting::get('hero')),
        ])->title('Админка');
    }
}
