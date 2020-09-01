<?php


namespace As247\WpEloquent\Capsule;
use As247\WpEloquent\Database\Capsule\Manager as CapsuleManager;
use As247\WpEloquent\Events\Dispatcher;
use As247\WpEloquent\Container\Container;
use As247\WpEloquent\Database\WpConnection;
use As247\WpEloquent\Support\Facades\Facade;

class Manager extends CapsuleManager{
	protected static $booted;
	public static function bootWp($useWpConnection=true){
		if(static::$booted){
			return static::$instance;
		}
		$capsule = new static();
		if($useWpConnection) {
			$capsule->addConnection([], 'wp');
			$capsule->getDatabaseManager()->extend('wp', function () {
				return WpConnection::instance();
			});
			$capsule->getDatabaseManager()->setDefaultConnection('wp');
		}else{
			global $wpdb;
			$dbuser     = defined( 'DB_USER' ) ? DB_USER : '';
			$dbpassword = defined( 'DB_PASSWORD' ) ? DB_PASSWORD : '';
			$dbname     = defined( 'DB_NAME' ) ? DB_NAME : '';
			$dbhost     = defined( 'DB_HOST' ) ? DB_HOST : '';
			$charset=$wpdb->charset;
			$collate=$wpdb->collate;
			$capsule->addConnection([
				'driver'    => 'mysql',
				'host'      => $dbhost,
				'database'  => $dbname,
				'username'  => $dbuser,
				'password'  => $dbpassword,
				'charset'   => $charset,
				'collation' => $collate,
				'prefix'    => $wpdb->base_prefix,
			]);
		}
		$app=$capsule->getContainer();
		$app->instance('db',$capsule->getDatabaseManager());
		Facade::setFacadeApplication($app);
		$capsule->setEventDispatcher(new Dispatcher(new Container));
		$capsule->setAsGlobal();
		$capsule->bootEloquent();
		return static::$instance;
	}

}
