# Internet Game Database API Wrapper

<!-- toc -->

- [Introduction](#introduction)
- [Documentation](#documentation)
- [Change Log](#change-log)
  * [v4.1.0 - May 15, 2021](#v410---may-15-2021)
  * [v4.0.2 - April 28, 2021](#v402---april-28-2021)
  * [v4.0.1 - February 18, 2021](#v401---february-18-2021)
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

> Now there is a utility class called `IGDBUtils` which contains an `authenticate` method to help you generate your access token! Refer to the [documentation](#documentation) for details!

For a better understanding of the wrapper, there are several example scripts in the [documentation](#documentation).

## Documentation

The documentation is now on a different branch and it is published to github pages. You can find it here:

[http://enisz.github.io/igdb](http://enisz.github.io/igdb)

## Change Log

### v4.1.0 - May 15, 2021
 - The wrapper got a brand new documentation!
 - Introduced the [IGDBQueryBuilder](#igdb-query-builder) class
 - Introduced the [IGDB Utils](#igdb-utils) class
 - Introduced `IGDBEndpointException` and `IGDBInvalidParameterException` classes
 - The wrapper [endpoint methods](#endpoints) no longer accepts `$options`, only [apicalypse query strings](https://api-docs.igdb.com/#apicalypse-1)

### v4.0.2 - April 28, 2021
 - Updated error response handling from IGDB

### v4.0.1 - February 18, 2021
 - Minor updates to the Readme

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