from string import Template
from inf import * 
from cgi import *
from utls  import * 
from sysconf import *

import types , re , os , string   

class mysql(resource) :
    host = "localhost"
    name = ""
    user   = ""
    password = ""
    sql = ""
    def __init__(self,name,user,sql,password="123",host="localhost"):
        self.host = host
        self.name = name
        self.password =password
        self.user  = user 
        self.sql   = sql 

    def locate(self):
        self.host   = env_exp.value(self.host)
        self.name   = env_exp.value(self.name)
        self.password = env_exp.value(self.password)
        self.user   = env_exp.value(self.user)
        self.sql    = env_exp.value(self.sql)

    def data(self):
        sql = "DROP DATABASE IF EXISTS $DBNAME;CREATE DATABASE $DBNAME DEFAULT CHARACTER SET UTF8;"
        sql +="GRANT ALL PRIVILEGES ON $DBNAME.* TO '$USER'@'%' IDENTIFIED BY '$PASSWD' ;"
        sql +="GRANT ALL PRIVILEGES ON $DBNAME.* TO '$USER'@'localhost' IDENTIFIED BY '$PASSWD' ;"
        cmd = Template(sql).substitute(DBNAME=self.name,USER=self.user,PASSWD=self.password)
        print("create database , please input mysql root password: ")
        shell.execmd(Template( 'mysql  -uroot -p -e "$CMD" ').substitute(CMD=cmd ))
        cmdtpl  = 'mysql $DBNAME -u$USER -p$PASSWD < $SQL'
        shell.execmd(Template(cmdtpl).substitute(DBNAME=self.name,USER=self.user,PASSWD=self.password,SQL=self.sql))

class nginx (resource):
    def start(self):
        cmd = ' service nginx start '
        shell.execmd(cmd)
    def stop(self):
        cmd = ' service nginx stop'
        shell.execmd(cmd)

class conf (resource):
    def path(self):
        pass
    def name(self):
        pass

class file_tpl(resource,conf):
    env  = None
    dst  = None

    def __init__(self,env, tpl,dst):
        self.env = env 
        self.dst   = dst
        self.tpl    = tpl

    def locate(self):
        if self.env is  not None :
            self.env.locate()
        self.dst  = env_exp.value(self.dst)
        self.tpl  = env_exp.value(self.tpl)

    def config(self):
        tpl_builder.build(self.tpl,self.dst)
    def path(self):
        return  self.dst


class conf_file(resource,conf):
    def __init__(self,path):
        self.path = path 
    def locate(self):
        self.path= env_exp.value(self.path)
    def path(self):
        return self.path 


class nginx_conf_tpl( file_tpl ):
    def config(self):
        file_tpl.config(self)
        dst_path  = "/etc/nginx/sites-enabled"
        tpl =  'rm $PATH/$DST ;ln -s $SRC $PATH/$DST'
        cmd = Template(tpl).substitute(PATH=dst_path, DST=os.path.basename(self.dst), SRC=self.dst)
        shell.execmd(cmd)

class apache_conf_tpl( file_tpl ):
    def config(self):
        file_tpl.config(self)
        dst_path  = "/etc/httpd/conf.d"
        tpl =  'rm $PATH/$DST ;ln -s $SRC $PATH/$DST'
        cmd = Template(tpl).substitute(PATH=dst_path, DST=os.path.basename(self.dst), SRC=self.dst)
        shell.execmd(cmd)


class apache (resource):
    def start(self):
        cmd = ' /sbin/service httpd start '
        shell.execmd(cmd)
    def stop(self):
        cmd = ' /sbin/service httpd stop'
        shell.execmd(cmd)



class files (resource):
    pass 


