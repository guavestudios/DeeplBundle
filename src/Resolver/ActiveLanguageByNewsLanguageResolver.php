<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Resolver;

use Contao\DataContainer;
use Contao\Input;
use Contao\NewsArchiveModel;
use Contao\NewsModel;

class ActiveLanguageByNewsLanguageResolver implements ActiveLanguageResolverInterface
{
    public function supports(DataContainer $dataContainer): bool
    {
        return $dataContainer->table === NewsModel::getTable() && Input::get('do') === 'news';
    }

    public function resolve(DataContainer $dataContainer): string|null
    {
        $news = NewsModel::findOneBy('id', (int) Input::get('id'));

        if ($news) {
            $newsArchive = NewsArchiveModel::findOneBy('id', (int) $news->pid);

            return $newsArchive->language ?? null;
        }

        return null;
    }
}
