- to enable composer script handler add

  `
  "scripts": {
    "globale-scripts": [
      "Globale\\Composer\\ScriptHandler::configureModules"
    ],
    "post-install-cmd": [
      "@globale-scripts"
    ],
    "post-update-cmd": [
      "@globale-scripts"
    ]
  },`
  to composer.json on magento installation
  
  to auto configure add `"globale-enabled-modules": ["Globale_Base", ...]` property to `extra` 
  

then run composer install or composer update