class script (resource):
    def __init__(self,env,script_file):
        self.env = env
        self.script_file = script_file 
    def locate(self):
        self.env.locate()
        self.script_file = env_exp.value(self.script_file)

    def config(self):
        cmd = self.script_file  +  " config"   
        shell.execmd(cmd)

    def start(self):
        cmd = self.script_file  +  " start"   
        shell.execmd(cmd)

    def stop(self):
        cmd = self.script_file  +  " stop"   
        shell.execmd(cmd)
    def data(self):
        cmd = self.script_file  +  " data"   
        shell.execmd(cmd)

class host(resource):
    def __init__(self,ip,domain):
        self.ip = ip 
        self.domain=domain
    def locate(self):
        self.ip     = env_exp.value(self.ip)
        self.domain = env_exp.value(self.domain)

    def config(self):
        path=os.path.dirname(os.path.realpath(__file__))
        cmdtpl="python $PATH/sysconf.py  -n $DOMAIN -f /etc/hosts  -t '#' -c '$IP $DOMAIN' "
        c = Template(cmdtpl).substitute(PATH=path,IP=self.ip,DOMAIN=self.domain)
        shell.execmd(c)

class links(resource):
    links_map={}
    def __init__(self,map):
        self.ori_map = map 

    def locate(self):
        for k ,v in self.ori_map.items():
            k=  env_exp.value(k)
            v=  env_exp.value(v)
            self.links_map[k] = v 
    def config(self):
        cmdtpl ="if test -L $DST ; then rm -rf  $DST ; fi ; dirname $DST | xargs mkdir -p ; ln -s  $SRC $DST"
        for k ,v in self.links_map.items():
            cmd = Template(cmdtpl).substitute(DST=k,SRC =v)
            shell.execmd(cmd)

class link(resource):
    def __init__(self,env,src,dst,force=False):
        self.env = env
        self.dst = dst
        self.src = src
        self.force   = force
    def locate(self):
        self.env.locate()
        self.dst = env_exp.value(self.dst)
        self.src = env_exp.value(self.src)

    def config(self):
        cmdtpl = "" 
        if self.force is True :
            cmdtpl ="if test -L $DST ; then rm -rf  $DST ; fi ; dirname $DST | xargs mkdir -p ; ln -s  $SRC $DST"
        else :
            cmdtpl ="if ! test -L $DST ; then   dirname $DST | xargs mkdir -p ;  ln -s   $SRC $DST ; fi;  "
        cmd = Template(cmdtpl).substitute(DST=self.dst,SRC =self.src)
        shell.execmd(cmd)

class nginx_conf_link(link):
    def __init__(self,env,src):
        f_name = os.path.basename(src)
        link.__init__(self,env,src,"/etc/nginx/sites-enabled/" + f_name ,True);


class copy(resource):
    def __init__(self,env,src,dst,force=False):
        self.env = env
        self.dst = dst
        self.src = src
        self.force   = force
    def locate(self):
        self.env.locate()
        self.dst = env_exp.value(self.dst)
        self.src = env_exp.value(self.src)

    def config(self):
        cmdtpl = "" 
        if self.force is True :
            cmdtpl ="if test -e $DST ; then rm -rf  $DST ; fi ; dirname $DST | xargs mkdir -p ; cp -r  $SRC $DST"
        else :
            cmdtpl ="if ! test -e $DST ; then   dirname $DST | xargs mkdir -p ; cp -r  $SRC $DST ; fi;  "
        cmd = Template(cmdtpl).substitute(DST=self.dst,SRC =self.src)
        shell.execmd(cmd)

class path(resource):
    env = None 
    arr = []
    dst = None
    paths= []
    def __init__(self,env,*args):
        self.env = env
        self.list = args

    def locate(self):
        if not self.env is None:
            self.env.locate()
        if not self.dst is None:
            self.paths.append( env_exp.value(self.dst))
        for v in self.arr:
            v=  env_exp.value(v)
            self.paths.append( v )
    def config(self):
        cmdtpl ="rm -rf  $DST ; mkdir -p   $DST ; chmod a+w  $DST; "
        for v in self.paths :
            cmd = Template(cmdtpl).substitute(DST=v)
            shell.execmd(cmd)


