<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Translate;

use Contao\Controller;
use Contao\CoreBundle\Slug\Slug;
use Contao\Database;
use Contao\Database\Statement;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use Exception;
use Guave\DeeplBundle\Api\DeeplApi;
use Guave\DeeplBundle\Config\Config;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

class TranslateDCA
{
    protected Config $config;

    protected DeeplApi $deeplApi;

    public function __construct(Config $config, DeeplApi $deeplApi)
    {
        $this->deeplApi = $deeplApi;
        $this->config = $config;
    }

    public function translateModel(Model $model, string $fromLang, string $toLang): void
    {
        $deeplConfigByTable = $this->config->getDeeplConfigByTable($model::getTable());

        if (!empty($deeplConfigByTable)) {
            Controller::loadDataContainer($model::getTable());

            if (!empty($deeplConfigByTable['fields'])) {
                foreach ($deeplConfigByTable['fields'] as $field) {
                    if (isset($GLOBALS['TL_DCA'][$model::getTable()]['fields'][$field])) {
                        if (!empty($model->$field)) {
                            $value = '';
                            // serialized field
                            if ($GLOBALS['TL_DCA'][$model::getTable()]['fields'][$field]['inputType'] === 'inputUnit') {
                                $value = unserialize($model->$field);

                                if (isset($value['value'])) {
                                    $value['value'] = $this->translate($value['value'], $fromLang, $toLang);
                                }
                                $value = serialize($value);
                            } elseif ($GLOBALS['TL_DCA'][$model::getTable()]['fields'][$field]['inputType'] !== 'pageTree') { // do not translate pageTree
                                $value = $this->translate($model->$field, $fromLang, $toLang);
                            }
                            $model->$field = $value;

                            // if isset rte also translate link urls
                            if (isset($GLOBALS['TL_DCA'][$model::getTable()]['fields'][$field]['eval']['rte'])) {
                                $model->$field = $this->translateLinkUrlOfRteField($model, $field, $toLang);
                            }
                        }
                    }
                }
            }

            if (!empty($deeplConfigByTable['multiColumnFields'])) {
                foreach ($deeplConfigByTable['multiColumnFields'] as $field => $fields) {
                    if (isset($GLOBALS['TL_DCA'][$model::getTable()]['fields'][$field])) {
                        if (!empty($model->$field)) {
                            $values = unserialize($model->$field);

                            foreach (array_keys($values) as $k) {
                                foreach ($fields['fields'] as $f) {
                                    $values[$k][$f] = $this->translate($values[$k][$f], $fromLang, $toLang);
                                }
                            }
                            $model->$field = serialize($values);
                        }
                    }
                }
            }
        }
    }

    public function translate(string|null $value, string $fromLang, string $toLang): string|null
    {
        if (!empty($value)) {
            $translateResponse = $this->deeplApi->translate($value, $fromLang, $toLang);

            if (isset($translateResponse['translations'][0]['text'])) {
                $value = $translateResponse['translations'][0]['text'];
            }
        }

        return $value;
    }

    public function save(Model $model): void
    {
        $params = $model->row();

        try {
            if ($model->id === null) {
                $stmt = Database::getInstance()->prepare('INSERT INTO '.$model::getTable().' %s')
                    ->set($params)
                    ->execute()
                ;
                /** @var Statement $stmt */
                $model->id = $stmt->insertId;
            } else {
                Database::getInstance()->prepare('UPDATE '.$model::getTable().' %s WHERE id = ? LIMIT 1')
                    ->set($params)
                    ->execute((int) $model->id)
                ;
            }
        } catch (Exception $e) {
            dump(debug_backtrace()[0]['class'].':'.debug_backtrace()[0]['function']);
            dump(debug_backtrace()[1]['class'].':'.debug_backtrace()[1]['function']);
            dump(debug_backtrace()[2]['class'].':'.debug_backtrace()[2]['function']);
            dump(debug_backtrace()[3]['class'].':'.debug_backtrace()[3]['function']);
            dump($model::class, $params);
            dd($e->getMessage());
        }
    }

    public function slugify(Model $model, string $field): void
    {
        /** @var Slug $slugify */
        $slugify = System::getContainer()->get('contao.slug');

        if (!empty($model->$field)) {
            $slug = $slugify->generate($model->$field);
        } else {
            return;
        }

        $params = [];
        $query = 'SELECT id FROM '.$model::getTable().' WHERE '.$field.' = ? AND id != ?';
        $params[] = $slug;
        $params[] = $model->id ?? 0;

        if ($model instanceof Multilingual) {
            $query .= ' AND langPid!=? AND id!=?';
            $params[] = $model->langPid;
            $params[] = $model->langPid;
        }
        $exists = Database::getInstance()->prepare($query)->execute(...$params)->numRows > 0;

        if ($exists) {
            $slug .= '-'.$model->id;
        }

        $model->$field = $slug;
    }

    public function translateLinkUrl(string|null $value, string $toLang): string|null
    {
        if (!$value) {
            return $value;
        }

        // remove {{
        $value = substr($value, 2);

        // remove }}
        $value = substr($value, 0, -2);

        [$insertTag, $pageString] = explode('::', $value, 2);
        [$pageId, $filter] = explode('|', $pageString, 2);
        $pageId = $this->getTranslatePage((int) $pageId, $toLang);

        return \sprintf('{{%s::%s|%s}}', $insertTag, $pageId, $filter);
    }

    public function getTranslatePage(int|null $value, string $toLang): int|null
    {
        if (!$value) {
            return $value;
        }

        $languageMain = (int) $value;
        // read alle pages with same main language
        $pages = PageModel::findAll(['column' => ['languageMain=?'], 'value' => [$languageMain]]);

        if ($pages) {
            foreach ($pages as $page) {
                $page->loadDetails();
                // take first matching toLang
                if ($page->rootLanguage === $toLang) {
                    $value = (int) $page->id;
                    break;
                }
            }
        }

        return $value;
    }

    public function translateLinkUrlOfRteField(Model $model, string $field, string $toLang): string|null
    {
        $value = $model->$field;

        if (empty($value)) {
            return $value;
        }

        if (isset($GLOBALS['TL_DCA'][$model::getTable()]['fields'][$field]['eval']['rte'])) {
            $re = '/{{(link_url::[0-9].*)}}/m';
            $matches = [];
            preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);

            if (!empty($matches)) {
                foreach ($matches as $match) {
                    $value = str_replace($match[0], $this->translateLinkUrl($match[0], $toLang), $value);
                }
            }
        }

        return $value;
    }
}
