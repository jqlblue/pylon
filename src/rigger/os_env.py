import  string ,   yaml  

class env_conf:
    nginx_conf_path  = "/etc/nginx/sites-enabled"
    apache_conf_path = "/etc/httpd/conf.d"
    spawn_fcgi       = "/usr/bin/spawn-fcgi"
    php_cgi          = "/usr/bin/php5-cgi"

_env_conf = env_conf()

def get_env_conf():
    return _env_conf

def load_env_conf(conf_path):
    doc = open(conf_path ,"r").read()
    doc = doc.replace("!OS","!!python/object:os_env")
    _env_conf = yaml.load(doc)
