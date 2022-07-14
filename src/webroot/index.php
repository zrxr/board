<?php /**
 * 
 *  src/webroot/indx.php
 * 
 */?>
<?php

    function collect() {
        $data            = array();
        $data['env']     = $_ENV;
        $data['get']     = $_GET;
        $data['post']    = $_POST;
        $data['request'] = $_REQUEST;
        $data['server']  = $_SERVER;
        return $data;
    }

    function dump($data) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    function save($data) {

        // save directory.
        if( isset($_ENV['BOARD_VAR']) ){
            $save_dir = $_ENV['BOARD_VAR'] . '/requests';
        } elseif ( is_dir('/var/board/requests') ) {
            $save_dir = '/var/board/requests';
        } else {
            $save_dir = __DIR__.'/../../var/requests';
        }

        // save file.
        $save_file = "request.".time().".json";

        // save file exists filename extend.
        while( file_exists("{$save_dir}/${save_file}") ){
            $save_file = 'request.'.time().'-'.rand(10000000,99999999).'.json';
        }

        // save data json.
        $json = json_encode($data, JSON_PRETTY_PRINT);

        // put contents.
        file_put_contents("{$save_dir}/${save_file}", $json);

    }

?>
<?php

    // request type.
    $REQUEST_TYPE = "storage";

    // request type [interactive].
    if( $_SERVER['REQUEST_METHOD'] == 'GET' ){
        if( $_SERVER['QUERY_STRING'] == '' ){
            $REQUEST_TYPE = "interactive";
        }
    }

?>
<?php

    // handle request type [storage].
    if( $REQUEST_TYPE == "storage" ){

        // data collect.
        $data = collect();

        // data save.
        save($data);

        // data dump.
        dump($data);

    }

?>
<?php

// handle request type [interactive].
if( $REQUEST_TYPE == "interactive" ){

    // todo ...
    echo "interactive mode coming soon ...";

}

?>
