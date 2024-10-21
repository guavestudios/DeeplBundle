<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Tests;

use Guave\DeeplBundle\GuaveDeeplBundle;
use PHPUnit\Framework\TestCase;

class GuaveDeeplBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new GuaveDeeplBundle();

        $this->assertInstanceOf('Guave\DeeplBundle\GuaveDeeplBundle', $bundle);
    }
}
