---
overview: The main wrapper. Methods, properties, configurations. Sending queries to the IGDB API.
icon: fa-gift
---

# IGDB Wrapper

The most important part of the wrapper, the `IGDB` class which does the most important work: communicating with the IGDB API.

As mentioned in the [Introduction](#introduction), to have access to IGDB's database you have to register a Twitch Account and have your own `client_id` and `access_token`.

> You can add your tokens to the documentation to replace them in the exmple codes. Click the logo in the top left corner to get back to the main page and save your tokens.

## Instantiating the wrapper

After importing the dependencies you can instantiate the class with the `new` keyword, passing your tokens to the constructor.

```php
<?php

    require_once "class.igdb.php";

    $igdb = new IGDB("{client_id}", "{access_token}");

?>
```

> The wrapper itself does not validate your tokens. If your credentials are invalid, you will get an error from the IGDB API after executing a query.

## Public Methods

These methods are exposed from the `$igdb` object.

### Get Request Info
```php
public function get_request_info()
```

After a query is executed, the request information will be stored and can be retrieved using this method.

The new version of the IGDB API (v4) will return a http response code `429` when you exceed the limit of requests on the database (4 requests per second at the time of writing this docs).

```php
<?php

    require_once "class.igdb.php";

    $igdb = new IGDB("{client_id}", "{access_token}");

    try {
        $igdb->game('fields id,name; search "uncharted 4"; limit 1;');

        var_dump($igdb->get_request_info());
    } catch (IGDBEndpointException $e) {
        echo $e->getMessage();
    }

?>
```

Details of the query can be fetched from the output of the `get_request_info()` method (for example, the HTTP Response code from `http_code`).

```text
array (size=37)
  'url' => string 'https://api.igdb.com/v4/games' (length=29)
  'content_type' => string 'application/json;charset=utf-8' (length=30)
  'http_code' => int 200
  'header_size' => int 870
  'request_size' => int 224
  'filetime' => int -1
  'ssl_verify_result' => int 20
  'redirect_count' => int 0
  'total_time' => float 0.748895
  'namelookup_time' => float 0.000705
  'connect_time' => float 0.048049
  'pretransfer_time' => float 0.088656
  'size_upload' => float 46
  'size_download' => float 73
  'speed_download' => float 97
  'speed_upload' => float 61
  'download_content_length' => float 73
  'upload_content_length' => float 46
  'starttransfer_time' => float 0.088661
  'redirect_time' => float 0
  'redirect_url' => string '' (length=0)
  'primary_ip' => string '104.22.65.239' (length=13)
  'certinfo' =>
    array (size=0)
      empty
  'primary_port' => int 443
  'local_ip' => string '192.168.1.99' (length=12)
  'local_port' => int 58813
  'http_version' => int 3
  'protocol' => int 2
  'ssl_verifyresult' => int 0
  'scheme' => string 'HTTPS' (length=5)
  'appconnect_time_us' => int 88535
  'connect_time_us' => int 48049
  'namelookup_time_us' => int 705
  'pretransfer_time_us' => int 88656
  'redirect_time_us' => int 0
  'starttransfer_time_us' => int 88661
  'total_time_us' => int 748895
```

### Construct URL
```php
public function construct_url(string $endpoint, boolean $count = false): string
```

The method will construct the full URL for the request and will return the constructed URL as a string.

**Parameters**:
 - `$endpoint`: the endpoint to use (the name of the endpoint, not the path to it!)
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: the full constructed URL to the IGDB Endpoint as a string.

### Close CURL Session
```php
public function curl_close(): void
```

You can close the CURL session handler manually if you need to.

**Parameters**: -

**Returns**: -

> The curl handler will be [reinitialized](#reinitialize-curl-session) automatically when a new request is sent to the IGDB API with any of the endpoint methods.

### Reinitialize CURL Session
```php
public function curl_reinit() : void
```

After you closed the CURL session manually with [curl_close()](#close-curl-session) you can manually reinitialize the curl handler.

**Parameters**: -

**Returns**: -

> Before sending a request with an endpoint method, the wrapper will check the status of the curl handler. If it is closed, it will reinitialize it automatically.

## Endpoints

Every endpoint method is named after the IGDB API endpoints using snake-casing naming convention. These methods are expecting at least one parameter, the `$query` itself. The second `$count` parameter is conditional, it is `false` by default.

**Parameters**:
 - `$query`: the query itself as an apicalypse string
 - `$count`: a `boolean` value to whether return the records or the count of the records

> To build your queries, give [IGDB Query Builder](#igdb-query-builder) a try!

These methods will return **an array with objects** decoded from IGDB response JSON by when the `$count` parameter is false. Otherwise, it will execute a count query against the selected endpoint which will return an object with a `count` property holding the sum of the found items. The count queries can be filtered with [where](#where) filters.

`IGDBEndpointException` is thrown in any case of error.

Refer to the [Return Values](#return-values) Section for more details about the return values of these methods.

### Age Rating Content Description
```php
public function age_rating_content_description(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Age Rating Content Description](https://api-docs.igdb.com/age-rating-content-description) endpoint.

**Endpoint Description**: The organisation behind a specific rating

**Fields in response**
 - `category` ([Category Enum](https://api-docs.igdb.com/#age-rating-content-description-enums))
 - `checksum` (uuid): Hash of the object
 - `description` (String)

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Age Rating Content Description endpoint method
    $igdb->age_rating_content_description($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Age Rating
```php
public function age_rating(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Age Rating](https://api-docs.igdb.com/age-rating) endpoint.

**Endpoint Description**: Age Rating according to various rating organisations

**Fields in response**
 - `category` ([Category Enum](https://api-docs.igdb.com/#age-rating-enums)): The organization that has issued a specific rating
 - `checksum` (uuid): Hash of the object
 - `content_descriptions` (Reference ID for [ Age Rating Content Description](https://api-docs.igdb.com/#age-rating-content-description))
 - `rating` ([Rating Enum](https://api-docs.igdb.com/#age-rating-enums)): The title of an age rating
 - `rating_cover_url` (String): The url for  the image of a age rating
 - `synopsis` (String): A free text motivating a rating

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Age Rating endpoint method
    $igdb->age_rating($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Alternative Name
```php
public function alternative_name(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Alternative Name](https://api-docs.igdb.com/alternative-name) endpoint.

**Endpoint Description**: Alternative and international game titles

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `comment` (String): A description of what kind of alternative name it is (Acronym, Working title, Japanese title etc)
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game)): The game this alternative name is associated with
 - `name` (String): An alternative name

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Alternative Name endpoint method
    $igdb->alternative_name($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Artwork
```php
public function artwork(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Artwork](https://api-docs.igdb.com/artwork) endpoint.

**Endpoint Description**: official artworks (resolution and aspect ratio may vary)

**Fields in response**
 - `alpha_channel` (boolean)
 - `animated` (boolean)
 - `checksum` (uuid): Hash of the object
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game)): The game this artwork is associated with
 - `height` (Integer): The height of the image in pixels
 - `image_id` (String): The ID of the image used to construct an IGDB image link
 - `url` (String): The website address (URL) of the item
 - `width` (Integer): The width of the image in pixels

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Artwork endpoint method
    $igdb->artwork($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Character Mug Shot
```php
public function character_mug_shot(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Character Mug Shot](https://api-docs.igdb.com/character-mug-shot) endpoint.

**Endpoint Description**: Images depicting game characters

**Fields in response**
 - `alpha_channel` (boolean)
 - `animated` (boolean)
 - `checksum` (uuid): Hash of the object
 - `height` (Integer): The height of the image in pixels
 - `image_id` (String): The ID of the image used to construct an IGDB image link
 - `url` (String): The website address (URL) of the item
 - `width` (Integer): The width of the image in pixels

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Character Mug Shot endpoint method
    $igdb->character_mug_shot($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Character
```php
public function character(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Character](https://api-docs.igdb.com/character) endpoint.

**Endpoint Description**: Video game characters

**Fields in response**
 - `akas` (Array of Strings): Alternative names for a character
 - `checksum` (uuid): Hash of the object
 - `country_name` (String): A characters country of origin
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `description` (String): A text describing a character
 - `games` (Array of [Game](https://api-docs.igdb.com/#game) IDs)
 - `gender` ([Gender Enum](https://api-docs.igdb.com/#character-enums))
 - `mug_shot` (Reference ID for [ Character Mug Shot](https://api-docs.igdb.com/#character-mug-shot)): An image depicting a character
 - `name` (String)
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `species` ([Species Enum](https://api-docs.igdb.com/#character-enums))
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Character endpoint method
    $igdb->character($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Collection
```php
public function collection(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Collection](https://api-docs.igdb.com/collection) endpoint.

**Endpoint Description**: Collection, AKA Series

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `games` (Array of [Game](https://api-docs.igdb.com/#game) IDs): The games that are associated with this collection
 - `name` (String): Umbrella term for a collection of games
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Collection endpoint method
    $igdb->collection($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Company Logo
```php
public function company_logo(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Company Logo](https://api-docs.igdb.com/company-logo) endpoint.

**Endpoint Description**: The logos of developers and publishers

**Fields in response**
 - `alpha_channel` (boolean)
 - `animated` (boolean)
 - `checksum` (uuid): Hash of the object
 - `height` (Integer): The height of the image in pixels
 - `image_id` (String): The ID of the image used to construct an IGDB image link
 - `url` (String): The website address (URL) of the item
 - `width` (Integer): The width of the image in pixels

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Company Logo endpoint method
    $igdb->company_logo($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Company Website
```php
public function company_website(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Company Website](https://api-docs.igdb.com/company-website) endpoint.

**Endpoint Description**: Company Website

**Fields in response**
 - `category` ([Category Enum](https://api-docs.igdb.com/#company-website-enums)): The service this website links to
 - `checksum` (uuid): Hash of the object
 - `trusted` (boolean)
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Company Website endpoint method
    $igdb->company_website($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Company
```php
public function company(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Company](https://api-docs.igdb.com/company) endpoint.

**Endpoint Description**: Video game companies. Both publishers &amp;amp; developers

**Fields in response**
 - `change_date` (Unix Time Stamp): The data when a company got a new ID
 - `change_date_category` ([Change Date Category Enum](https://api-docs.igdb.com/#company-enums))
 - `changed_company_id` (Reference ID for [ Company](https://api-docs.igdb.com/#company)): The new ID for a company that has gone through a merger or restructuring
 - `checksum` (uuid): Hash of the object
 - `country` (Integer): ISO 3166-1 country code
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `description` (String): A free text description of a company
 - `developed` (Reference ID for [ Game](https://api-docs.igdb.com/#game)): An array of games that a company has developed
 - `logo` (Reference ID for [ Company Logo](https://api-docs.igdb.com/#company-logo)): The company&amp;rsquo;s logo
 - `name` (String)
 - `parent` (Reference ID for [ Company](https://api-docs.igdb.com/#company)): A company with a controlling interest in a specific company
 - `published` (Reference ID for [ Game](https://api-docs.igdb.com/#game)): An array of games that a company has published
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `start_date` (Unix Time Stamp): The date a company was founded
 - `start_date_category` ([Start Date Category Enum](https://api-docs.igdb.com/#company-enums))
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item
 - `websites` (Reference ID for [ Company Website](https://api-docs.igdb.com/#company-website)): The companies official websites

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Company endpoint method
    $igdb->company($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Cover
```php
public function cover(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Cover](https://api-docs.igdb.com/cover) endpoint.

**Endpoint Description**: The cover art of games

**Fields in response**
 - `alpha_channel` (boolean)
 - `animated` (boolean)
 - `checksum` (uuid): Hash of the object
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game)): The game this cover is associated with
 - `height` (Integer): The height of the image in pixels
 - `image_id` (String): The ID of the image used to construct an IGDB image link
 - `url` (String): The website address (URL) of the item
 - `width` (Integer): The width of the image in pixels

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Cover endpoint method
    $igdb->cover($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### External Game
```php
public function external_game(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [External Game](https://api-docs.igdb.com/external-game) endpoint.

**Endpoint Description**: Game IDs on other services

**Fields in response**
 - `category` ([Category Enum](https://api-docs.igdb.com/#external-game-enums)): The id of the other service
 - `checksum` (uuid): Hash of the object
 - `countries` (Array of Integers): The ISO country code of the external game product.
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game)): The IGDB ID of the game
 - `media` ([Media Enum](https://api-docs.igdb.com/#external-game-enums)): The media of the external game.
 - `name` (String): The name of the game according to the other service
 - `platform` (Reference ID for [Platform](https://api-docs.igdb.com/#platform)): The platform of the external game product.
 - `uid` (String): The other services ID for this game
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item
 - `year` (Integer): The year in full (2018)

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // External Game endpoint method
    $igdb->external_game($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Franchise
```php
public function franchise(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Franchise](https://api-docs.igdb.com/franchise) endpoint.

**Endpoint Description**: A list of video game franchises such as Star Wars.

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `games` (Array of [Game](https://api-docs.igdb.com/#game) IDs): The games that are associated with this franchise
 - `name` (String): The name of the franchise
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Franchise endpoint method
    $igdb->franchise($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Game Engine Logo
```php
public function game_engine_logo(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Engine Logo](https://api-docs.igdb.com/game-engine-logo) endpoint.

**Endpoint Description**: The logos of game engines

**Fields in response**
 - `alpha_channel` (boolean)
 - `animated` (boolean)
 - `checksum` (uuid): Hash of the object
 - `height` (Integer): The height of the image in pixels
 - `image_id` (String): The ID of the image used to construct an IGDB image link
 - `url` (String): The website address (URL) of the item
 - `width` (Integer): The width of the image in pixels

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Game Engine Logo endpoint method
    $igdb->game_engine_logo($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Game Engine
```php
public function game_engine(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Engine](https://api-docs.igdb.com/game-engine) endpoint.

**Endpoint Description**: Video game engines such as unreal engine.

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `companies` (Array of [Company](https://api-docs.igdb.com/#company) IDs): Companies who used this game engine
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `description` (String): Description of the game engine
 - `logo` (Reference ID for [ Game Engine Logo](https://api-docs.igdb.com/#game-engine-logo)): Logo of the game engine
 - `name` (String): Name of the game engine
 - `platforms` (Array of [Platform](https://api-docs.igdb.com/#platform) IDs): Platforms this game engine was deployed on
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Game Engine endpoint method
    $igdb->game_engine($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Game Mode
```php
public function game_mode(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Mode](https://api-docs.igdb.com/game-mode) endpoint.

**Endpoint Description**: Single player, Multiplayer etc

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `name` (String): The name of the game mode
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Game Mode endpoint method
    $igdb->game_mode($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Game Version Feature Value
```php
public function game_version_feature_value(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Version Feature Value](https://api-docs.igdb.com/game-version-feature-value) endpoint.

**Endpoint Description**: The bool&#x2F;text value of the feature

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game)): The version&#x2F;edition this value refers to
 - `game_feature` (Reference ID for [ Game Version Feature](https://api-docs.igdb.com/#game-version-feature)): The id of the game feature
 - `included_feature` ([Included Feature Enum](https://api-docs.igdb.com/#game-version-feature-value-enums)): The boole value of this feature
 - `note` (String): The text value of this feature

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Game Version Feature Value endpoint method
    $igdb->game_version_feature_value($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Game Version Feature
```php
public function game_version_feature(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Version Feature](https://api-docs.igdb.com/game-version-feature) endpoint.

**Endpoint Description**: Features and descriptions of what makes each version&#x2F;edition different from the main game

**Fields in response**
 - `category` ([Category Enum](https://api-docs.igdb.com/#game-version-feature-enums)): The category of the feature description
 - `checksum` (uuid): Hash of the object
 - `description` (String): The description of the feature
 - `position` (Integer): Position of this feature in the list of features
 - `title` (String): The title of the feature
 - `values` (Reference ID for [ Game Version Feature Value](https://api-docs.igdb.com/#game-version-feature-value)): The bool&#x2F;text value of the feature

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Game Version Feature endpoint method
    $igdb->game_version_feature($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Game Version
```php
public function game_version(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Version](https://api-docs.igdb.com/game-version) endpoint.

**Endpoint Description**: Details about game editions and versions.

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `features` (Reference ID for [ Game Version Feature](https://api-docs.igdb.com/#game-version-feature)): Features and descriptions of what makes each version&#x2F;edition different from the main game
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game)): The game these versions&#x2F;editions are of
 - `games` (Array of [Game](https://api-docs.igdb.com/#game) IDs): Game Versions and Editions
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Game Version endpoint method
    $igdb->game_version($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Game Video
```php
public function game_video(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Video](https://api-docs.igdb.com/game-video) endpoint.

**Endpoint Description**: A video associated with a game

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game)): The game this video is associated with
 - `name` (String): The name of the video
 - `video_id` (String): The external ID of the video (usually youtube)

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Game Video endpoint method
    $igdb->game_video($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Game
```php
public function game(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game](https://api-docs.igdb.com/game) endpoint.

**Endpoint Description**: Video Games!

**Fields in response**
 - `age_ratings` (Reference ID for [ Age Rating](https://api-docs.igdb.com/#age-rating)): The PEGI rating
 - `aggregated_rating` (Double): Rating based on external critic scores
 - `aggregated_rating_count` (Integer): Number of external critic scores
 - `alternative_names` (Array of [Alternative Name](https://api-docs.igdb.com/#alternative-name) IDs): Alternative names for this game
 - `artworks` (Array of [Artwork](https://api-docs.igdb.com/#artwork) IDs): Artworks of this game
 - `bundles` (Reference ID for [ Game](https://api-docs.igdb.com/#game)): The bundles this game is a part of
 - `category` ([Category Enum](https://api-docs.igdb.com/#game-enums)): The category of this game
 - `checksum` (uuid): Hash of the object
 - `collection` (Reference ID for [Collection](https://api-docs.igdb.com/#collection)): The series the game belongs to
 - `cover` (Reference ID for [Cover](https://api-docs.igdb.com/#cover)): The cover of this game
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `dlcs` (Reference ID for [ Game](https://api-docs.igdb.com/#game)): DLCs for this game
 - `expansions` (Reference ID for [ Game](https://api-docs.igdb.com/#game)): Expansions of this game
 - `external_games` (Array of [External Game](https://api-docs.igdb.com/#external-game) IDs): External IDs this game has on other services
 - `first_release_date` (Unix Time Stamp): The first release date for this game
 - `follows` (Integer): Number of people following a game
 - `franchise` (Reference ID for [Franchise](https://api-docs.igdb.com/#franchise)): The main franchise
 - `franchises` (Array of [Franchise](https://api-docs.igdb.com/#franchise) IDs): Other franchises the game belongs to
 - `game_engines` (Array of [Game Engine](https://api-docs.igdb.com/#game-engine) IDs): The game engine used in this game
 - `game_modes` (Array of [Game Mode](https://api-docs.igdb.com/#game-mode) IDs): Modes of gameplay
 - `genres` (Array of [Genre](https://api-docs.igdb.com/#genre) IDs): Genres of the game
 - `hypes` (Integer): Number of follows a game gets before release
 - `involved_companies` (Array of [Involved Company](https://api-docs.igdb.com/#involved-company) IDs): Companies who developed this game
 - `keywords` (Array of [Keyword](https://api-docs.igdb.com/#keyword) IDs): Associated keywords
 - `multiplayer_modes` (Array of [Multiplayer Mode](https://api-docs.igdb.com/#multiplayer-mode) IDs): Multiplayer modes for this game
 - `name` (String)
 - `parent_game` (Reference ID for [ Game](https://api-docs.igdb.com/#game)): If a DLC, expansion or part of a bundle, this is the main game or bundle
 - `platforms` (Array of [Platform](https://api-docs.igdb.com/#platform) IDs): Platforms this game was released on
 - `player_perspectives` (Array of [Player Perspective](https://api-docs.igdb.com/#player-perspective) IDs): The main perspective of the player
 - `rating` (Double): Average IGDB user rating
 - `rating_count` (Integer): Total number of IGDB user ratings
 - `release_dates` (Array of [Release Date](https://api-docs.igdb.com/#release-date) IDs): Release dates of this game
 - `screenshots` (Array of [Screenshot](https://api-docs.igdb.com/#screenshot) IDs): Screenshots of this game
 - `similar_games` (Reference ID for [ Game](https://api-docs.igdb.com/#game)): Similar games
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `standalone_expansions` (Reference ID for [ Game](https://api-docs.igdb.com/#game)): Standalone expansions of this game
 - `status` ([Status Enum](https://api-docs.igdb.com/#game-enums)): The status of the games release
 - `storyline` (String): A short description of a games story
 - `summary` (String): A description of the game
 - `tags` (Array of [Tag Numbers](https://api-docs.igdb.com/#tag-numbers)): Related entities in the IGDB API
 - `themes` (Array of [Theme](https://api-docs.igdb.com/#theme) IDs): Themes of the game
 - `total_rating` (Double): Average rating based on both IGDB user and external critic scores
 - `total_rating_count` (Integer): Total number of user and external critic scores
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item
 - `version_parent` (Reference ID for [ Game](https://api-docs.igdb.com/#game)): If a version, this is the main game
 - `version_title` (String): Title of this version (i.e Gold edition)
 - `videos` (Reference ID for [ Game Video](https://api-docs.igdb.com/#game-video)): Videos of this game
 - `websites` (Reference ID for [ Website](https://api-docs.igdb.com/#website)): Websites associated with this game

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Game endpoint method
    $igdb->game($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Genre
```php
public function genre(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Genre](https://api-docs.igdb.com/genre) endpoint.

**Endpoint Description**: Genres of video game

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `name` (String)
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Genre endpoint method
    $igdb->genre($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Involved Company
```php
public function involved_company(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Involved Company](https://api-docs.igdb.com/involved-company) endpoint.

**Endpoint Description**: &lt;code&gt;https:&#x2F;&#x2F;api.igdb.com&#x2F;v4&#x2F;involved_companies&lt;&#x2F;code&gt;

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `company` (Reference ID for [Company](https://api-docs.igdb.com/#company))
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `developer` (boolean)
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game))
 - `porting` (boolean)
 - `publisher` (boolean)
 - `supporting` (boolean)
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Involved Company endpoint method
    $igdb->involved_company($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Keyword
```php
public function keyword(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Keyword](https://api-docs.igdb.com/keyword) endpoint.

**Endpoint Description**: Keywords are words or phrases that get tagged to a game such as “world war 2” or “steampunk”.

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `name` (String)
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Keyword endpoint method
    $igdb->keyword($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Multiplayer Mode
```php
public function multiplayer_mode(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Multiplayer Mode](https://api-docs.igdb.com/multiplayer-mode) endpoint.

**Endpoint Description**: Data about the supported multiplayer types

**Fields in response**
 - `campaigncoop` (boolean): True if the game supports campaign coop
 - `checksum` (uuid): Hash of the object
 - `dropin` (boolean): True if the game supports drop in&#x2F;out multiplayer
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game)): The game this multiplayer mode is associated with
 - `lancoop` (boolean): True if the game supports LAN coop
 - `offlinecoop` (boolean): True if the game supports offline coop
 - `offlinecoopmax` (Integer): Maximum number of offline players in offline coop
 - `offlinemax` (Integer): Maximum number of players in offline multiplayer
 - `onlinecoop` (boolean): True if the game supports online coop
 - `onlinecoopmax` (Integer): Maximum number of online players in online coop
 - `onlinemax` (Integer): Maximum number of players in online multiplayer
 - `platform` (Reference ID for [Platform](https://api-docs.igdb.com/#platform)): The platform this multiplayer mode refers to
 - `splitscreen` (boolean): True if the game supports split screen, offline multiplayer
 - `splitscreenonline` (boolean): True if the game supports split screen, online multiplayer

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Multiplayer Mode endpoint method
    $igdb->multiplayer_mode($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Platform Family
```php
public function platform_family(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Family](https://api-docs.igdb.com/platform-family) endpoint.

**Endpoint Description**: A collection of closely related platforms

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `name` (String): The name of the platform family
 - `slug` (String): A url-safe, unique, lower-case version of the name

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Platform Family endpoint method
    $igdb->platform_family($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Platform Logo
```php
public function platform_logo(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Logo](https://api-docs.igdb.com/platform-logo) endpoint.

**Endpoint Description**: Logo for a platform

**Fields in response**
 - `alpha_channel` (boolean)
 - `animated` (boolean)
 - `checksum` (uuid): Hash of the object
 - `height` (Integer): The height of the image in pixels
 - `image_id` (String): The ID of the image used to construct an IGDB image link
 - `url` (String): The website address (URL) of the item
 - `width` (Integer): The width of the image in pixels

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Platform Logo endpoint method
    $igdb->platform_logo($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Platform Version Company
```php
public function platform_version_company(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Version Company](https://api-docs.igdb.com/platform-version-company) endpoint.

**Endpoint Description**: A platform developer

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `comment` (String): Any notable comments about the developer
 - `company` (Reference ID for [Company](https://api-docs.igdb.com/#company)): The company responsible for developing this platform version
 - `developer` (boolean)
 - `manufacturer` (boolean)

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Platform Version Company endpoint method
    $igdb->platform_version_company($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Platform Version Release Date
```php
public function platform_version_release_date(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Version Release Date](https://api-docs.igdb.com/platform-version-release-date) endpoint.

**Endpoint Description**: A handy endpoint that extends platform release dates. Used to dig deeper into release dates, platforms and versions.

**Fields in response**
 - `category` ([Category Enum](https://api-docs.igdb.com/#platform-version-release-date-enums)): The format of the release date
 - `checksum` (uuid): Hash of the object
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `date` (Unix Time Stamp): The release date
 - `human` (String): A human readable version of the release date
 - `m` (Integer): The month as an integer starting at 1 (January)
 - `platform_version` (Reference ID for [Platform Version](https://api-docs.igdb.com/#platform-version)): The platform this release date is for
 - `region` ([Region Enum](https://api-docs.igdb.com/#platform-version-release-date-enums)): The region of the release
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `y` (Integer): The year in full (2018)

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Platform Version Release Date endpoint method
    $igdb->platform_version_release_date($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Platform Version
```php
public function platform_version(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Version](https://api-docs.igdb.com/platform-version) endpoint.

**Endpoint Description**: &lt;code&gt;https:&#x2F;&#x2F;api.igdb.com&#x2F;v4&#x2F;platform_versions&lt;&#x2F;code&gt;

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `companies` (Reference ID for [ Platform Version Company](https://api-docs.igdb.com/#platform-version-company)): Who developed this platform version
 - `connectivity` (String): The network capabilities
 - `cpu` (String): The integrated control processing unit
 - `graphics` (String): The graphics chipset
 - `main_manufacturer` (Reference ID for [ Platform Version Company](https://api-docs.igdb.com/#platform-version-company)): Who manufactured this version of the platform
 - `media` (String): The type of media this version accepted
 - `memory` (String): How much memory there is
 - `name` (String): The name of the platform version
 - `os` (String): The operating system installed on the platform version
 - `output` (String): The output video rate
 - `platform_logo` (Reference ID for [Platform Logo](https://api-docs.igdb.com/#platform-logo)): The logo of this platform version
 - `platform_version_release_dates` (Array of [Platform Version Release Date](https://api-docs.igdb.com/#platform-version-release-date) IDs): When this platform was released
 - `resolutions` (String): The maximum resolution
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `sound` (String): The sound chipset
 - `storage` (String): How much storage there is
 - `summary` (String): A short summary
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Platform Version endpoint method
    $igdb->platform_version($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Platform Website
```php
public function platform_website(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Website](https://api-docs.igdb.com/platform-website) endpoint.

**Endpoint Description**: The main website for the platform

**Fields in response**
 - `category` ([Category Enum](https://api-docs.igdb.com/#platform-website-enums)): The service this website links to
 - `checksum` (uuid): Hash of the object
 - `trusted` (boolean)
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Platform Website endpoint method
    $igdb->platform_website($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Platform
```php
public function platform(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform](https://api-docs.igdb.com/platform) endpoint.

**Endpoint Description**: The hardware used to run the game or game delivery network

**Fields in response**
 - `abbreviation` (String): An abbreviation of the platform name
 - `alternative_name` (String): An alternative name for the platform
 - `category` ([Category Enum](https://api-docs.igdb.com/#platform-enums)): A physical or virtual category of the platform
 - `checksum` (uuid): Hash of the object
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `generation` (Integer): The generation of the platform
 - `name` (String): The name of the platform
 - `platform_family` (Reference ID for [Platform Family](https://api-docs.igdb.com/#platform-family)): The family of platforms this one belongs to
 - `platform_logo` (Reference ID for [Platform Logo](https://api-docs.igdb.com/#platform-logo)): The logo of the first Version of this platform
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `summary` (String): The summary of the first Version of this platform
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item
 - `versions` (Reference ID for [ Platform Version](https://api-docs.igdb.com/#platform-version)): Associated versions of this platform
 - `websites` (Reference ID for [ Platform Website](https://api-docs.igdb.com/#platform-website)): The main website

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Platform endpoint method
    $igdb->platform($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Player Perspective
```php
public function player_perspective(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Player Perspective](https://api-docs.igdb.com/player-perspective) endpoint.

**Endpoint Description**: Player perspectives describe the view&#x2F;perspective of the player in a video game.

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `name` (String)
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Player Perspective endpoint method
    $igdb->player_perspective($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Release Date
```php
public function release_date(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Release Date](https://api-docs.igdb.com/release-date) endpoint.

**Endpoint Description**: A handy endpoint that extends game release dates. Used to dig deeper into release dates, platforms and versions.

**Fields in response**
 - `category` ([Category Enum](https://api-docs.igdb.com/#release-date-enums)): The format category of the release date
 - `checksum` (uuid): Hash of the object
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `date` (Unix Time Stamp): The date of the release
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game))
 - `human` (String): A human readable representation of the date
 - `m` (Integer): The month as an integer starting at 1 (January)
 - `platform` (Reference ID for [Platform](https://api-docs.igdb.com/#platform)): The platform of the release
 - `region` ([Region Enum](https://api-docs.igdb.com/#release-date-enums)): The region of the release
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `y` (Integer): The year in full (2018)

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Release Date endpoint method
    $igdb->release_date($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Screenshot
```php
public function screenshot(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Screenshot](https://api-docs.igdb.com/screenshot) endpoint.

**Endpoint Description**: Screenshots of games

**Fields in response**
 - `alpha_channel` (boolean)
 - `animated` (boolean)
 - `checksum` (uuid): Hash of the object
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game)): The game this video is associated with
 - `height` (Integer): The height of the image in pixels
 - `image_id` (String): The ID of the image used to construct an IGDB image link
 - `url` (String): The website address (URL) of the item
 - `width` (Integer): The width of the image in pixels

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Screenshot endpoint method
    $igdb->screenshot($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Search
```php
public function search(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Search](https://api-docs.igdb.com/search) endpoint.

**Endpoint Description**: &lt;code&gt;https:&#x2F;&#x2F;api.igdb.com&#x2F;v4&#x2F;search&lt;&#x2F;code&gt;

**Fields in response**
 - `alternative_name` (String)
 - `character` (Reference ID for [Character](https://api-docs.igdb.com/#character))
 - `checksum` (uuid): Hash of the object
 - `collection` (Reference ID for [Collection](https://api-docs.igdb.com/#collection))
 - `company` (Reference ID for [Company](https://api-docs.igdb.com/#company))
 - `description` (String)
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game))
 - `name` (String)
 - `platform` (Reference ID for [Platform](https://api-docs.igdb.com/#platform))
 - `published_at` (Unix Time Stamp): The date this item was initially published by the third party
 - `test_dummy` (Reference ID for [Test Dummy](https://api-docs.igdb.com/#test-dummy))
 - `theme` (Reference ID for [Theme](https://api-docs.igdb.com/#theme))

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Search endpoint method
    $igdb->search($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Theme
```php
public function theme(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Theme](https://api-docs.igdb.com/theme) endpoint.

**Endpoint Description**: Video game themes

**Fields in response**
 - `checksum` (uuid): Hash of the object
 - `created_at` (Unix Time Stamp): Date this was initially added to the IGDB database
 - `name` (String)
 - `slug` (String): A url-safe, unique, lower-case version of the name
 - `updated_at` (Unix Time Stamp): The last date this entry was updated in the IGDB database
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Theme endpoint method
    $igdb->theme($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

### Website
```php
public function website(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Website](https://api-docs.igdb.com/website) endpoint.

**Endpoint Description**: A website url, usually associated with a game

**Fields in response**
 - `category` ([Category Enum](https://api-docs.igdb.com/#website-enums)): The service this website links to
 - `checksum` (uuid): Hash of the object
 - `game` (Reference ID for [Game](https://api-docs.igdb.com/#game)): The game this website is associated with
 - `trusted` (boolean)
 - `url` (String): The website address (URL) of the item

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depending on the second `$count` parameter.

```php
<?php

    // Website endpoint method
    $igdb->website($query, $count);

?>
```

> For more information on return values, refer to the [Return Values](#return-values) section!

## MultiQuery
```php
public function multiquery(string $endpoint, string $result_name, string $query = null) : mixed
```

Multi-Query is a new way to request a huge amount of information in one request! With Multi-Query you can request multiple endpoints at once, it also works with multiple requests to a single endpoint as well.

**Parameters**
 - `$endpoint`: the endpoint to use (note: not the wrapper method name, but the IGDB endpoint name. Also accepts count)
 - `$result_name`:  name, given by you.
 - `$query`: an apicalypse query string. The default value is null, in this case no filter will be applied.

> To build a query you can use the [IGDB Query Builder](#igdb-query-builder)!

Returns either the records matching the filter criteria, or the count of these records. This depends on the `$endpoint` parameter.

Example query:
```php
/*
  A few things to note here:
    - the endpoint name has to be the IGDB endpoint name, not the
      wrapper class method name (platforms instead of the wrapper method name platform)
    - there is a /count after the endpoint name which tells the api
      to return the record count instead of the actual records
    - the third parameter is missing, which has a default value NULL
      and the request will be sent without any filter parameters
*/

$IGDB->mutliquery("platforms/count", "Count of Platforms");
```

Result of the query:
```text
array (size=1)
  0 =>
    object(stdClass)[2]
      public 'name' => string 'Count of Platforms' (length=18)
      public 'count' => int 169
```

Example query with filters:
```php
$IGDB->mutliquery(
  "games",                                                                        // endpoint
  "Playstation Games",                                                            // result name
  "fields name,platforms.name; where platforms !=n & platforms = {48}; limit 1;"  // apicalypse query string
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

> For more details on MultiQuery refer to the [IGDB Multi-Query Documentation](https://api-docs.igdb.com/#multi-query)!

## Return Values

Every endpoint method can return two different type of results, depending on the second parameter passed to them:
 - By default the second `$count` parameter is a boolean `false`. this means, that the query will be executed against the IGDB, returning a `$result` **array of objects**.
    ```php
    <?php

        // a query against the game endpoint without a $count parameter
        $igdb->game("fields id,name; where id = (1,2);");

    ?>
    ```

    The result of the query above:

    ```text
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

 - If you pass a boolean `true` as a second parameter, then you will get an object with a `count` property containing the item count from the selected endpoint filtered by the `$query` filters.
    ```php
    <?php
        // a query against the game endpoint with a second true parameter
        // note the second boolean true parameter
        $igdb->game( "fields id,name; where id = (1,2);", true);

    ?>
    ```

    The result of the query above:
    ```text
    object(stdClass)[3]
      public 'count' => int 2
    ```

The result object's properties will vary depending on the provided field list in the `$query`. From the example result above you can see, the result holds an array, containing two elements. Every element of the result array is an object, containing properties with name of the fields from the `fields` parameter.