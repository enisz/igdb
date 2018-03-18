<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    $options = array(
        'search' => 'star wars', // Searching Star Wars games
        'fields' => array('id', 'name'), // Showing ID and NAME fields in the results
        'order' => array(
            'field' => 'release_dates.date', // ORDER the results by RELEASE DATES
            'direction' => 'desc', // DESCENDING order
            'subfilter' => 'min' // ORDERING by the lowest RELEASE DATE value
        )
    );

    /*
        You can also provide the direction parameter as a string.
        
        $options['direction'] = 'release_dates.date:desc:min';
    */

    $result = $IGDB->game($options);

    var_dump($result);

?>