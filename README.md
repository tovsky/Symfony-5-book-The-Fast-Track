# conferences guestbook

#### Запуск контейнеров
    ```docker-compose up -d```

#### Установка зависимостей 
```docker-compose exec fpm composer install```

#### 
    ```docker-compose exec fpm bin/console d:m:m -n```
    ```docker-compose exec fpm bin/console d:f:l -n```


#### Посмотреть доступные роуты 
    ```bin/console d:r```

#### Роуты примеры
    http://localhost/conference/sochi-2020
    http://localhost/admin/
    
    admin:admin
    
#### Запуск тестов
    docker-compose exec fpm doctrine:fixtures:load
    docker-compose exec fpm bin/phpunit

#### Запуск воркера в визуальном режиме 
    docker-compose exec fpm bin/console messenger:consume async -vv

#### Админка кролика доступна здесь (`guest:guest`)
    http://localhost:15672

#### Админка перехватчика писем доступна 
    http://localhost:1080


### Посмотреть заголовки. например кеш 
    curl -s -I -X GET http://127.0.0.1/
