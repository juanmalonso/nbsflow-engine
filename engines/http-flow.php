<?php

if($container->get('globalScope')->has("machine.ports")){

    $ports                      = array();

    foreach($container->get('globalScope')->get("machine.ports") as $port){

        if($port["ip"] == '0.0.0.0'){

            $ports[]            = $port['private'];
        }
    }

    //SWOOLE SERVER
    $server                     = new Swoole\HTTP\Server("localhost", 0);

    //SERVER CONFIG
    $serverConfig               = [
        'worker_num'            => 4,      // The number of worker processes to start
        //'task_worker_num'       => 4,  // The amount of task workers to start
        'backlog'               => 128,       // TCP backlog connection number
        'upload_tmp_dir'        => "/tmp/",
        'http_parse_post'       => true,
        'http_parse_cookie'     => true,
        'http_parse_files'      => true,
    ];

    $server->set($serverConfig);

    echo "CREATING SERVER \r\n";
    
    //ADD PORTS
    foreach($ports as $port){

        $server->listen("localhost", $port, SWOOLE_SOCK_TCP);

        echo "SERVER LISTEN IN localhost:$port\r\n";
    }

    $httpFlowApp                = new Nubesys\Flow\Core\App\HttpFlowApp($container, $server);

    $server->on("Start", array($httpFlowApp, 'onServerStart'));

    $server->on("WorkerStart", array($httpFlowApp, 'onWorkerStart'));

    $server->on("Request", array($httpFlowApp, 'onRequest'));

    /*
    // Triggered when new worker processes starts
    $server->on("WorkerStart", function($server, $workerId)
    {
        // ...
    });

    // Triggered when the HTTP Server starts, connections are accepted after this callback is executed
    $server->on("Start", function($server, $workerId)
    {
        // ...
    });

    // The main HTTP server request callback event, entry point for all incoming HTTP requests
    $server->on('Request', function(Swoole/Server/Request $request, Swoole/Server/Response $response)
    {
        $response->end('<h1>Hello World!</h1>');
    });

    // Triggered when the server is shutting down
    $server->on("Shutdown", function($server, $workerId)
    {
        // ...
    });

    // Triggered when worker processes are being stopped
    $server->on("WorkerStop", function($server, $workerId)
    {
        // ...
    });
    */

    /*
    $server->on("start", function (Swoole\Http\Server $server) {

        var_dump("START SERVER");
        //var_dump($ser  ver);
    });

    $server->on("request", function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
        
        var_dump("REQUEST");

        $response->header("Content-Type", "text/plain");
        
        $response->write("asd");
        $response->end();
    });
    */

    $server->start();

}else{

    //TODO: NO HAY PUERTOS
}

/*

$server = new Swoole\HTTP\Server("localhost", 8899);

$server->on("start", function (Swoole\Http\Server $server) {

    var_dump("START");
});

$server->on("request", function (Swoole\Http\Request $request, Swoole\Http\Response $response) use ($server) {
    
    var_dump("RESPONSE");

    $response->header("Content-Type", "text/plain");
    $response->end("asd");
});

$server->start();
*/
