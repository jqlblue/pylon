<?php
class UploadSvc
{/*{{{*/
    const MAXSIZE = 10240000; //b
    protected $maxSize = UploadSvc::MAXSIZE;
    protected $fileInfo = null;
    protected $safeTypes = null;
    static $fileType = array('image','flash','txt','pkg');
    public function __construct($storePath,$fileDomain,$maxSize=null)
    {/*{{{*/
        $this->storePath  = $storePath;
        $this->fileDomain = $fileDomain;
        if($maxSize)$this->maxSize = $maxSize;
    }/*}}}*/
    static function init($storePath,$fileDomain,$maxSize=null)
    {/*{{{*/
        $up = self::getIns($storePath,$fileDomain,$maxSize);       
        $up->storePath  = $storePath;
        $up->fileDomain = $fileDomain;
    }/*}}}*/
    public function getUpload()
    {/*{{{*/
        $up = self::getIns();
        $types = func_get_args();
        $up->safeTypes = $types;
        return $up;
    }/*}}}*/
    public function upload($file,$defineName=null)
    {/*{{{*/
        $fileInfo = $this->getFileInfo($file);
        $this->fileInfo = $fileInfo;

        if(!$this->checkSize())
            return $this->getErr('sizeError');
        if(!$this->checkType())
            return $this->getErr('typeError');

        if($defineName){
            $newFilePath = $this->storePath . DIRECTORY_SEPARATOR . $defineName;
        }
        else{
            $newPath = $this->makeFilePath();
            if(!$newPath)
                return $this->getErr('pathError');

            $newName = $this->makeNewName();
            $newFilePath = $newPath . DIRECTORY_SEPARATOR . $newName;
        }
        if($this->saveFile($newFilePath))
            return $this->getSucc($newFilePath);
        else
            return $this->getErr('copyError');
    }/*}}}*/
    public function uploadRemoteImg($url,$defineName=null)
    {/*{{{*/
        $tmpFileInfo = @getimagesize($url);
        if(empty($tmpFileInfo))
            return $this->getErr('typeError');
        $filecontent  = file_get_contents($url);
        $tmp_name = '/tmp/'.md5($url);
        file_put_contents($tmp_name,$filecontent);
        $fileInfo['size']     = strlen($filecontent);
        $fileInfo['ext']      = substr(strrchr($url,'.'), 1);    
        $fileInfo['tmp_name'] = $tmp_name;
        $fileInfo['mime']     = $tmpFileInfo['mime'];
        $this->fileInfo = $fileInfo;

        if($defineName){
            $newFilePath = $this->storePath . DIRECTORY_SEPARATOR . $defineName;
        }
        else{
            $newPath = $this->makeFilePath();
            if(!$newPath)
                return $this->getErr('pathError');

            $newName = $this->makeNewName();
            $newFilePath = $newPath . DIRECTORY_SEPARATOR . $newName;
        }
        if($this->saveFile($newFilePath))
            return $this->getSucc($newFilePath);
        else
            return $this->getErr('copyError');
    }/*}}}*/
    static function getIns()
    {/*{{{*/
        static $ins = null;    
        if($ins == null)       
            $ins = new UploadSvc(null,null);
        return $ins;
    }/*}}}*/

/*{{{*/
    protected function getFileInfo($uploadFile)
    {/*{{{*/
        if(!is_array($uploadFile) || empty($uploadFile)) 
            return false;
        $fileInfo['name']     = $uploadFile['name'];
        $fileInfo['size']     = $uploadFile['size'];
        $fileInfo['ext']      = substr(strrchr($uploadFile['name'],'.'), 1);    
        $fileInfo['tmp_name'] = $uploadFile['tmp_name'];
        $fileInfo['mime']     = $uploadFile['type'];
        return $fileInfo;
    }/*}}}*/
    protected function checkSize()
    {/*{{{*/
        $size = $this->fileInfo['size'];
        if($size > $this->maxSize || $size <= 0)
            return false;
        return true;
    }/*}}}*/
    protected function checkType()
    {/*{{{*/
        $mime = $this->fileInfo['mime'];
        $m = MIMEInspect::init($this->safeTypes);
        return $m->checkMIME($mime);
    }/*}}}*/
    protected function makeNewName()
    {/*{{{*/
        $ext = $this->fileInfo['ext'];
        srand((double)microtime()*1000000);
        return rand().".".$ext;
    }/*}}}*/
    protected function makeFilePath()
    {/*{{{*/
        $fileBase = $this->storePath;
        $dateDir  = date("Ymd",time());
        $path = $fileBase.DIRECTORY_SEPARATOR.$dateDir;
        if(UploadUtls::makeDir($path))
            return $path;
        return false;
    }/*}}}*/
    protected function saveFile($filePath)
    {/*{{{*/
        $tmpName = $this->fileInfo['tmp_name'];
        return UploadUtls::saveFile($tmpName,$filePath);
    }/*}}}*/
    protected function getErr($err)
    {/*{{{*/
        @unlink($this->fileInfo['tmp_name']);
        if($err == 'sizeError')
            $msg ='文件大小错误'; 
        if($err == 'typeError')
            $msg ='文件格式错误'; 
        if($err == 'pathError')
            $msg ='生成文件路径错误'; 
        if($err == 'copyError')
            $msg ='复制文件错误'; 
        return array(
            'ST'=>'ERROR',
            'MSG'=>$msg,
            'ERROR'=>$err
            );
    }/*}}}*/
    protected function getSucc($filePath)
    {/*{{{*/
        $url       = UploadUtls::makeFileUrl($this->storePath,$this->fileDomain,$filePath);
        return array(
            'ST'=>'OK',
            'MSG'=>'上传成功',
            'URL'=>$url,
            'ALIAS_URL'=>$aliasUrl,
            'FILEINFO'=>$this->fileInfo,
            'PATH'=>$filePath
        ); 
    }/*}}}*/
/*}}}*/

}/*}}}*/
/*
 * ****  How to use? ****
 * UploadSvc::init($storePath,$fileDomain);
 * $u = UploadSvc::getUpload('image','flash','text');
 * $resInfo = $u->upload($_FILES['image']);
 */

