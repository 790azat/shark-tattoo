<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = [];

    public const CATEGORIES = [
        'tattoo' => 'Тату',
        'piercing' => 'Пирсинг',
        'studio' => 'Студия',
        'life' => 'Лайф',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'taken_at' => 'datetime',
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_visible', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderByDesc('is_featured')->orderBy('sort')->orderByDesc('taken_at')->orderByDesc('id');
    }

    public function coverUrl(): ?string
    {
        return Media::url($this->cover ?: ($this->images[0] ?? null));
    }

    /** @return list<string> */
    public function imageUrls(): array
    {
        $list = $this->images ?: ($this->cover ? [$this->cover] : []);

        return array_values(array_filter(array_map([Media::class, 'url'], $list)));
    }

    public function instagramUrl(): ?string
    {
        if (! $this->shortcode) {
            return null;
        }

        return 'https://www.instagram.com/'.($this->kind === 'reel' ? 'reel' : 'p').'/'.$this->shortcode.'/';
    }

    /** Ссылка на официальный плеер Instagram — видео играет прямо на сайте. */
    public function embedUrl(): ?string
    {
        return $this->instagramUrl() ? $this->instagramUrl().'embed/' : null;
    }
}
