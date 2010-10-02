<?php

abstract class  ActionBase  extends XAction
{/*{{{*/
    public $dda = null;
    public $ddq = null;
    public $sessSvc = null;
    public function __construct()
    {
        $this->dda = DDA::ins();
        $this->ddq = DQuery::ins();
        $this->sessSvc = ObjectFinder::find('SessionSvc');
        XAop::pos(XAop::NORET)->replace_by_match_cls(get_class($this),XNext::useTpl("AUTO","STRUCT")  );


    }

}/*}}}*/

class  AdminActionBase  extends XAction
{/*{{{*/
    public $dda = null;
    public $ddq = null;
    public $sessSvc = null;
    public function __construct()
    {/*{{{*/
        $this->dda = DDA::ins();
        $this->ddq = DQuery::ins();
        $this->sessSvc = ObjectFinder::find('SessionSvc');
        XAop::pos(XAop::NORET)->replace_by_match_cls(get_class($this),XNext::useTpl("AUTO","STRUCT")  );
    }/*}}}*/
    public function _setup($request,$xcontext)
    {/*{{{*/
        $pageCnt = $request->have("pageCnt")?$request->pageCnt:20;
        $request->pageno = ($request->have("pageno")&&$request->pageno>0)?$request->pageno:1;
        if(ActionUtls::needNewPage($request))
        {
            $request->pageObj = new DataPage($pageCnt);
        }
        $request->pageObj->gotoPage($request->pageno);

        $conf = XTools::actFinder()->_find($xcontext->action);
        $tags = $conf->extendValue('_tag');
//        $xcontext->appName = $conf->extendValue('_name');
        $xcontext->curPark = null;
        if(!empty($tags))
        {
            $allParks = Conf::getParkTags();
            foreach($tags as $tag)
            {
                foreach($allParks as $park)
                {
                    if($park == $tag)
                        $xcontext->curPark = $park;
                }
            }
        }

    }/*}}}*/
    public function _teardown($request,$xcontext)
    {/*{{{*/
        $ctrl= new UPageCtrl($request->pageObj);
        $pageCtrl= $ctrl->getPropArray();
        $pageCtrl['totalrows'] = $request->pageObj->totalrows;
        $xcontext->pageCtrl    = $pageCtrl;
    }/*}}}*/
    public function _run($request,$xcontext)
    {}

}/*}}}*/

abstract class ArsyncPostAdmBase extends AdminActionBase 
{
}
