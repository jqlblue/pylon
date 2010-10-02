from unittest import *
from zenv_tc  import *
from version_tc import *
def suite():
    suite = TestSuite()
#    suite.addTest(ZenvTestCase("testHowUse"))
    suite.addTest(ZenvTestCase("testBuildFromIni"))
#    suite.addTest(ZenvTestCase("testStdSetup"))
    suite.addTest(ZenvTestCase("testPromot"))
    suite.addTest(VersionTest("functionTest"))
    suite.addTest(VersionTest("runTest"))
    return suite
