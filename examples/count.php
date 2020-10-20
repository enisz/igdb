<?php

    require '../src/class.igdb.php';

    $IGDB = new IGDB("client_id", "access_token");

    $query = array(
        'where' => array(
            'field' => 'rating', // rating
            'postfix' => '>',    // greater than
            'value' => 75        // 75
        )
    );

    // Will return the number of all games with rating more than 75
    // Note the second true parameter
    $result = $IGDB->game($query, true);

    var_dump($result);

?>