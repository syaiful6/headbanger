### Headbanger

PHP data structure and or collections.

Motivation: PHP only give us array as compound type, not set, list etc. This make
operation on array not predictable, is sometimes act as ordered set, list, ordered map.
This package aim to provide some basic data structure to cover this gap.

### Install

```composer require syaiful6/headbanger```

This package contains implementations of Map, List, Set and Str. We consider string
as container, because it can be accessed via array notation and when we want
to iterate string, we actually want to iterate over character.

### Map

Python call this dict, Ruby call this type Hash. Whatever the name, this data
type can 'map' key value pair. Typically the key are unique (within one map).

The main operations on a map are storing a value with some key and extracting
the value given the key. It's also should possible to delete a key-value pair
for the given key.

This package provide one implementation for this data type. ```HashMap``` that
also implements Countable, Traversable and ArrayAccess. So you can treat this
instance as array. To delete key-value pair use ```unset($hash['somekey'])```

Note: ```HashMap``` is unordered map and not permit adding item during iteration,
replacing a key-value is fine. Iterate over map will give you the key only, with
this key you can access the value anyway. If you need the value use ```values``` method,
if you need both use ```items`` method.

```
$map = new Headbanger\HashMap();
// Add item, or just give map your array or any traversable on constructor
$map['one'] = 1;
$map['two'] = 2;
$map['three'] = 3;
// deleting
unset($map['one']);
// check if item exists
isset($map['one']);
```

### List

A list or sequence, represents an ordered sequence of values, these values may
occur more than once within on list. Think of it as finite sequence. Currently
only one implementation provided: ArrayList. Using SplFixedArray as their storage.

You can use ArrayList as ```Stack```, where the last element added is the first
element retrieved (“last-in, first-out”). Use ```push``` to add item, and retrieve
an item from the top use ```pop``` (without argument). However dont use ArrayList
as Queue, they are not efficient for this purpose. But it possible.

### Set

A set is an unordered collection with no duplicate elements. The common operations
are remove duplicates entries and membership testing. Set also support mathematical
operations like union, intersection, difference, etc.