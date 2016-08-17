<?php

namespace tdt4237\webapp\models;

class Phone
{

    private $phone;
    
    public function __construct($phone)
    {
        if (! $this->isPhoneNumber($phone)) {
            throw new \Exception("Phone number must be inside the range 00000000-99999999");
        }
        
        $this->phone = $phone;
    }
    
    public function __toString()
    {
        return $this->phone;
    }
    
    private function isPhoneNumber($phone)
    {
        return is_numeric($phone) and $phone >= 00000000 and $phone <= 99999999;
    }
}
