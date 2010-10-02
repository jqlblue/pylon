from unittest import *
from  bin_tc  import *
from version_tc import *
def suite():
    suite = TestSuite()
#    suite.addTest(BinTestCase("testfcgi"))
    suite.addTest(BinTestCase("testConfUtls"))
    return suite
