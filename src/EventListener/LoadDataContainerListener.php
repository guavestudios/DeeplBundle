<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Input;
use Guave\DeeplBundle\Controller\Backend\DeeplButtons;

/**
 * @Hook("loadDataContainer")
 */
class LoadDataContainerListener
{
    protected bool $enabled = false;
    protected array $tables;

    public function __construct(bool $enabled, array $tables)
    {
        $this->enabled = $enabled;
        $this->tables = $tables;
    }
    
    public function __invoke(string $table): void
    {
        if (!$this->enabled) {
            return;
        }

        if (!Input::get('do')) {
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

        $moduleTable = $arrModule['tables'][0] ?? null;
        if ($table !== $moduleTable) {
            return;
        }

        if (array_key_exists($table, $this->tables)) {
            $GLOBALS['TL_DCA'][$table]['config']['onload_callback'][] = [DeeplButtons::class, 'registerDeepl'];

            // register fallback translation
            if ($GLOBALS['TL_DCA'][$table]['config']['dataContainer'] === 'Multilingual') {
                $GLOBALS['TL_DCA'][$table]['config']['onload_callback'][] = [LoadFallbackTranslationsListener::class, 'loadFallbackTranslation'];
            }
        }
    }
}
