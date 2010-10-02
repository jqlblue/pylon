from unittest import *
import sys
import os 
import ConfigParser
sys.path.append('src/probe')
from  version import *
class VersionTest(TestCase):
    def functionTest(self):
        verfile= os.environ['PSIONIC_HOME'] + "/probe/test/props/version.txt"
        ver= Version(verfile)
        ver.upCommit()
        self.assertEquals(ver.info(), "1.0.0.101")
        ver.upBugfix()
        self.assertEquals(ver.info(), "1.0.1.102")
        ver.upFeature()
        self.assertEquals(ver.info(), "1.1.0.103")
        ver.upStruct()
        self.assertEquals(ver.info(), "2.0.0.104")
        print(ver.info())
    def runTest(self):
        verfile= os.environ['PSIONIC_HOME'] + "/probe/test/props/version2.txt"
        ver= Version(verfile)
        argv=["-c"]
        ver.update(Scm(),argv)