class autoload(resource): 
    def __init__(self,root,src,dst):
        self.src  = src  
        self.root = root
        self.dst  = dst 
    def locate(self):
        self.src  = env_exp.value(self.src)
        self.root = env_exp.value(self.root)
        self.dst  = env_exp.value(self.dst)
    def index(self):
        path=os.path.dirname(os.path.realpath(__file__))
        shell.execmd(Template('echo "" > $ROOT/._find_cls.tmp').substitute(ROOT=self.root)) 
        cmdtpl = 'find $SRC -name "*.php"   |  xargs  grep  -E "^ *(abstract)? *class "  >> $ROOT/._find_cls.tmp'
        for  s in self.src.split(':') :
            cmd = Template(cmdtpl).substitute( SRC =  self.root + '/' + s ,ROOT=self.root)
            shell.execmd(cmd)

        auto_file = self.root + "/" + self.dst +  "/_autoload_data.php" ;
        with   open(auto_file,'w') as autoload :
            autoload.write("<?php\n $data = array( \n ")
            with  open(self.root + "/._find_cls.tmp",'r') as find_cls :
                for line in find_cls.readlines():
                    res =  re.search('(.*\.php):\s*(abstract)?\s*class\s+(\S+)',line)
                    if not res :
                        continue
                    file_path=res.group(1)
                    file_path= "$ROOT.'"+file_path.replace(self.root,'') + "'"
                    autoload.write(Template("\t\t'$CLS' \t\t\t\t=> \t $PATH,\n").substitute(PATH=file_path,CLS=res.group(3)))
            find_cls.close()
            autoload.write("'ok'=>'ok');")
        autoload.close()


class action(resource):
    dst = None
    def __init__(self,ini,src,dst=None):
        self.src = src  
        self.dst = dst
        self.ini = ini
    def locate(self):
        self.src = env_exp.value(self.src)
        if self.dst == None:
            self.dst = self.src 
        self.dst = env_exp.value(self.dst)
        self.ini = env_exp.value(self.ini)

    def index(self):
        path=os.path.dirname(os.path.realpath(__file__))
        shell.execmd(Template('echo "" > $DST/._act_cls.tmp').substitute(DST=self.dst)) 
        cmdtpl1 = 'find $SRC -name "*.php"   |  xargs cat | grep "class Action_"  >> $DST/._act_cls.tmp'
        for  s in self.src.split(':') :
            cmd = Template(cmdtpl1).substitute(PYLON = path + "/../" , SRC =  s ,DST=self.dst )
            shell.execmd(cmd)
        cmdtpl2 = "php -c $INI  $PYLON/pylon/xmvc/build_conf.php  $DST/init.php  $DST/._act_cls.tmp $DST/_act_conf.php"
        cmd = Template(cmdtpl2).substitute(INI= self.ini, PYLON = path + "/../" , DST =  self.dst)
        shell.execmd(cmd)

class pylon_ui(resource):
    def __init__(self,web_inf,theme="brood"):
        self.web_inf = web_inf
        self.theme   = theme
    def locate(self):
        self.web_inf = env_exp.value(self.web_inf)
        self.theme   = env_exp.value(self.theme)

    def config(self):
        path=os.path.dirname(os.path.realpath(__file__))
        cmdtpl = "$PYLON/pylon_ui/setup.sh $SRC/scripts/  $SRC/styles  $SRC/images/  $THEME "
        cmd = Template(cmdtpl).substitute(PYLON = path + "/../" , SRC =  self.web_inf , THEME = self.theme )
        shell.execmd(cmd)


class system( controlor):
    allow_sys=None
    def __init__(self, name, *args):
        self.name = name
        controlor.__init__(self,*args)
    def allow(self):
        allow_sys_arr = system.allow_sys.split(",")
        for name  in  allow_sys_arr :
            name=name.lstrip()
            if name == "all"  or name == "ALL"  or name == self.name :
                return True
        return False


