<?php

error_reporting(E_ALL);

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('pylon/autoload/class_loads.php');
require_once('config.php');
$root=Conf::APP_ROOT;
ComboLoader::setup(__FILE__,

    new ClassLoader(Conf::PSIONIC . "/pylon/",Conf::PSIONIC."/pylon/autoload_data.php"),   
    new ClassLoader("$root/", "$root/common_load_data.php"),
    new ClassLoader("$root/", "$root/admin_load_data.php"),
    new ClassLoader(Conf::SHOW_SVC_ROOT,Conf::SHOW_SVC_ROOT."/showsvc_load_data.php"),
    new ClassLoader(Conf::ZMARK_ROOT,Conf::ZMARK_ROOT."/autoload_data.php"),
    new ClassLoader("$root/../test/","$root/../test/autoload_data.php"));

TestAssemply::setup();

class SysGod extends PropertyObj
{/*{{{*/
    public  $contentDefConf = array();
    public  $themeDefConf = array();
    public function __construct()
    {/*{{{*/
        parent::__construct();
    }/*}}}*/
    public function registSowner($username,$password,$email)
    {/*{{{*/
        $res = UcPassportSvc::register($username,$email,$password);
        if(!$res['errno'])
        {
            $objs=SiteOwnerSvc::createOne(intval($res['data']['uid']),$username,$email);
            $sowner= $objs->siteOwner;
            $sowner->active($sowner->activekey);    
        }
        else
        {
            $ures = UcPassportSvc::getUser($username);
            if(!$ures['errno'])
            {
                $objs=SiteOwnerSvc::createOne(intval($ures['data']['uid']),$username,$email);
                $sowner= $objs->siteOwner;
                $sowner->active($sowner->activekey);    
            }
            else
            {
                throw  new SysException($ures['errmsg']);
            }
        }
        return $sowner;
    }/*}}}*/

    public function buildFluxData($ownerArr)
    {/*{{{*/
        $cnt = count($ownerArr);
        $showData[]= "1000,220,120,110,200,50,{$ownerArr[0]}";
        if( $cnt <=1) return $showData;
        $showData[]= "10,1,1,0,1,0,{$ownerArr[1]}";
        if( $cnt <=2) return $showData;
        $showData[]= "104,12,5,5,20,3,{$ownerArr[2]}";
        if( $cnt <=3) return $showData;
        $showData[]= "100,20,2,2,50,1,{$ownerArr[3]}";
        if( $cnt <=4) return $showData;
        $showData[]= "10,1,1,0,0,0,{$ownerArr[4]}";
        if( $cnt <=5) return $showData;
        $showData[]= "104,12,5,5,30,2{$ownerArr[5]}";
        if( $cnt <=6) return $showData;
        return $showData;
    }/*}}}*/

    public function setupPartnerDomain()
    {/*{{{*/
        $email1    = "test1@zapollo.com";
        $email2    = "test2@zapollo.com";
        $email3    = "test3@zapollo.com";
        $this->siteowner1= $this->registSowner("test1","test1",$email1);
        $this->siteowner2= $this->registSowner("test2","test2",$email2);
        $this->siteowner3= $this->registSowner("test3","test3",$email3);
    }/*}}}*/
    public function setupSettleDomain()
    {/*{{{*/

    }/*}}}*/

