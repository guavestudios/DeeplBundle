<?php

namespace Guave\DeeplBundle\Resolver;

use Contao\DataContainer;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ActiveLanguageByDCMultilingualResolver implements ActiveLanguageResolverInterface
{
    protected SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function supports(DataContainer $dataContainer): bool
    {
        if ($dataContainer instanceof \DC_Multilingual) {
            return true;
        }

        return false;
    }

    public function resolve(DataContainer $dataContainer): ?string
    {
        $objSessionBag = $this->session->getBag('contao_backend');
        $sessionKey = 'dc_multilingual:' . $dataContainer->table . ':' . $dataContainer->id;
        if ($objSessionBag->get($sessionKey)) {
            return $objSessionBag->get($sessionKey);
        }

        return null;
    }
}
