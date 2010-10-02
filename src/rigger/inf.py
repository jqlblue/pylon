from string import Template
from utls  import * 
import types , re , os , string   

class shell:
    SHOW = False
    DO   = True
    SUDO = False
    @staticmethod
    def debug():
        shell.SHOW = True
        shell.DO= False

    @staticmethod 
    def out2txt(cmd,txt):
        f = open(txt,"w")
        f.write(cmd)
        f.close()

    @staticmethod 
    def sudo_enable():
        shell.SUDO= True

    @staticmethod 
    def sudo_disable():
        shell.SUDO= False 


    @staticmethod
    def execmd(cmd):
        cmd_txt = "/tmp/pylon_rigger_cmd.sh" 
        if shell.SHOW  :
            print( cmd  + "\n")
        if shell.DO  :
            shell.out2txt(cmd,cmd_txt)
            os.system("chmod +x " +  cmd_txt)
            if shell.SUDO  :
                return os.system("sudo " +  cmd_txt)
            else:
                return os.system( cmd_txt)
        else :
            return 0 
        return 1

class scope_sudo:
    def __enter__(self):
        shell.sudo_enable()
    def __exit__(self,exc_type,exc_value,traceback):
        shell.sudo_disable()


class resource :
    sudo = False
    def call_impl(self,cmd):
        if self.sudo : 
            with scope_sudo():
                cmd()
        else:
            cmd()

    def call_start(self):
        self.call_impl(self.start)
    def call_stop(self):
        self.call_impl(self.stop)
    def call_config(self):
        self.call_impl(self.config)
    def call_data(self):
        self.call_impl(self.data)
    def call_locate(self):
        self.call_impl(self.locate)
    def call_index(self):
        self.call_impl(self.index)
    def start(self):
        pass
    def stop(self):
        pass
    def config(self):
        pass
    def locate(self):
        pass
    def data(self):
        pass
    def index(self):
        pass


class controlor(resource):
    res=[]
    def __init__(self, *res):
        self.res = res
        pass
    def start(self):
        if self.allow():
            for r in self.res :
                r.call_start()

    def stop(self):
        if self.allow():
            for r in self.res :
                r.call_stop()

    def config(self):
        if self.allow():
            for r in self.res :
                r.call_config()
    def locate(self):
        if self.allow():
            for r in self.res :
                r.call_locate()
    def data(self):
        if self.allow():
            for r in self.res :
                r.call_data()
    def allow(self):
        return True


def dist_value_of(dist,key,default=None):
    if dist.has_key(key) :
        return  dist[key]
    return default 


class resouce_factory:
    builders ={}
    def register(self,res_type,builder):
        self.builders[res_type] = builder 
    def build(self,res_type, data):
        if self.builders.has_key(res_type) :
            builder = self.builders[res_type]
            return builder(data)
        return None

res_admin = resouce_factory()



class run_env(controlor):
    def __init__(self, name, *args):
        self.name = name
        controlor.__init__(self,*args)


def __init__():
    res_admin.register("system",system.load)
    pass
