----------

# Getting started

## Installation

Please check the official laravel installation guide for server requirements before you start. [Official Documentation](https://laravel.com/docs/10.x/installation)

Clone the repository

    git clone https://github.com/Angelog21/smartFPTest.git

Switch to the repo folder

    cd smartfptest

Install all the dependencies using composer

    composer install

Copy the example env file and make the required configuration changes in the .env file

    cp .env.example .env

Generate a new application key

    php artisan key:generate

Generate a new JWT authentication secret key

    php artisan jwt:generate

Run the database migrations with the seeds data (**Set the database connection in .env before migrating**)

    php artisan migrate --seed

Start the local development server

    php artisan serve

You can now access the server at http://localhost:8000

## Run Feature tests

Run the database migrations with the seeds data for test environment (**Set the database connection in .env.testing before migrating**)

    php artisan migrate --env=testing --seed

Run command to executing tests

    php artisan test

