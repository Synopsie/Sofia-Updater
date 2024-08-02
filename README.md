# Sofia-Updater 💢
## ✏ Description
- Sofia-Updater est une api permettant à un plugin de vérifier si une nouvelle release est disponible

## 🛠 Usage

`````php
Updater::checkUpdate($pluginName, $this->getDescription()->getVersion(), $ownerRepo, $repoName');
`````

## 📦 Installation
- Ajouter le repository dans le fichier ``composer.json``

`````
composer require synopsie/sofia-updater
`````

- Développé par [Synopsie](https://arkaniastudios.com)

![Sofia-Updater](sofia-updater.png)