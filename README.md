# testTasks
Тестовое о задачах с пользователями


В терминале должны быть доступны команды composer, npm, php

Поместить содержимое архива в папку.

Перейти в терминале в папку командой:
cd /d [путь до папки].

Поочерёдно выполнить команды:
composer install
php artisan migrate ( ввести yes )
php artisan db:seed --class=TasksSeeder
npm install
npm run dev



Данные для авторизации пользователей email:пароль
ivan@test-r.ru:2345
irina@test-r.ru:1234
elena@test-r.ru:9876
