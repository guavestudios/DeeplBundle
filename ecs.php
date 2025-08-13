<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitExpectationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([__DIR__.'/tools/ecs/vendor/contao/easy-coding-standard/config/contao.php']);
    $ecsConfig->ruleWithConfiguration(GlobalNamespaceImportFixer::class, [
        'import_classes' => true,
        'import_constants' => false,
        'import_functions' => false,
    ]);
    $ecsConfig->ruleWithConfiguration(YodaStyleFixer::class, [
        'equal' => false,
        'identical' => false,
        'less_and_greater' => false,
    ]);

    if (PHP_VERSION_ID < 80000) {
        $ecsConfig->ruleWithConfiguration(TrailingCommaInMultilineFixer::class, [
            'elements' => ['arrays'],
            'after_heredoc' => true,
        ]);
        $ecsConfig->skip([PhpUnitExpectationFixer::class]);
    }
};
