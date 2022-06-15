<?php

return $getNbsFlowLoader = function ($p_basePath, $p_config) {

    $loader = require_once $p_basePath . '/lib/vendor/autoload.php';
    
    if(file_exists($p_basePath . '/src')){

        if ($gestor = opendir($p_basePath . '/src')) {
            
            while (false !== ($entrada = readdir($gestor))) {
                
                if(substr($entrada, 0, 7) == 'nbsflow'){
    
                    $packageDevPath = $p_basePath . '/src/' . $entrada;
                    
                    if(file_exists($packageDevPath . '/composer.json')){
                        
                        $composerObject = json_decode(file_get_contents($packageDevPath . '/composer.json'));
                        
                        if(property_exists($composerObject, 'autoload')){

                            foreach($composerObject->autoload as $autoloadType=>$autoloadEntries){
    
                                if($autoloadType == "psr-4"){
        
                                    foreach($autoloadEntries as $classPath=>$srcPath){
        
                                        $loader->addPsr4($classPath, $packageDevPath . '/' . $srcPath, true);
                                    }
                                }
                            }
                        }
                    }
                }
            }
    
            closedir($gestor);
        }
    }

    //TODO: CARGA DESDE EL CONFIG

    return $loader;
};