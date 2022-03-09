<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Controller\Backend;

use Contao\ArticleModel;
use Contao\Backend;
use Contao\ContentModel;
use Contao\Controller;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Image;
use Contao\PageModel;
use DC_Multilingual;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeeplButtons extends Backend
{
    protected string $defaultLanguage;

    protected array $tables;

    protected string $activeLang;

    protected SessionInterface $session;

    public function __construct(string $defaultLanguage, array $tables, SessionInterface $session)
    {
        $this->defaultLanguage = $defaultLanguage;
        $this->tables = $tables;
        $this->session = $session;

        parent::__construct();
    }

    public function registerDeepl(DataContainer $dc)
    {
        if (!$dc->id) {
            return;
        }

        $activeLang = $this->getActiveLang($dc);
        if ($activeLang === $this->defaultLanguage) {
            return;
        }

        Controller::loadLanguageFile('modules');

        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/guavedeepl/assets/translate.js';

        foreach ($this->tables[$dc->table]['fields'] as $field) {
            $GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['xlabel'][] = [self::class, 'translateButton'];
        }

        $GLOBALS['TL_DCA'][$dc->table]['edit']['buttons_callback'][] = [self::class, 'addTranslateAllButton'];
    }

    public function translateButton(DataContainer $dc)
    {
        $field = $dc->field;

        return $this->getTranslateButton($field);
    }

    public function addTranslateAllButton($arrButtons)
    {
        $arrButtons['translateAll'] = sprintf(
            '<button type="button" data-translate-all data-translate-target-lang="%s" class="tl_submit" accesskey="a">%s</button>',
            $this->activeLang,
            sprintf(
                $GLOBALS['TL_LANG']['guave_deepl']['translateAll'][0],
                $this->defaultLanguage,
                $this->activeLang,
                Image::getHtml('pasteinto.svg')
            )
        );

        return $arrButtons;
    }

    public function getTranslateButton(string $field): string
    {
        return sprintf(
            '<span data-translate-field="%s" data-translate-target-lang="%s">%s</span>',
            $field,
            $this->activeLang,
            sprintf(
                $GLOBALS['TL_LANG']['guave_deepl']['translate'][0],
                $this->defaultLanguage,
                $this->activeLang,
                Image::getHtml('pasteinto.svg')
            )
        );
    }

    protected function getActiveLang(DataContainer $dc): string
    {
        $language = $this->defaultLanguage;

        if ($dc instanceof DC_Multilingual) {
            $objSessionBag = $this->session->getBag('contao_backend');
            $sessionKey = 'dc_multilingual:' . $dc->table . ':' . $dc->id;
            if ($objSessionBag->get($sessionKey)) {
                $language = $objSessionBag->get($sessionKey);
            }
        } elseif ($dc instanceof DC_Table) {
            if ($dc->table === ContentModel::getTable()) {
                $content = ContentModel::findOneBy('id', $dc->id);
                $language = $this->getRootLanguageFromArticle((int) $content->pid);
            } elseif ($dc->table === ArticleModel::getTable()) {
                $article = ArticleModel::findOneBy('id', $dc->id);
                $language = $this->getRootLanguageFromPage((int) $article->pid);
            } elseif ($dc->table === PageModel::getTable()) {
                $page = PageModel::findOneBy('id', $dc->id);
                $language = $this->getRootLanguageFromPage((int) $page->id);
            }
        }

        $this->activeLang = $language;

        return $this->activeLang;
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
