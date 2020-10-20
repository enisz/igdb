<?php

    require '../src/class.igdb.php';

    // Instantiate the class
    $IGDB = new IGDB("client_id", "access_token");

    // Setting up the query parameters
    $query = array(
        'search' => 'uncharted', // searching for games LIKE uncharted
        'fields' => array(       // we want to see these fields in the results
            'id',
            'name',
            'cover'
        ),
        'limit' => 5,            // we only need maximum 5 results per query (pagination)
        'offset' => 10           // we would like to show the third page; fetch the results from the tenth element (pagination)
    );

    try {
        // Running the query against IGDB; passing the query
        $result = $IGDB->game($query);

        // Showing the result
        var_dump($result);
    } catch (Exception $e) {
        // Catching Exceptions, if there is any
        echo $e->getMessage();
    }

?>