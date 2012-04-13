<?php
class BoxDotComAPI {
    var $URI;
    
    public function __construct ($URI) {
        $this->URI = $URI;
    }

    public function getPublicFolder( $count = 10 ) {
        $filesToGet = $count - 1;
        $XML = wp_remote_get ($this->URI . '/rss.xml');
        $folderFeed = simplexml_load_string($XML['body']);
        $folderArray = json_decode(json_encode($folderFeed)); // quick & dirty convert xml to array
        $response = array(
            'title'         => (string)$folderArray->channel->title,
            'link'          => (string)$folderArray->channel->link,
            'description'   => (string)$folderArray->channel->description,
            'item'          => array_slice($folderArray->channel->item, 0 , $count)
        );

        return json_encode($response);
    }
}