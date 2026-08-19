<?php

namespace BlackpigCreatif\Atelier\Plugins;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;

class InternalLinkPlugin implements RichContentPlugin
{
    public function __construct(protected Closure $searchCallback) {}

    public static function make(Closure $searchCallback): static
    {
        return new static($searchCallback);
    }

    /**
     * @return array<\Tiptap\Core\Extension>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [];
    }

    /**
     * @return array<\Filament\Forms\Components\RichEditor\RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        $searchCallback = $this->searchCallback;

        return [
            Action::make('link')
                ->label(__('filament-forms::components.rich_editor.actions.link.label'))
                ->modalHeading(__('filament-forms::components.rich_editor.actions.link.modal.heading'))
                ->modalWidth(Width::Large)
                ->fillForm(function (array $arguments): array {
                    $url = $arguments['url'] ?? null;

                    // Determine link type from existing URL
                    $linkType = 'external';
                    if (filled($url) && ! str_starts_with($url, 'http') && ! str_starts_with($url, 'mailto:') && ! str_starts_with($url, 'tel:')) {
                        $linkType = 'internal';
                    }

                    return [
                        'linkType' => $linkType,
                        'internalPage' => $linkType === 'internal' ? $url : null,
                        'url' => $url,
                        'shouldOpenInNewTab' => $arguments['shouldOpenInNewTab'] ?? false,
                    ];
                })
                ->schema([
                    Radio::make('linkType')
                        ->label('Link to')
                        ->options([
                            'internal' => 'Internal page',
                            'external' => 'External website',
                        ])
                        ->default('external')
                        ->inline()
                        ->live()
                        ->afterStateUpdated(function (Set $set): void {
                            $set('url', null);
                            $set('internalPage', null);
                        }),

                    Select::make('internalPage')
                        ->label('Search pages')
                        ->placeholder('Type a page name to search...')
                        ->searchable()
                        ->getSearchResultsUsing($searchCallback)
                        ->getOptionLabelUsing(fn (?string $value): ?string => $value)
                        ->live()
                        ->afterStateUpdated(function (?string $state, Set $set): void {
                            if (filled($state)) {
                                $set('url', $state);
                            }
                        })
                        ->visible(fn (Get $get): bool => $get('linkType') === 'internal'),

                    TextInput::make('url')
                        ->label(__('filament-forms::components.rich_editor.actions.link.modal.form.url.label'))
                        ->inputMode('url')
                        ->visible(fn (Get $get): bool => $get('linkType') === 'external'),

                    Checkbox::make('shouldOpenInNewTab')
                        ->label(__('filament-forms::components.rich_editor.actions.link.modal.form.should_open_in_new_tab.label')),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $url = match ($data['linkType'] ?? 'external') {
                        'internal' => $data['internalPage'] ?? null,
                        default => $data['url'] ?? null,
                    };

                    $isSingleCharacterSelection = ($arguments['editorSelection']['head'] ?? null) === ($arguments['editorSelection']['anchor'] ?? null);

                    if (blank($url)) {
                        $component->runCommands(
                            [
                                ...($isSingleCharacterSelection ? [EditorCommand::make(
                                    'extendMarkRange',
                                    arguments: ['link'],
                                )] : []),
                                EditorCommand::make('unsetLink'),
                            ],
                            editorSelection: $arguments['editorSelection'],
                        );

                        return;
                    }

                    $component->runCommands(
                        [
                            ...($isSingleCharacterSelection ? [EditorCommand::make(
                                'extendMarkRange',
                                arguments: ['link'],
                            )] : []),
                            EditorCommand::make(
                                'setLink',
                                arguments: [[
                                    'href' => $url,
                                    'target' => $data['shouldOpenInNewTab'] ? '_blank' : null,
                                ]],
                            ),
                        ],
                        editorSelection: $arguments['editorSelection'],
                    );
                }),
        ];
    }
}
