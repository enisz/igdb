---
overview: Everything about the Query Builder class. Setting up and configuring your queries.
icon: fa-hard-hat
---

# IGDB Query Builder

This class is a helper class to help you construct your queries. If you have used this wrapper's eariler versions you were able to send queries by passing an `$options` array directly to the endpoint methods. This possibility got removed and these endpoint methods are accepting only [apicalypse](https://api-docs.igdb.com/#apicalypse-1) formatted query strings.

> Using the Builder class is optional as you can pass your own queries to the endpoint methods.

## Instantiating the Builder

There are two ways to instantiate the Builder.

### Traditional way

To support the traditional way of configuring the queries - the `$options` array - there is an optional parameter in the constructor. If you wish to use this approach you can pass your array to the constructor. In this case make sure to have the instantiating line in a try...catch block as the constructor could throw an exception if there is an invalid key or field in your array.

```php
<?php

    require_once "class.igdb.php";

    $options = array(
        "search" => "uncharted 4",
        "fields" => "id,name",
        "limit" => 1
    );

    try {
        $builder = new IGDBQueryBuilder($options);
    } catch (IGDBInvaliParameterException $e) {
        // invalid key found in the $options array
        echo $e->getMessage();
    }

?>
```

> Using the Builder this way is not recommended and this functionality may be removed in future versions.

### Builder way

The Builder is using a builder pattern to configure itself before parsing the properties to a query string. When every parameter is set, calling the `build` method will start processing the parameters and will return the query string itself.

```php
<?php

    require_once "class.igdb.php";

    $builder = new IGDBQueryBuilder();

    try {
        $query = $builder
            ->search("uncharted 4")
            ->fields("id, name")
            ->limit(1)
             // make sure to call the build method to parse the query string
            ->build();
    } catch (IGDBInvaliParameterException $e) {
        // invalid value passed to a method
        echo $e->getMessage();
    }

?>
```

## Configuring Methods

The Builder class has its own configuring methods to set the parameters before building the query string. If any of these methods recieves an invalid argument they will throw an `IGDBInvalidArgumentException`. Make sure to set these parameters in a try...catch block.

### ID
```php
public function id(array | int $id) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

This is kind of a traditional thing. Since the IGDB team introduced the apicalypse query there is no separate ID field. This method simply adds a [where](#where) statement to your query.

**Parameters**:
 - `$id`: one (integer) or more (array of integers) record ID's.

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional way, single id
    $options = array(
        "id" => 1
    );

    // traditional way, multiple id's
    $options = array(
        "id" => array(1,2,3)
    );

    // builder way, single id
    $builder->id(1);

    // builder way, multiple id's
    $builder->id(array(1,2,3));

?>
```

### Search
```php
public function search(string $search) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

Search based on name, results are sorted by similarity to the given search string.

**Parameters**:
 - `$search`: the search string

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional way
    $options = array(
        "search" => "uncharted 4"
    );

    // builder way
    $builder->search("uncharted 4");

?>
```

### Fields
```php
public function fields(string | array $fields) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

You can tell which fields you want to get in the result with this parameter.

Fields are properties of an entity. For example, a Game field would be genres or release_dates. Some fields have properties of their own, for example, the genres field has the property name.

**Default value**: by default every field (`*`) will be requested from the endpoint.

**Parameters**:
 - `$fields`: the list of the required fields. This can be either a comma separated list of field names, or an array of field names.

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional way, as a string
    $options = array(
        "fields" => "id,name"
    );

    // traditional way, as an array
    $options = array(
        "fields" => array("id", "name")
    );

    // builder way, as a string
    $builder->fields("id,name");

    // builder way, as an array
    $builder->fields(array("id", "name"));

?>
```

### Exclude
```php
public function exclude(string | array $exclude) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

Exclude is a complement to the regular fields which allows you to request all fields with the exception of any numbers of fields specified with exclude.

**Parameters**:
 - `$exclude`: the list of the fields to exclude. This can be either a comma separated list of field names or an array of field names.

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional way, as a string
    $options = array(
        "exclude" => "name,slug"
    );

    // traditional way, as an array
    $options = array(
        "exclude" => array("name", "slug")
    );

    // builder way, as a string
    $builder->exclude("name,slug");

    // builder way, as an array
    $builder->exclude(array("name", "slug"));

?>
```

### Limit
```php
public function limit(int $limit) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

The maximum number of results in a single query. This value must be a number between `1` and `500`.

**Default value**: by default the IGDB API will return `10` results.

**Parameters**:
 - `$limit`: a number between 1 and 500

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional way
    $options => array(
        "limit" => 20
    );

    // builder way
    $builder->limit(20);

?>
```

