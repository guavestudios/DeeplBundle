<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Resolver;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\DataContainer;
use Contao\PageModel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActiveLanguageByPageOrArticleOrArticleContentResolver implements ActiveLanguageResolverInterface
{
    public function supports(DataContainer $dataContainer): bool
    {
        return ($dataContainer->table === ContentModel::getTable()
                && $dataContainer->parentTable === ArticleModel::getTable())
            || \in_array($dataContainer->table, [ArticleModel::getTable(), PageModel::getTable()], true);
    }

    public function resolve(DataContainer $dataContainer): string|null
    {
        $language = null;

        if ($dataContainer->table === ContentModel::getTable() && $dataContainer->parentTable === ArticleModel::getTable()) {
            $content = ContentModel::findOneBy('id', $dataContainer->id);
            $language = $this->getRootLanguageFromArticle((int) $content->pid);
        } elseif ($dataContainer->table === ArticleModel::getTable()) {
            $article = ArticleModel::findOneBy('id', $dataContainer->id);
            $language = $this->getRootLanguageFromPage((int) $article->pid);
        } elseif ($dataContainer->table === PageModel::getTable()) {
            $page = PageModel::findOneBy('id', $dataContainer->id);
            $language = $this->getRootLanguageFromPage((int) $page->id);
        }

        return $language;
    }

    protected function getRootLanguageFromArticle(int $id): string
    {
        $article = ArticleModel::findOneBy('id', $id);

        if (!$article) {
            throw new NotFoundHttpException(sprintf('page with id %s not found', $id));
        }

        return $this->getRootLanguageFromPage((int) $article->pid);
    }

    protected function getRootLanguageFromPage(int $id): string
    {
        $page = PageModel::findOneBy('id', $id);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('page with id %s not found', $id));
        }
        $page->loadDetails();

        return $page->rootLanguage;
    }
}
