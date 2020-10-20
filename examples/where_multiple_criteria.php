<?php

    require '../src/class.igdb.php';

    $IGDB = new IGDB("client_id", "access_token");

    $query = array(
        'fields' => 'id, name, platforms, genres',   // we want to see these fields in the result
        'where' => array(
            array(
                'field' => 'release_dates.platform', // filtering by the platform field
                'postfix' => '=',                    // equals postfix
                'value' => 8                         // looking for platforms with the ID equals to 8
            ),
            array(
                'field' => 'total_rating',           // filtering by the total_rating field
                'postfix' => '>=',                   // greater than or equals to
                'value' => 70                        // looking for the total_rating greater than or equals to 70
            ),
            array(
                'field' => 'genres',                 // filtering by the genres field
                'postfix' => '=',                    // equals to
                'value' => 4                         // looking for the genres equals to 4
            )
        )
    );

    $result = $IGDB->game($query);

    var_dump($result);

?>