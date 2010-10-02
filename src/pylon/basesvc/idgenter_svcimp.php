<?php
/**\addtogroup basesvc
 * @{
 */
/** 
 * @brief 
 * @example  idgentsvc_test.php
 */
interface IDGenterService
{/*{{{*/
    public function createID($idname='other');
}/*}}}*/
/** 
    * @brief  implment by mysql
 */
class IDGenterSvcImp implements IDGenterService
{/*{{{*/
    private $_executer;
    private $_idsets = array() ;

    public function __construct($executer,$clone=true)
    {/*{{{*/
        if(!$clone)
            $this->_executer = $executer; 
        else
            $this->_executer = clone $executer; 

    }/*}}}*/

    private function readIDSets()
    {/*{{{*/
        $cmd = "select * from id_genter";
        $rows = $this->_executer->querys($cmd);
        if($rows)
        {
            foreach($rows as $row)
            {
                $this->_idsets[$row['obj']]['curID'] = $row['id'];
                $this->_idsets[$row['obj']]['maxID'] = $row['id'];
                $this->_idsets[$row['obj']]['step'] = $row['step'];
            }
        }
    }/*}}}*/

    public function createID($idname='other')
    {/*{{{*/
        if(empty($this->_idsets))
        {
            $this->readIDSets();
        }
        if(array_key_exists($idname, $this->_idsets))
        {
            return $this->createIDimp($idname);
        }
        else
        {
            return $this->createIDimp('other');
        }
        
    }/*}}}*/

    private function createIDimp($objName)
    {/*{{{*/
        if($this->_idsets[$objName]['curID'] == $this->_idsets[$objName]['maxID'] )
        {
            if(!$this->getBatchID($objName)) return false;
        }
        $this->_idsets[$objName]['curID'] += 1;
        $createdID = $this->_idsets[$objName]['curID'];
        return $createdID;
    }/*}}}*/

    public function getBatchID($objName)
    {/*{{{*/
        $step = $this->_idsets[$objName]['step'];
        $cmd = "UPDATE id_genter SET id = LAST_INSERT_ID(id + $step) where obj= '$objName';";
        if($this->_executer->exeNoQueryDirect($cmd))
        {
            $cmd = 'SELECT LAST_INSERT_ID() as id;';
            $row = $this->_executer->query($cmd);
            if($row)
            {
                $this->_idsets[$objName]['maxID'] = $row['id'];
                $this->_idsets[$objName]['curID'] = $row['id'] - $this->_idsets[$objName]['step'];
                return true;
            }
        }
        return false;
    }/*}}}*/
}/*}}}*/
/** 
 *  @}
 */
?>
