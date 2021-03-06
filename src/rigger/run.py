import types , re , os , string ,  getopt , pickle ,res ,yaml , os_env
from  cgi import *


def help():
    print("rigger -e <env>  -p <prj path> -c <cmd> -s <system> [-d ] [ -f <conf>]")
    print("\nenv you defined env \neg:  \t\tdev\n\t\ttest\n\t\tonline")
    print("\n")
    print("\ncmd:  \t\tconfig\n\t\tdata\n\t\tstart\n\t\tstop\n\t\trestart")

class runargs : 
    env =   None
    prj =   None
#    debug = False
    sysname = None
#    cmd     = None
    def check(self):
        if self.prj is None :
            print("no prj_path file")
            return False
        if self.sysname is None:
            print("need system name or  'all'" )
            return False
        return True


def build_prj(conf_path):
    doc = open(conf_path + "/_pylon.yaml","r").read()
    doc = doc.replace("!R","!!python/object:res")
    conf_data = yaml.load(doc)
    prj = conf_data['prj']
    prj.reg_env(conf_data['env'])
    prj.reg_sys(conf_data['sys'])
    return prj 




if __name__ == '__main__':
    sys.path.append(os.path.dirname(os.path.realpath(__file__)))
    opts, args = getopt.getopt(sys.argv[1:], "f:e:p:c:ds:o", ["conf=","env=","prj=","cmd=","debug","sysname=","os="])
    rargs = runargs()  
    conf_file = None
    os_env_file = "_os_env.yaml"
    cmd = None
    for o, a in opts:
        if o == "-f":
            conf_file = a 
        if o == "-p":
            rargs.prj = a  
        if o == "-c":
            cmd = a
        if o == "-d":
            shexec.SHOW = True
        if o == "-e":
            rargs.env = a
        if o == "-s":
            rargs.sysname = a

        if o == "-o":
            os_env_file = a 

    if cmd is None:
        print("no cmd ")
        help()
        exit()

    if cmd == "config" or cmd == "conf" :
        with open(conf_file,'w')  as f: 
            pickle.dump(rargs, f)
    else:
        with open(conf_file,'r')  as f: 
            rargs = pickle.load(f)

    if not rargs.check() :
        help()
        exit()
    os_env_path = rargs.prj + "/" + os_env_file
    if os.path.exists(os_env_path) :
        os_env.conf_admin.init_by_yaml(os_env_path)
    prj = build_prj(rargs.prj)
    prj.run(rargs.env,cmd,rargs.sysname)
