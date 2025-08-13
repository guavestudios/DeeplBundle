<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Resolver;

use Contao\DataContainer;

interface ActiveLanguageResolverInterface
{
    public function supports(DataContainer $dataContainer): bool;

    public function resolve(DataContainer $dataContainer): string|null;
}
