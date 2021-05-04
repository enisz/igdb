---
title: Examples
overview: IGDB Wrapper in action. Covering most of the functionality.
icon: fa-hand-holding-heart
color: orange
---

# Basic Example

```php
<?php

    require '../src/class.igdb.php';

    // Instantiate the class
    $IGDB = new IGDB("{client_id}", "{access_token}");

    // Setting up the query parameters
    $query = array(
        'search' => 'uncharted', // searching for games LIKE uncharted
        'fields' => array(       // we want to see these fields in the results
            'id',
            'name',
            'cover'
        ),
        'limit' => 5,            // we only need maximum 5 results per query (pagination)
        'offset' => 10           // we would like to show the third page; fetch the results from the tenth element (pagination)
    );

    try {
        // Running the query against IGDB; passing the query
        $result = $IGDB->game($query);

        // Showing the result
        var_dump($result);
    } catch (Exception $e) {
        // Catching Exceptions, if there is any
        echo $e->getMessage();
    }

?>
```

# Apicalypse Query

```php
<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("{client_id}", "{access_token}");

    // Apicalypse formatted query string
    $query = 'search "uncharted"; fields id,name,cover; limit 5; offset 10;';

    var_dump($IGDB->game($query));

?>
```

# Close and Reinit CURL Handler

```php
<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("{client_id}", "{access_token}");

    $query = array(
        'search' => 'wolfenstein',
        'fields' => 'id, name',
        'limit' => 2
    );

    $result = $IGDB->game($query);

    var_dump($result);

    // Closing the CURL handler
    $IGDB->curl_close();

    /*

        Some other code

    */

    // If you want to run another query after closing the CURL handler
    // You have to reinitialize it in order to make it work
    $IGDB->curl_reinit();

    // You can define a new query, or you can modify the previous one
    // Now I'm defining a new one to use a different endpoint with the same instance
    $second_options = array(
        'search' => 'xbox',
        'fields' => 'id, name, slug'
    );

    $second_result = $IGDB->platform($second_options);

    var_dump($second_result);

?>
```

# Count

```php
<?php

    require '../src/class.igdb.php';

    $IGDB = new IGDB("{client_id}", "{access_token}");

    $query = array(
        'where' => array(
            'field' => 'rating', // rating
            'postfix' => '>',    // greater than
            'value' => 75        // 75
        )
    );

    // Will return the number of all games with rating more than 75
    // Note the second true parameter
    $result = $IGDB->game($query, true);

    var_dump($result);

?>
```

# Expander

```php
<?php

    // As per the IGDB Documentation the expander parameter is "dead".
    // Still, expanded fields can now be added as normal fields and the API figures out the rest.

    include '../src/class.igdb.php';

    $IGDB = new IGDB("{client_id}", "{access_token}");

    $query = array(
        'id' => array(1,2,3),   // first three games by ID
        'fields' => array(
            'name',
            'themes.url',       // Fields can be expanded with a dot followed by the fields you want to access from a certain endpoint
            'themes.name',
            'themes.slug'
        )
    );

    $result = $IGDB->game($query);

    var_dump($result);

?>
```

# ID

```php
<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("{client_id}", "{access_token}");

    // One ID is passed as parameter
    $query = array(
        'id' => 1,
        'fields' => array(
            'id',
            'name'
        )
    );

    $result = $IGDB->game($query);

    // Only one result in the array
    var_dump($result);

    // Now update the ID fields with multiple ID-s (pass ID-s as array)
    // Note, that the fields parameter is untouched
    $query['id'] = array(1,2,3);

    $result = $IGDB->game($query);

    // In this case you will have 3 games in the result array
    var_dump($result);

?>
```

# Multiquery

```php
<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("{client_id}", "{access_token}");

    // Leaving the optional third parameter, sending the request without any filters
    // Also asking for the record count by providing /count after the endpoint name
    // The method name has to be the IGDB endpoint name instead of the wrapper class method name => platforms instead of platform
    var_dump($IGDB->mutliquery("platforms/count", "Count of Platforms"));

    $query = array(
        "fields" => array(
            "name",
            "platforms.name"
        ),
        "where" => array(
            "platforms != null",
            "platforms = {48}"
        ),
        "limit" => 1
    );

    // Passing a query array as a third parameter to filter the results
    var_dump($IGDB->mutliquery("games", "Playstation Games", $query));

?>
```

# Platform

```php
<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("{client_id}", "{access_token}");

    $query = array(
        'search' => 'xbox',     // Searching the platforms for XBOX.
        'fields' => array(      // Showing ID, NAME and SLUG fields in the results
            'id',
            'name',
            'slug'
        )
    );

    $result = $IGDB->platform($query);

    var_dump($result);

?>
```

# Search

```php
<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("{client_id}", "{access_token}");

    $query = array(
        'search' => 'wolfenstein 2 new colossus',   // As search parameter you can pass any string you want to find
        'fields' => array(
            'id',
            'name'
        )
    );

    $result = $IGDB->game($query);

    var_dump($result);

?>
```

# Sort

```php
<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("{client_id}", "{access_token}");

    // Note: Search is sorting on relevancy and therefore sort is not applicable on search
    // Do not provide search in queries where sort is applied
    $query = array(
        'id' => array(100,200,300),     // Fetching games with these ID's
        'fields' => array(              // Showing ID and NAME fields in the results
            'id',
            'name'
        ),
        'sort' => array(
            'field' => 'name',          // ORDER the results by NAME field
            'direction' => 'asc',       // ASCENDING order
        )
    );

    /*
        You can also provide the sort parameter as a string with apicalypse syntax.

        $query['sort'] = 'name asc';
    */

    $result = $IGDB->game($query);

    var_dump($result);

?>
```

# Where - Multiple Criteria

```php
<?php

    require '../src/class.igdb.php';

    $IGDB = new IGDB("{client_id}", "{access_token}");

    $query = array(
        'fields' => 'id, name, platforms, genres',   // we want to see these fields in the result
        'where' => array(
            array(
                'field' => 'release_dates.platform', // filtering by the platform field
                'postfix' => '=',                    // equals postfix
                'value' => 8                         // looking for platforms with the ID equals to 8
            ),
            array(
                'field' => 'total_rating',           // filtering by the total_rating field
                'postfix' => '>=',                   // greater than or equals to
                'value' => 70                        // looking for the total_rating greater than or equals to 70
            ),
            array(
                'field' => 'genres',                 // filtering by the genres field
                'postfix' => '=',                    // equals to
                'value' => 4                         // looking for the genres equals to 4
            )
        )
    );

    $result = $IGDB->game($query);

    var_dump($result);

?>
```

# Where - Single Criteria

```php
<?php

    require '../src/class.igdb.php';

    $IGDB = new IGDB("{client_id}", "{access_token}");

    $query = array(
        'fields' => 'id, name, platforms, genres', // we want to see these fields in the result
        'where' => array(
            'field' => 'release_dates.platform',   // filtering by the platform field
            'postfix' => '=',                      // equals postfix
            'value' => 8                           // looking for platforms with the ID equals to 8
        )
    );

    /*
        You can also provide the filter parameter as a string with apicalypse syntax.

        $query['where'] = 'release_dates.platform = 8';
    */

    $result = $IGDB->game($query);

    var_dump($result);

?>
```