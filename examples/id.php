<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API URL>', '<YOUR API KEY>');

    // One ID is passed as parameter
    $options = array(
        'id' => 1,
        'fields' => array('id', 'name')
    );

    $result = $IGDB->game($options);

    // Only one result in the array
    var_dump($result);

    // Now update the ID fields with multiple ID-s (pass ID-s as array)
    // Note, that the fields parameter is untouhed
    $options['id'] = array(1,2,3);

    $result = $IGDB->game($options);

    // In this case you will have 3 games in the result array
    var_dump($result);

?>