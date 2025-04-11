
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

### Апи
## Создание плантации
```shell script
curl --location 'http://agroplan/api/v1/plantation/add' \
--form 'name="name"'
```
## Редактирование плантации
```shell script
curl --location 'http://agroplan/api/v1/plantation/edit' \
--form 'name="new_name" id=1'
```