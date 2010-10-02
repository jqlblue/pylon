import types , re , os , string,sys   
from  res import *

def proj_def(prj_conf):
    ngx_conf_admin  =   nginx_conf(
            env_def( {  "ADMIN_DOMAIN" : "admin.${DOMAIN}", "FASTCGI_PORT":"${PORT_BASE}1" } ),
            "conf",
            "admin_ngx.conf"
            )
    ngx_conf_stage = nginx_conf(
            env_def( {  "FASTCGI_PORT":"${PORT_BASE}2" } ),
            "conf",
            "stage_ngx.conf"
            )

    autoload   = autoload_conf("${PRJ_ROOT}","src","src")
    actconf    = action_conf("${PRJ_ROOT}/conf/used/admin_php.ini","${PRJ_ROOT}/src/","${PRJ_ROOT}/src/apps/admin/")
    uilib      = pylon_ui_lib("${PRJ_ROOT}/src/web_inf","brood")

    include_path  =  ":${PSIONIC_HOME}/src/:${PSIONIC_HOME}/demo/conf/used:"
    php_ini       = conf_tpl(env_def({"SYS_INCLUDE":include_path}),"conf","admin_php.ini")
    run_tmp       = path("${PRJ_ROOT}/tmp/smarty/templates_c");


    admin_sys =    system("admin_sys",
            ngx_conf_admin,
            cgi_svc(cgi_conf(php_ini,"${PORT_BASE}1")),
            host("127.0.0.1","admin.${DOMAIN}")
            )
    stage_sys =  system("stage_sys",
            ngx_conf_stage,
            cgi_svc(cgi_conf(php_ini,"${PORT_BASE}2")),
            host("127.0.0.1","${DOMAIN}")
            )

    main_conf =  conf_tpl(
                    env_def({ "DB_HOST" : "localhost", "DB_USER" : "u_${ENV_TAG}", "DB_NAME" : "db_${ENV_TAG}", "DB_PWD"  : "123" }),
                    "conf","config.php")

    prj_conf.register(
            run_env("dev",
                env_def({ 
                    "DOMAIN"   : "dev.cube.cn", 
                    "DOCROOT"  : "${PSIONIC_HOME}/demo/src/", 
                    "PRJ_ROOT" : "${PSIONIC_HOME}/demo", 
                    "PRJ_USE"  : "dev", 
                    "ENV_TAG"  : "dev_cube",
                    "GATE_PATH" : "${PSIONIC_HOME}/demo/src/apps",
                    "PORT_BASE" : "901"
                    } ),

                mysql("db_${ENV_TAG}","u_${ENV_TAG}","${PSIONIC_HOME}/demo/src/init/create_db.sql"),
                nginx(),
                main_conf,
                admin_sys,
                stage_sys,
                script(env_def({"TEST_VAR":"PSIONIC"}),"${PSIONIC_HOME}/demo/bin/test.sh"),
                run_tmp,
                autoload,
                actconf,
                uilib
                )
            )

    prj_conf.register(
            run_env("test",
                env_def({ 
                    "DOMAIN"   : "test.cube.cn", 
                    "DOCROOT"  : "${PSIONIC_HOME}/demo/src/", 
                    "PRJ_ROOT" : "${PSIONIC_HOME}/demo", 
                    "PRJ_USE"  : "test", 
                    "ENV_TAG"  : "test_cube",
                    "GATE_PATH" : "${PSIONIC_HOME}/demo/src/apps",
                    "PORT_BASE" : "902"
                    } ),

                mysql("db_${ENV_TAG}","u_${ENV_TAG}","${PSIONIC_HOME}/demo/src/init/create_db.sql"),
                nginx(),
                conf_tpl(None,"conf","config.php"),
                admin_sys,
                stage_sys,
                run_tmp,
                autoload,
                actconf,
                uilib
                )
            )
