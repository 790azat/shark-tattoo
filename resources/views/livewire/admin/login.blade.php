<div class="grid min-h-screen place-items-center px-4">
    <form wire:submit="login" class="card w-full max-w-sm space-y-4 p-8">
        <x-logo class="h-8 w-auto text-bone" />
        <h1 class="text-4xl text-bone">Админка</h1>
        <input wire:model="password" type="password" class="input" placeholder="Пароль" autofocus>
        @error('password') <p class="text-sm text-blood">{{ $message }}</p> @enderror
        <button class="btn-light w-full">Войти</button>
        <a href="{{ route('home') }}" class="block text-center text-sm text-fin hover:text-bone">← На сайт</a>
    </form>
</div>
