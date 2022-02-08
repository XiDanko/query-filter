# Laravel Query Filter

## Installation

---
```bash
composer require xidanko/query-filter
```

Laravel will discover package service provider automatically

## Basic Usage

---

First you have to add `XiDanko\QueryFilter\HasFilter` trait to the desired model.

This will register `useFilter` local scope to your model.

Now create new filter using this artisan command
```bash
php artisan make:filter <name>
```
This will create a new filter class in `App\Filters` directory, there you can define all your filter methods.

## Example

---

After using the trait in your desired model and creating the filter class you can hype hint your class in any controller method:
```php
public function index(Filter $yourFilterClass)
{
    return User::useFilter($yourFilterClass)->get();
}
```
