@props(['cta', 'block', 'index' => 0])

@php
    $url = $cta['url'] ?? '#';
    $label = $block->getCallToActionLabel($cta);
    $target = $block->getCallToActionTarget($cta);
    $styleClass = $block->getCallToActionStyleClass($cta);
    $icon = $cta['icon'] ?? null;
    $isExternal = $block->isExternalUrl($url);
@endphp

<a
    href="{{ $url }}"
    target="{{ $target }}"
    class="{{ $styleClass }}"
    @if($isExternal) rel="noopener noreferrer" @endif
    {{ $attributes }}
>
    @if($icon)
        <x-filament::icon :icon="$icon" class="w-5 h-5" />
    @endif

    {{ $label }}
</a>
