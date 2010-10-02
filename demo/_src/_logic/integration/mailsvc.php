<?php
class ApolloMail 
{/*{{{*/
    static public function testConf($user='zuowenjian',$passwd='pass-abcd-123')
    {/*{{{*/
        require_once 'Zend/Mail.php';
        require_once 'Zend/Mail/Transport/Smtp.php';
        $config['ssl']='tls';
        $config['port']='25';
        $config['username']=$user;
        $config['password']=$passwd;
        $config['auth']='login';
        $tr = new Zend_Mail_Transport_Smtp('mail.qihoo.net',$config);
        Zend_Mail::setDefaultTransport($tr);
    }/*}}}*/

    static public function init()
    {/*{{{*/
        self::mail17kConf();
        //        self::clkanaConf();
    }/*}}}*/
    static public function localConf()
    {/*{{{*/
        require_once 'Zend/Mail.php';
    }/*}}}*/
    static public function mail17kConf()
    {/*{{{*/
        require_once 'Zend/Mail.php';
        require_once 'Zend/Mail/Transport/Smtp.php';
        $config['port']='25';
        $config['username']='lm';
        $config['password']='ilove17k';
        $config['auth']='login';
        $tr = new Zend_Mail_Transport_Smtp('mail.17k.com',$config);
        Zend_Mail::setDefaultTransport($tr);
    }/*}}}*/

    static public function clkanaConf()
    {/*{{{*/

        require_once 'Zend/Mail.php';
        require_once 'Zend/Mail/Transport/Smtp.php';
        $config['ssl']='tls';
        $config['port']='26';
        $config['username']='test+clkana.com';
        $config['password']='test4zlabs';
        $config['auth']='login';
        $tr = new Zend_Mail_Transport_Smtp('mail.clkana.com',$config);
        Zend_Mail::setDefaultTransport($tr);
    }/*}}}*/

    static public function gmailConf()
    {/*{{{*/
        require_once 'Zend/Mail.php';
        require_once 'Zend/Mail/Transport/Smtp.php';
        $config['ssl']='ssl';
        $config['port']='465';
        $config['username']='lm.17k.com@gmail.com';
        $config['password']='ilove17k';
        $config['auth']='login';
        $tr = new Zend_Mail_Transport_Smtp('smtp.gmail.com',$config);
        Zend_Mail::setDefaultTransport($tr);
    }/*}}}*/

    static public function serverConf()
    {/*{{{*/
        require_once 'Zend/Mail.php';
        require_once 'Zend/Mail/Transport/Sendmail.php';
        $tr = new Zend_Mail_Transport_Sendmail();
        Zend_Mail::setDefaultTransport($tr);
    }/*}}}*/
    
    static public function zendMail()
    {/*{{{*/
        return new Zend_Mail('utf-8');
    }/*}}}*/

    static public function getMailDomain($address)
    {/*{{{*/
        $loginDomain = array
        (/*{{{*/
            'qq.com'      => 'mail.qq.com',
            'vip.qq.com'  => 'mail.qq.com',
            '163.com'     => 'mail.163.com',
            'yahoo.com.cn'=> 'mail.yahoo.cn',
            'yahoo.com'   => 'mail.yahoo.com',
            'yahoo.cn'    => 'mail.yahoo.cn',
            'sudo.com'    => 'mail.sohu.com',
            'sina.com.cn' => 'mail.sina.com.cn',
            'sina.com'    => 'mail.sina.com.cn',
            'sina.cn'     => 'mail.sina.com.cn',
            'vip.sina.com'=> 'mail.sina.com.cn',
            'tom.com'     => 'mail.tom.com',
            'msn.com'     => 'mail.live.com',
            'live.com'    => 'mail.live.com',
            'hotmail.com' => 'mail.live.com',
        );/*}}}*/
        $mailDomain = substr(strstr($address, '@'),1);

        return ($domain = $loginDomain[$mailDomain]) 
            ? $domain  :
            (count(explode('.', $mailDomain)) > 2 
            ? $mailDomain :
            'www.'.$mailDomain);
    }/*}}}*/
}/*}}}*/
?>
