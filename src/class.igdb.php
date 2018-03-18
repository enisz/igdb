<?php

    /**
     * Internet Game Database Api Class
     * 
     * Fethching data from IGDB's database.
     * 
     * @version 1.0.3
     * @author Enisz Abdalla <enisz87@gmail.com>
     */

    class IGDB {

        // IGDB API url
        private $API_URL = 'https://api-endpoint.igdb.com';

        // IGDB API key
        private $API_KEY;

        // Default limit for queries
        private $DEFAULT_LIMIT = 10;

        // Default offset for queries
        private $DEFAULT_OFFSET = 0;

        // Default fields for queries
        private $DEFAULT_FIELDS = '*';

        // CURL handler
        private $CH;

        /**
         * Sets the API key and the CURL handler. Doesn't have return value.
         * 
         * @param $key ( string ) The API key provided by IGDB
         * @return void
         */
        public function __construct($key)
        {
            $this->API_KEY = $key;

            $this->_init_curl();
        }

        /**
         * Initializing Curl Session. Doesn't have return value.
         * 
         * @return void
         */
        private function _init_curl()
        {
            $this->CH = curl_init();
            curl_setopt($this->CH, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->CH, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->CH, CURLOPT_HTTPHEADER, array(
            'user-key: ' . $this->API_KEY,
            'Accept: application/json'
            ));
        }

        /**
         * Stringify the options array for the URL.
         * Checking every value; throwing Exception in case of errors
         * Returns the options as a query string.
         * 
         * @param $options ( array ) An array containing option parameters for the query.
         * @param $add_defaults ( boolean ) Whether append the default parameters to the query string
         * @throws Exception In case of invalid or missing parameters.
         * @return $url ( string ) Query string from the options array.
         */
        private function _stringify_options($options, $add_defaults = true)
        {
            if($add_defaults)
            {
                // Setting the default fields option in case it's not defined
                if(!array_key_exists('fields', $options))
                    $options['fields'] = $this->DEFAULT_FIELDS;

                // Setting the default limit option in case it's not defined
                if(!array_key_exists('limit', $options))
                    $options['limit'] = $this->DEFAULT_LIMIT;

                // Setting the default offset option in case it's not defined
                if(!array_key_exists('offset', $options))
                    $options['offset'] = $this->DEFAULT_OFFSET;
            }

            // All available fields parameter
            $available_options = array('id', 'search', 'fields', 'limit', 'offset', 'expand', 'filter', 'order');

            $query = '';

            // If ID provided in the options
            if(array_key_exists('id', $options))
            {
                // If both ID and SEARCH provided, remove the search element from the options
                if(array_key_exists('search', $options))
                    unset($options['search']);

                // If the ID is an array, implode it with commas
                if(is_array($options['id']))
                    $options['id'] = implode(',', $options['id']);
                
                // Append the ID to the query url
                $query .= $options['id'];

                // Remove the ID from the options to avoid being stringified later
                unset($options['id']);
            }

            $query .= '?';

            $params = array();

            foreach($options as $parameter => $value)
            {
                // Throwing an Exception if the parameter is not in the available options array
                if(!in_array($parameter, $available_options))
                    throw new Exception('Invalid option parameter: ' . $parameter . '!');
                
                switch($parameter)
                {
                    // The search parameter have to be url encoded
                    case 'search':
                            $value = urlencode($value);
                    break;

                    // Constructing order parameter
                    case 'order':
                        $available_directions = array('asc', 'desc');
                        $available_subfilters = array('min', 'max', 'avg', 'sum', 'median');

                        // If it is provided as array
                        if(is_array($value))
                        {
                            // If field is missing from the array
                            if(!array_key_exists('field', $value))
                                throw new Exception('Missing order parameter: field!');

                            // If direction is missing from the array
                            if(!array_key_exists('direction', $value))
                                throw new Exception('Missing order parameter: direction!');

                            // If the provided direction is not among the available ones
                            if(!in_array($value['direction'], $available_directions))
                                throw new Exception('Invalid direction parameter: ' . $value['direction']);
                            
                            // If there is a subfilter, but it is not among the available ones
                            if(array_key_exists('subfilter', $value) && !in_array($value['subfilter'], $available_subfilters))
                                throw new Exception('Invalid subfilter parameter: ' . $value['subfilter']);
                            
                            $value = $value['field'] . ':' . $value['direction'] . (array_key_exists('subfilter', $value) ? ':' . $value['subfilter'] : '');
                        }

                        // If it is provided as string
                        else if(preg_match('#^([^:]*):([^:]*)(?:$|:(.+))$#i', $value, $match))
                        {
                            $field = $match[1];
                            $direction = $match[2];
                            $subfilter = array_key_exists(3, $match) ? $match[3] : null;

                            // If the provided direction is not among the available ones
                            if(!in_array($direction, $available_directions))
                                throw new Exception('Invalid direction parameter: ' . $direction . '!');

                            // If the provided subfilter is not among the available ones
                            if(isset($subfilter) && !in_array($subfilter, $available_subfilters))
                                throw new Exception('Invalid subfilter parameter: ' . $subfilter . '!');

                            $value = $field . ':' . $direction . (is_null($subfilter) ? '' : ':' . $subfilter);
                        }

                        // Invalid string or parameters
                        else
                            throw new Exception('Invalid or missing order parameter!');
                    break;

                    // The filter parameters have to be constructed differently
                    case 'filter':
                        $available_postfixes = array(
                            'eq', // Equal: Exact match equal.
                            'not_eq', // Not Equal: Exact match equal.
                            'gt', // Greater than works only on numbers.
                            'gte', // Greater than or equal to works only on numbers.
                            'lt', // Less than works only on numbers.
                            'lte', // Less than or equal to works only on numbers.
                            'prefix', // Prefix of a value only works on strings.
                            'exists', // The value is not null.
                            'not_exists', // The value is null.
                            'in', // The value exists within the (comma separated) array (AND between values).
                            'not_in', // The values must not exists within the (comma separated) array (AND between values).
                            'any', // The value has any within the (comma separated) array (OR between values).
                        );

                        // Only one filter parameter as array => converting to array
                        if(is_array($value) && array_key_exists('field', $value) && array_key_exists('postfix', $value) && array_key_exists('value', $value))
                        {
                            $value = array(
                                array(
                                    'field' => $value['field'],
                                    'postfix' => $value['postfix'],
                                    'value' => $value['value']
                                )
                            );
                        }

                        // Several filter parameters as array
                        else if(is_array($value) && array_key_exists(0, $value))
                        {
                            // Empty clause, just checking the type
                        }

                        // One parameter as string
                        else if(!is_array($value) && preg_match('#\[([^\]]*)\]\[([^\]]*)\]=(.*)#i', $value, $match))
                        {
                            $field = $match[1];
                            $postfix = $match[2];
                            $param = $match[3];

                            $value = array(
                                array(
                                    'field' => $field,
                                    'postfix' => $postfix,
                                    'value' => $param
                                )
                            );
                        }

                        else
                            throw new Exception('Invalid or missing filter parameters!');

                        // Temp variables
                        $tempparameters = array();
                        $tempvalues = array();

                        foreach($value as $index => $filter)
                        {
                            // If the field parameter is missing
                            if(!array_key_exists('field', $filter))
                                throw new Exception('Missing \'field\' filter parameter in filter #' . $index . '!');

                            // If the postfix parameter is missing
                            if(!array_key_exists('postfix', $filter))
                                throw new Exception('Missing \'postfix\' filter parameter in filter #' . $index . '!');

                            // If the value parameter is missing
                            if(!array_key_exists('value', $filter))
                                throw new Exception('Missing \'value\' filter parameter in filter #' . $index . '!');

                            // If the provided postfix value is not among the available ones
                            if(!in_array($filter['postfix'], $available_postfixes))
                                throw new Exception('Invalid postfix value ' . $filter['postfix'] . ' in filter #' . $index . '!');
                            
                            array_push($tempparameters, 'filter[' . $filter['field'] . '][' . $filter['postfix'] . ']');
                            array_push($tempvalues, $filter['value']);
                        }

                        $parameter = $tempparameters;
                        $value = $tempvalues;

                        // Removing temp variables
                        unset($tempparameters, $tempvalues);
                    break;

                    // If the parameters value is an array then implode it with commas
                    // Else remove the whitespaces, if there is any
                    default:
                        is_array($value) ? $value = implode(',', $value) : $value = preg_replace('# #', '', $value);
                    break;
                }

                if(is_array($parameter))
                    foreach($parameter as $index => $param)
                        array_push($params, $param . '=' . $value[$index]);
                else
                    array_push($params, $parameter . '=' . $value);
            }

            $query .= implode('&', $params);

            return $query;
        }

        /**
         * Get the number of all the records on the given endpoint matching the optionally provided filters.
         * 
         * @param $endpoint The name of the endpoint
         * @param $filters An optional $option array with only a filter parameter.
         * @throws Exception in case of invalid endpoint.
         * @return $result ( number ) The total count of the records.
         */
        public function count($endpoint, $filters = null)
        {
            // Available endpoints
            $available_endpoints = array(
                'character' => 'characters',
                'collection' => 'collections',
                'company' => 'companies',
                'credit' => 'credits',
                'feed' => 'feeds',
                'franchise' => 'franchises',
                'game' => 'games',
                'game_engine' => 'game_engines',
                'game_mode' => 'game_modes',
                'genre' => 'genres',
                'keyword' => 'keywords',
                'page' => 'pages',
                'person' => 'persons',
                'platform' => 'platforms',
                'player_perspective' => 'player_perspectives',
                'pulse' => 'pulses',
                'pulse_group' => 'pulse_groups',
                'pulse_source' => 'pulse_sources',
                'release_date' => 'release_dates',
                'review' => 'reviews',
                'theme' => 'themes',
                'title' => 'titles',
                'versions' => 'game_versions'
            );

            // If invalid endpoint is provided
            if(!array_key_exists($endpoint, $available_endpoints))
                throw new Exception('Invalid endpoint: ' . $endpoint . '!');

            // Query IGDB for the data
            $result = $this->_exec_query(rtrim($this->API_URL, '/') . '/' . $available_endpoints[$endpoint] . '/count' . (is_null($filters) ? '' : $this->_stringify_options($filters, false)));
            
            return $result->count;
        }

        /**
         * Constructs the complete query URL using the provided endpoint and options array.
         * Returns the contsturcted URL.
         * 
         * @param $endpoint ( string ) The IGDB endpoint name
         * @param $options ( array ) The array containing the parameters for the query.
         * @return $url ( string ) The complete query URL.
         */
        private function _construct_url($endpoint, $options)
        {
            return rtrim($this->API_URL, '/') . '/' . $endpoint . '/' . $this->_stringify_options($options);
        }

        /**
         * Executes the query against the constructed URL.
         * After the request the HTTP response code is examined.
         * Returns an array decoded from IGDB JSON response or throws Exception in case of error
         * 
         * @param $url ( string ) The complete IGDB URL.
         * @throws Exception in case the curl session has been closed manually.
         * @throws Exception in case of HTTP 0 response (Failed Request)
         * @throws Exception in case of HTTP 400 response (Bad Request)
         * @throws Exception in case of HTTP 401 response (Unauthorized)
         * @throws Exception in case of HTTP 403 response (Forbidden)
         * @throws Exception in case of HTTP 500 response (Internal Server Error)
         * @return $result ( array ) The response objects from IGDB in an array.
         */
        private function _exec_query($url)
        {
            // Throw Exception if CURL handler is null (closed)
            if(is_null($this->CH))
                throw new Exception('CURL session is closed!');

            // Set the request URL
            curl_setopt($this->CH, CURLOPT_URL, $url);

            // Executing the request
            $result = json_decode(curl_exec($this->CH));

            // Getting request information
            $request = curl_getinfo($this->CH);

            switch($request['http_code'])
            {
                case 0: // Failed Request
                    throw new Exception('Request failed! Check the Request URL!');
                break;

                case 400: // Bad Request
                    if(is_object($result))
                    {
                        if(property_exists($result, 'message'))
                            $message = $result->message;

                        if(property_exists($result, 'Err'))
                            $message = $result->Err->message;
                    }

                    if(property_exists($result[0], 'error'))
                        $message = implode(' ', $result[0]->error);

                    throw new Exception('Error 400: Bad Request!' . (isset($message) ? ' ' . $message : ''));
                break;

                case 401: // Unauthorized
                    throw new Exception('Error 401: Unauthorized!');
                break;

                case 403: // Forbidden
                    throw new Exception('Error 403: Forbidden!');
                break;

                case 500: // Internal Server Error
                    throw new Exception('Error 500: Internal Server Error!');
                break;
            }

            return $result;
        }

        /**
         * Set the default values for query parameters. Doesn't have return value.
         * 
         * @param $parameter ( string ) Name of the parameter you want to set.
         * @param $value ( mixed ) Value of the parameter. Vary depending on the parameter.
         * @throws Exception in case parameter is limit and the value is not a number.
         * @throws Exception in case parameter is limit and the value is not between 0 and 50.
         * @throws Exception in case parameter is offset and the value is not a number.
         * @throws Exception in case parameter is offset and the value is lower than 0.
         * @throws Exception in case parameter is unrecognizable.
         * @return void
         */
        public function set_default($parameter, $value)
        {
            switch(strtolower($parameter))
            {
                case 'fields':
                    if(is_array($value))
                        $this->DEFAULT_FIELDS = implode(',', $value);
                    else
                        $this->DEFAULT_FIELDS = $value;
                break;

                case 'limit':
                    if(!is_numeric($value))
                        throw new Exception('Limit parameter must be a number!');
                    else if($value < 0 || $value > 50)
                        throw new Exception('Limit parameter must be a number between 0 and 50!');
                    else
                        $this->DEFAULT_LIMIT = (int)$value;
                break;

                case 'offset':
                    if(!is_numeric($value))
                        throw new Exception('Offset parameter must be a number!');
                    else if($value < 0)
                        throw new Exception('Offset parameter must be a number between 0 and 50!');
                    else
                        $this->DEFAULT_OFFSET = (int)$value;
                break;
                
                default:
                    throw new Exception('Unrecognized parameter: ' . $parameter . '!');
                break;
            }
        }

        /**
         * Closes the CURL handler.
         * 
         * After this method is called, the class cannot run any queries against IGDB unless you reinitialize it manually.
         * Doesn't have return value.
         * 
         * @return void
         */
        public function close_handler()
        {
            curl_close($this->CH);
            $this->CH = null;
        }

        /**
         * Reinitialize the CURL session. Simply calls the _init_curl private method.
         * 
         * Doesn't have return value.
         */
        public function reinit_handler()
        {
            $this->_init_curl();
        }

        /**
         * Executes a custom query on the IGDB. Great solution for testing requests manually.
         * 
         * @param $url ( string ) manually assembled query string with the endpoint
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function custom_query($url)
        {
            return $this->_exec_query(rtrim($this->API_URL, '/') . '/' . ltrim($url, '/'));
        }

        /**
         * Fetch data from IGDB using CHARACTER endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/character/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function character($options, $execute = true)
        {
            $url = $this->_construct_url('characters', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using COLLECTION endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/collection/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function collection($options, $execute = true)
        {
            $url = $this->_construct_url('collections', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using COMPANY endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/company/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function company($options, $execute = true)
        {
            $url = $this->_construct_url('companies', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using CREDIT endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/credit/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function credit($options, $execute = true)
        {
            $url = $this->_construct_url('credits', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using FEED endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/feed/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function feed($options, $execute = true)
        {
            $url = $this->_construct_url('feeds', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using FRANCHISE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/franchise/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function franchise($options, $execute = true)
        {
            $url = $this->_construct_url('franchises', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using GAME endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/game/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function game($options, $execute = true)
        {
            $url = $this->_construct_url('games', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using GAME ENGINE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/game-engine/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function game_engine($options, $execute = true)
        {
            $url = $this->_construct_url('game_engines', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using GAME MODE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/game-mode/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function game_mode($options, $execute = true)
        {
            $url = $this->_construct_url('game_modes', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using GENRE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/genre/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function genre($options, $execute = true)
        {
            $url = $this->_construct_url('genres', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using KEYWORD endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/keyword/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function keyword($options, $execute = true)
        {
            $url = $this->_construct_url('keywords', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using PAGE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/page/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function page($options, $execute = true)
        {
            $url = $this->_construct_url('pages', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using PERSON endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/person/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function person($options, $execute = true)
        {
            $url = $this->_construct_url('persons', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using PLATFORM endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/platform/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function platform($options, $execute = true)
        {
            $url = $this->_construct_url('platforms', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using PLAYER PERSPECTIVE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/player-perspective/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function player_perspective($options, $execute = true)
        {
            $url = $this->_construct_url('player_perspectives', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using PULSE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/pulse/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function pulse($options, $execute = true)
        {
            $url = $this->_construct_url('pulses', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using PULSE GROUP endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/pulse-group/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function pulse_group($options, $execute = true)
        {
            $url = $this->_construct_url('pulse_groups', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using PULSE SOURCE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/pulse-source/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function pulse_source($options, $execute = true)
        {
            $url = $this->_construct_url('pulse_sources', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using RELEASE DATE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/release-date/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function release_date($options, $execute = true)
        {
            $url = $this->_construct_url('release_dates', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using REVIEW endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/review/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function review($options, $execute = true)
        {
            $url = $this->_construct_url('reviews', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using THEME endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/theme/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function theme($options, $execute = true)
        {
            $url = $this->_construct_url('themes', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using TITLE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/title/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function title($options, $execute = true)
        {
            $url = $this->_construct_url('titles', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

        /**
         * Fetch data from IGDB using VERSIONS endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
         * @link https://igdb.github.io/api/endpoints/versions/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @param $execute ( boolean ) Wether you want to execute the query and get the result or get the full url of the query
         * @return $result ( array | string ) an array with the Parsed JSON object or the full URL. Depending on the $execute parameter
         */
        public function versions($options, $execute = true)
        {
            $url = $this->_construct_url('game_versions', $options);

            if($execute)
                return $this->_exec_query($url);
            else
                return $url;
        }

    }

?>