# Symfony Docker - Guide d'installation

Cette configuration Docker vous permet de lancer une application Symfony en local avec PHP 8.3, Nginx, MySQL et phpMyAdmin.

## 📋 Prérequis

- Docker installé sur votre machine
- Docker Compose installé

## 🚀 Installation

### 1. Créer le projet Symfony

Depuis le répertoire racine du projet, exécutez :

```bash
# Démarrer uniquement le container PHP pour créer le projet
docker-compose up -d php

# Créer le projet Symfony dans le container
docker-compose exec php symfony new /var/www/symfony --version=lts --webapp

# OU pour une version spécifique :
# docker-compose exec php composer create-project symfony/skeleton:"7.2.*" /var/www/symfony
# docker-compose exec php composer require webapp

# Arrêter le container
docker-compose down
```

**Alternative** : Si vous avez déjà un projet Symfony, placez-le simplement dans le dossier `symfony/` à la racine.

### 2. Démarrer tous les services

```bash
docker-compose up -d
```

### 3. Installer les dépendances (si nécessaire)

```bash
docker-compose exec php composer install
```

### 4. Configurer la base de données

Éditez le fichier `symfony/.env` et modifiez la ligne DATABASE_URL :

```env
DATABASE_URL="mysql://symfony:symfony@mysql:3306/symfony_db?serverVersion=8.0"
```

Puis créez la base de données :

```bash
docker-compose exec php php bin/console doctrine:database:create
docker-compose exec php php bin/console doctrine:schema:update --force
```

## 🌐 Accès aux services

- **Application Symfony** : http://localhost:8080
- **phpMyAdmin** : http://localhost:8081
  - Serveur : `mysql`
  - Utilisateur : `symfony`
  - Mot de passe : `symfony`

## 🛠️ Commandes utiles

### Accéder au container PHP
```bash
docker-compose exec php bash
```

### Voir les logs
```bash
docker-compose logs -f
docker-compose logs -f php    # Logs PHP uniquement
docker-compose logs -f nginx  # Logs Nginx uniquement
```

### Arrêter les containers
```bash
docker-compose down
```

### Arrêter et supprimer les volumes
```bash
docker-compose down -v
```

### Reconstruire les images
```bash
docker-compose build --no-cache
```

### Commandes Symfony
```bash
# Créer un controller
docker-compose exec php php bin/console make:controller

# Créer une entité
docker-compose exec php php bin/console make:entity

# Migrations
docker-compose exec php php bin/console make:migration
docker-compose exec php php bin/console doctrine:migrations:migrate

# Cache
docker-compose exec php php bin/console cache:clear
```

### Commandes Composer
```bash
docker-compose exec php composer require <package>
docker-compose exec php composer update
```

## 📁 Structure du projet

```
.
├── docker/
│   └── nginx/
│       └── default.conf      # Configuration Nginx
├── symfony/                   # Votre application Symfony
├── docker-compose.yml         # Orchestration des services
├── Dockerfile                 # Image PHP personnalisée
└── README.md                  # Ce fichier
```

## 🔧 Configuration

### Ports utilisés
- **8080** : Nginx (application web)
- **8081** : phpMyAdmin
- **3306** : MySQL

Si ces ports sont déjà utilisés, modifiez-les dans le fichier `docker-compose.yml`.

### Base de données
- Hôte : `mysql`
- Port : `3306`
- Base : `symfony_db`
- Utilisateur : `symfony`
- Mot de passe : `symfony`
- Root password : `root`

## 🐛 Résolution de problèmes

### Permission denied
```bash
sudo chown -R $USER:$USER symfony/
```

### Le site ne s'affiche pas
1. Vérifiez que tous les containers sont démarrés : `docker-compose ps`
2. Vérifiez les logs : `docker-compose logs`
3. Vérifiez que le projet Symfony est bien dans `symfony/public/index.php`

### Problèmes de base de données
```bash
# Recréer la base de données
docker-compose exec php php bin/console doctrine:database:drop --force
docker-compose exec php php bin/console doctrine:database:create
docker-compose exec php php bin/console doctrine:schema:update --force
```

## 📚 Ressources

- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Documentation Docker](https://docs.docker.com/)
- [Documentation Docker Compose](https://docs.docker.com/compose/)

## 🎉 Bon développement !
