# Symfony REST API Server

This repository contains a Symfony-based REST API server for managing users and products, with JWT authentication.

## Local Setup

### Requirements

- **PHP**: Version 8.0 or above
- **PostgreSQL**: Version 13.0 or above
- **Composer**: Version 2.0 or above
- **Symfony CLI**: Follow installation instructions [here](https://symfony.com/download)

### Clone and Setup

```sh
git clone git@github.com:emmadedayo/gitstart.git
cd gitstart
```

### Configure Environment

1. Create a `.env` file in the project root based on `.env.example`.
2. Set your local PostgreSQL database credentials in `DATABASE_URL`.

### Install Dependencies

```sh
composer install
```

### Database Setup

```sh
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console lexik:jwt:generate-keypair
```

### Start Symfony Server

```sh
symfony server:start
```

## Docker Setup

### Requirements

- **Docker**
- **Docker Compose**

### Clone and Setup

```sh
git clone git@github.com:emmadedayo/gitstart.git
cd gitstart
```

### Configure Docker Environment

1. Create a `.env` file in the project root based on `.env.example`.
2. Update `DATABASE_URL` to match Docker PostgreSQL container settings.

### Build Docker Containers

```sh
docker compose -f ./docker-compose.yml up --build
```

### Database Migration

```sh
docker exec -it gitstart-web-1 php bin/console doctrine:migrations:migrate
```

### Access Docker Endpoint

- Access the application at: http://localhost:8080

## Testing Documentation

### Configure Test Environment

1. Update `.env` for the test environment with your local PostgreSQL database details.
2. Create and configure the test database:

```sh
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:schema:create
php bin/console --env=test doctrine:fixtures:load
```

### Run PHPUnit Tests

```sh
php bin/phpunit
```

## Documentation

The Postman documentation for this project can be found [here](https://documenter.getpostman.com/view/3080167/2sA3kPpPoR).

---