### Offset
```php
public function offset(int $offset) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

This will start the result list at the provided value and will give `limit` number of results. This value must be `0` or greater.

**Default value**: the IGDB API will use 0 as a default offset value.

**Parameters**:
 - `$offset`: a number which can be 0 or greater.

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional way
    $options = array(
        "offset" => 5
    );

    // builder way
    $builder->offset(5);

?>
```

### Where
```php
public function where(string | array $where) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

Filters are used to swift through results to get what you want. You can exclude and include results based on their properties. For example you could remove all Games where the rating was below 80 `(where rating >= 80)`.

The where parameter can be either an apicalypse formatted string or an array with specific key-value pairs. If you provide the where parameters as an array, you must have three fix keys in it:
 - `field`: The name of the field you want to apply the filter to.
 - `postfix`: The postfix you want to use with the filter. Refer to the [Available Postfixes](https://api-docs.igdb.com/#filters) section of the IGDB Filters Documentation for available postfixes.
 - `value`: The value of the filter.

> Multiple filter parameters can be applied to the same query. Check the examples below.

**Parameters**:
 - `$where`: either an apicalypse formatted string or an array with specific keys

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional way, single criteria as a string
    $options = array(
        "where" => "release_dates.platform = 8"
    );

    // traditional way, single criteria as an array
    $options = array(
        "where" => array(
            "field" => "release_dates.platform",
            "postfix" => "=",
            "value" => 8
        )
    );

    // traditional way, multiple criteria as a string
    $options = array(
        "where" => array(
            "release_dates.platform = 8",
            "total_rating >= 70"
        );
    );

    // traditional way, multiple criteria as an array
    $options = array(
        "where" => array(
            array(
                "field" => "release_dates.platform",
                "postfix" => "=",
                "value" => 8
            ),
            array(
                "field" => "total_rating",
                "postfix" => ">=",
                "value" => 70
            )
        )
    );

    // builder way, single criteria as a string
    $builder->where("release_dates.platform = 8");

    // builder way, single criteria as an array
    $builder->where(
        array(
            "field" => "release_dates.platform",
            "postfix" => "=",
            "value" => 8
        )
    );

    // builder way, multiple criteria as a string
    $builder
        ->where("release_dates.platform = 8")
        ->where("total_rating >= 70");

    // builder way, multiple criteria as an array
    $builder
        ->where(
            array(
                "field" => "release_dates.platform",
                "postfix" => "=",
                "value" => 8
            )
        )
        ->where(
            array(
                "field" => "total_rating",
                "postfix" => ">=",
                "value" => 70
            )
        );
?>
```

> This method is trying to validate your input against some rules and will throw an `IGDBInvalidArgumentException` in case of any issues. If you need more flexibility or custom where statements with complex conditions in you queries use the [Custom Where](#custom-where) method instead.

### Custom Where
```php
public function custom_where(string $custom_where): IGDBQueryBuilder
```

This method will add a where statement to your query, but will not validate your input or throw any exceptions.

**Parameters**:
 - `$custom_where`: an apicalypse string with a custom where statement

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional way
    $options = array(
        "custom_where" => "where (platforms = [6,48] & genres = 13) | (platforms = [130,48] & genres = 12)"
    );

    // method way
    $builder->custom_where("where (platforms = [6,48] & genres = 13) | (platforms = [130,48] & genres = 12)");

?>
```

### Sort
```php
public function sort(string | array $sort) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

Sorting (ordering) is used to order results by a specific field. The parameter can be either an apicalypse formatted sort string or an array with specific key-value pairs. If you provide the Order parameter as an array, you must have two values in it with the following indexes:
 - `field`: The field you want to do the ordering by
 - `direction`: The direction of the ordering. It must be either `asc` for ascending or `desc` for descending ordering.

**Parameters**:
 - `$sort`: either an apicalypse sort string or an array with specific key-value pairs.

**Returns**: `IGDBQueryBuilder` instance

```php
<?php
    // traditional way as a string
    $options = array(
        "sort" => "release_dates.date desc",
    );

    // traditional way as an array
    $options = array(
        "sort" => array(
            "field" => "release_dates.date",
            "direction" => "desc"
        )
    );

    // method way as a string
    $builder->sort("release_dates.date desc");

    // method way as an array
    $builder->sort(
        array(
            "field" => "release_dates.date",
            "direction" => "desc"
        )
    );

?>
```

## Building the Query
```php
public function build(): string
```

When the Builder object is configured properly the final step is to build the query to an apicalypse query string.

**Parameters**: -

**Returns**: the apicalypse query string

```php
<?php

    require_once "class.igdb.php";

    $builder = new IGDBQueryBuilder();

    try {
        // the return value of the final build method is the apicalypse query string
        $query = $builder
            ->search("uncharted 4")
            ->fields("id, name")
            ->limit(1)
            ->build();
    } catch (IGDBInvaliParameterException $e) {
        // invalid value passed to a method
        echo $e->getMessage();
    }

?>
```