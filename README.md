# Skillshore Backend
<p align="center"><img src="public/logo.svg" height="50"></p>
This readme file provides instructions for setting up and running the backend API for Skillshore project

## Getting Started

### Installation
Please check the official laravel installation guide for server requirements before you start.

Clone the repository


```bash
  git clone git@github.com:kundankarna1994/skillshore-backend.git
```
Switch to the repo folder

```bash
cd skillshore-backend
```

Install all the dependencies using composer

```bash
composer install
```
Copy the example env file and make the required configuration changes in the .env file

```bash
cp .env.example .env
```

Generate a new application key
```bash
php artisan key:generate
```

Run the database migrations (Set the database connection in .env before migrating)

```bash
php artisan migrate
```

Start the local development server

```bash
php artisan serve
```

You can now access the server at http://localhost:8000

### TL;DR command list

```bash
git clone git@github.com:gothinkster/laravel-realworld-example-app.git
cd laravel-realworld-example-app
composer install
cp .env.example .env
php artisan key:generate
```

Make sure you set the correct database connection information before running the migrations Environment variables

```bash
php artisan migrate
php artisan serve
```

### Database seeding

Populate the database with seed data with which includes users. The user will be the admin of the application.

Open the UserSeeder and set the property values as per your requirement

```bash
database/seeders/UserSeeder.php
```
Run the database seeder and you're done
```bash
php artisan db:seed
```
**Note:** It's recommended to have a clean database before seeding. You can refresh your migrations at any point to clean the database by running the following command

```bash
php artisan migrate:refresh
```

The api can be accessed at http://localhost:8000/api

## Code Overview

### Folders

`app` - Contains all the Eloquent models<br>
`app/Http/Controllers/Api` - Contains all the api controllers<br>
`app/Http/Middleware` - Contains auth middleware<br>
`app/Http/Repositories` - Contains repositories<br>
`app/Http/Request` - Contains api form request<br>
`app/Http/Resource` - Contains api form response<br>

### Enivronment Variables
`.env` - Environment variables can be set in this file

**Note:** You can quickly set the database information and other variables in this file and have the application fully working.

## Testing API

Run the laravel development server

```bash
php artisan serve
```

The api can now be accessed at

```bash
http://localhost:8000/api
```

## Authentication

This application uses Laravel Sanctum to handle authentication. Bearer Token is used to store the login token. Please check the following sources to know more about Laravel Sanctum.

- https://laravel.com/docs/10.x/sanctum