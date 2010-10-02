import re
import os 
import getopt
import sys
from string import Template
class Scm:
    def createTag(self,ver):
        pass
    def commit(self,ver,msg=""):
        pass
    def update(self):
        pass

    def haveChange(self):
        return True

    def haveConflict(self):
        return False
  
class Subversion(Scm):
    MUTI_PRJ="svn cp $URL/$TRUNK/$PRJ  $URL/$TAGS/$PRJ-$VER -m \"$VER\" "
    SIGN_PRJ="svn cp $URL/$TRUNK       $URL/$TAGS/$PRJ-$VER -m \"$VER\" "
    def __init__(self,workpath,url,prj,tagtpl,trunk="trunk",tags="tags",branchs="branchs"):
        self.workpath = workpath
        self.url   = url
        self.trunk = trunk
        self.tags  = tags
        self.branchs = branchs
        self.prj  = prj
        self.tagTPL = tagtpl
    def createTag(self,ver):
        svncp= Template(self.tagTPL)
        cmd = svncp.substitute(URL=self.url , TRUNK=self.trunk ,PRJ=self.prj, TAGS=self.tags , VER=ver)
        print(cmd)
        os.system(cmd)
    def commit(self,ver,msg="no info"):
        svnci= Template("cd $WORKPATH ; svn ci -m \"$VER, $MSG\" ")
        cmd = svnci.substitute(WORKPATH=self.workpath, VER=ver,MSG=msg)
        print(cmd)
        os.system(cmd)
    def update(self):
        svnup= Template("cd $WORKPATH ; svn up ")
        cmd = svnup.substitute(WORKPATH=self.workpath )
        print(cmd)
        os.system(cmd)

    def haveChange(self):
        svnst= Template("cd $WORKPATH ;svn st | grep -E \"^[M|C|A]\" | wc -l ")
        cmd = svnst.substitute(WORKPATH=self.workpath )
        print(cmd)
        cnt=int(os.popen(cmd).read())
        if cnt > 0 :
            return True
        return False

    def haveConflict(self):
        svncf= Template("cd $WORKPATH ;svn st | grep -E \"^C\" | wc -l ")
        cmd = svncf.substitute(WORKPATH=self.workpath )
        print(cmd)
        cnt=int(os.popen(cmd).read())
        if cnt > 0 :
            return True
        return False

class Version:
    first=0
    second=1
    third=0
    forth=0
    def __init__(self,verfile):
        self.verfile=verfile
        file=open(verfile)
        line=file.readline()
        file.close()
        strdata=line.split('.')
        intdata=[]
        for str in strdata:
            intdata.append(int(str))
        self.first,self.second,self.third,self.forth=intdata

    def upCommit(self):
        self.forth += 1
    def upBugfix(self):
        self.forth += 1
        self.third +=1
    def upFeature(self):
        self.forth += 1
        self.second +=1
        self.third  = 0 
    def upStruct(self):
        self.forth += 1
        self.second = 0 
        self.third  = 0 
        self.first += 1

    def  save(self):
        file=open(self.verfile,'w')
        data="%d.%d.%d.%d" %(self.first,self.second,self.third,self.forth)
        file.write(data)
        file.close()
    def info(self):
        return "%d.%d.%d.%d" %(self.first,self.second,self.third,self.forth)

    def promoteCommit(self,scm):
        scm.update()
        if scm.haveConflict():
            print("have conflict, please fix")
            return 
        print("plese chose only commit(c), fixbug(b), add feature(f) , struct revolution(s) or only tag (t) ?")
        recomTag = False
        needCommit = False
        chose = sys.stdin.readline().strip()
        if chose == "c":
            if not  scm.haveChange() :
                print("have no change !")
                return 
            self.upCommit()
            needCommit = True
        if chose == "b":
            self.upBugfix()
            recomTag = True
            needCommit = True
        if chose == "f":
            self.upFeature()
            recomTag = True
            needCommit = True
        if chose == "s":
            self.upStruct()
            recomTag = True
            needCommit = True
        if chose == "t":
            recomTag = True
            needCommit = True
        self.save()
        if needCommit and scm.haveChange() :
            scm.commit(self.info())
        if  recomTag :
            print("Create Tag %s? (y/N)" %(self.info()) ) 
            needTag = sys.stdin.readline().strip()
            if needTag.upper()== "Y" :
                scm.createTag(self.info())


    def update(self,scm,argv):
        opts, args = getopt.getopt(argv, "cbfst", ["commit","bugfix","upfeature","upstruct","tag"])
        scm.update()
        if scm.haveConflict():
            print("have conflict, please fix")
            return 
        recomTag = False
        needCommit = False
        for o, a in opts:
            if o == "-c":
                if not  scm.haveChange() :
                    print("have no change !")
                    return 
                self.upCommit()
                needCommit = True
            if o == "-b":
                self.upBugfix()
                recomTag = True
                needCommit = True
            if o == "-f":
                self.upFeature()
                recomTag = True
                needCommit = True
            if o == "-s":
                self.upStruct()
                recomTag = True
                needCommit = True
            if o == "-t":
                recomTag = True
                needCommit = True
        self.save()
        if needCommit and scm.haveChange() :
            scm.commit(self.info())
        if  recomTag :
            print("Create Tag %s? (y/N)" %(self.info()) ) 
            needTag = sys.stdin.readline().strip()
            if needTag.upper()== "Y" :
                scm.createTag(self.info())

