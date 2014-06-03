<?php
$fileDir = dirname(__FILE__);
$dsn = 'sqlite:tempDB.sqlite';
try {
	$dbh = new PDO($dsn, array(
		PDO::ATTR_PERSISTENT => $type,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_AUTOCOMMIT => TRUE,
		PDO::ATTR_EMULATE_PREPARES => FALSE
	));
} catch(PDOException $e) {
	exit($e -> getMessage());
}

var_dump($dsn);
