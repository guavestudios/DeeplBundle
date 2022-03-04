<?php

declare(strict_types=1);

/*
 * This file is part of [package name].
 *
 * (c) John Doe
 *
 * @license LGPL-3.0-or-later
 */

namespace Guave\DeeplBundle;

use Guave\DeeplBundle\DependencyInjection\GuaveDeeplExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GuaveDeeplBundle extends Bundle
{
    public function getContainerExtension(): Extension
    {
        return new GuaveDeeplExtension();
    }
}
