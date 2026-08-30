# Ina Zaoui

Portfolio photographique développé avec Symfony 7.4. L'application présente le portfolio d'Ina Zaoui et permet de gérer les utilisateurs invités, les albums et les médias depuis un espace d'administration.

## Pré-requis

### Installation avec Docker pour la base de données

- Docker Desktop avec Docker Compose
- Git
- Le port `5432` disponible

Docker est utilisé uniquement pour exécuter PostgreSQL 15. PHP, Symfony et l'application sont exécutés localement.

### Installation locale

- PHP 8.2 ou supérieur
- Composer
- PostgreSQL 15 ou supérieur
- Les extensions PHP `ctype`, `iconv`, `intl`, `mbstring`, `pdo_pgsql`, `xml`, `zip` et `gd`

## Installation

### Démarrer PostgreSQL avec Docker

Depuis la racine du projet :

```bash
docker compose up -d
```

La base de données est alors accessible sur `localhost:5432`.

Pour arrêter le conteneur :

```bash
docker compose down
```

Pour supprimer également les données PostgreSQL :

```bash
docker compose down -v
```

### Installer et lancer l'application en local

1. Installer les dépendances PHP :

	```bash
	composer install
	```

2. Configurer `DATABASE_URL` dans `.env.local` pour utiliser la base PostgreSQL Docker.

3. Créer la base, appliquer les migrations et charger les données de démonstration :

	```bash
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:migrations:migrate -n
	php bin/console doctrine:fixtures:load -n
	```

4. Lancer le serveur Symfony :

	```bash
	symfony server:start
	```

L'application est alors disponible à l'adresse suivante :

<http://localhost:8000> (<http://127.0.0.1:8000> sur Windows)

### Utiliser une base PostgreSQL locale

Il est également possible d'utiliser une installation locale de PostgreSQL. Dans ce cas, configurer `DATABASE_URL` dans `.env.local`, puis suivre les étapes 3 et 4 ci-dessus.

## Architecture du projet

Le projet suit la structure standard d'une application Symfony 7.4 :

- `src/Controller/` : contrôleurs du front-office et du back-office.
- `src/Entity/` : entités Doctrine (`User`, `Album`, `Media`).
- `src/Form/` : formulaires Symfony de création et modification.
- `src/Repository/` : accès aux données et requêtes spécifiques.
- `src/Service/` : logique métier découpée par domaine.
- `templates/` : vues Twig, séparées entre interface publique et administration.
- `public/` : point d'entrée web, fichiers publics, images et uploads.
- `migrations/` : script SQL de versionnement Doctrine.
- `config/` : configuration Symfony, sécurité, routes et services.
- `tests/` : tests fonctionnels et unitaires.

Le coeur fonctionnel de l'application repose sur la gestion d'un portfolio photographique :

- les utilisateurs peuvent être invités ou administrateurs ;
- les albums regroupent des médias photographiques ;
- les médias sont associés à un album et éventuellement à un utilisateur ;
- l'administration permet de gérer les comptes, les albums et les médias.

## Configuration de l'environnement

### Fichier .env.local

Créer un fichier `.env.local` à la racine du projet si nécessaire, puis définir la variable de connexion à la base :

```env
DATABASE_URL="postgresql://postgres:postgres@127.0.0.1:5432/ina_zaoui?serverVersion=15&charset=utf8"
```

Les valeurs peuvent varier selon votre installation locale ou l'environnement Docker. Le point important est de garder une base PostgreSQL cohérente avec les migrations et les fixtures.

### Fichiers d'upload

Les médias et les images sont stockés dans le dossier `public/uploads` ou dans les dossiers de `public/upload-{env}` de l'application. Vérifier que le dossier est lisible et modifiable par le processus PHP.

## Rôles et règles métier

Le système distingue plusieurs profils fonctionnels :

- `ROLE_USER` : utilisateur inscrit.
- `ROLE_ACTIVE_USER` : utilisateur inscrit et actif, visible côté front office.
- `ROLE_ADMIN` : administrateur, accès au back office.
- Les pages privées de l'administration seront protégées par la configuration de sécurité Symfony.

Les règles essentielles du projet sont les suivantes :

