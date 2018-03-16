<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    // As search parameter you can pass any string you want to find
    $options = array(
        'search' => 'wolfenstein 2 new colossus',
        'fields' => array('id', 'name')
    );

    $result = $IGDB->game($options);

    var_dump($result);

?>