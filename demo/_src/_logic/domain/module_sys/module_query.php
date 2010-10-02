<?php
class ModuleQuery extends Query
{
    public function listBy($mtype=null,$life=null,$offset=null,$num=null,$order="id desc")
    {/*{{{*/
        $limitStr = $where = "";
        $whereArr = array(); 
        if(!is_null($offset))
            $limitStr .= "limit $offset";
        if(!is_null($num)) 
            $limitStr .= ",$num";
        if(!is_null($mtype))
            $whereArr[] = "mtype = '{$mtype}'";
        if(!is_null($life))
            $whereArr[] = "lifestatus='{$life}'";
        if($whereArr)
            $where = "where ".implode(" and ",$whereArr);

        return $this->listByCmd("select * from module $where order by $order $limitStr");
    }/*}}}*/  

}

class ModulemoreQuery extends Query
{
    public function listBy($mtype=null,$life=null,$offset=null,$num=null,$order="id desc")
    {/*{{{*/
        $limitStr = $where = "";
        $whereArr = array(); 
        if(!is_null($offset))
            $limitStr .= "limit $offset";
        if(!is_null($num)) 
            $limitStr .= ",$num";
        if(!is_null($mtype))
            $whereArr[] = "mtype = '{$mtype}'";
        if(!is_null($life))
            $whereArr[] = "lifestatus='{$life}'";
        if($whereArr)
            $where = "where ".implode(" and ",$whereArr);
        return $this->listByCmd("select * from modulemore $where order by $order $limitStr");
    }/*}}}*/ 

    public function add($id,$key,$name,$desc,$icon,$developer,$depend,$recommend,$version,$mtype)
    {
        $life = LifeStatus::NORMAL;
        $sql = "insert modulemore (mkey,name,mdesc,icon,msize,version,developer,depend,recommend,lifestatus,id,ver,createtime,updatetime,mtype)
            values('{$key}','{$name}','".mysql_escape_string($desc)."','{$icon}',0,'{$version}','{$developer}','{$depend}','{$recommend}','{$life}',{$id},1,'".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."','{$mtype}')";
        return $this->exer->exeNoQuery($sql);
    }
    public function update($key,$version,$name,$desc,$icon,$developer,$depend,$recommend)
    {
        $sql = "update modulemore set name ='{$name}',mdesc='".mysql_escape_string($desc)."',icon='{$icon}',developer = '{$developer}'
             , depend ='{$depend}',recommend='{$recommend}',ver=ver+1,updatetime='".date("Y-m-d H:i:s")."' where (mkey='{$key}' and version='{$version}');";
        return $this->exer->exeNoQuery($sql);
    }
    public function updatelifestatus($key,$status)
    {
        $sql = "update modulemore set lifestatus ='{$status}',ver=ver+1,updatetime='".date("Y-m-d H:i:s")."' where (mkey='{$key}');";
        return $this->exer->exeNoQuery($sql);
    }
    public function del($key,$version)
    {
        $sql = "update modulemore set lifestatus ='".LifeStatus::DISUSE."',updatetime='".date("Y-m-d H:i:s")."' where (mkey='{$key}' and version='{$version}');";
        return $this->exer->exeNoQuery($sql);
    }
}
