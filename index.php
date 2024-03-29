<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class Route {

    private function simpleRoute($file, $route){

        
        //replacing first and last forward slashes
        //$_REQUEST['uri'] will be empty if req uri is /

        if(!empty($_REQUEST['uri'])){
            $route = preg_replace("/(^\/)|(\/$)/","",$route);
            $reqUri =  preg_replace("/(^\/)|(\/$)/","",$_REQUEST['uri']);
        }else{
            $reqUri = "/";
        }

        if($reqUri == $route){
            $params = [];
            include($file);
            exit();

        }

    }

    function add($route,$file){

        //will store all the parameters value in this array
        $params = [];

        //will store all the parameters names in this array
        $paramKey = [];

        //finding if there is any {?} parameter in $route
        preg_match_all("/(?<={).+?(?=})/", $route, $paramMatches);

        //if the route does not contain any param call simpleRoute();
        if(empty($paramMatches[0])){
            $this->simpleRoute($file,$route);
            return;
        }

        //setting parameters names
        foreach($paramMatches[0] as $key){
            $paramKey[] = $key;
        }

       
        //replacing first and last forward slashes
        //$_REQUEST['uri'] will be empty if req uri is /

        if(!empty($_REQUEST['uri'])){
            $route = preg_replace("/(^\/)|(\/$)/","",$route);
            $reqUri =  preg_replace("/(^\/)|(\/$)/","",$_REQUEST['uri']);
        }else{
            $reqUri = "/";
        }

        //exploding route address
        $uri = explode("/", $route);

        //will store index number where {?} parameter is required in the $route 
        $indexNum = []; 

        //storing index number, where {?} parameter is required with the help of regex
        foreach($uri as $index => $param){
            if(preg_match("/{.*}/", $param)){
                $indexNum[] = $index;
            }
        }

        //exploding request uri string to array to get
        //the exact index number value of parameter from $_REQUEST['uri']
        $reqUri = explode("/", $reqUri);

        //running for each loop to set the exact index number with reg expression
        //this will help in matching route
        foreach($indexNum as $key => $index){

             //in case if req uri with param index is empty then return
            //because url is not valid for this route
            if(empty($reqUri[$index])){
                return;
            }

            //setting params with params names
            $params[$paramKey[$key]] = $reqUri[$index];

            //this is to create a regex for comparing route address
            $reqUri[$index] = "{.*}";
        }

        //converting array to sting
        $reqUri = implode("/",$reqUri);

        //replace all / with \/ for reg expression
        //regex to match route is ready !
        $reqUri = str_replace("/", '\\/', $reqUri);

        //now matching route with regex
        if(preg_match("/$reqUri/", $route))
        {
            include($file);
            exit();

        }
    }

    function notFound($file){
        include($file);
        exit();
    }
}

$route = new Route();

// v0
$route->add("/v0/users/login",             "v0/users/login.php");
$route->add("/v0/users/register",          "v0/users/register.php");

$route->add("/v0/categories/get",          "v0/categories/get.php");
$route->add("/v0/categories/post",         "v0/categories/post.php");

$route->add("/v0/categories/{id}/get",     "v0/categories/get.php");
$route->add("/v0/categories/{id}/put",     "v0/categories/put.php");
$route->add("/v0/categories/{id}/delete",  "v0/categories/delete.php");

$route->add("/v0/categories/{categoryId}/sets/get",          "v0/sets/get.php");
$route->add("/v0/categories/{categoryId}/sets/post",         "v0/sets/post.php");

$route->add("/v0/categories/{categoryId}/sets/{id}/get",     "v0/sets/get.php");
$route->add("/v0/categories/{categoryId}/sets/{id}/put",     "v0/sets/put.php");
$route->add("/v0/categories/{categoryId}/sets/{id}/delete",  "v0/sets/delete.php");

$route->add("/v0/categories/{categoryId}/sets/{setId}/questions/get",          "v0/questions/get.php");
$route->add("/v0/categories/{categoryId}/sets/{setId}/questions/post",         "v0/questions/post.php");

$route->add("/v0/categories/{categoryId}/sets/{setId}/questions/{id}/get",     "v0/questions/get.php");
//$route->add("/v0/categories/{categoryId}/sets/{setId}/questions/{id}/put",     "v0/questions/put.php");
$route->add("/v0/categories/{categoryId}/sets/{setId}/questions/{id}/delete",  "v0/questions/delete.php");


// v1
$route->add("/v1/users/login",                              "v1/users/login.php");
$route->add("/v1/users/get",                                "v1/users/get.php");
$route->add("/v1/users/post",                               "v1/users/post.php");

$route->add("/v1/users/{username}/get",                     "v1/users/get.php");
$route->add("/v1/users/{username}/put",                     "v1/users/put.php");
$route->add("/v1/users/{username}/delete",                  "v1/users/delete.php");

$route->add("/v1/users/{username}/password/put",            "v1/users/ch_pswd.php");

$route->add("/v1/categories/get",                           "v1/categories/get.php");
$route->add("/v1/categories/post",                          "v1/categories/post.php");

$route->add("/v1/categories/{id}/get",                      "v1/categories/get.php");
$route->add("/v1/categories/{id}/put",                      "v1/categories/put.php");
$route->add("/v1/categories/{id}/delete",                   "v1/categories/delete.php");

$route->add("/v1/categories/{categoryId}/sets/get",          "v1/sets/get.php");
$route->add("/v1/categories/{categoryId}/sets/post",         "v1/sets/post.php");

$route->add("/v1/categories/{categoryId}/sets/{id}/get",     "v1/sets/get.php");
$route->add("/v1/categories/{categoryId}/sets/{id}/put",     "v1/sets/put.php");
$route->add("/v1/categories/{categoryId}/sets/{id}/delete",  "v1/sets/delete.php");

$route->add("/v1/categories/{categoryId}/sets/{setId}/questions/get",          "v1/questions/get.php");
$route->add("/v1/categories/{categoryId}/sets/{setId}/questions/post",         "v1/questions/post.php");

$route->add("/v1/categories/{categoryId}/sets/{setId}/questions/{id}/get",     "v1/questions/get.php");
//$route->add("/v1/categories/{categoryId}/sets/{setId}/questions/{id}/put",     "v1/questions/put.php");
$route->add("/v1/categories/{categoryId}/sets/{setId}/questions/{id}/delete",  "v1/questions/delete.php");


$route->notFound("404.php");

?>