<?php
interface MaterialSvc 
{
    public function upload();
}

class LocalMaterialSvc implements MaterialSvc
{
    //store in /home/z/image/apollo
    public function upload()
    {
    }
}
?>
