<p align="center"><img src="https://blog.pleets.org/img/articles/cashier-machine.png" height="150"></p>

<p align="center">
<a href="https://travis-ci.com/darioriverat/cash-register"><img src="https://travis-ci.com/darioriverat/cash-register.svg?branch=main" alt="Build Status"></a>
</p>

# Cash Register API

You can download this project as follows.

```bash
git clone https://github.com/darioriverat/cash-register
```

# 1. Installation

This application was developed with Laravel 8x, most of the following steps are related to laravel
installation and configuration.

## 1.1 Requirements

Additional to Laravel 8x requirements, you will need to make sure your server meets the following requirements.

- PHP >= 7.4

## 1.2 Set up

Set up permission of `storeage` and `bootstrap/cache` directories.

```bash
chmod -R a+w storage
chmod a+w bootstrap/cache
```

Let's copy the `.env.example` to `.env`.

```bash
cp .env.example .env
```

Finally, set up `DB_*` and other env vars as you need.

## 1.3 Step-by-Step Installation

This chapter going to explain to you how to install this application from scratch.

## 1.3.1 Base installation

Make sure you have composer installed in your machine and execute the following command to install all
dependencies.

```bash
composer install
```

Then generate the application key with the following.

```bash
php artisan key:generate
```

Finally, create the database schema and basic data executing the following command.

```bash
php artisan migrate
```

## 1.3.2 Creating sample user and token data

To create your first sample user and its token you could run the following seeder

```bash
php artisan db:seed --class=UserSampleSeeder
```

Then you can use the following token to log in on the api.

```text
token: 1|klvN2VKKhJi06oigREYDMOtJAyKbyZAZpbaQxvvM
```

## 1.4 Creating Cashier machines

To create a cashier machine you can use the following command

```bash
php artisan make:machine machineName
```

The above command creates the machine and its balance.
