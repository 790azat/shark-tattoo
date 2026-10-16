<div class="card p-6 sm:p-10">
    @if ($sent)
        <div class="py-10 text-center">
            <x-logo class="mx-auto h-10 w-auto text-bone" />
            <h3 class="mt-6 text-4xl text-bone">Заявка отправлена</h3>
            <p class="mt-3 text-fin">Скоро свяжемся с тобой, чтобы обсудить эскиз и время.</p>
            <button wire:click="$set('sent', false)" class="btn-line mt-8">Отправить ещё одну</button>
        </div>
    @else
        <form wire:submit="submit" class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <span class="label">Услуга</span>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\Booking::SERVICES as $key => $label)
                        <label @class(['chip cursor-pointer', 'border-bone bg-bone text-ink' => $service === $key, 'border-foam/15 text-fin hover:text-bone' => $service !== $key])>
                            <input type="radio" wire:model.live="service" value="{{ $key }}" class="sr-only"> {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="label" for="b-name">Имя</label>
                <input id="b-name" wire:model="name" class="input" autocomplete="name">
                @error('name') <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="b-contact">Телефон / Telegram / Instagram</label>
                <input id="b-contact" wire:model="contact" class="input" placeholder="+374 … или @ник">
                @error('contact') <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="b-place">Место на теле</label>
                <input id="b-place" wire:model="placement" class="input" placeholder="Предплечье, ключица…">
            </div>
            <div>
                <label class="label" for="b-size">Размер</label>
                <input id="b-size" wire:model="size" class="input" placeholder="Примерно 5 см">
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="b-idea">Идея</label>
                <textarea id="b-idea" wire:model="idea" rows="4" class="input" placeholder="Опиши, что хочешь. Референсы можно прислать в Direct."></textarea>
                @error('idea') <p class="mt-1 text-sm text-blood">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2 flex flex-wrap items-center justify-between gap-4">
                <p class="text-xs text-fin">Отправляя заявку, ты соглашаешься, чтобы мы с тобой связались.</p>
                <button class="btn-light" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Отправить заявку</span>
                    <span wire:loading wire:target="submit">Отправляем…</span>
                </button>
            </div>
        </form>
    @endif
</div>