class MIMEInspect
{/*{{{*/
    public $TYPES = array('image','flash','text','pkg');
    public static function init($safeTypes)
    {/*{{{*/
        return new MIMEInspect($safeTypes);
    }/*}}}*/

    public function __construct($safeTypes)
    {/*{{{*/
        $this->safeTypes = $safeTypes;
    }/*}}}*/

    public function checkMIME($mime)
    {/*{{{*/
        $mimes = $this->getAllMime();
        if(array_key_exists($mime,$mimes))
            return true;
        return false;
    }/*}}}*/

    protected function getAllMime()
    {/*{{{*/
        $mimes = array();
        foreach($this->safeTypes as $type) 
        {
            if(in_array($type,$this->TYPES))
            {
                $mimeFunc = $type."MIME";
                $mime = $this->$mimeFunc();
                $mimes = array_merge($mimes, $mime);
            }
        }
        return $mimes;
    }/*}}}*/

    protected function imageMIME()
    {/*{{{*/
        $mimes = array(
            'image/pjpeg'=>"jpg",
            'image/jpeg'=>"jpg",
            'image/jpg'=>"jpg",
            'image/png'=>"png",
            'image/x-png'=>"png",
            'image/gif'=>"gif",
            'image/bmp'=>"bmp"
        );
        return $mimes;
    }/*}}}*/
    protected function flashMIME()
    {/*{{{*/ 
        $mimes = array(
            'application/x-shockwave-flash'=>'swf'
        );
        return $mimes;
    }/*}}}*/
    protected function textMIME()
    {/*{{{*/
        $mimes = array(
            'text/plain'=>'txt'
        );
        return $mimes;
    }/*}}}*/
    protected function pkgMIME()
    {/*{{{*/
        $mimes = array(
            "application/zip" => "zip",
            "application/x-zip" => "zip",
            "application/x-zip-compressed" => "zip", 
            "application/octet-stream" => "zip",
            "application/x-compress" => "zip",
            "application/x-compressed" => "zip",
            "multipart/x-zip" => "zip",
            "application/empty"=>"zip"
            );
        return $mimes;
    }/*}}}*/

}/*}}}*/
/*
 * ****  How to use? ****
 * $u = UploadFileInspect::init($safeTypes = array('image','flash','text'));
 * $u->checkMIME($mime);
 */

class UploadUtls
{/*{{{*/
    static function makeDir($dirPath, $dirMod=0755)
    {/*{{{*/
        if (is_dir($dirPath) || empty($dirPath))
            return true;
        elseif (($dirPath=='/') || (file_exists($dirPath) && !is_dir($dirPath)))
            return false;
        $dirPath = rtrim(str_replace('\\', '/', $dirPath), '/');
        if (self::makeDir(substr($dirPath, 0, strrpos($dirPath, '/'))))
        {
            return @mkdir($dirPath, $dirMod);
        }
        else
            return false;
    }/*}}}*/
    static function makeFileUrl($storePath,$fileDomain,$filePath)
    {/*{{{*/
        $r = str_replace($storePath,$fileDomain,$filePath);    
        return $r;
    }/*}}}*/
    static function saveFile($fileName,$saveToFile)
    {/*{{{*/
        if(!$saveToFile)
        {
            @unlink($fileName);
            return false;
        }
        $r = @copy($fileName, $saveToFile); 
        @unlink($fileName);
        return $r;
    }/*}}}*/
    static function fmtFILES($files)
    {/*{{{*/
        if(is_array($files))
        {
            if(is_array($files['name']))
            {
                $newFiles = array();
                foreach($files as $type => $typedata)
                {
                    foreach($typedata as $key => $value)
                        $newFiles[$key][$type] = $value;
                }
                return $newFiles;
            }
            else
                return $files;
        }
        else
            return false;
    }/*}}}*/
}/*}}}*/
?>
