# Technical Test

## Installation

Install PHP dependencies:

```sh
composer install
```

Setup configuration:

```sh
cp .env.example .env
```

Setup configuration MongoDB database, Firebase, Mailgun, Sentry laravel. Simply update your configuration accordingly.

```sh
touch .env
```

Run test phpunit:

```sh
"vendor/bin/phpunit"
```

Run the dev server (the output will give the address):

```sh
php -S localhost:8000 public/index.php  
```

See Swagger API documentation:

```sh
http://localhost:8080/api/documentation
```

See Postman API documentation:

```sh
https://documenter.getpostman.com/view/13222135/UVkjuH4d
```
