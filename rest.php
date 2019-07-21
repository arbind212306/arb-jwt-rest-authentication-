<?php

require_once 'constant.php';
class Rest
{
    protected $serviceName;
    protected $request;
    protected $param;
    public function __construct()
    {
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->throwError(REQUEST_METHOD_NOT_VALID, 'Request method is not valid');
        }
        $handler = fopen('php://input', 'r');
        $this->request = stream_get_contents($handler);   
        $this->validateRequest();

    }

    public function validateRequest()
    {
        if($_SERVER['CONTENT_TYPE'] !== 'application/json'){
            $this->throwError(REQUEST_CONTENT_TYPE_NOT_VALID, 'Request content type is not valid');
        }
        $data = json_decode($this->request, true);
        if(!isset($data['name']) || $data['name'] == ""){
            $this->throwError(API_NAME_REQUIRED, 'Api name is required');
        }
        $this->serviceName = $data['name'];
        if(!is_array($data['param'])){
            $this->throwError(API_PARAM_REQUIRED, 'Api param is required');
        }
        $this->param = $data['param'];
    }   

    public function processRequest()
    {
        $api = new Api();
        $rMethod = new reflectionMethod('API', $this->serviceName);
        if(!method_exists($api, $this->serviceName)){
            $this->throwError(API_DOSE_NOT_EXIST, 'API does not exits.');
        }
        $rMethod->invoke($api);
    }

    public function validateParameter($fieldName, $value, $dataType, $required=true)
    {
        if($required == true && empty($value) == true){
            $this->throwError(VALIDATE_PARAMETER_REQUIRED, 'Email parameter is required');
        }

        switch ($dataType){
            case BOOLEAN:
                if(!is_bool($value)){
                    $this->throwError(VALIDATE_PARAMETER_REQUIRED, "Datatype is not valid for " .$fieldName. ".It should be boolean.");
                }
                break;
            
            case INTEGER:
                if(!is_numeric($value)){
                    $this->throwError(VALIDATE_PARAMETER_REQUIRED, "Datatype is not valid for " .$fieldName. ".It should be integer.");
                } 
                break;

            case STRING:
                if(!is_string($value)){
                    $this->throwError(VALIDATE_PARAMETER_REQUIRED, "Datatype is not valid for " .$fieldName. ".It should be string.");
                }
                break;  

            default:
            throwError(VALIDATE_PARAMETER_REQUIRED, "Datatype is not valid for ".$fieldName);
            break;        
        }

        return $value;
    }

    public function throwError($code, $message)
    {
        header("content-type: apllication/json");
        $errMsg = json_encode(['error' => ['status' => $code, 'message' => $message]]);
        echo $errMsg;
        exit;
    }

    public function returnResponse($code, $data)
    {
        header("content-type: application/json");
        $response = json_encode(["response" => ["code" => $code, "result" => $data]]);
        echo $response;
        exit;
    }

        /** 
     * Get header Authorization
     * */
    public function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
    * get access token from header
    * */
    public function getBearerToken()
    {
    $headers = $this->getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    
    $this->throwError(AUTHORIZATION_HEADER_NOT_FOUND, 'Access Token Not Found');
    }
}

?>