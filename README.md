Mongobox
========

Qu'est-ce que Mongobox
-----------------

Mongobox est un jukebox participatif fait en Symfony 2.

Il comprend :
- La lecture de la même vidéo sur plusieurs ordinateurs différents, avec un administrateur qui peut accélérer, mettre en pause, etc.
- Un système de vote pour passer à la chanson suivante
- Un système de playlist aléatoire, remontant principalement les chansons les moins diffusées et qui ont le plus de vote positif.
- Un système de tumblr intégré.
- Un regroupement de Flux RSS.

Requirements
------------
Symfony2

Installation
------------
Renommer app/config/parameters.yml.dist en app/config/parameters.yml et modifiez les valeurs en fonction de votre système.
Executer les commandes suivantes :
- php composer.phar install
- php app/console doctrine:database:create
- php app/console doctrine:schema:update --force
- php app/console assets:install web --symlink
