import types , re , os , string, sys ,  yaml
from  res import *

def proj_def():
    doc = open("_conf/_pylon.yaml","r").read()
    doc = doc.replace("!R","!!python/object:res")
    conf_data = yaml.load(doc)
    prj = conf_data['prj']
    prj.reg_env(conf_data['env'])
    prj.reg_sys(conf_data['sys'])
    print(conf_data['sys']['admin'].res[1].sudo)
    return prj
