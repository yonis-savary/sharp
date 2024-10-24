[< Back to Summary](../README.md)

# 🚃 ObjectArray class

Sharp got the [`ObjectArray`](../../src/Classes/Data/ObjectArray.php) class, which purpose is to be an Object version of an array of data (A list, not an associative array)

This class got the most of ["standard"](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array#array_methods_and_empty_slots) array methods an array can have, this include
```php
$myArray = new ObjectArray();
$myArray->push();
$myArray->pop();
$myArray->shift();
$myArray->unshift();

$myArray->forEach();
$myArray->map();
$myArray->filter();
$myArray->reduce();
$myArray->sortByKey();

$myArray->unique();
$myArray->diff();
$myArray->slice();
$myArray->reverse();
```

And most of them do exactly what you expect them to do

> [!IMPORTANT]
> This class data handling behavior is quite particular, when calling `$myArray->map($myFunction)`,
> callbacks are not applied until you call `$myArray->collect()`, which return the new data

Every methods above return a new `ObjectArray` instance with new filters/transformers, which means that you can create copies

```php
$original = new ObjectArray([0,1,2,3,4,5]);
$even = $original->filter(fn($x) => $x % 2 == 0);

$original->collect();
// [0,1,2,3,4,5]

$even->collect();
// [0,2,4]
```

Also, as `ObjectArray` return new instances of itself, this mean that you can chain method calls

```php
(new ObjectArray[0,1,1,2,3,3,4,4,5,6])
->unique()
->filter(fn($x) => $x % 2 == 0)
->map(fn($x) => $x * 2)
->collect()
// [0,4,8,12]
```

## Additional properties/methods

```php
# Alias to the constructor, can be used as a callback
ObjectArray::fromArray($myArray);

# Alias to ObjectArray::fromArray(explode($separator, $string))
ObjectArray::fromExplode(',', 'A,B,C');

# Select the first-column values of a query
ObjectArray::fromQuery('SELECT id FROM client LIMIT 500');

$myArray = new ObjectArray([0,1,2,3,4,5]);

$myArray->join(','); // '0,1,2,3,4,5'
$myArray->length(); // 6

// Return the first element that respect a condition
$myArray->find(fn($x) => $x % 2 == 0) // return 0
$myArray->find(fn($x) => $x >= 4) // return 4

// Check if any element respect a condition
$myArray->any(fn($x) => $x == 5) // true

// Check if every elements respect a condition
$myArray->all(is_numeric(...)); // true

$myArray->reduce(fn($acc, $cur) => $acc + $cur, 0);
// return 15

// Make an associative array from returned pairs
$alphabet = range('A', 'F');
$myArray->toAssociative(fn($value) => [$alphabet[$i], $i]);
// return ['A'=>0, 'B'=>1, 'C'=>2, 'D'=>3, 'E'=>4, 'F'=>5]


// Sort the array by a given key (given by a callback)
// Sort from worst score to best
$users->sortByKey(fn($user) => $user['score']);
// Sort from best score to worst
$users->sortByKey(fn($user) => $user['score'], true);
```

[< Back to Summary](../README.md)
