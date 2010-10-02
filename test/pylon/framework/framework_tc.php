<?php
require_once('simpletest/mock_objects.php');
class ArrayCache extends PropertyObj
{/*{{{*/
    public function __construct()
    {}
    public function get($key)
    {
        if($this->haveSet($key))
            return $this->$key;
        return null;
    }
    public function set($key,$val)
    {
        $this->$key=$val;
    }
    public function delete($key)
    {
        $this->remove($key);
    }
}/*}}}*/

class FrameWorkTestCase extends UnitTestCase
{/*{{{*/
    public function testCacheOberver()
    {/*{{{*/
        $cache = new ArrayCache();
        $keyCache = new ArrayCache();
        $ob = new CacheObserver($keyCache);

        $data[]=array('id'=>1);
        $data[]=array('id'=>3);
        $data[]=array('id'=>5);
        $data[]=array('id'=>7);
        $cacheKey=100;
        $cache->set($cacheKey,"qihoo");
        $ob->regCachedlist($cacheKey,$data);
        $ob->invalidate(5,$cache);
        $this->assertEqual(0,count($cache->getPropArray()));
        $this->assertEqual(3,count($keyCache->getPropArray()));

        $ret = $ob->isWriteCall("addObj");
        $this->assertTrue($ret);
        $ret = $ob->isWriteCall("xxaddObj");
        $this->assertFalse($ret);
    }/*}}}*/
    public function testCacheOberver4Add()
    {/*{{{*/
        $cache = new ArrayCache();
        $keyCache = new ArrayCache();
        $ob = new CacheObserver($keyCache);
        $cacheKey=100;
        $cache->set($cacheKey,"qihoo");
        $ob->regSensitive4Add('test',$cacheKey);
        $ob->invalidate4Add('test',$cache);
        $this->assertEqual(0,count($cache->getPropArray()));
//        $this->assertEqual(3,count($keyCache->getPropArray()));

//        $ret = $ob->isWriteCall("addObj");
//        $this->assertTrue($ret);
//        $ret = $ob->isWriteCall("xxaddObj");
//        $this->assertFalse($ret);
    }/*}}}*/
    public function testAppSession()
    {
        $app = AppSession::begin();
        $app->commit();
        $app =null;

    }

}/*}}}*/

class TestDao 
{/*{{{*/
    public function listByCond($name)
    {
        $data[] = array("id"=>1,"name"=>"test","desc"=>"abc");
        $data[] = array("id"=>2,"name"=>"test","desc"=>"abc");
        $data[] = array("id"=>3,"name"=>"test","desc"=>"abc");
        return $data;
    }
    public function update($data)
    {}
    public function add($data)
    {}
}/*}}}*/

Mock::generate('TestDao');
class CacheProxyTC extends UnitTestCase
{
    public function testNormal()
    {/*{{{*/

        $data[] = array("id"=>1,"name"=>"test","desc"=>"abc1");
        $data[] = array("id"=>2,"name"=>"test","desc"=>"abc2");
        $data[] = array("id"=>3,"name"=>"test","desc"=>"abc3");

        $cache = new ArrayCache();
        $keyCache = new ArrayCache();
        $ob = new CacheObserver($keyCache);

        $dao = new MockTestDao();
        $dao->setReturnValue('listByCond', $data);
        $dao->expectCallCount("listByCond", 1);
        $proxyDao= new RWCacheProxy($cache,$dao,'test',$ob);
        $data1=$proxyDao->listByCond("name");
        $data2=$proxyDao->listByCond("name");
        $this->assertEqual($data1,$data2);
        
    }/*}}}*/

    public function test4Update()
    {/*{{{*/
        try
        {
            $data[] = array("id"=>1,"name"=>"test","desc"=>"abc1");
            $data[] = array("id"=>2,"name"=>"test","desc"=>"abc2");
            $data[] = array("id"=>3,"name"=>"test","desc"=>"abc3");

            $cache = new ArrayCache();
            $keyCache = new ArrayCache();
            $ob = new CacheObserver($keyCache);

            $dao = new MockTestDao();
            $dao->setReturnValue('listByCond', $data);
            $dao->expectCallCount("listByCond", 2);
            $proxyDao= new RWCacheProxy($cache,$dao,'test',$ob);
            $data1=$proxyDao->listByCond("name");
            $data1=$proxyDao->listByCond("name");
            $data = $data1[0];
            $data["name"]="bbs";
            $proxyDao->update($data);
            $data2=$proxyDao->listByCond("name");
            $data2=$proxyDao->listByCond("name");
            //        $this->assertEqual($data1,$data2);
        }
        catch(Exception $e)
        {
            echo  $e->getMessage();
            echo $e->getTraceAsString();
            throw $e;
        }

    }/*}}}*/
    public function test4Add()
    {/*{{{*/
        try
        {
            $data[] = array("id"=>1,"name"=>"test","desc"=>"abc1");
            $data[] = array("id"=>2,"name"=>"test","desc"=>"abc2");
            $data[] = array("id"=>3,"name"=>"test","desc"=>"abc3");

            $cache = new ArrayCache();
            $keyCache = new ArrayCache();
            $ob = new CacheObserver($keyCache);

            $dao = new MockTestDao();
            $dao->setReturnValue('listByCond', $data);
            $dao->expectCallCount("listByCond", 2);
            $proxyDao= new RWCacheProxy($cache,$dao,'test',$ob);
            $proxyDao->catch4add_listByCond("name");
            $data1=$proxyDao->listByCond("name");
            $data1=$proxyDao->listByCond("name");
            $data = $data1[0];
            $data["name"]="bbs";
            $data["id"]="4";
            $proxyDao->add($data);
            $data2=$proxyDao->listByCond("name");
            $data2=$proxyDao->listByCond("name");
        }
        catch(Exception $e)
        {
            echo  $e->getMessage();
            echo $e->getTraceAsString();
            throw $e;
        }
    }/*}}}*/
}
?>
