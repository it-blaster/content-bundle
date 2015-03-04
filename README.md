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

Build models and install assets
``` bash
$ php app/console propel:build
$ php app/console assets:install --symlink
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