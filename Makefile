.PHONY: help build up down restart logs shell composer symfony-new

help: ## Afficher cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Construire les images Docker
	docker compose build --no-cache

up: ## Démarrer les containers
	docker compose up -d

down: ## Arrêter les containers
	docker compose down

restart: down up ## Redémarrer les containers

logs: ## Afficher les logs
	docker compose logs -f

shell: ## Accéder au shell du container PHP
	docker compose exec php bash

symfony-new: ## Créer un nouveau projet Symfony
	docker compose up -d php
	docker compose exec php symfony new /var/www/symfony --version=lts --webapp
	docker compose down
	@echo "Projet Symfony créé ! Exécutez 'make up' pour démarrer."

composer-install: ## Installer les dépendances Composer
	docker compose exec php composer install

db-create: ## Créer la base de données
	docker compose exec php php bin/console doctrine:database:create

db-migrate: ## Exécuter les migrations
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

cache-clear: ## Vider le cache Symfony
	docker compose exec php php bin/console cache:clear

clean: ## Supprimer les containers et volumes
	docker compose down -v

setup: build symfony-new up ## Installation complète (build + symfony + up)
