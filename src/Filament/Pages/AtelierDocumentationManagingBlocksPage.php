<?php

declare(strict_types=1);

namespace BlackpigCreatif\Atelier\Filament\Pages;

use BlackpigCreatif\Atelier\Filament\Clusters\AtelierDocumentationCluster;
use BlackpigCreatif\Grimoire\Filament\Pages\GrimoireChapterPage;

/**
 * Built-in Chapter Page: Atelier user docs — managing blocks.
 */
final class AtelierDocumentationManagingBlocksPage extends GrimoireChapterPage
{
    public static string $tomeId = 'atelier';

    public static string $chapterSlug = 'managing-blocks';

    protected static ?string $cluster = AtelierDocumentationCluster::class;
}
