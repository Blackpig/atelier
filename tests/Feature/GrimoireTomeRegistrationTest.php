<?php

use BlackpigCreatif\Atelier\AtelierPlugin;
use BlackpigCreatif\Atelier\Filament\Clusters\AtelierDocumentationCluster;
use BlackpigCreatif\Grimoire\Services\TomeRegistry;
use Filament\Panel;

it('registers the Atelier documentation Tome when the package boots', function () {
    expect(app(TomeRegistry::class)->find('atelier')?->clusterClass)->toBe(AtelierDocumentationCluster::class);
});

it('can be added to several panels without registering the Tome twice', function () {
    $first = Panel::make()->id('first')->plugin(AtelierPlugin::make());
    $second = Panel::make()->id('second')->plugin(AtelierPlugin::make());

    expect($first->getPages())->toContain(AtelierDocumentationCluster::class)
        ->and($second->getPages())->toContain(AtelierDocumentationCluster::class);
});
