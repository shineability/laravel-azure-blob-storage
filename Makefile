.PHONY: help build install .deps test test-unit test-feature test-coverage test-types lint clean

.DEFAULT_GOAL := help

help:
	@echo "Usage  make [command]"
	@echo ""
	@echo "Commands"
	@echo ""
	@echo "  build          Build the Docker image"
	@echo "  install        (Re)install dependencies in the container"
	@echo "  test           Run all quality checks (unit, feature, types, lint)"
	@echo "  test-unit      Run unit tests"
	@echo "  test-feature   Run feature tests (requires Azurite)"
	@echo "  test-coverage  Run tests with coverage report"
	@echo "  test-types     Run static analysis (PHPStan)"
	@echo "  lint           Run linter (Pint)"
	@echo "  clean          Remove containers and build artifacts"
	@echo ""

build:
	docker compose build

install:
	docker compose build -q
	docker compose run --rm php composer update --no-interaction --no-progress

# Build the image if needed, then install dependencies when the container's vendor/ is missing or older than composer.json/Dockerfile
.deps:
	docker compose build -q
	docker compose run --rm php sh -c '[ vendor/autoload.php -nt composer.json ] && [ vendor/autoload.php -nt Dockerfile ] || composer update --no-interaction --no-progress'

test: .deps
	docker compose run --rm php composer test

test-unit: .deps
	docker compose run --rm php composer test:unit

test-feature: .deps
	docker compose run --rm php composer test:feature

test-coverage: .deps
	docker compose run --rm php composer test:coverage

test-types: .deps
	docker compose run --rm php composer test:types

lint: .deps
	docker compose run --rm php composer lint

clean:
	docker compose down --volumes
	rm -rf build/
