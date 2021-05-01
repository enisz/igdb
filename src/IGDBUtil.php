<?php

    /**
     * IGDB Util
     *
     * A utility class for useful methods
     *
     * @version 1.0.0
     * @author Enisz Abdalla <enisz87@gmail.com>
     * @link https://github.com/enisz/igdb
     */

    class IGDBUtil {

        /**
         * URL of the IGDB API
         */
        private const API_URL = "https://api.igdb.com/v4";

        /**
         * Helper method to authenticate your application via Twitch
         * @param $client_id - Your client ID from twitch
         * @param $client_secret - The generated secret token for your application
         * @return $response - the response object from Twitch with properties access_token, expires_in, token_type
         * @throws Exception If a non-success response is returned
         */
        public static function authentication($client_id, $client_secret) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_URL, "https://id.twitch.tv/oauth2/token?client_id=$client_id&client_secret=$client_secret&grant_type=client_credentials");

            $info = curl_getinfo($curl);
            $response = json_decode(curl_exec($curl));
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if($responseCode < 200 || $responseCode > 299) {
                var_dump($response);
                throw new Exception($response->message, $response->status);
            } else {
                return $response;
            }
        }

        /**
         * Constructing an endpoint url using the name of the endpoint and the optional count parameter.
         * @param $endpoint - the name of the endpoint. Make sure to use the name of the endpoint, not the URL
         * @param $count - whether the number of results is required or the result set itself
         * @throws InvalidArgumentException if passed endpoint name is invalid
         * @return string - the constructed URL using the provided parameters
         */
        public static function construct_url($endpoint, $count = false) {
            $endpoints = array(
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

            if(array_key_exists($endpoint, $endpoints)) {
                return rtrim(self::API_URL, "/") . "/" . $endpoints[$endpoint] . ($count ? "/count" : "");
            } else {
                throw new InvalidArgumentException("Invalid endpoint name $endpoint!");
            }
        }

        /**
         * Get specific size URL of the required image.
         * @param $image_id - The ID of the image
         * @param $size - The requested size of the image. Refer to the documentation for possible values.
         * @see https://api-docs.igdb.com/#images
         * @return string The constructed URL for the image
         * @throws InvalidArgumentException If the size parameter is not valid.
         */
        public static function image_url($image_id, $size) {
            $possible_sizes = array("cover_small", "screenshot_med", "cover_big", "logo_med", "screenshot_big", "screenshot_huge", "thumb", "micro", "720p", "1080p");

            if(array_search($size, $possible_sizes) === false) {
                throw new InvalidArgumentException("Invalid size parameter " . $size . " for image_url!");
            }

            return "https://images.igdb.com/igdb/image/upload/t_$size/$image_id.jpg";
        }
    }

?>