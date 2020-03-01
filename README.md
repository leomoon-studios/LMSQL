# LMSQL
LMSQL is a simple MySQL class that uses PDO. With LMSQL you don't need to write a lot of code to get results from a table and it's very easy to use.


## Documentation

## Installation
```php
require_once('mysql.class.php');
```

## Commands
### Connect
```php
$mysql = new LMSQL('localhost', 'root', '123456', 'databaseName', true);
```
or
```php
$mysql = new LMSQL('localhost', 'root', '123456', 'databaseName');

$mysql->connect();
```
default charset is utf8.

### select
Get data from table with where clause, limit, order and custom index

@return array

- Simple usage
```php
$data = $mysql->select(['table'=>'news']);
```

- With fields, where clause, order and limit.
```php
$data = $mysql->select([
                        'table'=>'tableName', 
                        'fields'=>'id, title, body', 
                        'where'=>['category'=>'news'], 
                        'order'=>'id DESC', 
                        'limit'=>10
                        ]);
```

- With custom array index
```php
$data = $mysql->select([
                      'table'=>'tableName', 
                      'index'=>['column'=>'type', 'multi'=>true]
                      ]);
```

- With custom SQL
```php
$data = $mysql->select(["sql"=>"SELECT news_title FROM news, category WHERE news_category = category_id and category_type = 'active'"]);
```

### load
Get one row from table

@return array

```php
$data = $mysql->load('tableName', ['table'=>'news', 'where'=>'id = 1']);
```

### insert
Insert data to table

```php
$mysql->insert(['table'=>'users', 'values'=>['fullname'=>'Arash', 'company'=>'Leomoon']]);
```

### update
Update rows

```php
$mysql->update(['table'=>'users', 'where'=>['id'=>2218], 'values'=>['name'=>'Amin']]);
```

### delete
Delete rows

```php
$mysql->delete(['table'=>'tableName', 'where'=>['id', '817']]);
```

### total
Get total rows

@return int

```php
$mysql->total(['table'=>'tableName', 'where'=>'id > 5']);
```
or
```php
$mysql->count(['table'=>'tableName', 'where'=>['status'=>'active', 'category'=>'something']]);
```

### insertId
Get the last inserted id

```php
$mysql->insertId();
```
### search
Search in all fields
```php
$mysql->search([
        'table'=>'news',
        'word'=>'%fake%'
    ]);
```
Search in specific fields
```php
$mysql->search([
        'table'=>'news',
        'word'=>'%fake%',
        'searchs'=>['title']
    ]);
```

### schema
Show tables of current DB:

```php
$mysql->schema();
```
Show columns:
```php
$mysql->schema(['table'=>'YourTableName']);
```

### exec
Execute your custom sql query

```php
$mysql->exec($sql);
```
