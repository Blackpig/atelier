<?php

declare(strict_types=1);

namespace BlackpigCreatif\Atelier\Filament\Pages;

use BlackpigCreatif\Atelier\Filament\Clusters\AtelierDocumentationCluster;
use BlackpigCreatif\Grimoire\Filament\Pages\GrimoireChapterPage;

/**
 * Built-in Chapter Page: Atelier user docs — display options.
 */
final class AtelierDocumentationDisplayOptionsPage extends GrimoireChapterPage
{
    public static string $tomeId = 'atelier';

    public static string $chapterSlug = 'display-options';

    protected static ?string $cluster = AtelierDocumentationCluster::class;
}
