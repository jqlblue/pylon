import sys,re,os,string , inf

class prompt:
    @staticmethod 
    def recommend(find, keys):
        find_len = len(find)
        wordlen =3 
        if find_len >=13 :
            wordlen=5
        if find_len >=9 :
            wordlen=4
        recommend = []
        beg=0
        for x in range(wordlen-1,find_len,wordlen):
            end=x+1
            pice=find[beg:end]
            if len(pice) < 2:
                    continue;
            recommend  = recommend +  prompt.match(pice,keys)
            beg=end
        return recommend 
    @staticmethod
    def match(find ,keys):
        match=[]
        if len(find) > 0  :
            for key in keys:
                if re.compile(find).search(key):
                    match.append(key)
        return match;


def envval_of_match (match):
    var= str(match.group(1))
    var=var.upper()
    if var in  os.environ :
        var_ext = os.environ[var]
        var_ext = env_exp.value(var_ext)
        return var_ext
    else :
        recommend = prompt.recommend(var, os.environ.keys())
        print( "not find environ [" + var + "], meybe is:  " +  str(recommend))
        return "${" + var  + "}"


def envvars_replace(tpl):
    envexp=re.compile(r'\$\{(\w+)\}')
    try:
        new = envexp.sub(envval_of_match,tpl)
        return new
    except:
        print("tpl:" + tpl ) ;
        raise 


class env_exp:
    @staticmethod
    def value(exp):
        return envvars_replace(exp)



class tpl_builder:
    @staticmethod
    def build(tplfile,dstfile):
        tpl=open(tplfile, 'r')
        dst=open(dstfile, 'w')
        for line in tpl:
            data= env_exp.value(line)
            dst.write(data)

