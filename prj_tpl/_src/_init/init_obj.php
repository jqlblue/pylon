<?php
require_once('pylon/autoload/class_loads.php');
require_once('config.php');
$root=Conf::APP_ROOT;

ComboLoader::setup(__FILE__,
    new ClassLoader(Conf::PSIONIC,Conf::PSIONIC."/pylon/autoload_data.php"),   //sgtlib autoload data
    new ClassLoader("$root/", "$root/common_load_data.php"),
    new ClassLoader("$root/", "$root/admin_load_data.php"));

AdminSysAssemply::setup();

class SysGod extends PropertyObj
{/*{{{*/
    public function __construct()
    {/*{{{*/
        parent::__construct();
    }/*}}}*/
    public function setupSysInit()
    {/*{{{*/
        $appSess =  AppSession::begin();
        $acct =  Account::createByBiz(AccountDef::USP_ID,AccountDef::USP_MAIN); 
        $acct =  Account::createByBiz(AccountDef::USP_ID,AccountDef::USP_FILLING); 


        $this->stg =  SettleStg::createByBiz("25元",SettleStgDef::SETTLE_BY_CLKUV,
            new FixedPriceDef(),new NullCostDef(),25 * 100 ,1000,"1000次点击IP 25元");
        $playstg = new RotatePlayStg();
        $playstg->setRotateSecond(5);
        $this->playStgObj2 = PlayStgObj::createByBiz("标准轮播",$playstg);

        $playstg2 = new RotatePlayStg();
        $playstg2->setRotateSecond(2);
        $this->playStgObj3 = PlayStgObj::createByBiz("快速轮播",$playstg2);

        $appSess->commit();
        $appSess=null;
    }/*}}}*/

}/*}}}*/

try
{/*{{{*/
    $god = new SysGod();
    $god->setupSysInit();
}/*}}}*/
catch( Exception $e)
{/*{{{*/
    $errorMsg = $e->getMessage();
    $errorPos = $e->getTraceAsString();
    echo  "$errorMsg\n";
    echo  "$errorPos\n";
}/*}}}*/
?>
