<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Resolver;

use Contao\DataContainer;
use Contao\Input;
use Contao\NewsArchiveModel;

class ActiveLanguageByNewsArchiveLanguageResolver implements ActiveLanguageResolverInterface
{
    public function supports(DataContainer $dataContainer): bool
    {
        return $dataContainer->table === NewsArchiveModel::getTable() && Input::get('do') === 'news' && Input::get('act') === 'edit';
    }

    public function resolve(DataContainer $dataContainer): ?string
    {
        $newsArchive = NewsArchiveModel::findOneBy('id', (int)Input::get('id'));

        return $newsArchive->language ?? null;
    }

}
