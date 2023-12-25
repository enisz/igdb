---
overview: The main wrapper. Methods, endpoints, properties, configurations. Sending your queries to the IGDB API.
icon: fa-gift
---

# IGDB Wrapper

This is the most important part of the wrapper, the `IGDB` class which does the heavy lifting: communicating with the IGDB API.

As mentioned in the [Introduction](#getting-started), to have access to IGDB's database you have to register a Twitch Account and have your own `client_id` and `access_token`.

>:success You can add your tokens to the documentation to replace them in the exmple codes. Click the logo in the top left corner to get back to the main page and save your tokens.

## Instantiating the wrapper

After importing the dependencies you can instantiate the class with the `new` keyword by passing your tokens to the constructor.

```php
<?php

    require_once "class.igdb.php";

    $igdb = new IGDB("{client_id}", "{access_token}");

?>
```

>:info The wrapper itself does not validate your tokens. If your credentials are invalid, you will get an error from the IGDB API after executing a query.

## Public Methods

These methods are exposed from the `IGDB` object.

### Get Request Info
```php
public function get_request_info()
```

After a query is executed, the latest request information will be stored and can be retrieved using this method.

> The new version of the IGDB API (v4) will return a http response code `429` when you exceed the limit of requests on the database (4 requests per second at the time of writing this documentation).

```php
<?php

    require_once "class.igdb.php";

    $igdb = new IGDB("{client_id}", "{access_token}");

    try {
        $igdb->game('fields id,name; search "uncharted 4"; limit 1;');

        // getting the details of the latest query
        $request_info = $igdb->get_request_info();

        // showing the details
        var_dump($request_info);
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
public function construct_url(string $endpoint, boolean $count = false) throws IGDBInvalidParameterException: string
```

The method will construct the full URL for the request and will return the constructed URL as a string. If an invalid endpoint name passed to `$endpoint` an `IGDBInvalidParameterException` will be thrown.

**Parameters**:
 - `$endpoint`: the endpoint to use (the name of the endpoint, not the path to it!). Possible values:
   - age_rating
   - age_rating_content_description
   - alternative_name
   - artwork
   - character
   - character_mug_shot
   - collection
   - collection_membership
   - collection_membership_type
   - collection_relation
   - collection_relation_type
   - collection_type
   - company
   - company_logo
   - company_website
   - cover
   - event
   - event_logo
   - event_network
   - external_game
   - franchise
   - game
   - game_engine
   - game_engine_logo
   - game_localization
   - game_mode
   - game_version
   - game_version_feature
   - game_version_feature_value
   - game_video
   - genre
   - involved_company
   - keyword
   - language
   - language_support
   - multiplayer_mode
   - platform
   - language_support_type
   - platform_family
   - network_type
   - platform_logo
   - platform_version_company
   - platform_version
   - platform_website
   - platform_version_release_date
   - player_perspective
   - region
   - release_date
   - release_date_status
   - screenshot
   - search
   - theme
   - website
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: the full constructed URL to the IGDB Endpoint as a string.

```php
<?php

    // instantiating the wrapper
    $igdb = new IGDB("{client_id}", "{access_token}");

    try {
        // constructing an url
        $url = $igdb->construct_url("game");

        // constructing an url to get counts
        $count_url = $igdb->construct_url("game", true);

        // showing the results
        echo "url: " . $url;
        echo "count url: " . $count_url;
    } catch (IGDBInvalidParameterException $e) {
        // an invalid parameter passed to the construct_url method
        echo $e->getMessage();
    }

?>
```

Output:

```text
url: https://api.igdb.com/v4/games
count url: https://api.igdb.com/v4/games/count
```

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

## Handling Request Errors

Your query may fail on the IGDB side. In this case the API will send back a non-successful response code indicating that something went wrong. When this happens an `IGDBEndpointException` can be caught to extract information about the issue. To catch these errors you have to enclose your [endpoint](#endpoints) method calls in a try...catch block.

```php
<?php

    $igdb = new IGDB("{client_id}", "{access_token}");

    // your query string with a field that doesn't exist
    $query = 'search "uncharted"; fields nonexistingfield;';

    try {
        // executing the query
        $games = $igdb->game($query);
    } catch (IGDBEndpointException $e) {
        // since the query contains a non-existing field, an error occured
        // printing the response code and the error message
        echo "Response code: " . $e->getResponseCode();
        echo "Message: " . $e->getMessage();
    }

?>
```

Since the query above is not valid, as there is no field called `nonexistingfield` on the game endpoint, the API will send a response with an error message and a non-successful response code. The result of the script above is:

```text
Response code: 400
Message: Invalid Field
```

You can also get some additional information about this request using the [`get_request_info()`](#get-request-info) method.

## Endpoints

Every endpoint method is named after the IGDB API endpoints using snake-casing naming convention. These methods are expecting at least one parameter, the `$query` itself. The second `$count` parameter is optional, it is `false` by default.

**Parameters**:
 - `$query`: the query itself as an apicalypse string
 - `$count`: a `boolean` value to whether return the records or the count of the records

>:success To build your queries, give [IGDB Query Builder](#igdb-query-builder) a try!

These methods will return **an array of objects** decoded from IGDB response JSON when the `$count` parameter is false. Otherwise, it will execute a count query against the selected endpoint which will return an object with a `count` property holding the sum of the found items. The count queries can be filtered with [where](#where) parameters.

`IGDBEndpointException` is thrown if a non-successful response code is recieved from the IGDB API. To find out how to handle request errors, head to the [Handle Request Errors](#handling-request-errors) section.

Please refer to the [return values section](#return-values) for more details about the return values of these methods.

For the endpoint specific fields that the API returns please refer to the IGDB documentation's respective paragraph. Each endpoint has a direct link!

### Age Rating
```php
public function age_rating(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Age Rating](https://api-docs.igdb.com/#age-rating) endpoint.

**Endpoint Description**: Age Rating according to various rating organisations

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Age Rating endpoint method
    $igdb->age_rating($query, $count);

?>
```

### Age Rating Content Description
```php
public function age_rating_content_description(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Age Rating Content Description](https://api-docs.igdb.com/#age-rating-content-description) endpoint.

**Endpoint Description**: Age Rating Descriptors

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Age Rating Content Description endpoint method
    $igdb->age_rating_content_description($query, $count);

?>
```

### Alternative Name
```php
public function alternative_name(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Alternative Name](https://api-docs.igdb.com/#alternative-name) endpoint.

**Endpoint Description**: Alternative and international game titles

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Alternative Name endpoint method
    $igdb->alternative_name($query, $count);

?>
```

### Artwork
```php
public function artwork(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Artwork](https://api-docs.igdb.com/#artwork) endpoint.

**Endpoint Description**: official artworks (resolution and aspect ratio may vary)

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Artwork endpoint method
    $igdb->artwork($query, $count);

?>
```

### Character
```php
public function character(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Character](https://api-docs.igdb.com/#character) endpoint.

**Endpoint Description**: Video game characters

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Character endpoint method
    $igdb->character($query, $count);

?>
```

### Character Mug Shot
```php
public function character_mug_shot(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Character Mug Shot](https://api-docs.igdb.com/#character-mug-shot) endpoint.

**Endpoint Description**: Images depicting game characters

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Character Mug Shot endpoint method
    $igdb->character_mug_shot($query, $count);

?>
```

### Collection
```php
public function collection(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Collection](https://api-docs.igdb.com/#collection) endpoint.

**Endpoint Description**: Collection, AKA Series

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Collection endpoint method
    $igdb->collection($query, $count);

?>
```

### Collection Membership
```php
public function collection_membership(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Collection Membership](https://api-docs.igdb.com/#collection-membership) endpoint.

**Endpoint Description**: The Collection Memberships.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Collection Membership endpoint method
    $igdb->collection_membership($query, $count);

?>
```

### Collection Membership Type
```php
public function collection_membership_type(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Collection Membership Type](https://api-docs.igdb.com/#collection-membership-type) endpoint.

**Endpoint Description**: Enums for collection membership types.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Collection Membership Type endpoint method
    $igdb->collection_membership_type($query, $count);

?>
```

### Collection Relation
```php
public function collection_relation(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Collection Relation](https://api-docs.igdb.com/#collection-relation) endpoint.

**Endpoint Description**: Describes Relationship between Collections.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Collection Relation endpoint method
    $igdb->collection_relation($query, $count);

?>
```

### Collection Relation Type
```php
public function collection_relation_type(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Collection Relation Type](https://api-docs.igdb.com/#collection-relation-type) endpoint.

**Endpoint Description**: Collection Relation Types

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Collection Relation Type endpoint method
    $igdb->collection_relation_type($query, $count);

?>
```

### Collection Type
```php
public function collection_type(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Collection Type](https://api-docs.igdb.com/#collection-type) endpoint.

**Endpoint Description**: Enums for collection types.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Collection Type endpoint method
    $igdb->collection_type($query, $count);

?>
```

### Company
```php
public function company(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Company](https://api-docs.igdb.com/#company) endpoint.

**Endpoint Description**: Video game companies. Both publishers &amp; developers

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Company endpoint method
    $igdb->company($query, $count);

?>
```

### Company Logo
```php
public function company_logo(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Company Logo](https://api-docs.igdb.com/#company-logo) endpoint.

**Endpoint Description**: The logos of developers and publishers

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Company Logo endpoint method
    $igdb->company_logo($query, $count);

?>
```

### Company Website
```php
public function company_website(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Company Website](https://api-docs.igdb.com/#company-website) endpoint.

**Endpoint Description**: Company Website

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Company Website endpoint method
    $igdb->company_website($query, $count);

?>
```

### Cover
```php
public function cover(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Cover](https://api-docs.igdb.com/#cover) endpoint.

**Endpoint Description**: The cover art of games

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Cover endpoint method
    $igdb->cover($query, $count);

?>
```

### Event
```php
public function event(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Event](https://api-docs.igdb.com/#event) endpoint.

**Endpoint Description**: Gaming event like GamesCom, Tokyo Game Show, PAX or GSL

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Event endpoint method
    $igdb->event($query, $count);

?>
```

### Event Logo
```php
public function event_logo(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Event Logo](https://api-docs.igdb.com/#event-logo) endpoint.

**Endpoint Description**: Logo for the event

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Event Logo endpoint method
    $igdb->event_logo($query, $count);

?>
```

### Event Network
```php
public function event_network(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Event Network](https://api-docs.igdb.com/#event-network) endpoint.

**Endpoint Description**: Urls related to the event like twitter, facebook and youtube

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Event Network endpoint method
    $igdb->event_network($query, $count);

?>
```

### External Game
```php
public function external_game(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [External Game](https://api-docs.igdb.com/#external-game) endpoint.

**Endpoint Description**: Game IDs on other services

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // External Game endpoint method
    $igdb->external_game($query, $count);

?>
```

### Franchise
```php
public function franchise(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Franchise](https://api-docs.igdb.com/#franchise) endpoint.

**Endpoint Description**: A list of video game franchises such as Star Wars.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Franchise endpoint method
    $igdb->franchise($query, $count);

?>
```

### Game
```php
public function game(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game](https://api-docs.igdb.com/#game) endpoint.

**Endpoint Description**: Video Games!

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Game endpoint method
    $igdb->game($query, $count);

?>
```

### Game Engine
```php
public function game_engine(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Engine](https://api-docs.igdb.com/#game-engine) endpoint.

**Endpoint Description**: Video game engines such as unreal engine.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Game Engine endpoint method
    $igdb->game_engine($query, $count);

?>
```

### Game Engine Logo
```php
public function game_engine_logo(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Engine Logo](https://api-docs.igdb.com/#game-engine-logo) endpoint.

**Endpoint Description**: The logos of game engines

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Game Engine Logo endpoint method
    $igdb->game_engine_logo($query, $count);

?>
```

### Game Localization
```php
public function game_localization(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Localization](https://api-docs.igdb.com/#game-localization) endpoint.

**Endpoint Description**: Game localization for a game

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Game Localization endpoint method
    $igdb->game_localization($query, $count);

?>
```

### Game Mode
```php
public function game_mode(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Mode](https://api-docs.igdb.com/#game-mode) endpoint.

**Endpoint Description**: Single player, Multiplayer etc

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Game Mode endpoint method
    $igdb->game_mode($query, $count);

?>
```

### Game Version
```php
public function game_version(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Version](https://api-docs.igdb.com/#game-version) endpoint.

**Endpoint Description**: Details about game editions and versions.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Game Version endpoint method
    $igdb->game_version($query, $count);

?>
```

### Game Version Feature
```php
public function game_version_feature(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Version Feature](https://api-docs.igdb.com/#game-version-feature) endpoint.

**Endpoint Description**: Features and descriptions of what makes each version&#x2F;edition different from the main game

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Game Version Feature endpoint method
    $igdb->game_version_feature($query, $count);

?>
```

### Game Version Feature Value
```php
public function game_version_feature_value(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Version Feature Value](https://api-docs.igdb.com/#game-version-feature-value) endpoint.

**Endpoint Description**: The bool&#x2F;text value of the feature

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Game Version Feature Value endpoint method
    $igdb->game_version_feature_value($query, $count);

?>
```

### Game Video
```php
public function game_video(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Game Video](https://api-docs.igdb.com/#game-video) endpoint.

**Endpoint Description**: A video associated with a game

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Game Video endpoint method
    $igdb->game_video($query, $count);

?>
```

### Genre
```php
public function genre(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Genre](https://api-docs.igdb.com/#genre) endpoint.

**Endpoint Description**: Genres of video game

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Genre endpoint method
    $igdb->genre($query, $count);

?>
```

### Involved Company
```php
public function involved_company(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Involved Company](https://api-docs.igdb.com/#involved-company) endpoint.

**Endpoint Description**:

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Involved Company endpoint method
    $igdb->involved_company($query, $count);

?>
```

### Keyword
```php
public function keyword(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Keyword](https://api-docs.igdb.com/#keyword) endpoint.

**Endpoint Description**: Keywords are words or phrases that get tagged to a game such as &quot;world war 2&quot; or &quot;steampunk&quot;.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Keyword endpoint method
    $igdb->keyword($query, $count);

?>
```

### Language
```php
public function language(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Language](https://api-docs.igdb.com/#language) endpoint.

**Endpoint Description**: Languages that are used in the Language Support endpoint.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Language endpoint method
    $igdb->language($query, $count);

?>
```

### Language Support
```php
public function language_support(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Language Support](https://api-docs.igdb.com/#language-support) endpoint.

**Endpoint Description**: Games can be played with different languages for voice acting, subtitles, or the interface language.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Language Support endpoint method
    $igdb->language_support($query, $count);

?>
```

### Multiplayer Mode
```php
public function multiplayer_mode(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Multiplayer Mode](https://api-docs.igdb.com/#multiplayer-mode) endpoint.

**Endpoint Description**: Data about the supported multiplayer types

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Multiplayer Mode endpoint method
    $igdb->multiplayer_mode($query, $count);

?>
```

### Platform
```php
public function platform(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform](https://api-docs.igdb.com/#platform) endpoint.

**Endpoint Description**: The hardware used to run the game or game delivery network

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Platform endpoint method
    $igdb->platform($query, $count);

?>
```

### Language Support Type
```php
public function language_support_type(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Language Support Type](https://api-docs.igdb.com/#language-support-type) endpoint.

**Endpoint Description**: Language Support Types contains the identifiers for the support types that Language Support uses.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Language Support Type endpoint method
    $igdb->language_support_type($query, $count);

?>
```

### Platform Family
```php
public function platform_family(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Family](https://api-docs.igdb.com/#platform-family) endpoint.

**Endpoint Description**: A collection of closely related platforms

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Platform Family endpoint method
    $igdb->platform_family($query, $count);

?>
```

### Network Type
```php
public function network_type(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Network Type](https://api-docs.igdb.com/#network-type) endpoint.

**Endpoint Description**: Social networks related to the event like twitter, facebook and youtube

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Network Type endpoint method
    $igdb->network_type($query, $count);

?>
```

### Platform Logo
```php
public function platform_logo(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Logo](https://api-docs.igdb.com/#platform-logo) endpoint.

**Endpoint Description**: Logo for a platform

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Platform Logo endpoint method
    $igdb->platform_logo($query, $count);

?>
```

### Platform Version Company
```php
public function platform_version_company(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Version Company](https://api-docs.igdb.com/#platform-version-company) endpoint.

**Endpoint Description**: A platform developer

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Platform Version Company endpoint method
    $igdb->platform_version_company($query, $count);

?>
```

### Platform Version
```php
public function platform_version(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Version](https://api-docs.igdb.com/#platform-version) endpoint.

**Endpoint Description**:

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Platform Version endpoint method
    $igdb->platform_version($query, $count);

?>
```

### Platform Website
```php
public function platform_website(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Website](https://api-docs.igdb.com/#platform-website) endpoint.

**Endpoint Description**: The main website for the platform

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Platform Website endpoint method
    $igdb->platform_website($query, $count);

?>
```

### Platform Version Release Date
```php
public function platform_version_release_date(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Platform Version Release Date](https://api-docs.igdb.com/#platform-version-release-date) endpoint.

**Endpoint Description**: A handy endpoint that extends platform release dates. Used to dig deeper into release dates, platforms and versions.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Platform Version Release Date endpoint method
    $igdb->platform_version_release_date($query, $count);

?>
```

### Player Perspective
```php
public function player_perspective(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Player Perspective](https://api-docs.igdb.com/#player-perspective) endpoint.

**Endpoint Description**: Player perspectives describe the view&#x2F;perspective of the player in a video game.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Player Perspective endpoint method
    $igdb->player_perspective($query, $count);

?>
```

### Region
```php
public function region(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Region](https://api-docs.igdb.com/#region) endpoint.

**Endpoint Description**: Region for game localization

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Region endpoint method
    $igdb->region($query, $count);

?>
```

### Release Date
```php
public function release_date(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Release Date](https://api-docs.igdb.com/#release-date) endpoint.

**Endpoint Description**: A handy endpoint that extends game release dates. Used to dig deeper into release dates, platforms and versions.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Release Date endpoint method
    $igdb->release_date($query, $count);

?>
```

### Release Date Status
```php
public function release_date_status(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Release Date Status](https://api-docs.igdb.com/#release-date-status) endpoint.

**Endpoint Description**: An endpoint to provide definition of all of the current release date statuses.

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Release Date Status endpoint method
    $igdb->release_date_status($query, $count);

?>
```

### Screenshot
```php
public function screenshot(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Screenshot](https://api-docs.igdb.com/#screenshot) endpoint.

**Endpoint Description**: Screenshots of games

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Screenshot endpoint method
    $igdb->screenshot($query, $count);

?>
```

### Search
```php
public function search(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Search](https://api-docs.igdb.com/#search) endpoint.

**Endpoint Description**:

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Search endpoint method
    $igdb->search($query, $count);

?>
```

### Theme
```php
public function theme(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Theme](https://api-docs.igdb.com/#theme) endpoint.

**Endpoint Description**: Video game themes

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Theme endpoint method
    $igdb->theme($query, $count);

?>
```

### Website
```php
public function website(string $query, boolean $count = false) throws IGDBEndpointException: mixed
```

Fetching data from IGDB API using the [Website](https://api-docs.igdb.com/#website) endpoint.

**Endpoint Description**: A website url, usually associated with a game

**Parameters**:
 - `$query`: an apicalypse formatted query String
 - `$count`: whether the request should return the number of matches instead of the actual resultset

**Returns**: either the resultset as an array of objects, or a single object with a count property. Depends on the second `$count` parameter.

```php
<?php

    // Website endpoint method
    $igdb->website($query, $count);

?>
```

## MultiQuery
```php
public function multiquery(array $queries) throws IGDBEndpointException, IGDBInvalidParameterException: mixed
```

This method executes a query against the `multiquery` endpoint. With this functionality one is able to execute multiple queries in a single request.

> :warning If you are using the [Query Builder](#igdb-query-builder) to construct your queries, the parameters [`name`](#name) and [`endpoint`](#endpoint) are **mandatory**! There is also a third optional parameter [`count`](#count). If any of the mandatory parameters are missing for the multiquery, an `IGDBInvalidParameterException` is thrown! Please refer to the [`build`](#building-the-query) method for more information!

**Parameters**
 - `$queries`: an array of apicalypse formatted multiquery query strings.

**Returns**: the response from the IGDB endpoint. The result object can vary depending on your query.

```php
<?php

    // importing the wrapper
    require_once "class.igdb.php";

    // instantiate the wrapper
    $igdb = new IGDB("{client_id}", "{access_token}");

    // query builder for the main game
    $main_builder = new IGDBQueryBuilder();

    // query builder for the bundles
    $bundle_builder = new IGDBQueryBuilder();

    try {
        // building the main game query
        $main = $main_builder
            ->name("Main Game")
            ->endpoint("game")
            ->fields("id,name")
            ->where("id = 25076")
            ->build(true);

        // building the bundle query
        $bundle = $bundle_builder
            ->name("Bundles")
            ->endpoint("game")
            ->fields("id,name,version_parent,category")
            ->where("version_parent = 25076")
            ->where("category = 0")
            ->build(true);

        // the query can be passed as a string too

        // $main = "query games \"Main Game\" {
        //     fields id,name;
        //     where id = 25076;
        // };";

        // $bundle = "query games \"Bundles\" {
        //     fields id,name,version_parent,category;
        //     where version_parent = 25076 & category = 0;
        // };";

        // passing the queries to the multiquery method as an array of strings
        var_dump(
            $igdb->multiquery(
                array($main, $bundle)
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
The result of the query:

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
              public 'id' => int 103207
              public 'category' => int 0
              public 'name' => string 'Red Dead Redemption 2: Collector's Box' (length=38)
              public 'version_parent' => int 25076
          1 =>
            object(stdClass)[8]
              public 'id' => int 103206
              public 'category' => int 0
              public 'name' => string 'Red dead Redemption 2: Ultimate Edition' (length=39)
              public 'version_parent' => int 25076
          2 =>
            object(stdClass)[9]
              public 'id' => int 103205
              public 'category' => int 0
              public 'name' => string 'Red Dead Redemption 2: Special Edition' (length=38)
              public 'version_parent' => int 25076
```

## Return Values

Every endpoint method can return two different type of results, depending on the second parameter passed to them.

By default the second `$count` parameter is a boolean `false`. this means, that the query will be executed against the IGDB, returning a `$result` **array of objects**.

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

If you pass a boolean `true` as a second parameter, then you will get an object with a `count` property containing the item count from the selected endpoint filtered by the `$query` filters.

```php
<?php
    // a query against the game endpoint with a second true parameter
    // note the second boolean true parameter
    $igdb->game("fields id,name; where id = (1,2);", true);

?>
```

The result of the query above:
```text
object(stdClass)[3]
    public 'count' => int 2
```

The result object's properties will vary depending on the provided field list in the `$query`. From the example result above you can see, the result holds an array, containing two elements. Every element of the result array is an object, containing properties with name of the fields from the `fields` parameter.
