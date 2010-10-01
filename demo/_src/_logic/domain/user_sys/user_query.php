<?php
class UserQuery extends Query
{/*{{{*/
    public function getByPassportid($passportid)
    {/*{{{*/
        return $this->getByCmd("select * from user where passportid =$passportid");
    }/*}}}*/
    public function getByID($id)
    {/*{{{*/
        return $this->getByCmd("select * from user where id =$id");
    }/*}}}*/

}/*}}}*/

?>