- un invité peut consulter la liste des profils et le portfolio public ;
- un photographique ou un profil public peut être affiché selon l'état de l'utilisateur ;
- l'administration permet de gérer les comptes, les albums et les médias ;
- la suppression d'un utilisateur avec médias associés doit être traitée avec soin pour éviter les fichiers orphelins et les relations incohérentes.

## Routes et écrans principaux

### Front office

- `/` : page d'accueil du portfolio.
- `/about` : page de présentation.
- `/guests` : liste des invités actifs.
- `/guest/{id}` : profil d'un invité.
- `/portfolio/{id}` : portfolio d'un utilisateur ou d'un album.

### Administration

- `/login` : connexion à l'espace d'administration.
- `/admin/album` : liste des albums.
- `/admin/album/add` : ajout d'un album.
- `/admin/album/update/{id}` : modification d'un album.
- `/admin/album/delete/{id}` : suppression d'un album.
- `/admin/media` : liste des médias.
- `/admin/media/add` : ajout d'un média.
- `/admin/media/delete/{id}` : suppression d'un média.
- `/admin/user` : liste des utilisateurs.
- `/admin/user/add` : ajout d'un utilisateur.
- `/admin/user/{id}/edit` : modification d'un utilisateur.
- `/admin/user/{id}/delete` : suppression d'un utilisateur.
- `/admin/user/{id}/deactivate` : désactivation d'un utilisateur.
- `/admin/user/{id}/activate` : activation d'un utilisateur.

## Modèle de données

Les principales entités du projet sont :

### User

- informations de compte et de profil ;
- rôle utilisateur ;
- état actif/inactif ;
- éventuellement relation avec plusieurs médias ou albums selon le modèle métier.

### Album

- regroupement de contenus photographiques ;
- titre et métadonnées associées ;
- relation avec les médias du portfolio.

### Media

- fichier/image associé à un album ou à un utilisateur ;
- nom du fichier, chemin d’accès et éventuels détails de publication ;
- traitement spécifique lors des ajouts et suppressions.

Ces entités sont gérées par Doctrine et versionnées via les migrations Symfony.

## Tests et couverture

Pour lancer la suite de tests :

```bash
php bin/phpunit
```

Pour préparer la base de test puis exécuter les tests :

```bash
composer db-test
php bin/phpunit
```

Pour générer un rapport de couverture :

```bash
XDEBUG_MODE=coverage php vendor/bin/phpunit --coverage-html var/coverage
```

Le rapport HTML est alors disponible dans `var/coverage`.

Remplacé `var/coverage` par `test-coverage` pour le rapport à conserver dans le repo Git.

## Dépannage courant

### Problème de base de données

- vérifier que PostgreSQL est bien démarré ;
- vérifier la valeur de `DATABASE_URL` ;
- relancer les migrations si la base est vide ou incohérente.

```bash
composer db
```

Ou par étape :

```bash
php bin/console doctrine:database:drop -f # Pour remettre l'index à 0 (optionnel)
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n
```

### Problème de cache

```bash
php bin/console cache:clear
```

### Problème avec les images ou uploads

- vérifier les droits d'écriture sur les dossiers `public/uploads` ;
- vérifier que le chemin de stockage correspond à celui attendu par le projet ;
- vérifier les erreurs Symfony dans les logs si un upload échoue.

### Problème de dépendances PHP

```bash
composer install
```

Si certains modules PHP manquent, vérifier la liste des extensions requises dans la section de prérequis.

## Usage

### Accès à l'application

- Accueil : `/`
- Liste des invités : `/guests`
- Portfolio : `/portfolio`
- Présentation : `/about`
- Connexion à l'administration : `/login`

Les fixtures créent un compte administrateur de démonstration :

```text
Email : ina@zaoui.com
Mot de passe : password
```

### Commandes utiles

L'application étant exécutée en local, lancer directement les commandes Symfony avec `php bin/console`.

```bash
# Vider le cache
php bin/console cache:clear

# Charger à nouveau les données de démonstration
composer db

# Lancer les tests
php bin/phpunit

# Lancer une analyse static
vendor/bin/phpstan
```

Pour préparer entièrement la base de test :

```bash
composer db-test
```





