<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\EventListener;

use Contao\Database;
use Contao\DataContainer;
use Contao\Model;
use Guave\DeeplBundle\Config\Config;
use Guave\DeeplBundle\Model\MultilingualModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\DcMultilingualBundle\Driver as Multilingual;

class LoadFallbackTranslationsListener
{
    protected Config $config;
    protected RequestStack $requestStack;

    public function __construct(Config $config, RequestStack $requestStack)
    {
        $this->config = $config;
        $this->requestStack = $requestStack;
    }

    public function loadFallbackTranslation(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }

        if (!$dc instanceof Multilingual) {
            return;
        }

        $id = (int)$dc->id;
        $activeLang = $this->getActiveLang($dc->table, $id);
        if ($activeLang === $this->config->getDefaultLanguage()) {
            return;
        }

        $modelClass = Model::getClassFromTable($dc->table);
        $langModel = new MultilingualModel($dc->table);

        $translateFields = $langModel->getFields();

        $activeLangModel = $langModel->findOneByLangPidAndLanguage($id, $activeLang);
        $fallbackLangModel = $langModel->findOneByIdAndFallbackLanguage($id, "");

        $params = [];
        if (!$activeLangModel) {
            $mode = 'insert';
            $activeLangModel = new $modelClass();
            $params['tstamp'] = time();
            $params[$langModel::getPidColumn()] = $id;
            $params[$langModel::getLangColumn()] = $activeLang;
        } else {
            $mode = 'update';
            $params['tstamp'] = time();
        }

        $save = true;
        foreach ($translateFields as $translateField) {
            if (!empty($activeLangModel->$translateField)) {
                $save = false;
            }
            $params[$translateField] = $fallbackLangModel->$translateField;
        }

        if ($save) {
            if ($mode === 'insert') {
                Database::getInstance()->prepare('INSERT INTO ' . $dc->table . ' %s')->set($params)->execute();
            } else {
                Database::getInstance()->prepare('UPDATE ' . $dc->table . ' %s WHERE id = ? LIMIT 1')
                    ->set($params)
                    ->execute((int)$activeLangModel->id);
            }
        }
    }

    protected function getActiveLang(string $table, int $id): string
    {
        $objSessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $sessionKey = 'dc_multilingual:' . $table . ':' . $id;

        return $objSessionBag->get($sessionKey) ?? $this->config->getDefaultLanguage();
    }

    protected function getModel(string $table, int $id): ?Model
    {
        /** @var Model $class */
        $class = Model::getClassFromTable($table);

        return $class::findByPk($id);
    }
}
