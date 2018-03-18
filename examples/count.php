<?php

    require '../src/class.igdb.php';

    $IGDB = new IGDB('<YOUR API KEY>');

    $options = array(
        'filter' => array(
            'field' => 'rating', // rating
            'postfix' => 'gt',   // greater than
            'value' => 75        // 75
        )
    );

    // Will return the number of all games with rating more than 75
    $result = $IGDB->count('game', $options);

    var_dump($result);   

?>