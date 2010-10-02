import unittest , sys , os 
sys.path.append('test/minerals/')
sys.path.append('test/probe/')
import probe_ts , minerals_ts

if __name__ == '__main__':
    os.environ['MINERALS'] =  os.environ['PSIONIC_HOME'] + "/src/minerals/" 
    suite = unittest.TestSuite()
    suite.addTest(minerals_ts.suite())
    suite.addTest(probe_ts.suite())
    result = unittest.TextTestRunner(verbosity=2).run(suite)

