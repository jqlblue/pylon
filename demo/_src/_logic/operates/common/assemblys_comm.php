<?php
class CommonAssemply
{/*{{{*/
    static public function setup($cacheSvc=null)
    {/*{{{*/
        SysVersion::init( dirname(__FILE__)."/../../version.txt");
        $dbConf =  Conf::getDBConf();
        $executer = new FastSQLExecutor($dbConf->host,$dbConf->user,$dbConf->password,$dbConf->name,
            FastSQLExecutor::SHORT_CONN,'utf8');
        $logger = LogerManager::getSqlLoger();
        $executer->regLogger($logger,$logger);

        $authorQuery = null;

        $sessDriver = new MySqlSessDriver($dbConf->host,$dbConf->name,$dbConf->user,$dbConf->password);
        $sessionSvc  = new SessionSvc("ADMIN_BALL",$sessDriver);


        ObjectFinder::register('SQLExecuter',$executer);
        ObjectFinder::register('IDGenterService', new IDGenterSvcImp($executer));   
        ObjectFinder::regByClass($sessionSvc);
        DaoFinder::registerFactory(SimpleDaoFactory::funIns($executer),
                                   SimpleQueryFactory::funIns($executer));


//        ApolloMail::init();
        $singleOrderDao = DaoImp::simpleTableDao('SingleOrder','orders',   $executer);
        $mutiOrderDao   = DaoImp::simpleTableDao('MutiOrder','orders',   $executer);
        $orderStoreDao  = DaoImp::simpleTableDao('OrderStore','orders',   $executer);
        DaoFinder::registerDaos($singleOrderDao,$mutiOrderDao,$orderStoreDao );
    }/*}}}*/
    static public function clear()
    {/*{{{*/

    }/*}}}*/
}/*}}}*/
