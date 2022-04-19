<?php

    /**
     * IGDB Query Builder
     *
     * Building apicalypse query strings
     *
     * @version 1.2.0
     * @author Enisz Abdalla <enisz87@gmail.com>
     * @link https://github.com/enisz/igdb
     */

    require_once "IGDBInvalidParameterException.php";

    class IGDBQueryBuilder {
        /**
         * Search parameter of the query
         */
        private $_search;

        /**
         * Fields parameter of the query
         */
        private $_fields;

        /**
         * Exclude parameter of the qurey
         */
        private $_exclude;

        /**
         * Limit parameter of the query
         */
        private $_limit;

        /**
         * Offset parameter of the query
         */
        private $_offset;

        /**
         * Where parameter of the query
         */
        private $_where;

        /**
         * Sort parameter of the query
         */
        private $_sort;

        /**
         * A name for the query for multiquery (multiquery only)
         */
        private $_name;

        /**
         * The endpoint for the multiquery (multiquery only)
         */
        private $_endpoint;

        /**
         * Count switch for the endpoint (multiquery only)
         */
        private $_count;

        /**
         * Default value of the limit parameter
         */
        private $limit_default = 10;

        /**
         * Default value of the offset parameter
         */
        private $offset_default = 0;

        /**
         * Setting up the builder with default values
         */
        public function __construct() {
            $this->reset();
        }

        /**
         * Resets the configuration to the default values
         */
        public function reset() {
            $this->_search = "";
            $this->_fields = array("*");
            $this->_exclude = array();
            $this->_limit = $this->limit_default;
            $this->_offset = $this->offset_default;
            $this->_where = array();
            $this->_sort = array();
            $this->_name = null;
            $this->_endpoint = null;
            $this->_count = false;

            return $this;
        }

        /**
         * Configuring the query with an options array
         * @param $options - The options array containing configuration items
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException If the passed options array contains invalid parameters
         */
        public function options($options) {
            foreach($options as $parameter => $value) {
                if(method_exists($this, $parameter)) {
                    $this->$parameter($value);
                } else {
                    throw new IGDBInvalidParameterException("Invalid parameter found in passed query array. " . $parameter . " is not valid!");
                }
            }

            return $this;
        }

        /**
         * Setting the item id for the query. Please note, every ID related where statements will be removed by this method. Use it with caution!
         * @param $id - A numeric value or an array of numeric values of item IDs
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException if not a number or an array of numbers passed
         */
        public function id($id) {
            $parameter = array(
                "field" => "id",
                "postfix" => "=",
                "value" => ""
            );

            if(is_numeric($id)) {
                $parameter["value"] = intval($id);
            } else if(is_array($id)) {
                $ids = array();
                foreach($id as $index => $var) {
                    if(!is_numeric($var)) {
                        throw new IGDBInvalidParameterException("Invalid type of parameter in array for id! Only numeric values allowed, " . gettype($var) . " found in array at index " . $index . "!");
                    } else {
                        array_push($ids, intval($var));
                    }
                }

                sort($ids, SORT_NUMERIC);
                $parameter["value"] = (count($ids) > 1 ? "(" : "") . implode(",", $ids) . (count($ids) > 1 ? ")" : "");
            } else {
                throw new IGDBInvalidParameterException("Invalid type of parameter for id! Only numeric values or array of numeric values are allowed, " . gettype($id) . " passed!");
            }

            // Remove any ID related statements from the where clause
            $this->_where = array_filter($this->_where, function($statement) { return $statement["field"] != "id"; });

            array_push($this->_where, $parameter);
            return $this;
        }

        /**
         * Setting the search parameter for the query.
         * @param $search - A string or a numeric value to search for
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException if not a string or a numeric value is passed. Also if a sorting is already applied.
         */
        public function search($search) {
            if(count($this->_sort) != 0) {
                throw new IGDBInvalidParameterException("Your query has both search and sort. Search is sorting on relevancy and therefore sort is not applicable on search");
            }
            if(is_string($search) || is_numeric($search)) {
                $this->_search = $search;
            } else {
                throw new IGDBInvalidParameterException("Invalid type of parameter for search! String or numeric values are expected, " . gettype($search) . " passed!");
            }

            return $this;
        }

        /**
         * A common method called by fields and exclude
         * @param $fields - list of fields to process
         * @param $parameter - the parameter to set
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException If not a string or an array of strings passed
         */
        private function fields_and_exclude($fields, $parameter) {
            if(is_string($fields)) {
                $this->{"_" . $parameter} = array_map(function($field) { return trim($field); }, explode(",", $fields));
            } else if (is_array($fields)) {
                foreach($fields as $index => $field) {
                    if(!is_string($field)) {
                        throw new IGDBInvalidParameterException("Invalid type of parameter for " . $parameter . "! An array of strings are expected, " . gettype($field) . " found in array at index " . $index . "!");
                    }
                }

                $this->{"_" . $parameter} = array_map(function($field) { return trim($field); }, $fields);
            } else {
                throw new IGDBInvalidParameterException("Invalid type of parameter for " . $parameter . "! String or array of strings are expected, " . gettype($fields) . " passed!");
            }

            return $this;
        }

        /**
         * Setting the expected fields for the result
         * @param $fields - A string list separated by commas or an array of strings of the field names to exclude from the result
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException If not a string or an array of strings passed
         */
        public function fields($fields) {
            return $this->fields_and_exclude($fields, "fields");
        }

        /**
         * Setting the fields to exclude for the result
         * @param $fields - A string list separated by commas or an array of strings of the field names to include in the result
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException If not a string or an array of strings passed
         */
        public function exclude($fields) {
            return $this->fields_and_exclude($fields, "exclude");
        }

        /**
         * Setting the limit for the result
         * @param $limit - an integer to set as limit for the results
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException If not a numeric value passed between 1 and 500
         */
        public function limit($limit) {
            if(!is_numeric($limit)) {
                throw new IGDBInvalidParameterException("Invalid type of parameter for limit! A numeric value is expected, " . gettype($limit) . " passed!");
            } else if(intval($limit) < 1 || intval($limit) > 500) {
                throw new IGDBInvalidParameterException("Invalid number for limit! The limit must be between 1 and 500, " . $limit . " passed!");
            } else {
                $this->_limit = intval($limit);
            }

            return $this;
        }

        /**
         * Setting the offset for the result
         * @param $offset - an integer to set as offset for the results
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException If not a numeric value passed above zero
         */
        public function offset($offset) {
            if(!is_numeric($offset)) {
                throw new IGDBInvalidParameterException("Invalid type of parameter for offset! A numeric value is expected, " . gettype($offset) . " passed!");
            } else if(intval($offset) < 0) {
                throw new IGDBInvalidParameterException("Invalid number for offset! The limit must be greater than 0, " . $offset . " passed!");
            } else {
                $this->_offset = intval($offset);
            }

            return $this;
        }

        /**
         * Adding a where clause to the query
         * @param $where - an array with specific indexes or a string consists of 3 segments in a format of [field] [postfix] [value]
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException if passed as a string without the 3 segments or the passed array does not contain the 3 indexes field, postfix and value. Also if postfix is invalid
         */
        public function where($where) {
            $available_postfixes = array('=', '!=', '>', '>=', '<', '<=', '~');

            if(is_string($where)) {
                $segments = explode(" ", $where);

                if(count($segments) == 3) {
                    $split = explode(" ", $where);

                    if(array_search($split[1], $available_postfixes) === false) {
                        throw new IGDBInvalidParameterException("Passed postfix $split[1] is invalid! Available postfixes: " . implode(", ", $available_postfixes));
                    }

                    array_push($this->_where, array(
                        "field" => $split[0],
                        "postfix" => $split[1],
                        "value" => $split[2]
                    ));
                } else {
                    throw new IGDBInvalidParameterException("Where parameter cannot be split to 3 different segments. [field] [postfix] [value] format is required!");
                }
            } else if(is_array($where)) {
                if(array_key_exists("field", $where) && array_key_exists("postfix", $where) && array_key_exists("value", $where)) {
                    if(array_search($where["postfix"], $available_postfixes) === false) {
                        throw new IGDBInvalidParameterException("Passed postfix " . $where["postfix"] . " is invalid! Available postfixes: " . implode(", ", $available_postfixes));
                    }

                    array_push($this->_where, array(
                        "field" => $where["field"],
                        "postfix" => $where["postfix"],
                        "value" => is_array($where["value"]) ? "(" . implode(", ", $where["value"]) . ")" : $where["value"]
                    ));
                } else {
                    throw new IGDBInvalidParameterException("Missing parameters for where! field, postfix and value is required, " . implode(", ", array_keys($where)) . " passed!");
                }
            } else {
                throw new IGDBInvalidParameterException("Invalid type of parameter for where! A string or an array is expected, " . gettype($where) . " passed!");
            }

            return $this;
        }

        /**
         * Adding a custom where statement to the query.
         * The value of this parameter will not be checked in any way, just will be simply appended to the where statement.
         */
        public function custom_where($where) {
            array_push($this->_where, $where);
            return $this;
        }

        /**
         * Adding a sort clause to the query
         * @param $sort - Either an apicalypse formatted string for sort, or an array with field and direction keys
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException if not a proper sort clause is passed, or the passed array contains invalid fields or values. Also, if a search parameter is added.
         */
        public function sort($sort) {
            if($this->_search != "") {
                throw new IGDBInvalidParameterException("Your query has both search and sort. Search is sorting on relevancy and therefore sort is not applicable on search");
            } else if(is_string($sort)) {
                $segments = explode(" ", $sort);

                if(count($segments) == 2) {
                    if($segments[1] == "asc" || $segments[1] == "desc") {
                        $this->_sort = array(
                            "field" => $segments[0],
                            "direction" => $segments[1]
                        );
                    } else {
                        throw new IGDBInvalidParameterException("Sort parameter must be either asc for ascending or des for descending ordering. " . $segments[1] . " is not valid!");
                    }
                } else {
                    throw new IGDBInvalidParameterException("Sort parameter cannot be split to 2 different segments. [field] [direction] format is required!");
                }
            } else if(is_array($sort)) {
                if(array_key_exists("field", $sort) && array_key_exists("direction", $sort)) {
                    $this->_sort = array(
                        "field" => $sort["field"],
                        "direction" => $sort["direction"]
                    );
                } else {
                    throw new IGDBInvalidParameterException("Missing parameters for sort! field and direction is required, " . implode(", ", array_keys($sort)) . " passed!");
                }
            } else {
                throw new IGDBInvalidParameterException("Invalid type of parameter for sort! A string or an array is expected, " . gettype($sort) . " passed!");
            }

            return $this;
        }

        /**
         * Setting the name of the query. Will be processed in case of multiquery only.
         * @param $name - The name of the query.
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException if a non-string value is passed to the method.
         */
        public function name($name) {
            if(gettype($name) == "string") {
                $this->_name = $name;
            } else {
                throw new IGDBInvalidParameterException("Invalid type of parameter for name! A string is expected, " . gettype($name) . " passed!");
            }

            return $this;
        }

        /**
         * Setting the endpoint for the query. Will be processed in case of multiquery only.
         * @param $endpoint - The endpont to send the query against.
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException if a non-string value is passed to the method.
         */
        public function endpoint($endpoint) {
            if(gettype($endpoint) == "string") {
                $available_endpoints = array(
                    "age_rating_content_description" => "age_rating_content_descriptions",
                    "age_rating" => "age_ratings",
                    "alternative_name" => "alternative_names",
                    "artwork" => "artworks",
                    "character_mug_shot" => "character_mug_shots",
                    "character" => "characters",
                    "collection" => "collections",
                    "company_logo" => "company_logos",
                    "company_website" => "company_websites",
                    "company" => "companies",
                    "cover" => "covers",
                    "external_game" => "external_games",
                    "franchise" => "franchises",
                    "game_engine_logo" => "game_engine_logos",
                    "game_engine" => "game_engines",
                    "game_mode" => "game_modes",
                    "game_version_feature_value" => "game_version_feature_values",
                    "game_version_feature" => "game_version_features",
                    "game_version" => "game_versions",
                    "game_video" => "game_videos",
                    "game" => "games",
                    "genre" => "genres",
                    "involved_company" => "involved_companies",
                    "keyword" => "keywords",
                    "multiplayer_mode" => "multiplayer_modes",
                    "multiquery" => "multiquery",
                    "platform_family" => "platform_families",
                    "platform_logo" => "platform_logos",
                    "platform_version_company" => "platform_version_companies",
                    "platform_version_release_date" => "platform_version_release_dates",
                    "platform_version" => "platform_versions",
                    "platform_website" => "platform_websites",
                    "platform" => "platforms",
                    "player_perspective" => "player_perspectives",
                    "release_date" => "release_dates",
                    "screenshot" => "screenshots",
                    "search" => "search",
                    "theme" => "themes",
                    "website" => "websites"
                );

                if(array_key_exists($endpoint, $available_endpoints)) {
                    $this->_endpoint = $available_endpoints[$endpoint];
                } else {
                    throw new IGDBInvalidParameterException("The passed endpoint \"$endpoint\" is invalid. Make sure to use the name of the endpoint, not it's path!");
                }
            } else {
                throw new IGDBInvalidParameterException("Invalid type of parameter for endpoint! A string is expected, " . gettype($endpoint) . " passed!");
            }

            return $this;
        }

        /**
         * Setting the query to use the count functionality. Will be processed in case of multiquery only.
         * @param $count - A boolean value whether the count of records is needed.
         * @return IGDBQueryBuilder
         * @throws IGDBInvalidParameterException if a non-boolean value is passed to the method.
         */
        public function count($count = true) {
            if(gettype($count) == "boolean") {
                $this->_count = $count;
            } else {
                throw new IGDBInvalidParameterException("Invalid type of parameter for endpoint! A boolean is expected, " . gettype($count) . " passed!");
            }

            return $this;
        }

        /**
         * Building the apicalypse query from the configured object
         * @param $multiquery - whether a multiquery string is required or a simple endpoint query
         * @throws IGDBInavlidParameterException if a non-boolean parameter is passed to the method
         * @return string the apicalpyse query string
         */
        public function build($multiquery = false) {
            if(gettype($multiquery) != "boolean") {
                throw new IGDBInvalidParameterException("Invalid type of parameter for build! A boolean is expected, " . gettype($multiquery) . "passed!");
            }

            return $multiquery ? $this->multiquery() : $this->query();
        }

        /**
         * Building the Apicalypse query string from the configured object for endpoint query requests
         * @return string Apicalypse formatted query string
         */
        private function query() {
            $segments = array();

            foreach(array("fields", "search", "exclude", "limit", "offset", "where", "sort") as $parameter) {
                switch($parameter) {
                    case "search":
                        if(strlen($this->_search) > 0) {
                            array_push($segments, "search \"" . $this->_search . "\"");
                        }
                    break;

                    case "fields":
                    case "exclude":
                        if(count($this->{"_" . $parameter}) > 0) {
                            array_push($segments, $parameter . " " . implode(",", $this->{"_" . $parameter}));
                        }
                    break;

                    case "limit":
                    case "offset":
                        if($this->{"_" . $parameter} != $this->{$parameter . "_default"}) {
                            array_push($segments, $parameter . " " . $this->{"_" . $parameter});
                        }
                    break;

                    case "where":
                        if(count($this->_where) > 0) {
                            $parts = array();

                            foreach($this->_where as $statement) {
                                // A parsed where statement
                                if(is_array($statement)) {
                                    $need_quote = false;

                                    if(strpos($statement["value"], "\"") !== false) {
                                        $need_quote = false;
                                    } else if ($statement["value"][0] == "(" || $statement["value"][0] == "{" || $statement["value"][0] == "[") {

                                    }else if(is_string($statement["value"])) {
                                        if($statement["value"] == "null" || is_numeric($statement["value"])) {
                                            $need_quote = false;
                                        } else {
                                            $need_quote = true;
                                        }
                                    } else {
                                        $need_quote = false;
                                    }

                                    array_push($parts, $statement["field"] . " " . $statement["postfix"] . " " . ($need_quote ? "\"" : "") . $statement["value"] . ($need_quote ? "\"" : ""));
                                } else {
                                    // A custom where statement
                                    array_push($parts, $statement);
                                }
                            }

                            array_push($segments, "where " . implode(" & ", $parts));
                        }
                    break;

                    case "sort":
                        if(count($this->_sort) > 0) {
                            array_push($segments, $parameter . " " . $this->{"_" . $parameter}["field"] . " " . $this->{"_" . $parameter}["direction"]);
                        }
                    break;
                }
            }

            return implode(";\n", $segments) . ";";
        }

        /**
         * Building the apicalpyse query string for multiquery requests.
         * @throws IGBDInvalidParameterException if the name or endpoint properties are not set in the builder configuration
         * @return string Apicalypse formatted multiquery query string
         */
        private function multiquery() {
            if($this->_name == null) {
                throw new IGDBInvalidParameterException("The name parameter for the multiquery is not set!");
            }

            if($this->_endpoint == null) {
                throw new IGDBInvalidParameterException("The endpoint parameter for the multiquery is not set!");
            }

            $query = "query " . $this->_endpoint . ($this->_count ? "/count" : "") . " \"" . addslashes($this->_name) . "\" {\n";

            foreach(explode("\n", $this->query()) as $line) {
                $query .= "  $line\n";
            }

            $query .= "};";

            return $query;
        }

    }

?>