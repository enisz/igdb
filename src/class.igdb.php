<?php

    /**
     * Internet Game Database Api Class
     * 
     * Fethching data from IGDB's database.
     * 
     * @version 1.0
     * @author Enisz Abdalla <enisz87@gmail.com>
     */

    class IGDB {

        // IGDB API url
        private $API_URL;

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
         * @param $url ( string ) The URL of the IGDB API
         * @param $key ( string ) The API key provided by IGDB
         * @return void
         */
        public function __construct($url, $key)
        {
            $this->API_KEY = $key;
            $this->API_URL = $url;

            $this->_init_curl();
        }

        /**
         * Checking the passed API URL whether it is a valid API URL or not.
         * 
         * @param $url ( string ) the URL of the IGDB API
         * @return boolean whether $url is valid url or not.
         */
        public static function validate_api_url($url)
        {
            return preg_match('#^https://api-[0-9].*\.apicast.io(/|)#', $url) == false ? false : true;
        }

        /**
         * Checking the passed API Key whether it is a valid API Key or not.
         * 
         * @param $key ( string ) the API Key provided by IGDB
         * @return boolean whether the key is valid or not
         */
        public static function validate_api_key($key)
        {
            return preg_match('#^[a-f0-9]{32}$#', $key) == false ? false : true;
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
         * @throws Exception If neither id nor search parameter is set.
         * @throws Exception If fields parameter is missing when you want to use expander feature.
         * @throws Exception If unrecognized parameter is provided in the options array.
         * @return $url ( string ) Query string from the options array.
         */
        private function _stringify_options($options)
        {
            // Throwing Exception if neither id nor search is provided
            if(!isset($options['id']) && !isset($options['search']))
                throw new Exception('ID or search parameter must be set!');

            // Throwing Exception if expander function is missing the fields parameter
            if(isset($options['expand']) && !isset($options['fields']))
                throw new Exception('The expander function requires the fields parameter!');
            
            // Setting the default fields option in case it's not defined
            if(!array_key_exists('fields', $options))
                $options['fields'] = $this->DEFAULT_FIELDS;

            // Setting the default limit option in case it's not defined
            if(!array_key_exists('limit', $options))
                $options['limit'] = $this->DEFAULT_LIMIT;

            // Setting the default offset option in case it's not defined
            if(!array_key_exists('offset', $options))
                $options['offset'] = $this->DEFAULT_OFFSET;

            // All available fields parameter
            $available_options = array('id', 'search', 'fields', 'limit', 'offset', 'expand', 'filter', 'order');

            $query = '';

            if(array_key_exists('id', $options))
            {
                if(array_key_exists('search', $options))
                    unset($options['search']);

                if(is_array($options['id']))
                    $options['id'] = implode(',', $options['id']);
                
                $query .= $options['id'];

                unset($options['id']);
            }

            $query .= '?';

            $params = array();

            foreach($options as $parameter => $value)
            {
                // Throwing an Exception if the parameter is not in the available options array
                if(!in_array($parameter, $available_options))
                    throw new Exception('Unrecognized option parameter: ' . $parameter . '!');
                
                // Search parameter have to be URL encoded
                if($parameter == 'search')
                    $value = urlencode($value);
                
                if(is_array($value)) // If the value is an array, than implode it with commas
                    $value = implode(',', $value);
                else // Else replacing every whitespace in the value
                    $value = preg_replace('# #', '', $value);

                array_push($params, $parameter . '=' . $value);
            }

            $query .= implode('&', $params);

            return $query;
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
         * @throws Exception in case of failed request
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
                    throw new Exception('Request failed! Check your API URL!');
                break;

                case 200: // OK
                    if(is_null($result)) // In case of empty result
                        $result = array(); // Will return an empty array
                break;

                case 400: // Bad Request
                    if(property_exists($result, 'Err'))
                        $error = $result->Err->message;
                    
                    if(property_exists($result, 'message'))
                        $error = $result->message;

                    if(!isset($error))
                        $error = 'Error 400: Bad Request!';
                    
                    var_dump($result);
                    var_dump($request);

                    throw new Exception($error);
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
         * Setter and getter method. If $url is provided, the validity of the url will be checked.
         * 
         * @param $url ( string ) IGDB API URL
         * @return mixed string $url in case of get; boolean true if setting url was successful, false otherwise
         */
        public function api_url($url = null)
        {
            if(is_null($url))
                return $this->API_URL;
            
            else
            {
                if(!$this->validate_api_url($url))
                    return false;
                else
                {
                    $this->API_URL = $url;
                    return true;
                }
            }
        }

        /**
         * Setter and getter method. If $key is provided, the validity of the key will be checked.
         * 
         * @param $key ( string ) IGDB API Key
         * @return mixed string $key in case of get; boolean true if setting key was successful, false otherwise
         */
        public function api_key($key = null)
        {
            if(is_null($key))
                return $this->API_KEY;
            
            else
            {
                if(!$this->validate_api_key($key))
                    return false;
                else
                {
                    $this->API_KEY = $key;
                    return true;
                }
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
         * 
         * @param void
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
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function character($options)
        {
            return $this->_exec_query($this->_construct_url('characters', $options));
        }
        
        /**
         * Fetch data from IGDB using COLLECTION endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/collection/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function collection($options)
        {
            return $this->_exec_query($this->_construct_url('collections', $options));
        }

        /**
         * Fetch data from IGDB using COMPANY endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/company/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function company($options)
        {
            return $this->_exec_query($this->_construct_url('companies', $options));
        }

        /**
         * Fetch data from IGDB using CREDIT endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/credit/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function credit($options)
        {
            return $this->_exec_query($this->_construct_url('credits', $options));
        }

        /**
         * Fetch data from IGDB using FEED endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/feed/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function feed($options)
        {
            return $this->_exec_query($this->_construct_url('feeds', $options));
        }

        /**
         * Fetch data from IGDB using FRANCHISE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/franchise/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function franchise($options)
        {
            return $this->_exec_query($this->_construct_url('franchises', $options));
        }

        /**
         * Fetch data from IGDB using GAME endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/game/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
		public function game($options)
		{
			return $this->_exec_query($this->_construct_url('games', $options));
        }
        
        /**
         * Fetch data from IGDB using GAME ENGINE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/game-engine/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function game_engine($options)
        {
            return $this->_exec_query($this->_construct_url('game_engines', $options));
        }

        /**
         * Fetch data from IGDB using GAME MODE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/game-mode/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function game_mode($options)
        {
            return $this->_exec_query($this->_construct_url('game_modes', $options));
        }

        /**
         * Fetch data from IGDB using GENRE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/genre/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function genre($options)
        {
            return $this->_exec_query($this->_construct_url('genres', $options));
        }

        /**
         * Fetch data from IGDB using KEYWORD endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/keyword/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function keyword($options)
        {
            return $this->_exec_query($this->_construct_url('keywords', $options));
        }

        /**
         * Fetch data from IGDB using PAGE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/page/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function page($options)
        {
            return $this->_exec_query($this->_construct_url('pages', $options));
        }

        /**
         * Fetch data from IGDB using PERSON endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/person/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function person($options)
        {
            return $this->_exec_query($this->_construct_url('persons', $options));
        }

        /**
         * Fetch data from IGDB using PLATFORM endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/platform/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function platform($options)
        {
            return $this->_exec_query($this->_construct_url('platforms', $options));
        }

        /**
         * Fetch data from IGDB using PLAYER PERSPECTIVE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/player-perspective/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function player_perspective($options)
        {
            return $this->_exec_query($this->_construct_url('player_perspectives', $options));
        }

        /**
         * Fetch data from IGDB using PULSE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/pulse/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function pulse($options)
        {
            return $this->_exec_query($this->_construct_url('pulses', $options));
        }

        /**
         * Fetch data from IGDB using PULSE GROUP endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/pulse-group/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function pulse_group($options)
        {
            return $this->_exec_query($this->_construct_url('pulse_groups', $options));
        }

        /**
         * Fetch data from IGDB using PULSE SOURCE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/pulse-source/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function pulse_source($options)
        {
            return $this->_exec_query($this->_construct_url('pulse_source', $options));
        }
		
        /**
         * Fetch data from IGDB using RELEASE DATE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/release-date/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function release_date($options)
        {
            return $this->_exec_query($this->_construct_url('release_dates', $options));
        }
        
        /**
         * Fetch data from IGDB using REVIEW endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/review/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function review($options)
        {
            return $this->_exec_query($this->_construct_url('reviews', $options));
        }

        /**
         * Fetch data from IGDB using THEME endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/theme/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function theme($options)
        {
            return $this->_exec_query($this->_construct_url('themes', $options));
        }

        /**
         * Fetch data from IGDB using TITLE endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/title/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function title($options)
        {
            return $this->_exec_query($this->_construct_url('titles', $options));
        }

        /**
         * Fetch data from IGDB using VERSIONS endpoint. 
         * Returns an array with JSON object decoded from IGDB response.
		 * @link https://igdb.github.io/api/endpoints/versions/
         * 
         * @param $options ( array ) an options parameter setting up the details of the query.
         * @return $result ( array ) an array with Parsed JSON objects
         */
        public function versions($options)
        {
            return $this->_exec_query($this->_construct_url('game_versions', $options));
        }

    }

?>