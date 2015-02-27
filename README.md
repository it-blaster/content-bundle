# ContentBundle

## Installation
Add to composer.json and install

```js
{
    "require": {
        "it-blaster/content-bundle": "dev-master"
	},
}
```

Add bundle to AppKernel.php
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
    


