<?php
require_once 'mysql.class.php';

$mysql = new LMSQL('localhost', 'USERNAME', 'PASSWORD', 'DB-NAME', true);

$mysql->update([
    'table'=>'tableName',
    'values'=>['title'=>'title updated', 'desc'=>'something'],
    'where'=>['id'=>3]
]);

?>