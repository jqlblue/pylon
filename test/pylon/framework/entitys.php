<?php

class User extends Entity
{/*{{{*/
    static public function createByBiz($name,$passwd)
    {/*{{{*/
        $obj = new User(EntityID::create());  
        $obj->logname=$name;
        $obj->name=$name;
        $obj->passwd=$passwd;
        return Entity::createByBiz($obj);
    }/*}}}*/
    public function login($passwd)
    {
        return $this->passwd == $passwd;
    }
    public function hashStoreKey()
    {
        return $this->id();
    }
}/*}}}*/
class Book  extends Entity
{/*{{{*/
    static public function createByBiz($name,$author,$price,$summary)
    {/*{{{*/
        $obj = new Book(EntityID::create());  
        $obj->name=$name;
        $obj->author=$author;
        $obj->price=$price;
        $obj->summary=$summary;
        return Entity::createByBiz($obj);
    }/*}}}*/
    public function update($price,$summary)
    {/*{{{*/
        $this->price=$price;
        $this->summary=$summary;
    }/*}}}*/
}/*}}}*/

class Book2  extends Entity
{/*{{{*/
    static public function createByBiz($name,$price,$summary,$author1,$author2)
    {/*{{{*/
        $obj = new Book2(EntityID::create());  
        $obj->name=$name;
        $obj->fstAuthor = $author1;
        $obj->secAuthor = $author2;
        $obj->price=$price;
        $obj->summary=$summary;
        return Entity::createByBiz($obj);
    }/*}}}*/
    public function update($price,$summary)
    {/*{{{*/
        $this->price=$price;
        $this->summary=$summary;
    }/*}}}*/
}/*}}}*/
class Author extends Entity
{/*{{{*/
    static public function createByBiz($name,$birthday,$lang)
    {/*{{{*/
        $obj = new Author(EntityID::create());  
        $obj->name=$name;
        $obj->birthday=$birthday;
        $obj->lang = $lang;
        return Entity::createByBiz($obj);
    }/*}}}*/
    public function update($birthday,$lang)
    {/*{{{*/
        $this->birthday=$birthday;
        $this->lang = $lang;
    }/*}}}*/
}/*}}}*/
class BuyItem  extends Relation
{/*{{{*/
    public function index()
    {/*{{{*/
        return $this->book->index();
    }/*}}}*/
    static public function createByBiz($owner,$book,$count)
    {/*{{{*/
        $obj = new BuyItem();
        $obj->id=EntityUtls::createPureID();
        $obj->owner = $owner;
        $obj->book  = $book;
        $obj->count = $count;
        return $obj;
    }/*}}}*/
}/*}}}*//*}}}*/
class BuyCar extends Entity
{/*{{{*/
    const  ST_INIT=1;
    const  ST_CONFIRM=2; 
    const  ST_DEL=0; 
    public function getRelationSets()
    {/*{{{*/
        return array($this->buyItemSet);
    }/*}}}*/
    public function addBook($book,$count)
    {/*{{{*/
        $this->buyItemSet->regAdd(BuyItem::createByBiz($this->id(),$book,$count));
    }/*}}}*/
    public function removeBook($book,$count)
    {/*{{{*/
        $curCount= $this->buyItemSet->getByObj($book)->count;
        if($curCount > $count)
        {
            $this->buyItemSet->getByObj($book)->count = $curCount-$count;
        }
        elseif($curCount == $count)
        {
            $this->buyItemSet->regDelByIndex($book->index());
        }
        else
        {
            DBC::unExpect($count,"cur book count is $curCount, but you remove $cout");
        }
    }/*}}}*/
    public function bookNames()
    {/*{{{*/
        $names=array();
        foreach ($this->buyItemSet->items() as $item)
        {
            $names[]=$item->book->name;
        }
        return $names;
    }/*}}}*/
    public function confirm()
    {/*{{{*/
        $this->status =BuyCar::ST_CONFIRM;
    }/*}}}*/
    static public function createByBiz($owner)
    {/*{{{*/
        $obj= new BuyCar(EntityID::create());
        $obj->owner=$owner;
        $obj->buyItemSet= ObjectSet::createByBiz('BuyItem');
        $obj->status=BuyCar::ST_INIT;
        return Entity::createByBiz($obj);
    }/*}}}*/
    static public function load($array,$mappingStg)
    {/*{{{*/
        $prop= PropertyObj::create();
        $data= DDA::ins()->list_BuyItem_by_owner($array['id']);
        $prop->buyItemSet = ObjectSet::load('BuyItem', $data);
        return Entity::loadEntity2(__CLASS__,$array,$prop,$mappingStg);
    }/*}}}*/

    public function del() //×Ô¶¨ÒåÉ¾³ý
    {/*{{{*/
        $this->buyItemSet->regAll2Del();
        parent::del();
    }/*}}}*/
}/*}}}*/
?>
