<?php

    /**
     * IGDB Utils
     *
     * A utility class for useful methods
     *
     * @version 1.0.0
     * @author Enisz Abdalla <enisz87@gmail.com>
     * @link https://github.com/enisz/igdb
     */

    require_once "IGDBInvalidParameterException.php";

    class IGDBUtils {

        /**
         * Helper method to authenticate your application via Twitch
         * @param $client_id - Your client ID from twitch
         * @param $client_secret - The generated secret token for your application
         * @return $response - the response object from Twitch with properties access_token, expires_in, token_type
         * @throws Exception If a non-success response is returned
         */
        public static function authenticate($client_id, $client_secret) {
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
                throw new Exception($response->message, $response->status);
            } else {
                return $response;
            }
        }

        /**
         * Get specific size URL of the required image.
         * @param $image_id - The ID of the image
         * @param $size - The requested size of the image. Refer to the documentation for possible values.
         * @see https://api-docs.igdb.com/#images
         * @return string The constructed URL for the image
         * @throws IGDBInvalidParameterException If the size parameter is not valid.
         */
        public static function image_url($image_id, $size) {
            $possible_sizes = array(
                "cover_small",
                "cover_small_2x",
                "screenshot_med",
                "screenshot_med_2x",
                "cover_big",
                "cover_big_2x",
                "logo_med",
                "logo_med_2x",
                "screenshot_big",
                "screenshot_big_2x",
                "screenshot_huge",
                "screenshot_huge_2x",
                "thumb",
                "thumb_2x",
                "micro",
                "micro_2x",
                "720p",
                "720p_2x",
                "1080p",
                "1080p_2x"
            );

            if(array_search($size, $possible_sizes) === false) {
                throw new IGDBInvalidParameterException("Invalid size parameter " . $size . " for image_url!");
            }

            return "https://images.igdb.com/igdb/image/upload/t_$size/$image_id.jpg";
        }
    }

?>