<?php

class Action_main extends AdminActionBase
{/*{{{*/
    public function _run($request,$xcontext)
    {/*{{{*/
        return XNext::useTpl("mainx.html","STRUCT");
    }/*}}}*/
}/*}}}*/
