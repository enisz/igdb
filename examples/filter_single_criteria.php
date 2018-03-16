<?php

    require '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    $options = array(
        'search' => '', // looking for every game
        'fields' => 'id, name, platforms, genres', // we want to see these fields in the result
        'filter' => array(
            'field' => 'release_dates.platform', // filtering by the platform field
            'postfix' => 'eq', // equals postfix
            'value' => 8 // looking for platforms with the ID equals to 8
        )
    );

    /*
        You can also provide the filter parameter as a string.
        Note: You can use this only when you want to filter by only one criteria.
        
        $options['filter'] = '[release_dates.platform][eq]=8';
    */

    $result = $IGDB->game($options);

    var_dump($result);

?>