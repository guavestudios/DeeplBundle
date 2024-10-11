<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Model;

use Contao\Database;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

class MultilingualModel extends Multilingual
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable;

    public function __construct(string $table, $objResult = null)
    {
        static::$strTable = $table;

        parent::__construct($objResult);
    }

    public function getFields(): array
    {
        return self::getTranslatableFields();
    }

    public static function getPidColumn(): string
    {
        return parent::getPidColumn();
    }

    public function findOneByLangPidAndLanguage(int $langPid, string $language)
    {
        $query = 'SELECT * FROM ' . static::$strTable . ' WHERE ' . static::getPidColumn() . ' = ? AND ' . static::getLangColumn() . ' = ? LIMIT 1';

        $objStatement = Database::getInstance()->prepare($query);

        $objStatement = static::preFind($objStatement);
        $objResult = $objStatement->execute([
            $langPid,
            $language,
        ]);

        if ($objResult->numRows < 1) {
            return [];
        }

        $objResult = static::postFind($objResult);

        $models = static::createCollectionFromDbResult($objResult, static::$strTable)->getModels();

        return $models[0] ?? [];
    }

    public function findOneByIdAndFallbackLanguage(int $id)
    {
        $query = 'SELECT * FROM ' . static::$strTable . ' WHERE id = ? AND ' . static::getLangColumn() . ' = ? LIMIT 1';

        $objStatement = Database::getInstance()->prepare($query);

        $objStatement = static::preFind($objStatement);
        $objResult = $objStatement->execute([
            $id,
            '',
        ]);

        if ($objResult->numRows < 1) {
            return [];
        }

        $objResult = static::postFind($objResult);

        $models = static::createCollectionFromDbResult($objResult, static::$strTable)->getModels();

        return $models[0] ?? [];
    }

    public static function getRootPageLanguages(): array
    {
        $objPages = Database::getInstance()->execute("SELECT DISTINCT language FROM tl_page WHERE type='root' AND language != ''");
        $languages = $objPages->fetchEach('language');

        array_walk(
            $languages,
            function (&$value) {
                $value = str_replace('-', '_', $value);
            }
        );

        asort($languages);

        return $languages;
    }
}
