# Spec Curator

Build multiple curated versions of an OpenAPI specification document.

## Usage

```php
<?php

$spec = \Beblife\SpecCurator\Spec::fromFile('path/to/my/spec.json');

$curatedSpec = \Beblife\SpecCurator\Curate::fromSpec($spec)
    ->servers([
        'https://my.production-server.com/api',
    ])
    ->paths([
        '/my-path',
        '/my-other-path' => ['GET'],
    ])
    ->security([
        'User Access Token',
    ])
    ->noTags()
    ->toCuratedSpec()
;

file_put_contents('/my/path/to/save/the/curated-spec.json', (string) $curatedSpec);
```

## Checklist

- [x] Curate servers
    - [x] Global servers
    - [x] Path Servers
- [x] Curate paths
    - [x] Responses
    - [x] Methods
    - [ ] Request bodies
- [x] Curate security
    - [x] Global security
    - [x] Path security
- [x] Curate schemas
- [ ] Curate tags
- [x] Remove all tags


