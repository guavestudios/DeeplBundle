<?php

declare(strict_types=1);

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\NewsArchiveModel;

$table = NewsArchiveModel::getTable();
Controller::loadLanguageFile($table);

PaletteManipulator::create()
    ->addField('language', 'title_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('langPid', 'title_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news_archive')
;

/**
 * LIST
 */
$GLOBALS['TL_DCA'][$table]['list']['label']['fields'] = ['title', 'language'];
$GLOBALS['TL_DCA'][$table]['list']['label']['format'] = '%s <span style="color:#999;">(%s)</span>';

/**
 * FIELDS
 */
$GLOBALS['TL_DCA'][$table]['fields']['language'] = [
    'label' => &$GLOBALS['TL_LANG'][$table]['language'],
    'exclude' => true,
    'inputType' => 'select',
    'filter' => true,
    'eval' => [
        'mandatory' => true,
        'tl_class' => 'w50 clr',
        'chosen' => true,
        'includeBlankOption' => true,
    ],
    'sql' => ['type' => 'string', 'length' => 2, 'default' => ''],
];

