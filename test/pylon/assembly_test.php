<?php
class TestAssemply
{
    static public function setup()
    {
        $dbConf =  Conf::getDBConf();
        $executer = new FastSQLExecutor($dbConf->host,$dbConf->user,$dbConf->password,$dbConf->name);
        ObjectFinder::register('SQLExecuter',$executer);
        ObjectFinder::register('IDGenterService', new IDGenterSvcImp($executer));   

        DaoFinder::registerFactory(SimpleDaoFactory::funIns($executer),
                                   SimpleQueryFactory::funIns($executer));

        $book2Dao   = new DaoImp('Book2',$executer,'book2',StdMapping::ins());
        $buyItemDao = new DaoImp('BuyItem',$executer,'car_item',SimpleMapping::ins());
        DaoFinder::registerDaos($book2Dao,$buyItemDao);

    }
}
?>
