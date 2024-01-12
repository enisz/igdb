<?php

    /**
     * IGDB Utils
     *
     * A utility class for useful methods
     *
     * @version 2.0.0
     * @author Enisz Abdalla <enisz87@gmail.com>
     * @link https://github.com/enisz/igdb
     */

    require_once "IGDBInvalidParameterException.php";
    require_once "IGDBConstants.php";

    class IGDBUtils {

        /**
         * Helper method to authenticate your application via Twitch
         * @param $client_id - Your client ID from twitch
         * @param $client_secret - The generated secret token for your application
         * @return $response - the response object from Twitch with properties access_token, expires_in, token_type
         * @throws Exception If a non-success response is returned
         */
        public static function authenticate($client_id, $client_secret) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_URL, "https://id.twitch.tv/oauth2/token?client_id=$client_id&client_secret=$client_secret&grant_type=client_credentials");

            $response = json_decode(curl_exec($ch));
            $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if($response_code < 200 || $response_code > 299) {
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
            if(array_search($size, IGDBW_IMAGE_SIZES) === false) {
                throw new IGDBInvalidParameterException("Invalid size parameter $size for image_url!");
            }

            return "https://images.igdb.com/igdb/image/upload/t_$size/$image_id.jpg";
        }

        /**
         * Create a webhook
         * @param $client_id IGDB Client ID
         * @param $access_token generated IGDB Access Token
         * @param $endpoint name of the endpoint for the webhook
         * @param $method type of the expected data
         * @param $url the url expecting the webhook data
         * @param $secret password for the webhook
         * @see https://api-docs.igdb.com/#webhooks
         * @return object registered webhook data
         * @throws IGDBInvalidParameterException if the name of the endpoint is invalid
         * @throws IGDBInvalidParameterException if the method parameter is invalid
         * @throws Exception If a non-success response is returned
         */
        public static function create_webhook($client_id, $access_token, $endpoint, $method, $url, $secret) {
            if(!array_key_exists($endpoint, IGDBW_ENDPOINTS)) {
                throw new IGDBInvalidParameterException("Invalid Endpoint name $endpoint!");
            }

            if(!in_array(strtolower($method), IGDBW_WEBHOOK_ACTIONS)) {
                throw new IGDBInvalidParameterException("Invalid method '$method'! It has to be one of " . implode(", ", IGDBW_WEBHOOK_ACTIONS) . "!");
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_URL, IGDBW_API_URL . "/" . IGDBW_ENDPOINTS[$endpoint] . "/webhooks");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Client-ID: $client_id",
                "Authorization: Bearer $access_token",
                "Content-Type: application/x-www-form-urlencoded"
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, "url=$url&method=$method&secret=$secret");

            $response = json_decode(curl_exec($ch));
            $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if($response_code < 200 || $response_code > 299) {
                throw new Exception("Failed to create webhook!", $response_code);
            } else {
                return $response;
            }
        }

        /**
         * Delete a webhook
         * @param $client_id IGDB Client ID
         * @param $access_token generated IGDB Access Token
         * @param $id ID of the webhook
         * @see https://api-docs.igdb.com/#webhooks
         * @return object deleted webhook data
         * @throws Exception If a non-success response is returned
         */
        public static function delete_webhook($client_id, $access_token, $id) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_URL, IGDBW_API_URL . "/webhooks/$id");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Client-ID: $client_id",
                "Authorization: Bearer $access_token"
            ));

            $response = json_decode(curl_exec($ch));
            $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if($response_code < 200 || $response_code > 299) {
                throw new Exception("Failed to delete webhook!", $response_code);
            } else {
                return $response;
            }
        }

        /**
         * Get a webhook
         * @param $client_id IGDB Client ID
         * @param $access_token generated IGDB Access Token
         * @param $id ID of the webhook
         * @see https://api-docs.igdb.com/#webhooks
         * @return object webhook data
         */
        public static function get_webhook($client_id, $access_token, $id) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_URL, IGDBW_API_URL . "/webhooks/$id");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Client-ID: $client_id",
                "Authorization: Bearer $access_token"
            ));

            return json_decode(curl_exec($ch));
        }

        /**
         * Get all webhooks
         * @param $client_id IGDB Client ID
         * @param $access_token generated IGDB Access Token
         * @see https://api-docs.igdb.com/#webhooks
         * @return array all webhooks
         */
        public static function get_webhooks($client_id, $access_token) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_URL, IGDBW_API_URL . "/webhooks");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Client-ID: $client_id",
                "Authorization: Bearer $access_token"
            ));

            return json_decode(curl_exec($ch));
        }
    }

?>
