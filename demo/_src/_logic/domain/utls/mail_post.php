<?php
class ApolloMailPost
{
    const WELCOME = "welcome";
    public function getPostConf($c)
    {/*{{{*/
        switch($c)
        {
            case ApolloMailPost::WELCOME:
                ApolloMail::mail17kConf();
                $conf = self::_postConfWelcome();
                break;
            default:
                $conf = null;
                break;
        }
        return $conf;
    }/*}}}*/

    static private function _postConfWelcome()
    {/*{{{*/ 
        $conf = array();
        $conf["subject"]    = "欢迎来到17K联盟";
        $conf["bodyTpl"]    = file_get_contents(Conf::EMAIL_TPL ."/welcome_email.html");
        $conf["receiver"]   = self::_getReceiverList(Conf::EMAIL_ADDR."/welcome.list");
        $conf["sender"]     = array("email"=>"lm@17k.com","name"=>"17k联盟客服");
        $conf["interval"]   = 0; 
        return $conf;
    }/*}}}*/
    
    static public  function _fmtDataLine(&$v,$k,&$fmtData)
    {/*{{{*/
        $v = str_replace(array("\r", "\n"), "", $v);
        if(!empty($v))
        {
            $dataArr = explode("\t",$v);
            $email   = $dataArr[0];
            $emailArr= explode("@",$email);
            $fmtData["domain"][] = $emailArr[1];                    
            $emailOwner = isset($dataArr[1])?$dataArr[1]:$emailArr[0];
            $fmtData["receiver"][] = array("email"=>$email,"name"=>$emailOwner);
        }
    }/*}}}*/

    static private function _getReceiverList($file)
    {/*{{{*/
        $fmtData     = array("receiver"=>array(),"domain"=>array());
        /**/
        for($i=0;$i<200;$i++)
        {
            $test[] = $i."helloworld@chineseall.com";
        }
        /**/
        $dataLines   = file($file);
        $dataLines   = array_merge($dataLines,$test);
        array_walk($dataLines,"ApolloMailPost::_fmtDataLine",&$fmtData);
        unset($dataLines);
        $domainData = $fmtData["domain"];
        $receiver   = $fmtData["receiver"];
        return $receiver;
    }/*}}}*/


}

?>
