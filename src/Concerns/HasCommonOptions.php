<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

trait HasCommonOptions
{
    use HasBackground, HasDivider, HasSpacing, HasWidth;

    /**
     * Get the Published toggle field (appears first in schema)
     */
    public static function getPublishedField(): Toggle
    {
        return Toggle::make('is_published')
            ->label('Published')
            ->helperText('Unpublished blocks will not appear on the frontend')
            ->default(true)
            ->inline(false);
    }

    /**
     * Get the Fragment ID field for scroll-to navigation.
     */
    public static function getFragmentIdField(): TextInput
    {
        return TextInput::make('fragment_id')
            ->label('Fragment ID')
            ->helperText('Assigns an anchor to this section (e.g. "about" → #about). Used for scroll navigation.')
            ->prefix('#')
            ->maxLength(100)
            ->nullable();
    }

    /**
     * Returns the top-level header fields: Published toggle, and Fragment ID
     * when scroll navigation is enabled. Replaces a bare getPublishedField()
     * call in each block's getSchema().
     */
    public static function getHeaderFields(): array
    {
        $fields = [static::getPublishedField()];

        if (config('atelier.features.scroll_navigation.enabled')) {
            $fields[] = static::getFragmentIdField();
        }

        return $fields;
    }

    /**
     * Get the fragment ID value for this block instance.
     */
    public function getFragmentId(): ?string
    {
        $value = $this->get('fragment_id');

        return ($value !== null && $value !== '') ? (string) $value : null;
    }

    /**
     * Get common display options (appears last in schema)
     */
    public static function getCommonOptionsSchema(): array
    {
        $displayFields = [];

        if (config('atelier.features.backgrounds.enabled')) {
            $displayFields[] = static::getBackgroundField();
        }

        if (config('atelier.features.spacing.enabled')) {
            $spacingFields = static::getSpacingField();
            // Handle both single field and array of fields
            if (is_array($spacingFields)) {
                $displayFields = array_merge($displayFields, $spacingFields);
            } else {
                $displayFields[] = $spacingFields;
            }
        }

        if (config('atelier.features.width.enabled')) {
            $displayFields[] = static::getWidthField();
        }

        if (config('atelier.features.dividers.enabled')) {
            $dividerFields = static::getDividerField();
            // Handle both single field and array of fields
            if (is_array($dividerFields)) {
                $displayFields = array_merge($displayFields, $dividerFields);
            } else {
                $displayFields[] = $dividerFields;
            }
        }

        if (! empty($displayFields)) {
            return [
                Section::make('Display Options')
                    ->schema($displayFields)
                    ->columns(3)
                    ->collapsed()
                    ->collapsible()
            ];
        }

        return [];
    }

    public function getWrapperClasses(): string
    {
        $classes = [];

        // Add relative positioning if block has a divider
        if (method_exists($this, 'hasDivider') && $this->hasDivider()) {
            $classes[] = 'relative';
        }

        if (method_exists($this, 'getBackgroundClass')) {
            $classes[] = $this->getBackgroundClass();
        }

        if (method_exists($this, 'getSpacingClass')) {
            $classes[] = $this->getSpacingClass();
        }

        return implode(' ', array_filter($classes));
    }

    public function getContainerClasses(): string
    {
        if (method_exists($this, 'getWidthClass')) {
            return $this->getWidthClass();
        }

        return '';
    }
}
