# PHP-Library
Some useful php libraries for easing code :)

## Working with DB (PDO)


### Instantiating Object and setting table
```php
$db = new DB();
$db->table('tablename');
```

### Or Do it Directly (DID)
```php
// Fetching results
DB::table('tablename')->get(); // will fetch every result from table
```

### For Complex Querying
```php
$query = DB::table('tablename')->query("SELECT col, FROM @table . . .");

// @table refer to current table reference, if set with table() method.
```

### Fetching results
```php

/**
 * obj | array stash($type = '{}' || '[]')
 * obj | array record($type = '{}' || '[]')
 *
 * $type parameter can be '{}' fetching results as object or '[]' for arrays
 * $type parameter by default set to '{}'
 */

// single result
$result = $query->stash();

// for heap of results
$result = $query->record();
```
