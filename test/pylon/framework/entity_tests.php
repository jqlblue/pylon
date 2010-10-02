<?php

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('simpletest/mock_objects.php');


class EntityTest extends UnitTestCase
{/*{{{*/
    public function __construct()
    { 
        parent::__construct("Entitys Group Test init ");
    }
    public function __destruct()
    {
    }

    public function setup()
    {
        TestAssemply::setup();
    }
    public function tearDown()
    {
        DaoFinder::clean();
        ObjectFinder::clean();
    }
    public function testUnitWorkException()
    {/*{{{*/

        $author=null;
        EntityUtls::assembly(new UnitWorkImpl()); 
        try
        {
            $author= Author::createByBiz('zwj','1975-10-18','chinese');
            $book  = Book::createByBiz('c++',$author,'10.2','c++ std lib');
            $book->noAttr="xxx";

            Entity::unitWork()->commit();
        }
        catch(Exception $e)
        {
            $this->assertTrue(true);
        }
        $found = DDA::ins()->get_Author_by_id($author->id());
        $this->assertTrue($found == null);

    }/*}}}*/
    public function testUnitWork()
    {/*{{{*/
        $executer =  ObjectFinder::find('SQLExecuter');          
        $logImpl= new MemCollectLogger();
//        $log = ScopeSqlLog::echoCollectWLog($executer,$logImpl);
        $log = ScopeSqlLog::collectWLog($executer,$logImpl);
                EntityUtls::assembly(new UnitWorkImpl()); 

        try
        {
            $author= Author::createByBiz('zwj','1975-10-18','chinese');
            $author2= Author::createByBiz('zwj2','1975-10-18','chinese');
            $book  = Book::createByBiz('c++',$author,'10.2','c++ std lib');
            $book2 = Book::createByBiz('java',$author,0,'java std lib');

            $book3 = Book2::createByBiz('java','10.2','java std lib',$author,$author2);
            $book4 = Book2::createByBiz('java','10.2','java std lib',$author, new NullEntity('Author'));


            $car = BuyCar::createByBiz('zwj');
            $car->addBook($book,1);
            $car->addBook($book2,3);
            Entity::unitWork()->commit();

            $msgs  = $logImpl->logMsgs;
            $this->assertPattern("/insert author/",$msgs[0]);
            $this->assertPattern("/insert author/",$msgs[1]);
            $this->assertPattern("/insert book/",$msgs[2]);
            $this->assertPattern("/insert book/",$msgs[3]);
            $this->assertPattern("/insert book2/",$msgs[4]);
            $this->assertPattern("/insert book2/",$msgs[5]);
            $this->assertPattern("/insert buycar/",$msgs[6]);
            $this->assertPattern("/insert car_item/",$msgs[7]);
            $this->assertPattern("/insert car_item/",$msgs[8]);

            $log=null;
            $logImpl= new MemCollectLogger();
            $log = ScopeSqlLog::collectWLog($executer,$logImpl);
            //        $log = ScopeSqlLog::echoCollectWLog($executer,$logImpl);
            $mycar= DaoFinder::find($car)->getByID($car->id());

            $book3 = Book::createByBiz('php',$author,'10.2','java std lib');
            $mycar->addBook($book3,3);
            $mycar->removeBook($book,1);
            Entity::unitWork()->commit();
            $msgs  = $logImpl->logMsgs;
            $this->assertPattern("/insert book/",$msgs[0]);
            $this->assertPattern("/update buycar/",$msgs[1]);
            $this->assertPattern("/insert car_item/",$msgs[2]);
            $this->assertPattern("/delete from car_item/",$msgs[3]);
        }
        catch(Exception $e)
        {
            echo $e->getTraceAsString();
            throw $e;
        }
    }/*}}}*/
    public function t1estUnitWork2Session()
    {/*{{{*/
        $executer =  ObjectFinder::find('SQLExecuter');          
        $logImpl= new MemCollectLogger();
        //        $log = ScopeSqlLog::echoCollectLog($executer,$logImpl);
        $log = ScopeSqlLog::collectLog($executer,$logImpl);
        $unitwork = new UnitWorkImpl();
        EntityUtls::assembly($unitwork); 
        $author= Author::createByBiz('zwj','1975-10-18','chinese');
        $book  = Book::createByBiz('c++',$author,'10.2','c++ std lib');
        $book2 = Book::createByBiz('java',$author,'10.2','java std lib');

        $car = BuyCar::createByBiz('zwj');
        $car->addBook($book,1);
        $car->addBook($book2,3);
        $keepdata= serialize($unitwork);


        $unitwork2 = unserialize($keepdata); 
        EntityUtls::assembly($unitwork2); 
        $this->assertEqual($unitwork,$unitwork2);
        $this->assertTrue($unitwork->equal($unitwork2));
        $unitwork=null;
        $book3 = Book::createByBiz('javax',$author,'10.2','java std libxx');
        $car->addBook($book3,3);
        $unitwork2->commit();

        $msgs  = array_slice($logImpl->logMsgs,count($logImpl)-9, 8);
        $this->assertPattern("/insert author/",$msgs[0]);
        $this->assertPattern("/insert book/",$msgs[1]);
        $this->assertPattern("/insert book/",$msgs[2]);
        $this->assertPattern("/insert buycar/",$msgs[3]);
        $this->assertPattern("/insert car_item/",$msgs[4]);
        $this->assertPattern("/insert car_item/",$msgs[5]);
    }/*}}}*/

}/*}}}*/
?>
