<?php

namespace App\Livewire;

use App\Models\Highlight;
use App\Models\Post;
use App\Models\Setting;
use Livewire\Attributes\Url;
use Livewire\Component;

class Home extends Component
{
    #[Url(as: 'works', except: 'all')]
    public string $filter = 'all';

    public int $limit = 12;

    public function setFilter(string $filter): void
    {
        $this->filter = array_key_exists($filter, Post::CATEGORIES) ? $filter : 'all';
        $this->limit = 12;
    }

    public function more(): void
    {
        $this->limit += 12;
    }

    public function render()
    {
        $query = Post::visible()->when($this->filter !== 'all', fn ($q) => $q->where('category', $this->filter));
        $counts = Post::visible()->selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category');

        return view('livewire.home', [
            'posts' => (clone $query)->ordered()->take($this->limit)->get(),
            'total' => $query->count(),
            'counts' => $counts,
            'highlights' => Highlight::where('is_visible', true)->orderBy('sort')->get(),
            's' => fn (string $k, $d = null) => Setting::get($k, $d),
        ])->title(Setting::get('name', 'Shark Tattoo').' — fine line тату в Ереване');
    }
}
