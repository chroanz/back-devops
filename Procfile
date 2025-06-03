release: php artisan key:generate --force && php artisan jwt:secret --force && php artisan migrate:fresh --seed --force
web: heroku-php-apache2 public/
