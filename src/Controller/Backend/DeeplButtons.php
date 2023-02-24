<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Controller\Backend;

use Contao\Backend;
use Contao\Controller;
use Contao\DataContainer;
use Contao\Image;
use Guave\DeeplBundle\Resolver\ActiveLanguageResolverInterface;

class DeeplButtons extends Backend
{
    protected string $defaultLanguage;

    protected array $tables;

    protected string $activeLang;

    protected iterable $activeLanguageResolver;

    public function __construct(string $defaultLanguage, array $tables, iterable $activeLanguageResolver)
    {
        $this->defaultLanguage = $defaultLanguage;
        $this->tables = $tables;
        $this->activeLanguageResolver = $activeLanguageResolver;

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

        foreach ($this->tables[$dc->table]['multiColumnFields'] as $field => $fields) {
            $GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['xlabel'][] = [self::class, 'translateMultiColumnButton'];
        }

        $GLOBALS['TL_DCA'][$dc->table]['edit']['buttons_callback'][] = [self::class, 'addTranslateAllButton'];
    }

    public function translateButton(DataContainer $dc)
    {
        $field = $dc->field;
        // inputUnit
        if ($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['inputType'] === 'inputUnit') {
            $field .= '[value]';
        }

        return $this->getTranslateButton($field);
    }

    public function translateMultiColumnButton(DataContainer $dc)
    {
        $field = $dc->field;

        return $this->getMulticolumnTranslateButton($field, $this->tables[$dc->table]['multiColumnFields'][$field]['fields']);
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

    public function getMulticolumnTranslateButton(string $field, array $fields): string
    {
        return sprintf(
            '<span data-translate-multicol="%s" data-translate-fields="%s" data-translate-target-lang="%s">%s</span>',
            $field,
            implode(',', $fields),
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
        $language = null;

        /** @var ActiveLanguageResolverInterface $resolver */
        foreach ($this->activeLanguageResolver as $resolver) {
            if (!$resolver->supports($dc)) {
                continue;
            }

            $language = $resolver->resolve($dc);
        }

        if (!$language) {
            $language = $this->defaultLanguage;
        }

        $this->activeLang = $language;

        return $this->activeLang;
    }
}
