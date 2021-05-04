---
title: IGDB Query Builder
overview: The query builder class. Usage, configuration, query parsing.
icon: fa-tools
color: pink
---

# IGDB Query builder

The IGDBQueryBuilder class is a helper class which aims to make parsing apicalypse query strings easier. As this class is not part of the main `IGDB` class you will have to import it separately.

# Basic usage

The class is using the builder pattern which makes the configuration much easier. After the required parameters set, calling the build method will parse and return the apicalypse query string. Make sure to build the query in a try...catch block to catch possible invalid parameters.

```php
<?php

    require_once "IGDBQueryBuilder.php";

    $builder = new IGDBQueryBuilder();

    try {
        echo $builder
            ->search("uncharted 4") // setting the search parameter to search for uncharted 4
            ->fields("id,name")     // setting the fields to include in the query
            ->limit(1)              // limit the results to 1 record
            ->build();              // building a query; returning a string
    } catch (IGDBInvalidParameterException $e) {
        echo $e->getMessage();
    }

?>
```

Since this is a valid query no exceptions are thrown, and the output will be as below:

```text
fields id,name; search "uncharted 4"; limit 1;
```

The output of this builder class can be passed straight to the `IGDB` endpoint methods.

```php
<?php

    require_once "IGDB.php";
    require_once "IGDBQueryBuilder.php";

    $igdb = new IGDB("{client_id}", "{access_token}");
    $builder = new IGDBQueryBuilder();

    try {
        $builder
            ->search("uncharted 4")
            ->fields("id,name")
            ->limit(1);

        var_dump(
            $igdb->game(
                $builder->build()
            )
        );
    } catch (IGDBInvalidParameterException $e) {
        // invalid parameter
        echo $e->getMessage();
    } catch (IGDBEndpointException $e) {
        // failed request
        echo $e->getMessage();
    }

?>
```

And the output will be:

```text
array (size=1)
  0 =>
    object(stdClass)[3]
      public 'id' => int 7331
      public 'name' => string 'Uncharted 4: A Thief's End' (length=26)
```

# Legacy Configuration

In earlier versions of the wrapper the query string could be parsed from an associative array. If you prefer that format you can still use it with this Query Builder. In this case you have to pass the `$options` associative array to the Query Builder's constructor.

```php
<?php

    require_once "IGDBQueryBuilder.php";

    $options = array(
        "search" => "uncharted 4",
        "fields" => "id,name",
        "limit" => 1
    );

    try {
        $builder = new IGDBQueryBuilder($options);
        echo $builder->build();
    } catch (IGDBInvalidParameterException $e) {
        echo $e->getMessage();
    }

?>
```

