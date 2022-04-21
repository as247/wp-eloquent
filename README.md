# As247 WpEloquent

> This package extract from laravel 8.9

The WpEloquent component is a full database toolkit for PHP, providing an expressive query builder, ActiveRecord style ORM, and schema builder. It currently supports MySQL, Postgres, SQL Server, and SQLite.

## Installing

```SH
composer require as247/wp-eloquent
```

## Usage Instructions

First, boot Application with a connection.

**Use $wpdb connection**
```PHP
use As247\WpEloquent\Application;

Application::bootWp();
````
**Use separated connection**
```PHP
use As247\WpEloquent\Application;

Application::boot([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'database',
    'username'  => 'root',
    'password'  => 'password',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
````

Once the Application booted. You may use it like so:

**Using The Query Builder**

```PHP
use As247\WpEloquent\Support\Facades\DB;
$users = DB::table('users')->where('votes', '>', 100)->get();
```
Other core methods may be accessed directly from the Capsule in the same manner as from the DB facade:
```PHP
use As247\WpEloquent\Support\Facades\DB;
$results = DB::select('select * from users where id = ?', [1]);
```

**Using The Schema Builder**

```PHP
use As247\WpEloquent\Support\Facades\Schema;
Schema::create('users', function ($table) {
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

For further documentation on using the various database facilities this library provides, consult the [Laravel framework documentation](https://laravel.com/docs/8.x/eloquent).

## Version 1.0
You are looking for v1.x? Check it here
https://github.com/as247/wp-eloquent/tree/1.x