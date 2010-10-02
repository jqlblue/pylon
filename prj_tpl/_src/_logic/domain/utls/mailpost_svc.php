<?php
class MailPostSvc
{/*{{{*/
    private $_conf = null;
    public function __construct($config = null) 
    {/*{{{*/
        BR::notNull($config,"缺少发信配置");
        $conf = ApolloMailPost::getPostConf($config);
        BR::notNull($conf,"不存在发信配置:".$conf);
        $this->_conf = $conf;
    }/*}}}*/
    
    public function send()
    {/*{{{*/
        $receiverCnt = count($this->_conf["receiver"]);
        echo "BEGIN send {$receiverCnt} mails at ".date("Y-m-d H:i:s")."....\n";
        foreach($this->_conf["receiver"] as $receiver)
        {
            $mail = new Zend_Mail('utf-8');
            $mail->setFrom($this->_conf["sender"]["email"],$this->_conf["sender"]["name"]);
            $options["name"] = $receiver["name"];
            $options["date"] = date("Y-m-d");
            $bodyHtml        = $this->buildHtml($this->_conf["bodyTpl"],$options);
            $title           = $this->buildHtml($this->_conf["subject"],$options);
            $mail->setSubject($title);
            $mail->setBodyHtml($bodyHtml,'utf-8');
            $mail->addTo($receiver["email"],$receiver["name"]);
            echo "sending...........{$receiver["email"]} begin at ".date("Y-m-d H:i:s")."\n";
            try{
                $res = $mail->send();
            }
            catch(exception $e)
            {
                echo "error ............at sending mail to {$receiver["email"]} \n";
//                var_dump($e);
            }
            echo "sending...........{$receiver["email"]} end at ".date("Y-m-d H:i:s")."\n and sleep {$this->_conf["interval"]} secs\n";
            sleep($this->conf["interval"]);
        }
        echo "END send {$receiverCnt} mails at ".date("Y-m-d H:i:s")."....\n";
    }/*}}}*/
    
    public function buildHtml($html,$options)
    {/*{{{*/
        foreach($options as $key=>$value)
        {
            $html = str_replace("{{$key}}",$value,$html);
        }
        return $html;
    }/*}}}*/

}/*}}}*/
?>
