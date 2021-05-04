---
title: Getting Started
overview: A few thoughts about the tool and the documentation. Setting up your project.
icon: fa-paper-plane
color: green
---

# Introduction

Welcome to the documentation of the IGDB API PHP Wrapper! The main purpose of this class is to provide a simple solution to fetch data from the IGDB Database. The only dependency this class relies on is [Client URL Library (CURL)](php.net/manual/en/book.curl.php), this is how the wrapper sends the requests.

To use this wrapper you will need your own account at Twitch and you will have to request your own `client_id` and `access_token`. For more information in this topic please refer to the [account creation](https://api-docs.igdb.com/#account-creation) and [authentication](https://api-docs.igdb.com/#authentication) sections of the [IGDB Documentation](https://api-docs.igdb.com/).

<blockquote>
The `IGDBUtil` class has a helper method called `authenticate`. With this method you can easily get your `access_token` from the Twitch server.
</blockquote>

# Importing to your project

First of all, you'll need to [download](https://github.com/enisz/igdb/archive/master.zip) the files from the repository. After unzipping the `igdb-master.zip` file you'll have an `src` folder which contains the source code of this wrapper. In this folder you will see three files:
 - IGDB.php: the main IGDB Wrapper class
 - IGDBQueryBuilder.php: the QueryBuilder class which helps parsing your apicalypse query
 - IGDBUtil.php: utility class with helper methods

You can copy these files anywhere in your project, then you will have to simply import it where you need to use it.

```php
<?php
    require_once "IGDB.php";
    require_once "IGDBQueryBuilder.php"; // optional
    require_once "IGDBUtil.php";
?>
```

After the files are imported, you are ready to use the wrapper!

# Basic Usage Example

To use the wrapper you will have to instantiate the wrapper with your `client_id` and `access_token`.

```php
<?php

    require_once "IGDB.php";

    $igdb = new IGDB("{client_id}", "{access_token}");

?>
```

# Sending a query

The `$igdb` object will expose all of the endpoint methods which will send your queries. The method names are "snake cased" versions of the endpoint names from the IGDB Documentation. For example, if you want to fetch data from the [Release Date](https://api-docs.igdb.com/#release-date) endpoint, you will have to use the `release_date` method of the wrapper.

<blockquote>
These are simple examples of the usage of the wrapper. You can find detailed documentation on each at the respective sections of this documentation.
</blockquote>

## Fetching the records

The endpoint methods are expecting at least on parameter: the apicalypse query string to send.

```php
try {
    $igdb->release_date("fields *; limit 1;");
} catch (IGDBEndpointException $e) {
    echo $e->getMessage();
}
```

Always make sure to enclose every endpoint method calls in a try...catch block. The endpoint methods will throw an `IGDBEndpointException" in case of any errors. If there are problems with the query you can catch it there. Since this query is valid you will see the below output:

```text
array (size=1)
  0 =>
    object(stdClass)[2]
      public 'id' => int 170111
      public 'category' => int 0
      public 'created_at' => int 1558826090
      public 'date' => int 1552953600
      public 'game' => int 76870
      public 'human' => string 'Mar 19, 2019' (length=12)
      public 'm' => int 3
      public 'platform' => int 48
      public 'region' => int 8
      public 'updated_at' => int 1558826519
      public 'y' => int 2019
      public 'checksum' => string 'eda4c3fc-5ce2-5c43-9a96-bfd20b24ab88' (length=36)
```

As you can see, the result is an **array of objects**, therefore you can access the values with the [object operator](https://www.php.net/manual/en/language.oop5.properties.php).

```php
try {
    $result = $igdb->release_date("fields *; limit 1;");

    // printing the id of the first item with the object operator
    echo $result[0]->id;
} catch (IGDBEndpointException $e) {
    echo $e->getMessage();
}
```

## Fetching the number of matched records

You can also fetch the number of records matching your filtering criteria. In this case you will have to pass a second `boolean` argument to the endpoint method which tells the wrapper to reach the endpoint's `count` function. This way the IGDB API will send the number of matched records as a response.

```php
try {
    // note the second TRUE parameter
    $igdb->release_date("fields *; where id = (170111, 165447, 85558);", true);
} catch (IGDBEndpointException $e) {
    echo $e->getMessage();
}
```

The return value of the call will be:

```text
object(stdClass)[2]
  public 'count' => int 3
```

In this case you can see from the output that there are three items in the database matching the filtering criteria (id field matching the id's in the list). You can access this value from the `$result` object's `count` property

```php
// this will print 3
echo $result->count;
```

# Building a query with IGDBQueryBuilder

There is a class called `IGDBQueryBuilder` which helps you to construct your queries. To use this you will have to import it to your project separately as this is not part of the `IGDB` class.

The building should be enclosed in try...catch block too. If an invalid parameter is passed to these methods, an `IGDBInvalidParameterException` will be thrown.

```php
<?php

    require_once "IGDBQueryBuilder.php";

    $builder = new IGDBQueryBuilder();

    try {
        $query = $builder
            ->fields("*")
            ->where(
                array(
                    "field" => "id",
                    "postfix" => "=",
                    "value" => array(170111, 165447, 85558)
                )
            )
            ->build();

        echo $query;
    } catch (IGDBInvalidParameterException $e) {
        echo $e->getMessage();
    }
?>
```

The code above will construct a query according to the passed parameters and will return the result as a query string when the `build` method is called. The output of the code is below:

```text
fields *; where id = (170111, 165447, 85558);
```

This string can be passed straight to the endpoint method

```php
<?php

    require_once "IGDB.php";
    require_once "IGDBQueryBuilder.php";

    $igdb = new IGDB("{client_id}", "{access_token}");
    $builder = new IGDBQueryBuilder();

    try {
        $builder
            ->fields("*")
            ->where(
                array(
                    "field" => "id",
                    "postfix" => "=",
                    "value" => array(170111, 165447, 85558)
                )
            );

        var_dump($igdb->release_date($builder->build()));
    } catch (IGDBInvalidParameterException $e) {
        // invalid parameter is passed to the builder
        echo $e->getMessage();
    } catch (IGDBEndpointException $e) {
        // something went wrong with the query
        echo $e->getMessage();
    }

?>
```

The output of the code above is below:

```text
array (size=3)
  0 =>
    object(stdClass)[3]
      public 'id' => int 85558
      public 'category' => int 0
      public 'created_at' => int 1497948084
      public 'date' => int 566092800
      public 'game' => int 38035
      public 'human' => string 'Dec 10, 1987' (length=12)
      public 'm' => int 12
      public 'platform' => int 121
      public 'region' => int 5
      public 'updated_at' => int 1497948122
      public 'y' => int 1987
      public 'checksum' => string 'df4b2c61-916b-d823-cdf7-286f06aeac62' (length=36)
  1 =>
    object(stdClass)[4]
      public 'id' => int 165447
      public 'category' => int 0
      public 'created_at' => int 1550867412
      public 'date' => int 1561420800
      public 'game' => int 115477
      public 'human' => string 'Jun 25, 2019' (length=12)
      public 'm' => int 6
      public 'platform' => int 6
      public 'region' => int 8
      public 'updated_at' => int 1550867489
      public 'y' => int 2019
      public 'checksum' => string '3f7a3186-1622-4da6-fb05-d223cea14454' (length=36)
  2 =>
    object(stdClass)[5]
      public 'id' => int 170111
      public 'category' => int 0
      public 'created_at' => int 1558826090
      public 'date' => int 1552953600
      public 'game' => int 76870
      public 'human' => string 'Mar 19, 2019' (length=12)
      public 'm' => int 3
      public 'platform' => int 48
      public 'region' => int 8
      public 'updated_at' => int 1558826519
      public 'y' => int 2019
      public 'checksum' => string 'eda4c3fc-5ce2-5c43-9a96-bfd20b24ab88' (length=36)
```