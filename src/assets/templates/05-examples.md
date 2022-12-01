---
overview: Working examples, covering most of the functionalities of the wrapper
icon: fa-code
---

# Examples

The examples in this section will try to cover most of the use cases of the wrapper.

>:tip To see your own tokens in the example codes set them up on the home page as described in [Using the documentation section](#using-the-documentation)!

## Basic Example

A basic example to send your apicalypse query to the IGDB API.

> Make sure to place your [endpoint method](#endpoints) calls in a try...catch block to be able to catch errors!

**Code**

```php
<?php

    // importing the wrapper
    require 'class.igdb.php';

    // instantiating the wrapper
    $igdb = new IGDB("{client_id}", "{access_token}");

    // your query string
    $query = 'search "uncharted"; fields id,name,cover; limit 5; offset 10;';

    try {
        // executing the query
        $games = $igdb->game($query);

        // showing the results
        var_dump($games);
    } catch (IGDBEnpointException $e) {
        // a non-successful response recieved from the IGDB API
        echo $e->getMessage();
    }

?>
```

**Result**

```text
array (size=5)
  0 =>
    object(stdClass)[3]
      public 'id' => int 125062
      public 'cover' => int 83686
      public 'name' => string 'Uncharted Ocean: Set Sail' (length=25)
  1 =>
    object(stdClass)[4]
      public 'id' => int 19583
      public 'cover' => int 15883
      public 'name' => string 'Uncharted: Fight for Fortune' (length=28)
  2 =>
    object(stdClass)[5]
      public 'id' => int 26193
      public 'cover' => int 85149
      public 'name' => string 'Uncharted: The Lost Legacy' (length=26)
  3 =>
    object(stdClass)[6]
      public 'id' => int 19609
      public 'cover' => int 85164
      public 'name' => string 'Uncharted: Fortune Hunter' (length=25)
  4 =>
    object(stdClass)[7]
      public 'id' => int 7331
      public 'cover' => int 81917
      public 'name' => string 'Uncharted 4: A Thief's End' (length=26)
```

## Using the Query Builder

An example to see how to use the [IGDB Query Builder](#igdb-query-builder) to build the query strings.

> Make sure to place your [query builder configuration](#configuring-methods) and [endpoint method](#endpoints) calls in a try...catch block to be able to catch errors!

**Code**

```php
<?php

    // importing the wrapper
    require 'class.igdb.php';

    // instantiating the wrapper
    $igdb = new IGDB("{client_id}", "{access_token}");

    // instantiate the query builder
    $builder = new IGDBQueryBuilder();

    try {
        // building the query
        $query = $builder
            // searching for games LIKE uncharted
            ->search("uncharted")
            // we want to see these fields in the results
            ->fields("id, name, cover")
            // we only need maximum 5 results per query (pagination)
            ->limit(5)
            // we would like to show the third page; fetch the results from the tenth element (pagination)
            ->offset(10)
            // process the configuration and return a string
            ->build();

        // executing the query
        $games = $igdb->game($query);

        // showing the results
        var_dump($games);
    } catch (IGDBInvalidParameterException $e) {
        // an invalid parameter is passed to the query builder
        echo $e->getMessage();
    } catch (IGDBEnpointException $e) {
        // a non-successful response recieved from the IGDB API
        echo $e->getMessage();
    }

?>
```

**Result**

```text
array (size=5)
  0 =>
    object(stdClass)[3]
      public 'id' => int 125062
      public 'cover' => int 83686
      public 'name' => string 'Uncharted Ocean: Set Sail' (length=25)
  1 =>
    object(stdClass)[4]
      public 'id' => int 19583
      public 'cover' => int 15883
      public 'name' => string 'Uncharted: Fight for Fortune' (length=28)
  2 =>
    object(stdClass)[5]
      public 'id' => int 26193
      public 'cover' => int 85149
      public 'name' => string 'Uncharted: The Lost Legacy' (length=26)
  3 =>
    object(stdClass)[6]
      public 'id' => int 19609
      public 'cover' => int 85164
      public 'name' => string 'Uncharted: Fortune Hunter' (length=25)
  4 =>
    object(stdClass)[7]
      public 'id' => int 7331
      public 'cover' => int 81917
      public 'name' => string 'Uncharted 4: A Thief's End' (length=26)
```

## Query Builder with Options

The [IGDB Query Builder](#igdb-query-builder) still supports the legacy `$options` array to parameterize the query.

>:warning Using the Builder this way is not recommended as this functionality may be removed in future versions. Use the [builder approach](#builder-approach) instead.

**Code**

```php
<?php

    // importing the wrapper
    require_once "class.igdb.php";

    // instantiating the wrapper
    $igdb = new IGDB("{client_id}", "{access_token}");

    // instantiating the builder
    $builder = new IGDBQueryBuilder();

    // creating the options array
    $options = array(
        // searching for games LIKE uncharted
        "search" => "uncharted",
        // we want to see these fields in the results
        "fields" => array("id", "name", "cover"),
        // we only need maximum 5 results per query (pagination)
        "limit" => 5,
        // we would like to show the third page; fetch the results from the tenth element (pagination)
        "offset" => 10
    );

    try {
        // adding your $options array with the options method
        $builder->options($options);

        // building the query
        $query = $builder->build();

        // executing the query
        $games = $igdb->game($query);

        // showing the results
        var_dump($games);
    } catch (IGDBInvalidParameterException $e) {
        // an invalid parameter is passed to the query builder
        echo $e->getMessage();
    } catch (IGDBEnpointException $e) {
        // a non-successful response recieved from the IGDB API
        echo $e->getMessage();
    }

?>
```

**Result**

```text
array (size=5)
  0 =>
    object(stdClass)[3]
      public 'id' => int 125062
      public 'cover' => int 83686
      public 'name' => string 'Uncharted Ocean: Set Sail' (length=25)
  1 =>
    object(stdClass)[4]
      public 'id' => int 19583
      public 'cover' => int 15883
      public 'name' => string 'Uncharted: Fight for Fortune' (length=28)
  2 =>
    object(stdClass)[5]
      public 'id' => int 26193
      public 'cover' => int 85149
      public 'name' => string 'Uncharted: The Lost Legacy' (length=26)
  3 =>
    object(stdClass)[6]
      public 'id' => int 19609
      public 'cover' => int 85164
      public 'name' => string 'Uncharted: Fortune Hunter' (length=25)
  4 =>
    object(stdClass)[7]
      public 'id' => int 7331
      public 'cover' => int 81917
      public 'name' => string 'Uncharted 4: A Thief's End' (length=26)
```

## Counting Results

An example to count the matched records.

> When `true` is passed as the second parameter, the return value will be an object with a single property called `count`. For more details on the return values of the endpoint methods please refer to the [return values section](#return-values).

**Code**

```php
<?php

    // importing the wrapper
    require 'class.igdb.php';

    // instantiating the wrapper
    $igdb = new IGDB("{client_id}", "{access_token}");

    // instantiate the query builder
    $builder = new IGDBQueryBuilder();

    try {
        // building the query
        $query = $builder
            // setting a filter to fetch games with rating greater than 75
            ->where(
                array(
                    'field' => 'rating',
                    'postfix' => '>',
                    'value' => 75
                )
            )
            // process the configuration and return a string
            ->build();

        // executing the query
        // note the second true parameter
        $game_count = $igdb->game($query, true);

        // showing the results
        var_dump($game_count);
    } catch (IGDBInvalidParameterException $e) {
        // an invalid parameter is passed to the query builder
        echo $e->getMessage();
    } catch (IGDBEnpointException $e) {
        // a non-successful response recieved from the IGDB API
        echo $e->getMessage();
    }

?>
```

**Result**

```text
object(stdClass)[3]
  public 'count' => int 8081
```

## Expander

Some fields are actually ids pointing to other endpoints. The expander feature is a convenient way to go into these other endpoints and access more information from them in the same query, instead of having to do multiple queries.

**Code**

```php
<?php

    // importing the wrapper
    require 'class.igdb.php';

    // instantiating the wrapper
    $igdb = new IGDB("{client_id}", "{access_token}");

    // instantiate the query builder
    $builder = new IGDBQueryBuilder();

    try {
        // building the query
        $query = $builder
            // fetching the first 2 games by id 1 and 2
            ->id(array(1,2))
            // fields can be expanded with a dot followed by the fields you want to access from a certain endpoint
            ->fields(array("name", "themes.url", "themes.name"))
            // process the configuration and return a string
            ->build();

        // executing the query
        $game_count = $igdb->game($query);

        // showing the results
        var_dump($game_count);
    } catch (IGDBInvalidParameterException $e) {
        // an invalid parameter is passed to the query builder
        echo $e->getMessage();
    } catch (IGDBEnpointException $e) {
        // a non-successful response recieved from the IGDB API
        echo $e->getMessage();
    }

?>
```

**Result**

```text
array (size=2)
  0 =>
    object(stdClass)[3]
      public 'id' => int 1
      public 'name' => string 'Thief II: The Metal Age' (length=23)
      public 'themes' =>
        array (size=3)
          0 =>
            object(stdClass)[4]
              public 'id' => int 1
              public 'name' => string 'Action' (length=6)
              public 'url' => string 'https://www.igdb.com/themes/action' (length=34)
          1 =>
            object(stdClass)[5]
              public 'id' => int 17
              public 'name' => string 'Fantasy' (length=7)
              public 'url' => string 'https://www.igdb.com/themes/fantasy' (length=35)
          2 =>
            object(stdClass)[6]
              public 'id' => int 23
              public 'name' => string 'Stealth' (length=7)
              public 'url' => string 'https://www.igdb.com/themes/stealth' (length=35)
  1 =>
    object(stdClass)[7]
      public 'id' => int 2
      public 'name' => string 'Thief: The Dark Project' (length=23)
      public 'themes' =>
        array (size=3)
          0 =>
            object(stdClass)[8]
              public 'id' => int 1
              public 'name' => string 'Action' (length=6)
              public 'url' => string 'https://www.igdb.com/themes/action' (length=34)
          1 =>
            object(stdClass)[9]
              public 'id' => int 17
              public 'name' => string 'Fantasy' (length=7)
              public 'url' => string 'https://www.igdb.com/themes/fantasy' (length=35)
          2 =>
            object(stdClass)[10]
              public 'id' => int 23
              public 'name' => string 'Stealth' (length=7)
              public 'url' => string 'https://www.igdb.com/themes/stealth' (length=35)
```

## MultiQuery

Using multiquery multiple queries can be executed against the IGDB database using a single query. The multiquery method expects an array of multiquery query strings.

>:info Using the [`build`](#building-the-query) method with a boolean `true` parameter, a query will be returned with a multiquery syntax.

**Code**

```php
<?php

    // importing the wrapper
    require_once "class.igdb.php";

    // instantiate the wrapper
    $igdb = new IGDB("{client_id}", "{access_token}");

    // query builder for the main game
    $main = new IGDBQueryBuilder();

    // query builder for the bundles
    $bundle = new IGDBQueryBuilder();

    try {
        // configuring the main query
        $main
            ->name("Main Game")
            ->endpoint("game")
            ->fields("id,name")
            ->where("id = 25076");

        // configuring the bundle query
        $bundle
            ->name("Bundles")
            ->endpoint("game")
            ->fields("id,name,version_parent,category")
            ->where("version_parent = 25076")
            ->where("category = 0");

        var_dump(
            $igdb->multiquery(
                array(
                    $main->build(true),
                    $bundle->build(true)
                )
            )
        );
    } catch (IGDBInvaliParameterException $e) {
        // a builder property is invalid
        echo $e->getMessage();
    } catch (IGDBEndpointException $e) {
        // something went wront with the query
        echo $e->getMessage();
    }
?>
```

**Result**

```text
array (size=2)
  0 =>
    object(stdClass)[4]
      public 'name' => string 'Main Game' (length=9)
      public 'result' =>
        array (size=1)
          0 =>
            object(stdClass)[5]
              public 'id' => int 25076
              public 'name' => string 'Red Dead Redemption 2' (length=21)
  1 =>
    object(stdClass)[6]
      public 'name' => string 'Bundles' (length=7)
      public 'result' =>
        array (size=3)
          0 =>
            object(stdClass)[7]
              public 'id' => int 103205
              public 'category' => int 0
              public 'name' => string 'Red Dead Redemption 2: Special Edition' (length=38)
              public 'version_parent' => int 25076
          1 =>
            object(stdClass)[8]
              public 'id' => int 103207
              public 'category' => int 0
              public 'name' => string 'Red Dead Redemption 2: Collector's Box' (length=38)
              public 'version_parent' => int 25076
          2 =>
            object(stdClass)[9]
              public 'id' => int 103206
              public 'category' => int 0
              public 'name' => string 'Red dead Redemption 2: Ultimate Edition' (length=39)
              public 'version_parent' => int 25076
```