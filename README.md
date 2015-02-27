# ContentBundle

## Installation
Add to `composer.json` and install

    "it-blaster/content-bundle": "dev-master"

Add bundle to `AppKernel.php`

    new Etfostra\ContentBundle\EtfostraContentBundle()
    
Edit your `config.yml`, add etfostra_content:

    etfostra_content:
        frontend_controllers_namespace: Artsofte\MainBundle