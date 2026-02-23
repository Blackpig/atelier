<?php

declare(strict_types=1);

namespace BlackpigCreatif\Atelier\Filament\Pages;

use BlackpigCreatif\Atelier\Filament\Clusters\AtelierDocumentationCluster;
use BlackpigCreatif\Grimoire\Filament\Pages\GrimoireChapterPage;

/**
 * Built-in Chapter Page: Atelier user docs — editing a block.
 */
final class AtelierDocumentationEditingABlockPage extends GrimoireChapterPage
{
    public static string $tomeId = 'atelier';

    public static string $chapterSlug = 'editing-a-block';

    protected static ?string $cluster = AtelierDocumentationCluster::class;
}
