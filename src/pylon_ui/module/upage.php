<?php 
class Upage
{/*{{{*/
    public function __construct($dataPager,$uargs)
    {/*{{{*/
        $this->curPage   = $dataPager->curPage;
        $this->haveLeft  = $dataPager->curPage!=1? 1:0;
        $this->haveRight = $dataPager->curPage!= $dataPager->totalPages ? true:false;
        $this->nextPage  = $dataPager->curPage + 1 ;
        $this->prePage   = $dataPager->curPage -1 ;
        $this->totalRow  = $dataPager->totalRows;
        $this->totalPage = $dataPager->totalPages;
        $this->begin     = $dataPager->curPage-5 > 0 ? $dataPager->curPage -4 : 1;
        $this->end       = $dataPager->totalPages >  $dataPager->curPage +5 ? $dataPager->curPage + 5  : $dataPager->totalPages;
        $this->build($uargs);
    }/*}}}*/
    public function build($uargs,$css="")
    {/*{{{*/

        $u = new Udom();
        if($this->haveLeft)
        {/*{{{*/
            $item[]=$u->a_title_href("首页"  ,"?pageno=1&{$uargs}","|&lt;");
            $item[]=$u->a_title_href("上一页","?pageno={$this->prePage}&{$uargs}","&lt;&lt;");
        }/*}}}*/

        for($i=$this->begin;$i<=$this->end;$i++)
        {/*{{{*/
            if( $this->curPage == $i )
                $item[]=$u->strong("$i");
            else
                $item[]=$u->a_href("?pageno={$i}&{$uargs}","$i");
        }/*}}}*/
        $this->lableList=$lableList;

        if($this->haveright)
        {/*{{{*/
            $item[] = $u->a_title_href("下一页"  ,"?pageno={$this->nextPage}&{$uargs}",">&gt;&gt;");
            $item[] = $u->a_title_href("未页"  ,"?pageno={$this->totalPage}&{$uargs}","&gt;|");
        }/*}}}*/
        $this->obj = $u->div_class("pages",
            $u->span( "共有信息{$this->totalRow}条 {$this->curPage} /{$this->totalPage}"),
            $item);
    }/*}}}*/
    public function show()
    {/*{{{*/
        $this->obj->show();

    }/*}}}*/
}/*}}}*/
