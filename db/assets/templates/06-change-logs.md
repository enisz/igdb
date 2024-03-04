---
overview: Changes, updates, notes.
icon: fa-clipboard-list
---

# Change Log

## v5.0.0 - March 03, 2024

Major version! May break your project! Make sure to check the new features

 - IGDBUtils [webhook](#webhooks) support
 - [IGDBQueryBuilder](#the-query-builder) updates:
   - building standard and multiquery query strings is now separated to their respective methods:
     - [`build`](#apicalypse): standard apicalypse query
     - [`build_multiquery`](#multiquery): multiquery query string
 - [Wrapper](#the-wrapper) updates:
   - synchronised API endpoints from IGDB, every available endpoint has its wrapper endpoint method counterpart
   - endpoint methods - and multiquery - now accept both an apicalypse query string or a configured IGDBQueryBuilder instance(s)
   - every endpoint method has its count counterpart. Count parameter is no longer available for the endpoint methods
   - multiple constant values are moved to a dedicated file `IGDBConstants.php`.
 - the documentation is completely rewritten in Angular and [it can be installed on multiple devices as a PWA](#installing-the-application)!

## v4.3.2 - October 26, 2023
 - Added new endpoint methods to the wrapper
   - [collection_membership](#collection-membership)
   - [collection_membership_type](#collection-membership-type)
   - [collection_relation](#collection-relation)
   - [collection_relation_type](#collection-relation-type)
   - [collection_type](#collection-type)
   - [event](#event)
   - [event_logo](#event-logo)
   - [event_network](#event-network)
   - [game_localization](#game-localization)
   - [language](#language)
   - [language_support](#language-support)
   - [language_support_type](#language-support-type)
   - [network_type](#network-type)
   - [region](#region)
   - [release_date_status](#release-date-status)

## v4.3.1 - April 19, 2022
 - IGDBEndpointException
   - Added a `getResponseCode()` to fetch http response code from IGDB API
 - Added sections to the documentation:
   - [Handling Errors](#handling-errors)
   - [Handling Builder Errors](#handling-builder-errors)
   - [Handling Request Errors](#handling-request-errors)

## v4.3.0 - August 19, 2021
 - IGDBQueryBuilder: three new properties introduced for multiquery:
  - [name](#name)
  - [endpoint](#endpoint)
  - [count](#count)
 - IGDBQueryBuilder: [build method](#building-the-query) signature updated
 - IGDBWrapper: [multiquery](#multiquery) updated to accept array of queries

## v4.2.0 - May 22, 2021
 - IGDBQueryBuilder: Moved the `$options` array parsing to the [`options()`](#options) method
 - IGDBQueryBuilder: [`reset()`](#reset) method added

## v4.1.2 - May 20, 2021
 - Minor updates to the readme

## v4.1.1 - May 20, 2021
 - Removed a debugging var_dump from the IGDB Wrapper
 - Updated the documentation with a [Query Builder example](#query-builder-with-options) with `$options` array

## v4.1.0 - May 15, 2021
 - The wrapper got a brand new documentation!
 - Introduced the [IGDBQueryBuilder](#the-query-builder) class
 - Introduced the [IGDB Utils](#utilities) class
 - Introduced `IGDBEndpointException` and `IGDBInvalidParameterException` classes
 - The wrapper [endpoint methods](#endpoints) no longer accepts `$options`, only [apicalypse query strings](https://api-docs.igdb.com/#apicalypse-1)

## v4.0.2 - April 28, 2021
 - Updated error response handling from IGDB

## v4.0.1 - February 18, 2021
 - Minor updates to the Readme

## v4.0.0 - October 20, 2020
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

## v2.0.3 - September 17, 2020
 - Fixed a bug with the `where` filter ([#6 Issues with slug field](https://github.com/enisz/igdb/issues/6))

## v2.0.2 - February 03, 2020
 - Fixing inaccurate information in the Readme

## v2.0.1 - January 27, 2020
 - Minor changes / fixes in the Readme
 - Added method [`_construct_url`](#construct-url)
 - Updated every endpoint method to construct the endpoint url's different

## v2.0.0 - December 04, 2019
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

## v1.0.5 - March 11, 2019
 - Fixed a bug at the request's error handling
 - [``public IGDB::get_request_info()``](#get-request-information) public method added

## v1.0.4 - March 25, 2018
 - Default properties has been removed.
 - set\_default public method has been removed.

## v1.0.3 - March 18, 2018
 - Providing either search or id parameter in the options array are not mandatory anymore.
 - Providing fields parameter when using expander is not mandatory anymore.
 - Ordering parameter 'order' in the options array has been renamed to 'direction'. Refer to the [order](#order) section of the [options parameters](#options-parameters).
 - Implemented count method. Refer to the [count](#count) section of the Readme.
 - Example _count.php_ has been added.
 - Updated Readme

## v1.0.2 - March 17, 2018
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

## v1.0.1 - March 16, 2018
 - Added [Changes](#changes) section to the ReadMe.
 - Fixed [filter parameter](#filters) constructing; the parameter input has been changed.
 - Added example snippets to the [Options Parameters](#options-parameters) section.
 - Added example file _filter_multiple_criteria.php_
 - Added example file _filter_single_criteria.php_
