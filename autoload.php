<?php

spl_autoload_register(function($clase){

    $archivo=__DIR__."/".$clase.".php";
    $archivo=srt_replace("\\","/",$archivo);

    if(is_file($archivo)){
        require_once $archivo;
    }
});