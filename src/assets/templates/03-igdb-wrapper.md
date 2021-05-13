---
overview: A few thoughts about the project and the documentation.
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

### Age Rating Content Description
```php
public function age_rating_content_description(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

The organisation behind a specific rating

**Fields**
 - `(Category Enum) category`
 - `(uuid) checksum`: Hash of the object
 - `(String) description`

### Age Rating
```php
public function age_rating(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Age Rating according to various rating organisations

**Fields**
 - `(Category Enum) category`: The organization that has issued a specific rating
 - `(uuid) checksum`: Hash of the object
 - `(Reference ID for  Age Rating Content Description) content_descriptions`
 - `(Rating Enum) rating`: The title of an age rating
 - `(String) rating_cover_url`: The url for  the image of a age rating
 - `(String) synopsis`: A free text motivating a rating

### Alternative Name
```php
public function alternative_name(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Alternative and international game titles

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(String) comment`: A description of what kind of alternative name it is (Acronym, Working title, Japanese title etc)
 - `(Reference ID for Game) game`: The game this alternative name is associated with
 - `(String) name`: An alternative name

### Artwork
```php
public function artwork(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

official artworks (resolution and aspect ratio may vary)

**Fields**
 - `(boolean) alpha_channel`
 - `(boolean) animated`
 - `(uuid) checksum`: Hash of the object
 - `(Reference ID for Game) game`: The game this artwork is associated with
 - `(Integer) height`: The height of the image in pixels
 - `(String) image_id`: The ID of the image used to construct an IGDB image link
 - `(String) url`: The website address (URL) of the item
 - `(Integer) width`: The width of the image in pixels

### Character Mug Shot
```php
public function character_mug_shot(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Images depicting game characters

**Fields**
 - `(boolean) alpha_channel`
 - `(boolean) animated`
 - `(uuid) checksum`: Hash of the object
 - `(Integer) height`: The height of the image in pixels
 - `(String) image_id`: The ID of the image used to construct an IGDB image link
 - `(String) url`: The website address (URL) of the item
 - `(Integer) width`: The width of the image in pixels

### Character
```php
public function character(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Video game characters

**Fields**
 - `(Array of Strings) akas`: Alternative names for a character
 - `(uuid) checksum`: Hash of the object
 - `(String) country_name`: A characters country of origin
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(String) description`: A text describing a character
 - `(Array of Game IDs) games`
 - `(Gender Enum) gender`
 - `(Reference ID for  Character Mug Shot) mug_shot`: An image depicting a character
 - `(String) name`
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(Species Enum) species`
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item

### Collection
```php
public function collection(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Collection, AKA Series

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(Array of Game IDs) games`: The games that are associated with this collection
 - `(String) name`: Umbrella term for a collection of games
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item

### Company Logo
```php
public function company_logo(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

The logos of developers and publishers

**Fields**
 - `(boolean) alpha_channel`
 - `(boolean) animated`
 - `(uuid) checksum`: Hash of the object
 - `(Integer) height`: The height of the image in pixels
 - `(String) image_id`: The ID of the image used to construct an IGDB image link
 - `(String) url`: The website address (URL) of the item
 - `(Integer) width`: The width of the image in pixels

### Company Website
```php
public function company_website(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Company Website

**Fields**
 - `(Category Enum) category`: The service this website links to
 - `(uuid) checksum`: Hash of the object
 - `(boolean) trusted`
 - `(String) url`: The website address (URL) of the item

### Company
```php
public function company(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Video game companies. Both publishers &amp;amp; developers

**Fields**
 - `(Unix Time Stamp) change_date`: The data when a company got a new ID
 - `(Change Date Category Enum) change_date_category`
 - `(Reference ID for  Company) changed_company_id`: The new ID for a company that has gone through a merger or restructuring
 - `(uuid) checksum`: Hash of the object
 - `(Integer) country`: ISO 3166-1 country code
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(String) description`: A free text description of a company
 - `(Reference ID for  Game) developed`: An array of games that a company has developed
 - `(Reference ID for  Company Logo) logo`: The company&amp;rsquo;s logo
 - `(String) name`
 - `(Reference ID for  Company) parent`: A company with a controlling interest in a specific company
 - `(Reference ID for  Game) published`: An array of games that a company has published
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(Unix Time Stamp) start_date`: The date a company was founded
 - `(Start Date Category Enum) start_date_category`
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item
 - `(Reference ID for  Company Website) websites`: The companies official websites

### Cover
```php
public function cover(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

The cover art of games

**Fields**
 - `(boolean) alpha_channel`
 - `(boolean) animated`
 - `(uuid) checksum`: Hash of the object
 - `(Reference ID for Game) game`: The game this cover is associated with
 - `(Integer) height`: The height of the image in pixels
 - `(String) image_id`: The ID of the image used to construct an IGDB image link
 - `(String) url`: The website address (URL) of the item
 - `(Integer) width`: The width of the image in pixels

### External Game
```php
public function external_game(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Game IDs on other services

**Fields**
 - `(Category Enum) category`: The id of the other service
 - `(uuid) checksum`: Hash of the object
 - `(Array of Integers) countries`: The ISO country code of the external game product.
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(Reference ID for Game) game`: The IGDB ID of the game
 - `(Media Enum) media`: The media of the external game.
 - `(String) name`: The name of the game according to the other service
 - `(Reference ID for Platform) platform`: The platform of the external game product.
 - `(String) uid`: The other services ID for this game
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item
 - `(Integer) year`: The year in full (2018)

### Franchise
```php
public function franchise(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

A list of video game franchises such as Star Wars.

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(Array of Game IDs) games`: The games that are associated with this franchise
 - `(String) name`: The name of the franchise
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item

### Game Engine Logo
```php
public function game_engine_logo(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

The logos of game engines

**Fields**
 - `(boolean) alpha_channel`
 - `(boolean) animated`
 - `(uuid) checksum`: Hash of the object
 - `(Integer) height`: The height of the image in pixels
 - `(String) image_id`: The ID of the image used to construct an IGDB image link
 - `(String) url`: The website address (URL) of the item
 - `(Integer) width`: The width of the image in pixels

### Game Engine
```php
public function game_engine(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Video game engines such as unreal engine.

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Array of Company IDs) companies`: Companies who used this game engine
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(String) description`: Description of the game engine
 - `(Reference ID for  Game Engine Logo) logo`: Logo of the game engine
 - `(String) name`: Name of the game engine
 - `(Array of Platform IDs) platforms`: Platforms this game engine was deployed on
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item

### Game Mode
```php
public function game_mode(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Single player, Multiplayer etc

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(String) name`: The name of the game mode
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item

### Game Version Feature Value
```php
public function game_version_feature_value(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

The bool&#x2F;text value of the feature

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Reference ID for Game) game`: The version&#x2F;edition this value refers to
 - `(Reference ID for  Game Version Feature) game_feature`: The id of the game feature
 - `(Included Feature Enum) included_feature`: The boole value of this feature
 - `(String) note`: The text value of this feature

### Game Version Feature
```php
public function game_version_feature(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Features and descriptions of what makes each version&#x2F;edition different from the main game

**Fields**
 - `(Category Enum) category`: The category of the feature description
 - `(uuid) checksum`: Hash of the object
 - `(String) description`: The description of the feature
 - `(Integer) position`: Position of this feature in the list of features
 - `(String) title`: The title of the feature
 - `(Reference ID for  Game Version Feature Value) values`: The bool&#x2F;text value of the feature

### Game Version
```php
public function game_version(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Details about game editions and versions.

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(Reference ID for  Game Version Feature) features`: Features and descriptions of what makes each version&#x2F;edition different from the main game
 - `(Reference ID for Game) game`: The game these versions&#x2F;editions are of
 - `(Array of Game IDs) games`: Game Versions and Editions
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item

### Game Video
```php
public function game_video(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

A video associated with a game

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Reference ID for Game) game`: The game this video is associated with
 - `(String) name`: The name of the video
 - `(String) video_id`: The external ID of the video (usually youtube)

### Game
```php
public function game(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Video Games!

**Fields**
 - `(Reference ID for  Age Rating) age_ratings`: The PEGI rating
 - `(Double) aggregated_rating`: Rating based on external critic scores
 - `(Integer) aggregated_rating_count`: Number of external critic scores
 - `(Array of Alternative Name IDs) alternative_names`: Alternative names for this game
 - `(Array of Artwork IDs) artworks`: Artworks of this game
 - `(Reference ID for  Game) bundles`: The bundles this game is a part of
 - `(Category Enum) category`: The category of this game
 - `(uuid) checksum`: Hash of the object
 - `(Reference ID for Collection) collection`: The series the game belongs to
 - `(Reference ID for Cover) cover`: The cover of this game
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(Reference ID for  Game) dlcs`: DLCs for this game
 - `(Reference ID for  Game) expansions`: Expansions of this game
 - `(Array of External Game IDs) external_games`: External IDs this game has on other services
 - `(Unix Time Stamp) first_release_date`: The first release date for this game
 - `(Integer) follows`: Number of people following a game
 - `(Reference ID for Franchise) franchise`: The main franchise
 - `(Array of Franchise IDs) franchises`: Other franchises the game belongs to
 - `(Array of Game Engine IDs) game_engines`: The game engine used in this game
 - `(Array of Game Mode IDs) game_modes`: Modes of gameplay
 - `(Array of Genre IDs) genres`: Genres of the game
 - `(Integer) hypes`: Number of follows a game gets before release
 - `(Array of Involved Company IDs) involved_companies`: Companies who developed this game
 - `(Array of Keyword IDs) keywords`: Associated keywords
 - `(Array of Multiplayer Mode IDs) multiplayer_modes`: Multiplayer modes for this game
 - `(String) name`
 - `(Reference ID for  Game) parent_game`: If a DLC, expansion or part of a bundle, this is the main game or bundle
 - `(Array of Platform IDs) platforms`: Platforms this game was released on
 - `(Array of Player Perspective IDs) player_perspectives`: The main perspective of the player
 - `(Double) rating`: Average IGDB user rating
 - `(Integer) rating_count`: Total number of IGDB user ratings
 - `(Array of Release Date IDs) release_dates`: Release dates of this game
 - `(Array of Screenshot IDs) screenshots`: Screenshots of this game
 - `(Reference ID for  Game) similar_games`: Similar games
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(Reference ID for  Game) standalone_expansions`: Standalone expansions of this game
 - `(Status Enum) status`: The status of the games release
 - `(String) storyline`: A short description of a games story
 - `(String) summary`: A description of the game
 - `(Array of Tag Numbers) tags`: Related entities in the IGDB API
 - `(Array of Theme IDs) themes`: Themes of the game
 - `(Double) total_rating`: Average rating based on both IGDB user and external critic scores
 - `(Integer) total_rating_count`: Total number of user and external critic scores
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item
 - `(Reference ID for  Game) version_parent`: If a version, this is the main game
 - `(String) version_title`: Title of this version (i.e Gold edition)
 - `(Reference ID for  Game Video) videos`: Videos of this game
 - `(Reference ID for  Website) websites`: Websites associated with this game

### Genre
```php
public function genre(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Genres of video game

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(String) name`
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item

### Involved Company
```php
public function involved_company(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

&lt;code&gt;https:&#x2F;&#x2F;api.igdb.com&#x2F;v4&#x2F;involved_companies&lt;&#x2F;code&gt;

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Reference ID for Company) company`
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(boolean) developer`
 - `(Reference ID for Game) game`
 - `(boolean) porting`
 - `(boolean) publisher`
 - `(boolean) supporting`
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database

### Keyword
```php
public function keyword(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Keywords are words or phrases that get tagged to a game such as “world war 2” or “steampunk”.

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(String) name`
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item

### Multiplayer Mode
```php
public function multiplayer_mode(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Data about the supported multiplayer types

**Fields**
 - `(boolean) campaigncoop`: True if the game supports campaign coop
 - `(uuid) checksum`: Hash of the object
 - `(boolean) dropin`: True if the game supports drop in&#x2F;out multiplayer
 - `(Reference ID for Game) game`: The game this multiplayer mode is associated with
 - `(boolean) lancoop`: True if the game supports LAN coop
 - `(boolean) offlinecoop`: True if the game supports offline coop
 - `(Integer) offlinecoopmax`: Maximum number of offline players in offline coop
 - `(Integer) offlinemax`: Maximum number of players in offline multiplayer
 - `(boolean) onlinecoop`: True if the game supports online coop
 - `(Integer) onlinecoopmax`: Maximum number of online players in online coop
 - `(Integer) onlinemax`: Maximum number of players in online multiplayer
 - `(Reference ID for Platform) platform`: The platform this multiplayer mode refers to
 - `(boolean) splitscreen`: True if the game supports split screen, offline multiplayer
 - `(boolean) splitscreenonline`: True if the game supports split screen, online multiplayer

### Platform Family
```php
public function platform_family(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

A collection of closely related platforms

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(String) name`: The name of the platform family
 - `(String) slug`: A url-safe, unique, lower-case version of the name

### Platform Logo
```php
public function platform_logo(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Logo for a platform

**Fields**
 - `(boolean) alpha_channel`
 - `(boolean) animated`
 - `(uuid) checksum`: Hash of the object
 - `(Integer) height`: The height of the image in pixels
 - `(String) image_id`: The ID of the image used to construct an IGDB image link
 - `(String) url`: The website address (URL) of the item
 - `(Integer) width`: The width of the image in pixels

### Platform Version Company
```php
public function platform_version_company(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

A platform developer

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(String) comment`: Any notable comments about the developer
 - `(Reference ID for Company) company`: The company responsible for developing this platform version
 - `(boolean) developer`
 - `(boolean) manufacturer`

### Platform Version Release Date
```php
public function platform_version_release_date(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

A handy endpoint that extends platform release dates. Used to dig deeper into release dates, platforms and versions.

**Fields**
 - `(Category Enum) category`: The format of the release date
 - `(uuid) checksum`: Hash of the object
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(Unix Time Stamp) date`: The release date
 - `(String) human`: A human readable version of the release date
 - `(Integer) m`: The month as an integer starting at 1 (January)
 - `(Reference ID for Platform Version) platform_version`: The platform this release date is for
 - `(Region Enum) region`: The region of the release
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(Integer) y`: The year in full (2018)

### Platform Version
```php
public function platform_version(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

&lt;code&gt;https:&#x2F;&#x2F;api.igdb.com&#x2F;v4&#x2F;platform_versions&lt;&#x2F;code&gt;

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Reference ID for  Platform Version Company) companies`: Who developed this platform version
 - `(String) connectivity`: The network capabilities
 - `(String) cpu`: The integrated control processing unit
 - `(String) graphics`: The graphics chipset
 - `(Reference ID for  Platform Version Company) main_manufacturer`: Who manufactured this version of the platform
 - `(String) media`: The type of media this version accepted
 - `(String) memory`: How much memory there is
 - `(String) name`: The name of the platform version
 - `(String) os`: The operating system installed on the platform version
 - `(String) output`: The output video rate
 - `(Reference ID for Platform Logo) platform_logo`: The logo of this platform version
 - `(Array of Platform Version Release Date IDs) platform_version_release_dates`: When this platform was released
 - `(String) resolutions`: The maximum resolution
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(String) sound`: The sound chipset
 - `(String) storage`: How much storage there is
 - `(String) summary`: A short summary
 - `(String) url`: The website address (URL) of the item

### Platform Website
```php
public function platform_website(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

The main website for the platform

**Fields**
 - `(Category Enum) category`: The service this website links to
 - `(uuid) checksum`: Hash of the object
 - `(boolean) trusted`
 - `(String) url`: The website address (URL) of the item

### Platform
```php
public function platform(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

The hardware used to run the game or game delivery network

**Fields**
 - `(String) abbreviation`: An abbreviation of the platform name
 - `(String) alternative_name`: An alternative name for the platform
 - `(Category Enum) category`: A physical or virtual category of the platform
 - `(uuid) checksum`: Hash of the object
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(Integer) generation`: The generation of the platform
 - `(String) name`: The name of the platform
 - `(Reference ID for Platform Family) platform_family`: The family of platforms this one belongs to
 - `(Reference ID for Platform Logo) platform_logo`: The logo of the first Version of this platform
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(String) summary`: The summary of the first Version of this platform
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item
 - `(Reference ID for  Platform Version) versions`: Associated versions of this platform
 - `(Reference ID for  Platform Website) websites`: The main website

### Player Perspective
```php
public function player_perspective(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Player perspectives describe the view&#x2F;perspective of the player in a video game.

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(String) name`
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item

### Release Date
```php
public function release_date(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

A handy endpoint that extends game release dates. Used to dig deeper into release dates, platforms and versions.

**Fields**
 - `(Category Enum) category`: The format category of the release date
 - `(uuid) checksum`: Hash of the object
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(Unix Time Stamp) date`: The date of the release
 - `(Reference ID for Game) game`
 - `(String) human`: A human readable representation of the date
 - `(Integer) m`: The month as an integer starting at 1 (January)
 - `(Reference ID for Platform) platform`: The platform of the release
 - `(Region Enum) region`: The region of the release
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(Integer) y`: The year in full (2018)

### Screenshot
```php
public function screenshot(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Screenshots of games

**Fields**
 - `(boolean) alpha_channel`
 - `(boolean) animated`
 - `(uuid) checksum`: Hash of the object
 - `(Reference ID for Game) game`: The game this video is associated with
 - `(Integer) height`: The height of the image in pixels
 - `(String) image_id`: The ID of the image used to construct an IGDB image link
 - `(String) url`: The website address (URL) of the item
 - `(Integer) width`: The width of the image in pixels

### Search
```php
public function search(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

&lt;code&gt;https:&#x2F;&#x2F;api.igdb.com&#x2F;v4&#x2F;search&lt;&#x2F;code&gt;

**Fields**
 - `(String) alternative_name`
 - `(Reference ID for Character) character`
 - `(uuid) checksum`: Hash of the object
 - `(Reference ID for Collection) collection`
 - `(Reference ID for Company) company`
 - `(String) description`
 - `(Reference ID for Game) game`
 - `(String) name`
 - `(Reference ID for Platform) platform`
 - `(Unix Time Stamp) published_at`: The date this item was initially published by the third party
 - `(Reference ID for Test Dummy) test_dummy`
 - `(Reference ID for Theme) theme`

### Theme
```php
public function theme(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

Video game themes

**Fields**
 - `(uuid) checksum`: Hash of the object
 - `(Unix Time Stamp) created_at`: Date this was initially added to the IGDB database
 - `(String) name`
 - `(String) slug`: A url-safe, unique, lower-case version of the name
 - `(Unix Time Stamp) updated_at`: The last date this entry was updated in the IGDB database
 - `(String) url`: The website address (URL) of the item

### Website
```php
public function website(string $query, boolean $count = false) throws IGDBEndpointException: array | object
```

A website url, usually associated with a game

**Fields**
 - `(Category Enum) category`: The service this website links to
 - `(uuid) checksum`: Hash of the object
 - `(Reference ID for Game) game`: The game this website is associated with
 - `(boolean) trusted`
 - `(String) url`: The website address (URL) of the item

## MultiQuery

## Return Values