# PHP-Library
Some useful php libraries for easing code :)

## Working with DB (PDO)


### Instantiating Object and setting table
```php
$db = new DB();
$db->table('tablename');
```

### OR Do it directly (DID)
```php
// Fetching results
DB::table('tablename')->get(); // will fetch every result from table
```
