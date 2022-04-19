---
overview: Everything about the Query Builder. Configuring and building your queries.
icon: fa-hammer
---

# IGDB Query Builder

This class is a helper class to help you construct your queries. If you have used this wrapper's eariler versions you were able to send queries by passing an `$options` array directly to the endpoint methods. This possibility got removed and these endpoint methods are accepting only [apicalypse](https://api-docs.igdb.com/#apicalypse-1) formatted query strings.

>:tip Using the Builder class is optional as you can pass your own queries to the endpoint methods.

## Instantiating the Builder

To instantiate the Builder simply use the `new` keyword.

```php
<?php

    require_once "class.igdb.php";

    $builer = new IGDBQueryBuiler();

?>
```

Now the `$builder` object will expose every configuring methods to set up your query before [building it](#building-the-query).

## Configuring the Builder

There are two ways to configure the builder.

### Builder approach

The Builder is using a builder pattern to configure itself with the [configuring methods](#configuring-methods) before parsing the properties to a query string. When every parameter is set, calling the [`build()`](#building-the-query) method will start processing the parameters and will return the query string itself.

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

### Traditional approach

To support the traditional approach of configuring the queries - the `$options` array - there is a method called [`options()`](#options). Passing your `$options` array to this method will process your parameters and will set up the builder.

>:warning Using the Builder this way is not recommended as this functionality may will be removed in future versions. Use the [builder approach](#builder-approach) instead.

```php
<?php

    require_once "class.igdb.php";

    $builder = new IGDBQueryBuilder();

    $options = array(
        "search" => "uncharted 4",
        "fields" => "id,name",
        "limit" => 1
    );

    try {
        // pass your $options to the options method
        // then build the query
        $query = $builder->options($options)->build();
    } catch (IGDBInvaliParameterException $e) {
        // invalid key or value found in the $options array
        echo $e->getMessage();
    }

?>
```

## Configuring Methods

The Builder class has its own configuring methods to set the parameters before building the query string. If any of these methods recieves an invalid argument they will throw an `IGDBInvalidArgumentException`. Make sure to set these parameters in a try...catch block.

### Count
```php
public function count(boolean $count = true) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

This method will accept a boolean value. This parameter is processed **in case of a multiquery build only**. If this value is `true` the response from IGDB will contain the number of records matching the filters. If `false` the records will be returned.

> If the method is called without parameters, the value will be set to `true`.

**Default value**: by default this value is `false`, the queries will return the records instead of their count.

**Parameters**:
 - `$count`: `true` if a record count is required, `false` otherwise.

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // importing the wrapper
    require_once "class.igdb.php";

    // instantiate the query builder
    $builder = new IGDBQueryBuilder();

    try {
        // Configuring the query
        $builder
            ->name("Number of games")
            ->endpoint("game")
            // the default value of count is false
            // to retrieve the count of the matched records, set it to true
            // ->count(true) has the same result
            ->count();

            // if you wish to remove the count parameter for any reason later on
            // you can call the count method with a false parameter
            // ->count(false)

        // building the query
        $query = $builder->build(true);
    } catch (IGDBInvaliParameterException $e) {
        // invalid key or value found in the $options array
        echo $e->getMessage();
    }

?>
```

The value of `$query`:

```text
query games/count "Number of games" {
  fields *;
};
```

### Custom Where
```php
public function custom_where(string $custom_where): IGDBQueryBuilder
```

This method will add a [where statement](#where) to your query, but will not validate your input or throw any exceptions.

**Parameters**:
 - `$custom_where`: an apicalypse string with a custom where statement

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional approach
    $options = array(
        "custom_where" => "(platforms = [6,48] & genres = 13) | (platforms = [130,48] & genres = 12)"
    );

    // builder approach
    $builder->custom_where("(platforms = [6,48] & genres = 13) | (platforms = [130,48] & genres = 12)");

?>
```

### Endpoint
```php
public function endpoint(string $endpoint) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

This method will set the endpoint to send your **multiquery** against. This parameter is processed **in case of a multiquery build only**. When setting this value make sure to use the name of the endpoint instead of it's request path. (for example `game` instead of `games` and so on).

>:warning In case of a multiquery build this parameter is mandatory! If this is missing from the configuration the build will throw an `IGDBInvalidParameterException`! Refer to the [build method](#building-the-query) for details.

**Parameters**:
 - `$endpoint`: the name of the endpoint to send the query against. Make sure to use the name of the endpoint instead of it's request path! The list of endpoints for this parameter:
   - age_rating
   - alternative_name
   - artwork
   - character_mug_shot
   - character
   - collection
   - company_logo
   - company_website
   - company
   - cover
   - external_game
   - franchise
   - game_engine_logo
   - game_engine
   - game_mode
   - game_version_feature_value
   - game_version_feature
   - game_version
   - game_video
   - game
   - genre
   - involved_company
   - keyword
   - multiplayer_mode
   - multiquery
   - platform_family
   - platform_logo
   - platform_version_company
   - platform_version_release_date
   - platform_version
   - platform_website
   - platform
   - player_perspective
   - release_date
   - screenshot
   - search
   - theme
   - website

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // importing the wrapper
    require_once "class.igdb.php";

    // instantiate the query builder
    $builder = new IGDBQueryBuilder();

    try {
        // configuring the query
        $builder
            ->name("Game with ID of 25076")
            ->endpoint("game")
            ->fields("id,name")
            ->where("id = 25076");

        // building the query
        $query = $builder->build(true);
    } catch (IGDBInvaliParameterException $e) {
        // invalid key or value found in the $options array
        echo $e->getMessage();
    }

?>
```

The value of `$query`:

```text
query games "Game with ID 25076" {
  fields id,name;
  where id = 25076;
};
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

    // traditional approach, as a string
    $options = array(
        "exclude" => "name,slug"
    );

    // traditional approach, as an array
    $options = array(
        "exclude" => array("name", "slug")
    );

    // builder approach, as a string
    $builder->exclude("name,slug");

    // builder approach, as an array
    $builder->exclude(array("name", "slug"));

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

    // traditional approach, as a string
    $options = array(
        "fields" => "id,name"
    );

    // traditional approach, as an array
    $options = array(
        "fields" => array("id", "name")
    );

    // builder approach, as a string
    $builder->fields("id,name");

    // builder approach, as an array
    $builder->fields(array("id", "name"));

?>
```

### ID
```php
public function id(array | int $id) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

This is kind of a traditional thing. Since the IGDB team introduced the apicalypse query there is no separate ID field. This method simply adds a [where](#where) statement to your query.

```php
<?php

    $builder->id(1);
    // where id = 1;

    $builder->id(array(1,2,3));
    // where id = (1,2,3);

?>
```

**Parameters**:
 - `$id`: one (integer) or more (array of integers) record ID's.

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional approach, single id
    $options = array(
        "id" => 1
    );

    // traditional approach, multiple id's
    $options = array(
        "id" => array(1,2,3)
    );

    // builder approach, single id
    $builder->id(1);

    // builder approach, multiple id's
    $builder->id(array(1,2,3));

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

    // traditional approach
    $options => array(
        "limit" => 20
    );

    // builder approach
    $builder->limit(20);

?>
```

### Name
```php
public function name(string $name) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

This method will set a name for the query. This parameter is processed **in case of a multiquery build only**.

>:warning In case of a multiquery build this parameter is **mandatory**! If this is missing from the configuration the build will throw an `IGDBInvalidParameterException`! Refer to the [build method](#building-the-query) for details.

**Parameters**:
 - `$name`: the name of the query

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // importing the wrapper
    require_once "class.igdb.php";

    // instantiate the query builder
    $builder = new IGDBQueryBuilder();

    try {
        // configuring the query
        $builder
            ->name("Game with ID of 25076")
            ->endpoint("game")
            ->fields("id,name")
            ->where("id = 25076");

        // building the query
        $query = $builder->build(true);
    } catch (IGDBInvaliParameterException $e) {
        // invalid key or value found in the $options array
        echo $e->getMessage();
    }

?>
```

The value of `$query`:

```text
query games "Game with ID 25076" {
  fields id,name;
  where id = 25076;
};
```

### Offset
```php
public function offset(int $offset) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

This will start the result list at the provided value and will give [`limit`](#limit) number of results. This value must be `0` or greater.

**Default value**: the IGDB API will use `0` as a default offset value.

**Parameters**:
 - `$offset`: a number which can be `0` or greater.

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional approach
    $options = array(
        "offset" => 5
    );

    // builder approach
    $builder->offset(5);

?>
```

### Options
```php
public function options(array $options) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

>:warning Using the Builder this way is not recommended as this functionality may be removed in future versions. Use the [builder approach](#builder-approach) instead.

With this method you can parse your `$options` array instead of using the [builder approach](#builder-approach). If a non-valid key or value is in the array, an `IGDBInvalidParameterException` will be thrown.

Passing your `$options` array to this method will configure the builder with the parameters in it.

**Parameters**:
 - `$options`: the options array with your configuration in it

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    require_once "class.igdb.php";

    // instantiate the query builder
    $builder = new IGDBQueryBuilder();

    // setting up the options array
    $options = array(
        "search" => "uncharted 4",
        "fields" => "id,name",
        "limit" => 1
    );

    try {
        // configuring the builder with an options array
        $builder->options($options);

        // still have to call the build method to building the query
        $query = $builder->build();
    } catch (IGDBInvaliParameterException $e) {
        // invalid key or value found in the $options array
        echo $e->getMessage();
    }

?>
```

>:warning Calling this method **will not reset** the configuration. Stacking the `options()` calls will add each parameter to the builder, overwriting the old values with the new ones. To clear the previous configuration items use the [reset method](#reset).

```php
<?php

    // importing the wrapper
    require_once "class.igdb.php";

    // instantiate the query builder
    $builder = new IGDBQueryBuilder();

    try {
        // stacking the options calls will add each parameter as this not resets the configuration
        $builder
            ->options(array("search" => "uncharted"))
            ->options(array("fields" => "id,name"))
            // this call will overwrite the search parameter!
            ->options(array("search" => "overwritten uncharted"))
            ->options(array("limit" => 1));

        // building the query
        $query = $builder->build();
    } catch (IGDBInvaliParameterException $e) {
        // invalid key or value found in the $options array
        echo $e->getMessage();
    }

?>
```

The query:

```text
fields id,name; search "overwritten uncharted"; limit 1;
```

### Reset
```php
public function reset(): IGDBQueryBuilder
```

This method will reset every configuration to the default values.

**Parameters**: -

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // importing the wrapper
    require_once "class.igdb.php";

    // instantiate the query builder
    $builder = new IGDBQueryBuilder();

    // setting up the options1 array
    $options1 = array(
        "search" => "uncharted 4",
        "fields" => "id,name",
        "limit" => 2
    );

    // setting upt he options2 array
    $options2 = array(
        "search" => "star wars",
        "fields" => "cover,slug",
        "limit" => 1
    );

    try {
        // configuring the builder with options1, reset the values then pass options2
        $builder->options($options1)->reset()->options($options2);

        // building the query
        $query = $builder->build();
    } catch (IGDBInvaliParameterException $e) {
        // invalid key or value found in the $options array
        echo $e->getMessage();
    }

?>
```

The code above will return the `$options2` array's configuration, as it got reset with the `reset()` method.

```text
fields cover,slug; search "star wars"; limit 1;
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

    // traditional approach
    $options = array(
        "search" => "uncharted 4"
    );

    // builder approach
    $builder->search("uncharted 4");

?>
```

### Sort
```php
public function sort(string | array $sort) throws IGDBInvalidArgumentException: IGDBQueryBuilder
```

Sorting (ordering) is used to order results by a specific field. The parameter can be either an apicalypse formatted sort string or an array with specific key-value pairs. If you provide the Order parameter as an array, you must have two values in it with the following keys:
 - `field`: The field you want to do the ordering by
 - `direction`: The direction of the ordering. It must be either `asc` for ascending or `desc` for descending ordering.

**Parameters**:
 - `$sort`: either an apicalypse sort string or an array with specific key-value pairs.

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional approach as a string
    $options = array(
        "sort" => "release_dates.date desc",
    );

    // traditional approach as an array
    $options = array(
        "sort" => array(
            "field" => "release_dates.date",
            "direction" => "desc"
        )
    );

    // builder approach as a string
    $builder->sort("release_dates.date desc");

    // builder approach as an array
    $builder->sort(
        array(
            "field" => "release_dates.date",
            "direction" => "desc"
        )
    );

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

The where filters will be concatenated with **AND** operators (`&`).

>:tip Multiple filter parameters can be applied to the same query. Check the examples below.

**Parameters**:
 - `$where`: either an apicalypse formatted string or an array with specific keys

**Returns**: `IGDBQueryBuilder` instance

```php
<?php

    // traditional approach, single criteria as a string
    $options = array(
        "where" => "release_dates.platform = 8"
    );

    // traditional approach, single criteria as an array
    $options = array(
        "where" => array(
            "field" => "release_dates.platform",
            "postfix" => "=",
            "value" => 8
        )
    );

    // traditional approach, multiple criteria as a string
    $options = array(
        "where" => array(
            "release_dates.platform = 8",
            "total_rating >= 70"
        );
    );

    // traditional approach, multiple criteria as an array
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

    // builder approach, single criteria as a string
    $builder->where("release_dates.platform = 8");

    // builder approach, single criteria as an array
    $builder->where(
        array(
            "field" => "release_dates.platform",
            "postfix" => "=",
            "value" => 8
        )
    );

    // builder approach, multiple criteria as a string
    $builder
        ->where("release_dates.platform = 8")
        ->where("total_rating >= 70");

    // builder approach, multiple criteria as an array
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

## Building the Query
```php
public function build(boolean $multiquery = false) throws IGDBInvalidParameterException: mixed
```

When the Builder object is configured properly the final step is to build the query to an apicalypse query string. The syntax of the query depends on which endpoint the query is executed against. The multiquery endpoint requires a few additional information and a slightly different syntax.

A [`game`](#game) endpoint query:
```text
fields id,name;
where id = 25076;
```

A [`multiquery`](#multiquery) to the game [`game`](#game) endpoint:
```text
query games "Main Game" {
  fields id,name;
  where id = 25076;
};
```

The build method accepts one `boolean` parameter. Using this parameter the build method decides which syntax to return. If a multiquery is required, a few extra fields has to be set for the builder:
 - [`name`](#name): the name of the query choosen by the user
 - [`endpoint`](#endpoint): the endpoint to send the query against
 - [`count`](#count) [optional]: whether the records or their count is required. By default the value of this parameter is `false`.

>:warning In case of a multiquery request the properties [name](#name) and [endpoint](#endpoint) are **mandatory**! If any of these are missing from the configuration, an `IGDBInvalidParameterException` will be thrown.

**Parameters**:
 - `$multiquery`: if `true`, a multiquery will be returned, an endpoint query otherwise. The default value of this parameter is `false`.

 >:warning If a non-boolean parameter is passed to the build method, an `IGDBInavlidParameterException` is thrown!

**Returns**: the apicalypse query string

An endpoint query example:
```php
<?php

    // importing the wrapper
    require_once "class.igdb.php";

    // instantiate the query builder
    $builder = new IGDBQueryBuilder();

    try {
        // configuring the query
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

The value of `$query`:

```text
fields id,name;
search "uncharted 4";
limit 1;
```

A multiquery example:

```php
<?php

    // importing the wrapper
    require_once "class.igdb.php";

    // instantiate the query builder
    $builder = new IGDBQueryBuilder();

    try {
        // configuring the query
        $builder
            ->name("Game with ID of 25076")
            ->endpoint("game")
            ->fields("id,name")
            ->where("id = 25076")
            // note the true parameter
            ->build(true);
    } catch (IGDBInvaliParameterException $e) {
        // invalid key or value found in the $options array
        echo $e->getMessage();
    }

?>
```

The value of `$query`:

```text
query games "Game with ID of 25076" {
  fields id,name;
  where id = 25076;
};
```