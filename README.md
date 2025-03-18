

This repository contains a fully Dockerized Laravel + Vue.js web application.

---

## Prerequisites

Ensure you have the following installed on your system:

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

---

## Clone the Repository

First, clone this repository:

```sh
git clone https://github.com/avesovich/docker-report-app.git
cd docker-report-app
```

---

## Build and Start Containers

Run the following command to build and start the Docker containers:

```sh
docker-compose build --no-cache
docker-compose up -d
```

This will:
- Build and start the Laravel, Vue.js, MySQL, and Nginx containers.
- Install dependencies and set up the environment.

---

## Generate Application Key

Before generating the application key, copy .env.example to .env:

```sh
docker exec -it laravel_app cp /var/www/html/.env.example /var/www/html/.env
```
Then, generate the Laravel application key:
```sh
docker exec -it laravel_app php artisan key:generate
```

---

## Run Database Migrations and Seeders

To set up the database schema and seed initial data, run:

```sh
docker exec -it laravel_app php artisan migrate --seed
```

---

## Fetch Latest News

Manually fetch RSS news updates:

```sh
docker exec -it laravel_app php artisan fetch:news
```

---

## Run Laravel Scheduler

The application relies on scheduled tasks. Start the Laravel scheduler:

```sh
docker exec -it laravel_app php artisan schedule:work
```

---

## Access the Application

Once everything is up and running:

- **Backend API (Laravel)**: [http://localhost](http://localhost)
- **Frontend (Vue.js via Vite)**: [http://localhost:5173](http://localhost:5173)
- **MySQL Database**: `mysql -h 127.0.0.1 -P 3306 -u laravel -psecret`

---

## Stopping the Containers

To stop all running containers:

```sh
docker-compose down
```

To stop containers and **remove volumes** (⚠️ This will erase the database):

```sh
docker-compose down -v
```

---

## Restarting Containers

If you need to restart your app:

```sh
docker-compose restart
```

or fully rebuild:

```sh
docker-compose up -d --build
```
