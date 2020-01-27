## Requirements

- PHP
- Composer

## How to install

Run these commands:

`git clone git@github.com:paulmold/pokerhands.git`

`cd pokerhands`

`cp .env.example .env`

`composer install`

----
Edit file `.env` and add full path to `DB_DATABASE` variable

Example: `DB_DATABASE=C:\wamp\www\pokerhands\database\database.sqlite`

----

`php artisan migrate`

`php artisan key:generate`

`php artisan serve`

##How to use

You can check the website to http://127.0.0.1:8000

You need to register before you can use it.
