<?php
define('ADMIN_SYS_VER','0.1.1');


class AdminSysAssembly
{/*{{{*/
    static public function setup($cacheSvc=null)
    {/*{{{*/
        CommonAssemply::setup($cacheSvc);
        XAop::pos(XAop::LOGIC)->append_by_dismatch_name("(login)|(xxx)", XBoxScopeIntc::make(new AdminAuthorization()));
        XAop::pos(XAop::LOGIC)->append_by_match_name(".*", new AutoCommit());
        XAop::pos(XAop::TPL  )->append_by_match_name(".*", new AutoSmartyView());
        XAop::pos(XAop::ERROR)->append_by_match_name("(_add)|(_del)", new ArsyncErrorPoc());
        XAop::pos(XAop::ERROR)->append_by_match_name(".*", new StructErrorProc());
//        $cacheDriver = new MemCacheSvc(MemCacheSvc::localhostConf());   
//        CacheAdmin::setup($cacheDriver,new CacheStg(600));
//        PylonCtrl::switchDaoCache(PylonCtrl::ON);
//        PylonCtrl::clearCacheStg()->addAddtions("contract",array("product","productQuery"));
//        PylonCtrl::clearCacheStg()->addAddtions("playlistitem",array("advert","advertQuery"));
    }/*}}}*/
}/*}}}*/
?>
