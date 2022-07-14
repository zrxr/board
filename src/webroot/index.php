<?php /**
 * 
 *  src/webroot/index.php
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

    function env() {
        $env = array();

        // env var.
        if( isset($_ENV['BOARD_VAR']) ){
            $env['var'] = $_ENV['BOARD_VAR'];
        } elseif ( is_dir('/var/board') && is_writable('/var/board') ) {
            $env['var'] = '/var/board';
        } else {
            $env['var'] = __DIR__.'/../../var';
        }

        return $env;
    }

    function save($data) {

        // env.
        $env = env();

        // save dir.
        $save_dir = $env['var'].'/requests';

        // save dir create.
        if( ! is_dir($save_dir) ){ mkdir($save_dir); }

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

    // request type [default].
    $REQUEST_TYPE = "storage";

    // request type detect.
    if( $_SERVER['REQUEST_METHOD'] == 'GET' ){
        if( $_SERVER['QUERY_STRING'] == 'board-request-type-api' ){
            $REQUEST_TYPE = "api";
        } elseif( empty($_SERVER['QUERY_STRING']) ){
            $REQUEST_TYPE = "interactive";
        } else {
            $REQUEST_TYPE = "storage";
        }
    }

?>
<?php

// handle request type [api].
if( $REQUEST_TYPE == "api" ){

    //files = scandir()

    // env.
    $env = env();

    // requests dir.
    $requests_dir = $env['var'].'/requests';

    // requests data.
    $requests_data = array();

    // request files.
    $request_files = scandir($requests_dir);
    rsort($request_files);

    // requests.
    foreach( $request_files as $request_file ){
        if( str_ends_with($request_file, '.json') ){
        
            // request json.
            $request_json = file_get_contents("{$requests_dir}/${request_file}");

            // request json decode.
            $request_data = json_decode($request_json, true);

            // request pieces / epoch / date.
            $request_file_pieces = explode('.', $request_file);
            $request_file_epoch  = $request_file_pieces[1];
            $request_file_date   = date('r', intval($request_file_epoch));

            // requests data append request data.
            $requests_data[] = array(
                "meta" => array(
                    "file"  => $request_file,
                    "epoch" => $request_file_epoch,
                    "date"  => $request_file_date
                ),
                "data" => $request_data
            );

        }
    }

    // requests json encode.
    $requests_json = json_encode($requests_data);

    // requests headers.
    header('Content-Type: application/json; charset=utf-8');

    // requests response.
    print($requests_json);

}

?>
<?php

    // handle request type [interactive].
    if( $REQUEST_TYPE == "interactive" ){

        // todo ...
        echo "interactive mode coming soon ...";

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
