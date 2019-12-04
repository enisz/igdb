<?php

    // As per the IGDB Documentation the expander parameter is "dead".
    // Still, expanded fields can now be added as normal fields and the API figures out the rest.

    include '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    $options = array(
        'id' => array(1,2,3),                                                  // first three games by ID
        'fields' => array('name', 'themes.url', 'themes.name', 'themes.slug'), // Fields can be expanded with a dot followed by the fields you want to access from a certain endpoint
    );

    $result = $IGDB->game($options);

    var_dump($result);

?>