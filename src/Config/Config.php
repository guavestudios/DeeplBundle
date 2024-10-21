<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\Config;

class Config
{
    private bool $enabled = false;
    private bool $freeApi = false;
    private string $defaultLanguage = 'de';
    private array $tables = [];

    public function __construct(array $config = ['defaultLanguage' => 'de'])
    {
        if (isset($config['enabled'])) {
            $this->setEnabled($config['enabled']);
        }
        if (isset($config['freeApi'])) {
            $this->setFreeApi($config['freeApi']);
        }
        if (isset($config['defaultLanguage'])) {
            $this->setDefaultLanguage($config['defaultLanguage']);
        }
        if (isset($config['tables'])) {
            $this->setTables($config['tables']);
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isFreeApi(): bool
    {
        return $this->freeApi;
    }

    public function setFreeApi(bool $freeApi): void
    {
        $this->freeApi = $freeApi;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    public function setDefaultLanguage(string $defaultLanguage): void
    {
        $this->defaultLanguage = $defaultLanguage;
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function setTables(array $tables): void
    {
        $this->tables = $tables;
    }

    public function getDeeplConfigByTable(string $table): array
    {
        $tables = $this->getTables();

        return $tables[$table] ?? [];
    }
}
