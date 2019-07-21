<?php

class Customer
{
    private $id;
    private $name;
    private $emial;
    private $address;
    private $mobile;
    private $updatedBy;
    private $updated_at;
    private $created_by;
    private $tableName = 'customers';
    private $db;

    public function setId($id) {  $this->id = $id; }
    public function getId() {     return $this->id; }
    public function setName($name) { $this->name = $name; }
    public function getName() { return $this->name; }
    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }
    public function setAddress($address) { $this->address = $address; } 
    public function getAddress() { return $this->address; }
    public function setMobile($mobile) { $this->mobile = $mobile; }
    public function getMobile() { return $this->mobile; }
    public function setUpdatedBy($updatedBy) {  $this->updatedBy = $updatedBy; }
    public function getUpdatedBy() { return $this->updatedBy; }
    public function setCreatedBy($createdBy) {  $this->created_by = $createdBy; }

    public function __construct()
    {
        $conn = new Config();
        $this->db = $conn->connect();
    }

    public function getAllCustomer()
    {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->tableName);
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $customers;
    }

    public function insert()
    {
        $sql = "INSERT INTO " . $this->tableName . "(name,email,address,mobile,created_by) 
        VALUES(:name, :email, :address, :mobile, :created_by)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':mobile', $this->mobile);
        $stmt->bindParam(':created_by', $this->created_by);

        if($stmt->execute()){
            return true;
        }else{
            return false;
        }
    }

    public function update()
    {
        $sql = "UPDATE $this->tableName SET ";
        
        if( null != $this->getName()){
            $sql .= " name = '".$this->getName()."', ";
        }

        if( null != $this->getEmail()){
            $sql .= " email = '".$this->getEmail()."', ";
        }

        if( null != $this->getAddress()){
            $sql .= " address = '".$this->getEmail()."', ";
        }

        if( null != $this->getMobile()){
            $sql .= " mobile = '".$this->getMobile()."', ";
        }

        $sql .= " updated_by = :updatedBy WHERE id = :userId ";

        echo $sql;

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':updated_by', $this->updatedBy);
        $stmt->bindParam(':userId', $this->id);
        if($stmt->is_executable()){
            return true;
        }else{
            return false;
        }
    }

    public function delete()
    {
        $stmt = $this->db->prepare("DELETE FROM $this->tableName WHERE id = :userId");
        $stmt->bindParam(':userId', $this->id);
        if($stmt->execute()){
            return true;
        }else{
            return false;
        }
    }
}