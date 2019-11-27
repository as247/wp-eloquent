<?php


namespace As247\WpEloquent\Capsule;
use As247\WpEloquent\Database\Capsule\Manager as CapsuleManager;
use As247\WpEloquent\Events\Dispatcher;
use As247\WpEloquent\Container\Container;
class Manager extends CapsuleManager{

	public static function bootWp(){
		$capsule = new static();
		$capsule->addConnection([],'wp');
		$capsule->getDatabaseManager()->extend('wp',function(){
			return WpConnection::instance();
		});
		$capsule->getDatabaseManager()->setDefaultConnection('wp');
		$capsule->setEventDispatcher(new Dispatcher(new Container));
		$capsule->setAsGlobal();
		$capsule->bootEloquent();
	}
}