<?php

    require '../src/class.igdb.php';

    $IGDB = new IGDB('8698ac0af2ff2808a9ecf91237ff560f');

    $result = $IGDB->api_status();

    var_dump($result[0]);

?>