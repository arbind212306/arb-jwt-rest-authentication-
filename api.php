<?php

class Api extends Rest
{
    public $db;

    public function __construct()
    {
        parent::__construct();
        $dbConn = new Config;
        $this->db = $dbConn->connect();
    }

    public function generateToken()
    {
        $email = $this->validateParameter('email', $this->param['email'], STRING);
        $password = $this->validateParameter('password', $this->param['password'], STRING);

        try{
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email=:email AND password=:password");
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $password);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!is_array($user)){
                $this->returnResponse(INVALID_USER_PASS, 'Email or Password provided by you is invalid');
            }

            if($user['is_active'] == 0){
                $this->returnResponse(USER_NOT_ACTIVE, 'User is not activated. Please contact to the admin.');
            }

            $payload = [
                'isat' => time(),
                'iss'  => 'localhost',
                'exp'  => time() + (60*60),
                'userId' => $user['id']
            ];

            $token = JWT::encode($payload, SECRET_KEY);
            $data = ['token' => $token];
            $this->returnResponse(SUCCESS_RESPONSE, $data);
        }catch(Exception $e){
            $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
        }
        
    }

    public function addCustomer()
    {
        $name = $this->validateParameter('name', $this->param['name'], STRING, false);
        $email = $this->validateParameter('email', $this->param['email'], STRING, false);
        $address = $this->validateParameter('address', $this->param['address'], STRING, false); 
        $mobile = $this->validateParameter('mobile', $this->param['mobile'], INTEGER, false);

        try{
            $token =    $this->getBearerToken();
            $payload = JWT::decode($token, SECRET_KEY, ['HS256']);

            $stmt = $this->db->prepare("SELECT * FROM users WHERE id=:userId");
            $stmt->bindParam(":userId", $payload->userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!is_array($user)){
                $this->returnResponse(INVALID_USER_PASS, 'This user is not found in our database');
            }

            if($user['is_active'] == 0){
                $this->returnResponse(USER_NOT_ACTIVE, 'This user may be deactivated. Please contact to the admin.');
            }
            $cust = new Customer();
            $cust->setName($name);
            $cust->setEmail($email);
            $cust->setAddress($address);
            $cust->setMobile($mobile);
            $cust->setCreatedBy($payload->userId);
            if(!$cust->insert()){
                $message = "Failed to Insert.";
            }else{
                $message = "Inserted successfully.";
            }
            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }catch(Exception $e){
            $this->throwError(ACCESS_TOKEN_ERRORS, $e->getMessage());
        }
    }

    public function deleteCustomer()
    {
        $email = $this->validateParameter('email', $this->param['email'], STRING, false);
        try{
            $token =    $this->getBearerToken();
            $payload = JWT::decode($token, SECRET_KEY, ['HS256']);
            $cust = new Customer();
            $cust->setId($payload->userId);
            if(!$cust->delete()){
                $message = " Failed to delete the customer.";
            }else{
                $message = " Customer deleted successfully.";
            }
            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }catch(Exception $e){
            $this->throwError(ACCESS_TOKEN_ERRORS, $e->getMessage()); 
        }
    }
}

?>