init:
	docker-compose up -d --build
	docker-compose exec php composer install
	docker-compose exec php cp .env.example .env
	docker-compose exec php cp .env.example .env.testing
	docker-compose exec php sed -i 's/DB_DATABASE=laravel_db/DB_DATABASE=test_database/g' .env.testing
	@echo "Waiting for database connection..."
	sleep 15
	docker-compose exec php php artisan key:generate
	docker-compose exec php php artisan key:generate --env=testing
	docker-compose exec mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS test_database; GRANT ALL PRIVILEGES ON test_database.* TO 'laravel_user'@'%';"
	docker-compose exec php php artisan migrate:fresh --seed
	docker-compose exec php php artisan migrate:fresh --env=testing
	docker-compose exec php chmod -R 777 storage bootstrap/cache
