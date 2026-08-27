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





