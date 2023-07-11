<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Input;
use Guave\DeeplBundle\Config\Config;
use Guave\DeeplBundle\Controller\Backend\DeeplButtons;

/**
 * @Hook("loadDataContainer")
 */
class LoadDataContainerListener
{
    protected Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function __invoke(string $table): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!Input::get('do')) {
            return;
        }

        if (Input::get('act') !== 'edit') {
            return;
        }

        $module = Input::get('do');
        $arrModule = [];

        foreach ($GLOBALS['BE_MOD'] as &$arrGroup) {
            if (isset($arrGroup[$module])) {
                $arrModule = &$arrGroup[$module];
                break;
            }
        }

        $moduleTable = Input::get('table') ?? $arrModule['tables'][0];

        if ($moduleTable !== $table) {
            return;
        }

        if (array_key_exists($table, $this->config->getTables())) {
            $GLOBALS['TL_DCA'][$table]['config']['onload_callback'][] = [DeeplButtons::class, 'registerDeepl'];

            // register fallback translation
            if ($GLOBALS['TL_DCA'][$table]['config']['dataContainer'] === 'Multilingual') {
                $GLOBALS['TL_DCA'][$table]['config']['onload_callback'][] = [LoadFallbackTranslationsListener::class, 'loadFallbackTranslation'];
            }
        }
    }
}
