<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    $options = array(
        'search' => 'star wars', // Searching Star Wars games
        'fields' => array('id', 'name'), // Showing ID and NAME fields in the results
        'order' => array(
            'field' => 'name', // ORDER the results by NAME field
            'direction' => 'asc', // ASCENDING order
        )
    );

    /*
        You can also provide the direction parameter as a string.
        
        $options['direction'] = 'name:asc';
    */

    $result = $IGDB->game($options);

    var_dump($result);

?>