[< Back to summary](../README.md)

# 📚 Database

Database connection is made through the [`Database`](../../src/Classes/Data/Database.php) component (which uses `PDO`)

## Using the database

Before using the database, we have to configure its connection in `sharp.php`

```php
return [
	new DatabaseConfiguration(
		driver: "mysql",
		database: "database",
		host: "localhost",
		port: 3306,
		user: "root",
		password: null,
		charset: "utf8",
	),
]
```

> [!NOTE]
> The default database config is `driver=mysql, host=localhost, port=3306, user=root`, so you only have to configure `database` and `password` if working on a local MySQL database

Then, your database usage is done through three main methods

```php
$db = Database::getInstance();

# Used to build a query string
$query = $db->build(
    'INSERT INTO ship (name) VALUES ({})',
    ['PHP Bounty']
);

# Used to directly fetch rows
$results = $db->query($query);

$id = $db->lastInsertId();
```

### Additional Database Properties

```php
// build() binding can take arrays of data
$results = $db->query(
    'SELECT id FROM ship WHERE name IN {}',
    [['Above the code', 'PHP Bounty']]
);

// Check if a table exists (return true/false)
$db->hasTable('ship_order');

// Check if a field in a table exists (return true if both exists)
$db->hasField('ship_order', 'fk_ship');
```

### Working with SQLite

`Database` also support SQLite connections ! Here is an example of configuration

```php
return [
	new DatabaseConfiguration(
		driver: "sqlite",
		database: "myDatabase.db",
	),
]
```

This config will create a `Storage/myDatabase.db` file with your data inside

[< Back to summary](../README.md)