import types , re , os , string,sys   
from  res import *

def proj_def(prj_conf):
    prj_conf.register(
            run_env("dev",
                env_def({ 
                    "PRJ_TPL"   : "/home/yunyou/devspace/psionic/prj_tpl", 
                    "PRJ_INS"   : "/home/yunyou/devspace/prj_ins" ,
                    "PRJ_INS_ENTRY"   : "/home/yunyou/devspace/prj_ins/src" ,
                    "SYS" : "admin",
                    "DOMAIN":"admin.yy.com"
                    } ),
                copy(env_def({}),"${PRJ_TPL}","${PRJ_INS}"),
                file_tpl(env_def({}),"${PRJ_TPL}/rigger.sh","${PRJ_INS}/rigger.sh"),
                file_tpl(
                    env_def({"CGI_ENTRY":"admin","CGI_PORT":"9001"}),
                    "${PRJ_TPL}/_conf/options/tpl_nginx.conf","${PRJ_INS}/_conf/used/admin_nginx.conf"),

                file_tpl(
                    env_def({"INCLUDE":"${PSIONIC_HOME}/_src/"}),
                    "${PRJ_TPL}/_conf/options/tpl_php.ini","${PRJ_INS}/_conf/options/admin_php.ini"),
                file_tpl(
                    env_def({"INCLUDE":"${PSIONIC_HOME}/src/","DB_NAME":"prj_ins_test","DB_USER":"prj_ins"}),
                    "${PRJ_TPL}/_pylon.py","${PRJ_INS}/_pylon.py")
                )
            )

