<?php /**
 * 
 *  src/webroot/index.php
 * 
 */?>
<?php

    function collect() {
        $data            = array();
        $data['get']     = $_GET;
        $data['json']    = collect_json();
        $data['post']    = $_POST;
        $data['request'] = $_REQUEST;
        $data['server']  = $_SERVER;
        return $data;
    }
    function collect_json() {
        $json = file_get_contents('php://input');
        return json_decode($json);
    }

    function dump($data) {
        header('Content-Type: application/json; charset=utf-8');
        print(json_encode($data));
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
        } elseif( str_starts_with($_SERVER['QUERY_STRING'], "board-request-type-file") ){
            $REQUEST_TYPE = "file";
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

    // env.
    $env = env();

    // requests dir.
    $requests_dir = $env['var'].'/requests';

    // requests data.
    $requests_data = array();

    // requests limit.
    $requests_limit = 16;

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
                    "id"    => str_replace('.', '-', $request_file),
                    "file"  => $request_file,
                    "epoch" => $request_file_epoch,
                    "date"  => $request_file_date
                ),
                "data"   => $request_data
            );

            // requests limit.
            if( count($requests_data) == $requests_limit ){
                break;
            }

        }
    }

    // requests dump.
    dump($requests_data);

}

?>
<?php

// handle request type [storage].
if( $REQUEST_TYPE == "file" ){

    // env.
    $env = env();

    // request dir.
    $requests_dir = $env['var'].'/requests';

    // request file.
    $request_file = $_GET['board-request-type-file'];
        
    // request json.
    $request_json = file_get_contents("{$requests_dir}/${request_file}");

    // request dump.
    dump(json_decode($request_json));

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

// <-- INTERACTIVE_MODE_TEMPLATE_START --> 

?>

<!doctype html>

<html lang="en">
<head>
    <title>Board</title>
    <meta charset="utf-8">
    <meta name="viewport"           content="width=device-width, initial-scale=1">
    <meta name="description"        content="A board to chuck requests at.">
    <meta name="author"             content="zrxr">
    <meta property="og:title"       content="Board">
    <meta property="og:type"        content="website">
    <meta property="og:description" content="A board to chuck requests at.">
    <meta property="og:image"       content="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@700&display=swap" rel="stylesheet" >

    <style>

        * {
            padding: 0;
            margin:  0;
            -ms-overflow-style: none;
            scrollbar-width: none;
            text-decoration: none;
        }
        *::-webkit-scrollbar {
            display: none;
        }

        .bg-background {
            background-color: #202225;
        }
        .bg-midground {
            background-color: #2f3136;
        }
        .bg-foreground {
            background-color: #36393f;
        }
        .text-background {
            color: #202225;
        }
        .text-midground {
            color: #2f3136;
        }
        .text-foreground {
            color: #888888;
        }

        html, body, .container {
            height: 100%;
            width:  100%;
            min-height: 800px;
        }

        .requests {
            height: 100%;
            width:  100%;
        }
        .requests .request {
            border: 2px solid #202225;
            height: 100px;
            width:  calc( 100% - 4px );
            overflow: hidden;
            cursor: pointer;
        }
        .requests .request.hidden {
            display: none;
        }
        .requests .request .title {
            text-align: center;
            padding: 28px;
        }
        .requests .request .title h1 {
            font-family: 'Karla', sans-serif;
        }
        .requests .request .body {
            text-align: center;
        }
        .requests .request .body.hidden {
            display: none;
        }

    </style>

</head>
<body>
    <div class="container">

        <!-- requests. -->
        <div class="requests bg-background">
        </div>

    </div>
    <script src="jquery.js"></script>
    <script>
        function refresh() {

            // fetch latest requests.
            $.get( "index.php?board-request-type-api", function( data ) {

                // iterate data in reverse order.
                $.each(data.reverse(), function(i, request) {

                    // if not displayed, ignore already visible.
                    if($("#" + request['meta']['id']).length == 0) {

                        // console log.
                        console.log(request['data']);

                        // prepend request.
                        $(".requests").prepend(
                            '<div id="' + request['meta']['id'] + '" class="request bg-midground text-foreground hidden">' +
                                '<a href="index.php?board-request-type-file=' + request['meta']['file'] + '">' +
                                    '<div class="title text-foreground">' +
                                        '<h1>' + request['meta']['date'] + '</h1>' +
                                    '</div>' +
                                '</a>' +
                            '</div>'
                        );

                        // prepend request animate.
                        setTimeout(function() {
                            $("#" + request['meta']['id']).show("slow");
                        }, 100);

                    }

                });

                // refresh.
                setTimeout(function() {
                    refresh();
                }, 400);

            });
        }
        $(document).ready(function() { refresh(); });
    </script>
</body>
</html>

<?php

// <-- INTERACTIVE_MODE_TEMPLATE_END --> 

}

?>
 