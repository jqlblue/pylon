<?php
class AutoemailSvc
{
    public function getEmailByUserid($userid)
    {
        $emails = Dquery::ins()->list_autopassport_by_userid_passtype($userid,PassportType::EMAIL);
        return $emails;
    }

    public function addEmail($userid,$passport,$password,$isp)
    {
        $email = Autopassport::createByBiz($userid,$isp,PassportType::EMAIL,$passport,$password);
        return $email; 
    }
}

