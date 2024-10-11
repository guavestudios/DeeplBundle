<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Resolver;

use Contao\ContentModel;
use Contao\DataContainer;
use Contao\NewsArchiveModel;
use Contao\NewsModel;

class ActiveLanguageByNewsContentLanguageResolver implements ActiveLanguageResolverInterface
{
    public function supports(DataContainer $dataContainer): bool
    {
        if ($dataContainer->table === ContentModel::getTable() && \Input::get('do') === 'news') {
            return true;
        }

        return false;
    }

    public function resolve(DataContainer $dataContainer): ?string
    {
        $content = ContentModel::findOneBy('id', (int) \Input::get('id'));

        $news = NewsModel::findOneBy('id', (int) $content->pid);

        $newsArchive = NewsArchiveModel::findOneBy('id', (int) $news->pid);

        return $newsArchive->language ?? null;
    }

}
