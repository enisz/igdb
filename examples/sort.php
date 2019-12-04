<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    $options = array(
        'search' => 'star wars',         // Searching Star Wars games
        'fields' => array('id', 'name'), // Showing ID and NAME fields in the results
        'sort' => array(
            'field' => 'name',           // ORDER the results by NAME field
            'direction' => 'asc',        // ASCENDING order
        )
    );

    /*
        You can also provide the sort parameter as a string with apicalypse syntax.

        $options['sort'] = 'name asc';
    */

    $result = $IGDB->game($options);

    var_dump($result);

?>