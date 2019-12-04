<?php

    require '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    $options = array(
        'fields' => 'id, name, platforms, genres', // we want to see these fields in the result
        'where' => array(
            'field' => 'release_dates.platform',   // filtering by the platform field
            'postfix' => '=',                      // equals postfix
            'value' => 8                           // looking for platforms with the ID equals to 8
        )
    );

    /*
        You can also provide the filter parameter as a string with apicalypse syntax.

        $options['where'] = 'release_dates.platform = 8';
    */

    $result = $IGDB->game($options);

    var_dump($result);

?>