<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Resolver;

use Contao\DataContainer;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\DcMultilingualBundle\Driver as Multilingual;

class ActiveLanguageByDCMultilingualResolver implements ActiveLanguageResolverInterface
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function supports(DataContainer $dataContainer): bool
    {
        return $dataContainer instanceof Multilingual;
    }

    public function resolve(DataContainer $dataContainer): string|null
    {
        $objSessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $sessionKey = 'dc_multilingual:'.$dataContainer->table.':'.$dataContainer->id;

        if ($objSessionBag->get($sessionKey)) {
            return $objSessionBag->get($sessionKey);
        }

        return null;
    }
}
