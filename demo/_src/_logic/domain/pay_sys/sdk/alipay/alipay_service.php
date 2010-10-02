<?php
/**
¡¡* ÀàÃû alipay_service
¡¡* ¹¦ÄÜ  Ö§¸¶±¦Íâ²¿·þÎñ½Ó¿Ú¿ØÖÆ
¡¡* °æ±¾  0.6
¡¡* ÈÕÆÚ  2006-6-10
¡¡* ×÷Õß   http://www.buybay.org
  * ÁªÏµ   Email£º raftcham@hotmail.com  Homepage£ºhttp://www.buybay.org
¡¡* °æÈ¨   Copyright2006 Buybay NetTech
¡¡*/

class alipay_service {

	var $gateway = "https://www.alipay.com/cooperate/gateway.do?";         //Ö§¸¶½Ó¿Ú
	var $parameter;       //È«²¿ÐèÒª´«µÝµÄ²ÎÊý
	var $security_code;  	//°²È«Ð£ÑéÂë
	var $mysign;             //Ç©Ãû

	//¹¹ÔìÖ§¸¶±¦Íâ²¿·þÎñ½Ó¿Ú¿ØÖÆ
	function alipay_service($parameter,$security_code,$sign_type = "MD5",$transport= "https") {
		$this->parameter      = $this->para_filter($parameter);
		$this->security_code  = $security_code;
		$this->sign_type      = $sign_type;
		$this->mysign         = '';
		$this->transport      = $transport;
		if($parameter['_input_charset'] == "")
		$this->parameter['_input_charset']='GBK';
		if($this->transport == "https") {
			$this->gateway = "https://www.alipay.com/cooperate/gateway.do?";
		} else $this->gateway = "http://www.alipay.com/cooperate/gateway.do?";
		$sort_array = array();
		$arg = "";
		$sort_array = $this->arg_sort($this->parameter);
		while (list ($key, $val) = each ($sort_array)) {
			$arg.=$key."=".$this->charset_encode($val,$this->parameter['_input_charset'])."&";
		}
		$prestr = substr($arg,0,count($arg)-2);  //È¥µô×îºóÒ»¸öÎÊºÅ
		$this->mysign = $this->sign($prestr.$this->security_code);
	}


	function create_url() {
		$url = $this->gateway;
		$sort_array = array();
		$arg = "";
		$sort_array = $this->arg_sort($this->parameter);
		while (list ($key, $val) = each ($sort_array)) {
			$arg.=$key."=".urlencode($this->charset_encode($val,$this->parameter['_input_charset']))."&";
		}
		$url.= $arg."sign=" .$this->mysign ."&sign_type=".$this->sign_type;
		return $url;

	}

	function arg_sort($array) {
		ksort($array);
		reset($array);
		return $array;

	}

	function sign($prestr) {
		$mysign = "";
		if($this->sign_type == 'MD5') {
			$mysign = md5($prestr);
		}elseif($this->sign_type =='DSA') {
			//DSA Ç©Ãû·½·¨´ýºóÐø¿ª·¢
			die("DSA Ç©Ãû·½·¨´ýºóÐø¿ª·¢£¬ÇëÏÈÊ¹ÓÃMD5Ç©Ãû·½Ê½");
		}else {
			die("Ö§¸¶±¦ÔÝ²»Ö§³Ö".$this->sign_type."ÀàÐÍµÄÇ©Ãû·½Ê½");
		}
		return $mysign;

	}
	function para_filter($parameter) { //³ýÈ¥Êý×éÖÐµÄ¿ÕÖµºÍÇ©ÃûÄ£Ê½
		$para = array();
		while (list ($key, $val) = each ($parameter)) {
			if($key == "sign" || $key == "sign_type" || $val == "")continue;
			else	$para[$key] = $parameter[$key];

		}
		return $para;
	}
	//ÊµÏÖ¶àÖÖ×Ö·û±àÂë·½Ê½
	function charset_encode($input,$_output_charset ,$_input_charset ="GBK" ) {
		$output = "";
		if(!isset($_output_charset) )$_output_charset  = $this->parameter['_input_charset '];
		if($_input_charset == $_output_charset || $input ==null) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")){
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset change.");
		return $output;
	}
	

}


?>
