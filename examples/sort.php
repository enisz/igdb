<?php

    include '../src/class.igdb.php';

    $IGDB = new IGDB("client_id", "access_token");

    // Note: Search is sorting on relevancy and therefore sort is not applicable on search
    // Do not provide search in queries where sort is applied
    $query = array(
        'id' => array(100,200,300),     // Fetching games with these ID's
        'fields' => array(              // Showing ID and NAME fields in the results
            'id',
            'name'
        ),
        'sort' => array(
            'field' => 'name',          // ORDER the results by NAME field
            'direction' => 'asc',       // ASCENDING order
        )
    );

    /*
        You can also provide the sort parameter as a string with apicalypse syntax.

        $query['sort'] = 'name asc';
    */

    $result = $IGDB->game($query);

    var_dump($result);

?>