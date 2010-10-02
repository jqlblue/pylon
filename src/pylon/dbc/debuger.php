<?php
/// 
/// @file foundation/debug/Debuger.php
/// @brief 
/// @author zwj
/// @date 2004-10-15
/// 

define ('DEBUG_ENABLE',1);
define ('DEBUG_DISABLE',2);
define ('DEBUG_ACCREDIT',3);
	
/// 
/// @brief 
/// 
class PageDebuger
{/*{{{*/
	var $level;
	var $reflist;
	/// 
	/// @brief 
	/// 
	/// @param $level 
	/// 
	/// @return 
	/// 
	function PageDebuger($level)
	{/*{{{*/
		$this->level = $level;

		if($this->level == DEBUG_DISABLE) 
			return;
		$this->reflist = array();	
	}/*}}}*/
	/// 
	/// @brief 
	/// 
	/// @param &$regvalue 
	/// @param $valuename 
	/// 
	/// @return 
	/// 
	function register( &$regvalue, $valuename = "" )
	{/*{{{*/
		if($this->level == DEBUG_DISABLE) 
			return;

		if( $valuename == "" )
			$this->reflist[]= &$regvalue;
		else
			$this->reflist[$valuename] = &$regvalue;
	}/*}}}*/
	/// 
	/// @brief 
	/// 
	/// @param $value 
	/// @param $name 
	/// 
	/// @return void 
	/// 
	function curValue($value, $name="")
	{
		if($this->level == DEBUG_DISABLE) 
			return;
			
		echo "<pre>";
		//var_dump($value);
		echo "</pre>";
		/*
		if(is_array($value))
		{
			echo '<br>'.$name.':<br>';
			foreach($value as $key=> $item)
			{
				echo $key.' = ['.$item.']'."<br>";            
			}
		}
		else
		{
			echo $name.' = ['.$value.']'."<br>";            
		}
		*/
	}
	/// 
	/// @brief 
	/// 
	/// @return 
	/// 
	function showValues()
	{/*{{{*/
		if($this->level == DEBUG_DISABLE) 
			return;
	
		foreach($this->reflist as $key=>$value)
		{
			$this->curValue($value, $key);
		}
	}/*}}}*/

	/// 
	/// @brief 
	/// 
	/// @return 
	/// 
	function clear()
	{
		if($this->level == DEBUG_DISABLE) 
			return;
			
		unset($this->reflist);
		$this->reflist = array();
	}
		
	/// 
	/// @brief 
	/// 
	/// @param $msg 
	/// 
	/// @return 
	/// 
	function showMessage($msg)
	{
		if($this->level == DEBUG_DISABLE) 
			return;
			
		echo "<br>msg:$msg<br>";
	}
	/// 
	/// @brief 
	/// 
	/// @return ture or false 
	/// 
	function isDebug()
	{
		return $this->level == DEBUG_ENABLE; 
	}
}/*}}}*/

/// 
/// @brief 
/// 
class DebugerManager
{/*{{{*/
	var $level;
	var $count;
	/// 
	/// @brief 
	/// 
	/// @return 
	/// 
	function DebugerManager()
	{
		$this->count = 0;
		$this->level = DEBUG_ACCREDIT;
		//$this->level = DEBUG_DISABLE;
	}
	/// 
	/// @brief 
	/// 
	/// @return DebugerManager instance 
	/// 
	function &  instance()
	{/*{{{*/
		static $inst; 
		//if(array_key_exists('DEBUG',$_ENV))
		if($inst == null)
		{
			$inst = new DebugerManager();
		}
		return $inst;
	}/*}}}*/

	/// 
	/// @brief 
	/// 
	/// @return PageDebuger instance; 
	/// 
	function & defultDebuger()
	{
        $debuger = new PageDebuger($this->level);
		return $debuger; 
	}
	/// 
	/// @brief 
	/// 
	/// @param $level 
	/// 
	/// @return  PageDebuger instance;
	/// 
	function & levelDebuger($level)
	{/*{{{*/

        $debuger = null;
		if($this->level == DEBUG_ACCREDIT)
		{
            $debuger = new PageDebuger($level);
		}
		else
		{
            $debuger = &$this->defultDebuger();
		}
		return $debuger; 
	}/*}}}*/

	/// 
	/// @brief 
	/// 
	/// @param $level 
	/// 
	/// @return void
	/// 
	function setLevel($level)
	{
		$this->level = $level;
	}
	function selfAdd()
	{
		$this->count++;	
		return $this->count;
	}
}/*}}}*/

?>
