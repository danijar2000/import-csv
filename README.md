## Установка и запуск
1) Скачать проект
``` bash
git clone https://github.com/danijar2000/import-csv.git import
```
2) Установить проект
``` bash
cd import
composer install --no-dev
cp .env.example .env
php artisan key:generate
sudo chmod -R 777 storage/
sudo chmod -R 777 bootstrap/cache/
```

3) Настроить проект
``` bash
nano .env
```

4) Миграция таблиц
``` bash
php artisan migrate
```

5) Перенос пример файл. Либо поместите свой файл.

``` bash
cp random.csv storage/app/imports/random.csv
```

7) Run import command
``` bash
php artisan import
```
