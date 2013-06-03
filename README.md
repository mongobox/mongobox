# Mongobox

La Mongobox est à l'origine un jukebox participatif fait en Symfony2.

L'application s'enrichit désormais de nouvelles fonctionnalités de jour en jour au gré des nouvelles idées de ses utilisateurs/concepteurs...

## Fonctionnalités
  * **Système d'authentification classique**
  * **Gestion de groupes d'utilisateurs qui peuvent être publiques ou privés**
  * **Jukebox collaboratif alimenté par des vidéos Youtube**
    * Chaque membre d'un groupe peut poster de nouvelles vidéos à partir de n'importe quelle page
    * Les vidéos sont diffusées en direct pour tous les membres du groupe grâce à un serveur de websockets
    * Un système de votes permet de passer les chansons à partir d'un certain ratio (configurable par groupe)
    * Un second système de vote permet d'augmenter ou de diminuer le volume à distance de la vidéo diffusée
    * Des droits d'administrateur sont disponibles pour le live
      * *L'utilisateur avec ces droits peut : lancer ou mettre en pause de la vidéo, passer à la suivante ou remplacer la vidéo actuelle par une autre*
      * *Ce rôle n'est pas fixe, il peut être récupéré à tout moment par un autre utilisateur grâce à un système de "putsch" utilisant lui-aussi le serveur de websockets*
      * *Toutes les actions effectuées par l'administrateur sont répercutées aux clients de son live*
    * La diffusion des vidéos se fait grâce à un système d'aléatoire intelligent ainsi qu'une gestion de tags pour autoriser ou non le passage de certains types de vidéos
  * **"Tumblr privé" où les membres d'un groupe peuvent se partager des images, les noter et consulter leurs classements**
  * **Statistiques d'utilisation de l'application avec graphiques (jukebox, tumblr, connexions des utilisateurs)**
  * **Le module "Mongoeat" met à disposition un système de votes pour définir quel sera le restaurant du midi**
  * **...**

## Pré-requis
  * Node.js & npm
  * [Forever](https://github.com/nodejitsu/forever) (facultatif)

## Installation & Utilisation
  * [Déploiement classique d'un projet Symfony2](http://symfony.com/doc/master/cookbook/deployment-tools.html)
  * <code>npm install socket.io</code>
  * <code>node web/bundles/mongoboxjukebox/js/app.js</code> ou <code>forever start web/bundles/mongoboxjukebox/js/app.js</code>

## Problèmes connus
  * Aucun design actuellement réalisé donc l'intégration se résume à un thème Bootstrap classique... bientôt peut-être !
