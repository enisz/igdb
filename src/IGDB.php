<?php

    /**
     * Internet Game Database API Wrapper
     *
     * Fethching data from IGDB's database.
     * Compatible with IGDB API v4
     *
     * @version 4.3.2
     * @author Enisz Abdalla <enisz87@gmail.com>
     * @link https://github.com/enisz/igdb
     */

    require_once "IGDBEndpointException.php";
    require_once "IGDBInvalidParameterException.php";

    class IGDB {

        /**
         * Client ID
         */
        private $client_id;

        /**
         * Generated Access Token
         */
        private $access_token;

        /**
         * cUrl handler
         */
        private $curl_handler;

        /**
         * Most recent request's details
         */
        private $request_info;

        /**
         * IGDB API Url
         */
        private $api_url = "https://api.igdb.com/v4";

        /**
         * Instantiates the IGDB object
         *
         * @param $client_id Your Client ID
         * @param $access_token Your generated Access Token
         */
        public function __construct($client_id, $access_token) {
            $this->client_id = $client_id;
            $this->access_token = $access_token;

            $this->_curl_init();
        }

        /**
         * Initialising the curl session
         */
        private function _curl_init() {
            $this->curl_handler = curl_init();
            curl_setopt($this->curl_handler, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->curl_handler, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curl_handler, CURLOPT_POST, true);
            curl_setopt($this->curl_handler, CURLOPT_HTTPHEADER, array(
                "Client-ID: $this->client_id",
                "Authorization: Bearer $this->access_token"
            ));
        }

        /**
         * Return the request details of the most recent query
         */
        public function get_request_info() {
            return $this->request_info;
        }

        /**
         * Constructing an endpoint url using the name of the endpoint and the optional count parameter.
         * @param $endpoint - the name of the endpoint. Make sure to use the name of the endpoint with "snake casing"
         * @param $count - whether the number of results is required or the result set itself
         * @throws IGDBInvalidParameterException if passed endpoint name is invalid
         * @return string - the constructed URL using the provided parameters
         */
        public function construct_url($endpoint, $count = false) {
            $endpoints = array(
                "age_rating" => "age_ratings",
                "age_rating_content_description" => "age_rating_content_descriptions",
                "alternative_name" => "alternative_names",
                "artwork" => "artworks",
                "character" => "characters",
                "character_mug_shot" => "character_mug_shots",
                "collection" => "collections",
                "collection_membership" => "collection_memberships",
                "collection_membership_type" => "collection_membership_types",
                "collection_relation" => "collection_relations",
                "collection_relation_type" => "collection_relation_types",
                "collection_type" => "collection_types",
                "company" => "companies",
                "company_logo" => "company_logos",
                "company_website" => "company_websites",
                "cover" => "covers",
                "event" => "events",
                "event_logo" => "event_logos",
                "event_network" => "event_networks",
                "external_game" => "external_games",
                "franchise" => "franchises",
                "game" => "games",
                "game_engine" => "game_engines",
                "game_engine_logo" => "game_engine_logos",
                "game_localization" => "game_localizations",
                "game_mode" => "game_modes",
                "game_version" => "game_versions",
                "game_version_feature" => "game_version_features",
                "game_version_feature_value" => "game_version_feature_values",
                "game_video" => "game_videos",
                "genre" => "genres",
                "involved_company" => "involved_companies",
                "keyword" => "keywords",
                "language" => "languages",
                "language_support" => "language_supports",
                "language_support_type" => "language_support_types",
                "multiplayer_mode" => "multiplayer_modes",
                "multiquery" => "multiquery",
                "network_type" => "network_types",
                "platform" => "platforms",
                "platform_family" => "platform_families",
                "platform_logo" => "platform_logos",
                "platform_version" => "platform_versions",
                "platform_version_company" => "platform_version_companies",
                "platform_version_release_date" => "platform_version_release_dates",
                "platform_website" => "platform_websites",
                "player_perspective" => "player_perspectives",
                "region" => "regions",
                "release_date" => "release_dates",
                "release_date_status" => "release_date_statuses",
                "screenshot" => "screenshots",
                "search" => "search",
                "theme" => "themes",
                "website" => "websites",
            );

            if(array_key_exists($endpoint, $endpoints)) {
                return rtrim($this->api_url, "/") . "/" . $endpoints[$endpoint] . ($count ? "/count" : "");
            } else {
                throw new IGDBInvalidParameterException("Invalid Endpoint name " . $endpoint . "!");
            }
        }

        /**
         * Executes the query against IGDB API.
         * Returns an array of objects decoded from IGDB JSON response or throws Exception in case of error
         *
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @param $url ( string ) The url of the endpoint
         * @param $query ( string ) The apicalypse query string to send
         * @return $result ( array ) The response objects from IGDB in an array.
         */
        private function _exec_query($url, $query) {
            if(is_null($this->curl_handler)) {
                $this->curl_reinit();
            }

            // Set the request URL
            curl_setopt($this->curl_handler, CURLOPT_URL, $url);

            // Set the body of the request
            curl_setopt($this->curl_handler, CURLOPT_POSTFIELDS, $query);

            // Executing and decoding the request
            $result = json_decode(curl_exec($this->curl_handler));

            // Getting request information
            $this->request_info = curl_getinfo($this->curl_handler);

            // HTTP response code
            $response_code = $this->request_info['http_code'];

            // If there were errors
            if($response_code < 200 || $response_code > 299) {
                $message = "Something went wrong with your query! Use <a href=\"https://enisz.github.io/igdb/documentation#get-request-info\" target=\"_blank\">get_request_info()</a> method to see the details of your query!";

                if(is_array($result) && is_object($result[0])) {
                    if(property_exists($result[0], "cause")) {
                        $message = $result[0]->cause;
                    }

                    if(property_exists($result[0], "title")) {
                        $message = $result[0]->title;
                    }
                }

                if(is_object($result) && property_exists($result, "message")) {
                    $message = $result->message;
                }

                throw new IGDBEndpointException($message, $response_code);
            }

            return $result;
        }

        /**
         * Closes the CURL handler.
         */
        public function curl_close() {
            curl_close($this->curl_handler);
            $this->curl_handler = null;
        }

        /**
         * Reinitialize the CURL session. Simply calls the _curl_init private method.
         */
        public function curl_reinit() {
            if(is_null($this->curl_handler)) {
                $this->_curl_init();
            }
        }

        /**
         * Executing a multiquery
         *
         * Multi-Query is a new way to request a huge amount of information in one request!
         * With Multi-Query you can request multiple endpoints at once,
         * it also works with multiple requests to a single endpoint as well.
         *
         * @link https://api-docs.igdb.com/#multi-query
         *
         * @param $queries ( array of strings ) The queries to send to the multiquery endpoint as an array of multiquery formatted apicalypse strings.
         * @return $result ( mixed ) The result of the query.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException If not array of strings is passed as a parameter
         */
        public function multiquery($queries) {
            if(gettype($queries) == "array") {
                foreach($queries as $index => $query) {
                    if(gettype($query) != "string") {
                        throw new IGDBInvalidParameterException("Invalid type of parameter for multiquery! An array of strings is expected, " . gettype($query) . " passed at index " . $index . "!");
                    }
                }
            } else {
                throw new IGDBInvalidParameterException("Invalid type of parameter for multiquery! An array is expected, " . gettype($queries) . " passed!");
            }

            return $this->_exec_query($this->construct_url("multiquery", false), implode("\n\n", $queries));
        }

        /**
         * Age Rating according to various rating organisations
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#age-rating
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function age_rating($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Age Rating Descriptors
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#age-rating-content-description
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function age_rating_content_description($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Alternative and international game titles
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#alternative-name
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function alternative_name($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * official artworks (resolution and aspect ratio may vary)
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#artwork
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function artwork($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Video game characters
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#character
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function character($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Images depicting game characters
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#character-mug-shot
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function character_mug_shot($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Collection, AKA Series
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#collection
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function collection($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * The Collection Memberships.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#collection-membership
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function collection_membership($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Enums for collection membership types.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#collection-membership-type
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function collection_membership_type($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Describes Relationship between Collections.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#collection-relation
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function collection_relation($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Collection Relation Types
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#collection-relation-type
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function collection_relation_type($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Enums for collection types.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#collection-type
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function collection_type($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Video game companies. Both publishers & developers
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#company
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function company($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * The logos of developers and publishers
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#company-logo
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function company_logo($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Company Website
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#company-website
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function company_website($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * The cover art of games
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#cover
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function cover($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Gaming event like GamesCom, Tokyo Game Show, PAX or GSL
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#event
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function event($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Logo for the event
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#event-logo
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function event_logo($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Urls related to the event like twitter, facebook and youtube
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#event-network
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function event_network($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Game IDs on other services
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#external-game
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function external_game($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * A list of video game franchises such as Star Wars.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#franchise
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function franchise($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Video Games!
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#game
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Video game engines such as unreal engine.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#game-engine
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_engine($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * The logos of game engines
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#game-engine-logo
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_engine_logo($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Game localization for a game
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#game-localization
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_localization($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Single player, Multiplayer etc
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#game-mode
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_mode($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Details about game editions and versions.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#game-version
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Features and descriptions of what makes each version&#x2F;edition different from the main game
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#game-version-feature
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version_feature($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * The bool&#x2F;text value of the feature
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#game-version-feature-value
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version_feature_value($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * A video associated with a game
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#game-video
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_video($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Genres of video game
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#genre
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function genre($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         *
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#involved-company
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function involved_company($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Keywords are words or phrases that get tagged to a game such as &quot;world war 2&quot; or &quot;steampunk&quot;.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#keyword
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function keyword($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Languages that are used in the Language Support endpoint.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#language
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function language($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Games can be played with different languages for voice acting, subtitles, or the interface language.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#language-support
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function language_support($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Data about the supported multiplayer types
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#multiplayer-mode
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function multiplayer_mode($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * The hardware used to run the game or game delivery network
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#platform
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Language Support Types contains the identifiers for the support types that Language Support uses.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#language-support-type
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function language_support_type($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * A collection of closely related platforms
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#platform-family
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_family($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Social networks related to the event like twitter, facebook and youtube
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#network-type
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function network_type($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Logo for a platform
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#platform-logo
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_logo($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * A platform developer
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#platform-version-company
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version_company($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         *
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#platform-version
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * The main website for the platform
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#platform-website
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_website($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * A handy endpoint that extends platform release dates. Used to dig deeper into release dates, platforms and versions.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#platform-version-release-date
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version_release_date($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Player perspectives describe the view&#x2F;perspective of the player in a video game.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#player-perspective
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function player_perspective($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Region for game localization
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#region
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function region($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * A handy endpoint that extends game release dates. Used to dig deeper into release dates, platforms and versions.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#release-date
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function release_date($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * An endpoint to provide definition of all of the current release date statuses.
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#release-date-status
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function release_date_status($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Screenshots of games
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#screenshot
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function screenshot($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         *
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#search
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function search($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * Video game themes
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#theme
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function theme($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }

        /**
         * A website url, usually associated with a game
         *
         * Depending on $count, the method will either return:
         *  - <code>TRUE</code>: an object containing a <code>count</code> property with the number of matched records
         *  - <code>FALSE</code>: an array of objects, containing the matched records from IGDB
         *
         * @link https://api-docs.igdb.com/#website
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function website($query, $count = false) {
            return $this->_exec_query($this->construct_url(__FUNCTION__, $count), $query);
        }
    }
?>
