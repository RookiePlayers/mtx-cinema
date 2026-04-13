COMPOSE_FILE := dockerfile-compose.yml
COMPOSE := docker compose -f $(COMPOSE_FILE)
NETWORK := mtx_cinema_network

.PHONY: up down build rebuild destroy network logs logs-api logs-api-100 worker-logs migrate dev new-migration worker worker-restart

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

build: network
	$(COMPOSE) build

logs:
	$(COMPOSE) logs -f app

logs-api:
	tail -f storage/logs/laravel.log

logs-api-100:
	tail -n 100 -f storage/logs/laravel.log

worker-logs:
	$(COMPOSE) logs -f worker

migrate:
	$(COMPOSE) exec app php artisan migrate

new-migration:
	$(COMPOSE) exec app php artisan make:migration $(name)

worker:
	$(COMPOSE) up -d worker

worker-restart:
	$(COMPOSE) restart worker

dev: up
	npm run dev

rebuild: network
	$(COMPOSE) down
	$(COMPOSE) build --no-cache
	$(COMPOSE) up -d

destroy:
	$(COMPOSE) down --volumes --remove-orphans
	@if docker network inspect $(NETWORK) >/dev/null 2>&1; then \
		docker network rm $(NETWORK); \
	else \
		echo "Docker network $(NETWORK) does not exist."; \
	fi

network:
	@if docker network inspect $(NETWORK) >/dev/null 2>&1; then \
		echo "Docker network $(NETWORK) already exists."; \
	else \
		docker network create $(NETWORK); \
	fi
