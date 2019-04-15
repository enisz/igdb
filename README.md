**This class is DEPRECATED**

This class was written to the V2 api of IGDB. This version of the api will be discontinued and unsupported from 30th of June, 2019. 

# **Internet Game Database API Class Documentation**

<!-- toc -->

- [Introduction](#introduction)
- [Initializing Class](#initializing-class)
- [Class Properties](#class-properties)
  * [API URL](#api-url)
  * [API Key](#api-key)
  * [CURL Resource Handler](#curl-resource-handler)
- [Options Parameters](#options-parameters)
  * [ID](#id)
  * [Search](#search)
  * [Fields](#fields)
  * [Limit](#limit)
  * [Offset](#offset)
  * [Expand](#expand)
  * [Filters](#filters)
  * [Order](#order)
- [Public Methods](#public-methods)
  * [Close CURL Session](#close-curl-session)
  * [Reinitialize CURL session](#reinitialize-curl-session)
  * [Custom Query](#custom-query)
  * [Count](#count)
  * [Get Request Information](#get-request-information)
- [Private Methods](#private-methods)
  * [Initialize CURL Session](#initialize-curl-session)
  * [Construct URL](#construct-url)
  * [Stringify Options](#stringify-options)
  * [Executing Query](#executing-query)
- [Endpoints](#endpoints)
  * [Character](#character)
  * [Collection](#collection)
  * [Company](#company)
  * [Credit](#credit)
  * [Feed](#feed)
  * [Franchise](#franchise)
  * [Games](#games)
  * [Game Engine](#game-engine)
  * [Game Mode](#game-mode)
  * [Genre](#genre)
  * [Keyword](#keyword)
  * [Page](#page)
  * [Person](#person)
  * [Platform](#platform)
  * [Player Perspective](#player-perspective)
  * [Pulse](#pulse)
  * [Pulse Group](#pulse-group)
  * [Pulse Source](#pulse-source)
  * [Release Date](#release-date)
  * [Review](#review)
  * [Theme](#theme)
  * [Title](#title)
  * [Versions](#versions)
- [Example Query](#example-query)
- [Return Values](#return-values)
- [Changes](#changes)
  * [v1.0.5 - March 11, 2019](#v105---march-11-2019)
  * [v1.0.4 - March 25, 2018](#v104---march-25-2018)
  * [v1.0.3 - March 18, 2018](#v103---march-18-2018)
  * [v1.0.2 - March 17, 2018](#v102---march-17-2018)
  * [v1.0.1 - March 16, 2018](#v101---march-16-2018)

<!-- tocstop -->

## Introduction
The class's main purpose is to provide a simple solution to fetch data from IGDB's database using PHP. Method names are matching the IGDB's endpoint names.

To use IGDB's database you have to register an account at [https://api.igdb.com](https://api.igdb.com).

## Initializing Class
``public IGDB::__construct ( string $key ) : void``<br/>
You can initialize the class by passing your IGDB API Key to the constructor. The credentials will be verfied only by IGDB server when you send the query.

```php
$IGDB = new IGDB('<YOUR API KEY>');
```

## Class Properties

### API URL
``private IGDB::$API_URL ( string )``: IGDB API URL. This is the address you have to send your query to. At the moment it is fixed (https://api-endpoint.igdb.com), so you don't have to change it.

### API Key
``private IGDB::$API_KEY ( string )``: your personal API Key provided by IGDB. It's value is set by [``IGDB::__construct()``](#initializing-class).

### CURL Resource Handler
``private IGDB::$CH ( resource )``: CURL resource handler. Return value of [``curl_init()``](http://php.net/curl_init). You can close  [``IGDB::close_handler()``](##close-curl-session) and reinitialize [``IGDB::reinit_handler()``](#reinitialize-curl-session) the session.

## Options Parameters
For every [endpoint method](#endpoints) that fetching data from IGDB you will need to provide an ``$options`` array, that contains the parameters of the query.

Let's see an example options array:
```php
$options = array(
	'search' => 'uncharted',         // searching elements by the name UNCHARTED
	'fields' => array('id', 'name'), // the result object will contain only the ID and NAME fields
	'order' => 'name:asc',           // ordering the results ASCENDING by NAME field
	'offset' => 15,                  // second page of a 15 element result
	'limit' => 15                    // 15 elements per query
);
```

> Note: the order of the parameters in the ``$options`` array does not matter!

### ID
``id ( array | number ) [ optional ]``: one ore more item ID's. When ID is provided, the ``search`` parameter will be ignored.

```php
// Providing one ID
$options = array(
  'id' => 5
)

// Providing several ID's
$options = array(
  'id' => array(5, 6, 7, 8)
);
```

### Search
``search ( string ) [ optional ]``: the query will search the name field looking for this value. If ``id`` is provided in the same options array, than this value will be ignored.

```php
// Provide search string
$options = array(
  'search' => 'star wars'
);
```

### Fields
``fields ( array | string ) [ optional ]``: the fields you want to see in the result array.
 - If not provided only the ID field will be returned.
 - If the field list is provided as string, you have to separate the field names using comma (id,name).
 - If you want to see every fields in the result you have to pass a star (*).

```php
// Provide single or multiple fields as a string separated by comma
$options = array(
  'fields' => 'id,name'
);

// Provide single or multiple fields as an array
$options = array(
  'fields' => array('id', 'name');
);

// Get all fields in the result
$options = array(
  'fields' => '*'
);
```

> [IGDB Fields Documentation](https://igdb.github.io/api/references/fields/)

### Limit
``limit ( number ) [ optional ]``: the maximum number of results in a single query. This value must be a number between 1 and 50.

```php
// Provide a limit parameter
$options = array(
  'limit' => 20
);
```

> [IGDB Pagination Documentation](https://igdb.github.io/api/references/pagination/)

### Offset
``offset ( number ) [ optional ]``: this will start the result list at the provided value and will give ``limit`` number of results. This value must be 0 or greater.

```
// Provide an offset parameter
$options = array(
  'offset' => 5
);
```

> [IGDB Pagination Documentation](https://igdb.github.io/api/references/pagination/)

### Expand
``expand ( array | string ) [ optional ]``: the expander feature is used to combine multiple requests.

```php
// Provide single or multiple expander rule as a string separated by comma
$options = array(
  'expand' => array('game', 'themes')
);

// Provide single or multiple expander rule as an array
$options = array(
  'expand' => 'game,themes'
);
```

> [IGDB Expander Documentation ](https://igdb.github.io/api/references/expander/)

### Filters
``filter ( string | array ) [ optional ]``: filters are used to swift through results to get what you want. You can exclude and include results based on their properties. If you provide the filter parameters as an array, you must have three values in it with the following indexes:
 - ``field``: The name of the field you want to apply the filter to
 - ``postfix``: The postfix you want to use with the filter. Refer to the IGDB Filters Documentation for available postfixes.
 - ``value``: The value of the filter.

```php
// Provide a single filter rule as an array
// In this case you must have field, postfix and value elements in the array
$options = array(
  'field' => 'release_dates.platform',
  'postfix' => 'eq',
  'value' => 8
);

// Provide multiple filter rules as an array
// In this case you must have field, postfix and value elements in the arrays
$options = array(
  array(
    'field' => 'release_dates.platform',
    'postfix' => 'eq',
    'value' => 8
  ),
  array(
    'field' => 'total_rating',
    'postfix' => 'gte',
    'value' => 70
  )
);
```

You can provide the filter parameter as string. In this case you can pass the string as you would as an URL segment:
```php
// Provide a single filter rule as a string
$options = array(
  'filter' => '[release_dates.platform][eq]=8'
);
```

> [IGDB Filters Documentation](https://igdb.github.io/api/references/filters/)

### Order
``order ( string | array ) [ optional ]``: ordering (sorting) is used to order results by a specific field. When not provided, the results will be ordered ASCENDING by ID. IF you provide the Order parameter as an array, you must have at least two (and an optional third) values in it with the following indexes:
 - ``field``: The field you want to do the ordering by
 - ``direction``: The direction of the ordering. It must be either ``asc`` for ascending or ``desc`` for descending ordering.
 - ``subfilter [optional]``: You can apply this optional subfilter for even more complex ordering. Available subfilters are: ``min``, ``max``, ``avg``, ``sum``, ``median``.

```php
// Provide an order parameter as an array
$options = array(
    'order' => array(
        'field' => 'release_dates.date',
        'direction' => 'desc',
        'subfilter' => 'min'
    )
);
```

You can also provide the order parameter as string. In this case you can pass the string as you would as an URL segment:
```php
// Provide an order parameter as a string
$options = array(
  'order' => 'release_dates.date:desc:min'
);
```

> [IGDB Ordering Documentation](https://igdb.github.io/api/references/ordering/)

## Public Methods

### Close CURL Session
``public IGDB::close_handler ( ) : void``<br/>
You can close the CURL session handler manually if you need to. The class will not do it by itself after any query in case you need to start several queries. After closing the session you will not be able to start new query with the actual instance of the class.

### Reinitialize CURL session
``public IGDB::reinit_handler ( ) : void``<br/>
After you closed the CURL session manually with [``IGDB::close_handler()``](#close-curl-session) than you will not be able to run any query against IGDB with the current instance of the class. However, if you need to run a query again, just call this method, and the CURL handler will be reinitialized.

### Custom Query
``public IGDB::custom_query ( string $url ) : array``<br/>
You can launch manually assembled queries with this method. Great solution for testing purposes. This method automatically executes the query against IGDB and will return the result array.

<p>Parameters:</p>

 - ``$url ( string )``: this URL will be appended to the API URL.

<p>Return</p>

The IGDB response will be returned as an array. Refer to the [return values](#return-values) section of the readme.

Example
```php
$result = $IGDB->custom_query('games/?search=uncharted&fields=id,name&order=name:asc');
```

### Count
``public IGDB::count ( string $endpoint, array ?$filters = NULL ) : number``<br/>
You can count all the records on the given ``$endpoint``. If no ``$filter`` is provided, then all the records will be counted. The ``$filters`` parameter is an optional [``$option``](#options-parameters) array with only a filter parameter in it.

<p>Parameters</p>

 - ``$endpoint ( string )`` : the name of the endpoint you want to count the records on. Refer to the [endpoints section](#endpoints) or [IGDB Endpoint Documentation](https://igdb.github.io/api/endpoints/) for endpoint names.
 - ``$filters [optional] ( array )`` : This is a simple [option](#options-parameters) array containing only filter parameters (single or multiple).

 <p>Return</p>

 This method will return the number of counted records.

```php
$options = array(       // all games will be counted
  'filter' => 'rating', // with rating
  'postfix' => 'gt',    // greater than
  'value' => 75         // 75
);

// Will return the number of all games with rating more than 75
$IGDB->count('game', $options);
```

You can call this method with more [filters](#filters) (the same way you would do for an endpoint method) or even without them.

```php
  // Calling the method without filters
  $IGDB->count('game');
```

### Get Request Information
``public IGDB::get_request_info() ( ) : array``<br/>
If you need detailed information regarding the latest query, you can get it by this method. It returns the return value of [``curl_getinfo()``](http://php.net/curl_getinfo) php function.

## Private Methods
These methods cannot be accessed from outside of the class. These are responsible to check option parameters, constructing URL's and query strings.

### Initialize CURL Session
``private IGDB::_init_curl ( ) : void``<br/>
This method creates the CURL session and sets a few additional configuration to it.

### Construct URL
``private IGDB::_construct_url ( string $endpoint, array $options ) : string``<br/>
This method is responsible for constructing the complete request URL. It is done by calling the [``IGDB::_stringify_options()``](#stringify-options) method. Returns the complete constructed request URL.

<p>Parameters:</p>

- ``$endpoint ( string )``: The name of the endpoint
- ``$options ( array )``: The array holding the parameters for the query.

<p>Return</p>

Will return the full constructed URL with the query string constructed from the ``$options`` array.

### Stringify Options
``private IGDB::_stringify_options ( array $options, boolean ?$add_defaults = TRUE ) : string``<br/>
This method is checking every parameter passed to it. Throwing Exceptions in case of errors. If everything is fine, the complete options array is returned as a query string.

<p>Parameters</p>

 - ``$options ( array )`` : The array holding the option parameters that needs to be stringified.
 - ``$add_defaults ( boolean )`` : This parameter will tell if the default parameters should be added to the query string. 

<p>Return</p>
The method will return the constructed query string.

### Executing Query
``private IGDB::_exec_query ( string $url ) : array`` - This method will start the query against IGDB. The ``$url`` parameter is constructed by the [``IGBB::_construct_url()``](#construct-url) method. Returns the JSON decoded response from IGDB as an array.

<p>Parameters</p>
 
 - ``$url ( string )`` : The complete constructed URL with the query string.

<p>Return</p>
The method returns the IGDB response as an array.

## Endpoints
Every endpoint method takes an ``$options`` array as a parameter to set up the query (check the [Options Parameters](#options-parameters) Section for more details about the available parameters and values). As a second optional parameter you can pass a boolean ``$execute``.

These methods are returning an array with objects decoded from IGDB response JSON by default. If you provide boolean ``FALSE`` as a second parameter, it will prevent the class to execute the query, and returns the constructed URL instead.

Exceptions are thrown in any case of errors.

Refer to the [Return Values](#return-values) Section for more details about the return values of these methods.

<p>Parameters</p>

 - ``$options ( array )`` : The options array
 - ``$execute (boolean) [optional]`` : Whether you want to run the query against IGDB. If this value is ``FALSE`` then the constructed URL will be returned.

 <p>Return</p>

 If ``$execute`` parameter is set to ``FALSE`` the constructed URL will be returned. Otherwise the IGDB response as an array.

### Character
``public IGDB::character ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using CHARACTER endpoint.
> [IGDB CHARACTER Endpoint Documentation](https://igdb.github.io/api/endpoints/character/)

### Collection
``public IGDB::collection ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using COLLECTION endpoint.
> [IGDB COLLECTION Endpoint Documentation](https://igdb.github.io/api/endpoints/collection/)

### Company
``public IGDB::company ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using COMPANY endpoint.
> [IGDB COMPANY Endpoint Documentation](https://igdb.github.io/api/endpoints/company/)

### Credit
``public IGDB::credit ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using CREDIT endpoint.
> [IGDB CREDIT Endpoint Documentation](https://igdb.github.io/api/endpoints/credit/)

### Feed
``public IGDB::feed ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using FEED endpoint.
> [IGDB FEED Endpoint Documentation](https://igdb.github.io/api/endpoints/feed/)

### Franchise
``public IGDB::franchise ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using FRANCHISE endpoint.
> [IGDB FRANCHISE Endpoint Documentation](https://igdb.github.io/api/endpoints/franchise/)

### Games
``public IGDB::game ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using GAME endpoint.
> [IGDB GAME Endpoint Documentation](https://igdb.github.io/api/endpoints/game/)

### Game Engine
``public IGDB::game_engine ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using GAME ENGINE endpoint.
> [IGDB GAME ENGINE Endpoint Documentation](https://igdb.github.io/api/endpoints/game-engine/)

### Game Mode
``public IGDB::game_mode ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using GAME MODE endpoint.
> [IGDB GAME MODE Endpoint Documentation](https://igdb.github.io/api/endpoints/game-mode/)

### Genre
``public IGDB::genre ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using GENRE endpoint.
> [IGDB GENRE Endpoint Documentation](https://igdb.github.io/api/endpoints/genre/)

### Keyword
``public IGDB::keyword ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using KEYWORD endpoint.
> [IGDB KEYWORD Endpoint Documentation](https://igdb.github.io/api/endpoints/keyword/)

### Page
``public IGDB::page ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using PAGE endpoint.
> [IGDB PAGE Endpoint Documentation](https://igdb.github.io/api/endpoints/page/)

### Person
``public IGDB::person ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using PERSON endpoint.
> [IGDB PERSON Endpoint Documentation](https://igdb.github.io/api/endpoints/person/)

### Platform
``public IGDB::platform ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using PLATFORM endpoint.
> [IGDB PLATFORM Endpoint Documentation](https://igdb.github.io/api/endpoints/platform/)

### Player Perspective
``public IGDB::player_perspective ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using PLAYER PERSPECTIVE endpoint.
> [IGDB PLAYER PERSPECTIVE Endpoint Documentation](https://igdb.github.io/api/endpoints/player-perspective/)

### Pulse
``public IGDB::pulse ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using PULSE endpoint.
> [IGDB PULSE Endpoint Documentation](https://igdb.github.io/api/endpoints/pulse/)

### Pulse Group
``public IGDB::pulse_group ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using PULSE GROUP endpoint.
> [IGDB PULSE GROUP Endpoint Documentation](https://igdb.github.io/api/endpoints/pulse-group/)

### Pulse Source
``public IGDB::pulse_source ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using PULSE SOURCE endpoint.
> [IGDB PULSE SOURCE Endpoint Documentation](https://igdb.github.io/api/endpoints/pulse-source/)

### Release Date
``public IGDB::release_date ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using RELEASE DATE endpoint.
> [IGDB RELEASE DATE Endpoint Documentation](https://igdb.github.io/api/endpoints/release-date/)

### Review
``public IGDB::review ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using REVIEW endpoint.
> [IGDB REVIEW Endpoint Documentation](https://igdb.github.io/api/endpoints/review/)

### Theme
``public IGDB::theme ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using THEME endpoint.
> [IGDB THEME Endpoint Documentation](https://igdb.github.io/api/endpoints/theme/)

### Title
``public IGDB::title ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using TITLE endpoint.
> [IGDB TITLE Endpoint Documentation](https://igdb.github.io/api/endpoints/title/)

### Versions
``public IGDB::versions ( array $options, boolean ?$execute = TRUE ) : array | string`` <br/>
Fetch data using VERSIONS endpoint.
> [IGDB VERSIONS Endpoint Documentation](https://igdb.github.io/api/endpoints/versions/)

## Example Query
Let's do a simple example. Get the third page of a game list, where the game we are looking for is LIKE "uncharted" (this example is available in _examples/\_basic_example.php_)
``` php
<?php

    require 'class.igdb.php';

    // Instantiate the class
    $IGDB = new IGDB('<YOUR API KEY>');

    // Setting up the query parameters
    $options = array(
        'search' => 'uncharted', // searching for the game called "uncharted"
        'fields' => array( // we want to see these values in the results
            'id',
            'name',
            'cover',
        ),
        'limit' => 5, // we only need maximum 5 results per query (pagination)
        'offset' => 10, // we would like to show the third page; fetch the results from the sixth element
        'order' => 'name:asc' // order the results ASCENDING by the filed NAME
    );

    try
    {
        // Running the query against IGDB; passing the options parameter
        $result = $IGDB->game($options);

        // Showing the result
        var_dump($result);
    }

    catch (Exception $e)
    {
        // Catching Exceptions, if there is any
        echo $e->getMessage();
    }

?>
```

## Return Values
Every [Endpoint Method](#endpoints) can return two different type of results, depending on the second parameter provided for them:
 - By default the second ``$execute`` parameter is boolean ``TRUE``. this means, that the query string will be constructed, then will be ran against the IGDB, returning a ``$result`` array.
 ```php
 // This will return an array with the results
 $IGDB->game($options);
 ```
 - If you pass a boolean ``FALSE`` as a second parameter, then you will get the full constructed URL, but the query will not be ran against IGDB.
 ```php
 // This will return a string with the full URL.
 $IGDB->game($options, false);
 ```
 
 The result object's properties will vary depending on the provided field list in the [``options``](#options-parameters) array. Let's see what is the result of the above example query:
```php
array (size=5)
  0 => 
    object(stdClass)[2]
      public 'id' => int 22117
      public 'name' => string 'Oceanhorn' (length=9)
      public 'cover' => 
        object(stdClass)[3]
          public 'url' => string '//images.igdb.com/igdb/image/upload/t_thumb/dytkeomgzvjcech9q7ex.jpg' (length=68)
          public 'cloudinary_id' => string 'dytkeomgzvjcech9q7ex' (length=20)
          public 'width' => int 573
          public 'height' => int 606
  1 => 
    object(stdClass)[4]
      public 'id' => int 18975
      public 'name' => string 'Oceanhorn: Monster of Uncharted Seas' (length=36)
      public 'cover' => 
        object(stdClass)[5]
          public 'url' => string '//images.igdb.com/igdb/image/upload/t_thumb/tevieaod1lnuuhwinnnw.jpg' (length=68)
          public 'cloudinary_id' => string 'tevieaod1lnuuhwinnnw' (length=20)
          public 'width' => int 688
          public 'height' => int 789
  2 => 
    object(stdClass)[6]
      public 'id' => int 9637
      public 'name' => string 'Orcs Must Die! Unchained' (length=24)
      public 'cover' => 
        object(stdClass)[7]
          public 'url' => string '//images.igdb.com/igdb/image/upload/t_thumb/wsuw9rjpgbayrhl0ns59.jpg' (length=68)
          public 'cloudinary_id' => string 'wsuw9rjpgbayrhl0ns59' (length=20)
          public 'width' => int 1008
          public 'height' => int 1422
  3 => 
    object(stdClass)[8]
      public 'id' => int 52657
      public 'name' => string 'Pinball Heroes: Uncharted Drake's Fortune' (length=41)
  4 => 
    object(stdClass)[9]
      public 'id' => int 6906
      public 'name' => string 'Unchained Blades' (length=16)
      public 'cover' => 
        object(stdClass)[10]
          public 'url' => string '//images.igdb.com/igdb/image/upload/t_thumb/khlmibn0bievi4kfecgw.jpg' (length=68)
          public 'cloudinary_id' => string 'khlmibn0bievi4kfecgw' (length=20)
          public 'width' => int 250
          public 'height' => int 208
```
As you can see, the ``$result`` variable holds an array, containing 5 elements (the ``limit`` parameter is set to 5), and these elements are on the third page of the results. (``offset`` is set to 10) Every element of the ``$result`` array is an object, containing properties called like the fields from options ``fields`` parameter.

> Please note, that sometimes there are records which are missing one or more fields.<br/>
> Refer to the IGDB's endpoint documentation regarding the mandatory fields.<br/>
> Working with non-mandatory fileds requires you to check for availability before accessing them.

## Changes

### v1.0.5 - March 11, 2019
 - Fixed a bug at the request's error handling
 - [``public IGDB::get_request_info()``](#get-request-information) public method added

### v1.0.4 - March 25, 2018
 - Default properties has been removed.
 - set\_default public method has been removed.

### v1.0.3 - March 18, 2018
 - Providing either search or id parameter in the options array are not mandatory anymore.
 - Providing fields parameter when using expander is not mandatory anymore.
 - Ordering parameter 'order' in the options array has been renamed to 'direction'. Refer to the [order](#order) section of the [options parameters](#options-parameters).
 - Implemented count method. Refer to the [count](#count) section of the Readme.
 - Example _count.php_ has been added.
 - Updated Readme

### v1.0.2 - March 17, 2018
 - Modified the [constructor](#initializing-class) to ask only for the API Key. The API URL has been changed to be fix for every user (by IGDB).
 - The API URL and KEY setter and getter methods has been removed.
 - The API URL and KEY validator methods has been removed.
 - New method for order parameter constructing has been implemented. 
 - [Stringify Options](#stringify-options) method is private again. Use the updated endpoint methods instead.
 - Updated [Endpoint Methods](#endpoints) to accept a second optional parameter to return the constructed URL instead of executing the query.
 - _basic.php_ example file has been renamed to _basic.example.php_.
 - _order.php_ example has been added.
 - _order_subfilter.php_ example has been added.
 - All example files has been modified with the updated constructor.

### v1.0.1 - March 16, 2018
 - Added [Changes](#changes) section to the ReadMe.
 - Fixed [filter parameter](#filters) constructing; the parameter input has been changed.
 - Added example snippets to the [Options Parameters](#options-parameters) section.
 - Added example file _filter_multiple_criteria.php_
 - Added example file _filter_single_criteria.php_
