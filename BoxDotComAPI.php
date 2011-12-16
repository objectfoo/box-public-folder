<?php
class BoxDotComAPI {
    var $URI;
    
    public function __construct ($URI) {
        $this->URI = $URI;
    }
    
    public function getPublicFolder( $count = 10 ) {
        $filesToGet = $count - 1;
        $URI = $this->URI;
        $XML = wp_remote_get ($URI);

        $folderFeed = simplexml_load_string($XML['body']);
        
        $folderArray = json_encode($folderFeed);
        $folderArray = json_decode($folderArray);
        
        $totalDocuments = 

        $response = array(
            'title'         => (string)$folderArray->channel->title,
            'link'          => (string)$folderArray->channel->link,
            'description'   => (string)$folderArray->channel->description,
            'item'          => array_slice($folderArray->channel->item, 0 , $count)
        );

        $response = json_encode($response);
        return $response;
    }
}