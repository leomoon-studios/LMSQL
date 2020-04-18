<?php
require_once 'mysql.class.php';

$mysql = new LMSQL('localhost', 'USERNAME', 'PASSWORD', 'DB-NAME', true);

$mysql->insert([
    'table'=>'tableName',
    'values'=>['title'=>'Something', 'body'=>'Test Test']
]);

?>