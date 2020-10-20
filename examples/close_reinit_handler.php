<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("client_id", "access_token");

    $query = array(
        'search' => 'wolfenstein',
        'fields' => 'id, name',
        'limit' => 2
    );

    $result = $IGDB->game($query);

    var_dump($result);

    // Closing the CURL handler
    $IGDB->curl_close();

    /*

        Some other code

    */

    // If you want to run another query after closing the CURL handler
    // You have to reinitialize it in order to make it work
    $IGDB->curl_reinit();

    // You can define a new query, or you can modify the previous one
    // Now I'm defining a new one to use a different endpoint with the same instance
    $second_options = array(
        'search' => 'xbox',
        'fields' => 'id, name, slug'
    );

    $second_result = $IGDB->platform($second_options);

    var_dump($second_result);

?>