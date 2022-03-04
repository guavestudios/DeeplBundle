# Deepl Bundle

Adds support for deepl.com translation to contao dca input fields

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
