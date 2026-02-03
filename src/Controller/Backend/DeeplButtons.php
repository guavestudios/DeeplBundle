<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Controller\Backend;

use Contao\Backend;
use Contao\Controller;
use Contao\DataContainer;
use Contao\Image;
use Guave\DeeplBundle\Config\Config;
use Guave\DeeplBundle\Resolver\ActiveLanguageResolverInterface;

class DeeplButtons extends Backend
{
    protected iterable $activeLanguageResolver;

    protected string $activeLang;

    protected Config $config;

    public function __construct(Config $config, iterable $activeLanguageResolver)
    {
        $this->activeLanguageResolver = $activeLanguageResolver;
        $this->config = $config;

        parent::__construct();
    }

    public function registerDeepl(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }

        $activeLang = $this->getActiveLang($dc);

        if ($activeLang === $this->config->getDefaultLanguage()) {
            return;
        }

        Controller::loadLanguageFile('modules');

        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/guavedeepl/assets/translate.js';

        foreach ($this->config->getTables()[$dc->table]['fields'] as $field) {
            $GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['xlabel'][] = [self::class, 'translateButton'];
        }

        foreach (array_keys($this->config->getTables()[$dc->table]['multiColumnFields']) as $field) {
            $GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['xlabel'][] = [self::class, 'translateMultiColumnButton'];
        }

        $GLOBALS['TL_DCA'][$dc->table]['edit']['buttons_callback'][] = [self::class, 'addTranslateAllButton'];
    }

    public function translateButton(DataContainer $dc): string
    {
        $field = $dc->field;
        // inputUnit
        if ($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['inputType'] === 'inputUnit') {
            $field .= '[value]';
        }

        return $this->getTranslateButton($field);
    }

    public function translateMultiColumnButton(DataContainer $dc): string
    {
        $field = $dc->field;

        return $this->getMulticolumnTranslateButton(
            $field,
            $this->config->getTables()[$dc->table]['multiColumnFields'][$field]['fields'],
        );
    }

    public function addTranslateAllButton(array $arrButtons): array
    {
        $arrButtons['translateAll'] = \sprintf(
            '<button type="button" data-translate-all data-translate-source-lang="%s" data-translate-target-lang="%s" class="tl_submit" accesskey="a">%s</button>',
            $this->config->getDefaultLanguage(),
            $this->activeLang,
            \sprintf(
                $GLOBALS['TL_LANG']['guave_deepl']['translateAll'][0],
                $this->config->getDefaultLanguage(),
                $this->activeLang,
                Image::getHtml('pasteinto.svg'),
            ),
        );

        return $arrButtons;
    }

    public function getTranslateButton(string $field): string
    {
        return \sprintf(
            '<span data-translate-field="%s" data-translate-source-lang="%s" data-translate-target-lang="%s">%s</span>',
            $field,
            $this->config->getDefaultLanguage(),
            $this->activeLang,
            \sprintf(
                $GLOBALS['TL_LANG']['guave_deepl']['translate'][0],
                $this->config->getDefaultLanguage(),
                $this->activeLang,
                Image::getHtml('pasteinto.svg'),
            ),
        );
    }

    public function getMulticolumnTranslateButton(string $field, array $fields): string
    {
        return \sprintf(
            '<span data-translate-multicol="%s" data-translate-fields="%s" data-translate-source-lang="%s" data-translate-target-lang="%s">%s</span>',
            $field,
            implode(',', $fields),
            $this->config->getDefaultLanguage(),
            $this->activeLang,
            \sprintf(
                $GLOBALS['TL_LANG']['guave_deepl']['translate'][0],
                $this->config->getDefaultLanguage(),
                $this->activeLang,
                Image::getHtml('pasteinto.svg'),
            ),
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
            $language = $this->config->getDefaultLanguage();
        }

        $this->activeLang = $language;

        return $this->activeLang;
    }
}
