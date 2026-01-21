.PHONY: help build test test-unit test-feature test-coverage test-types lint clean

.DEFAULT_GOAL := help

help:
	@echo "Usage: make [command]"
	@echo ""
	@echo "Commands:"
	@echo ""
	@echo "  build          Build the Docker image"
	@echo "  test           Run all quality checks (unit, feature, types, lint)"
	@echo "  test-unit      Run unit tests"
	@echo "  test-feature   Run feature tests (requires Azurite)"
	@echo "  test-coverage  Run tests with coverage report"
	@echo "  test-types     Run static analysis (PHPStan)"
	@echo "  lint           Run linter (Pint)"
	@echo "  clean          Remove containers and build artifacts"
	@echo ""

.docker-built: Dockerfile compose.yaml
	docker compose build
	touch .docker-built

build: .docker-built

test: .docker-built
	docker compose run --rm php composer test

test-unit: .docker-built
	docker compose run --rm php composer test:unit

test-feature: .docker-built
	docker compose run --rm php composer test:feature

test-coverage: .docker-built
	docker compose run --rm php composer test:coverage

test-types: .docker-built
	docker compose run --rm php composer test:types

lint: .docker-built
	docker compose run --rm php composer lint

clean:
	docker compose down
	rm -rf build/ .docker-built
