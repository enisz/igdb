<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API URL>', '<YOUR API KEY>');

    // As search parameter you can pass any string you want to find
    $options = array(
        'search' => 'wolfenstein 2 new colossus',
        'fields' => array('id', 'name')
    );

    // The result array will hold maximum 10 records because of the default limit
    $result = $IGDB->game($options);

    var_dump($result);

?>