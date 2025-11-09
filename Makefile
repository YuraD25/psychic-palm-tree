.PHONY: build up down logs shell migrate test clean swagger

# Build and start containers
build:
	docker-compose up --build -d && sleep 5 && docker-compose exec php-fpm php yii migrate --interactive=0

# Start containers
up:
	docker-compose up -d

# Stop containers
down:
	docker-compose down

# View logs
logs:
	docker-compose logs -f

# Access PHP container shell
shell:
	docker-compose exec php-fpm sh

# Run migrations
migrate:
	docker-compose exec php-fpm php yii migrate --interactive=0

# Run tests (when implemented)
test:
	docker-compose exec php-fpm vendor/bin/phpunit

# Open Swagger UI in browser
swagger:
	@echo "Opening Swagger UI at http://localhost/swagger"
	@command -v open >/dev/null 2>&1 && open http://localhost/swagger || \
	 command -v xdg-open >/dev/null 2>&1 && xdg-open http://localhost/swagger || \
	 echo "Please open http://localhost/swagger in your browser"

# Clean up everything
clean:
	docker-compose down -v
	docker system prune -f