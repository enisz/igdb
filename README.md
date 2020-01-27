# **IGDB API v3 Class Documentation**

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
  * [Where](#where)
  * [Sorting](#sorting)
- [Public Methods](#public-methods)
  * [Close CURL Session](#close-curl-session)
  * [Reinitialize CURL session](#reinitialize-curl-session)
  * [Convert Options to Apicalypse query string](#convert-options-to-apicalypse-query-string)
  * [Get Request Information](#get-request-information)
  * [Get the status of your API Key](#get-the-status-of-your-api-key)
- [Private Methods](#private-methods)
  * [Initialize CURL Session](#initialize-curl-session)
  * [Executing Query](#executing-query)
  * [Constructing URL's](#constructing-urls)
- [Endpoints](#endpoints)
  * [Achievement](#achievement)
  * [Achievement Icon](#achievement-icon)
  * [Age Rating](#age-rating)
  * [Age Rating Content Description](#age-rating-content-description)
  * [Alternative Name](#alternative-name)
  * [Artwork](#artwork)
  * [Character](#character)
  * [Character Mug Shot](#character-mug-shot)
  * [Collection](#collection)
  * [Company](#company)
  * [Company Logo](#company-logo)
  * [Company Website](#company-website)
  * [Cover](#cover)
  * [External Game](#external-game)
  * [Feed](#feed)
  * [Feed Follow](#feed-follow)
  * [Follow](#follow)
  * [Franchise](#franchise)
  * [Game](#game)
  * [Game Engine](#game-engine)
  * [Game Engine Logo](#game-engine-logo)
  * [Game Mode](#game-mode)
  * [Game Version](#game-version)
  * [Game Version Feature](#game-version-feature)
  * [Game Version Feature Value](#game-version-feature-value)
  * [Game Video](#game-video)
  * [Genre](#genre)
  * [Involved Company](#involved-company)
  * [Keyword](#keyword)
  * [List](#list)
  * [List Entry](#list-entry)
  * [Multiplayer Mode](#multiplayer-mode)
  * [Page](#page)
  * [Page Background](#page-background)
  * [Page Logo](#page-logo)
  * [Page Website](#page-website)
  * [Platform](#platform)
  * [Platform Logo](#platform-logo)
  * [Platform Version](#platform-version)
  * [Platform Version Company](#platform-version-company)
  * [Platform Version Release Date](#platform-version-release-date)
  * [Platform Website](#platform-website)
  * [Player Perspective](#player-perspective)
  * [Product Family](#product-family)
  * [Pulse](#pulse)
  * [Pulse Group](#pulse-group)
  * [Pulse Source](#pulse-source)
  * [Pulse Url](#pulse-url)
  * [Rate](#rate)
  * [Release Date](#release-date)
  * [Review](#review)
  * [Review Video](#review-video)
  * [Screenshot](#screenshot)
  * [Search](#search-1)
  * [Theme](#theme)
  * [Time To Beat](#time-to-beat)
  * [Title](#title)
  * [Website](#website)
- [Example Query](#example-query)
- [Return Values](#return-values)
- [Change Log](#change-log)
  * [v2.0.1 - January 27, 2020](#v201---january-27-2020)
  * [v2.0.0 - December 04, 2019](#v200---december-04-2019)
  * [v1.0.5 - March 11, 2019](#v105---march-11-2019)
  * [v1.0.4 - March 25, 2018](#v104---march-25-2018)
  * [v1.0.3 - March 18, 2018](#v103---march-18-2018)
  * [v1.0.2 - March 17, 2018](#v102---march-17-2018)
  * [v1.0.1 - March 16, 2018](#v101---march-16-2018)

<!-- tocstop -->

## Introduction
The class's main purpose is to provide a simple solution to fetch data from IGDB's database using PHP.

To use IGDB's database you have to register an account at [https://api.igdb.com](https://api.igdb.com). For details on how to use the IGDB API, what endpoints can be used or for informations in general check out the [Official Documentation of the IGDB API](https://api-docs.igdb.com).

## Initializing Class
``public IGDB::__construct ( string $key ) : void``<br/>
After the class is imported in your project you can instantiate the class by passing your IGDB API Key to the constructor. The credentials will be verfied only by IGDB server when you send the query.

```php
require 'class.igdb.php';

$IGDB = new IGDB('<YOUR API KEY>');
```

From now on multiple request can be sent to the IGDB API with the same instance.

## Class Properties

### API URL
``private IGDB::$API_URL ( string )``: IGDB API URL. This is the address you have to send your query to.

### API Key
``private IGDB::$API_KEY ( string )``: your personal API Key provided by IGDB. It's value is set by [``IGDB::__construct()``](#initializing-class).

### CURL Resource Handler
``private IGDB::$CH ( resource )``: CURL resource handler. Return value of [``curl_init()``](http://php.net/curl_init). You can close  [``IGDB::close_handler()``](##close-curl-session) and reinitialize [``IGDB::reinit_handler()``](#reinitialize-curl-session) the session anytime.

## Options Parameters
For every [endpoint method](#endpoints) that fetching data from IGDB you will need to provide an ``$options`` array, that contains the parameters of the query.

> Note: the order of the parameters in the ``$options`` array does not matter!

### ID
``id ( array | number ) [ optional ]``: one ore more item ID's.

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

> The new version of the IGDB API (v3) is handling item id's differently (from query perspective). Filtering the results by item id is now done by providing it in the `where` statement of the query. During runtime the class will add this value there. You can provide the id's in the `where` statement as well.

### Search
``search ( string ) [ optional ]``: search based on name, results are sorted by similarity to the given search string.

```php
// Provide search string
$options = array(
  'search' => 'star wars'
);
```

> [IGDB Search Documentation](https://api-docs.igdb.com/#search)

### Fields
``fields ( array | string ) [ optional ]``: Fields are properties of an entity. For example, a Game field would be `genres` or `release_dates`. Some fields have properties of their own, for example, the `genres` field has the property `name`.

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

> [IGDB Fields Documentation](https://api-docs.igdb.com/#fields)

### Limit
``limit ( number ) [ optional ]``: the maximum number of results in a single query. This value must be a number between 1 and 50.

```php
// Provide a limit parameter
$options = array(
  'limit' => 20
);
```

> [IGDB Pagination Documentation](https://api-docs.igdb.com/#pagination)

### Offset
``offset ( number ) [ optional ]``: this will start the result list at the provided value and will give ``limit`` number of results. This value must be 0 or greater.

```php
// Provide an offset parameter
$options = array(
  'offset' => 5
);
```

> [IGDB Pagination Documentation](https://api-docs.igdb.com/#pagination)

### Where
``where ( string | array ) [ optional ]``: Filters are used to swift through results to get what you want. You can exclude and include results based on their properties. For example you could remove all Games where the rating was below 80 `(where rating >= 80)`.

> Note: in the old (v2) IGDB API this field was called `filter`.

If you provide the filter parameters as an array, you must have three values in it with the following indexes:
 - ``field``: The name of the field you want to apply the filter to
 - ``postfix``: The postfix you want to use with the filter. Refer to the IGDB Filters Documentation for available postfixes.
 - ``value``: The value of the filter.

```php
// Provide a single filter rule as an array
// In this case you must have field, postfix and value elements in the array
$options = array(
  'field' => 'release_dates.platform',
  'postfix' => '=',
  'value' => 8
);

// Provide multiple filter rules as an array
// In this case you must have field, postfix and value elements in the arrays
$options = array(
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
```

You can provide the filter parameter as string. In this case you can pass the string with apicalypse string:

```php
// Provide a single filter rule as a string
$options = array(
  'where' => 'release_dates.platform = 8'
);
```

Or you can provide multiple criteria as an array with apicalypse syntax:

```php
$options = array(
    'fields' => 'id, name, platforms, genres',  // we want to see these fields in the result
    'where' => array(                           // make sure to have each criteria as a separate element in the array
        'release_dates.platform = 8',           // and separate field names, postfixes and values with space
        'total_rating >= 70',
        'genres = 4'
    )
);
```

In this case make sure to separate the field name, the postfix and the value with spaces!

> [IGDB Filters Documentation](https://api-docs.igdb.com/#filters)

### Sorting
``sort ( string | array ) [ optional ]``: sorting (ordering) is used to order results by a specific field.

> Note: in the old (v2) IGDB API this field was called `order`.

IF you provide the Order parameter as an array, you must have two values in it with the following indexes:
 - ``field``: The field you want to do the ordering by
 - ``direction``: The direction of the ordering. It must be either ``asc`` for ascending or ``desc`` for descending ordering.

```php
// Provide an sort parameter as an array
$options = array(
    'sort' => array(
        'field' => 'release_dates.date',
        'direction' => 'desc',
    )
);
```

You can also provide the sort parameter as string. In this case you can pass the string with apycalipse syntax:

```php
// Provide an order parameter as a string
$options = array(
  'sort' => 'release_dates.date desc'
);
```

> [IGDB Sorting Documentation](https://api-docs.igdb.com/#sorting)

## Public Methods

### Close CURL Session
``public IGDB::close_handler ( ) : void``<br/>
You can close the CURL session handler manually if you need to. The class will not do it by itself after any query in case you need to start several queries. After closing the session you will not be able to start new query with the actual instance of the class.

### Reinitialize CURL session
``public IGDB::reinit_handler ( ) : void``<br/>
After you closed the CURL session manually with [``IGDB::close_handler()``](#close-curl-session) than you will not be able to run any query against IGDB with the current instance of the class. However, if you need to run a query again, just call this method, and the CURL handler will be reinitialized.

### Convert Options to Apicalypse query string
``public IGDB::apicalypse( $options ) : string``<br/>
You can convert the options array to IGDB's query language called Apicalypse. This method will return a string with the parsed parameters. You can read additional informations in the [IGDB Apicalypse Documentation](https://api-docs.igdb.com/#apicalypse)

<p>Parameters</p>

 - ``$options``: the options array to convert

<p>Return</p>
The method returns the query string with apicalypse syntax.

### Get Request Information
``public IGDB::get_request_info ( ) : array``<br/>
If you need detailed information regarding the latest query, you can get it by this method. It returns the return value of [``curl_getinfo()``](http://php.net/curl_getinfo) php function.

### Get the status of your API Key
``public IGDB::api_status ( ) : array``<br/>
The API Status endpoint is a way to see a usage report for an API key. It shows stats such as requests made in the current period and when that period ends. Requests to this endpoint does not count towards you monthly request limit, however this endpoint is not intended to be requested repeatedly before every request to other endpoints, but rather be used more sparingly. Therefore this endpoint is rate limited to 5 requests per minute. Exceeding this limit will suspend your access to THIS endpoint for 1 hour. If you have exceeded the limit and make another request you will receive a response with status code 429 ‘Too many requests’.

## Private Methods

### Initialize CURL Session
``private IGDB::_init_curl ( ) : void``<br/>
This method creates the CURL session and sets a few additional configuration to it.

### Executing Query
``private IGDB::_exec_query ( string $endpoint, array $options ) : array`` - This method will start the query against IGDB. Returns the JSON decoded response from IGDB as an array.

<p>Parameters</p>

 - ``$endpoint ( string )`` : url of the endpoint to execute the query against
 - ``$options ( array )`` : the options array

<p>Return</p>

The method returns the IGDB response as an array.

### Constructing URL's
``private IGDB::_construct_url( string $endpoint, boolean $count = false) : string`` - The method will construct the full URL for the request and will return the constructed URL as a string.

<p>Parameters</p>

- ``$endpoint ( string )`` : the endpoint to use
- ``$count ( boolean )`` : whether the request should return the number of matches instead of the actual resultset

<p>Return</p>

This method will return the full constructed URL to the IGDB Endpoint as a string.

## Endpoints
Every endpoint method takes an ``$options`` array as a parameter to set up the query (check the [Options Parameters](#options-parameters) Section for more details about the available parameters and values). As a second optional parameter you can pass a boolean ``$count``.

These methods are returning an array with objects decoded from IGDB response JSON by default. If you provide boolean ``true`` as a second parameter, it will execute a count query against the selected endpoint which will return an object with a `count` property holding the sum of the found items. You can filter the results with the `$options` array.

Exceptions are thrown in any case of error.

Refer to the [Return Values](#return-values) Section for more details about the return values of these methods.

<p>Parameters</p>

 - ``$options ( array )`` : The options array
 - ``$count (boolean) [optional]`` : Whether you want to get the found items or the sum of them. If this value is ``true`` then the result count will be returned. By default this is `false`.

 <p>Return</p>

 If ``$count`` parameter is set to ``true`` the number of items will be returned. Otherwise the IGDB response as an array.

### Achievement
``public IGDB::achievement ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Achievement endpoint.
> [IGDB Achievement Documentation](https://api-docs.igdb.com/#achievement)

### Achievement Icon
``public IGDB::achievement_icon ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Achievement Icon endpoint.
> [IGDB Achievement Icon Documentation](https://api-docs.igdb.com/#achievement-icon)

### Age Rating
``public IGDB::age_rating ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Age Rating endpoint.
> [IGDB Age Rating Documentation](https://api-docs.igdb.com/#age-rating)

### Age Rating Content Description
``public IGDB::age_rating_content_description ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Age Rating Content Description endpoint.
> [IGDB Age Rating Content Description Documentation](https://api-docs.igdb.com/#age-rating-content-description)

### Alternative Name
``public IGDB::alternative_name ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Alternative Name endpoint.
> [IGDB Alternative Name Documentation](https://api-docs.igdb.com/#alternative-name)

### Artwork
``public IGDB::artwork ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Artwork endpoint.
> [IGDB Artwork Documentation](https://api-docs.igdb.com/#artwork)

### Character
``public IGDB::character ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Character endpoint.
> [IGDB Character Documentation](https://api-docs.igdb.com/#character)

### Character Mug Shot
``public IGDB::character_mug_shot ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Character Mug Shot endpoint.
> [IGDB Character Mug Shot Documentation](https://api-docs.igdb.com/#character-mug-shot)

### Collection
``public IGDB::collection ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Collection endpoint.
> [IGDB Collection Documentation](https://api-docs.igdb.com/#collection)

### Company
``public IGDB::company ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Company endpoint.
> [IGDB Company Documentation](https://api-docs.igdb.com/#company)

### Company Logo
``public IGDB::company_logo ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Company Logo endpoint.
> [IGDB Company Logo Documentation](https://api-docs.igdb.com/#company-logo)

### Company Website
``public IGDB::company_website ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Company Website endpoint.
> [IGDB Company Website Documentation](https://api-docs.igdb.com/#company-website)

### Cover
``public IGDB::cover ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Cover endpoint.
> [IGDB Cover Documentation](https://api-docs.igdb.com/#cover)

### External Game
``public IGDB::external_game ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using External Game endpoint.
> [IGDB External Game Documentation](https://api-docs.igdb.com/#external-game)

### Feed
``public IGDB::feed ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Feed endpoint.
> [IGDB Feed Documentation](https://api-docs.igdb.com/#feed)

### Feed Follow
``public IGDB::feed_follow ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Feed Follow endpoint.
> [IGDB Feed Follow Documentation](https://api-docs.igdb.com/#feed-follow)

### Follow
``public IGDB::follow ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Follow endpoint.
> [IGDB Follow Documentation](https://api-docs.igdb.com/#follow)

### Franchise
``public IGDB::franchise ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Franchise endpoint.
> [IGDB Franchise Documentation](https://api-docs.igdb.com/#franchise)

### Game
``public IGDB::game ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Game endpoint.
> [IGDB Game Documentation](https://api-docs.igdb.com/#game)

### Game Engine
``public IGDB::game_engine ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Game Engine endpoint.
> [IGDB Game Engine Documentation](https://api-docs.igdb.com/#game-engine)

### Game Engine Logo
``public IGDB::game_engine_logo ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Game Engine Logo endpoint.
> [IGDB Game Engine Logo Documentation](https://api-docs.igdb.com/#game-engine-logo)

### Game Mode
``public IGDB::game_mode ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Game Mode endpoint.
> [IGDB Game Mode Documentation](https://api-docs.igdb.com/#game-mode)

### Game Version
``public IGDB::game_version ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Game Version endpoint.
> [IGDB Game Version Documentation](https://api-docs.igdb.com/#game-version)

### Game Version Feature
``public IGDB::game_version_feature ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Game Version Feature endpoint.
> [IGDB Game Version Feature Documentation](https://api-docs.igdb.com/#game-version-feature)

### Game Version Feature Value
``public IGDB::game_version_feature_value ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Game Version Feature Value endpoint.
> [IGDB Game Version Feature Value Documentation](https://api-docs.igdb.com/#game-version-feature-value)

### Game Video
``public IGDB::game_video ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Game Video endpoint.
> [IGDB Game Video Documentation](https://api-docs.igdb.com/#game-video)

### Genre
``public IGDB::genre ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Genre endpoint.
> [IGDB Genre Documentation](https://api-docs.igdb.com/#genre)

### Involved Company
``public IGDB::involved_company ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Involved Company endpoint.
> [IGDB Involved Company Documentation](https://api-docs.igdb.com/#involved-company)

### Keyword
``public IGDB::keyword ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Keyword endpoint.
> [IGDB Keyword Documentation](https://api-docs.igdb.com/#keyword)

### List
``public IGDB::list ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using List endpoint.
> [IGDB List Documentation](https://api-docs.igdb.com/#list)

### List Entry
``public IGDB::list_entry ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using List Entry endpoint.
> [IGDB List Entry Documentation](https://api-docs.igdb.com/#list-entry)

### Multiplayer Mode
``public IGDB::multiplayer_mode ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Multiplayer Mode endpoint.
> [IGDB Multiplayer Mode Documentation](https://api-docs.igdb.com/#multiplayer-mode)

### Page
``public IGDB::page ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Page endpoint.
> [IGDB Page Documentation](https://api-docs.igdb.com/#page)

### Page Background
``public IGDB::page_background ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Page Background endpoint.
> [IGDB Page Background Documentation](https://api-docs.igdb.com/#page-background)

### Page Logo
``public IGDB::page_logo ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Page Logo endpoint.
> [IGDB Page Logo Documentation](https://api-docs.igdb.com/#page-logo)

### Page Website
``public IGDB::page_website ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Page Website endpoint.
> [IGDB Page Website Documentation](https://api-docs.igdb.com/#page-website)

### Platform
``public IGDB::platform ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Platform endpoint.
> [IGDB Platform Documentation](https://api-docs.igdb.com/#platform)

### Platform Logo
``public IGDB::platform_logo ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Platform Logo endpoint.
> [IGDB Platform Logo Documentation](https://api-docs.igdb.com/#platform-logo)

### Platform Version
``public IGDB::platform_version ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Platform Version endpoint.
> [IGDB Platform Version Documentation](https://api-docs.igdb.com/#platform-version)

### Platform Version Company
``public IGDB::platform_version_company ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Platform Version Company endpoint.
> [IGDB Platform Version Company Documentation](https://api-docs.igdb.com/#platform-version-company)

### Platform Version Release Date
``public IGDB::platform_version_release_date ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Platform Version Release Date endpoint.
> [IGDB Platform Version Release Date Documentation](https://api-docs.igdb.com/#platform-version-release-date)

### Platform Website
``public IGDB::platform_website ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Platform Website endpoint.
> [IGDB Platform Website Documentation](https://api-docs.igdb.com/#platform-website)

### Player Perspective
``public IGDB::player_perspective ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Player Perspective endpoint.
> [IGDB Player Perspective Documentation](https://api-docs.igdb.com/#player-perspective)

### Product Family
``public IGDB::product_family ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Product Family endpoint.
> [IGDB Product Family Documentation](https://api-docs.igdb.com/#product-family)

### Pulse
``public IGDB::pulse ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Pulse endpoint.
> [IGDB Pulse Documentation](https://api-docs.igdb.com/#pulse)

### Pulse Group
``public IGDB::pulse_group ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Pulse Group endpoint.
> [IGDB Pulse Group Documentation](https://api-docs.igdb.com/#pulse-group)

### Pulse Source
``public IGDB::pulse_source ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Pulse Source endpoint.
> [IGDB Pulse Source Documentation](https://api-docs.igdb.com/#pulse-source)

### Pulse Url
``public IGDB::pulse_url ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Pulse Url endpoint.
> [IGDB Pulse Url Documentation](https://api-docs.igdb.com/#pulse-url)

### Rate
``public IGDB::rate ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Rate endpoint.
> [IGDB Rate Documentation](https://api-docs.igdb.com/#rate)

### Release Date
``public IGDB::release_date ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Release Date endpoint.
> [IGDB Release Date Documentation](https://api-docs.igdb.com/#release-date)

### Review
``public IGDB::review ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Review endpoint.
> [IGDB Review Documentation](https://api-docs.igdb.com/#review)

### Review Video
``public IGDB::review_video ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Review Video endpoint.
> [IGDB Review Video Documentation](https://api-docs.igdb.com/#review-video)

### Screenshot
``public IGDB::screenshot ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Screenshot endpoint.
> [IGDB Screenshot Documentation](https://api-docs.igdb.com/#screenshot)

### Search
``public IGDB::search ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Search endpoint.
> [IGDB Search Documentation](https://api-docs.igdb.com/#search)

### Theme
``public IGDB::theme ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Theme endpoint.
> [IGDB Theme Documentation](https://api-docs.igdb.com/#theme)

### Time To Beat
``public IGDB::time_to_beat ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Time To Beat endpoint.
> [IGDB Time To Beat Documentation](https://api-docs.igdb.com/#time-to-beat)

### Title
``public IGDB::title ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Title endpoint.
> [IGDB Title Documentation](https://api-docs.igdb.com/#title)

### Website
``public IGDB::website ( array $options, boolean $count = false ) : array | object `` <br/>
Fetch data using Website endpoint.
> [IGDB Website Documentation](https://api-docs.igdb.com/#website)

## Example Query
Let's do a simple example. Get the third page of a game list, where the game we are looking for is LIKE "uncharted" (this example is available in _examples/\_basic_example.php_)
``` php
<?php

    require '../src/class.igdb.php';

    // Instantiate the class
    $IGDB = new IGDB('<YOUR API KEY>');

    // Setting up the query parameters
    $options = array(
        'search' => 'uncharted', // searching for games LIKE uncharted
        'fields' => array(       // we want to see these values in the results
            'id',
            'name',
            'cover'
        ),
        'limit' => 5,            // we only need maximum 5 results per query (pagination)
        'offset' => 10           // we would like to show the third page; fetch the results from the tenth element (pagination)
    );

    try {
        // Running the query against IGDB; passing the options parameter
        $result = $IGDB->game($options);

        // Showing the result
        var_dump($result);
    } catch (Exception $e) {
        // Catching Exceptions, if there is any
        echo $e->getMessage();
    }

?>
```

## Return Values
Every [Endpoint Method](#endpoints) can return two different type of results, depending on the second parameter provided for them:
 - By default the second ``$count`` parameter is boolean ``false``. this means, that the query will be ran against the IGDB, returning a ``$result`` array.
    ```php
    // This will return an array with the results
    $IGDB->game($options);
    ```
 - If you pass a boolean ``true`` as a second parameter, then you will get an object with a `count` property containing the item count from the selected endpoint.
    ```php
    // This will return a string with the full URL.
    $IGDB->game($options, false);
    ```

 - The result object's properties will vary depending on the provided field list in the [``options``](#options-parameters) array. Let's see what is the result of the above example query:
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

    As you can see, the ``$result`` variable holds an array, containing 5 elements (the ``limit`` parameter is set to 5), and these elements are on the third page of the results (``offset`` is set to 10). Every element of the ``$result`` array is an object, containing properties called like the fields from options ``fields`` parameter.

> Please note, that sometimes there are records which are missing one or more fields.<br/>
> Refer to the IGDB's respective endpoint documentation regarding the mandatory fields.<br/>
> Working with non-mandatory fileds requires you to check for availability before accessing them.

## Change Log

### v2.0.1 - January 27, 2020
 - Minor changes / fixes in the Readme
 - Added method [`_construct_url`](#constructing-urls)
 - Updated every endpoint method to construct the endpoint url's different

### v2.0.0 - December 04, 2019
 - **IGDB Api v3 compatibility update**
 - Removed `expander` parameter
 - Renamed parameter `filter` to `where`
 - Renamed parameter `order` to `sort`
 - Removed multiple methods:
    - `_stringify_options`
    - `_construct_url`
    - `count`
    - `custom_query`
 - Added method [`apicalypse`](#convert-options-to-apicalypse-query-string)
 - Added method [`api_status`](#get-the-status-of-your-api-key)
 - Updated every [endpoint method](#endpoints) (removed `$execute`, added `$count`)

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