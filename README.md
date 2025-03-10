# Setup Instructions for Running the Application

## 1. Install Laravel Dependencies
Run the following command to install Laravel backend dependencies:

```sh
composer install
```

## 2. Install Frontend Dependencies
Run the following command to install frontend assets:

```sh
npm install
```

## 3. Setup Environment File
Copy the example environment file:

```sh
cp .env.example .env
```

## 4. Generate Application Key
Generate the Laravel application key:

```sh
php artisan key:generate
```

## 5. Run Database Migrations
Run database migrations to set up tables:

```sh
php artisan migrate
```

## 6. Seed the Database
Seed the database with initial data:

```sh
php artisan db:seed
```

## 7. Fetching RSS News
### Schedule (Hourly):
Run the scheduler to fetch news automatically every hour:

```sh
php artisan schedule:work
```

### Manually:
Fetch news manually using the command:

```sh
php artisan fetch:news
```

## 8. Compile Frontend Assets
Run the following command to compile frontend assets:

```sh
npm run dev
```

## 9. Start the Application
Run the Laravel development server:

```sh
php artisan serve
