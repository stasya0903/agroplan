
### Сборка контейнеров
```shell script
docker-compose build
```

### Запуск контейнеров
```shell script
docker-compose up -d &&
docker-compose exec app bash -c "export COMPOSER_HOME=/data/agroplan && composer install" 
```

### Наполнение базы
```shell script
docker-compose exec app bash -c "php bin/console doctrine:migrations:migrate" 
```