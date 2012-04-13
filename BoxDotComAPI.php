<?php
class BoxDotComAPI {
    var $URI;
    
    public function __construct ($URI) {
        $this->URI = $URI;
    }

    public function getPublicFolder( $count = 10 ) {
        $filesToGet = $count - 1;
        $XML = wp_remote_get ($this->URI);

        $folderFeed = simplexml_load_string($XML['body']);

        /*
            TODO test see if this is necessary
        */
        $folderArray = json_decode(json_encode($folderFeed));
        
        $response = array(
            'title'         => (string)$folderArray->channel->title,
            'link'          => (string)$folderArray->channel->link,
            'description'   => (string)$folderArray->channel->description,
            'item'          => array_slice($folderArray->channel->item, 0 , $count)
        );

        /*
            TODO test refactor
        */
        // $response = json_encode($response);
        // return $response;

        return json_encode($response);
    }
}