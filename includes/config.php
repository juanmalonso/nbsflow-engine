<?php

return $getNbsFlowConfig = function ($p_basePath, $p_appId) {

    $result             = false;

    $appConfigPath      = $p_basePath . "/apps/" . $p_appId . "/config";
    
    if(file_exists($appConfigPath)){

        if ($gestor = opendir($appConfigPath)) {

            while (false !== ($entrada = readdir($gestor))) {

                //JSON
                if($entrada != "." && $entrada != ".." && substr($entrada,-5) == ".json"){

                    $configKey      = basename($entrada, ".json");

                    $configData     = file_get_contents($appConfigPath . '/' . $entrada);

                    if($configData){

                        if($result == false){

                            $result = array();
                        }

                        $result[$configKey]     = json_decode($configData, true);
                    }else{

                        //TODO: ERROR AL PROCESAR JSON FILE DE CONFIG
                    }
                }
            }
        }
    }

    return $result;

};