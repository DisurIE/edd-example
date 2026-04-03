APP_SERVICE=app
DB_SERVICE=db

.PHONY: help up down destroy restart ps logs logs-app logs-db shell demo migrate fresh composer-install mysql db-creds

help:
	@echo "Доступные команды:"
	@echo "  make up             - поднять docker-окружение"
	@echo "  make down           - остановить контейнеры"
	@echo "  make destroy        - остановить контейнеры и удалить volume с БД"
	@echo "  make restart        - перезапустить окружение"
	@echo "  make ps             - показать состояние контейнеров"
	@echo "  make logs           - показать общие логи"
	@echo "  make logs-app       - показать логи приложения"
	@echo "  make logs-db        - показать логи MySQL"
	@echo "  make shell          - открыть shell в контейнере приложения"
	@echo "  make demo           - запустить demo:order-flow"
	@echo "  make migrate        - выполнить миграции"
	@echo "  make fresh          - пересоздать таблицы и прогнать demo"
	@echo "  make composer-install - выполнить composer install в контейнере"
	@echo "  make mysql          - открыть mysql client внутри контейнера БД"
	@echo "  make db-creds       - показать креды для PhpStorm"

up:
	docker compose up --build -d

down:
	docker compose down

destroy:
	docker compose down -v

restart: down up

ps:
	docker compose ps

logs:
	docker compose logs --tail=200

logs-app:
	docker compose logs --tail=200 $(APP_SERVICE)

logs-db:
	docker compose logs --tail=200 $(DB_SERVICE)

shell:
	docker compose exec $(APP_SERVICE) sh

demo:
	docker compose exec $(APP_SERVICE) php artisan demo:order-flow

migrate:
	docker compose exec $(APP_SERVICE) php artisan migrate

fresh:
	docker compose exec $(APP_SERVICE) php artisan migrate:fresh --force
	docker compose exec $(APP_SERVICE) php artisan demo:order-flow

composer-install:
	docker compose exec $(APP_SERVICE) composer install

mysql:
	docker compose exec $(DB_SERVICE) mysql -uedd_user -pedd_password edd_example

db-creds:
	@echo "Host: 127.0.0.1"
	@echo "Port: 3307"
	@echo "Database: edd_example"
	@echo "User: edd_user"
	@echo "Password: edd_password"
