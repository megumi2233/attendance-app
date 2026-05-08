init:
	docker-compose up -d --build
	docker-compose exec php composer install
	docker-compose exec php cp .env.example .env
	sleep 15
	docker-compose exec php php artisan key:generate
	docker-compose exec php php artisan migrate:fresh --seed
