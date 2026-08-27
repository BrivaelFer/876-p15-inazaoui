# Contribuer au projet Ina Zaoui

Ce guide explique comment participer au projet en signalant un problème, en proposant une fonctionnalité ou en contribuant au code, aux tests et à la documentation.

## Soumettre un problème

Avant de créer un ticket, vérifier qu'un problème similaire n'existe pas déjà. Décrire ensuite :

- le comportement observé et le comportement attendu ;
- les étapes précises pour reproduire le problème ;
- la page, la commande ou la fonctionnalité concernée ;
- le contexte d'exécution, notamment le navigateur, le système et l'installation utilisée ;
- les messages d'erreur, captures d'écran ou extraits de logs utiles.

Ne pas publier d'informations personnelles, de mots de passe, de clés ou d'autres données sensibles.

## Proposer une fonctionnalité

Présenter la fonctionnalité en expliquant :

- le besoin auquel elle répond ;
- le parcours utilisateur attendu ;
- les écrans, routes ou entités concernés ;
- les alternatives éventuellement envisagées.

Une proposition claire et limitée à un besoin précis facilite son évaluation. Attendre les retours sur le ticket avant de commencer un développement important.

## Contribuer au code, aux tests et à la documentation

### Code

- Créer une branche dédiée depuis `main`, par exemple `feature/gestion-albums` ou `fix/validation-email`.
- Respecter la structure Symfony existante et privilégier les services et repositories déjà présents.
- Utiliser une migration Doctrine pour toute modification du schéma de base de données.
- Ne jamais ajouter de secrets, de fichiers temporaires ou de contenu provenant de `vendor/`.

### Tests

Ajouter ou mettre à jour les tests concernés par toute modification de comportement. Avant de soumettre une contribution, lancer :

```bash
composer db-test
php bin/phpunit
```
Pour générer un rapport de tests:

```bash
XDEBUG_MODE=coverage php vendor/bin/phpunit --coverage-html var/coverage
```
Veuillez un taux de couverture suppérieur à 70%.

### Documentation

Mettre à jour le `README.md`, ce guide ou les commentaires concernés lorsque la contribution modifie l'installation, l'utilisation ou le fonctionnement du projet.

### Pull request

La pull request doit expliquer le problème ou le besoin traité, résumer la solution, indiquer les tests exécutés et mentionner les migrations ou changements de configuration éventuels. Garder une pull request centrée sur un sujet clairement identifié.
