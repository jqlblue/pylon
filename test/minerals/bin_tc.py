from unittest import *
import sys
import os 
sys.path.append('minerals/src')
import fcgi
import confutls

class BinTestCase(TestCase):
    def testfcgi(self):
        conf = fcgi.UbuntuFcgiConf()
        conf.cgi_ini = "/etc/php5/cgi/php.ini"
        svc = fcgi.FcgiSvc(conf)
        svc.start()
        svc.stop()
    def testConfUtls(self):
        prjHome=os.environ["PSIONIC_HOME"]
        content="192.168.1.1   my.test.cn"
        conf = confutls.ConfigUtls(prjHome + "/minerals/test/props/hosts")
        conf.replace("testip",content)
        replace_file = prjHome + "/minerals/test/props/need_append_hsots"
        conf.replaceByFile("from file", replace_file)
