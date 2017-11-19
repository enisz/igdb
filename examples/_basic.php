<?php

    require '../src/class.igdb.php';

    // Instantiate the class
    $IGDB = new IGDB('<YOUR API URL>', '<YOUR API KEY>');

    // Setting up the query parameters
    $options = array(
        'search' => 'uncharted', // searching for games LIKE uncharted
        'fields' => array('id', 'name', 'cover'), // we want to see these values in the results
        'limit' => 5, // we only need maximum 5 results per query (pagination)
        'offset' => 10, // we would like to show the third page; fetch the results from the tenth element (pagination)
        'order' => 'name:asc' // order the results ASCENDING by the filed NAME
    );

    try
    {
        // Running the query against IGDB; passing the options parameter
        $result = $IGDB->game($options);

        // Showing the result
        var_dump($result);   
    }

    catch (Exception $e)
    {
        // Catching Exceptions, if there is any
        echo $e->getMessage();
    }

?>