# ContentBundle
[![Build Status](https://scrutinizer-ci.com/g/it-blaster/content-bundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/it-blaster/content-bundle/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/it-blaster/content-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/it-blaster/content-bundle/?branch=master)


## Installation
Add to `composer.json` and install

``` js
{
    "require": {
        "it-blaster/content-bundle": "dev-master"
	},
}
```

Add bundle to `AppKernel.php`
``` php
<?php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Etfostra\ContentBundle\EtfostraContentBundle(),
    );
}
```

Add to `routing.yml`
``` yaml
EtfostraContentBundle:
    resource: .
    type: extra
```

Build models, run SQL and install assets
``` bash
$ php app/console propel:model:build
$ php app/console propel:migration:generate-diff
$ php app/console propel:migration:migrate
$ php app/console assets:install
```

To enable labels translation in SonataAdmin
``` yaml
framework:
    translator:      { fallbacks: ["%locale%"] }
```

## Cofigure
Edit your `config.yml`, add etfostra_content:
``` yaml
etfostra_content:
    page_controller_name: EtfostraContentBundle:PageFront:page
    page_template_name: EtfostraContentBundle:Front:default.html.twig
    module_route_groups: # optional, modules (routes groups)
        - { name: News, routes: @AcmeAppBundle/Resources/config/routing_news.yml }
        - { name: Catalog, routes: @AcmeAppBundle/Resources/config/routing_catalog.yml }
```

Debug routes
``` bash
$ php app/console debug:router
```

### Twig functions
Getting link by route name:
``` twig
{{ page_path('etfostra_content_56') }}
```
This function return link to page by "Route Name", you can copy it from Page edit form. Function prevent exception when route not found.

Getting link by slug:
``` twig
{{ page_path_by_slug('any-page-slug') }}
```
**Generates extra query!**
This function return link to page by Slug, you can edit and copy it on Page edit form. Function prevent exception when route not found.