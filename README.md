# Internet Game Database API Wrapper (v4)

<!-- toc -->

- [Introduction](#introduction)
- [Initializing Class](#initializing-class)
- [Class Properties](#class-properties)
  * [Client ID](#client-id)
  * [Access Token](#access-token)
  * [Api Url](#api-url)
  * [CURL Handler](#curl-handler)
  * [Request Info](#request-info)
- [Query Parameters](#query-parameters)
  * [ID](#id)
  * [Search](#search)
  * [Fields](#fields)
  * [Limit](#limit)
  * [Offset](#offset)
  * [Where](#where)
  * [Sort](#sort)
- [Public Methods](#public-methods)
  * [Apicalypse](#apicalypse)
  * [Get Request Info](#get-request-info)
  * [Close CURL Session](#close-curl-session)
  * [Reinitialize CURL session](#reinitialize-curl-session)
- [Private Methods](#private-methods)
  * [Initialize CURL Session](#initialize-curl-session)
  * [Executing Query](#executing-query)
  * [Constructing URL's](#constructing-urls)
- [Endpoints](#endpoints)
  * [Age Rating Content Description](#age-rating-content-description)
  * [Age Rating](#age-rating)
  * [Age Rating Content Description](#age-rating-content-description-1)
  * [Age Rating](#age-rating-1)
  * [Alternative Name](#alternative-name)
  * [Artwork](#artwork)
  * [Character Mug Shot](#character-mug-shot)
  * [Character](#character)
  * [Collection](#collection)
  * [Company Logo](#company-logo)
  * [Company Website](#company-website)
  * [Company](#company)
  * [Cover](#cover)
  * [External Game](#external-game)
  * [Franchise](#franchise)
  * [Game Engine Logo](#game-engine-logo)
  * [Game Engine](#game-engine)
  * [Game Mode](#game-mode)
  * [Game Version Feature Value](#game-version-feature-value)
  * [Game Version Feature](#game-version-feature)
  * [Game Version](#game-version)
  * [Game Video](#game-video)
  * [Game](#game)
  * [Genre](#genre)
  * [Involved Company](#involved-company)
  * [Keyword](#keyword)
  * [Multiplayer Mode](#multiplayer-mode)
  * [Platform Family](#platform-family)
  * [Platform Logo](#platform-logo)
  * [Platform Version Company](#platform-version-company)
  * [Platform Version Release Date](#platform-version-release-date)
  * [Platform Version](#platform-version)
  * [Platform Website](#platform-website)
  * [Platform](#platform)
  * [Player Perspective](#player-perspective)
  * [Release Date](#release-date)
  * [Screenshot](#screenshot)
  * [Search](#search-1)
  * [Theme](#theme)
  * [Website](#website)
- [MultiQuery](#multiquery)
- [Return Values](#return-values)
- [Change Log](#change-log)
  * [v4.0.0 - October 20, 2020](#v400---october-20-2020)
  * [v2.0.3 - September 17, 2020](#v203---september-17-2020)
  * [v2.0.2 - February 03, 2020](#v202---february-03-2020)
  * [v2.0.1 - January 27, 2020](#v201---january-27-2020)
  * [v2.0.0 - December 04, 2019](#v200---december-04-2019)
  * [v1.0.5 - March 11, 2019](#v105---march-11-2019)
  * [v1.0.4 - March 25, 2018](#v104---march-25-2018)
  * [v1.0.3 - March 18, 2018](#v103---march-18-2018)
  * [v1.0.2 - March 17, 2018](#v102---march-17-2018)
  * [v1.0.1 - March 16, 2018](#v101---march-16-2018)

<!-- tocstop -->

## Introduction
The wrapper's main purpose is to provide a simple solution to fetch data from IGDB's database using PHP.

To have access to IGDB's database you have to register a Twitch Account and have your own `client_id` and `access_token`. Refer to the [Account Creation](https://api-docs.igdb.com/#account-creation) and [Authentication](https://api-docs.igdb.com/#authentication) sections of the [IGDB API Documentation](https://api-docs.igdb.com/) for details.

The wrapper itself is located in the `src/class.igdb.php` file and after importing it into your project, you are ready to go.

For a better understanding there are several example scripts in the `examples` folder.

## Initializing Class
``public IGDB::__construct ( string $client_id, string $access_token ) : void``<br/>
After the class is imported in your project you can instantiate the class by passing your `client_id` and `access_token` to the constructor. The credentials will be verfied only by IGDB server when you send your query.

```php
require 'class.igdb.php';

$IGDB = new IGDB("client_id", "access_token");
```

Using this instance, you can execute multiple requests against the database.

## Class Properties

### Client ID
`IGDB::$client_id ( string )`

The Client ID you can get from your Twitch Account.

### Access Token
`IGDB::$access_token ( string )`

The Access Token you can get from your Twitch Account.

### Api Url
`IGDB::$api_url ( string )`

The URL of the IGDB API. Your queries will be sent to this URL.

### CURL Handler
`IGDB::$curl_handler ( resource )`

The resource handler of the curl session. This property will hold the return value of [curl_init](http://php.net/curl_init) php function.

### Request Info
`IGDB::$request_info ( mixed )`

This object will hold the most recent query's request information. This property will hold the return value of the [curl_getinfo](https://www.php.net/manual/en/function.curl-getinfo.php) php function.

## Query Parameters

For the new version of this wrapper class the `options` got renamed to `query` because from now on you will be able to use your own apicalypse query strings.

> For the apicalypse syntax refer to the [IGDB Apicalypse section](https://api-docs.igdb.com/#apicalypse-1)

For every [endpoint method](#endpoints) that fetching data from IGDB you will need to provide a `query`. This can be either an `apicalypse` string, or an array containing specific keys and values (like the options array in previous versions of the wrapper).

> Note: the order of the parameters in the `$query` array does not matter!

### ID
`id ( array | number ) [ optional ]`

One ore more item ID's.

```php
// Providing one ID
$query = array(
  'id' => 5
)

// Providing several ID's
$query = array(
  'id' => array(5, 6, 7, 8)
);
```

> The new versions of the IGDB API (from v3) is handling item id's differently (from query perspective). Filtering the results by item id is now done by providing it in the `where` statement of the query. During runtime the class will add this value to the `where` statement. You can provide the id's in the `where` statement as well.

### Search
`search ( string ) [ optional ]`

Search based on name, results are sorted by similarity to the given search string.

```php
// Provide search string
$query = array(
  'search' => 'star wars'
);
```

> [IGDB Search Documentation](https://api-docs.igdb.com/#search)

### Fields
`fields ( array | string ) [ optional ]`

You can tell the wrapper which fields you want to get in the result.

Fields are properties of an entity. For example, a Game field would be `genres` or `release_dates`. Some fields have properties of their own, for example, the `genres` field has the property `name`.

```php
// Provide single or multiple fields as a string separated by comma
$query = array(
  'fields' => 'id,name'
);

// Provide single or multiple fields as an array
$query = array(
  'fields' => array('id', 'name');
);

// Get all fields in the result
$query = array(
  'fields' => '*'
);
```

> [IGDB Fields Documentation](https://api-docs.igdb.com/#fields)

### Limit
`limit ( number ) [ optional ]`

The maximum number of results in a single query. This value must be a number between `1` and `500`. The default value is `10`.

```php
// Provide a limit parameter
$query = array(
  'limit' => 20
);
```

> [IGDB Pagination Documentation](https://api-docs.igdb.com/#pagination)

### Offset
`offset ( number ) [ optional ]`

This will start the result list at the provided value and will give `limit` number of results. This value must be `0` or greater. The default value is `0`.

```php
// Provide an offset parameter
$query = array(
  'offset' => 5
);
```

> [IGDB Pagination Documentation](https://api-docs.igdb.com/#pagination)

### Where
`where ( string | array ) [ optional ]`

Filters are used to swift through results to get what you want. You can exclude and include results based on their properties. For example you could remove all Games where the rating was below 80 `(where rating >= 80)`.

> Note: in the old (v2) IGDB API wrapper this field was called `filter`.

If you provide the filter parameters as an array, you must have three values in it with the following indexes:
 - `field`: The name of the field you want to apply the filter to
 - `postfix`: The postfix you want to use with the filter. Refer to the IGDB Filters Documentation for available postfixes.
 - `value`: The value of the filter.

```php
// Provide a single filter rule as an array
// In this case you must have field, postfix and value elements in the array
$query = array(
  'field' => 'release_dates.platform',
  'postfix' => '=',
  'value' => 8
);

// Provide multiple filter rules as an array
// In this case you must have field, postfix and value elements in the arrays
$query = array(
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

You can provide the filter parameter as string too. In this case you can pass the string with as an apicalypse string:

```php
// Provide a single filter rule as a string
$query = array(
  'where' => 'release_dates.platform = 8'
);
```

Or you can provide multiple criteria as an array with apicalypse syntax:

```php
$query = array(
    'fields' => 'id, name, platforms, genres',  // we want to see these fields in the result
    'where' => array(                           // make sure to have each criteria as a separate element in the array
        'release_dates.platform = 8',           // and separate field names, postfixes and values with space
        'total_rating >= 70',
        'genres = 4'
    )
);
```

In this case make sure to separate the field name, the postfix and the value with spaces!

> [IGDB Apicalypse Documentation](https://api-docs.igdb.com/#apicalypse-1) and [IGDB Filters Documentation](https://api-docs.igdb.com/#filters)

### Sort
``sort ( string | array ) [ optional ]``: sorting (ordering) is used to order results by a specific field.

> Note: in the old (v2) IGDB API wrapper this field was called `order`.

IF you provide the Order parameter as an array, you must have two values in it with the following indexes:
 - ``field``: The field you want to do the ordering by
 - ``direction``: The direction of the ordering. It must be either ``asc`` for ascending or ``desc`` for descending ordering.

```php
// Provide an sort parameter as an array
$query = array(
    'sort' => array(
        'field' => 'release_dates.date',
        'direction' => 'desc',
    )
);
```

You can also provide the sort parameter as string. In this case you can pass the string with apycalipse syntax:

```php
// Provide an order parameter as a string
$query = array(
  'sort' => 'release_dates.date desc'
);
```

> [IGDB Sorting Documentation](https://api-docs.igdb.com/#sorting)

## Public Methods

### Apicalypse
`IGDB::apicalypse( array $query ) : string`

You can convert the `$query` array to IGDB's query language called Apicalypse. This method will return a string with the parsed parameters.

**Parameters**
 - `$query`: an array, containing specific fields the [Query Parameters](#query-parameters)

Returns a string formatted as an apicalypse string.

> [IGDB Apicalypse Documentation](https://api-docs.igdb.com/#apicalypse-1)

### Get Request Info
`IGDB::get_request_info() : mixed`

After a query is executed, the request information will be stored in the [`IGDB::$request_info`](#request-info) property and can be retrieved using this method.

The new version of the IGDB API (v4) will return a http response code `429` when you exceed the limit on the database of 4 requests per second. The response code of the requests can be fetched from the request info:

```php
$IGDB->game($query);
$info = $IGDB->get_request_info();

// this will print the status code of the last request
echo $info['http_code']
```

> [IGDB Rate Limits Documentation](https://api-docs.igdb.com/#rate-limits)

### Close CURL Session
`IGDB::curl_close() : void`

You can close the CURL session handler manually if you want to. The class will not do it by itself to be able to execute multiple requests with the same instance. After closing the session you will not be able to start new query with the actual instance of the class unless you reinitialize it.

### Reinitialize CURL session
`IGDB::curl_reinit() : void`

After you closed the CURL session manually with [`IGDB::curl_close()`](#close-curl-session) than you will not be able to run any query against IGDB with the current instance of the class. However, if you need to run a query again, just call this method, and the CURL handler will be reinitialized.

## Private Methods

### Initialize CURL Session
`IGDB::_curl_init() : void`

This method creates the CURL session and sets a few additional configuration to it.

### Executing Query
`IGDB::_exec_query ( string $endpoint, string | array $query ) : array`

This method will start the query against IGDB. Returns the decoded JSON response from IGDB as an array of objects.

**Parameters**
 - `$endpoint`: url of the endpoint to execute the query against
 - `$query`: the query to send. It can be either an apicalypse string or a query array

Returns the response from the IGDB database as an array of objects.

### Constructing URL's
`private IGDB::_construct_url( string $endpoint, boolean $count = false) : string`

The method will construct the full URL for the request and will return the constructed URL as a string.

**Parameters**
 - `$endpoint`: the endpoint to use
 - `$count`: whether the request should return the number of matches instead of the actual resultset

Returns the full constructed URL to the IGDB Endpoint as a string.

## Endpoints
Every endpoint method takes two parameters:
 - `$query`: this will set the required details of the query
 - `$count`: this will tell whether to return the records or the count of the records

> For more details on the query parameters check the [Query Parameters Section](#query-parameters)

These methods are returning an array with objects decoded from IGDB response JSON by default. If you provide boolean `true` as a second parameter, it will execute a count query against the selected endpoint which will return an object with a `count` property holding the sum of the found items. The count queries can be filtered with the `$query` filters.

Exceptions are thrown in any case of error.

Refer to the [Return Values](#return-values) Section for more details about the return values of these methods.

### Age Rating Content Description
`IGDB::age_rating_content_description(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Age Rating Content Description Endpoint](https://api-docs.igdb.com/#age-rating-content-description). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

The organisation behind a specific rating

Fields:

| Field | Type | Description |
|-------|------|-------------|
| category | Category Enum |  |
| checksum | uuid | Hash of the object |
| description | String |  |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Age Rating
`IGDB::age_rating(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Age Rating Endpoint](https://api-docs.igdb.com/#age-rating). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Age Rating according to various rating organisations

Fields:

| Field | Type | Description |
|-------|------|-------------|
| category | Category Enum | The organization that has issued a specific rating |
| checksum | uuid | Hash of the object |
| content_descriptions | Reference ID for  Age Rating Content Description |  |
| rating | Rating Enum | The title of an age rating |
| rating_cover_url | String | The url for  the image of a age rating |
| synopsis | String | A free text motivating a rating |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Alternative Name
`IGDB::alternative_name(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Alternative Name Endpoint](https://api-docs.igdb.com/#alternative-name). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Alternative and international game titles

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| comment | String | A description of what kind of alternative name it is (Acronym, Working title, Japanese title etc) |
| game | Reference ID for Game | The game this alternative name is associated with |
| name | String | An alternative name |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Artwork
`IGDB::artwork(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Artwork Endpoint](https://api-docs.igdb.com/#artwork). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

official artworks (resolution and aspect ratio may vary)

Fields:

| Field | Type | Description |
|-------|------|-------------|
| alpha_channel | boolean |  |
| animated | boolean |  |
| checksum | uuid | Hash of the object |
| game | Reference ID for Game | The game this artwork is associated with |
| height | Integer | The height of the image in pixels |
| image_id | String | The ID of the image used to construct an IGDB image link |
| url | String | The website address (URL) of the item |
| width | Integer | The width of the image in pixels |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Character Mug Shot
`IGDB::character_mug_shot(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Character Mug Shot Endpoint](https://api-docs.igdb.com/#character-mug-shot). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Images depicting game characters

Fields:

| Field | Type | Description |
|-------|------|-------------|
| alpha_channel | boolean |  |
| animated | boolean |  |
| checksum | uuid | Hash of the object |
| height | Integer | The height of the image in pixels |
| image_id | String | The ID of the image used to construct an IGDB image link |
| url | String | The website address (URL) of the item |
| width | Integer | The width of the image in pixels |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Character
`IGDB::character(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Character Endpoint](https://api-docs.igdb.com/#character). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Video game characters

Fields:

| Field | Type | Description |
|-------|------|-------------|
| akas | Array of Strings | Alternative names for a character |
| checksum | uuid | Hash of the object |
| country_name | String | A characters country of origin |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| description | String | A text describing a character |
| games | Array of Game IDs |  |
| gender | Gender Enum |  |
| mug_shot | Reference ID for  Character Mug Shot | An image depicting a character |
| name | String |  |
| slug | String | A url-safe, unique, lower-case version of the name |
| species | Species Enum |  |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Collection
`IGDB::collection(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Collection Endpoint](https://api-docs.igdb.com/#collection). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Collection, AKA Series

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| games | Array of Game IDs | The games that are associated with this collection |
| name | String | Umbrella term for a collection of games |
| slug | String | A url-safe, unique, lower-case version of the name |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Company Logo
`IGDB::company_logo(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Company Logo Endpoint](https://api-docs.igdb.com/#company-logo). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

The logos of developers and publishers

Fields:

| Field | Type | Description |
|-------|------|-------------|
| alpha_channel | boolean |  |
| animated | boolean |  |
| checksum | uuid | Hash of the object |
| height | Integer | The height of the image in pixels |
| image_id | String | The ID of the image used to construct an IGDB image link |
| url | String | The website address (URL) of the item |
| width | Integer | The width of the image in pixels |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Company Website
`IGDB::company_website(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Company Website Endpoint](https://api-docs.igdb.com/#company-website). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Company Website

Fields:

| Field | Type | Description |
|-------|------|-------------|
| category | Category Enum | The service this website links to |
| checksum | uuid | Hash of the object |
| trusted | boolean |  |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Company
`IGDB::company(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Company Endpoint](https://api-docs.igdb.com/#company). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Video game companies. Both publishers &amp; developers

Fields:

| Field | Type | Description |
|-------|------|-------------|
| change_date | Unix Time Stamp | The data when a company got a new ID |
| change_date_category | Change Date Category Enum |  |
| changed_company_id | Reference ID for  Company | The new ID for a company that has gone through a merger or restructuring |
| checksum | uuid | Hash of the object |
| country | Integer | ISO 3166-1 country code |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| description | String | A free text description of a company |
| developed | Reference ID for  Game | An array of games that a company has developed |
| logo | Reference ID for  Company Logo | The company&rsquo;s logo |
| name | String |  |
| parent | Reference ID for  Company | A company with a controlling interest in a specific company |
| published | Reference ID for  Game | An array of games that a company has published |
| slug | String | A url-safe, unique, lower-case version of the name |
| start_date | Unix Time Stamp | The date a company was founded |
| start_date_category | Start Date Category Enum |  |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |
| websites | Reference ID for  Company Website | The companies official websites |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Cover
`IGDB::cover(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Cover Endpoint](https://api-docs.igdb.com/#cover). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

The cover art of games

Fields:

| Field | Type | Description |
|-------|------|-------------|
| alpha_channel | boolean |  |
| animated | boolean |  |
| checksum | uuid | Hash of the object |
| game | Reference ID for Game | The game this cover is associated with |
| height | Integer | The height of the image in pixels |
| image_id | String | The ID of the image used to construct an IGDB image link |
| url | String | The website address (URL) of the item |
| width | Integer | The width of the image in pixels |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### External Game
`IGDB::external_game(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [External Game Endpoint](https://api-docs.igdb.com/#external-game). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Game IDs on other services

Fields:

| Field | Type | Description |
|-------|------|-------------|
| category | Category Enum | The id of the other service |
| checksum | uuid | Hash of the object |
| countries | Array of Integers | The ISO country code of the external game product. |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| game | Reference ID for Game | The IGDB ID of the game |
| media | Media Enum | The media of the external game. |
| name | String | The name of the game according to the other service |
| platform | Reference ID for Platform | The platform of the external game product. |
| uid | String | The other services ID for this game |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |
| year | Integer | The year in full (2018) |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Franchise
`IGDB::franchise(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Franchise Endpoint](https://api-docs.igdb.com/#franchise). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

A list of video game franchises such as Star Wars.

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| games | Array of Game IDs | The games that are associated with this franchise |
| name | String | The name of the franchise |
| slug | String | A url-safe, unique, lower-case version of the name |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Game Engine Logo
`IGDB::game_engine_logo(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Game Engine Logo Endpoint](https://api-docs.igdb.com/#game-engine-logo). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

The logos of game engines

Fields:

| Field | Type | Description |
|-------|------|-------------|
| alpha_channel | boolean |  |
| animated | boolean |  |
| checksum | uuid | Hash of the object |
| height | Integer | The height of the image in pixels |
| image_id | String | The ID of the image used to construct an IGDB image link |
| url | String | The website address (URL) of the item |
| width | Integer | The width of the image in pixels |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Game Engine
`IGDB::game_engine(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Game Engine Endpoint](https://api-docs.igdb.com/#game-engine). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Video game engines such as unreal engine.

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| companies | Array of Company IDs | Companies who used this game engine |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| description | String | Description of the game engine |
| logo | Reference ID for  Game Engine Logo | Logo of the game engine |
| name | String | Name of the game engine |
| platforms | Array of Platform IDs | Platforms this game engine was deployed on |
| slug | String | A url-safe, unique, lower-case version of the name |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Game Mode
`IGDB::game_mode(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Game Mode Endpoint](https://api-docs.igdb.com/#game-mode). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Single player, Multiplayer etc

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| name | String | The name of the game mode |
| slug | String | A url-safe, unique, lower-case version of the name |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Game Version Feature Value
`IGDB::game_version_feature_value(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Game Version Feature Value Endpoint](https://api-docs.igdb.com/#game-version-feature-value). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

The bool/text value of the feature

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| game | Reference ID for Game | The version/edition this value refers to |
| game_feature | Reference ID for  Game Version Feature | The id of the game feature |
| included_feature | Included Feature Enum | The boole value of this feature |
| note | String | The text value of this feature |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Game Version Feature
`IGDB::game_version_feature(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Game Version Feature Endpoint](https://api-docs.igdb.com/#game-version-feature). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Features and descriptions of what makes each version/edition different from the main game

Fields:

| Field | Type | Description |
|-------|------|-------------|
| category | Category Enum | The category of the feature description |
| checksum | uuid | Hash of the object |
| description | String | The description of the feature |
| position | Integer | Position of this feature in the list of features |
| title | String | The title of the feature |
| values | Reference ID for  Game Version Feature Value | The bool/text value of the feature |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Game Version
`IGDB::game_version(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Game Version Endpoint](https://api-docs.igdb.com/#game-version). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Details about game editions and versions.

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| features | Reference ID for  Game Version Feature | Features and descriptions of what makes each version/edition different from the main game |
| game | Reference ID for Game | The game these versions/editions are of |
| games | Array of Game IDs | Game Versions and Editions |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Game Video
`IGDB::game_video(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Game Video Endpoint](https://api-docs.igdb.com/#game-video). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

A video associated with a game

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| game | Reference ID for Game | The game this video is associated with |
| name | String | The name of the video |
| video_id | String | The external ID of the video (usually youtube) |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Game
`IGDB::game(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Game Endpoint](https://api-docs.igdb.com/#game). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Video Games!

Fields:

| Field | Type | Description |
|-------|------|-------------|
| age_ratings | Reference ID for  Age Rating | The PEGI rating |
| aggregated_rating | Double | Rating based on external critic scores |
| aggregated_rating_count | Integer | Number of external critic scores |
| alternative_names | Array of Alternative Name IDs | Alternative names for this game |
| artworks | Array of Artwork IDs | Artworks of this game |
| bundles | Reference ID for  Game | The bundles this game is a part of |
| category | Category Enum | The category of this game |
| checksum | uuid | Hash of the object |
| collection | Reference ID for Collection | The series the game belongs to |
| cover | Reference ID for Cover | The cover of this game |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| dlcs | Reference ID for  Game | DLCs for this game |
| expansions | Reference ID for  Game | Expansions of this game |
| external_games | Array of External Game IDs | External IDs this game has on other services |
| first_release_date | Unix Time Stamp | The first release date for this game |
| follows | Integer | Number of people following a game |
| franchise | Reference ID for Franchise | The main franchise |
| franchises | Array of Franchise IDs | Other franchises the game belongs to |
| game_engines | Array of Game Engine IDs | The game engine used in this game |
| game_modes | Array of Game Mode IDs | Modes of gameplay |
| genres | Array of Genre IDs | Genres of the game |
| hypes | Integer | Number of follows a game gets before release |
| involved_companies | Array of Involved Company IDs | Companies who developed this game |
| keywords | Array of Keyword IDs | Associated keywords |
| multiplayer_modes | Array of Multiplayer Mode IDs | Multiplayer modes for this game |
| name | String |  |
| parent_game | Reference ID for  Game | If a DLC, expansion or part of a bundle, this is the main game or bundle |
| platforms | Array of Platform IDs | Platforms this game was released on |
| player_perspectives | Array of Player Perspective IDs | The main perspective of the player |
| rating | Double | Average IGDB user rating |
| rating_count | Integer | Total number of IGDB user ratings |
| release_dates | Array of Release Date IDs | Release dates of this game |
| screenshots | Array of Screenshot IDs | Screenshots of this game |
| similar_games | Reference ID for  Game | Similar games |
| slug | String | A url-safe, unique, lower-case version of the name |
| standalone_expansions | Reference ID for  Game | Standalone expansions of this game |
| status | Status Enum | The status of the games release |
| storyline | String | A short description of a games story |
| summary | String | A description of the game |
| tags | Array of Tag Numbers | Related entities in the IGDB API |
| themes | Array of Theme IDs | Themes of the game |
| total_rating | Double | Average rating based on both IGDB user and external critic scores |
| total_rating_count | Integer | Total number of user and external critic scores |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |
| version_parent | Reference ID for  Game | If a version, this is the main game |
| version_title | String | Title of this version (i.e Gold edition) |
| videos | Reference ID for  Game Video | Videos of this game |
| websites | Reference ID for  Website | Websites associated with this game |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Genre
`IGDB::genre(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Genre Endpoint](https://api-docs.igdb.com/#genre). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Genres of video game

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| name | String |  |
| slug | String | A url-safe, unique, lower-case version of the name |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Involved Company
`IGDB::involved_company(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Involved Company Endpoint](https://api-docs.igdb.com/#involved-company). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

<code>https://api.igdb.com/v4/involved_companies</code>

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| company | Reference ID for Company |  |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| developer | boolean |  |
| game | Reference ID for Game |  |
| porting | boolean |  |
| publisher | boolean |  |
| supporting | boolean |  |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Keyword
`IGDB::keyword(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Keyword Endpoint](https://api-docs.igdb.com/#keyword). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Keywords are words or phrases that get tagged to a game such as “world war 2” or “steampunk”.

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| name | String |  |
| slug | String | A url-safe, unique, lower-case version of the name |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Multiplayer Mode
`IGDB::multiplayer_mode(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Multiplayer Mode Endpoint](https://api-docs.igdb.com/#multiplayer-mode). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Data about the supported multiplayer types

Fields:

| Field | Type | Description |
|-------|------|-------------|
| campaigncoop | boolean | True if the game supports campaign coop |
| checksum | uuid | Hash of the object |
| dropin | boolean | True if the game supports drop in/out multiplayer |
| game | Reference ID for Game | The game this multiplayer mode is associated with |
| lancoop | boolean | True if the game supports LAN coop |
| offlinecoop | boolean | True if the game supports offline coop |
| offlinecoopmax | Integer | Maximum number of offline players in offline coop |
| offlinemax | Integer | Maximum number of players in offline multiplayer |
| onlinecoop | boolean | True if the game supports online coop |
| onlinecoopmax | Integer | Maximum number of online players in online coop |
| onlinemax | Integer | Maximum number of players in online multiplayer |
| platform | Reference ID for Platform | The platform this multiplayer mode refers to |
| splitscreen | boolean | True if the game supports split screen, offline multiplayer |
| splitscreenonline | boolean | True if the game supports split screen, online multiplayer |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Platform Family
`IGDB::platform_family(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Platform Family Endpoint](https://api-docs.igdb.com/#platform-family). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

A collection of closely related platforms

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| name | String | The name of the platform family |
| slug | String | A url-safe, unique, lower-case version of the name |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Platform Logo
`IGDB::platform_logo(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Platform Logo Endpoint](https://api-docs.igdb.com/#platform-logo). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Logo for a platform

Fields:

| Field | Type | Description |
|-------|------|-------------|
| alpha_channel | boolean |  |
| animated | boolean |  |
| checksum | uuid | Hash of the object |
| height | Integer | The height of the image in pixels |
| image_id | String | The ID of the image used to construct an IGDB image link |
| url | String | The website address (URL) of the item |
| width | Integer | The width of the image in pixels |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Platform Version Company
`IGDB::platform_version_company(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Platform Version Company Endpoint](https://api-docs.igdb.com/#platform-version-company). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

A platform developer

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| comment | String | Any notable comments about the developer |
| company | Reference ID for Company | The company responsible for developing this platform version |
| developer | boolean |  |
| manufacturer | boolean |  |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Platform Version Release Date
`IGDB::platform_version_release_date(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Platform Version Release Date Endpoint](https://api-docs.igdb.com/#platform-version-release-date). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

A handy endpoint that extends platform release dates. Used to dig deeper into release dates, platforms and versions.

Fields:

| Field | Type | Description |
|-------|------|-------------|
| category | Category Enum | The format of the release date |
| checksum | uuid | Hash of the object |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| date | Unix Time Stamp | The release date |
| human | String | A human readable version of the release date |
| m | Integer | The month as an integer starting at 1 (January) |
| platform_version | Reference ID for Platform Version | The platform this release date is for |
| region | Region Enum | The region of the release |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| y | Integer | The year in full (2018) |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Platform Version
`IGDB::platform_version(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Platform Version Endpoint](https://api-docs.igdb.com/#platform-version). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

<code>https://api.igdb.com/v4/platform_versions</code>

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| companies | Reference ID for  Platform Version Company | Who developed this platform version |
| connectivity | String | The network capabilities |
| cpu | String | The integrated control processing unit |
| graphics | String | The graphics chipset |
| main_manufacturer | Reference ID for  Platform Version Company | Who manufactured this version of the platform |
| media | String | The type of media this version accepted |
| memory | String | How much memory there is |
| name | String | The name of the platform version |
| os | String | The operating system installed on the platform version |
| output | String | The output video rate |
| platform_logo | Reference ID for Platform Logo | The logo of this platform version |
| platform_version_release_dates | Array of Platform Version Release Date IDs | When this platform was released |
| resolutions | String | The maximum resolution |
| slug | String | A url-safe, unique, lower-case version of the name |
| sound | String | The sound chipset |
| storage | String | How much storage there is |
| summary | String | A short summary |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Platform Website
`IGDB::platform_website(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Platform Website Endpoint](https://api-docs.igdb.com/#platform-website). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

The main website for the platform

Fields:

| Field | Type | Description |
|-------|------|-------------|
| category | Category Enum | The service this website links to |
| checksum | uuid | Hash of the object |
| trusted | boolean |  |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Platform
`IGDB::platform(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Platform Endpoint](https://api-docs.igdb.com/#platform). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

The hardware used to run the game or game delivery network

Fields:

| Field | Type | Description |
|-------|------|-------------|
| abbreviation | String | An abbreviation of the platform name |
| alternative_name | String | An alternative name for the platform |
| category | Category Enum | A physical or virtual category of the platform |
| checksum | uuid | Hash of the object |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| generation | Integer | The generation of the platform |
| name | String | The name of the platform |
| platform_family | Reference ID for Platform Family | The family of platforms this one belongs to |
| platform_logo | Reference ID for Platform Logo | The logo of the first Version of this platform |
| slug | String | A url-safe, unique, lower-case version of the name |
| summary | String | The summary of the first Version of this platform |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |
| versions | Reference ID for  Platform Version | Associated versions of this platform |
| websites | Reference ID for  Platform Website | The main website |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Player Perspective
`IGDB::player_perspective(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Player Perspective Endpoint](https://api-docs.igdb.com/#player-perspective). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Player perspectives describe the view/perspective of the player in a video game.

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| name | String |  |
| slug | String | A url-safe, unique, lower-case version of the name |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Release Date
`IGDB::release_date(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Release Date Endpoint](https://api-docs.igdb.com/#release-date). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

A handy endpoint that extends game release dates. Used to dig deeper into release dates, platforms and versions.

Fields:

| Field | Type | Description |
|-------|------|-------------|
| category | Category Enum | The format category of the release date |
| checksum | uuid | Hash of the object |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| date | Unix Time Stamp | The date of the release |
| game | Reference ID for Game |  |
| human | String | A human readable representation of the date |
| m | Integer | The month as an integer starting at 1 (January) |
| platform | Reference ID for Platform | The platform of the release |
| region | Region Enum | The region of the release |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| y | Integer | The year in full (2018) |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Screenshot
`IGDB::screenshot(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Screenshot Endpoint](https://api-docs.igdb.com/#screenshot). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Screenshots of games

Fields:

| Field | Type | Description |
|-------|------|-------------|
| alpha_channel | boolean |  |
| animated | boolean |  |
| checksum | uuid | Hash of the object |
| game | Reference ID for Game | The game this video is associated with |
| height | Integer | The height of the image in pixels |
| image_id | String | The ID of the image used to construct an IGDB image link |
| url | String | The website address (URL) of the item |
| width | Integer | The width of the image in pixels |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Search
`IGDB::search(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Search Endpoint](https://api-docs.igdb.com/#search). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

<code>https://api.igdb.com/v4/search</code>

Fields:

| Field | Type | Description |
|-------|------|-------------|
| alternative_name | String |  |
| character | Reference ID for Character |  |
| checksum | uuid | Hash of the object |
| collection | Reference ID for Collection |  |
| company | Reference ID for Company |  |
| description | String |  |
| game | Reference ID for Game |  |
| name | String |  |
| platform | Reference ID for Platform |  |
| published_at | Unix Time Stamp | The date this item was initially published by the third party |
| test_dummy | Reference ID for Test Dummy |  |
| theme | Reference ID for Theme |  |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Theme
`IGDB::theme(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Theme Endpoint](https://api-docs.igdb.com/#theme). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

Video game themes

Fields:

| Field | Type | Description |
|-------|------|-------------|
| checksum | uuid | Hash of the object |
| created_at | Unix Time Stamp | Date this was initially added to the IGDB database |
| name | String |  |
| slug | String | A url-safe, unique, lower-case version of the name |
| updated_at | Unix Time Stamp | The last date this entry was updated in the IGDB database |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

### Website
`IGDB::website(array $query, boolean $count = false) : mixed`

Fetching data from IGDB database using [Website Endpoint](https://api-docs.igdb.com/#website). For more details on the method parameters check the [Endpoints Section](#endpoints).

**Endpoint Details:**

A website url, usually associated with a game

Fields:

| Field | Type | Description |
|-------|------|-------------|
| category | Category Enum | The service this website links to |
| checksum | uuid | Hash of the object |
| game | Reference ID for Game | The game this website is associated with |
| trusted | boolean |  |
| url | String | The website address (URL) of the item |

Return value depends on the `$count` parameter. For more details on the return values check the [Return Values Section](#return-values).

## MultiQuery
`IGDB::multiquery(string $endpoint, string $result_name, mixed | array $query = null) : mixed`

Multi-Query is a new way to request a huge amount of information in one request! With Multi-Query you can request multiple endpoints at once, it also works with multiple requests to a single endpoint as well.

**Parameters**
 - `$endpoint ( string )`: the endpoint to use (note: not the wrapper method name, but the IGDB endpoint name. Also accepts count. Refer to the IGDB documentation for details!)
 - `$result_name ( string )`: A name, given by you.
 - `$query ( string | array | null )`: Either an apicalypse string or a query array. The default value is null, in this case no filter will be applied.

Returns either the records matching the filter criteria, or the count of these records. This depends on the `$endpoint` parameter.

Example query:
```php
/*
  A few things to note here:
    - the endpoint name has to be the IGDB endpoint name, not the
      wrapper class method name
    - there is a /count after the endpoint name which tells the api
      to return the record count instead of the actual records
    - the third parameter is missing, which has a default value NULL
      and the request will be sent without any filter parameters
*/

$IGDB->mutliquery("platforms/count", "Count of Platforms");
```

Result of the query:
```php
array (size=1)
  0 =>
    object(stdClass)[2]
      public 'name' => string 'Count of Platforms' (length=18)
      public 'count' => int 169
```

Example query with filters:
```php
$IGDB->mutliquery(
  "games",                                                                        // Endpoint
  "Playstation Games",                                                            // Result name
  "fields name,platforms.name; where platforms !=n & platforms = {48}; limit 1;"  // Apicalypse formatted string (also could be a query array)
);
```

Result of the query:
```php
// because of the limit parameter in the query, we have only 1 element in the result
array (size=1)
  0 =>
    object(stdClass)[2]
      public 'name' => string 'Playstation Games' (length=17)
      public 'result' =>
        array (size=1)
          0 =>
            object(stdClass)[3]
              public 'id' => int 41058
              public 'name' => string 'The Seven Deadly Sins: Knights of Britannia' (length=43)
              public 'platforms' =>
                array (size=1)
                  0 =>
                    object(stdClass)[4]
                      public 'id' => int 48
                      public 'name' => string 'PlayStation 4' (length=13)
```

> [IGDB Multi-Query Documentation](https://api-docs.igdb.com/#multi-query)

## Return Values
Every [Endpoint Method](#endpoints) can return two different type of results, depending on the second parameter provided for them:
 - By default the second ``$count`` parameter is boolean ``false``. this means, that the query will be executed against the IGDB, returning a ``$result`` array of objects.
    ```php
    var_dump(
      $IGDB->game(
        array(
          "id" => array(1,2),
          "fields" => "id,name"
        )
      )
    );
    ```

    The result of the query above:

    ```php
    array (size=2)
    0 =>
      object(stdClass)[2]
        public 'id' => int 1
        public 'name' => string 'Thief II: The Metal Age' (length=23)
    1 =>
      object(stdClass)[3]
        public 'id' => int 2
        public 'name' => string 'Thief: The Dark Project' (length=23)
    ```

 - If you pass a boolean ``true`` as a second parameter, then you will get an object with a ``count`` property containing the item count from the selected endpoint filtered by the `$query` filters.
    ```php
    var_dump(
      $IGDB->game(
        array(
          "id" => array(1,2),
          "fields" => "id,name"
        ), true // Note the second boolean true parameter
      )
    );
    ```

    The result of the query above:
    ```php
    object(stdClass)[3]
      public 'count' => int 2
    ```

The result object's properties will vary depending on the provided field list in the `$query` array. From the example result above you can see, the result holds an array, containing two elements. Every element of the result array is an object, containing properties with name of the fields from the `fields` parameter.

## Change Log

### v4.0.0 - October 20, 2020
 - **IGDB Api v4 compatibility update**
 - Updated Class constructor to accept the new tokens from Twitch
 - Removed API KEY
 - Removed `IGDB::api_status()` method
 - Removed Endpoint methods according to the [IGDB Changes](https://api-docs.igdb.com/#breaking-changes)
 - Renamed methods:
   - `_init_curl() => _curl_init()`
   - `close_curl() => curl_close()`
   - `reinit_curl() => curl_reinit()`
 - Updated endpoint methods to accept apicalypse strings as well
 - Implemented [Multiquery](#multiquery)

### v2.0.3 - September 17, 2020
 - Fixed a bug with the `where` filter ([#6 Issues with slug field](https://github.com/enisz/igdb/issues/6))

### v2.0.2 - February 03, 2020
 - Fixing inaccurate information in the Readme

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