#!/usr/bin/python2.5
import sys,os 
prjHome = os.environ['PSIONIC_HOME'] 
sys.path.append("%s/probe/src" %(prjHome))
from version import *

print("Please Chose  Probe(b),Pylon(p),Pylon_ui(u),Ide(i),Minerals(m)?")
com=sys.stdin.readline().strip()
verfile=None
prjname=None
subprj=prjHome
if com == "b" :
    verfile= os.environ['PSIONIC_HOME'] + "/probe/src/version.txt"
    prjname="probe"
    subprj=prjHome +"/probe"
elif com == "p" :
    verfile= os.environ['PSIONIC_HOME'] + "/pylon/src/version.txt"
    prjname="pylon"
    subprj=prjHome +"/pylon"
elif com == "i":
    verfile= os.environ['PSIONIC_HOME'] + "/ide/version.txt"
    prjname="ide"
    subprj=prjHome +"/ide"
elif com == "m":
    verfile= os.environ['PSIONIC_HOME'] + "/minerals/src/version.txt"
    prjname="minerals"
    subprj=prjHome +"/minerals"
elif com == "u":
    verfile= os.environ['PSIONIC_HOME'] + "/pylon_ui/version.txt"
    prjname="pylon_ui"
    subprj=prjHome +"/pylon_ui"
else:
    pass 

if verfile == None:
    print("no choise!")
    exit
ver= Version(verfile)
scm=Subversion(subprj,"https://psionic.svn.sourceforge.net/svnroot/psionic",prjname,Subversion.MUTI_PRJ);
ver.promoteCommit(scm)
#ver.update(scm,sys.argv[1:])

