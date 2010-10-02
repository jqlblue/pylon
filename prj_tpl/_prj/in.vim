set nowrap
set encoding=utf-8
set fileencoding=utf-8
set makeprg=$CUBE_WEB_HOME/test/unittest.sh
set tags=$CUBE_WEB_HOME/project/tags
set background=dark
set nu

noremap <F3> <Esc>:! $CUBE_WEB_HOME/src/runphp  $CUBE_WEB_HOME/test/alltest.php % <CR>
set errorformat=%m\ in\ %f\ on\ line\ %l 
"TagList conf
"map \zci <Esc>:!$CUBE_WEB_HOME/project/ci.sh<CR>
call Probe_ide_init($CUBE_WEB_HOME)
au! BufRead,BufNewFile *.html setfiletype php


