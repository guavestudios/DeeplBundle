<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\EventListener\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\NewsArchiveModel;

#[AsCallback(table: 'tl_news_archive', target: 'fields.langPid.options')]
class NewsArchiveLangPidOptionsCallbackListener
{
    public function __invoke(DataContainer $dataContainer): array
    {
        Controller::loadDataContainer(NewsArchiveModel::class);

        $array = [];

        $archives = NewsArchiveModel::findAll();
        if ($archives) {
            foreach ($archives as $a) {
                $array[$a->id] = $a->title . ' (' . $a->language . ')';
            }
        }

        return $array;
    }
}
