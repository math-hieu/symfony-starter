.PHONY: run stop start down db

run:
	docker-compose up -d
	docker-compose exec php chown -R www-data:www-data app/cache && rm -rf app/cache/*
	docker-compose exec php chown -R www-data:www-data app/logs
	docker-compose exec php php app/console doctrine:schema:update --force 2>/dev/null; true
	docker-compose exec php php app/console cache:clear 2>/dev/null; true
	docker-compose exec php php app/console doctrine:fixture:load
stop:
	docker-compose stop
start:
	docker-compose up -d
down:
	docker-compose down

db:
	docker-compose exec db mysql -uroot -p"root"