This will produce the same output as the example code in the [basic usage](#basic-usage) section.

# Configuration Methods

These methods can be used to configure the object after the class is instantiated.

```php
<?php

    require_once "IGDBQueryBuilder.php";
    $builder = new IGDBQueryBuilder();

?>
```

If any of the configuration methods recieves a parameter which is invalid, an `IGDBInvalidParameterException` will be thrown.

## ID
```php
public function id(int | array $id) throws IGDBInvalidParameterException: IGDBQueryBuilder
```

This is a legacy method, the IGDB API now filters the record id's in the `where` clause. This method is simply adding a statement to the where clause with the provided ID's.

**Parameters**:
 - `$id`: either a number to set one id, or an array of numbers to set multiple id's

**Returns**: the `IGDBQueryBuilder` instance

```php
<?php

    // legacy configuration, single record id
    $options = array(
        "id" => 1,
    )

    // legacy configuration, multiple record id's
    $options = array(
        "id" => array(1,2,3)
    )

    // method configuration, single record id
    $builder->id(1);

    // method configuration, multiple record id's
    $builder->id(
        array(1,2,3)
    );

?>
```

## Search
```php
public function search(string $search) throws IGDBInvalidParameterException: IGDBQueryBuilder
```

Search based on name, results are sorted by similarity to the given search string.

**Parameters**:
 - `$search`: a string to set for the search statement

**Returns**: the `IGDBQueryBuilder` instance

```php
<?php

    // legacy configuration
    $options = array(
        "search" => "uncharted 4"
    )

    // method configuration
    $builder->search("uncharted 4");

?>
```

## Fields
```php
public function fields(string | array $fields) throws IGDBInvalidParameterException: IGDBQueryBuilder
```

You can tell the wrapper which fields you want to get in the result.

Fields are properties of an entity. For example, a Game field would be `genres` or `release_dates`. Some fields have properties of their own, for example, the `genres` field has the property `name`.

The default value of the `fields` property is `*` which means every fields will be returned in the results.

**Parameters**:
 - `$fields`: either a comma separated list or an array of strings of the names of the fields you want to include in the results

**Returns**: the `IGDBQueryBuilder` instance

```php
<?php

    // legacy configuration, comma separated list of field names
    $options = array(
        "fields" => "id,name,slug"
    );

    // legacy configuration, array of field names
    $options = array(
        "fields" => array("id", "name", "slug");
    )

    // method configuration, comma separated list of field names
    $builder->fields("id,name,slug");

    // method configuration, array of field names
    $builder->fields(
        array("id", "name", "slug")
    )

?>
```

## Exclude
```php
public function exlude(string | array $exclude) throws IGDBInvalidParameterException: IGDBQueryBuilder
```

Exclude is a complement to the regular fields which allows you to request all fields with the exception of any numbers of fields specified with exclude.

**Parameters**:
 - `$exclude`: either a comma separated list or an array of strings of the names of the fields you want to exclude from the results

**Returns**: the `IGDBQueryBuilder` instance

```php
<?php

    // legacy configuration, comma separated list of field names
    $options = array(
        "exclude" => "slug,cover"
    );

    // legacy configuration, array of field names
    $options = array(
        "exclude" => array("slug", "cover");
    )

    // method configuration, comma separated list of field names
    $builder->exclude("slug,cover");

    // method configuration, array of field names
    $builder->exclude(
        array("slug", "cover");
    )

?>
```

## Limit
```php
public function limit(int $limit) throws IGDBInvalidParameterException: IGDBQueryBuilder
```

The maximum number of results in a single query. This value must be a number between 1 and 500. The default value of this parameter is 10.

**Parameters**:
 - `$limit`: a number between 1 and 500

**Returns**: the `IGDBQueryBuilder` instance

```php
<?php

    // legacy configuration
    $options = array(
        "limit" => 5
    );

    // method configuration
    $builder->limit(5);

?>
```

## Offset
```php
public function offset(number $offset) throws IGDBInvalidParameterException: IGDBQueryBuilder
```

This will start the result list at the provided value and will give `limit` number of results. This value must be 0 or greater. The default value is 0.

**Parameters**:
 - `$offset`: a number. 0 or greater.

**Returns**: the `IGDBQueryBuilder` instance

```php
<?php

    // legacy configuration
    $options = array(
        "offset" => 5
    );

    // method configuration
    $builder->offset(5);

?>
```

## Where
```php
public function where(string | array $where) throws IGDBInvalidParameterException: IGDBQueryBuilder
```

Filters are used to swift through results to get what you want. You can exclude and include results based on their properties. For example you could remove all Games where the rating was below 80 `(where rating >= 80)`.

If you provide the where parameters as an array, you must have three values in it with the following indexes:
 - `$field`: The name of the field you want to apply the filter to
 - `$postfix`: The postfix you want to use with the filter. Refer to the Available Postfixes section of the IGDB Filters Documentation for available postfixes.
 - `$value`: The value of the filter.

```php
<?php

    // legacy configuration
    // pass a single filter rule as an array
    // in this case you must have field, postfix and value elements in the array
    $query = array(
        'where' => array(
            'field' => 'release_dates.platform',
            'postfix' => '=',
            'value' => 8
        )
    );

    // method configuration
    $builder->where(
        array(
            "field" => "release_dates.platform",
            "postfix" => "=",
            "value" => 8
        )
    )

    // legacy configuration
    // pass multiple filter rules as an array of arrays
    // in this case you must have field, postfix and value elements in the arrays
    $query = array(
    'where' => array(
        array(
            'field' => 'release_dates.platform',
            'postfix' => '=',
            'value' => 8
        ),
        array(
            'field' => 'total_rating',
            'postfix' => '>=',
            'value' => 70
        )
    );

    // method configuration
    // multiple where statements can be combined by calling the when method multiple times
    // in the query string the segments will be merged by and "&" signs
    $builder
        ->where(
            array(
                'field' => 'release_dates.platform',
                'postfix' => '=',
                'value' => 8
            )
        )
        ->where(
            array(
                'field' => 'total_rating',
                'postfix' => '>=',
                'value' => 70
            )
        );
?>
```

You can provide the filter parameter as string too. In this case you can pass the string as an apicalypse string:

```php
<?php

    // legacy configuration
    // pass a single filter rule as a string
    $query = array(
    'where' => 'release_dates.platform = 8'
    );

    // method configuration
    $builder->where("release_dates.platform = 8");

?>
```

Or you can provide multiple criteria:

```php
<?php

    // legacy configuration
    // multiple filter rule as an array of apicalypse string
    $query = array(
        'where' => array(
            'release_dates.platform = 8',
            'total_rating >= 70',
            'genres = 4'
        )
    );

    // method configuration
    // the where method has to be called multiple times
    $builder
        ->where("release_dates.platform = 8")
        ->where("total_rating >= 70")
        ->where("genres = 4");

?>
```

In this case make sure to separate the field name, the postfix and the value with spaces!

<blockquote>
When building the query string after calling the build method, the segments will be concated by "and" operators (&). If you need more complex where statements consider using the custom_where method which allows you to use different operators and does not restrict the format of the passed parameters.
</blockquote>

## Custom Where
```php
public function custom_where(string $where) throws IGDBInvalidParameterException: IGDBQueryBuilder
```

With this method you can add a custom where statement to the query. This method validates only the type of the passed `$where` parameter and will throw an `IGDBInvalidParameterException` only if the parameter is a non-string value.

When building the query the parameters added by this method will be processed along with the "simple" where statements. If you have multiple where statements (custom or simple) they will be merged with an and `&` operator in the end.

**Parameters**:
 - `$where`: must be a string. In case of any other type of variables will cause this method to throw an `IGDBInvalidParameterException`.

**Returns**: the `IGDBQueryBuilder` instance

```php
<?php

    // legacy configuration
    $options = array(
        "custom_where" => "id = 5"
    );

    // method configuration
    $builder->custom_where("id = 5");

?>
```

## Sort
```php
public function sort(string | array $sort) throws IGDBInvalidParameterException: IGDBQueryBuilder
```

The ordering of the results can be controlled by this parameter. You can order the results by a specific field name in ascending (asc) and descending (desc) order.

IF you provide the Order parameter as an array, you must have two values in it with the following indexes:
 - `$field`: The field you want to do the ordering by
 - `$direction`: The direction of the ordering. It must be either `asc` for ascending or `desc` for descending ordering.

**Parameters**:
 - `$sort`: either an apicalypse formatted sort statement, or an array with the field and direction keys in them

**Returns**: the `IGDBQueryBuilder` instance

```php
<?php
    // legacy configuration, apicalpyse string
    $options = array(
        "sort" => "name asc"
    );

    // legacy configuration, array
    $options = array(
        "sort" => array(
            "field" => "name",
            "order" => "asc"
        )
    );

    // method configuration, apicalypse string
    $builder->sort("name asc");

    // method configuration, array
    $builder->sort(
        array(
            "field" => "name",
            "order" => "asc"
        )
    );
?>
```

# Building the query
```php
public function build(): string
```

When the builder configuration is done the query string can be built by the `build` method. The output of this method will be your query string which can be passed straight to the `IGDB` endpoint methods.

**Parameters**: -

**Returns**: the apicalypse query string

```php

    $builder = new IGDBQueryBuilder();

    // output will be:
    // fields id,name; search "uncharted 4"; limit 5;
    echo $builder
        ->fields("id, name")
        ->search("uncharted 4")
        ->limit(5)
        ->build();

?>
```