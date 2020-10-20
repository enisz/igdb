<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("client_id", "access_token");

    $query = array(
        'search' => 'xbox',     // Searching the platforms for XBOX.
        'fields' => array(      // Showing ID, NAME and SLUG fields in the results
            'id',
            'name',
            'slug'
        )
    );

    $result = $IGDB->platform($query);

    var_dump($result);

?>