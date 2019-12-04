<?php

    require '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    $options = array(
        'where' => array(
            'field' => 'rating', // rating
            'postfix' => '>',    // greater than
            'value' => 75        // 75
        )
    );

    // Will return the number of all games with rating more than 75
    $result = $IGDB->game($options, true);

    var_dump($result);

?>