# Contribuer au projet Ina Zaoui

Ce guide explique comment participer au projet en signalant un problème, en proposant une fonctionnalité ou en contribuant au code, aux tests et à la documentation.

## 1. Comprendre le projet avant de contribuer

Avant de modifier le code ou la documentation, il est important de comprendre la structure du projet et les responsabilités de chaque couche :

- `src/Controller/` contient les actions HTTP et la logique de navigation.
- `src/Entity/` définit le modèle de données Doctrine.
- `src/Repository/` centralise les requêtes métiers et les filtres.
- `src/Form/` contient les formulaires de création et de modification.
- `src/Service/` regroupe la logique applicative spécifique.
- `templates/` contient les vues Twig du front office et du back office.
- `tests/` contient les validations fonctionnelles et unitaires.

Une modification doit toujours rester cohérente avec cette architecture Symfony et avec les conventions du projet.

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
- Vérifier les impacts sur les relations entre `User`, `Album` et `Media` avant toute suppression ou modification de schéma.

### Tests

Ajouter ou mettre à jour les tests concernés par toute modification de comportement. Avant de soumettre une contribution, lancer :

```bash
composer db-test
php bin/phpunit
```

Pour générer un rapport de tests :

```bash
XDEBUG_MODE=coverage php vendor/bin/phpunit --coverage-html var/coverage
```

Veuillez un taux de couverture supérieur à 70%.

### Documentation

Mettre à jour le `README.md`, ce guide ou les commentaires concernés lorsque la contribution modifie l'installation, l'utilisation, la structure ou le fonctionnement du projet.

La documentation doit inclure, selon le contexte :

- les nouvelles commandes à exécuter ;
- les changements de configuration ;
- les routes ou écrans modifiés ;
- les règles métier impactées ;
- les points de vigilance liés aux données ou aux uploads.

### Règles de qualité

Avant une pull request, vérifier :

- que le code suit la structure du projet ;
- que les migrations sont bien ajoutées si le schéma change ;
- que les tests associés sont passés ;
- que la documentation est à jour ;
- que les fichiers générés ou temporaires n'ont pas été ajoutés accidentellement.

## Processus de contribution recommandé

1. Vérifier l'existence d'une issue ou d'un ticket.
2. Créer une branche dédiée.
3. Implémenter le correctif ou la fonctionnalité.
4. Mettre à jour les tests et la documentation.
5. Lancer la validation locale.
6. Ouvrir une pull request avec un résumé clair.

### Pull request

La pull request doit expliquer le problème ou le besoin traité, résumer la solution, indiquer les tests exécutés et mentionner les migrations ou changements de configuration éventuels. Garder une pull request centrée sur un sujet clairement identifié.

Une bonne PR contient en général :

- un titre clair et explicite ;
- une description du contexte ;
- la solution mise en place ;
- les fichiers ou modules touchés ;
- les validations effectuées.
