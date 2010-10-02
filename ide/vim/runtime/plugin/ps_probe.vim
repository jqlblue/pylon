if exists("probe_ide_loaded")
    finish
endif
let probe_ide_loaded = 1

function Probe_build_index()
    execute  '! ' . g:probe_prjroot. '/_prj/srcindex.sh ' 
    execute  "cs kill -1 "
    execute  "cs add " . g:probe_prjroot. '/_prj/cscope.out  '
endfunction

function Probe_prj_cmd(cmd)
    execute  '! ' . g:probe_prjroot. '/_prj/' . a:cmd 
endfunction

function Probe_action2tpl(action_do)

    let action = substitute( substitute(a:action_do, "do_", "", ""),"submit_","",""  )
    let findcmd = 'find ' . g:probe_prjroot . '/ -name "'. action .'.h*" | grep -v -E "(\.svn)|(~)"'
    echo findcmd
    let tplfile = system(findcmd)
    let result = split(tplfile,"\n")
    if  len(result)  > 1 
        let res_options=["please chose the template file"]
        for  i in range(0, len(result) -1)
            call add(res_options, printf("%d . %s",i+1, result[i]))
        endfor
        let chose= inputlist(res_options)
        execute " find " . result[chose -1]
    elseif  len(result) == 1 
        execute " find " . result[0]
    else
        echo "unfound the template file "
    endif 
endfunction


function Probe_debug_watch(varname,type)
    let n= line('.')  
    let pos = indent('.')
    let watchstr = repeat(" ",pos) .  printf("Debug::watch(__FILE__,__LINE__,%s,'%s');",a:varname,a:varname)
    if a:type == "up" 
        let    n = n-1
    endif
    call append(n,watchstr)
endfunction

function Probe_dbc_add(varname,type)
    let pos = indent('.')
    let watchstr = repeat(" ",pos+4) . printf("DBC::%s(%s,'%s');",a:type,a:varname,a:varname)
    let n= line('.')  
    call append(n+1,watchstr)
endfunction

let g:probe_prj_init="init.sh"
let g:probe_prj_ci  = "ci.sh"


function Probe_ide_init(prjroot )
    let g:probe_prjroot = a:prjroot 
"    let g:probe_prj_tplpath = a:prjroot
    noremap <F9> <Esc>: call Probe_build_index() <CR>
    noremap <F8> <Esc>: call Probe_prj_cmd(g:probe_prj_init) <CR>
    noremap <F7> <Esc>: call Probe_prj_cmd("build_index.sh") <CR>
    map \zci <Esc>:call Probe_prj_cmd(g:probe_prj_ci) <CR>

    noremap \dw <Esc>:call Probe_debug_watch(expand("<cword>"),"down")<CR> 
    noremap \dW <Esc>:call Probe_debug_watch(expand("<cWORD>"),"down")<CR> 
    noremap \du <Esc>:call Probe_debug_watch(expand("<cword>"),"up")<CR> 
    noremap \dU <Esc>:call Probe_debug_watch(expand("<cWORD>"),"up")<CR> 

    noremap \re <Esc>:call Probe_dbc_add(expand("<cword>"),"requireNotNull")<CR> 
    noremap \rn <Esc>:call Probe_dbc_add(expand("<cword>"),"requireNotNull")<CR> 
    noremap \rt <Esc>:call Probe_dbc_add(expand("<cword>"),"requireTrue")<CR> 
    noremap \rue <Esc>:call Probe_dbc_add(expand("<cword>"),"unExpect")<CR> 
    noremap \rui <Esc>:call Probe_dbc_add(expand("<cword>"),"unImplement")<CR> 

    noremap \at <Esc>:call Probe_action2tpl(expand("<cword>"))<CR> 
    ia s1   echo "---------------step 1 ---------------<br>\n";
    ia s2   echo "---------------step 2 ---------------<br>\n";
    ia s3   echo "---------------step 3 ---------------<br>\n";
    ia s4   echo "---------------step 4 ---------------<br>\n";
    ia s5   echo "---------------step 5 ---------------<br>\n";

    :command! -nargs=1 Ap :cs find  s do_<args>
    :command! -nargs=1 At :call Probe_action2tpl("<args>")
    :command! -nargs=1 Af :cs find  t -><args>

endfunction
"noremap <unique> <script> <Plug><SID>Add


