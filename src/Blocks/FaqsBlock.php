<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Sceau\Enums\SchemaType;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class FaqsBlock extends BaseBlock
{
    public static function getLabel(): string
    {
        return 'FAQs';
    }

    public static function getDescription(): ?string
    {
        return 'A list of frequently asked questions with answers. Automatically generates FAQPage schema markup for search engines.';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-question-mark-circle';
    }

    public static function getSchema(): array
    {
        return [
            static::getPublishedField(),

            Section::make('Questions & Answers')
                ->schema([
                    Repeater::make('faqs')
                        ->label('FAQ Items')
                        ->schema([
                            TextInput::make('question')
                                ->label('Question')
                                ->required()
                                ->maxLength(500)
                                ->columnSpanFull(),

                            Textarea::make('answer')
                                ->label('Answer')
                                ->required()
                                ->rows(3)
                                ->maxLength(2000)
                                ->columnSpanFull(),
                        ])
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (?array $state): ?string => $state['question'] ?? null)
                        ->addActionLabel('Add FAQ Item')
                        ->columnSpanFull()
                        ->defaultItems(1),
                ])
                ->collapsible(),

            ...static::getCommonOptionsSchema(),
        ];
    }

    public function render(): View
    {
        return view(static::getViewPath(), $this->getViewData());
    }

    /**
     * Return FAQPage schema type when there is at least one complete Q&A pair.
     */
    public function getSchemaType(): ?SchemaType
    {
        if (empty($this->getValidFaqPairs())) {
            return null;
        }

        return SchemaType::FAQPage;
    }

    /**
     * @return array{faqs: array<int, array{question: string, answer: string}>}
     */
    public function getSchemaData(): array
    {
        return [
            'faqs' => $this->getValidFaqPairs(),
        ];
    }

    /**
     * Return only complete Q&A pairs with non-empty question and answer.
     *
     * @return array<int, array{question: string, answer: string}>
     */
    protected function getValidFaqPairs(): array
    {
        return array_values(
            array_filter(
                $this->get('faqs', []),
                fn (array $item): bool => ! empty($item['question']) && ! empty($item['answer'])
            )
        );
    }
}
