<?php

    /**
     * Internet Game Database API Wrapper
     *
     * Fethching data from IGDB's database.
     * Compatible with IGDB Api v4
     *
     * @version 4.0.1
     * @author Enisz Abdalla <enisz87@gmail.com>
     * @link https://github.com/enisz/igdb
     */

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
         * API Url of IGDB
         */
        private $api_url = "https://api.igdb.com/v4";

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
            curl_setopt($this->curl_handler, CURLOPT_POST, true);
            curl_setopt($this->curl_handler, CURLOPT_HTTPHEADER, array(
                "Client-ID: $this->client_id",
                "Authorization: Bearer $this->access_token"
            ));
        }

        /**
         * Parsing the Apicalypse query string from the query
         *
         * @throws Exception In case of missing parameters
         * @throws Exception When invalid parameters passed
         * @throws Exception If a non-array parameter is passed to the method
         * @param $query A query to parse
         */
        public function apicalypse($query) {
            if(!is_array($query)) {
                throw new Exception("The query is not an array!");
            }
            $fields = array('search', 'fields', 'exclude', 'where', 'limit', 'offset', 'sort');
            $apicalypse = '';

            // id provided; push it in the where statement
            if(!array_key_exists('where', $query) && array_key_exists('id', $query)) {
                if(!is_array($query['id'])) {
                    $query['id'] = array_map( function ($item) { return trim($item); }, explode(',', $query['id']));
                }

                $query['where'] = array(
                    'field' => 'id',
                    'postfix' => '=',
                    'value' => (count($query['id']) > 1 ? '(' : '') . implode(',', $query['id']) . (count($query['id']) > 1 ? ')' : '')
                );

                unset($query['id']);
            }

            foreach($fields as $parameter) {
                if(array_key_exists($parameter, $query)) {
                    switch($parameter) {
                        case 'search':
                            $apicalypse .= 'search "' . $query[$parameter] . '"';
                        break;

                        case 'fields':
                        case 'exclude':
                            if(!is_array($query[$parameter])) {
                                $query[$parameter] = array_map( function($item) { return trim($item); }, explode(',', $query[$parameter]));
                            }

                            $apicalypse .= $parameter . ' ' . implode(',', $query[$parameter]);
                        break;

                        case 'where':
                            if(is_string($query[$parameter])) { // as string
                                $params = explode(' ', $query[$parameter]);

                                if(count($params) != 3) {
                                    throw new Exception('when "where" statement is passed as a string, it has to contain a field name, a postfix and a value separated by spaces!');
                                }

                                $query[$parameter] = array(
                                    array(
                                        'field' => $params[0],
                                        'postfix' => $params[1],
                                        'value' => $params[2]
                                    )
                                );
                            } else if(is_array($query[$parameter]) && array_key_exists(0, $query[$parameter]) && is_string($query[$parameter][0]))  { // array of strings
                                $new_value = array();

                                foreach($query[$parameter] as $param) {
                                    $params = explode(' ', $param);

                                    if(count($params) != 3) {
                                        throw new Exception('when "where" statement is passed as a string, it has to contain a field name, a postfix and a value separated by spaces!');
                                    }

                                    array_push($new_value, array(
                                        'field' => $params[0],
                                        'postfix' => $params[1],
                                        'value' => $params[2]
                                    ));
                                }

                                $query[$parameter] = $new_value;
                            } else if(is_array($query[$parameter]) && array_key_exists('field', $query[$parameter])) { // filter array
                                $query[$parameter] = array(
                                    array(
                                        'field' => $query[$parameter]['field'],
                                        'postfix' => $query[$parameter]['postfix'],
                                        'value' => $query[$parameter]['value']
                                    )
                                );
                            } else if(is_array($query[$parameter]) && array_key_exists(0, $query[$parameter]) && array_key_exists('field', $query[$parameter][0])) { // array of filter arrays
                                // data is in correct format, nothing to do here
                            } else {
                                throw new Exception('"where" statement in the query contains invalid data!');
                            }

                            // id provided; push it in the where statement
                            if(array_key_exists('id', $query)) {
                                if(!is_array($query['id'])) {
                                    $query['id'] = array_map( function ($item) { return trim($item); }, explode(',', $query['id']));
                                }

                                if(!array_key_exists('where', $query)) {
                                    $query['where'] = array();
                                }

                                array_unshift($query['where'], array(
                                    'field' => 'id',
                                    'postfix' => '=',
                                    'value' => (count($query['id']) > 1 ? '(' : '') . implode(',', $query['id']) . (count($query['id']) > 1 ? ')' : '')
                                ));
                            }

                            $items = array();

                            foreach($query[$parameter] as $filter) {
                                if(!array_key_exists('field', $filter)) {
                                    throw new Exception('"field" parameter is missing from the where statement!');
                                }

                                if(!array_key_exists('postfix', $filter)) {
                                    throw new Exception('"postfix" parameter is missing from the where statement!');
                                }

                                if(!array_key_exists('value', $filter)) {
                                    throw new Exception('"value" parameter is missing from the where statement!');
                                }

                                $available_postfixes = array('=', '!=', '>', '>=', '<', '<=', '~');

                                if(!in_array($filter['postfix'], $available_postfixes)) {
                                    throw new Exception('invalid postfix "' . $filter['postfix'] . '" in where statement!');
                                }

                                // is a number
                                if(is_numeric($filter['value'])) {
                                    $need_quote = false;
                                } else if ($filter['value'] == "null") { // is null
                                    $need_quote = false;
                                } else if (strpos($filter['value'], "{") !== false || strpos($filter['value'], "(") !== false) { // range
                                    $need_quote = false;
                                } else { // is a string
                                    $need_quote = true;
                                }

                                array_push($items, $filter['field'] . ' ' . $filter['postfix'] . ' ' . ($need_quote ? '"' : '') . $filter['value'] . ($need_quote ? '"' : ''));
                            }

                            $apicalypse .= 'where ' . implode(' & ', $items);
                        break;

                        case 'limit':
                        case 'offset':
                            $value = $query[$parameter];

                            if($parameter == 'limit' && ($value < 1 || $value > 500)) {
                                throw new Exception('Limit value must be between 1 and 500!');
                            }

                            if($parameter == 'offset' && ($value < 0)) {
                                throw new Exception('Offset value must be 0 or above!');
                            }

                            $apicalypse .= $parameter . ' ' . $value;
                        break;

                        case 'sort':
                            $available_directions = array('asc', 'desc');

                            if(is_array($query[$parameter])) {
                                // field parameter is missing
                                if(!array_key_exists('field', $query[$parameter])) {
                                    throw new Exception('"field" parameter is missing from the sort statement!');
                                }

                                // order parameter is missing
                                if(!array_key_exists('direction', $query[$parameter])) {
                                    throw new Exception('"direction" parameter is missing from the sort statement!');
                                }

                                // order parameter is invalid
                                if(!in_array($query[$parameter]['direction'], $available_directions)) {
                                    throw new Exception('the value of the "direction" field is invalid (' . $query[$parameter] . ')! it has to be either asc or desc!');
                                }

                                $apicalypse .= $parameter . ' ' . $query[$parameter]['field'] . ' ' . $query[$parameter]['direction'];
                            } else {
                                $params = explode(' ', $query[$parameter]);

                                if(count($params) != 2) {
                                    throw new Exception('sort parameter must contain a field name and the sorting direction separated with a space!');
                                }

                                if(!in_array($params[1], $available_directions)) {
                                    throw new Exception('the direction of sorting must be either "asc" or "desc"!');
                                }

                                $apicalypse .= $parameter . ' ' . $params[0] . ' ' . $params[1];
                            }

                        break;
                    }

                    $apicalypse .= ";\n";
                }
            }

            return trim($apicalypse);
        }

        /**
         * Return the request details of the most recent query
         */
        public function get_request_info() {
            return $this->request_info;
        }

        /**
         * Executes the query against IGDB API.
         * Returns an array of objects decoded from IGDB JSON response or throws Exception in case of error
         *
         * @throws Exception in case of closed CURL session
         * @throws Exception if the response code is any other than 200
         * @param $url ( string ) The url of the endpoint
         * @param $query ( array | string ) The query to send
         * @return $result ( array ) The response objects from IGDB in an array.
         */
        private function _exec_query($url, $query) {
            // Throw Exception if CURL handler is null (closed)
            if(is_null($this->curl_handler)) {
                throw new Exception('CURL session is closed!');
            }

            // Set the request URL
            curl_setopt($this->curl_handler, CURLOPT_URL, $url);

            // Set the body of the request
            curl_setopt($this->curl_handler, CURLOPT_POSTFIELDS, is_array($query) ? $this->apicalypse($query) : $query);

            // Executing the request
            $result = json_decode(curl_exec($this->curl_handler));

            // Getting request information
            $this->request_info = curl_getinfo($this->curl_handler);

            // If there were errors
            if($this->request_info['http_code'] != 200) {
                if(!is_array($result) && property_exists($result, "Message")) {
                    $error_message = $result->Message;
                } else if(property_exists($result[0], 'cause')) {
                    $error_message = $result[0]->cause;
                } else if (property_exists($result[0], "title")) {
                    $error_message = $result[0]->title;
                } else {
                    $error_message = "unknown error";
                }

                throw new Exception('Error ' . $this->request_info['http_code'] . ': ' . $error_message);
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
         * Reinitialize the CURL session. Simply calls the _init_curl private method.
         */
        public function curl_reinit() {
            if(is_null($this->curl_handler)) {
                $this->_curl_init();
            }
        }

        /**
         * Constructing the endpoint url for the request
         * @param $endpoint (string ) the endpoint to execute the query against
         * @param $count ( boolean ) whether a record count, or the records are requested
         */
        private function _construct_url($endpoint, $count) {
            return rtrim($this->api_url, '/') . '/' . $endpoint . ($count ? '/count' : '');
        }

        /**
         * Fetch data from IGDB using Age Rating Content Description endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#age-rating-content-description
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function age_rating_content_description($query, $count = false) {
            return $this->_exec_query($this->_construct_url("age_rating_content_descriptions", $count), $query);
        }

        /**
         * Fetch data from IGDB using Age Rating endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#age-rating
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function age_rating($query, $count = false) {
            return $this->_exec_query($this->_construct_url("age_ratings", $count), $query);
        }

        /**
         * Fetch data from IGDB using Alternative Name endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#alternative-name
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function alternative_name($query, $count = false) {
            return $this->_exec_query($this->_construct_url("alternative_names", $count), $query);
        }

        /**
         * Fetch data from IGDB using Artwork endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#artwork
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function artwork($query, $count = false) {
            return $this->_exec_query($this->_construct_url("artworks", $count), $query);
        }

        /**
         * Fetch data from IGDB using Character Mug Shot endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#character-mug-shot
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function character_mug_shot($query, $count = false) {
            return $this->_exec_query($this->_construct_url("character_mug_shots", $count), $query);
        }

        /**
         * Fetch data from IGDB using Character endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#character
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function character($query, $count = false) {
            return $this->_exec_query($this->_construct_url("characters", $count), $query);
        }

        /**
         * Fetch data from IGDB using Collection endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#collection
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function collection($query, $count = false) {
            return $this->_exec_query($this->_construct_url("collections", $count), $query);
        }

        /**
         * Fetch data from IGDB using Company Logo endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#company-logo
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function company_logo($query, $count = false) {
            return $this->_exec_query($this->_construct_url("company_logos", $count), $query);
        }

        /**
         * Fetch data from IGDB using Company Website endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#company-website
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function company_website($query, $count = false) {
            return $this->_exec_query($this->_construct_url("company_websites", $count), $query);
        }

        /**
         * Fetch data from IGDB using Company endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#company
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function company($query, $count = false) {
            return $this->_exec_query($this->_construct_url("companies", $count), $query);
        }

        /**
         * Fetch data from IGDB using Cover endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#cover
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function cover($query, $count = false) {
            return $this->_exec_query($this->_construct_url("covers", $count), $query);
        }

        /**
         * Fetch data from IGDB using External Game endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#external-game
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function external_game($query, $count = false) {
            return $this->_exec_query($this->_construct_url("external_games", $count), $query);
        }

        /**
         * Fetch data from IGDB using Franchise endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#franchise
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function franchise($query, $count = false) {
            return $this->_exec_query($this->_construct_url("franchises", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Engine Logo endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-engine-logo
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_engine_logo($query, $count = false) {
            return $this->_exec_query($this->_construct_url("game_engine_logos", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Engine endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-engine
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_engine($query, $count = false) {
            return $this->_exec_query($this->_construct_url("game_engines", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Mode endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-mode
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_mode($query, $count = false) {
            return $this->_exec_query($this->_construct_url("game_modes", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Version Feature Value endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-version-feature-value
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version_feature_value($query, $count = false) {
            return $this->_exec_query($this->_construct_url("game_version_feature_values", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Version Feature endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-version-feature
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version_feature($query, $count = false) {
            return $this->_exec_query($this->_construct_url("game_version_features", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Version endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-version
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version($query, $count = false) {
            return $this->_exec_query($this->_construct_url("game_versions", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game Video endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-video
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_video($query, $count = false) {
            return $this->_exec_query($this->_construct_url("game_videos", $count), $query);
        }

        /**
         * Fetch data from IGDB using Game endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game($query, $count = false) {
            return $this->_exec_query($this->_construct_url("games", $count), $query);
        }

        /**
         * Fetch data from IGDB using Genre endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#genre
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function genre($query, $count = false) {
            return $this->_exec_query($this->_construct_url("genres", $count), $query);
        }

        /**
         * Fetch data from IGDB using Involved Company endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#involved-company
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function involved_company($query, $count = false) {
            return $this->_exec_query($this->_construct_url("involved_companies", $count), $query);
        }

        /**
         * Fetch data from IGDB using Keyword endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#keyword
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function keyword($query, $count = false) {
            return $this->_exec_query($this->_construct_url("keywords", $count), $query);
        }

        /**
         * Fetch data from IGDB using Multiplayer Mode endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#multiplayer-mode
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function multiplayer_mode($query, $count = false) {
            return $this->_exec_query($this->_construct_url("multiplayer_modes", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Family endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-family
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_family($query, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_families", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Logo endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-logo
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_logo($query, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_logos", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Version Company endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-version-company
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version_company($query, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_version_companies", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Version Release Date endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-version-release-date
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version_release_date($query, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_version_release_dates", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Version endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-version
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version($query, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_versions", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform Website endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-website
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_website($query, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_websites", $count), $query);
        }

        /**
         * Fetch data from IGDB using Platform endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform($query, $count = false) {
            return $this->_exec_query($this->_construct_url("platforms", $count), $query);
        }

        /**
         * Fetch data from IGDB using Player Perspective endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#player-perspective
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function player_perspective($query, $count = false) {
            return $this->_exec_query($this->_construct_url("player_perspectives", $count), $query);
        }

        /**
         * Fetch data from IGDB using Release Date endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#release-date
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function release_date($query, $count = false) {
            return $this->_exec_query($this->_construct_url("release_dates", $count), $query);
        }

        /**
         * Fetch data from IGDB using Screenshot endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#screenshot
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function screenshot($query, $count = false) {
            return $this->_exec_query($this->_construct_url("screenshots", $count), $query);
        }

        /**
         * Fetch data from IGDB using Search endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#search
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function search($query, $count = false) {
            return $this->_exec_query($this->_construct_url("search", $count), $query);
        }

        /**
         * Fetch data from IGDB using Theme endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#theme
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function theme($query, $count = false) {
            return $this->_exec_query($this->_construct_url("themes", $count), $query);
        }

        /**
         * Fetch data from IGDB using Website endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#website
         *
         * @param $query ( array ) a query setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function website($query, $count = false) {
            return $this->_exec_query($this->_construct_url("websites", $count), $query);
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
         * @param $endpoint ( string ) The endpoint to send your query to
         * @param $result_name ( string ) A name for the result given by you
         * @param $query ( array | string ) Either an apicalypse string or a query array. If null provided, nothing will be parsed
         * @return $result ( mixed ) The result of the query
         */
        public function mutliquery($endpoint, $result_name, $query = null) {
            return $this->_exec_query(
                $this->_construct_url(
                    "multiquery",
                    false
                ),
                "query $endpoint \"$result_name\" {\n" . (!is_null($query) ? (is_array($query) ? $this->apicalypse($query) : $query) : "") . "\n};"
            );
        }
    }

?>
