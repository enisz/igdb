---
overview: A few thoughts about the project and the documentation.
icon: fa-map-signs
---

# Getting Started

Welcome to the IGDB Wrapper documentation! This documentation will cover all of the functionalities of this wrapper with lots of example codes. To personalise these codes you can add your own tokens to this documentation on the main page (click on the logo in the top left corner to go back). This way every code example where the tokens are required will contain your own tokens.

The wrapper's main purpose is to provide a simple solution to fetch data from IGDB's database using PHP. The wrapper contains endpoint methods for every IGDB API endpoints and even more!

To have access to IGDB's database you have to register a Twitch Account and have your own `client_id` and `access_token`. Refer to the [Account Creation](https://api-docs.igdb.com/#account-creation) and [Authentication](https://api-docs.igdb.com/#authentication) sections of the [IGDB API Documentation](https://api-docs.igdb.com/) for details.

## Setting up your project

The project has multiple PHP files which are in the `src` folder of the repository:
 - **IGDB.php**: the IGDB wrapper itself
 - **IGDBEndpointException.php**: an exception thrown by the IGDB wrapper endpoint methods
 - **IGDBInvalidParameterException.php**: an exception thrown by the Query Builder configuring methods
 - **IGDBQueryBuilder.php**: the Query Builder
 - **IGDBUtil.php**: utility and helper methods

These files can be imported one-by-one but it is highly recommended to import the `class.igdb.php` file which handles all the imports for you.

```php
<?php

    require_once "class.igdb.php";

?>
```

After the import, you are ready to use the wrapper.

> If you decide to import the files separately, make sure to check the source code since the exception classes are imported in the respective files where they are needed with hardcoded paths. The files has to be in the same folder to make it work!

## Instantiating the wrapper

The wrapper will need your `client_id` and [generated](https://api-docs.igdb.com/#authentication) `access_token`.

```php
<?php

    require_once "class.igdb.php";

    $igdb = new IGDB("{client_id}", "{access_token}");

?>
```

> The wrapper itself does not validate your tokens. If your credentials are invalid you will get an error response from the endpoint methods.

## Sending Queries to IGDB

When your `$igdb` object is ready you can send your queries to the IGDB database right away. There are multiple endpoint methods which will allow you to fetch data from the respective [IGDB Endpoint](https://api-docs.igdb.com/#endpoints). These endpoint methods in the wrapper will accept [apicalypse](https://api-docs.igdb.com/#apicalypse-1) formatted query strings.

```php
<?php

    require_once "class.igdb.php";

    $igdb = new IGDB("{client_id}", "{access_token}");

    try {
        $result = $igdb->game('search "uncharted 4"; fields id,name; limit 1;');
    } catch (IGDBEndpointException $e) {
        // Something went wrong, we have an error
        echo $e->getMessage();
    }

?>
```

Since the query is valid we have the `$result` array containing our matched records.

```text
array (size=1)
  0 =>
    object(stdClass)[2]
      public 'id' => int 7331
      public 'name' => string 'Uncharted 4: A Thief's End' (length=26)
```

> Refer to the [IGDB Wrapper Class Section](#x) of this documentation.

## Building Queries

There is a helper class to build apicalypse queries called `IGDBQueryBuilder`. With this class you can easily build parameterized queries.

```php
<?php

    require_once "class.igdb.php";

    $builder = new IGDBQueryBuilder();

    try {
        $query = $builder
            ->search("uncharted 4")
            ->fields("id, name")
            ->limit(1)
            ->build();
    } catch (IGDBInvalidParameterException $e) {
        // Invalid argument passed to one of the methods
        echo $e->getMessage();
    }
?>
```

The parameters in the above example are valid, so the `$query` variable will hold the valid apicalypse string.

```text
fields id,name; search "uncharted 4"; limit 1;
```