<x-filament-panels::page>
    <form wire:submit="resolve" class="fi-form">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                {{ __('skriptdepot.resolve.submit') }}
            </x-filament::button>
        </div>
    </form>

    @if ($searched)
        <x-filament::section :heading="__('skriptdepot.resolve.results')">
            @forelse ($matches as $match)
                <div @class(['border-t pt-4 mt-4' => ! $loop->first])>
                    <p class="text-lg font-semibold">{{ $match['user'] }} &lt;{{ $match['email'] }}&gt;</p>
                    <p class="mt-1">
                        {{ __('skriptdepot.fields.script') }}: <strong>{{ $match['script'] }}</strong>
                        @if ($match['version'])
                            &middot; {{ __('skriptdepot.portal.version', ['version' => $match['version']]) }}
                        @endif
                    </p>
                    <p class="mt-1 text-sm">
                        {{ __('skriptdepot.resolve.identifier') }}: <code>{{ $match['identifier'] }}</code>
                        &middot; {{ __('skriptdepot.resolve.method') }}: {{ $match['method'] }}
                    </p>
                    <p class="mt-1 text-sm">
                        <x-filament::badge :color="$match['is_active'] ? 'success' : 'danger'">
                            {{ $match['is_active'] ? __('skriptdepot.resolve.active') : __('skriptdepot.resolve.inactive') }}
                        </x-filament::badge>
                    </p>
                </div>
            @empty
                <p>{{ __('skriptdepot.resolve.no_match') }}</p>
            @endforelse
        </x-filament::section>
    @endif
</x-filament-panels::page>
