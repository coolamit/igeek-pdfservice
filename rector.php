<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

use function Igeek\RectorHtmlOutput\withHtmlOutput;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    $rectorConfig->phpVersion(80300);
    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();

    $rectorConfig->sets([
        LaravelLevelSetList::UP_TO_LARAVEL_110,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
    ]);

    $rectorConfig->rules([
        AddTypeToConstRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ]);

    $rectorConfig->ruleWithConfiguration(
        AddOverrideAttributeToOverriddenMethodsRector::class,
        ['allow_override_empty_method' => false]
    );

    // Register HTML output formatter
    withHtmlOutput(
        rectorConfig: $rectorConfig,
        outputDirectory: __DIR__.'/rector-reports/reports',
    );
};
