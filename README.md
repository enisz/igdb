# **Internet Game Database API Class Documentation**
  - [Introduction](#introduction)
  - [Initializing Class](#initializing-class)
  - [Class Properties](#class-properties)
    - [API URL](#api-url)
    - [API Key](#api-key)
    - [Default Limit](#default-limit)
    - [Default Offset](#default-offset)
    - [Default Fields](#default-fields)
    - [CURL Resource Handler](#curl-resource-handler)
  - [Options Parameters](#options-parameters)
    - [ID](#id)
    - [Search](#search)
    - [Fields](#fields)
    - [Limit](#limit)
    - [Offset](#offset)
    - [Expand](#expand)
    - [Filters](#filters)
    - [Order](#order)
  - [Static Methods](#static-methods)
    - [Validate API URL](#validate-api-url)
    - [Validate API Key](#validate-api-key)
  - [Public Methods](#public-methods)
    - [Set Default](#set-default)
    - [Set or Get API URL](#set-or-get-api-url)
    - [Set or Get API Key](#set-or-get-api-key)
    - [Close CURL Session](#close-curl-session)
    - [Reinitialize CURL session](#reinitialize-curl-session)
    - [Stringify Options](#stringify-options)
    - [Custom Query](#custom-query)
  - [Private Methods](#private-methods)
    - [Initialize CURL Session](#initialize-curl-session)
    - [Construct URL](#construct-url)
    - [Executing Query](#executing-query)
  - [Endpoints](#endpoints)
    - [Character](#character)
    - [Collection](#collection)
    - [Company](#company)
    - [Credit](#credit)
    - [Feed](#feed)
    - [Franchise](#franchise)
    - [Games](#games)
    - [Game Engine](#game-engine)
    - [Game Mode](#game-mode)
    - [Genre](#genre)
    - [Keyword](#keyword)
    - [Page](#page)
    - [Person](#person)
    - [Platform](#platform)
    - [Player Perspective](#player-perspective)
    - [Pulse](#pulse)
    - [Pulse Group](#pulse-group)
    - [Pulse Source](#pulse-source)
    - [Release Date](#release-date)
    - [Review](#review)
    - [Theme](#theme)
    - [Title](#title)
    - [Versions](#versions)
  - [Example Query](#example-query)
  - [Return Values](#return-values)

## Introduction
The class's main purpose is to provide a simple solution to fetch data from IGDB's database using PHP. Method names are matching the IGDB's endpoint names.

To use IGDB's database you have to register an account at [https://api.igdb.com](https://api.igdb.com).

## Initializing Class
``public IGDB::__construct ( string $url, string $key ) : void``<br/>
You can initialize the class by passing your IGDB URL and API Key to the constructor. The credentials will be verfied only by IGDB server when you send the query.

```
$IGDB = new IGDB('<YOUR API URL>', '<YOUR API KEY>');
```

## Class Properties

### API URL
``private IGDB::$API_URL ( string )``: your personal API URL provided by IGDB. It's value is set by [``IGDB::__construct()``](#initializing-class). You can set or get it's value by calling [``IGDB::api_url()``](#set-or-get-api-key).

### API Key
``private IGDB::$API_KEY ( string )``: your personal API Key provided by IGDB. It's value is set by [``IGDB::__construct()``](#initializing-class). You can set or get it's value by calling [``IGDB::api_key()``](#set-or-get-api-key).

### Default Limit
``private IGDB::$DEFAULT_LIMIT ( number )``: the default value of the limit in the queries. It's a predefined value, that you can update calling [``IGDB::set_default()``](#set-default) method. In case you set a limit parameter in the [``$options``](#options-parameters) array, this value will be ignored. By default this value is 10.

### Default Offset
``private IGDB::$DEFAULT_OFFSET ( number )``: the default value of the offset in the queries. It's a predefined value, that you can update calling [``IGDB::set_default()``](#set-default) method. In case you set an offset parameter in the [``$options``](#options-parameters) array, this value will be ignored. By default this value is 0.

### Default Fields
``private IGDB::$DEFAULT_FIELDS ( string )``: the default value of the fields in the queries. It's a predefined value, that you can update calling [``IGDB::set_default()``](#set-default) method. In case you set a fields parameter in the [``$options``](#options-parameters) array, this value will be ignored. By default this value is * (all fields).

### CURL Resource Handler
``private IGDB::$CH ( resource )``: CURL resource handler. Return value of [``curl_init()``](http://php.net/curl_init). You can close  [``IGDB::close_handler()``](##close-curl-session) and reinitialize [``IGDB::reinit_handler()``](#reinitialize-curl-session) the session.

## Options Parameters
For every [endpoint method](#endpoints) that fetching data from IGDB you will need to provide an ``$options`` array, that contains the parameters of the query.

Let's see an example options array:
```
$options = array(
	'search' => 'uncharted', // searching elements by the name UNCHARTED
	'fields' => array('id', 'name'), // the result object will contain only the ID and NAME fields
	'order' => 'name:asc', // ordering the results ASCENDING by NAME field
	'offset' => 15, // second page of a 15 element result
	'limit' => 15 // 15 elements per query
);
```
> Note: the order of the parameters in the ``$options`` array does not matter!

### ID
``id ( array | number )``: one ore more item ID's. When ID is provided, the ``search`` parameter will be ignored.

### Search
``search ( string )``: the query will search the name field looking for this value. If ``id`` is provided in the same options array, than this value will be ignored.

### Fields
``fields ( array | string ) [ optional ]``: the fields you want to see in the result array. If not provided all available fields will be returned (default = *). If the field list is provided as string, you have to separate the field names using comma (id,name).

> [IGDB Fields Documentation](https://igdb.github.io/api/references/fields/)

### Limit
``limit ( number ) [ optional ]``: the maximum number of results in a single query. (minimum = 0; default = 10; maximum = 50)

> [IGDB Pagination Documentation](https://igdb.github.io/api/references/pagination/)

### Offset
``offset ( number ) [ optional ]``: this will start the result list at the provided value and will give ``limit`` number of results. The lowest value it can get is 0 and the greatest is 50.

> [IGDB Pagination Documentation](https://igdb.github.io/api/references/pagination/)

### Expand
``expand ( array | string ) [ optional ]``: the expander feature is used to combine multiple requests. If this parameter is defined, than you have to provide the ``fields`` parameter as well.

> [IGDB Expander Documentation ](https://igdb.github.io/api/references/expander/)

### Filters
``filter ( string ) [ optional ]``: filters are used to swift through results to get what you want. You can exclude and include results based on their properties.

> [IGDB Filters Documentation](https://igdb.github.io/api/references/filters/)

### Order
``order ( string ) [ optional ]``: ordering (sorting) is used to order results by a specific field. When not provided, the results will be ordered ASCENDING by ID.

> [IGDB Ordering Documentation](https://igdb.github.io/api/references/ordering/)

## Static Methods
There are two methods you can call without instantiating the class.

### Validate API URL
``public static IGDB::validate_api_url ( string $url ) : boolean``<br/>
Validates whether the provided ``$url`` is valid or not. Returns a ( boolean ) TRUE if valid, ( boolean ) FALSE otherwise.

### Validate API Key
``public static IGDB::validate_api_key ( string $key ) : boolean``<br/>
Validates whether the provided ``$key`` is valid or not. Returns a ( boolean ) TRUE if valid, ( boolean ) FALSE otherwise.

## Public Methods

### Set Default
``public IGDB::set_default ( string $parameter, mixed $value ) : void``<br/>
Set the default values for the following parameters:
 - ``fields ( array | string )``: set the default fields of the queries. Default is all fields (*).
 - ``limit ( number )``: set the default limit of the queries. Value must be a number between 1 and 50. Default is 10.
 - ``offset ( number )``: set the default offset of the queries. Value must be a number greater or equal to 0. Default is 0.

 Example
```
$IGDB->set_default('fields', array('id', 'name'));
$IGDB->set_default('limit', 15);
$IGDB->set_default('offset', 5);
```

### Set or Get API URL
``IGDB::api_url ( [string $url] ) : string``<br/>
You can set or get the API URL with this method. In case you call it without parameters, than the return value will be current API URL.

If you provide a parameter, than it will be checked with the [``IGDB::validate_api_url()``](#validate-api-url) method. If setting the URL was successful, the method will return with ( boolean ) TRUE, ( boolean ) FALSE otherwise.

### Set or Get API Key
``IGDB::api_key ( [string $key] ) : string``<br/>
You can set or get the API Key with this method. In case you call it without parameters, than the return value will be current API URL.

If you provide a parameter, than it will be checked with the [``IGDB::validate_api_key()``](#validate-api-key) method. If setting the URL was successful, the method will return with ( boolean ) TRUE, ( boolean ) FALSE otherwise.

### Close CURL Session
``public IGDB::close_handler ( ) : void``<br/>
You can close the CURL session handler manually if you need to. The class will not do it by itself after any query in case you need to start several queries. After closing the session you will not be able to start new query with the actual instance of the class.

### Reinitialize CURL session
``public IGDB::reinit_handler ( ) : void``<br/>
After you closed the CURL session manually with [``IGDB::close_handler()``](#close-curl-session) than you will not be able to run any query against IGDB with the current instance of the class. However, if you need to run a query again, just call this method, and the CURL handler will be reinitialized.

### Stringify Options
``public IGDB::stringify_options ( array $options ) : string``<br/>
This method is checking every parameter passed to it. Throwing Exceptions in case of errors. If everything is fine, the complete options array is returned as a query string. You can check the request string with this method.

### Custom Query
``public IGDB::custom_query ( string $url ) : array``<br/>
You can launch manually assembled queries with this method. Great solution for testing purposes. This method automatically executes the query against IGDB and will return the result array.

Parameter:
 - ``$url ( string )``: this URL will be appended to the API URL.

Example
```
$result = $IGDB->custom_query('games/?search=uncharted&fields=id,name&order=name:asc');
```

## Private Methods
These methods cannot be accessed from outside of the class. These are responsible to check option parameters, constructing URL's and query strings.
### Initialize CURL Session
``private IGDB::_init_curl ( ) : void``<br/>
This method creates the CURL session and sets a few additional configuration to it.

### Construct URL
``private IGDB::_construct_url ( string $endpoint, array $options ) : string``<br/>
This method is responsible for constructing the complete request URL. It is done by calling the [``IGDB::_stringify_options()``](#stringify-options) method. Returns the complete constructed request URL.

### Executing Query
``private IGDB::_exec_query ( string $url ) : array`` - This method will start the query against IGDB. The ``$url`` parameter is constructed by the [``IGBB::_construct_url()``](#construct-url) method. Returns the JSON decoded response from IGDB as an array.

## Endpoints
Every endpoint method takes an ``$options`` array as a parameter to set up the query (check the [Options Parameters](#options-parameters) Section for more details about the available parameters and values).

Exceptions are thrown in case of any errors.

These methods returns an array with objects decoded from IGDB response JSON. Refer to the [Return Values](#return-values) Section for more details
### Character
``public IGDB::character ( array $options ) : array`` - Fetch data using CHARACTER endpoint.
> [IGDB CHARACTER Endpoint Documentation](https://igdb.github.io/api/endpoints/character/)

### Collection
``public IGDB::collection ( array $options ) : array`` - Fetch data using COLLECTION endpoint.
> [IGDB COLLECTION Endpoint Documentation](https://igdb.github.io/api/endpoints/collection/)

### Company
``public IGDB::company ( array $options ) : array`` - Fetch data using COMPANY endpoint.
> [IGDB COMPANY Endpoint Documentation](https://igdb.github.io/api/endpoints/company/)

### Credit
``public IGDB::credit ( array $options ) : array`` - Fetch data using CREDIT endpoint.
> [IGDB CREDIT Endpoint Documentation](https://igdb.github.io/api/endpoints/credit/)

### Feed
``public IGDB::feed ( array $options ) : array`` - Fetch data using FEED endpoint.
> [IGDB FEED Endpoint Documentation](https://igdb.github.io/api/endpoints/feed/)

### Franchise
``public IGDB::franchise ( array $options ) : array`` - Fetch data using FRANCHISE endpoint.
> [IGDB FRANCHISE Endpoint Documentation](https://igdb.github.io/api/endpoints/franchise/)

### Games
``public IGDB::game ( array $options ) : array`` - Fetch data using GAME endpoint.
> [IGDB GAME Endpoint Documentation](https://igdb.github.io/api/endpoints/game/)

### Game Engine
``public IGDB::game_engine ( array $options ) : array`` - Fetch data using GAME ENGINE endpoint.
> [IGDB GAME ENGINE Endpoint Documentation](https://igdb.github.io/api/endpoints/game-engine/)

### Game Mode
``public IGDB::game_mode ( array $options ) : array`` - Fetch data using GAME MODE endpoint.
> [IGDB GAME MODE Endpoint Documentation](https://igdb.github.io/api/endpoints/game-mode/)

### Genre
``public IGDB::genre ( array $options ) : array`` - Fetch data using GENRE endpoint.
> [IGDB GENRE Endpoint Documentation](https://igdb.github.io/api/endpoints/genre/)

### Keyword
``public IGDB::keyword ( array $options ) : array`` - Fetch data using KEYWORD endpoint.
> [IGDB KEYWORD Endpoint Documentation](https://igdb.github.io/api/endpoints/keyword/)

### Page
``public IGDB::page ( array $options ) : array`` - Fetch data using PAGE endpoint.
> [IGDB PAGE Endpoint Documentation](https://igdb.github.io/api/endpoints/page/)

### Person
``public IGDB::person ( array $options ) : array`` - Fetch data using PERSON endpoint.
> [IGDB PERSON Endpoint Documentation](https://igdb.github.io/api/endpoints/person/)

### Platform
``public IGDB::platform ( array $options ) : array`` - Fetch data using PLATFORM endpoint.
> [IGDB PLATFORM Endpoint Documentation](https://igdb.github.io/api/endpoints/platform/)

### Player Perspective
``public IGDB::player_perspective ( array $options ) : array`` - Fetch data using PLAYER PERSPECTIVE endpoint.
> [IGDB PLAYER PERSPECTIVE Endpoint Documentation](https://igdb.github.io/api/endpoints/player-perspective/)

### Pulse
``public IGDB::pulse ( array $options ) : array`` - Fetch data using PULSE endpoint.
> [IGDB PULSE Endpoint Documentation](https://igdb.github.io/api/endpoints/pulse/)

### Pulse Group
``public IGDB::pulse_group ( array $options ) : array`` - Fetch data using PULSE GROUP endpoint.
> [IGDB PULSE GROUP Endpoint Documentation](https://igdb.github.io/api/endpoints/pulse-group/)

### Pulse Source
``public IGDB::pulse_source ( array $options ) : array`` - Fetch data using PULSE SOURCE endpoint.
> [IGDB PULSE SOURCE Endpoint Documentation](https://igdb.github.io/api/endpoints/pulse-source/)

### Release Date
``public IGDB::release_date ( array $options ) : array`` - Fetch data using RELEASE DATE endpoint.
> [IGDB RELEASE DATE Endpoint Documentation](https://igdb.github.io/api/endpoints/release-date/)

### Review
``public IGDB::review ( array $options ) : array`` - Fetch data using REVIEW endpoint.
> [IGDB REVIEW Endpoint Documentation](https://igdb.github.io/api/endpoints/review/)

### Theme
``public IGDB::theme ( array $options ) : array`` - Fetch data using THEME endpoint.
> [IGDB THEME Endpoint Documentation](https://igdb.github.io/api/endpoints/theme/)

### Title
``public IGDB::title ( array $options ) : array`` - Fetch data using TITLE endpoint.
> [IGDB TITLE Endpoint Documentation](https://igdb.github.io/api/endpoints/title/)

### Versions
``public IGDB::versions ( array $options ) : array`` - Fetch data using VERSIONS endpoint.
> [IGDB VERSIONS Endpoint Documentation](https://igdb.github.io/api/endpoints/versions/)

## Example Query
Let's do a simple example. Get the second page of a game list, where the game we are looking for is LIKE "uncharted" (this example is available in _examples/\_basic.php_)
```
<?php

    require 'class.igdb.php';

    // Instantiate the class
    $IGDB = new IGDB('<YOUR API URL>', '<YOUR API KEY>');

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
Every [endpoint method](#endpoints) will return a JSON decoded array from IGDB response. The result object's properties will vary depending on the provided field list in the [``options``](#options-parameters) array. Let's see what is the result of the above example query:
```
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

> Please note, that sometimes there are records which missing one or more fields.<br/>
> Refer to the IGDB's endpoint documentation regarding the mandatory fields.<br/>
> Working with non-mandatory fileds requires you to check for availability before accessing them.