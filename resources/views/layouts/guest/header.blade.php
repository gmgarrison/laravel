<flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <x-app-logo href="{{ route('home') }}" />

    <flux:spacer />

    <flux:navbar class="space-x-0.5 rtl:space-x-reverse py-0!">
        @auth
            <flux:navbar.item :href="route('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:navbar.item>
        @else
            <flux:navbar.item :href="route('login')" wire:navigate>
                {{ __('Log in') }}
            </flux:navbar.item>

            @if (Route::has('register'))
                <flux:navbar.item :href="route('register')" wire:navigate>
                    {{ __('Register') }}
                </flux:navbar.item>
            @endif
        @endauth
    </flux:navbar>
</flux:header>
