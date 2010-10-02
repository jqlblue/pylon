from unittest import *
import sys
import os 
import ConfigParser
sys.path.append('probe/src')
from domain import *
class ZenvTestCase(TestCase):
    def testHowUse(self):
        websvr = Nginx()
        confs = conf_group("conf","options","builded","zwj")
        confs.addConf("apollo_tpl.conf","apollo.conf",websvr)
        confs.addConf("php_tpl.ini","php.ini")

        myprj = project("/home/zwj/devspace/zenv/")
        myprj.addEnv(confs)
        myprj.addEnv(websvr)

        tasks = myprj.buildTask();
        TaskUtls.stdExecLogging(myprj.root + "/test/build/setupcmds_2.sh");
        TaskUtls.ensureExecTasks(tasks);

    def testBuildFromIni(self):
        config = ConfigParser.ConfigParser()
        prjHome=os.environ["PSIONIC_HOME"]
        os.environ['PROBE']= prjHome + "/probe/src";

        inifile= os.environ['PSIONIC_HOME'] + "/probe/test/props/prjenv.ini"
        conf.owner=os.environ["USER"]
        os.environ["OWNER"]=conf.owner
        config.read(inifile)
        project.initObjsType(config.sections())
        prj = project.createByConf(config,"setup:simple") 
        prj.setup()
        tasks = prj.buildTask()
        TaskUtls.stdExecLogging(prjHome + "/probe/test/cmds.sh");
        TaskUtls.ensureExecTasks(tasks);

    def testStdSetup(self):
        config = ConfigParser.ConfigParser()
        prjHome=os.environ["PSIONIC_HOME"]
        inifile= prjHome + "/test/probe/props/prjenv.ini"
        conf.owner="online"
        os.environ["OWNER"]=conf.owner
        config.read(inifile)
        project.initObjsType(config.sections())
        prj = project.createByConf(config,"setup:std") 
        tasks = prj.buildTask();
        TaskUtls.stdExecLogging(prjHome + "/test/probe/cmds.sh");
        TaskUtls.ensureExecTasks(tasks);

    def testPromot(self):
        find="zwj"
        find2="bbszwj"
        options =["zxx","lovezwj","abcdzwjefg"]
        what= Prompt.recommend(find,options)
        print what
        what= Prompt.recommend(find2,options)
        print what


