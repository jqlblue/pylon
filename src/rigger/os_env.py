import  string ,   yaml  

class env_conf:
    nginx_conf_path  = "/usr/local/nginx/conf/include"
    nginx_ctrl       = "/usr/local/nginx/bin/nginx"
    apache_ctrl      = "/usr/local/apache2/bin/apachectl"
    apache_conf_path = "/usr/local/apache2/conf/include"
    spawn_fcgi       = "/usr/local/spawn-fcgi/bin/spawn-fcgi"
    php_cgi          = "/usr/local/php/bin/php-cgi"
    php              = "/usr/local/php/bin/php"
    python           = "python"



class conf_admin:
    conf_ins=None
    @staticmethod
    def ins():
        if not conf_admin.conf_ins:
            conf_admin.conf_ins = env_conf()
        return conf_admin.conf_ins
    @staticmethod
    def init_by_yaml(conf_path):
        doc = open(conf_path ,"r").read()
        doc = doc.replace("!OS","!!python/object:os_env")
        conf_admin.conf_ins= yaml.load(doc)

_env_conf = env_conf()

def get_env_conf():
    return conf_admin.ins()
