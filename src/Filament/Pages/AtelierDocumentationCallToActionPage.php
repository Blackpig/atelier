<?php

declare(strict_types=1);

namespace BlackpigCreatif\Atelier\Filament\Pages;

use BlackpigCreatif\Atelier\Filament\Clusters\AtelierDocumentationCluster;
use BlackpigCreatif\Grimoire\Filament\Pages\GrimoireChapterPage;

/**
 * Built-in Chapter Page: Atelier user docs — call to action buttons.
 */
final class AtelierDocumentationCallToActionPage extends GrimoireChapterPage
{
    public static string $tomeId = 'atelier';

    public static string $chapterSlug = 'call-to-action';

    protected static ?string $cluster = AtelierDocumentationCluster::class;
}
