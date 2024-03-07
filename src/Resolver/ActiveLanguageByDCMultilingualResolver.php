<?php

namespace Guave\DeeplBundle\Resolver;

use Contao\DataContainer;
use DC_Multilingual;
use Symfony\Component\HttpFoundation\RequestStack;

class ActiveLanguageByDCMultilingualResolver implements ActiveLanguageResolverInterface
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function supports(DataContainer $dataContainer): bool
    {
        return $dataContainer instanceof DC_Multilingual;
    }

    public function resolve(DataContainer $dataContainer): ?string
    {
        $objSessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $sessionKey = 'dc_multilingual:' . $dataContainer->table . ':' . $dataContainer->id;
        if ($objSessionBag->get($sessionKey)) {
            return $objSessionBag->get($sessionKey);
        }

        return null;
    }
}
