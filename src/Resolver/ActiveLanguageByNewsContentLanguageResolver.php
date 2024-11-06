<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Resolver;

use Contao\ContentModel;
use Contao\DataContainer;
use Contao\Input;
use Contao\NewsArchiveModel;
use Contao\NewsModel;

class ActiveLanguageByNewsContentLanguageResolver implements ActiveLanguageResolverInterface
{
    public function supports(DataContainer $dataContainer): bool
    {
        return $dataContainer->table === ContentModel::getTable() && Input::get('do') === 'news';
    }

    public function resolve(DataContainer $dataContainer): ?string
    {
        $content = ContentModel::findOneBy('id', (int)Input::get('id'));

        $news = NewsModel::findOneBy('id', (int)$content->pid);

        $newsArchive = NewsArchiveModel::findOneBy('id', (int)$news->pid);

        return $newsArchive->language ?? null;
    }
}
