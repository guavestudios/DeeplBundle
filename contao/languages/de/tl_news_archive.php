<?php

declare(strict_types=1);

use Contao\NewsArchiveModel;

$table = NewsArchiveModel::getTable();

$GLOBALS['TL_DCA'][$table]['language'] = ['Sprache'];
