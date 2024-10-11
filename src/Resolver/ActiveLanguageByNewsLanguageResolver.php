<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Resolver;

use Contao\DataContainer;
use Contao\NewsArchiveModel;
use Contao\NewsModel;

class ActiveLanguageByNewsLanguageResolver implements ActiveLanguageResolverInterface
{
    public function supports(DataContainer $dataContainer): bool
    {
        if ($dataContainer->table === NewsModel::getTable() && \Input::get('do') === 'news') {
            return true;
        }

        return false;
    }

    public function resolve(DataContainer $dataContainer): ?string
    {
        $news = NewsModel::findOneBy('id', (int) \Input::get('id'));

        if ($news) {
            $newsArchive = NewsArchiveModel::findOneBy('id', (int) $news->pid);

            return $newsArchive->language ?? null;
        }

        return null;
    }

}
