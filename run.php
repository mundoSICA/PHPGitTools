#!/usr/bin/env php
<?php
if (!defined('PHPGIT_RUNNING'))
{
	if( strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ){
		define('OS','WIN');
	}else{
		define('OS','UNIX');
	}
	define('PHPGIT_RUNNING', 1);
	define('DS',DIRECTORY_SEPARATOR);
	define('PHPGIT_PATH', dirname(__FILE__) );
	include_once PHPGIT_PATH . DS . 'lib' . DS . 'GitPHP.php';
}
	
class GitDispatcher {
	
	public function __construct($args = array()) {
		set_time_limit(0);
		$defaults = array(
			'option' => 'Help'
		);
		switch($args[1]){
			case 'service':
					include_once PHPGIT_PATH . DS . 'lib' . DS . 'services' . DS . 'Service.php';
					include_once PHPGIT_PATH . DS . 'lib' . DS . 'services' . DS . $args[2] . '.php';
					$service = new $args[1]($args);
					break;
			case 'hook': 
					include_once PHPGIT_PATH . DS . 'hooks' . DS . 'Service.php';
					include_once PHPGIT_PATH . DS . 'hooks' . DS . $args[2].'.php';
					break;
			default:
					die('elija una opcion valida');
		}
	}
	
	public static function run($argv) {
		$dispatcher = new GitDispatcher($argv);
	}
}

return GitDispatcher::run($argv);


?>
