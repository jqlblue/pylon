YAML=yaml-0.1.3
PY_YAML=PyYAML-3.09
if test ! -f $YAML.tar.gz 
then 
    wget http://pyyaml.org/download/libyaml/$YAML.tar.gz
fi
tar -xzf $YAML.tar.gz
cd $YAML
./configure
make
sudo make install

if test ! -f $PY_YAML.tar.gz 
then 
    wget http://pyyaml.org/download/pyyaml/$PY_YAML.tar.gz
fi
tar -xzf $PY_YAML.tar.gz
cd $PY_YAML
python  setup.py  build
sudo python  setup.py install
