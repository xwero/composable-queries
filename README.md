# Composable queries

**ALPHA stage: breaking changes can happen in any version update**

The main goal of this library is to create a single source of truth for data storage containers and properties.
This not only in data storage queries but also in the application code.

The backbone of this source of truth is the `ReplacementInterface` class which is the library's custom name for an enumeration.
In the queries this will look like `SELECT ~Test\Unit\Users:Name FROM ~Test\Unit\Users:Users`, and when using one of the map functions
it will be possible to get the value with `$map[Test\Unit\Users:Name]`.

The composable queries name comes from the fact that the functionality of the library is in functions instead of objects.
When you use PHP 8.5 it will be possible to do following.

```php
$query = 'SELECT ~Test\Unit\Users:Name FROM ~Test\Unit\Users:Users WHERE ~Test\Unit\Users:Name = :Test\Unit\Users:Name';
$map = getStatement($pdo, $query, new QueryParametersCollection(Users::Name, 'me'))
       |> getRow(...)
       |> fn($result) => createMapFromArray(Users::Users, $result);
```
## TODOS

[] More documentation
[] More (real-world) tests
[] Functions for non SQL queries.
[] More map functions
[] Less hard coding

## Inspiration

ORM solutions in general and DQL specifically. 
I like the distance between the table and the columns that DQL brings to the table. 
The dark side for me was creating an object that has a lot of functionality tied to it. Models can do validation, serialisation,
query building, data mutation. 

I wanted an object with a single focus, and if you want to add all that model functionality be my guest. 
For now I'm leaning to the bring your own tools mentality, because I want to keep hammering on the single source truth idea.   

