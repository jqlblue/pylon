<?php
interface IAutoemail
{
    public function addAutoemail($userid,$passport,$password,$isp); 
}
class AutoemailImpl implements IAutoemail
{
    public function addAutoemail($userid,$passport,$password,$isp) 
    {
        $user = DDA::ins()->get_User_by_id($userid);
        BR::notNull($user,"不存在该用户[userid:{$userid}]");
        BR::notNull(EmailIsp::getIsp($isp),"不存在此ISP记录");
        $autoemail = AutoemailSvc::addEmail($userid,$passport,$password,$isp);
        return true;
    } 
}
