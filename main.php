<?php
echo str_repeat("-",50) . "\r\n";

$basePath   = '/var/www/share';

//BASE PATH ENV
if(isset($_ENV['NBSHOME'])){

    $basePath   = $_ENV['NBSHOME'];
}

//INI FILE
include $basePath . '/nbsflow.ini.php';

include 'includes/errors.php';
include 'includes/config.php';
include 'includes/loader.php';

//APPID
$appId              = $defaultAppId;

if(isset($_ENV['NBSAPP'])){

    $appId          = $_ENV['NBSAPP'];
}

$config             = $getNbsFlowConfig($basePath, $appId);
 
$loader             = $getNbsFlowLoader($basePath, $config);

$containerBuilder   = new \DI\ContainerBuilder();

//TODO: Configurar el compilador de DI
/*
$containerBuilder->enableCompilation(__DIR__ . '/tmp');
$containerBuilder->writeProxiesToFile(true, __DIR__ . '/tmp/proxies');
*/
/*
$containerBuilder->addDefinitions([
    'globalScope' => \DI\create('Nubesys\Flow\Core\Register')
]);
*/

$container          = $containerBuilder->build();

//CLASS LOADER
$container->set('classLoader', $loader);

//DI GLOBAL SCOPE SERVICE
$container->set('globalScope', function() {
    
    return new \Nubesys\Flow\Core\Register();
});

$container->get('globalScope')->set("app",$appId);

$container->get('globalScope')->set("env",$_ENV);

//ARGS
$argIndex           = 0;
foreach($argv as $arg){

    $argParts = explode(":", $arg);

    if(is_array($argParts) && count($argParts) == 2){

        $valueParts = explode(",", $argParts[1]);

        if(is_array($valueParts) && count($valueParts) >= 2){

            $container->get('globalScope')->set("arguments." . $argParts[0],$valueParts);
        } else {

            $container->get('globalScope')->set("arguments." . $argParts[0],$valueParts[1]);
        }
    } else {

        $container->get('globalScope')->set("arguments." . $argIndex, $argParts[0]);
    }
}

$container->get('globalScope')->set("config",$config);

//DI DOCKER MANAGER
//TODO: SOLUCIONAR EL TIEMPO DE ESPERA EN EL SOCKET, CON UNA CORUTINA O ALGUNA OTRA FORMA
sleep(2);
$container->set('dockerManager', function() {
    
    //TODO: QUE LEA EL PATH DEL SOCKET DESDE CONFIG
    return new Nubesys\Flow\Docker\DockerManager('/var/run/docker.sock');
});

//DOCKER CONTAINERS
$container->get('globalScope')->set("machine.id", exec("cat /proc/1/cpuset | cut -c9-"));

foreach($container->get('dockerManager')->lsContainers() as $dockerContainer){
    
    if($dockerContainer['id'] == $container->get('globalScope')->get("machine.id")){

        $container->get('globalScope')->set("machine", $dockerContainer);
    }
}

//ENGINE
if($container->get('globalScope')->has("config.main.defaultAppEngine")){

    $container->get('globalScope')->set("engine", $container->get('globalScope')->get("config.main.defaultAppEngine"));
}

if($container->get('globalScope')->has("env.NBSENGINE")){

    $container->get('globalScope')->set("engine", $container->get('globalScope')->get("env.NBSENGINE"));
}

if($container->get('globalScope')->has("engine")){

    $engineFilePath = 'engines/' . $container->get('globalScope')->get("engine") . '.php';

    if(file_exists($engineFilePath)){

        include $engineFilePath;
    }else{

        //TODO: No hay engine!
    }
}