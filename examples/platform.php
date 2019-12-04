<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    $options = array(
        'search' => 'xbox',                     // Searching the platforms for XBOX.
        'fields' => array('id', 'name', 'slug') // Showing ID, NAME and SLUG fields in the results
    );

    $result = $IGDB->platform($options);

    var_dump($result);

?>