class project:
    root_path = ""
    @staticmethod
    def root():
        if  "PRJ_ROOT"  not in  os.environ:
            raise  Exception("not define PRJ_ROOT env var ")
        return  os.environ["PRJ_ROOT"]
    def use():
        if  "PRJ_USE"  not in  os.environ:
            raise  Exception("not define PRJ_USE env var ")
        return  os.environ["PRJ_USE"]
    def env_tag():
        if  "ENV_TAG"  not in  os.environ:
            raise  Exception("not define ENV_TAG env var ")
        return  os.environ["ENV_TAG"]

class vars(resource):
    defs={}
    def __init__(self, *vars):
        self.defs = vars
    def locate(self):
        for name , val in   self.defs.items():
            name= name.upper()
            val = env_exp.value(val)
            os.environ[name]=val
            if inf.shell.SHOW :
                print( name  + " = " + val)



class cgi_svc(resource):
    spawn_fcgi= "/usr/bin/spawn-fcgi"
    php_cgi   = "/usr/bin/php5-cgi"
    ip        = "127.0.0.1"
    port      = "9000"
    proc_nu   = "2"
    proc_tag  = ""
    php_ini   = ""
    def __init__(self,ini,port="9000",fcgi_nu ="1" ):
        self.php_ini = ini
        self.port = port 
        self.proc_nu= fcgi_nu 
    def config(self):
        self.php_ini.config()
    def locate(self):
        self.php_ini.locate()
        self.port    = env_exp.value(self.port)
        self.proc_nu =  env_exp.value(self.proc_nu)
    def start(self):
        cmdtpl="$SPAWN_FCGI -C $FCGI_NU -a $IP -p $PORT -u nobody -f \"$PHP_CGI  -c $PHP_INI \""
        cmd = Template(cmdtpl).substitute( SPAWN_FCGI=self.spawn_fcgi,
                FCGI_NU=self.proc_nu, IP=self.ip,PORT= self.port, 
                PHP_CGI=self.php_cgi,
                PHP_INI=self.php_ini.path())
        shell.execmd(cmd)
    def stop(self):
        path=os.path.dirname(os.path.realpath(__file__))
        cmdtpl="$PATH/kill_procs.sh $CMD $FILTER"
        filter = self.php_ini.path()
        if not self.proc_tag == "" :
            filter = self.proc_tag 
        cmd = Template(cmdtpl).substitute(PATH=path,CMD=self.php_cgi, FILTER=filter )
        shell.execmd(cmd)


def restart_op(obj):
    obj.call_stop()
    obj.call_start()


class prj(controlor) :
    sys={}
    env={}
    def reg_sys(self,sys):
        self.sys = sys
    def reg_env(self,env):
        self.env = env 
    def run(self,envname,cmd,sysname):
        system.allow_sys = sysname
        execmd = None
        if cmd == "start" :
            execmd = lambda x :  x.call_start() 
        if cmd == "conf" or cmd == "config"  :
            execmd = lambda x :  x.call_config() 
        if cmd == "stop" :
            execmd = lambda x :  x.call_stop() 
        if cmd == "restart" :
            execmd = lambda x :  restart_op(x)

        if cmd == "data" :
            execmd = lambda x :  x.call_data() 

        if cmd == "index" :
            execmd = lambda x :  x.call_index() 
        if execmd == None :
            print("not support this cmd : " + cmd )
            return 

        env_obj = self.env[envname]
        env_obj.call_locate()
        for  r in self.res:
                r.call_locate()
                execmd(r)
        if sysname == "all" or sysname == "ALL" :
            for n,s in self.sys.items():
                s.call_locate()
                execmd(s)
        else:
            sys_obj = self.sys[sysname]
            sys_obj.call_locate()
            execmd(sys_obj)
        return 


