# Deepl Bundle

Adds support for deepl.com translation to contao dca input fields

also supports Multilingual data container

## Install


```bash
composer require guave/deepl-bundle
```

add deepl api key to .env
```
DEEPL_API_KEY=''
```
install assets
perhaps you need web as option, for old structure
```bash
php vendor/bin/contao-console assets:install
```

## Configuration

```yaml
guave_deepl:
    enabled: true
    defaultLanguage: de
    tables:
        tl_content:
            fields:
                - title
                - text
```


## Register Custom ActiveLanguageResolver
register it with tag 'deepl.resolver' and implmenet

```yaml
#services.yml
services:
    # ...

    App\Resolver\ActiveLanguageByProductLanguageResolver:
        public: true
        tags:
            - { name: 'deepl.resolver', priority: 50 }

```


```php
<?php
#src/Resolver/ActiveLanguageByProductLanguageResolver.php
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
