<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    $options = array(
        'search' => 'star wars', // Searching Star Wars games
        'fields' => array('id', 'name'), // Showing ID and NAME fields in the results
        'order' => array(
            'field' => 'name', // ORDER the results by NAME field
            'order' => 'asc', // ASCENDING order
        )
    );

    /*
        You can also provide the order parameter as a string.
        
        $options['order'] = 'name:asc';
    */

    $result = $IGDB->game($options);

    var_dump($result);

?>