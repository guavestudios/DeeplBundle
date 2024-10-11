<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Guave\DeeplBundle\Model\MultilingualModel;

#[AsCallback(table: 'tl_news_archive', target: 'fields.language.options')]
class NewsArchiveLanguageOptionsCallbackListener
{
    public function __invoke(DataContainer $dataContainer): array
    {
        return MultilingualModel::getRootPageLanguages();
    }
}
