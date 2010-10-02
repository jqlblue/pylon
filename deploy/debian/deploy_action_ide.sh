#安装前执行
function pre_install()
{
    echo " === 安装前执行 OK=== "
}

#安装后执行
function post_install()
{
    echo " === 安装后执行 === "
    chmod a+x /home/z/shell/ps-ide/setup.sh
    /home/z/shell/ps-ide/setup.sh
}

#卸载后执行
function  post_rm()
{
    echo " === 卸载后执行 OK === "
}

#卸载前执行
function pre_rm()
{
    echo " === 卸载前执行 OK === "
}
