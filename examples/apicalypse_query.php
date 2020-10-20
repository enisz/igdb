<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("client_id", "access_token");

    // Apicalypse formatted query string
    $query = 'search "uncharted"; fields id,name,cover; limit 5; offset 10;';

    var_dump($IGDB->game($query));

?>