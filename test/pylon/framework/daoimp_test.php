<?php
class StoreStg
{/*{{{*/
    static public function userStore($id)
    {/*{{{*/
        DBC::requireNotNull($id);
        if($id%2 == 1) 
        {
            return "user_1";
        }
        return "user_2";
    }/*}}}*/
}/*}}}*/
class DaoImpTest extends UnitTestCase
{/*{{{*/
    protected $oldDaoFinder=null;
    public function __construct()
    {/*{{{*/
        self::init();
        parent::__construct();
    }/*}}}*/
    static public function init()
    {/*{{{*/
        DaoFinder::ins()->clean();
    }/*}}}*/
    public function setUp()
    {/*{{{*/
        TestAssemply::setup();
        EntityUtls::assembly(new EmptyUnitWork());
    }/*}}}*/
    public function tearDown()
    {/*{{{*/
        DaoFinder::clean();
        ObjectFinder::clean();
    }/*}}}*/
    public function testSimpleObjDao()
    {/*{{{*/
        $executer =  ObjectFinder::find('SQLExecuter');          
        PylonCtrl::switchLazyLoad(PylonCtrl::OFF);
//        $log =  new  ScopeEchoLog($executer);
        try{

            $author= Author::createByBiz('zwj','1975-10-18','chinese');
            $authorDao = DaoImp::simpleDao('Author',$executer);
            DaoFinder::register($authorDao,'Author');
            $this->daoTestTplImp( $authorDao,$author,'name','qq');
            $authorDao->add($author);


            $book = Book::createByBiz('c++',$author,'10.2','c++ std lib');
            $book2 = Book::createByBiz('c++',$author,'10.1',null);
            $book3 = Book::createByBiz('c++',$author,'10.3',"xxx'xxx");
            $bookDao = DaoImp::simpleDao('Book',$executer);
            DaoFinder::register($bookDao,'Book');
            $this->daoTestTplImp( $bookDao,$book,'name','java');
            $this->daoTestTplImp( $bookDao,$book2,'name','java');
            $this->daoTestTplImp( $bookDao,$book3,'name','java');

//           $log =  new  ScopeEchoLog($executer);
            $dda = DDA::ins();

            $books = $dda->list_Book_by_price('? > 10 and ? < 10.5 ');
            $this->assertTrue(count($books)>=3); 
            $books2 = $dda->list_Book_by_name('? like "c%"');
            $this->assertTrue(count($books2)>=3); 

            $books3 = $dda->list_Book_by_name_price('? like "c%"',"10.3");
//            $this->assertTrue(count($books3)>=1); 

            Dwriter::ins()->update_Book_set_price_by_name("15",' ? like "c%"');
            $books4 = Dquery::ins()->list_Book_by_price('? > "12" ');
            $this->assertTrue(count($books4)>0); 

            $cacheDriver = new MemCacheSvc(MemCacheSvc::localhostConf());   
            CacheAdmin::setup($cacheDriver,new CacheStg(10));
            PylonCtrl::switchDaoCache(PylonCtrl::ON);

            $log =  new  ScopeEchoLog($executer);
            $books = $dda->list_Book_by_price('? > 10 and ? < 10.5 ');
            $this->assertTrue(count($books)>=3); 
            echo "---------------step 1 ---------------<br>\n"; 

            $books = $dda->list_Book_by_price('? > 10 and ? < 10.5 ');
            $this->assertTrue(count($books)>=3); 
            echo "---------------step 2 ---------------<br>\n"; 

            $books = $dda->list_Book_by_price('? > 10 and ? < 10.5 ');
            $this->assertTrue(count($books)>=3); 
            echo "---------------step 3 ---------------<br>\n"; 
            DaoFinder::clearBinder();

            
        }
        catch(Exception $e)
        {
            echo $e->getMessage()."\n";
            echo $e->getTraceAsString();
            $this->assertTrue(false);
           exit;
        }
    }/*}}}*/
    public function t1estComplexObjDao()
    {/*{{{*/

        DaoFinder::clean();
        try
        {
            $executer =  ObjectFinder::find('SQLExecuter');          
            $authorDao = DaoImp::simpleDao('Author',$executer);
//            $authorDao->updateLoadStg(Entity::IMMED_LOADER);
            $bookDao = DaoImp::simpleDao('Book',$executer);
//            $bookDao->updateLoadStg(Entity::IMMED_LOADER);
            $carDao = DaoImp::simpleDao('BuyCar',$executer);
//            $carDao->updateLoadStg(Entity::IMMED_LOADER);
            $buyItemDao = new DaoImp('BuyItem',$executer,'car_item',SimpleMapping::ins());
//            $buyItemDao->updateLoadStg(Entity::IMMED_LOADER);
            DaoFinder::registerDaos($authorDao,$bookDao,$carDao,$buyItemDao);

//            $log =  new  ScopeEchoLog($executer);

            $author= Author::createByBiz('zwj','1975-10-18','chinese');
            $authorDao->add($author);
            $book = Book::createByBiz('c++',$author,'10.2','c++ std lib');
            $bookDao->add($book);
            $book2 = Book::createByBiz('java',$author,'10.2','java std lib');
            $bookDao->add($book2);

            $car = BuyCar::createByBiz('test');
            $car->addBook($book,1);
            $car->addBook($book2,3);
            $carDao->add($car);
            $getedObj=$carDao->getByID($car->id());
            $this->assertEqual($car->entityID,$getedObj->entityID);
            $data1 = $car->buyItemSet->items();
            $data2 = $getedObj->buyItemSet->items();
            $this->assertEqual($car->buyItemSet->items(),$getedObj->buyItemSet->items());
            $this->assertTrue($car->buyItemSet->equal($getedObj->buyItemSet));

            $getedObj->removeBook($book2,1);
            $carDao->update($getedObj);
            $getedObj2=$carDao->getByID($getedObj->id());
            $this->assertEqual($getedObj2->entityID,$getedObj->entityID);
            $this->assertEqual($getedObj2->buyItemSet->items(),$getedObj->buyItemSet->items());
            $this->assertTrue($getedObj2->buyItemSet->equal($getedObj->buyItemSet));
            $this->assertFalse($getedObj2->buyItemSet->equal($car->buyItemSet));
            $this->assertEqual(count($getedObj2->buyItemSet->items()),count($car->buyItemSet->items()));

            $getedObj2->removeBook($book,1);
            $carDao->update($getedObj2);
            $getedObj3=$carDao->getByID($getedObj->id());
            $this->assertEqual($getedObj3->entityID,$car->entityID);
            $this->assertFalse($getedObj3->buyItemSet->equal($car->buyItemSet));
            $this->assertEqual($getedObj3->buyItemSet->items(),$getedObj2->buyItemSet->items());
        }
        catch( Exception $e)
        {
            echo $e->getTraceAsString();
            throw $e;
        }


    }/*}}}*/
    public function testHashStoreDao()
    {/*{{{*/
        $executer =  ObjectFinder::find('SQLExecuter');          
//        $log =  new  ScopeEchoLog($executer);
        try{

            $user1= User::createByBiz('sgtuser1','sgt');
            $user2= User::createByBiz('sgtuser2','sgt');
            $user3= User::createByBiz('sgtuser3','sgt');
            $userDao = new DaoImp('User',$executer,null,SimpleMapping::ins(),array('StoreStg','userStore'));
            DaoFinder::register($userDao,'User');
            $userDao->setHashStoreKey($user1->hashStoreKey());
            $this->daoTestTplImp( $userDao,$user1,'name','qq');
            $userDao->setHashStoreKey($user2->hashStoreKey());
            $this->daoTestTplImp( $userDao,$user2,'name','qq');
            $userDao->setHashStoreKey($user3->hashStoreKey());
            $this->daoTestTplImp( $userDao,$user3,'name','qq');
        }
        catch(Exception $e)
        {
            echo $e->getMessage()."\n";
            echo $e->getTraceAsString();
            $this->assertTrue(false);
           exit;
        }
    }/*}}}*/
    public function daoTestTplImp($objDao, $obj,$chkey=null,$chval=null)
    {/*{{{*/
        try{
            $objDao->add($obj);
            $getedObj = $objDao->getByID($obj->id());
            $this->assertEqual($obj,$getedObj);
            
            if(!is_null($chkey))
                $getedObj->$chkey=$chval;
            $objDao->update($getedObj);
            $getedObj2 = $objDao->getByID($obj->id());
            $this->assertEqual($getedObj,$getedObj2);
            $objDao->del($obj);
            $found= $objDao->getByID($obj->id());
            $this->assertTrue($found == null);
        }
        catch ( Exception $e) 
        {  
            echo $e->getMessage() ."\n"; 
            echo $e->getTraceAsString(); 
            $this->assertTrue(false); 
        } 

    }/*}}}*/
    public function testDynQuery()
    {/*{{{*/
        $dda = new DDA();
        $cls='';
        $oparam=null;
        extract(DynCallParser::condObjParse("get_user_by_name_age_obj__id"));
        $prop = DynCallParser::buildCondProp($condnames,array("a","b","c"),$oparam);
        $this->assertEqual($cls,"user");
        $this->assertEqual($op,"get");
        $this->assertEqual($prop->name,"a");
        $this->assertEqual($prop->age,"b");
        $this->assertEqual($prop->obj__id,"c");
        $this->assertEqual($cls,"user");
        $this->assertEqual(count($oparam),0);


        extract(DynCallParser::condObjParse("list_user_by_name"));
        $prop = DynCallParser::buildCondProp($condnames,array("a"),$oparam);
        $this->assertEqual($cls,"user");
        $this->assertEqual($op,"list");
        $this->assertEqual($prop->name,"a");


        extract(DynCallParser::condObjParse("list_user"));
        $prop = DynCallParser::buildCondProp($condnames,array(),$oparam);
        $this->assertEqual($cls,"user");
        $this->assertEqual($op,"list");

//        $dda->list_user_by_age('? >18 or ? <20 ');
        extract(DynCallParser::condObjParse("list_user_by_age"));
        $prop = DynCallParser::buildCondProp($condnames,array("? > 18 or ? < 20 "),$oparam);
        $this->assertEqual($cls,"user");
        $this->assertEqual($op,"list");
        $this->assertEqual($prop->age,new DQLObj("? > 18 or ? < 20 "));



        extract(DynCallParser::condObjParse("get_user_by2_name__age__obj_id"));
        $prop = DynCallParser::buildCondProp($condnames,array("a","b","c"),$oparam);

        $this->assertEqual($cls,"user");
        $this->assertEqual($op,"get");
        $this->assertEqual($prop->name,"a");
        $this->assertEqual($prop->age,"b");
        $this->assertEqual($prop->obj_id,"c");
        $this->assertEqual($cls,"user");
        $this->assertEqual(count($oparam),0);


        extract(DynCallParser::condObjParse("get_user_by3_name___ag_e___obj__id"));
        $prop = DynCallParser::buildCondProp($condnames,array("a","b","c"),$oparam);
        $this->assertEqual($cls,"user");
        $this->assertEqual($op,"get");
        $this->assertEqual($prop->name,"a");
        $this->assertEqual($prop->ag_e,"b");
        $this->assertEqual($prop->obj__id,"c");
        $this->assertEqual($cls,"user");
        $this->assertEqual(count($oparam),0);

        $page = new DataPage(10);
        extract(DynCallParser::condObjParse("get_user_by_name_age"));
        $prop = DynCallParser::buildCondProp($condnames,array("a","b",$page),$oparam);
        $this->assertEqual(count($oparam),1);

        extract(DynCallParser::condUpdateObjParse("update_user_set_name_by_age"));
        $this->assertEqual($cls,"user");
        $this->assertEqual($by,"by");
        $this->assertEqual($updatenames[0],"name");
        $this->assertEqual($condnames[0],"age");

        extract(DynCallParser::condUpdateObjParse("update_user_set_name_age_by2_age"));
        $this->assertEqual($cls,"user");
        $this->assertEqual($by,"by2");
        $this->assertEqual($updatenames[0],"name");
        $this->assertEqual($updatenames[1],"age");
        $this->assertEqual($condnames[0],"age");
        
        extract(DynCallParser::condUpdateObjParse("update_user_set_name_age"));
        $this->assertEqual($cls,"user");
        $this->assertEqual($by,"");
        $this->assertEqual($updatenames[0],"name");
        $this->assertEqual($updatenames[1],"age");
    }/*}}}*/
    public function testCacheDA()
    {/*{{{*/

        $cacheDriver = new MemCacheSvc(MemCacheSvc::localhostConf());
        CacheAdmin::setup($cacheDriver,new CacheStg(600));
        PylonCtrl::switchDaoCache(PylonCtrl::ON);

        $executer =  ObjectFinder::find('SQLExecuter');          
        PylonCtrl::switchLazyLoad(PylonCtrl::OFF);
        $log =  new  ScopeEchoLog($executer);
        $app = AppSession::begin(); 
        $author= Author::createByBiz('zwj_test','1975-10-18','chinese');
        $app->commit();
       
        $app = AppSession::begin(); 
        $found = DDA::ins()->get_Author_by_id($author->id());
        $found->lang="yyy";
        $app->commit();
        $this->assertEqual($found->ver() , 2);


        $found = DDA::ins()->get_Author_by_id($author->id());
        $found->lang="xxx";
        $app->commit();
        $this->assertEqual($found->ver() , 3);
        $this->assertTrue(true);
    }/*}}}*/
}/*}}}*/
?>
