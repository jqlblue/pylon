<?php
class UtilityTest extends UnitTestCase
{
    public function testPromot()
    {/*{{{*/
        $keys =array('zuowenjian','zuowenping','zuowenqiang','wangqiang' );
        $what =Prompt::recommend('zuo',$keys);
        $this->assertEqual(count($what),3);

        $what =Prompt::recommend('zuowen',$keys);
        $this->assertEqual(count($what),3);

        $what =Prompt::recommend('zuowenA',$keys);
        $this->assertEqual(count($what),3);
        $what =Prompt::recommend('qiang',$keys);
        $this->assertEqual(count($what),3);

        $what =Prompt::recommend('wang',$keys);
        $this->assertEqual(count($what),1);

        $what =Prompt::recommend('wangqqing',$keys);
        $this->assertEqual(count($what),1);
    }/*}}}*/

    public function testJoinUtls()
    {/*{{{*/
        $str = JoinUtls::j2str("123","456","789");
        $this->assertEqual($str,"123456789");
        $str = JoinUtls::jarray(',',array("123","456","789"));
        $this->assertEqual($str,"123,456,789");
        $str = JoinUtls::jarray(',',array("123",null,"456","789"));
        $this->assertEqual($str,"123,456,789");

        $str= JoinUtls::jarrayEx(',',array("abc","efg","hig"),strtoupper); 
        $this->assertEqual($str,"ABC,EFG,HIG");

        $str= JoinUtls::jwithEgis(",","*",array("123","456","789"));
        $this->assertEqual($str,"*123*,*456*,*789*");

        $str= JoinUtls::jassoArray(" and ","=",array("a"=>"123",'b'=>"456",'c'=>"789"));
        $this->assertEqual($str,"a = 123 and b = 456 and c = 789");
        
        $str= JoinUtls::jassoArrayEx(" and ",array("a"=>"abc",'b'=>"def",'c'=>"ghi"),
                            create_function('$key,$val','return "$key=[$val]";'));
        $this->assertEqual($str,"a=[abc] and b=[def] and c=[ghi]");

        $str= JoinUtls::joinPath("/usr/local/","apache/bin","");
        $this->assertEqual($str,"/usr/local/apache/bin/");
    }/*}}}*/
    public function testProp()
    {
        $prop= PropertyObj::create();
        $prop->id="me";
        $prop2= PropertyObj::create();
        $prop2->name="zwj";
        $prop2->merge($prop);
        $this->assertEqual($prop2->id,"me");
        $prop2->x=null;
        $prop2->y="";
        $prop2->z=0;
        $prop2->filter();
        $this->assertFalse($prop2->haveSet('x'));
        $this->assertFalse($prop2->haveSet('y'));
        $this->assertFalse($prop2->haveSet('z'));

        $prop2->x="y";
        $prop2->y="y";
        $prop2->z="y";
        $prop2->filter("y");

    }
    public function testSql()
    {
        $cmd="select a.mtype as mtype,tourl,material, width,height,a.status as status,taskid,y.adplayer__id as playerid, s.id as spec_id,a.id as adv_id,p.id as pdt_id,taskid,p.name as pdtname from advert as a, showspec as s,product as p,playtask as y where a.showspec__id = s.id and a.product__id= p.id and taskid = y.id order by playerid,taskid asc;";
        $cntcmd = preg_replace("/(select .+)(from .+)/","select count(1) as cnt \$2",$cmd);
        $this->assertEqual($cntcmd,"select count(1) as cnt from advert as a, showspec as s,product as p,playtask as y where a.showspec__id = s.id and a.product__id= p.id and taskid = y.id order by playerid,taskid asc;");
       
    }
}
?>
