---
overview: The main wrapper. Methods, endpoints, properties, configurations. Sending your queries to the IGDB API.
icon: fa-gift
---

# The Wrapper

This is the most important part of the wrapper, the `IGDB` class which does the heavy lifting: communicating with the IGDB API.

As mentioned in the [Introduction](#getting-started), to have access to IGDB's database you have to register a Twitch Account and have your own `client_id` and `access_token`.

> You can add your tokens to the documentation to replace them in the exmple codes. Click the logo in the top left corner to get back to the main page and save your tokens.

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

### Example {.tabset}
#### Source
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

#### Result

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

### {-}

Details of the query can be fetched from the output of the `get_request_info()` method (for example, the HTTP Response code from `http_code`).

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
   - language_support
   - language_support_type
   - language
   - multiplayer_mode
   - network_type
   - platform
   - platform_family
   - platform_logo
   - platform_version
   - platform_version_company
   - platform_version_release_date
   - platform_website
   - player_perspective
   - region
   - release_date
   - release_date_status
   - screenshot
   - search
   - theme
   - website
 - `$count`: if the count endpoint is required or the simple endpoint

**Returns**: the full constructed URL to the IGDB Endpoint as a string.

### Example {.tabset}
#### Source
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

#### Result

```text
url: https://api.igdb.com/v4/games
count url: https://api.igdb.com/v4/games/count
```

### {-}

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

## Example {.tabset}
### Source
```php
<?php

    $igdb = new IGDB("{client_id}", "{access_token}");

    // your query string with a field that doesn't exist
    $query = 'search "uncharted"; fields nonexistingfield;';

    try {
        // executing the query
        $games = $igdb->game($query);
    } catch (IGDBEndpointException $e) {
        // since the query contains a non-existing field, an error occurs
        // printing the response code and the error message
        echo "Response code: " . $e->getResponseCode();
        echo "Message: " . $e->getMessage();
    }

?>
```

### Result

```text
Response code: 400
Message: Invalid Field
```

## {-}

Since the query above is not valid, as there is no field called `nonexistingfield` on the game endpoint, the API will send a response with an error message and a non-successful response code.

You can also get some additional information about this request using the [`get_request_info()`](#get-request-info) method.

## Endpoints

Every endpoint method is named after the IGDB API endpoints using snake-casing naming convention. Also, each endpoint method has a `count` counterpart which handles counting the matched entities.

These methods will return **an array of objects** decoded from IGDB response. Please refer to the [return values section](#return-values) for more details about the return values of these methods.

`IGDBEndpointException` is thrown if a non-successful response code is recieved from the IGDB API. To find out how to handle request errors, head to the [Handle Request Errors](#handling-request-errors) section.

>:success To build your queries, give [IGDB Query Builder](#igdb-query-builder) a try!

For the endpoint specific fields that the API returns please refer to the IGDB documentation's respective paragraph. Each endpoint has a direct link!

### Age Rating
```php
public function age_rating(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Age Rating](https://api-docs.igdb.com/#age-rating) endpoint.

**Endpoint Description**: Age Rating according to various rating organisations
**Endpoint URL**: `https://api.igdb.com/v4/age-rating`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Age Rating endpoint method
    $igdb->age_rating($query);

?>
```

### Age Rating Count
```php
public function age_rating_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Age Rating](https://api-docs.igdb.com/#age-rating) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Age Rating according to various rating organisations

**Endpoint URL**: `https://api.igdb.com/v4/age-rating/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->age_rating_count($query);

    // all record count
    $igdb->age_rating_count();

?>
```

### Age Rating Content Description
```php
public function age_rating_content_description(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Age Rating Content Description](https://api-docs.igdb.com/#age-rating-content-description) endpoint.

**Endpoint Description**: Age Rating Descriptors
**Endpoint URL**: `https://api.igdb.com/v4/age-rating-content-description`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Age Rating Content Description endpoint method
    $igdb->age_rating_content_description($query);

?>
```

### Age Rating Content Description Count
```php
public function age_rating_content_description_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Age Rating Content Description](https://api-docs.igdb.com/#age-rating-content-description) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Age Rating Descriptors

**Endpoint URL**: `https://api.igdb.com/v4/age-rating-content-description/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->age_rating_content_description_count($query);

    // all record count
    $igdb->age_rating_content_description_count();

?>
```

### Alternative Name
```php
public function alternative_name(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Alternative Name](https://api-docs.igdb.com/#alternative-name) endpoint.

**Endpoint Description**: Alternative and international game titles
**Endpoint URL**: `https://api.igdb.com/v4/alternative-name`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Alternative Name endpoint method
    $igdb->alternative_name($query);

?>
```

### Alternative Name Count
```php
public function alternative_name_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Alternative Name](https://api-docs.igdb.com/#alternative-name) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Alternative and international game titles

**Endpoint URL**: `https://api.igdb.com/v4/alternative-name/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->alternative_name_count($query);

    // all record count
    $igdb->alternative_name_count();

?>
```

### Artwork
```php
public function artwork(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Artwork](https://api-docs.igdb.com/#artwork) endpoint.

**Endpoint Description**: official artworks (resolution and aspect ratio may vary)
**Endpoint URL**: `https://api.igdb.com/v4/artwork`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Artwork endpoint method
    $igdb->artwork($query);

?>
```

### Artwork Count
```php
public function artwork_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Artwork](https://api-docs.igdb.com/#artwork) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: official artworks (resolution and aspect ratio may vary)

**Endpoint URL**: `https://api.igdb.com/v4/artwork/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->artwork_count($query);

    // all record count
    $igdb->artwork_count();

?>
```

### Character
```php
public function character(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Character](https://api-docs.igdb.com/#character) endpoint.

**Endpoint Description**: Video game characters
**Endpoint URL**: `https://api.igdb.com/v4/character`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Character endpoint method
    $igdb->character($query);

?>
```

### Character Count
```php
public function character_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Character](https://api-docs.igdb.com/#character) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Video game characters

**Endpoint URL**: `https://api.igdb.com/v4/character/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->character_count($query);

    // all record count
    $igdb->character_count();

?>
```

### Character Mug Shot
```php
public function character_mug_shot(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Character Mug Shot](https://api-docs.igdb.com/#character-mug-shot) endpoint.

**Endpoint Description**: Images depicting game characters
**Endpoint URL**: `https://api.igdb.com/v4/character-mug-shot`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Character Mug Shot endpoint method
    $igdb->character_mug_shot($query);

?>
```

### Character Mug Shot Count
```php
public function character_mug_shot_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Character Mug Shot](https://api-docs.igdb.com/#character-mug-shot) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Images depicting game characters

**Endpoint URL**: `https://api.igdb.com/v4/character-mug-shot/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->character_mug_shot_count($query);

    // all record count
    $igdb->character_mug_shot_count();

?>
```

### Collection
```php
public function collection(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Collection](https://api-docs.igdb.com/#collection) endpoint.

**Endpoint Description**: Collection, AKA Series
**Endpoint URL**: `https://api.igdb.com/v4/collection`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Collection endpoint method
    $igdb->collection($query);

?>
```

### Collection Count
```php
public function collection_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Collection](https://api-docs.igdb.com/#collection) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Collection, AKA Series

**Endpoint URL**: `https://api.igdb.com/v4/collection/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->collection_count($query);

    // all record count
    $igdb->collection_count();

?>
```

### Collection Membership
```php
public function collection_membership(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Collection Membership](https://api-docs.igdb.com/#collection-membership) endpoint.

**Endpoint Description**: The Collection Memberships.
**Endpoint URL**: `https://api.igdb.com/v4/collection-membership`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Collection Membership endpoint method
    $igdb->collection_membership($query);

?>
```

### Collection Membership Count
```php
public function collection_membership_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Collection Membership](https://api-docs.igdb.com/#collection-membership) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: The Collection Memberships.

**Endpoint URL**: `https://api.igdb.com/v4/collection-membership/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->collection_membership_count($query);

    // all record count
    $igdb->collection_membership_count();

?>
```

### Collection Membership Type
```php
public function collection_membership_type(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Collection Membership Type](https://api-docs.igdb.com/#collection-membership-type) endpoint.

**Endpoint Description**: Enums for collection membership types.
**Endpoint URL**: `https://api.igdb.com/v4/collection-membership-type`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Collection Membership Type endpoint method
    $igdb->collection_membership_type($query);

?>
```

### Collection Membership Type Count
```php
public function collection_membership_type_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Collection Membership Type](https://api-docs.igdb.com/#collection-membership-type) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Enums for collection membership types.

**Endpoint URL**: `https://api.igdb.com/v4/collection-membership-type/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->collection_membership_type_count($query);

    // all record count
    $igdb->collection_membership_type_count();

?>
```

### Collection Relation
```php
public function collection_relation(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Collection Relation](https://api-docs.igdb.com/#collection-relation) endpoint.

**Endpoint Description**: Describes Relationship between Collections.
**Endpoint URL**: `https://api.igdb.com/v4/collection-relation`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Collection Relation endpoint method
    $igdb->collection_relation($query);

?>
```

### Collection Relation Count
```php
public function collection_relation_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Collection Relation](https://api-docs.igdb.com/#collection-relation) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Describes Relationship between Collections.

**Endpoint URL**: `https://api.igdb.com/v4/collection-relation/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->collection_relation_count($query);

    // all record count
    $igdb->collection_relation_count();

?>
```

### Collection Relation Type
```php
public function collection_relation_type(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Collection Relation Type](https://api-docs.igdb.com/#collection-relation-type) endpoint.

**Endpoint Description**: Collection Relation Types
**Endpoint URL**: `https://api.igdb.com/v4/collection-relation-type`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Collection Relation Type endpoint method
    $igdb->collection_relation_type($query);

?>
```

### Collection Relation Type Count
```php
public function collection_relation_type_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Collection Relation Type](https://api-docs.igdb.com/#collection-relation-type) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Collection Relation Types

**Endpoint URL**: `https://api.igdb.com/v4/collection-relation-type/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->collection_relation_type_count($query);

    // all record count
    $igdb->collection_relation_type_count();

?>
```

### Collection Type
```php
public function collection_type(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Collection Type](https://api-docs.igdb.com/#collection-type) endpoint.

**Endpoint Description**: Enums for collection types.
**Endpoint URL**: `https://api.igdb.com/v4/collection-type`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Collection Type endpoint method
    $igdb->collection_type($query);

?>
```

### Collection Type Count
```php
public function collection_type_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Collection Type](https://api-docs.igdb.com/#collection-type) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Enums for collection types.

**Endpoint URL**: `https://api.igdb.com/v4/collection-type/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->collection_type_count($query);

    // all record count
    $igdb->collection_type_count();

?>
```

### Company
```php
public function company(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Company](https://api-docs.igdb.com/#company) endpoint.

**Endpoint Description**: Video game companies. Both publishers &amp; developers
**Endpoint URL**: `https://api.igdb.com/v4/company`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Company endpoint method
    $igdb->company($query);

?>
```

### Company Count
```php
public function company_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Company](https://api-docs.igdb.com/#company) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Video game companies. Both publishers &amp; developers

**Endpoint URL**: `https://api.igdb.com/v4/company/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->company_count($query);

    // all record count
    $igdb->company_count();

?>
```

### Company Logo
```php
public function company_logo(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Company Logo](https://api-docs.igdb.com/#company-logo) endpoint.

**Endpoint Description**: The logos of developers and publishers
**Endpoint URL**: `https://api.igdb.com/v4/company-logo`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Company Logo endpoint method
    $igdb->company_logo($query);

?>
```

### Company Logo Count
```php
public function company_logo_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Company Logo](https://api-docs.igdb.com/#company-logo) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: The logos of developers and publishers

**Endpoint URL**: `https://api.igdb.com/v4/company-logo/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->company_logo_count($query);

    // all record count
    $igdb->company_logo_count();

?>
```

### Company Website
```php
public function company_website(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Company Website](https://api-docs.igdb.com/#company-website) endpoint.

**Endpoint Description**: Company Website
**Endpoint URL**: `https://api.igdb.com/v4/company-website`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Company Website endpoint method
    $igdb->company_website($query);

?>
```

### Company Website Count
```php
public function company_website_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Company Website](https://api-docs.igdb.com/#company-website) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Company Website

**Endpoint URL**: `https://api.igdb.com/v4/company-website/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->company_website_count($query);

    // all record count
    $igdb->company_website_count();

?>
```

### Cover
```php
public function cover(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Cover](https://api-docs.igdb.com/#cover) endpoint.

**Endpoint Description**: The cover art of games
**Endpoint URL**: `https://api.igdb.com/v4/cover`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Cover endpoint method
    $igdb->cover($query);

?>
```

### Cover Count
```php
public function cover_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Cover](https://api-docs.igdb.com/#cover) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: The cover art of games

**Endpoint URL**: `https://api.igdb.com/v4/cover/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->cover_count($query);

    // all record count
    $igdb->cover_count();

?>
```

### Event
```php
public function event(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Event](https://api-docs.igdb.com/#event) endpoint.

**Endpoint Description**: Gaming event like GamesCom, Tokyo Game Show, PAX or GSL
**Endpoint URL**: `https://api.igdb.com/v4/event`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Event endpoint method
    $igdb->event($query);

?>
```

### Event Count
```php
public function event_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Event](https://api-docs.igdb.com/#event) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Gaming event like GamesCom, Tokyo Game Show, PAX or GSL

**Endpoint URL**: `https://api.igdb.com/v4/event/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->event_count($query);

    // all record count
    $igdb->event_count();

?>
```

### Event Logo
```php
public function event_logo(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Event Logo](https://api-docs.igdb.com/#event-logo) endpoint.

**Endpoint Description**: Logo for the event
**Endpoint URL**: `https://api.igdb.com/v4/event-logo`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Event Logo endpoint method
    $igdb->event_logo($query);

?>
```

### Event Logo Count
```php
public function event_logo_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Event Logo](https://api-docs.igdb.com/#event-logo) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Logo for the event

**Endpoint URL**: `https://api.igdb.com/v4/event-logo/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->event_logo_count($query);

    // all record count
    $igdb->event_logo_count();

?>
```

### Event Network
```php
public function event_network(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Event Network](https://api-docs.igdb.com/#event-network) endpoint.

**Endpoint Description**: Urls related to the event like twitter, facebook and youtube
**Endpoint URL**: `https://api.igdb.com/v4/event-network`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Event Network endpoint method
    $igdb->event_network($query);

?>
```

### Event Network Count
```php
public function event_network_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Event Network](https://api-docs.igdb.com/#event-network) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Urls related to the event like twitter, facebook and youtube

**Endpoint URL**: `https://api.igdb.com/v4/event-network/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->event_network_count($query);

    // all record count
    $igdb->event_network_count();

?>
```

### External Game
```php
public function external_game(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [External Game](https://api-docs.igdb.com/#external-game) endpoint.

**Endpoint Description**: Game IDs on other services
**Endpoint URL**: `https://api.igdb.com/v4/external-game`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // External Game endpoint method
    $igdb->external_game($query);

?>
```

### External Game Count
```php
public function external_game_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [External Game](https://api-docs.igdb.com/#external-game) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Game IDs on other services

**Endpoint URL**: `https://api.igdb.com/v4/external-game/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->external_game_count($query);

    // all record count
    $igdb->external_game_count();

?>
```

### Franchise
```php
public function franchise(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Franchise](https://api-docs.igdb.com/#franchise) endpoint.

**Endpoint Description**: A list of video game franchises such as Star Wars.
**Endpoint URL**: `https://api.igdb.com/v4/franchise`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Franchise endpoint method
    $igdb->franchise($query);

?>
```

### Franchise Count
```php
public function franchise_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Franchise](https://api-docs.igdb.com/#franchise) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: A list of video game franchises such as Star Wars.

**Endpoint URL**: `https://api.igdb.com/v4/franchise/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->franchise_count($query);

    // all record count
    $igdb->franchise_count();

?>
```

### Game
```php
public function game(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Game](https://api-docs.igdb.com/#game) endpoint.

**Endpoint Description**: Video Games!
**Endpoint URL**: `https://api.igdb.com/v4/game`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Game endpoint method
    $igdb->game($query);

?>
```

### Game Count
```php
public function game_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Game](https://api-docs.igdb.com/#game) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Video Games!

**Endpoint URL**: `https://api.igdb.com/v4/game/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->game_count($query);

    // all record count
    $igdb->game_count();

?>
```

### Game Engine
```php
public function game_engine(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Game Engine](https://api-docs.igdb.com/#game-engine) endpoint.

**Endpoint Description**: Video game engines such as unreal engine.
**Endpoint URL**: `https://api.igdb.com/v4/game-engine`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Game Engine endpoint method
    $igdb->game_engine($query);

?>
```

### Game Engine Count
```php
public function game_engine_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Game Engine](https://api-docs.igdb.com/#game-engine) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Video game engines such as unreal engine.

**Endpoint URL**: `https://api.igdb.com/v4/game-engine/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->game_engine_count($query);

    // all record count
    $igdb->game_engine_count();

?>
```

### Game Engine Logo
```php
public function game_engine_logo(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Game Engine Logo](https://api-docs.igdb.com/#game-engine-logo) endpoint.

**Endpoint Description**: The logos of game engines
**Endpoint URL**: `https://api.igdb.com/v4/game-engine-logo`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Game Engine Logo endpoint method
    $igdb->game_engine_logo($query);

?>
```

### Game Engine Logo Count
```php
public function game_engine_logo_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Game Engine Logo](https://api-docs.igdb.com/#game-engine-logo) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: The logos of game engines

**Endpoint URL**: `https://api.igdb.com/v4/game-engine-logo/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->game_engine_logo_count($query);

    // all record count
    $igdb->game_engine_logo_count();

?>
```

### Game Localization
```php
public function game_localization(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Game Localization](https://api-docs.igdb.com/#game-localization) endpoint.

**Endpoint Description**: Game localization for a game
**Endpoint URL**: `https://api.igdb.com/v4/game-localization`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Game Localization endpoint method
    $igdb->game_localization($query);

?>
```

### Game Localization Count
```php
public function game_localization_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Game Localization](https://api-docs.igdb.com/#game-localization) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Game localization for a game

**Endpoint URL**: `https://api.igdb.com/v4/game-localization/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->game_localization_count($query);

    // all record count
    $igdb->game_localization_count();

?>
```

### Game Mode
```php
public function game_mode(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Game Mode](https://api-docs.igdb.com/#game-mode) endpoint.

**Endpoint Description**: Single player, Multiplayer etc
**Endpoint URL**: `https://api.igdb.com/v4/game-mode`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Game Mode endpoint method
    $igdb->game_mode($query);

?>
```

### Game Mode Count
```php
public function game_mode_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Game Mode](https://api-docs.igdb.com/#game-mode) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Single player, Multiplayer etc

**Endpoint URL**: `https://api.igdb.com/v4/game-mode/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->game_mode_count($query);

    // all record count
    $igdb->game_mode_count();

?>
```

### Game Version
```php
public function game_version(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Game Version](https://api-docs.igdb.com/#game-version) endpoint.

**Endpoint Description**: Details about game editions and versions.
**Endpoint URL**: `https://api.igdb.com/v4/game-version`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Game Version endpoint method
    $igdb->game_version($query);

?>
```

### Game Version Count
```php
public function game_version_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Game Version](https://api-docs.igdb.com/#game-version) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Details about game editions and versions.

**Endpoint URL**: `https://api.igdb.com/v4/game-version/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->game_version_count($query);

    // all record count
    $igdb->game_version_count();

?>
```

### Game Version Feature
```php
public function game_version_feature(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Game Version Feature](https://api-docs.igdb.com/#game-version-feature) endpoint.

**Endpoint Description**: Features and descriptions of what makes each version&#x2F;edition different from the main game
**Endpoint URL**: `https://api.igdb.com/v4/game-version-feature`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Game Version Feature endpoint method
    $igdb->game_version_feature($query);

?>
```

### Game Version Feature Count
```php
public function game_version_feature_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Game Version Feature](https://api-docs.igdb.com/#game-version-feature) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Features and descriptions of what makes each version&#x2F;edition different from the main game

**Endpoint URL**: `https://api.igdb.com/v4/game-version-feature/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->game_version_feature_count($query);

    // all record count
    $igdb->game_version_feature_count();

?>
```

### Game Version Feature Value
```php
public function game_version_feature_value(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Game Version Feature Value](https://api-docs.igdb.com/#game-version-feature-value) endpoint.

**Endpoint Description**: The bool&#x2F;text value of the feature
**Endpoint URL**: `https://api.igdb.com/v4/game-version-feature-value`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Game Version Feature Value endpoint method
    $igdb->game_version_feature_value($query);

?>
```

### Game Version Feature Value Count
```php
public function game_version_feature_value_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Game Version Feature Value](https://api-docs.igdb.com/#game-version-feature-value) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: The bool&#x2F;text value of the feature

**Endpoint URL**: `https://api.igdb.com/v4/game-version-feature-value/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->game_version_feature_value_count($query);

    // all record count
    $igdb->game_version_feature_value_count();

?>
```

### Game Video
```php
public function game_video(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Game Video](https://api-docs.igdb.com/#game-video) endpoint.

**Endpoint Description**: A video associated with a game
**Endpoint URL**: `https://api.igdb.com/v4/game-video`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Game Video endpoint method
    $igdb->game_video($query);

?>
```

### Game Video Count
```php
public function game_video_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Game Video](https://api-docs.igdb.com/#game-video) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: A video associated with a game

**Endpoint URL**: `https://api.igdb.com/v4/game-video/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->game_video_count($query);

    // all record count
    $igdb->game_video_count();

?>
```

### Genre
```php
public function genre(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Genre](https://api-docs.igdb.com/#genre) endpoint.

**Endpoint Description**: Genres of video game
**Endpoint URL**: `https://api.igdb.com/v4/genre`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Genre endpoint method
    $igdb->genre($query);

?>
```

### Genre Count
```php
public function genre_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Genre](https://api-docs.igdb.com/#genre) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Genres of video game

**Endpoint URL**: `https://api.igdb.com/v4/genre/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->genre_count($query);

    // all record count
    $igdb->genre_count();

?>
```

### Involved Company
```php
public function involved_company(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Involved Company](https://api-docs.igdb.com/#involved-company) endpoint.

**Endpoint URL**: `https://api.igdb.com/v4/involved-company`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Involved Company endpoint method
    $igdb->involved_company($query);

?>
```

### Involved Company Count
```php
public function involved_company_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Involved Company](https://api-docs.igdb.com/#involved-company) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint URL**: `https://api.igdb.com/v4/involved-company/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->involved_company_count($query);

    // all record count
    $igdb->involved_company_count();

?>
```

### Keyword
```php
public function keyword(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Keyword](https://api-docs.igdb.com/#keyword) endpoint.

**Endpoint Description**: Keywords are words or phrases that get tagged to a game such as &quot;world war 2&quot; or &quot;steampunk&quot;.
**Endpoint URL**: `https://api.igdb.com/v4/keyword`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Keyword endpoint method
    $igdb->keyword($query);

?>
```

### Keyword Count
```php
public function keyword_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Keyword](https://api-docs.igdb.com/#keyword) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Keywords are words or phrases that get tagged to a game such as &quot;world war 2&quot; or &quot;steampunk&quot;.

**Endpoint URL**: `https://api.igdb.com/v4/keyword/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->keyword_count($query);

    // all record count
    $igdb->keyword_count();

?>
```

### Language Support
```php
public function language_support(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Language Support](https://api-docs.igdb.com/#language-support) endpoint.

**Endpoint Description**: Games can be played with different languages for voice acting, subtitles, or the interface language.
**Endpoint URL**: `https://api.igdb.com/v4/language-support`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Language Support endpoint method
    $igdb->language_support($query);

?>
```

### Language Support Count
```php
public function language_support_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Language Support](https://api-docs.igdb.com/#language-support) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Games can be played with different languages for voice acting, subtitles, or the interface language.

**Endpoint URL**: `https://api.igdb.com/v4/language-support/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->language_support_count($query);

    // all record count
    $igdb->language_support_count();

?>
```

### Language Support Type
```php
public function language_support_type(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Language Support Type](https://api-docs.igdb.com/#language-support-type) endpoint.

**Endpoint Description**: Language Support Types contains the identifiers for the support types that Language Support uses.
**Endpoint URL**: `https://api.igdb.com/v4/language-support-type`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Language Support Type endpoint method
    $igdb->language_support_type($query);

?>
```

### Language Support Type Count
```php
public function language_support_type_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Language Support Type](https://api-docs.igdb.com/#language-support-type) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Language Support Types contains the identifiers for the support types that Language Support uses.

**Endpoint URL**: `https://api.igdb.com/v4/language-support-type/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->language_support_type_count($query);

    // all record count
    $igdb->language_support_type_count();

?>
```

### Language
```php
public function language(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Language](https://api-docs.igdb.com/#language) endpoint.

**Endpoint Description**: Languages that are used in the Language Support endpoint.
**Endpoint URL**: `https://api.igdb.com/v4/language`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Language endpoint method
    $igdb->language($query);

?>
```

### Language Count
```php
public function language_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Language](https://api-docs.igdb.com/#language) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Languages that are used in the Language Support endpoint.

**Endpoint URL**: `https://api.igdb.com/v4/language/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->language_count($query);

    // all record count
    $igdb->language_count();

?>
```

### Multiplayer Mode
```php
public function multiplayer_mode(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Multiplayer Mode](https://api-docs.igdb.com/#multiplayer-mode) endpoint.

**Endpoint Description**: Data about the supported multiplayer types
**Endpoint URL**: `https://api.igdb.com/v4/multiplayer-mode`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Multiplayer Mode endpoint method
    $igdb->multiplayer_mode($query);

?>
```

### Multiplayer Mode Count
```php
public function multiplayer_mode_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Multiplayer Mode](https://api-docs.igdb.com/#multiplayer-mode) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Data about the supported multiplayer types

**Endpoint URL**: `https://api.igdb.com/v4/multiplayer-mode/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->multiplayer_mode_count($query);

    // all record count
    $igdb->multiplayer_mode_count();

?>
```

### Network Type
```php
public function network_type(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Network Type](https://api-docs.igdb.com/#network-type) endpoint.

**Endpoint Description**: Social networks related to the event like twitter, facebook and youtube
**Endpoint URL**: `https://api.igdb.com/v4/network-type`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Network Type endpoint method
    $igdb->network_type($query);

?>
```

### Network Type Count
```php
public function network_type_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Network Type](https://api-docs.igdb.com/#network-type) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Social networks related to the event like twitter, facebook and youtube

**Endpoint URL**: `https://api.igdb.com/v4/network-type/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->network_type_count($query);

    // all record count
    $igdb->network_type_count();

?>
```

### Platform
```php
public function platform(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Platform](https://api-docs.igdb.com/#platform) endpoint.

**Endpoint Description**: The hardware used to run the game or game delivery network
**Endpoint URL**: `https://api.igdb.com/v4/platform`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Platform endpoint method
    $igdb->platform($query);

?>
```

### Platform Count
```php
public function platform_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Platform](https://api-docs.igdb.com/#platform) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: The hardware used to run the game or game delivery network

**Endpoint URL**: `https://api.igdb.com/v4/platform/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->platform_count($query);

    // all record count
    $igdb->platform_count();

?>
```

### Platform Family
```php
public function platform_family(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Platform Family](https://api-docs.igdb.com/#platform-family) endpoint.

**Endpoint Description**: A collection of closely related platforms
**Endpoint URL**: `https://api.igdb.com/v4/platform-family`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Platform Family endpoint method
    $igdb->platform_family($query);

?>
```

### Platform Family Count
```php
public function platform_family_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Platform Family](https://api-docs.igdb.com/#platform-family) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: A collection of closely related platforms

**Endpoint URL**: `https://api.igdb.com/v4/platform-family/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->platform_family_count($query);

    // all record count
    $igdb->platform_family_count();

?>
```

### Platform Logo
```php
public function platform_logo(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Platform Logo](https://api-docs.igdb.com/#platform-logo) endpoint.

**Endpoint Description**: Logo for a platform
**Endpoint URL**: `https://api.igdb.com/v4/platform-logo`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Platform Logo endpoint method
    $igdb->platform_logo($query);

?>
```

### Platform Logo Count
```php
public function platform_logo_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Platform Logo](https://api-docs.igdb.com/#platform-logo) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Logo for a platform

**Endpoint URL**: `https://api.igdb.com/v4/platform-logo/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->platform_logo_count($query);

    // all record count
    $igdb->platform_logo_count();

?>
```

### Platform Version
```php
public function platform_version(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Platform Version](https://api-docs.igdb.com/#platform-version) endpoint.

**Endpoint URL**: `https://api.igdb.com/v4/platform-version`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Platform Version endpoint method
    $igdb->platform_version($query);

?>
```

### Platform Version Count
```php
public function platform_version_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Platform Version](https://api-docs.igdb.com/#platform-version) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint URL**: `https://api.igdb.com/v4/platform-version/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->platform_version_count($query);

    // all record count
    $igdb->platform_version_count();

?>
```

### Platform Version Company
```php
public function platform_version_company(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Platform Version Company](https://api-docs.igdb.com/#platform-version-company) endpoint.

**Endpoint Description**: A platform developer
**Endpoint URL**: `https://api.igdb.com/v4/platform-version-company`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Platform Version Company endpoint method
    $igdb->platform_version_company($query);

?>
```

### Platform Version Company Count
```php
public function platform_version_company_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Platform Version Company](https://api-docs.igdb.com/#platform-version-company) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: A platform developer

**Endpoint URL**: `https://api.igdb.com/v4/platform-version-company/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->platform_version_company_count($query);

    // all record count
    $igdb->platform_version_company_count();

?>
```

### Platform Version Release Date
```php
public function platform_version_release_date(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Platform Version Release Date](https://api-docs.igdb.com/#platform-version-release-date) endpoint.

**Endpoint Description**: A handy endpoint that extends platform release dates. Used to dig deeper into release dates, platforms and versions.
**Endpoint URL**: `https://api.igdb.com/v4/platform-version-release-date`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Platform Version Release Date endpoint method
    $igdb->platform_version_release_date($query);

?>
```

### Platform Version Release Date Count
```php
public function platform_version_release_date_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Platform Version Release Date](https://api-docs.igdb.com/#platform-version-release-date) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: A handy endpoint that extends platform release dates. Used to dig deeper into release dates, platforms and versions.

**Endpoint URL**: `https://api.igdb.com/v4/platform-version-release-date/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->platform_version_release_date_count($query);

    // all record count
    $igdb->platform_version_release_date_count();

?>
```

### Platform Website
```php
public function platform_website(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Platform Website](https://api-docs.igdb.com/#platform-website) endpoint.

**Endpoint Description**: The main website for the platform
**Endpoint URL**: `https://api.igdb.com/v4/platform-website`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Platform Website endpoint method
    $igdb->platform_website($query);

?>
```

### Platform Website Count
```php
public function platform_website_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Platform Website](https://api-docs.igdb.com/#platform-website) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: The main website for the platform

**Endpoint URL**: `https://api.igdb.com/v4/platform-website/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->platform_website_count($query);

    // all record count
    $igdb->platform_website_count();

?>
```

### Player Perspective
```php
public function player_perspective(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Player Perspective](https://api-docs.igdb.com/#player-perspective) endpoint.

**Endpoint Description**: Player perspectives describe the view&#x2F;perspective of the player in a video game.
**Endpoint URL**: `https://api.igdb.com/v4/player-perspective`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Player Perspective endpoint method
    $igdb->player_perspective($query);

?>
```

### Player Perspective Count
```php
public function player_perspective_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Player Perspective](https://api-docs.igdb.com/#player-perspective) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Player perspectives describe the view&#x2F;perspective of the player in a video game.

**Endpoint URL**: `https://api.igdb.com/v4/player-perspective/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->player_perspective_count($query);

    // all record count
    $igdb->player_perspective_count();

?>
```

### Region
```php
public function region(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Region](https://api-docs.igdb.com/#region) endpoint.

**Endpoint Description**: Region for game localization
**Endpoint URL**: `https://api.igdb.com/v4/region`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Region endpoint method
    $igdb->region($query);

?>
```

### Region Count
```php
public function region_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Region](https://api-docs.igdb.com/#region) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Region for game localization

**Endpoint URL**: `https://api.igdb.com/v4/region/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->region_count($query);

    // all record count
    $igdb->region_count();

?>
```

### Release Date
```php
public function release_date(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Release Date](https://api-docs.igdb.com/#release-date) endpoint.

**Endpoint Description**: A handy endpoint that extends game release dates. Used to dig deeper into release dates, platforms and versions.
**Endpoint URL**: `https://api.igdb.com/v4/release-date`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Release Date endpoint method
    $igdb->release_date($query);

?>
```

### Release Date Count
```php
public function release_date_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Release Date](https://api-docs.igdb.com/#release-date) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: A handy endpoint that extends game release dates. Used to dig deeper into release dates, platforms and versions.

**Endpoint URL**: `https://api.igdb.com/v4/release-date/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->release_date_count($query);

    // all record count
    $igdb->release_date_count();

?>
```

### Release Date Status
```php
public function release_date_status(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Release Date Status](https://api-docs.igdb.com/#release-date-status) endpoint.

**Endpoint Description**: An endpoint to provide definition of all of the current release date statuses.
**Endpoint URL**: `https://api.igdb.com/v4/release-date-status`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Release Date Status endpoint method
    $igdb->release_date_status($query);

?>
```

### Release Date Status Count
```php
public function release_date_status_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Release Date Status](https://api-docs.igdb.com/#release-date-status) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: An endpoint to provide definition of all of the current release date statuses.

**Endpoint URL**: `https://api.igdb.com/v4/release-date-status/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->release_date_status_count($query);

    // all record count
    $igdb->release_date_status_count();

?>
```

### Screenshot
```php
public function screenshot(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Screenshot](https://api-docs.igdb.com/#screenshot) endpoint.

**Endpoint Description**: Screenshots of games
**Endpoint URL**: `https://api.igdb.com/v4/screenshot`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Screenshot endpoint method
    $igdb->screenshot($query);

?>
```

### Screenshot Count
```php
public function screenshot_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Screenshot](https://api-docs.igdb.com/#screenshot) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Screenshots of games

**Endpoint URL**: `https://api.igdb.com/v4/screenshot/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->screenshot_count($query);

    // all record count
    $igdb->screenshot_count();

?>
```

### Search
```php
public function search(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Search](https://api-docs.igdb.com/#search) endpoint.

**Endpoint URL**: `https://api.igdb.com/v4/search`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Search endpoint method
    $igdb->search($query);

?>
```

### Search Count
```php
public function search_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Search](https://api-docs.igdb.com/#search) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint URL**: `https://api.igdb.com/v4/search/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->search_count($query);

    // all record count
    $igdb->search_count();

?>
```

### Theme
```php
public function theme(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Theme](https://api-docs.igdb.com/#theme) endpoint.

**Endpoint Description**: Video game themes
**Endpoint URL**: `https://api.igdb.com/v4/theme`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Theme endpoint method
    $igdb->theme($query);

?>
```

### Theme Count
```php
public function theme_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Theme](https://api-docs.igdb.com/#theme) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: Video game themes

**Endpoint URL**: `https://api.igdb.com/v4/theme/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->theme_count($query);

    // all record count
    $igdb->theme_count();

?>
```

### Website
```php
public function website(string $query | IGDBQueryBuilder $builder) throws IGDBEndpointException: mixed
```

Fetching records from IGDB API using the [Website](https://api-docs.igdb.com/#website) endpoint.

**Endpoint Description**: A website url, usually associated with a game
**Endpoint URL**: `https://api.igdb.com/v4/website`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: an array of objects decoded from the IGDB API response.

```php
<?php

    // Website endpoint method
    $igdb->website($query);

?>
```

### Website Count
```php
public function website_count(string $query = "" | IGDBQueryBuilder $builder) throws IGDBEndpointException: integer
```

Fetching the number of records from IGDB API using the [Website](https://api-docs.igdb.com/#website) endpoint. This method will return the record count matching the filter query. If the number of all records are required the method can be called without a filter query.

**Endpoint Description**: A website url, usually associated with a game

**Endpoint URL**: `https://api.igdb.com/v4/website/count`

**Parameters**:
 - `$query`: an apicalypse formatted query string or a configured IGDBQueryBuilder instance

**Returns**: the number of records matching the filter query

```php
<?php

    // filtered record count
    $igdb->website_count($query);

    // all record count
    $igdb->website_count();

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

## Example {.tabset}
### Source
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
            // make sure to call the build_multiquery method instead of build
            ->build_multiquery();

        // building the bundle query
        $bundle = $bundle_builder
            ->name("Bundles")
            ->endpoint("game")
            ->fields("id,name,version_parent,category")
            ->where("version_parent = 25076")
            ->where("category = 0")
            // make sure to call the build_multiquery method instead of build
            ->build_multiquery();

        // the query can be passed as a string too

        // $main = "query games \"Main Game\" {
        //     fields id,name;
        //     where id = 25076;
        // };";

        // $bundle = "query games \"Bundles\" {
        //     fields id,name,version_parent,category;
        //     where version_parent = 25076 & category = 0;
        // };";

        var_dump(
            // passing the queries to the multiquery method
            $igdb->multiquery(
                // either the parsed queries
                array($main, $bundle)

                // or the builder objects directly
                // array($main_builder, $bundle_builder)
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
### Result

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

## {-}

## Return Values

There are 2 types of endpoint methods returning 2 different type of results.

### Entity requests

In case of a successful request every endpoint method will return **an array of objects** decoded from the response.

### Example {.tabset}
#### Source
```php
<?php

    // a query against the game endpoint
    $igdb->game("fields id,name; where id = (1,2);");

?>
```

#### Result

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

### {-}

The result object's properties will vary depending on the provided field list in the `$query`. From the example result above you can see, the result holds an array, containing two elements. Every element of the result array is an object, containing properties with name of the fields from the `fields` parameter.

### Count requests

Every endpoint method has a count counterpart. These methods will return the number of matched records.

### Example {.tabset}
#### With filter
```php
<?php

    // count the records matching the filter
    $igdb->game_count("where id = (1,2);");

?>
```

#### Without filter

```php
<?php

    // count all records from the game endpoint
    $igdb->game_count();

?>
```

### {-}

This query with filter will return the number `2` since the query matches only 2 games. In this query there is no point to add field list but the number of records can be filtered using a where statement. To count every records call the method without a filter query.
