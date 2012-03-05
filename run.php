#!/usr/bin/env php
<?php
define('PHPGIT_RUNNING', 1);

$pathinfo =  pathinfo(__FILE__);
define('PHPGIT_PATH', $pathinfo['dirname'] );
include PHPGIT_PATH . '/lib/GitPHP.php';


?>
