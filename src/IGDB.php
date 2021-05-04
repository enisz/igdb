<?php

    /**
     * Internet Game Database API Wrapper
     *
     * Fethching data from IGDB's database.
     * Compatible with IGDB Api v4
     *
     * @version 4.1.0
     * @author Enisz Abdalla <enisz87@gmail.com>
     * @link https://github.com/enisz/igdb
     */

    require_once "IGDBEndpointException.php";

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
         * @param $endpoint - the name of the endpoint. Make sure to use the name of the endpoint
         * @param $count - whether the number of results is required or the result set itself
         * @throws InvalidArgumentException if passed endpoint name is invalid
         * @return string - the constructed URL using the provided parameters
         */
        public function construct_url($endpoint, $count = false) {
            return rtrim($this->api_url, "/") . "/" . $endpoint . ($count ? "/count" : "");
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
            curl_setopt($this->curl_handler, CURLOPT_POSTFIELDS, is_array($query) ? $this->apicalypse($query) : $query);

            // Executing and decoding the request
            $result = json_decode(curl_exec($this->curl_handler));

            // Getting request information
            $this->request_info = curl_getinfo($this->curl_handler);

            // HTTP response code
            $response_code = $this->request_info['http_code'];

            // If there were errors
            if($response_code < 200 || $response_code > 299) {
                $message = "Error " . $response_code;

                if(property_exists($result, "message")) {
                    $message .= ": " . $result->message;
                }

                throw new IGDBEndpointException($message, $response_code);
            }

            return $result;
        }

        /**
         * Closes the CURL handler.
         * After this method is called, the class cannot run any queries against IGDB unless you reinitialize it manually.
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
         * Fetch data from IGDB using Age Rating Content Description endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#age-rating-content-description
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function age_rating_content_description($query, $count = false) {
            return $this->_exec_query($this->construct_url("age_rating_content_descriptions", $count), $query);
        }

        /**
         * Fetch data from IGDB using Age Rating endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#age-rating
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function age_rating($query, $count = false) {
            return $this->_exec_query($this->construct_url("age_ratings", $count), $query);
        }

        /**
         * Fetch data from IGDB using Alternative Name endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#alternative-name
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function alternative_name($query, $count = false) {
            return $this->_exec_query($this->construct_url("alternative_names", $count), $query);
        }

        /**
         * Fetch data from IGDB using Artwork endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#artwork
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function artwork($query, $count = false) {
            return $this->_exec_query($this->construct_url("artworks", $count), $query);
        }

        /**
         * Fetch data from IGDB using Character Mug Shot endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#character-mug-shot
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function character_mug_shot($query, $count = false) {
            return $this->_exec_query($this->construct_url("character_mug_shots", $count), $query);
        }

        /**
         * Fetch data from IGDB using Character endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#character
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function character($query, $count = false) {
            return $this->_exec_query($this->construct_url("characters", $count), $query);
        }

        /**
         * Fetch data from IGDB using Collection endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#collection
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function collection($query, $count = false) {
            return $this->_exec_query($this->construct_url("collections", $count), $query);
        }

        /**
         * Fetch data from IGDB using Company Logo endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#company-logo
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function company_logo($query, $count = false) {
            return $this->_exec_query($this->construct_url("company_logos", $count), $query);
        }

        /**
         * Fetch data from IGDB using Company Website endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#company-website
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function company_website($query, $count = false) {
            return $this->_exec_query($this->construct_url("company_websites", $count), $query);
        }

        /**
         * Fetch data from IGDB using Company endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#company
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function company($query, $count = false) {
            return $this->_exec_query($this->construct_url("companies", $count), $query);
        }

        /**
         * Fetch data from IGDB using Cover endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#cover
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function cover($query, $count = false) {
            return $this->_exec_query($this->construct_url("covers", $count), $query);
        }

        /**
         * Fetch data from IGDB using External Game endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#external-game
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function external_game($query, $count = false) {
            return $this->_exec_query($this->construct_url("external_games", $count), $query);
        }

        /**
         * Fetch data from IGDB using Franchise endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#franchise
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function franchise($query, $count = false) {
            return $this->_exec_query($this->construct_url("franchises", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Engine Logo endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-engine-logo
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_engine_logo($query, $count = false) {
            return $this->_exec_query($this->construct_url("game_engine_logos", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Engine endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-engine
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_engine($query, $count = false) {
            return $this->_exec_query($this->construct_url("game_engines", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Mode endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-mode
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_mode($query, $count = false) {
            return $this->_exec_query($this->construct_url("game_modes", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Version Feature Value endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-version-feature-value
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version_feature_value($query, $count = false) {
            return $this->_exec_query($this->construct_url("game_version_feature_values", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Version Feature endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-version-feature
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version_feature($query, $count = false) {
            return $this->_exec_query($this->construct_url("game_version_features", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Version endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-version
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version($query, $count = false) {
            return $this->_exec_query($this->construct_url("game_versions", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Video endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-video
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game_video($query, $count = false) {
            return $this->_exec_query($this->construct_url("game_videos", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function game($query, $count = false) {
            return $this->_exec_query($this->construct_url("games", $count), $query);
        }

        /**
         * Fetch data from IGDB using Genre endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#genre
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function genre($query, $count = false) {
            return $this->_exec_query($this->construct_url("genres", $count), $query);
        }

        /**
         * Fetch data from IGDB using Involved Company endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#involved-company
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function involved_company($query, $count = false) {
            return $this->_exec_query($this->construct_url("involved_companies", $count), $query);
        }

        /**
         * Fetch data from IGDB using Keyword endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#keyword
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function keyword($query, $count = false) {
            return $this->_exec_query($this->construct_url("keywords", $count), $query);
        }

        /**
         * Fetch data from IGDB using Multiplayer Mode endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#multiplayer-mode
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function multiplayer_mode($query, $count = false) {
            return $this->_exec_query($this->construct_url("multiplayer_modes", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Family endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-family
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_family($query, $count = false) {
            return $this->_exec_query($this->construct_url("platform_families", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Logo endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-logo
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_logo($query, $count = false) {
            return $this->_exec_query($this->construct_url("platform_logos", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Version Company endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-version-company
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version_company($query, $count = false) {
            return $this->_exec_query($this->construct_url("platform_version_companies", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Version Release Date endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-version-release-date
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version_release_date($query, $count = false) {
            return $this->_exec_query($this->construct_url("platform_version_release_dates", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Version endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-version
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version($query, $count = false) {
            return $this->_exec_query($this->construct_url("platform_versions", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Website endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-website
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_website($query, $count = false) {
            return $this->_exec_query($this->construct_url("platform_websites", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function platform($query, $count = false) {
            return $this->_exec_query($this->construct_url("platforms", $count), $query);
        }

        /**
         * Fetch data from IGDB using Player Perspective endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#player-perspective
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function player_perspective($query, $count = false) {
            return $this->_exec_query($this->construct_url("player_perspectives", $count), $query);
        }

        /**
         * Fetch data from IGDB using Release Date endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#release-date
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function release_date($query, $count = false) {
            return $this->_exec_query($this->construct_url("release_dates", $count), $query);
        }

        /**
         * Fetch data from IGDB using Screenshot endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#screenshot
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function screenshot($query, $count = false) {
            return $this->_exec_query($this->construct_url("screenshots", $count), $query);
        }

        /**
         * Fetch data from IGDB using Search endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#search
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function search($query, $count = false) {
            return $this->_exec_query($this->construct_url("search", $count), $query);
        }

        /**
         * Fetch data from IGDB using Theme endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#theme
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function theme($query, $count = false) {
            return $this->_exec_query($this->construct_url("themes", $count), $query);
        }

        /**
         * Fetch data from IGDB using Website endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a <code>count</code> property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#website
         *
         * @param $query ( string ) an apicalypse query string to send to the IGDB server
         * @param $count ( boolean ) whether the method should return the results or their count.
         * @throws IGDBEndpointException if the response code is non successful (successful range is from 200 to 299)
         * @return $result ( array | object ) response from IGDB
         */
        public function website($query, $count = false) {
            return $this->_exec_query($this->construct_url("websites", $count), $query);
        }

    }

?>