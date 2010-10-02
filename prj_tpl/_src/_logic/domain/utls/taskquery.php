<?php
class TaskQuery extends Query
{
    public function getMyTask($userid,$status,$otype="site")
    {
        $cmd = "select * from task where userid='$userid' and otype='$otype' and status='$status'";
        return  $this->listByCmd($cmd);
    }
    public function getSownerNeedAuditTask()
    {
        $status=Task::ST_AUDITING;
        return  $this->getNeedAuditTask(Tasktype::ID_CARD);
    }
    public function getSiteNeedAuditTask()
    {
        $status=Task::ST_AUDITING;
        return  $this->getNeedAuditTask(Tasktype::SITE);
    }
    public function getNeedAuditTask($type=NULL)
    {
        $status=Task::ST_AUDITING;
        $cmd = "select * from task where status='$status'";
        if($type)$cmd .= " and otype=$type";
        return  $this->listByCmd($cmd);
    }
    public function getMyRefuseTask($userid,$otype = "site")
    {
        $status = Task::ST_AUDITED;
        $result = Task::RT_REFUSE;
        $cmd = "select * from task where userid='$userid' and otype='$otype' and status='$status' and result='$result'";
        return  $this->listByCmd($cmd);
    }
}
?>
