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

$db = new DB();
$query = $db->table('tablename')->get();

// single result
$result = $query->stash();

// for heap of results
$result = $query->record();
```

### Fetching data for single columns
```php
DB::table('tablename')->get('column')
```

### Fetching multiple columns of your choice
```php
DB::table('tablename')->get([
  'cols' => ['col1', 'col2', 'col3', 'col4']
])
```

### Making use of table reference for every query
```php
$db = new DB();
$db->table('tablename') // set table

// tablename will be set automatically as table reference for every query you made
$query1 = $db->get(); // no need to set table again
$result = $query1->stash();

$query2 = $db->where('col1', 'value')->get('col1'); // same for here
$result = $query2->stash();
```


## Working with Assets

To link css and js file per file basis:
```
Assets::bootstrap([
	'home' => [
		'css' => [
        "path/to/file.js" ...
     ],
		'js' => [
			  "path/to/file.js" ...
		]
	]
]);
