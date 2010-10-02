case $1 in 
    push)
    git push $USER@zlabs.cn:/usr/local/git_store/psionic.git master
    ;;
    pull)
    git pull $USER@zlabs.cn:/usr/local/git_store/psionic.git master
    ;;
esac


