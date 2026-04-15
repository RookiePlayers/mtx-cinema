COMPOSE_FILE := dockerfile-compose.yml
COMPOSE := docker compose -f $(COMPOSE_FILE)
NETWORK := mtx_cinema_network

.PHONY: up down build rebuild destroy network logs logs-api logs-api-100 worker-logs web-ui-logs migrate dev new-migration worker worker-restart

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

build:
	$(COMPOSE) build

logs:
	$(COMPOSE) logs -f app

logs-api:
	$(COMPOSE) exec app sh -lc "tail -f storage/logs/laravel.log"

logs-api-100:
	$(COMPOSE) exec app sh -lc "tail -n 100 -f storage/logs/laravel.log"

worker-logs:
	$(COMPOSE) logs -f worker

web-ui-logs:
	$(COMPOSE) logs -f web-ui

migrate:
	$(COMPOSE) exec app php artisan migrate

new-migration:
	$(COMPOSE) exec app php artisan make:migration $(name)

worker:
	$(COMPOSE) up -d worker

worker-restart:
	$(COMPOSE) restart worker

dev: up

rebuild:
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
	@echo "Docker Compose manages the $(NETWORK) network automatically."
