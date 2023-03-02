# Laravel9 Web Crawler
Fresh file laravel 9 with docker compose:
  - nginx:stable-alpine
  - php8:stable-alpine
  - composer:latest

Docker-compose build nginx, mysql, and php8:
> docker-compose up -d --build

Update folder /vendor and Auto load
> docker exec -it backend composer update

run project on local browser
> localhost:80

if the file read permission is limited
>chmod -R 777 web/

delete container
> docker-compose down






