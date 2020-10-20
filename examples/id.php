<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("client_id", "access_token");

    // One ID is passed as parameter
    $query = array(
        'id' => 1,
        'fields' => array(
            'id',
            'name'
        )
    );

    $result = $IGDB->game($query);

    // Only one result in the array
    var_dump($result);

    // Now update the ID fields with multiple ID-s (pass ID-s as array)
    // Note, that the fields parameter is untouched
    $query['id'] = array(1,2,3);

    $result = $IGDB->game($query);

    // In this case you will have 3 games in the result array
    var_dump($result);

?>