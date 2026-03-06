<x-layouts::guest>
    <div class="flex flex-1 items-center justify-center">
        <div class="text-center">
            <x-app-logo-icon class="mx-auto size-12 text-zinc-900 dark:text-white" />

            <flux:heading size="xl" level="1" class="mt-6">
                {{ config('app.name', 'Laravel') }}
            </flux:heading>

            <flux:text class="mt-2 text-base">
                {{ __('Welcome to your application. Please log in or register to get started.') }}
            </flux:text>

            @guest
                <div class="mt-6 flex items-center justify-center gap-3">
                    <flux:button :href="route('login')" variant="primary" wire:navigate>
                        {{ __('Log in') }}
                    </flux:button>

                    @if (Route::has('register'))
                        <flux:button :href="route('register')" variant="ghost" wire:navigate>
                            {{ __('Register') }}
                        </flux:button>
                    @endif
                </div>
            @endguest
        </div>
    </div>
</x-layouts::guest>
