## Wp Eloquent Database

> This package extract from laravel 6.x

The WpEloquent Database component is a full database toolkit for PHP, providing an expressive query builder, ActiveRecord style ORM, and schema builder. It currently supports MySQL, Postgres, SQL Server, and SQLite. It also serves as the database layer of the Laravel PHP framework.

### Installing

```composer require as247/wp-eloquent```

### Usage Instructions

First, create a new "Capsule" manager instance. Capsule aims to make configuring the library for usage outside of the Laravel framework as easy as possible.

```PHP
use As247\WpEloquent\Capsule\Manager as Capsule;

Capsule::bootWp();
```
Once the Capsule instance has been registered. You may use it like so:

**Using The Query Builder**

```PHP
$users = Capsule::table('users')->where('votes', '>', 100)->get();
```
Other core methods may be accessed directly from the Capsule in the same manner as from the DB facade:
```PHP
$results = Capsule::select('select * from users where id = ?', [1]);
```

**Using The Schema Builder**

```PHP
Capsule::schema()->create('users', function ($table) {
    $table->increments('id');
    $table->string('email')->unique();
    $table->timestamps();
});
```

**Using The Eloquent ORM**

```PHP
class User extends As247\WpEloquent\Database\Eloquent\Model {}

$users = User::where('votes', '>', 1)->get();
```

For further documentation on using the various database facilities this library provides, consult the [Laravel framework documentation](https://laravel.com/docs).
