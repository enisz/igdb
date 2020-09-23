<?php

    /**
     * Internet Game Database API Wrapper Class
     *
     * Fethching data from IGDB's database.
     * Compatible with IGDB Api v4
     *
     * @version 3.0.0
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
         * Instantiate the IGDB object
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
         * Parsing the Apicalypse query string from the options array
         *
         * @throws Exception In case of missing parameters
         * @throws Exception When invalid parameters passed
         * @param $options The options array to parse
         */
        public function apicalypse($options) {
            $fields = array('search', 'fields', 'exclude', 'where', 'limit', 'offset', 'sort');
            $query = '';

            // id provided; push it in the where statement
            if(!array_key_exists('where', $options) && array_key_exists('id', $options)) {
                if(!is_array($options['id'])) {
                    $options['id'] = array_map( function ($item) { return trim($item); }, explode(',', $options['id']));
                }

                $options['where'] = array(
                    'field' => 'id',
                    'postfix' => '=',
                    'value' => (count($options['id']) > 1 ? '(' : '') . implode(',', $options['id']) . (count($options['id']) > 1 ? ')' : '')
                );

                unset($options['id']);
            }

            foreach($fields as $parameter) {
                if(array_key_exists($parameter, $options)) {
                    switch($parameter) {
                        case 'search':
                            $query .= 'search "' . $options[$parameter] . '"';
                        break;

                        case 'fields':
                        case 'exclude':
                            if(!is_array($options[$parameter])) {
                                $options[$parameter] = array_map( function($item) { return trim($item); }, explode(',', $options[$parameter]));
                            }

                            $query .= $parameter . ' ' . implode(',', $options[$parameter]);
                        break;

                        case 'where':
                            if(is_string($options[$parameter])) { // as string
                                $params = explode(' ', $options[$parameter]);

                                if(count($params) != 3) {
                                    throw new Exception('when "where" statement is passed as a string, it has to contain a field name, a postfix and a value separated by spaces!');
                                }

                                $options[$parameter] = array(
                                    array(
                                        'field' => $params[0],
                                        'postfix' => $params[1],
                                        'value' => $params[2]
                                    )
                                );
                            } else if(is_array($options[$parameter]) && array_key_exists(0, $options[$parameter]) && is_string($options[$parameter][0]))  { // array of strings
                                $new_value = array();

                                foreach($options[$parameter] as $param) {
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

                                $options[$parameter] = $new_value;
                            } else if(is_array($options[$parameter]) && array_key_exists('field', $options[$parameter])) { // filter array
                                $options[$parameter] = array(
                                    array(
                                        'field' => $options[$parameter]['field'],
                                        'postfix' => $options[$parameter]['postfix'],
                                        'value' => $options[$parameter]['value']
                                    )
                                );
                            } else if(is_array($options[$parameter]) && array_key_exists(0, $options[$parameter]) && array_key_exists('field', $options[$parameter][0])) { // array of filter arrays
                                // data is in correct format, nothing to do here
                            } else {
                                throw new Exception('"where" statement in the options array contains invalid data!');
                            }

                            // id provided; push it in the where statement
                            if(array_key_exists('id', $options)) {
                                if(!is_array($options['id'])) {
                                    $options['id'] = array_map( function ($item) { return trim($item); }, explode(',', $options['id']));
                                }

                                if(!array_key_exists('where', $options)) {
                                    $options['where'] = array();
                                }

                                array_unshift($options['where'], array(
                                    'field' => 'id',
                                    'postfix' => '=',
                                    'value' => (count($options['id']) > 1 ? '(' : '') . implode(',', $options['id']) . (count($options['id']) > 1 ? ')' : '')
                                ));
                            }

                            $items = array();

                            foreach($options[$parameter] as $filter) {
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

                            $query .= 'where ' . implode(' & ', $items);
                        break;

                        case 'limit':
                        case 'offset':
                            $value = $options[$parameter];

                            if($parameter == 'limit' && ($value < 1 || $value > 50)) {
                                throw new Exception('Limit value must be between 1 and 50!');
                            }

                            if($parameter == 'offset' && ($value < 0)) {
                                throw new Exception('Offset value must be 0 or above!');
                            }

                            $query .= $parameter . ' ' . $value;
                        break;

                        case 'sort':
                            $available_directions = array('asc', 'desc');

                            if(is_array($options[$parameter])) {
                                // field parameter is missing
                                if(!array_key_exists('field', $options[$parameter])) {
                                    throw new Exception('"field" parameter is missing from the sort statement!');
                                }

                                // order parameter is missing
                                if(!array_key_exists('direction', $options[$parameter])) {
                                    throw new Exception('"direction" parameter is missing from the sort statement!');
                                }

                                // order parameter is invalid
                                if(!in_array($options[$parameter]['direction'], $available_directions)) {
                                    throw new Exception('the value of the "direction" field is invalid (' . $options[$parameter] . ')! it has to be either asc or desc!');
                                }

                                $query .= $parameter . ' ' . $options[$parameter]['field'] . ' ' . $options[$parameter]['direction'];
                            } else {
                                $params = explode(' ', $options[$parameter]);

                                if(count($params) != 2) {
                                    throw new Exception('sort parameter must contain a field name and the sorting direction separated with a space!');
                                }

                                if(!in_array($params[1], $available_directions)) {
                                    throw new Exception('the direction of sorting must be either "asc" or "desc"!');
                                }

                                $query .= $parameter . ' ' . $params[0] . ' ' . $params[1];
                            }

                        break;
                    }

                    $query .= ";\n";
                }
            }

            return trim($query);
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
         * @param $options ( array | string ) Either the options array to convert to an apicalypse string, or an apicalypse string itself
         * @return $result ( array ) The response objects from IGDB in an array.
         */
        private function _exec_query($url, $options) {
            // Throw Exception if CURL handler is null (closed)
            if(is_null($this->curl_handler)) {
                throw new Exception('CURL session is closed!');
            }

            // Set the request URL
            curl_setopt($this->curl_handler, CURLOPT_URL, $url);

            // Set the body of the request
            curl_setopt($this->curl_handler, CURLOPT_POSTFIELDS, is_array($options) ? $this->apicalypse($options) : $options);

            // Executing the request
            $result = json_decode(curl_exec($this->curl_handler));

            // Getting request information
            $this->request_info = curl_getinfo($this->curl_handler);

            // If there were errors
            if($this->request_info['http_code'] != 200) {
                if(property_exists($result[0], 'cause')) {
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
        public function close_handler() {
            curl_close($this->curl_handler);
            $this->curl_handler = null;
        }

        /**
         * Reinitialize the CURL session. Simply calls the _init_curl private method.
         */
        public function reinit_handler() {
            if(is_null($this->curl_handler)) {
                $this->_init_curl();
            }
        }

        /**
         * Constructing the endpoint url for the request
         * @param $endpoint (string ) the endpoint to execute the query against
         * @param $count ( boolean ) whether a count requested or the results
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
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function age_rating_content_description($options, $count = false) {
            return $this->_exec_query($this->_construct_url("age_rating_content_descriptions", $count), $options);
        }

        /**
         * Fetch data from IGDB using Age Rating endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#age-rating
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function age_rating($options, $count = false) {
            return $this->_exec_query($this->_construct_url("age_ratings", $count), $options);
        }

        /**
         * Fetch data from IGDB using Alternative Name endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#alternative-name
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function alternative_name($options, $count = false) {
            return $this->_exec_query($this->_construct_url("alternative_names", $count), $options);
        }

        /**
         * Fetch data from IGDB using Artwork endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#artwork
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function artwork($options, $count = false) {
            return $this->_exec_query($this->_construct_url("artworks", $count), $options);
        }

        /**
         * Fetch data from IGDB using Character Mug Shot endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#character-mug-shot
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function character_mug_shot($options, $count = false) {
            return $this->_exec_query($this->_construct_url("character_mug_shots", $count), $options);
        }

        /**
         * Fetch data from IGDB using Character endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#character
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function character($options, $count = false) {
            return $this->_exec_query($this->_construct_url("characters", $count), $options);
        }

        /**
         * Fetch data from IGDB using Collection endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#collection
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function collection($options, $count = false) {
            return $this->_exec_query($this->_construct_url("collections", $count), $options);
        }

        /**
         * Fetch data from IGDB using Company Logo endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#company-logo
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function company_logo($options, $count = false) {
            return $this->_exec_query($this->_construct_url("company_logos", $count), $options);
        }

        /**
         * Fetch data from IGDB using Company Website endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#company-website
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function company_website($options, $count = false) {
            return $this->_exec_query($this->_construct_url("company_websites", $count), $options);
        }

        /**
         * Fetch data from IGDB using Company endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#company
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function company($options, $count = false) {
            return $this->_exec_query($this->_construct_url("companies", $count), $options);
        }

        /**
         * Fetch data from IGDB using Cover endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#cover
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function cover($options, $count = false) {
            return $this->_exec_query($this->_construct_url("covers", $count), $options);
        }

        /**
         * Fetch data from IGDB using External Game endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#external-game
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function external_game($options, $count = false) {
            return $this->_exec_query($this->_construct_url("external_games", $count), $options);
        }

        /**
         * Fetch data from IGDB using Franchise endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#franchise
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function franchise($options, $count = false) {
            return $this->_exec_query($this->_construct_url("franchises", $count), $options);
        }

        /**
         * Fetch data from IGDB using Game Engine Logo endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-engine-logo
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_engine_logo($options, $count = false) {
            return $this->_exec_query($this->_construct_url("game_engine_logos", $count), $options);
        }

        /**
         * Fetch data from IGDB using Game Engine endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-engine
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_engine($options, $count = false) {
            return $this->_exec_query($this->_construct_url("game_engines", $count), $options);
        }

        /**
         * Fetch data from IGDB using Game Mode endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-mode
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_mode($options, $count = false) {
            return $this->_exec_query($this->_construct_url("game_modes", $count), $options);
        }

        /**
         * Fetch data from IGDB using Game Version Feature Value endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-version-feature-value
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version_feature_value($options, $count = false) {
            return $this->_exec_query($this->_construct_url("game_version_feature_values", $count), $options);
        }

        /**
         * Fetch data from IGDB using Game Version Feature endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-version-feature
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version_feature($options, $count = false) {
            return $this->_exec_query($this->_construct_url("game_version_features", $count), $options);
        }

        /**
         * Fetch data from IGDB using Game Version endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-version
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_version($options, $count = false) {
            return $this->_exec_query($this->_construct_url("game_versions", $count), $options);
        }

        /**
         * Fetch data from IGDB using Game Video endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game-video
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game_video($options, $count = false) {
            return $this->_exec_query($this->_construct_url("game_videos", $count), $options);
        }

        /**
         * Fetch data from IGDB using Game endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#game
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function game($options, $count = false) {
            return $this->_exec_query($this->_construct_url("games", $count), $options);
        }

        /**
         * Fetch data from IGDB using Genre endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#genre
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function genre($options, $count = false) {
            return $this->_exec_query($this->_construct_url("genres", $count), $options);
        }

        /**
         * Fetch data from IGDB using Involved Company endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#involved-company
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function involved_company($options, $count = false) {
            return $this->_exec_query($this->_construct_url("involved_companies", $count), $options);
        }

        /**
         * Fetch data from IGDB using Keyword endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#keyword
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function keyword($options, $count = false) {
            return $this->_exec_query($this->_construct_url("keywords", $count), $options);
        }

        /**
         * Fetch data from IGDB using Multiplayer Mode endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#multiplayer-mode
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function multiplayer_mode($options, $count = false) {
            return $this->_exec_query($this->_construct_url("multiplayer_modes", $count), $options);
        }

        /**
         * Fetch data from IGDB using Platform Family endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-family
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_family($options, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_families", $count), $options);
        }

        /**
         * Fetch data from IGDB using Platform Logo endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-logo
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_logo($options, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_logos", $count), $options);
        }

        /**
         * Fetch data from IGDB using Platform Version Company endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-version-company
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version_company($options, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_version_companies", $count), $options);
        }

        /**
         * Fetch data from IGDB using Platform Version Release Date endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-version-release-date
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version_release_date($options, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_version_release_dates", $count), $options);
        }

        /**
         * Fetch data from IGDB using Platform Version endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-version
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_version($options, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_versions", $count), $options);
        }

        /**
         * Fetch data from IGDB using Platform Website endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform-website
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform_website($options, $count = false) {
            return $this->_exec_query($this->_construct_url("platform_websites", $count), $options);
        }

        /**
         * Fetch data from IGDB using Platform endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#platform
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function platform($options, $count = false) {
            return $this->_exec_query($this->_construct_url("platforms", $count), $options);
        }

        /**
         * Fetch data from IGDB using Player Perspective endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#player-perspective
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function player_perspective($options, $count = false) {
            return $this->_exec_query($this->_construct_url("player_perspectives", $count), $options);
        }

        /**
         * Fetch data from IGDB using Release Date endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#release-date
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function release_date($options, $count = false) {
            return $this->_exec_query($this->_construct_url("release_dates", $count), $options);
        }

        /**
         * Fetch data from IGDB using Screenshot endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#screenshot
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function screenshot($options, $count = false) {
            return $this->_exec_query($this->_construct_url("screenshots", $count), $options);
        }

        /**
         * Fetch data from IGDB using Search endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#search
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function search($options, $count = false) {
            return $this->_exec_query($this->_construct_url("search", $count), $options);
        }

        /**
         * Fetch data from IGDB using Theme endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#theme
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function theme($options, $count = false) {
            return $this->_exec_query($this->_construct_url("themes", $count), $options);
        }

        /**
         * Fetch data from IGDB using Website endpoint.
         * Depending on the @param $count, the method will either return
         *  - an array of objects, containing the matched records from IGDB
         *  - an object containing a count property with the number of matched records
         *
         * @link https://api-docs.igdb.com/#website
         *
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $count ( boolean ) Whether the method should return the results or their count.
         * @return $result ( array | object ) response from IGDB
         */
        public function website($options, $count = false) {
            return $this->_exec_query($this->_construct_url("websites", $count), $options);
        }

        /**
         * Executing a multiquery
         *
         * @link https://api-docs.igdb.com/#multi-query
         *
         * @param $endpoint
         * @param $result_name
         * @param $options
         * @return $result
         */
        public function mutliquery($endpoint, $result_name, $options) {
            return $this->_exec_query($this->_construct_url("multiquery", false), "query $endpoint \"$result_name\" {\n" . (is_array($options) ? $this->apicalypse($options) : $options) . "\n};");
        }
    }

?>
