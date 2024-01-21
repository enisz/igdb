<?php

    /**
     * Internet Game Database API Wrapper
     *
     * Fethching data from IGDB's database.
     * Compatible with IGDB API v4
     *
     * @version 5.0.0
     * @author Enisz Abdalla <enisz87@gmail.com>
     * @link https://github.com/enisz/igdb
     * @link https://enisz.github.io/igdb
     */

    require_once "IGDBEndpointException.php";
    require_once "IGDBInvalidParameterException.php";
    require_once "IGDBConstants.php";

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
            curl_setopt($this->curl_handler, CURLOPT_CUSTOMREQUEST, "POST");
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
            if(array_key_exists($endpoint, IGDBW_ENDPOINTS)) {
                return rtrim(IGDBW_API_URL, "/") . "/" . IGDBW_ENDPOINTS[$endpoint] . ($count ? "/count" : "");
            } else {
                throw new IGDBInvalidParameterException("Invalid Endpoint name $endpoint!");
            }
        }

        /**
         * Executes the query against IGDB API.
         * Returns an array of objects decoded from IGDB JSON response or throws Exception in case of error
         *
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @param $url ( string ) The url of the endpoint
         * @param $query ( string | IGDBQueryBuilder ) query to send to IGDB
         * @return $result ( array ) The response objects from IGDB in an array.
         */
        private function _exec_query($url, $query) {
            if(is_null($this->curl_handler)) {
                $this->curl_reinit();
            }

            if(!is_string($query) && !($query instanceof IGDBQueryBuilder)) {
                throw new IGDBInvalidParameterException("Type of query has to be either a string or an IGDBQueryBuilder instance instead of " . gettype($query) . "!");
            }

            // Set the request URL
            curl_setopt($this->curl_handler, CURLOPT_URL, $url);

            // Set the body of the request
            curl_setopt($this->curl_handler, CURLOPT_POSTFIELDS, is_string($query) ? $query : $query->build());

            // Executing and decoding the request
            $result = json_decode(curl_exec($this->curl_handler));

            // Getting request information
            $this->request_info = curl_getinfo($this->curl_handler);

            // HTTP response code
            $response_code = curl_getinfo($this->curl_handler, CURLINFO_HTTP_CODE);

            // If there were errors
            if($response_code < 200 || $response_code > 299) {
                var_dump($result);
                $message = "Something went wrong with your query! Use <a href=\"https://enisz.github.io/igdb/documentation#get-request-info\" target=\"_blank\">get_request_info()</a> method to see the details of your query!";

                if(is_array($result) && is_object($result[0])) {
                    if(property_exists($result[0], "title")) {
                        $message = $result[0]->title;
                    }

                    if(property_exists($result[0], "cause")) {
                        $message .= ': ' . $result[0]->cause;
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
         * @param $queries ( string[] | IGDBQueryBuilder[] ) The queries to send to the multiquery endpoint as an array of multiquery formatted apicalypse strings or configured IGDBQueryBuilder instances
         * @return $result ( mixed ) The result of the query.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException If not array of strings or IGDBQueryBuilder instances are passed as a parameter
         */
        public function multiquery($queries) {
            $prepared = array();

            if(is_array($queries)) {
                foreach($queries as $index => $query) {
                    if(!is_string($query) && !($query instanceof IGDBQueryBuilder)) {
                        throw new IGDBInvalidParameterException("Invalid type of parameter for multiquery! An array of strings or IGDBQueryBuilder instances are expected, " . gettype($query) . " passed at index $index!");
                    }

                    array_push($prepared, is_string($query) ? $query : $query->build_multiquery());
                }
            } else {
                throw new IGDBInvalidParameterException("Invalid type of parameter for multiquery! An array is expected, " . gettype($queries) . " passed!");
            }

            return $this->_exec_query($this->construct_url(__FUNCTION__, false), implode("\n\n", $prepared));
        }

        /**
         * Age Rating according to various rating organisations
         *
         * @link https://api-docs.igdb.com/#age-rating
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function age_rating($query) {
            return $this->_exec_query($this->construct_url("age_rating", false), $query);
        }

        /**
         * Age Rating according to various rating organisations
         *
         * @link https://api-docs.igdb.com/#age-rating
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function age_rating_count($query = "") {
            return $this->_exec_query($this->construct_url("age_rating", true), $query)->count;
        }

        /**
         * Age Rating Descriptors
         *
         * @link https://api-docs.igdb.com/#age-rating-content-description
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function age_rating_content_description($query) {
            return $this->_exec_query($this->construct_url("age_rating_content_description", false), $query);
        }

        /**
         * Age Rating Descriptors
         *
         * @link https://api-docs.igdb.com/#age-rating-content-description
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function age_rating_content_description_count($query = "") {
            return $this->_exec_query($this->construct_url("age_rating_content_description", true), $query)->count;
        }

        /**
         * Alternative and international game titles
         *
         * @link https://api-docs.igdb.com/#alternative-name
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function alternative_name($query) {
            return $this->_exec_query($this->construct_url("alternative_name", false), $query);
        }

        /**
         * Alternative and international game titles
         *
         * @link https://api-docs.igdb.com/#alternative-name
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function alternative_name_count($query = "") {
            return $this->_exec_query($this->construct_url("alternative_name", true), $query)->count;
        }

        /**
         * official artworks (resolution and aspect ratio may vary)
         *
         * @link https://api-docs.igdb.com/#artwork
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function artwork($query) {
            return $this->_exec_query($this->construct_url("artwork", false), $query);
        }

        /**
         * official artworks (resolution and aspect ratio may vary)
         *
         * @link https://api-docs.igdb.com/#artwork
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function artwork_count($query = "") {
            return $this->_exec_query($this->construct_url("artwork", true), $query)->count;
        }

        /**
         * Video game characters
         *
         * @link https://api-docs.igdb.com/#character
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function character($query) {
            return $this->_exec_query($this->construct_url("character", false), $query);
        }

        /**
         * Video game characters
         *
         * @link https://api-docs.igdb.com/#character
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function character_count($query = "") {
            return $this->_exec_query($this->construct_url("character", true), $query)->count;
        }

        /**
         * Images depicting game characters
         *
         * @link https://api-docs.igdb.com/#character-mug-shot
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function character_mug_shot($query) {
            return $this->_exec_query($this->construct_url("character_mug_shot", false), $query);
        }

        /**
         * Images depicting game characters
         *
         * @link https://api-docs.igdb.com/#character-mug-shot
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function character_mug_shot_count($query = "") {
            return $this->_exec_query($this->construct_url("character_mug_shot", true), $query)->count;
        }

        /**
         * Collection, AKA Series
         *
         * @link https://api-docs.igdb.com/#collection
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function collection($query) {
            return $this->_exec_query($this->construct_url("collection", false), $query);
        }

        /**
         * Collection, AKA Series
         *
         * @link https://api-docs.igdb.com/#collection
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function collection_count($query = "") {
            return $this->_exec_query($this->construct_url("collection", true), $query)->count;
        }

        /**
         * The Collection Memberships.
         *
         * @link https://api-docs.igdb.com/#collection-membership
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function collection_membership($query) {
            return $this->_exec_query($this->construct_url("collection_membership", false), $query);
        }

        /**
         * The Collection Memberships.
         *
         * @link https://api-docs.igdb.com/#collection-membership
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function collection_membership_count($query = "") {
            return $this->_exec_query($this->construct_url("collection_membership", true), $query)->count;
        }

        /**
         * Enums for collection membership types.
         *
         * @link https://api-docs.igdb.com/#collection-membership-type
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function collection_membership_type($query) {
            return $this->_exec_query($this->construct_url("collection_membership_type", false), $query);
        }

        /**
         * Enums for collection membership types.
         *
         * @link https://api-docs.igdb.com/#collection-membership-type
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function collection_membership_type_count($query = "") {
            return $this->_exec_query($this->construct_url("collection_membership_type", true), $query)->count;
        }

        /**
         * Describes Relationship between Collections.
         *
         * @link https://api-docs.igdb.com/#collection-relation
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function collection_relation($query) {
            return $this->_exec_query($this->construct_url("collection_relation", false), $query);
        }

        /**
         * Describes Relationship between Collections.
         *
         * @link https://api-docs.igdb.com/#collection-relation
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function collection_relation_count($query = "") {
            return $this->_exec_query($this->construct_url("collection_relation", true), $query)->count;
        }

        /**
         * Collection Relation Types
         *
         * @link https://api-docs.igdb.com/#collection-relation-type
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function collection_relation_type($query) {
            return $this->_exec_query($this->construct_url("collection_relation_type", false), $query);
        }

        /**
         * Collection Relation Types
         *
         * @link https://api-docs.igdb.com/#collection-relation-type
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function collection_relation_type_count($query = "") {
            return $this->_exec_query($this->construct_url("collection_relation_type", true), $query)->count;
        }

        /**
         * Enums for collection types.
         *
         * @link https://api-docs.igdb.com/#collection-type
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function collection_type($query) {
            return $this->_exec_query($this->construct_url("collection_type", false), $query);
        }

        /**
         * Enums for collection types.
         *
         * @link https://api-docs.igdb.com/#collection-type
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function collection_type_count($query = "") {
            return $this->_exec_query($this->construct_url("collection_type", true), $query)->count;
        }

        /**
         * Video game companies. Both publishers &amp; developers
         *
         * @link https://api-docs.igdb.com/#company
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function company($query) {
            return $this->_exec_query($this->construct_url("company", false), $query);
        }

        /**
         * Video game companies. Both publishers &amp; developers
         *
         * @link https://api-docs.igdb.com/#company
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function company_count($query = "") {
            return $this->_exec_query($this->construct_url("company", true), $query)->count;
        }

        /**
         * The logos of developers and publishers
         *
         * @link https://api-docs.igdb.com/#company-logo
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function company_logo($query) {
            return $this->_exec_query($this->construct_url("company_logo", false), $query);
        }

        /**
         * The logos of developers and publishers
         *
         * @link https://api-docs.igdb.com/#company-logo
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function company_logo_count($query = "") {
            return $this->_exec_query($this->construct_url("company_logo", true), $query)->count;
        }

        /**
         * Company Website
         *
         * @link https://api-docs.igdb.com/#company-website
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function company_website($query) {
            return $this->_exec_query($this->construct_url("company_website", false), $query);
        }

        /**
         * Company Website
         *
         * @link https://api-docs.igdb.com/#company-website
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function company_website_count($query = "") {
            return $this->_exec_query($this->construct_url("company_website", true), $query)->count;
        }

        /**
         * The cover art of games
         *
         * @link https://api-docs.igdb.com/#cover
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function cover($query) {
            return $this->_exec_query($this->construct_url("cover", false), $query);
        }

        /**
         * The cover art of games
         *
         * @link https://api-docs.igdb.com/#cover
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function cover_count($query = "") {
            return $this->_exec_query($this->construct_url("cover", true), $query)->count;
        }

        /**
         * Gaming event like GamesCom, Tokyo Game Show, PAX or GSL
         *
         * @link https://api-docs.igdb.com/#event
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function event($query) {
            return $this->_exec_query($this->construct_url("event", false), $query);
        }

        /**
         * Gaming event like GamesCom, Tokyo Game Show, PAX or GSL
         *
         * @link https://api-docs.igdb.com/#event
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function event_count($query = "") {
            return $this->_exec_query($this->construct_url("event", true), $query)->count;
        }

        /**
         * Logo for the event
         *
         * @link https://api-docs.igdb.com/#event-logo
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function event_logo($query) {
            return $this->_exec_query($this->construct_url("event_logo", false), $query);
        }

        /**
         * Logo for the event
         *
         * @link https://api-docs.igdb.com/#event-logo
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function event_logo_count($query = "") {
            return $this->_exec_query($this->construct_url("event_logo", true), $query)->count;
        }

        /**
         * Urls related to the event like twitter, facebook and youtube
         *
         * @link https://api-docs.igdb.com/#event-network
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function event_network($query) {
            return $this->_exec_query($this->construct_url("event_network", false), $query);
        }

        /**
         * Urls related to the event like twitter, facebook and youtube
         *
         * @link https://api-docs.igdb.com/#event-network
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function event_network_count($query = "") {
            return $this->_exec_query($this->construct_url("event_network", true), $query)->count;
        }

        /**
         * Game IDs on other services
         *
         * @link https://api-docs.igdb.com/#external-game
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function external_game($query) {
            return $this->_exec_query($this->construct_url("external_game", false), $query);
        }

        /**
         * Game IDs on other services
         *
         * @link https://api-docs.igdb.com/#external-game
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function external_game_count($query = "") {
            return $this->_exec_query($this->construct_url("external_game", true), $query)->count;
        }

        /**
         * A list of video game franchises such as Star Wars.
         *
         * @link https://api-docs.igdb.com/#franchise
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function franchise($query) {
            return $this->_exec_query($this->construct_url("franchise", false), $query);
        }

        /**
         * A list of video game franchises such as Star Wars.
         *
         * @link https://api-docs.igdb.com/#franchise
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function franchise_count($query = "") {
            return $this->_exec_query($this->construct_url("franchise", true), $query)->count;
        }

        /**
         * Video Games!
         *
         * @link https://api-docs.igdb.com/#game
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function game($query) {
            return $this->_exec_query($this->construct_url("game", false), $query);
        }

        /**
         * Video Games!
         *
         * @link https://api-docs.igdb.com/#game
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function game_count($query = "") {
            return $this->_exec_query($this->construct_url("game", true), $query)->count;
        }

        /**
         * Video game engines such as unreal engine.
         *
         * @link https://api-docs.igdb.com/#game-engine
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function game_engine($query) {
            return $this->_exec_query($this->construct_url("game_engine", false), $query);
        }

        /**
         * Video game engines such as unreal engine.
         *
         * @link https://api-docs.igdb.com/#game-engine
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function game_engine_count($query = "") {
            return $this->_exec_query($this->construct_url("game_engine", true), $query)->count;
        }

        /**
         * The logos of game engines
         *
         * @link https://api-docs.igdb.com/#game-engine-logo
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function game_engine_logo($query) {
            return $this->_exec_query($this->construct_url("game_engine_logo", false), $query);
        }

        /**
         * The logos of game engines
         *
         * @link https://api-docs.igdb.com/#game-engine-logo
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function game_engine_logo_count($query = "") {
            return $this->_exec_query($this->construct_url("game_engine_logo", true), $query)->count;
        }

        /**
         * Game localization for a game
         *
         * @link https://api-docs.igdb.com/#game-localization
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function game_localization($query) {
            return $this->_exec_query($this->construct_url("game_localization", false), $query);
        }

        /**
         * Game localization for a game
         *
         * @link https://api-docs.igdb.com/#game-localization
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function game_localization_count($query = "") {
            return $this->_exec_query($this->construct_url("game_localization", true), $query)->count;
        }

        /**
         * Single player, Multiplayer etc
         *
         * @link https://api-docs.igdb.com/#game-mode
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function game_mode($query) {
            return $this->_exec_query($this->construct_url("game_mode", false), $query);
        }

        /**
         * Single player, Multiplayer etc
         *
         * @link https://api-docs.igdb.com/#game-mode
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function game_mode_count($query = "") {
            return $this->_exec_query($this->construct_url("game_mode", true), $query)->count;
        }

        /**
         * Details about game editions and versions.
         *
         * @link https://api-docs.igdb.com/#game-version
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function game_version($query) {
            return $this->_exec_query($this->construct_url("game_version", false), $query);
        }

        /**
         * Details about game editions and versions.
         *
         * @link https://api-docs.igdb.com/#game-version
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function game_version_count($query = "") {
            return $this->_exec_query($this->construct_url("game_version", true), $query)->count;
        }

        /**
         * Features and descriptions of what makes each version&#x2F;edition different from the main game
         *
         * @link https://api-docs.igdb.com/#game-version-feature
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function game_version_feature($query) {
            return $this->_exec_query($this->construct_url("game_version_feature", false), $query);
        }

        /**
         * Features and descriptions of what makes each version&#x2F;edition different from the main game
         *
         * @link https://api-docs.igdb.com/#game-version-feature
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function game_version_feature_count($query = "") {
            return $this->_exec_query($this->construct_url("game_version_feature", true), $query)->count;
        }

        /**
         * The bool&#x2F;text value of the feature
         *
         * @link https://api-docs.igdb.com/#game-version-feature-value
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function game_version_feature_value($query) {
            return $this->_exec_query($this->construct_url("game_version_feature_value", false), $query);
        }

        /**
         * The bool&#x2F;text value of the feature
         *
         * @link https://api-docs.igdb.com/#game-version-feature-value
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function game_version_feature_value_count($query = "") {
            return $this->_exec_query($this->construct_url("game_version_feature_value", true), $query)->count;
        }

        /**
         * A video associated with a game
         *
         * @link https://api-docs.igdb.com/#game-video
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function game_video($query) {
            return $this->_exec_query($this->construct_url("game_video", false), $query);
        }

        /**
         * A video associated with a game
         *
         * @link https://api-docs.igdb.com/#game-video
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function game_video_count($query = "") {
            return $this->_exec_query($this->construct_url("game_video", true), $query)->count;
        }

        /**
         * Genres of video game
         *
         * @link https://api-docs.igdb.com/#genre
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function genre($query) {
            return $this->_exec_query($this->construct_url("genre", false), $query);
        }

        /**
         * Genres of video game
         *
         * @link https://api-docs.igdb.com/#genre
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function genre_count($query = "") {
            return $this->_exec_query($this->construct_url("genre", true), $query)->count;
        }

        /**
         *
         *
         * @link https://api-docs.igdb.com/#involved-company
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function involved_company($query) {
            return $this->_exec_query($this->construct_url("involved_company", false), $query);
        }

        /**
         *
         *
         * @link https://api-docs.igdb.com/#involved-company
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function involved_company_count($query = "") {
            return $this->_exec_query($this->construct_url("involved_company", true), $query)->count;
        }

        /**
         * Keywords are words or phrases that get tagged to a game such as &quot;world war 2&quot; or &quot;steampunk&quot;.
         *
         * @link https://api-docs.igdb.com/#keyword
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function keyword($query) {
            return $this->_exec_query($this->construct_url("keyword", false), $query);
        }

        /**
         * Keywords are words or phrases that get tagged to a game such as &quot;world war 2&quot; or &quot;steampunk&quot;.
         *
         * @link https://api-docs.igdb.com/#keyword
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function keyword_count($query = "") {
            return $this->_exec_query($this->construct_url("keyword", true), $query)->count;
        }

        /**
         * Games can be played with different languages for voice acting, subtitles, or the interface language.
         *
         * @link https://api-docs.igdb.com/#language-support
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function language_support($query) {
            return $this->_exec_query($this->construct_url("language_support", false), $query);
        }

        /**
         * Games can be played with different languages for voice acting, subtitles, or the interface language.
         *
         * @link https://api-docs.igdb.com/#language-support
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function language_support_count($query = "") {
            return $this->_exec_query($this->construct_url("language_support", true), $query)->count;
        }

        /**
         * Language Support Types contains the identifiers for the support types that Language Support uses.
         *
         * @link https://api-docs.igdb.com/#language-support-type
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function language_support_type($query) {
            return $this->_exec_query($this->construct_url("language_support_type", false), $query);
        }

        /**
         * Language Support Types contains the identifiers for the support types that Language Support uses.
         *
         * @link https://api-docs.igdb.com/#language-support-type
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function language_support_type_count($query = "") {
            return $this->_exec_query($this->construct_url("language_support_type", true), $query)->count;
        }

        /**
         * Languages that are used in the Language Support endpoint.
         *
         * @link https://api-docs.igdb.com/#language
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function language($query) {
            return $this->_exec_query($this->construct_url("language", false), $query);
        }

        /**
         * Languages that are used in the Language Support endpoint.
         *
         * @link https://api-docs.igdb.com/#language
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function language_count($query = "") {
            return $this->_exec_query($this->construct_url("language", true), $query)->count;
        }

        /**
         * Data about the supported multiplayer types
         *
         * @link https://api-docs.igdb.com/#multiplayer-mode
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function multiplayer_mode($query) {
            return $this->_exec_query($this->construct_url("multiplayer_mode", false), $query);
        }

        /**
         * Data about the supported multiplayer types
         *
         * @link https://api-docs.igdb.com/#multiplayer-mode
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function multiplayer_mode_count($query = "") {
            return $this->_exec_query($this->construct_url("multiplayer_mode", true), $query)->count;
        }

        /**
         * Social networks related to the event like twitter, facebook and youtube
         *
         * @link https://api-docs.igdb.com/#network-type
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function network_type($query) {
            return $this->_exec_query($this->construct_url("network_type", false), $query);
        }

        /**
         * Social networks related to the event like twitter, facebook and youtube
         *
         * @link https://api-docs.igdb.com/#network-type
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function network_type_count($query = "") {
            return $this->_exec_query($this->construct_url("network_type", true), $query)->count;
        }

        /**
         * The hardware used to run the game or game delivery network
         *
         * @link https://api-docs.igdb.com/#platform
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function platform($query) {
            return $this->_exec_query($this->construct_url("platform", false), $query);
        }

        /**
         * The hardware used to run the game or game delivery network
         *
         * @link https://api-docs.igdb.com/#platform
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function platform_count($query = "") {
            return $this->_exec_query($this->construct_url("platform", true), $query)->count;
        }

        /**
         * A collection of closely related platforms
         *
         * @link https://api-docs.igdb.com/#platform-family
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function platform_family($query) {
            return $this->_exec_query($this->construct_url("platform_family", false), $query);
        }

        /**
         * A collection of closely related platforms
         *
         * @link https://api-docs.igdb.com/#platform-family
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function platform_family_count($query = "") {
            return $this->_exec_query($this->construct_url("platform_family", true), $query)->count;
        }

        /**
         * Logo for a platform
         *
         * @link https://api-docs.igdb.com/#platform-logo
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function platform_logo($query) {
            return $this->_exec_query($this->construct_url("platform_logo", false), $query);
        }

        /**
         * Logo for a platform
         *
         * @link https://api-docs.igdb.com/#platform-logo
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function platform_logo_count($query = "") {
            return $this->_exec_query($this->construct_url("platform_logo", true), $query)->count;
        }

        /**
         *
         *
         * @link https://api-docs.igdb.com/#platform-version
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function platform_version($query) {
            return $this->_exec_query($this->construct_url("platform_version", false), $query);
        }

        /**
         *
         *
         * @link https://api-docs.igdb.com/#platform-version
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function platform_version_count($query = "") {
            return $this->_exec_query($this->construct_url("platform_version", true), $query)->count;
        }

        /**
         * A platform developer
         *
         * @link https://api-docs.igdb.com/#platform-version-company
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function platform_version_company($query) {
            return $this->_exec_query($this->construct_url("platform_version_company", false), $query);
        }

        /**
         * A platform developer
         *
         * @link https://api-docs.igdb.com/#platform-version-company
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function platform_version_company_count($query = "") {
            return $this->_exec_query($this->construct_url("platform_version_company", true), $query)->count;
        }

        /**
         * A handy endpoint that extends platform release dates. Used to dig deeper into release dates, platforms and versions.
         *
         * @link https://api-docs.igdb.com/#platform-version-release-date
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function platform_version_release_date($query) {
            return $this->_exec_query($this->construct_url("platform_version_release_date", false), $query);
        }

        /**
         * A handy endpoint that extends platform release dates. Used to dig deeper into release dates, platforms and versions.
         *
         * @link https://api-docs.igdb.com/#platform-version-release-date
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function platform_version_release_date_count($query = "") {
            return $this->_exec_query($this->construct_url("platform_version_release_date", true), $query)->count;
        }

        /**
         * The main website for the platform
         *
         * @link https://api-docs.igdb.com/#platform-website
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function platform_website($query) {
            return $this->_exec_query($this->construct_url("platform_website", false), $query);
        }

        /**
         * The main website for the platform
         *
         * @link https://api-docs.igdb.com/#platform-website
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function platform_website_count($query = "") {
            return $this->_exec_query($this->construct_url("platform_website", true), $query)->count;
        }

        /**
         * Player perspectives describe the view&#x2F;perspective of the player in a video game.
         *
         * @link https://api-docs.igdb.com/#player-perspective
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function player_perspective($query) {
            return $this->_exec_query($this->construct_url("player_perspective", false), $query);
        }

        /**
         * Player perspectives describe the view&#x2F;perspective of the player in a video game.
         *
         * @link https://api-docs.igdb.com/#player-perspective
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function player_perspective_count($query = "") {
            return $this->_exec_query($this->construct_url("player_perspective", true), $query)->count;
        }

        /**
         * Region for game localization
         *
         * @link https://api-docs.igdb.com/#region
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function region($query) {
            return $this->_exec_query($this->construct_url("region", false), $query);
        }

        /**
         * Region for game localization
         *
         * @link https://api-docs.igdb.com/#region
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function region_count($query = "") {
            return $this->_exec_query($this->construct_url("region", true), $query)->count;
        }

        /**
         * A handy endpoint that extends game release dates. Used to dig deeper into release dates, platforms and versions.
         *
         * @link https://api-docs.igdb.com/#release-date
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function release_date($query) {
            return $this->_exec_query($this->construct_url("release_date", false), $query);
        }

        /**
         * A handy endpoint that extends game release dates. Used to dig deeper into release dates, platforms and versions.
         *
         * @link https://api-docs.igdb.com/#release-date
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function release_date_count($query = "") {
            return $this->_exec_query($this->construct_url("release_date", true), $query)->count;
        }

        /**
         * An endpoint to provide definition of all of the current release date statuses.
         *
         * @link https://api-docs.igdb.com/#release-date-status
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function release_date_status($query) {
            return $this->_exec_query($this->construct_url("release_date_status", false), $query);
        }

        /**
         * An endpoint to provide definition of all of the current release date statuses.
         *
         * @link https://api-docs.igdb.com/#release-date-status
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function release_date_status_count($query = "") {
            return $this->_exec_query($this->construct_url("release_date_status", true), $query)->count;
        }

        /**
         * Screenshots of games
         *
         * @link https://api-docs.igdb.com/#screenshot
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function screenshot($query) {
            return $this->_exec_query($this->construct_url("screenshot", false), $query);
        }

        /**
         * Screenshots of games
         *
         * @link https://api-docs.igdb.com/#screenshot
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function screenshot_count($query = "") {
            return $this->_exec_query($this->construct_url("screenshot", true), $query)->count;
        }

        /**
         *
         *
         * @link https://api-docs.igdb.com/#search
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function search($query) {
            return $this->_exec_query($this->construct_url("search", false), $query);
        }

        /**
         *
         *
         * @link https://api-docs.igdb.com/#search
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function search_count($query = "") {
            return $this->_exec_query($this->construct_url("search", true), $query)->count;
        }

        /**
         * Video game themes
         *
         * @link https://api-docs.igdb.com/#theme
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function theme($query) {
            return $this->_exec_query($this->construct_url("theme", false), $query);
        }

        /**
         * Video game themes
         *
         * @link https://api-docs.igdb.com/#theme
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function theme_count($query = "") {
            return $this->_exec_query($this->construct_url("theme", true), $query)->count;
        }

        /**
         * A website url, usually associated with a game
         *
         * @link https://api-docs.igdb.com/#website
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result array entities from IGDB matching the query
         */
        public function website($query) {
            return $this->_exec_query($this->construct_url("website", false), $query);
        }

        /**
         * A website url, usually associated with a game
         *
         * @link https://api-docs.igdb.com/#website
         *
         * @param $query ( string | IGDBQueryBuilder ) either an apicalypse string or IGDBQueryBuilder instance
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @throws IGDBInvalidParameterException if $query is not a string or an IGDBQueryBuilder instance
         * @return $result integer number of entities from IGDB matching the query
         */
        public function website_count($query = "") {
            return $this->_exec_query($this->construct_url("website", true), $query)->count;
        }
    }
?>
