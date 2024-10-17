[< Back to summary](../README.md)

# 📖 Models & Queries

Sharp philosophy on models is
> Your application don't have to dictate how your database schema should look like
>
> It is your application that must adapt itself to your structure

Models are class that represent a database table and extends from [`AbstractModel`](../../src/Classes/Data/AbstractModel.php)

The goal of sharp is to avoid writting manually any model, they can be generated automatically

## Generating models

To generate your models, launch this in your terminal

```bash
php do create-models
```

This will create models classes in `YourApp/Models`, with `snake_case` names transformed their `PascalCase` equivalent

> [!NOTE]
> So far, only two types of database are supported :
> - MySQL (+MariaDB)
> - SQLite
>
> A new adapter can be created by implementing a new `GeneratorDriver`

### Model Interaction

Let's say we have a `User` model which got this structure

```sql
CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    salt VARCHAR(100) NOT NULL
);
```

Here is how we can interact with the model

```php
User::getTable(); // Return "user"
User::getPrimaryKey(); // Return "id"
User::getFields(); // Return an array as `FieldName => DatabaseField` object
User::getFieldNames(); // Return ["id", "login", "password", "salt"]
User::getInsertables(); // Return ["login", "password", "salt"]

User::insert(); // Return a ModelQuery object ready to insert inside user table
User::select(); // Return a ModelQuery object ready to select from user table
User::update(); // Return a ModelQuery object ready to update user table
User::delete(); // Return a ModelQuery object ready to delete from user table

# Some examples of ModelQuery usage

$users = User::select()
->where("fk_country", 14)
->whereSQL("creation_date > DATESUB(NOW(), INTERVAL 3 MONTH)")
->limit(5)
->fetch();

$someUser = User::select()
->where("id", 168)
->first();

// Same as the previous query
$someUser = User::findId(168);

User::update()
->set("fk_type", 2)
->where("fk_type", 5)
->first();

User::delete()
->whereSQL("fk_type IN (1, 12, 52, 4)")
->order("id", "DESC")
->fetch();

# Collect data and put it in an object array
User::delete()
->whereSQL("fk_type IN (1, 12, 52, 4)")
->order("id", "DESC")
->toObjectArray();
```


## INSERT query

The only type of insert that is supported is `INSERT INTO ... VALUES ...`

```php
$query = new ModelQuery(UserData::class, ModelQuery::INSERT);
$query->setInsertField(["fk_user", "data"]);
$query->insertValues([1, "one-row"], [2, "another-one"]);

$res = $query->fetch();
$sql = $query->build();
```

## SELECT query

```php
# First Solution (with a model)
$query = User::select();

# Second solution
$query = new ModelQuery(User::class, ModelQuery::SELECT);
$query->exploreModel(User::class);


# Manipulation
$query->where("my_field", 5) ;
$query->where("my_field", null, "<>");
$query->whereSQL("creation_date > {}", ['2023-01-01']);
$query->order('user', 'creation_date', 'DESC');
$query->limit(1000);
$query->offset(5);

$first = $query->first();
$res = $query->fetch();
$sql = $query->build();
```

Tips:
- The `where` method support "=" and "<>" comparison with `NULL` (converted to `IS` and `IS NOT`)

### Select query return format

By default, ModelQuery explore your models relations to select every fields possible

Let's say you have a `User` model, which points to the `Person` model through `fk_person`, which points to `PersonPhone` through `fk_phone`

Executing

```php
$user = User::select()->first();
```

Allow use to read its data through the `->data` expression,
you can also read its foreign key data with the `->$foreignKey` expression

```php
$user = User::select()->fetch()
$user->fk_person->number;
$user->data->fk_person;
$user->id;
```

Also, you can convert this data to an array with `->toArray()` which will return something like this

```json
{
    "data": {
        "id": "...",
        "login": "...",
        "password": "...",
        "...": "..."
    },
    "fk_person": {
        "data": {
            "firstname": "bob",
            "lastname": "robertson",
            "...": "..."
        },
        "fk_phone": {
            "number": "0123456789",
            "...": "..."
        }
    }
}
```

This format can seem quite hard to use at first, but it is really simple:
- use `data.[key-name]` on specified table to access data
- use a foreign key name to access a foreign table


### Bottleneck model exploration

Using those prototypes
```php
ModelQuery::exploreModel(
    string $model,
    bool $recursive=true,
    array $foreignKeyIgnores=[]
): self;

// OR

Model::select(
    bool $recursive=true,
    array $foreignKeyIgnores=[]
);
```

We can control how `ModelQuery` explore our "model tree"

By setting `$recursive` to `false`, we only fetch our first table data, and don't explore more.

Putting relations in `$foreignKeysIgnores` allow use to prevent specified table relations

Exemple, giving `user&fk_person` will allow the exploration or `user` table but not `person` or any table that depends on it.


## UPDATE query

```php
$query = new ModelQuery(User::class, ModelQuery::UPDATE);

$query->set("created_this_year", true)
$query->set("active", false)

$query->where("my_field", 5) ;
$query->where("my_field", null, "<>");
$query->whereSQL("creation_date > {}", ['2023-01-01']);
$query->order('user', 'creation_date', 'DESC');
$query->limit(1000);

$res = $query->fetch();
$sql = $query->build();
```

## DELETE query

```php
$query = new ModelQuery(User::class, ModelQuery::DELETE);

$query->where("my_field", 5) ;
$query->where("my_field", null, "<>");
$query->whereSQL("creation_date > {}", ['2023-01-01']);
$query->order('user', 'creation_date', 'DESC');
$query->limit(1000);

$first = $query->first();
$res = $query->fetch();
$sql = $query->build();
```

## Configuration

`ModelQuery` don't have a big configurable, so far, you can only change the maximum number of `JOIN` a query can handle (50 by default)

```json
"database-query": {
    "join-limit": 50
}
```

[< Back to summary](../README.md)
