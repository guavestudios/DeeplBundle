# Deepl Bundle

Adds support for [deepl](https://deepl.com) translation API in DCA input fields.

Also supports [Multilingual Data Container](https://github.com/terminal42/contao-DC_Multilingual)

## Install

### Install Contao bundle

```bash
composer require guave/deepl-bundle
```

### Add deepl API key to .env

```
DEEPL_API_KEY=''
```

### Install assets

if you use `web` as the document root, pass it as an argument

```bash
php vendor/bin/contao-console assets:install
```

## Configuration

```yaml
guave_deepl:
  enabled: true
  freeApi: true
  defaultLanguage: de
  tables:
    tl_content:
      fields:
        - title
        - text
```

## Register Custom ActiveLanguageResolver

Register it with the tag `deepl.resolver`

```yaml
# services.yml
services:
  # ...

  App\Resolver\ActiveLanguageByProductLanguageResolver:
    public: true
    tags:
      - { name: 'deepl.resolver', priority: 50 }

```

```php
# src/Resolver/ActiveLanguageByProductLanguageResolver.php
<?php

namespace App\Resolver;

use Contao\DataContainer;
use Guave\DeeplBundle\Resolver\ActiveLanguageResolverInterface;

class ActiveLanguageByProductLanguageResolver implements ActiveLanguageResolverInterface
{
    public function supports(DataContainer $dataContainer): bool
    {
        // TODO: Implement supports() method.
    }

    public function resolve(DataContainer $dataContainer): ?string
    {
        // TODO: Implement resolve() method.
    }}
}

```
