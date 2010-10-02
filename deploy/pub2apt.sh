shell="cd /home/release/psionic; \
svn up ; \
/home/release/psionic/deploy/debian/deploy.sh make; \
cp -rf /home/release/psionic/deploy/debian/i386/*.deb /home/release/apt-get/dists/gx/main/binary-i386/ ; \
cp -rf /home/release/psionic/deploy/debian/amd64/*.deb /home/release/apt-get/dists/gx/main/binary-amd64/ ; \
/home/z/zlabs_team/build_apt.sh
"
ssh release@apt.bannerlab.cn "$shell"
sudo apt-get update
