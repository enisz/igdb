<?php

    // As per the IGDB Documentation the expander parameter is "dead".
    // Still, expanded fields can now be added as normal fields and the API figures out the rest.

    include '../src/class.igdb.php';

    $IGDB = new IGDB("client_id", "access_token");

    $query = array(
        'id' => array(1,2,3),   // first three games by ID
        'fields' => array(
            'name',
            'themes.url',       // Fields can be expanded with a dot followed by the fields you want to access from a certain endpoint
            'themes.name',
            'themes.slug'
        )
    );

    $result = $IGDB->game($query);

    var_dump($result);

?>