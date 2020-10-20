<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("client_id", "access_token");

    $query = array(
        'search' => 'wolfenstein 2 new colossus',   // As search parameter you can pass any string you want to find
        'fields' => array(
            'id',
            'name'
        )
    );

    $result = $IGDB->game($query);

    var_dump($result);

?>