<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("client_id", "access_token");

    // Leaving the optional third parameter, sending the request without any filters
    // Also asking for the record count by providing /count after the endpoint name
    // The method name has to be the IGDB endpoint name instead of the wrapper class method name => platforms instead of platform
    var_dump($IGDB->mutliquery("platforms/count", "Count of Platforms"));

    $query = array(
        "fields" => array(
            "name",
            "platforms.name"
        ),
        "where" => array(
            "platforms != null",
            "platforms = {48}"
        ),
        "limit" => 1
    );

    // Passing a query array as a third parameter to filter the results
    var_dump($IGDB->mutliquery("games", "Playstation Games", $query));

?>