# Ina Zaoui

Portfolio photographique développé avec Symfony 7.4. L'application présente le portfolio d'Ina Zaoui et permet de gérer les utilisateurs invités, les albums et les médias depuis un espace d'administration.

## Pré-requis

### Installation avec Docker (recommandée)

- Docker Desktop avec Docker Compose
- Git
- Les ports `8080`, `5432` et `9003` disponibles

L'environnement Docker fournit automatiquement PHP 8.2, Apache, PostgreSQL 15 et les extensions PHP nécessaires.

### Installation locale

- PHP 8.2 ou supérieur
- Composer
- PostgreSQL 15 ou supérieur
- Les extensions PHP `ctype`, `iconv`, `intl`, `mbstring`, `pdo_pgsql`, `xml`, `zip` et `gd`

## Installation

### Avec Docker

Depuis la racine du projet :

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php bin/console doctrine:migrations:migrate -n
docker compose exec app php bin/console doctrine:fixtures:load -n
```

L'application est alors disponible à l'adresse suivante :

<http://localhost:8080>

Pour arrêter les conteneurs :

```bash
docker compose down
```

Pour supprimer également les données PostgreSQL :

```bash
docker compose down -v
```

### En local

1. Installer les dépendances PHP :

	```bash
	composer install
	```

2. Configurer `DATABASE_URL` dans `.env.local` pour pointer vers une base PostgreSQL locale.

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

Avec Docker, préfixer les commandes Symfony par `docker compose exec app`. En installation locale, exécuter directement les commandes `php bin/console`.

```bash
# Vider le cache
php bin/console cache:clear

# Charger à nouveau les données de démonstration
php bin/console doctrine:fixtures:load -n

# Lancer les tests
php bin/phpunit
```

Pour préparer entièrement la base de test :

```bash
composer db-test
```





