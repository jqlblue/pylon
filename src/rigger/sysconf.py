import datetime , shutil ,os ,getopt, sys
from string import Template
class sysconf:
    def __init__(self,filename,commenttag="#"):
        self.conffile=filename 
        self.commenttag=commenttag
    def replace(self,section,content):
        begtpl="$TAG POWER BY PSIONIC---SECTION-[$NAME] BEGIN\n"
        endtpl="$TAG POWER BY PSIONIC---SECTION-[$NAME] END\n"
        datetpl="$TAG DATE: $DATE\n"

        now= datetime.datetime.now().strftime("%Y-%m-%d.%H:%M")
        name = os.path.basename(self.conffile)
        backup =  "/tmp/" +  name + "_" +  now
        shutil.copy(self.conffile,backup)
        beg = Template(begtpl).substitute(TAG=self.commenttag,NAME=section) 
        end = Template(endtpl).substitute(TAG=self.commenttag,NAME=section) 
        timetag = Template(datetpl).substitute(TAG=self.commenttag,DATE=now)
        newfileName = self.conffile + ".new"
        file = open(self.conffile)
        nfile = open(newfileName,"w")
        ispass=False
        for  line in file:
            if line == beg :
                ispass = True
            if not ispass :
                nfile.write(line)
            if line == end :
                ispass = False
        nfile.write(beg )
        nfile.write(timetag)
        if isinstance(content,list) :
            for line in content :
                nfile.write(line)
        else:
            nfile.write(content + "\n")
        nfile.write(end )
        nfile.close()
        file.close()
        shutil.copy(newfileName ,self.conffile)

    def replace_by_file(self,section,contentFile):
        file = open(contentFile)
        content = file.readlines()
        self.replace(section,content)
        file.close()

if __name__ == '__main__':
    opts, args = getopt.getopt(sys.argv[1:], "f:n:c:t:p:", ["conf=","name=","content=","tag=","type="])
    tag="#"
    type="string"
    content=""
    file=None
    name="unknow"
    for o, a in opts:
        if o == "-t":
            tag= a  
        if o == "-c":
            content=a
        if o == "-f":
            file=a
        if o == "-n":
            name=a
        if o == "-p":
            type=a

    if file != None :
        conf = sysconf(file,tag)
        if type == "file":
            conf.replace_by_file(name,content)
        else:
            conf.replace(name,content)
        print( Template(" update  conf [$FILE] over!").substitute(FILE=file))
    else:
        print("None file")