    public function setupAdOwnerDomain()
    {/*{{{*/
        $this->adowner1  = AdOwner::createByBiz("测试-奥美");
        $this->adowner2  = AdOwner::createByBiz("BannerLab");

        $this->product1  = Product::createByBiz("奥美主题", $this->adowner1->id(),PopularizeType::POPU_THEME,AdvModel::CPC);
        $this->product1->setValidtime(OccurDate::dayBefore(10), OccurDate::dayAfter(100));
        $this->product2  = Product::createByBiz("奥美Words",$this->adowner1->id(),PopularizeType::POPU_THEME,AdvModel::CPA);
        $this->product2->setValidtime(OccurDate::dayBefore(10), OccurDate::dayAfter(100));
        $this->product3  = Product::createByBiz("ZLabs",$this->adowner2->id(),PopularizeType::POPU_CONTENT,AdvModel::CPS);
        $this->product3->setValidtime(OccurDate::dayBefore(10), OccurDate::dayAfter(100));

        $this->stg1 =  SettleStg::createByBiz("标准10元",SettleStgDef::SETTLE_BY_CLKUV,
            new FixedPriceDef(),new NullCostDef(),10 * 100 ,1000,"1000次点击IP 10元");
        $this->stg2 =  SettleStg::createByBiz("标准20元",SettleStgDef::SETTLE_BY_CLKUV,
            new FixedPriceDef(),new NullCostDef(),20 * 100,1000,"1000次点击IP 20元");
        $this->stg3 =  SettleStg::createByBiz("GX20元",SettleStgDef::SETTLE_BY_AUV,
            new FixedPriceDef(),new NullCostDef(),20 * 100,1000,"1000次点击IP 20元");

        $costDef = new ChannelCostDef();
        $costDef->addFixed('ALIPAY','支付宝',5);
        $costDef->addRate ('CMCC','中国移动',0.4);
        $costDef->addRate ('TEL_*','通用通道',0.6);
        $this->stg4 = SettleStg::createByBiz("CPS佣金5%",SettleStgDef::SETTLE_BY_CPS,
            new RatePriceDef(),$costDef,0.05,1,"CPS佣金5%");

        $this->contract1 = Contract::createByBiz($this->product1,$this->stg1,"WeeklySettleCycle"); 
        $this->contract2 = Contract::createByBiz($this->product2,$this->stg3,"MonthlySettleCycle"); 
        $this->contract3 = Contract::createByBiz($this->product3,$this->stg4,"WeeklySettleCycle"); 

        $advDomain = DDA::ins()->get_AdvDomain_by_id(10);
        $advDomain->addProduct($this->product1->id());
        $advDomain->addProduct($this->product2->id());
        $advDomain->addProduct($this->product3->id());

        $this->advdomain1 = $advDomain;

        $this->contentDefConf['advdomainid'] = $advDomain->id();
        $this->themeDefConf['advdomainid'] = $advDomain->id();

        $dims['content'] = array('name'=>'内容','color'=>'red','form'=>'checkbox');
        $sysconf = new AdvAttrConfSvc();
        $sysconf->set($dims);
        $sysconf->close();

        AdvTags::create('content','女性',1);
        AdvTags::create('content','历史',1);
        AdvTags::create('content','军事',1);
        AdvTags::create('content','言情',1);
        
    }/*}}}*/
    public function setupSysInit()
    {/*{{{*/
        $acct =  Account::createByBiz(AccountDef::USP_ID,AccountDef::USP_MAIN); 
        $acct =  Account::createByBiz(AccountDef::USP_ID,AccountDef::USP_FILLING); 
    }/*}}}*/
    public  function setupTpls()
    {/*{{{*/

        $tpl = '
	<style>
#pos-wrapper{
	margin:0;
	padding:5px 5px 18px;
	border:1px solid none;
	position:relative;
       width:100%;
       height:100%;
}
#pos-wrapper a#icon{
	background: transparent none no-repeat scroll 0% 50%;
	-moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;
	z-index: 150;
	height: 17px;
	width: 100px;
	position: absolute;
}
.dot{
	font-size:1px;
	height:1px;
	overflow:hidden;
	position:absolute;
	width:1px;
}
.line{
	font-size:1px;
	height:1px;
	overflow:hidden;
	position:absolute;
}
#pos-wrapper table,#pos-wrapper table td{
	vertical-align: middle;
	text-align:center;
	align:center;
	margin:0 auto;
	padding:0;
	border:0;
}
#pos-wrapper table{
	height:100%;
	width:100%;
	text-align:center;
	border-collapse:collapse;
	empty-cells:show;
	border:0;
}
</style>
<div id="pos-wrapper">
<a id="icon" style="" href="http://www.hainei.com" target="_blank">
<div id="icon-pic" style="position: absolute; top: 0px; height: 17px; width: 104px; left: -4px; z-index: 2; background-repeat: no-repeat;"></div>
<div id="icon-bg" style="position: absolute; top: 0px; height: 17px; width: 100px; left: 0px; z-index: 1;"></div>
<div id="icon-lang" style="width: 17px; height: 17px; position: absolute; left: -17px; top: 0px; z-index: 1;"></div>
</a>
[_ADVS_]
</div>
<script>
(function(){
	var browser = navigator.userAgent.toLowerCase();
	var isopera = (browser.indexOf("opera") > -1);
	var isie = (!isopera && browser.indexOf("msie") > -1);
	var icopacity = 0.6;	
		document.getElementById("pos-wrapper").style.border = "1px solid "+borderColor;
	document.getElementById("pos-wrapper").style.width  = (wt-12)+"px";
	document.getElementById("pos-wrapper").style.height = (ht-25)+"px";
	document.getElementById("icon-bg").style.backgroundColor = borderColor;
	document.getElementById("icon").style.top = parseInt(document.getElementById("pos-wrapper").style.height)+7+"px";
	document.getElementById("icon").style.left= parseInt(document.getElementById("pos-wrapper").style.width)-89+"px";
	if(isie){
		document.getElementById("icon-pic").style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(src=http://banner.alimama.com/uploads/materials/original/2009-02-27/42f3c0a3c59d2f391ee233567249a99020892c7f.png)";
		document.getElementById("icon-bg").style.filter = "Alpha(opacity="+icopacity*100+")";
	}else{
		document.getElementById("icon-pic").style.backgroundImage = "url(http://banner.alimama.com/uploads/materials/original/2009-02-27/42f3c0a3c59d2f391ee233567249a99020892c7f.png)";
		document.getElementById("icon-bg").style.opacity = icopacity;
	}
	cd = document.getElementById("icon-lang");
	function at(x){
		return Math.atan(x/2)*6;
	}
	function t(y){
		return Math.tan(y/6)*2;
	}
	function h2(x,y){
		return x*x*y*y/(x*x+y*y);
	}
	function drawdot(l,t,o){
		o=o*icopacity
		var od = document.createElement("div");
		od.style.backgroundColor = borderColor;
		od.className="dot";
		od.style.zIndex=20;
		od.style.left = l.toString()+"px";
		od.style.top = t.toString()+"px";
		od.style.opacity = o;
		var oie = o*100;
		od.style.filter="Alpha(opacity="+oie+")";
		cd.appendChild(od);
	}
	function drawline(l,t){
		var od = document.createElement("div");
		od.style.backgroundColor = borderColor;
		od.className="line";
		od.style.zIndex=10;
		od.style.left = l.toString()+"px";
		od.style.top = t.toString()+"px";
		od.style.width = (17-l).toString()+"px";
		od.style.opacity = icopacity;
		od.style.filter="Alpha(opacity="+(icopacity*100)+")";
		cd.appendChild(od);
	}
	var lo = [];
	for(var i=8;i<=16;i++){
		for(var j=0;j<=8;j++){
			var x = j-8;
			var xc = at(x);			
			var y = 8-i;
			var yc = t(y);			
			var xcha = Math.abs(x-yc);
			var ycha = Math.abs(y-xc);
			var xyh2 = h2(xcha,ycha);		
			if(xyh2<=1){
				drawdot(j,i,(1-Math.sqrt(xyh2)));
				if(x-yc>=0&&y-xc<=0&&!lo[i]){
					lo[i] = true;
					drawline(j,i);
				}
			}
		}
	}
	for(var i=0;i<=8;i++){
		for(var j=8;j<=16;j++){
			var x = j-8;
			var y = 8-i;
			var xc = at(x);
			var yc = t(y);
			var xcha = Math.abs(x-yc);
			var ycha = Math.abs(y-xc)
			var xyh2 = h2(xcha,ycha);
			if(xyh2<=1){
				drawdot(j,i,(1-Math.sqrt(xyh2)));
				if(x-yc>=0&&y-xc<=0&&!lo[i]){
					lo[i] = true;
					drawline(j,i);
				}
			}
		}
	}	
	drawdot(8,8,1);
})();
</script>
	';
                                            
        $this->playtpl1 = PlayTpl::createByBiz('测试播放模板',TplLang::LANG_HTML,$tpl);
        $this->contentDefConf['playtplid'] = $this->playtpl1->id();

        $this->playtpl2 = PlayTpl::createByBiz('主题播放模板',TplLang::LANG_HTML,'[_ADVS_]');
        $this->themeDefConf['playtplid'] = $this->playtpl2->id();

        $this->advtpl1 = AdvTpl::getDefaultTpl();
        $this->advtpl2 = AdvTpl::createByBiz("左右结构","html","ComboTplExport","<div><table><tr><td>[I,left:0x0]</td><td> [I,right:0x0]</td></tr></table>.</div>");
        $this->advtpl3 = AdvTpl::createByBiz("广告单元","html","ComboTplExport","
            <style>
            .advdemo{
                height:190px;
                width:140px;
                overflow:hidden;
                font-family:Tahoma,SimSun,Arial;
                font-weight:200;
                font-size:12px;
                line-height:15px;
                position:relative;
                margin: 0 auto;
    }
    .advdemo img {border:0;}
    .advdemo .summary {
        position:absolute;absolute
            height:45px;
        width:125px;
        bottom:1px;
        left:0px;
        text-align:left;
        margin:0 5px;
        overflow:hidden;
    }
    .advdemo .photo {
        height:110px;
        width:110px;
        border:1px solid #DDDDDD;
        margin:5px auto 5px;
        overflow:hidden;
    }
    .advdemo .price {
        padding-left:8px;
        height:17px;
    }
    .advdemo .price .now-price{
        color:#CC0000;
        font-size:14px;
        font-weight:700;
        float:left;
        white-space:nowrap;
    }
    .advdemo .price .now-price em{
        font-style:normal;
        background:transparent url(http://assets.taobaocdn.com/ark/img/rmb.gif) no-repeat scroll 0 -1px;
        padding:0 2px 0 16px;
    }
    .advdemo .price .market-price{
        float:left;
    }
    .advdemo .price .market-price del{
        color:#999999;
        font-size:11px;
        line-height:18px;
        padding-left:5px;
        white-space:nowrap
            font-family:simsun;
    }
    .advdemo .rank {
        
    }
    </style>

        <div class=\"advdemo\">
        <div class=\"summary\"><a target=\"_blank\" href=\"#\">[I,first:0x0]</a></div>
        <div class=\"photo\">
                <a target=\"_blank\" href=\"#\">[I,second:0x0]</a>
                       </div>
                        <div class=\"price\">
                                       <div class=\"now-price\"><em>xx.00</em></div>
                                                      <div class=\"market-price\"><del>yyy.00</del></div>
                                                             </div>
                                                             </div>
                    ");
    $this->advtpl4 = AdvTpl::createByBiz("crossmo测试广告","html","ComboTplExport","
        <style>
        .videounit {
            width:125px;

            height:135px;

            overflow:hidden;

            background-color: #dddddd;
            position:relative;
            line-height:15px;15px
    }
    .videounit .photo {
        width:115px;
        height:80px;
        margin:5px auto 5px;
        overflow:hidden;
        position:relative;
    }
    .videounit .timer {
        background-color:#000;
        font-family: Verdana, Arial, Helvetica, sans-serif;

        font-size: 10px;

        line-height: 15px;

        color: #FFF;
        height:15px;
        left:0;
        top:0;
        position:absolute;
    }
    .videounit .price {
        width:54px;

        height:42px;

        left: 62px;/*115-54+1*/

        top: 39px;/*80-42+1*/

        background-image:url(images/price_corner.png);

        position:absolute;
    }
    .videounit .price em {
        right:5px;
        bottom:3px;
        font-size:16px;

        color: #FF0;
        font-style:normal;
        position:absolute;
    }
    .videounit .summary {
        height:45px;
        width:110px;
        bottom:2px;
        left:0px;
        text-align:left;
        font-size:12px;
        font-family:Tahoma,SimSun,Arial;
        font-weight:200;
        margin:0 5px;
        overflow:hidden;
        position:absolute;
    }
    </style>

        <div class=\"videounit\">
        <div class=\"photo\">
        [I,photo:0x0]
        <span class=\"timer\">[I,timer:0x0]</span>
        <span class=\"price\"><em>[I,price:0x0]</em></span>
        </div>
        <div class=\"summary\"> 
        [I,summary:0x0]
        </div>
       </div> 
        ");
    }/*}}}*/
    public function setupAdvData()
    {/*{{{*/

        $spec1     = ShowSpec::createByBiz(180,150);
        $spec2     = ShowSpec::createByBiz(200,200);


        $goal = AdvSubject::createByBiz("测试",OccurDate::dayAfter(30),"測試");

        $elements['main'] =  new DirectElement('<img src="http://www.google.cn/intl/zh-CN/images/logo_cn.gif">');

        $this->adv1      = Advert::createByBiz( $spec1,$this->product1,
            $goal,$this->advtpl1,PopularizeType::POPU_THEME,"http://www.google.cn", $elements
        );

        $img  = Material::createByBiz(MaterialType::IMG,200,100,"http://aimg.qihoo.com/images/2008/agg/qihoo/120-180.gif");
        $elements2['left']  =   new MaterialElement($img->id());
        $elements2['right'] =   new DirectElement("google");

        $this->adv2     = Advert::createByBiz($spec2,$this->product1,
            $goal,$this->advtpl2,PopularizeType::POPU_THEME, "http://www.360ting.com", $elements2
            );


        $spec   = ShowSpec::createByBiz(110,110);
        $product       = $this->product3;
        $advtpl        = $this->advtpl1;
        $this->showspec = $spec;

        $this->contentDefConf['advspecid']= $spec->id();
        $this->contentDefConf['advtplid'] = $advtpl->id();
        
        $product  = DDA::ins()->get_Product_by_id($this->product3->id());
        $this->product = $product;
        $advConf  = $product->getAdvConf();
        $advConf->specID = $spec->id();
        $advConf->advtplID = $advtpl->id();
        $product->setAdvConf($advConf);
        $poputype= PopularizeType::POPU_CONTENT;
        $target = "http://www.sina.com.cn";


        $elements1['main'] =  new DirectElement('<img src="http://ec4.images-amazon.com/images/I/51-otH5SuoL._AA110_.jpg">');
        $adv1 = Advert::createByBiz($spec,$product,$goal,$advtpl,$poputype ,$target,$elements1);
        $elements2['main'] =  new DirectElement('<img src="http://ec4.images-amazon.com/images/I/51wtnbQMRhL._AA110_.jpg">');
        $adv2 = Advert::createByBiz($spec,$product,$goal,$advtpl,$poputype ,$target,$elements2);
        $elements3['main'] =  new DirectElement('<img src="http://ec4.images-amazon.com/images/I/51NgqQ1snNL._AA110_.jpg">');
        $adv3 = Advert::createByBiz($spec,$product,$goal,$advtpl,$poputype ,$target,$elements3);
        $elements4['main'] =  new DirectElement('<img src="http://ec4.images-amazon.com/images/I/41X7hh2ek2L._AA110_.jpg">');
        $adv4 = Advert::createByBiz($spec,$product,$goal,$advtpl,$poputype ,$target,$elements4);
        $elements5['main'] =  new DirectElement('<img src="http://ec4.images-amazon.com/images/I/51DeMFgX57L._AA110_.jpg">');
        $adv5 = Advert::createByBiz($spec,$product,$goal,$advtpl,$poputype ,$target,$elements5);
        $elements6['main'] =  new DirectElement('<img src="http://ec4.images-amazon.com/images/I/51OMbg0JQEL._AA110_.jpg">');
        $adv6 = Advert::createByBiz($spec,$product,$goal,$advtpl,$poputype ,$target,$elements6);
        $elements7['main'] =  new DirectElement('<img src="http://ec4.images-amazon.com/images/I/51xxnRa6bGL._AA110_.jpg">');
        $adv7 = Advert::createByBiz($spec,$product,$goal,$advtpl,$poputype ,$target,$elements7);
        $elements8['main'] =  new DirectElement('<img src="http://ec4.images-amazon.com/images/I/41Ew0dFgk7L._AA110_.jpg">');
        $adv8 = Advert::createByBiz($spec,$product,$goal,$advtpl,$poputype ,$target,$elements8);

        
        $themepoputype= PopularizeType::POPU_THEME;
        $themespec   = ShowSpec::createByBiz(170,260);
        $elements8['main'] =  
            new DirectElement('<img src="http://pics.taobaocdn.com/bao/album/b2cdigital/KXNL_170x260_081225_yama.jpg">');
        $adv8 = Advert::createByBiz($themespec,$this->product1,$goal,$advtpl,$themepoputype,$target,$elements8);

        $elements9['main'] =  
            new DirectElement('<img src="http://pics.taobaocdn.com/bao/album/b2cdigital/HOT_170x260_081009_01_cfz.jpg">');
        $adv9 = Advert::createByBiz($themespec,$this->product1,$goal,$advtpl,$themepoputype,$target,$elements9);

        $elements10['main'] = new DirectElement('<img src="http://pics.taobaocdn.com/bao/album/b2cjiadian/mall-yanhuan-20081104-_170x260.jpg ">');
        $adv10 = Advert::createByBiz($themespec,$this->product1,$goal,$advtpl,$themepoputype,$target,$elements10);

        $this->adv3= $adv1;

        $name = $this->siteowner1->id().'-播放库-'.date("Y-m-d H:i");
        $advDepot = AdvDepot::createByBiz($name,$this->siteowner1->id()); 
        $advDepot->addAdv($adv1);
        $advDepot->addAdv($adv2);
        $advDepot->addAdv($adv3);
        $advDepot->addAdv($adv4);
        $advDepot->addAdv($adv5);
        $advDepot->addAdv($adv6);
        $advDepot->addAdv($adv7);
        $advDepot->addAdv($adv8);
        $this->advdepot1 = $advDepot;
    }/*}}}*/
    public function setupFluxData()
    {/*{{{*/
        $this->repareFluxData();
        for($i=20; $i >=0 ; $i--)
        {
            $t = OccurDate::dayBefore($i);
            $this->setupDayFluxData($t);
            echo "\n---import $t  data!-----over!-----------\n"; 
            $isSettle = $i% 7 ;
            if ($isSettle  == 0 ) 
            {
                echo "\n---settle $t flux  data!-----begin-----------\n"; 
                $settleTime  = OccurDate::dayBefore($i+7);
                $this->setupSettle($settleTime);
                echo "\n---settle $t flux  data!-----over!-----------\n"; 
            }
        }
    }/*}}}*/
    public function repareFluxData()
    {/*{{{*/

        $sownerID1 = $this->siteowner1->id();
        $sownerID2 = $this->siteowner2->id();
        $sownerID3 = $this->siteowner3->id();

        $pdtID1    = $this->product1->id();
        $pdtID2    = $this->product2->id();
        $pdtID3    = $this->product3->id();

        $domains = array(
            "union.sina.com",
            "sina.com",
            "google.com",
            "google.com.cn",
            "g.cn",
            "baidu.cn"
        );

        $arrCombiner = new ArrayCombiner();
        //        $owners= $arrCombiner->combin(array($pdtID1,$pdtID2),
        //                                      array($sownerID1,$sownerID2,$sownerID3));



        $this->sownpdtOwners= $arrCombiner->combin(
            array($sownerID1,$sownerID2,$sownerID3),
            array($pdtID1,$pdtID2,$pdtID3)
        );
        $this->sownpdtDatas= $this->buildFluxData($this->sownpdtOwners);

        //        $showData = buildFluxData($owners);
        $domains2  =  $arrCombiner->combin(array($sownerID1),$domains);
        $domains3  =  $arrCombiner->combin(array($sownerID1),$domains,array(11));
        $this->siteData  =  $this->buildFluxData($domains);
        $this->siteData2 = $this->buildFluxData($domains2);
        $this->siteData3 = $this->buildFluxData($domains3);


        $this->advowners   = $arrCombiner->combin(array($pdtID1,$pdtID2),array($this->adv1->id(),$this->adv3->id()));

        $this->pdtDatas    = $this->buildFluxData(array($pdtID1,$pdtID2));
        $this->sownerDatas = $this->buildFluxData(array($sownerID1,$sownerID2,$sownerID3));
        $this->advdatas    = $this->buildFluxData($this->advowners);


        $this->dataDims = array(FLUX::SHOW_PV,FLUX::SHOW_UV,FLUX::CLK_NU,FLUX::CLK_UV,FLUX::ACT_NU,FLUX::ACT_UV);

        $this->pdtOwner    = array(OWNER::PDT_ID);
        $this->sownerOwner = array(OWNER::SOWNER_ID);
        $this->specOwner   = array(OWNER::SPEC_ID);
        $this->advOwner    = array(OWNER::PDT_ID,OWNER::ADV_ID);
        $this->siteOwner   = array(OWNER::SITE);

        $this->sownerPdt   = array(OWNER::SOWNER_ID,OWNER::PDT_ID);
        $this->sownerSite  = array(OWNER::SOWNER_ID,OWNER::SITE);
        $this->sownerSitePlayer  = array(OWNER::SOWNER_ID,OWNER::SITE,OWNER::PLAYER_ID);
    }/*}}}*/
    public function setupDayFluxData($t)
    {/*{{{*/
        $filter = '';
        $settleImporter = new SettleImporter();
        $simpleImporter = new SimpleImporter();
        $settleImporter->importFluxData("SettleFlux",$t,$this->sownerPdt  ,$this->dataDims,$filter,$this->sownpdtDatas);
        $simpleImporter->importFluxData("EvalProductFlux",$t,$this->pdtOwner   ,$this->dataDims,$filter,$this->pdtDatas);
        $simpleImporter->importFluxData("EvalSownerFlux",$t,$this->sownerOwner,$this->dataDims,$filter,$this->sownerDatas);
//        $simpleImporter->importFluxData("EvalSpecFlux",$t,$this->specOwner  ,$this->dataDims,$filter,$this->specdatas);
        $simpleImporter->importFluxData("EvalAdvFlux",$t,$this->advOwner   ,$this->dataDims,$filter,$this->advdatas);
        $simpleImporter->importFluxData("EvalSiteFlux",$t,$this->siteOwner  ,$this->dataDims,$filter,$this->siteData);
        $simpleImporter->importFluxData("SownerSiteFlux",$t,$this->sownerSite,$this->dataDims,$filter,$this->siteData2);
    }/*}}}*/
    public function setupSettle($t)
    {/*{{{*/
        try
        {
            //SettleSvc::settle($t);
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            echo  "$errorMsg\n";

        }
    }/*}}}*/
    public function setupPos()
    {/*{{{*/

        $playStgObj1 = PlayStgObj::createByBiz("简单播放",new SingletonPlayStg());
        $playstg = new RotatePlayStg();
        $playstg->setRotateSecond(5);
        $playStgObj2 = PlayStgObj::createByBiz("标准轮播",$playstg);

        $playtpl  = $this->playtpl1;
        $playStgObj = $playStgObj2;
        $matchStg = new MatchStgByOrigRecom;
        $matchStg->setMatchBatch(3);
        $matcher = RoleAdvMatcher::save($matchStg,'只出推荐');
        $playctrl = PlayCtrl::createByBiz(array($this->product->id(),$this->product1->id()),$this->showspec
            ,$playStgObj,$matcher,$this->advdepot1->id(),'test1',Scope::S_PUBLIC);

        $matchStg->setMatchBatch(7);
        $matcher2 = RoleAdvMatcher::save($matchStg,'只出推荐');


        $this->contentDefConf['matcherid'] = $matcher->id();
        $this->contentDefConf['playstgid'] = $playStgObj2->id();

        $this->themeDefConf['matcherid'] = $matcher2->id();
        $this->themeDefConf['playstgid'] = $playStgObj2->id();

        $width  = 230;
        $height = 230;
        $row = 2;
        $col = 2;
        $advDomain = DDA::ins()->get_AdvDomain_by_id(10);
        $matchData = array();

        AdvPositionSvc::saveAdvPosition(PopularizeType::POPU_CONTENT,$this->siteowner1,$matchData
            ,$width,$height,$this->playtpl1,$playctrl->id()
            ,$row,$col,null,array('ver'=>1,'borderColor'=>'red'));

    }/*}}}*/

    public function defautlSetting()
    {/*{{{*/
        $conf = new ContentAdvConfSvc();
        $conf->set($this->contentDefConf);
        $conf->close();

        $themeconf = new ThemeAdvConfSvc();
        $themeconf->set($this->themeDefConf);
        $themeconf->close();
    }/*}}}*/

}/*}}}*/

try
{
    $sql = DebugUtls::sqlLogEnable();
    $god = new SysGod();
    
    $steps[] = "setupSysInit";
    $steps[] = "setupPartnerDomain";
    $steps[] = "setupAdOwnerDomain";
    $steps[] = "setupTpls";
    $steps[] = "setupAdvData";
    $steps[] = "setupPos";
    $steps[] = "defautlSetting";
    foreach($steps as $s)
    {
        $appSess = AppSession::begin();
        call_user_func(array($god,$s));
        $appSess->commit();
    }
    $god->setupFluxData();
}
catch( Exception $e)
{/*{{{*/
    $errorMsg = $e->getMessage();
    $errorPos = $e->getTraceAsString();
    echo  "$errorMsg\n";
    echo  "$errorPos\n";
}/*}}}*/



?>
