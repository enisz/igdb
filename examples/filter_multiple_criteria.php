<?php

    require '../src/class.igdb.php';

    // Instantiate the class
    $IGDB = new IGDB('<YOUR API URL>', '<YOUR API KEY>');

    // Setting up the query parameters
    $options = array(
        'search' => '', // looking for every game
        'fields' => 'id, name, platforms, genres', // we want to see these fields in the result
        'filter' => array(
            array(
                'field' => 'release_dates.platform', // filtering by the platform field
                'postfix' => 'eq', // equals postfix
                'value' => 8 // looking for platforms with the ID equals to 8
            ),
            array(
                'field' => 'total_rating', // filtering by the total_rating field
                'postfix' => 'gte', // greater than or equals to
                'value' => 70 // looking for the total_rating greater than or equals to 70
            ),
            array(
                'field' => 'genres', // filtering by the genres field
                'postfix' => 'eq', // equals to
                'value' => 4 // looking for the genres equals to 4
            )
        )
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