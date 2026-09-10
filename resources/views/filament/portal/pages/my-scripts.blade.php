<x-filament-panels::page>
    @php($entitlements = $this->getEntitlements())

    @forelse ($entitlements as $entitlement)
        <x-filament::section :heading="$entitlement->script->name">
            @if ($entitlement->script->description)
                <p>{{ $entitlement->script->description }}</p>
            @endif

            @if ($entitlement->script->latestVersion)
                <p class="mt-2 text-sm">
                    {{ __('skriptdepot.portal.version', ['version' => $entitlement->script->latestVersion->version]) }}
                </p>

                <div class="mt-4">
                    <x-filament::button
                        tag="a"
                        :href="route('scripts.user', ['token' => $entitlement->token, 'slug' => $entitlement->script->slug])"
                        target="_blank"
                        icon="heroicon-o-arrow-down-tray"
                    >
                        {{ __('skriptdepot.portal.install') }}
                    </x-filament::button>
                </div>
            @else
                <p class="mt-2 text-sm">{{ __('skriptdepot.portal.no_version') }}</p>
            @endif
        </x-filament::section>
    @empty
        <x-filament::section>
            <p>{{ __('skriptdepot.portal.empty') }}</p>
        </x-filament::section>
    @endforelse

    <x-filament::section :heading="__('skriptdepot.portal.hint_title')" icon="heroicon-o-shield-check">
        <p>{{ __('skriptdepot.portal.hint') }}</p>
    </x-filament::section>
</x-filament-panels::page>
