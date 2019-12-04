<?php

    require '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    $options = array(
        'fields' => 'id, name, platforms, genres',  // we want to see these fields in the result
        'where' => array(                           // make sure to have each criteria as a separate element in the array
            'release_dates.platform = 8',           // and separate field names, postfixes and values with space
            'total_rating >= 70',
            'genres = 4'
        )
    );

    $result = $IGDB->game($options);

    var_dump($result);

?>