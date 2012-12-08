<?php
/*
* 
* Jun 18, 2012
* GBK
* 6:19:48 PM
* Bruce
* php4_json.fun.php
*/  
function jsonEncode($data) {  
    if (2==func_num_args()) {  
        $callee=__FUNCTION__;  
        return json_format_scalar(strval(func_get_arg(1))).":".$callee($data);  
    }  
    is_object($data) && $data=get_object_vars($data);  
    if (is_scalar($data)) { return json_format_scalar($data); }  
    if (empty($data)) { return '[]';}  
    $keys=array_keys($data);  
    if (is_numeric(join('',$keys))) {  
        $data=array_map(__FUNCTION__,$data);  
        return '['.join(',',$data).']';  
    } else {  
        $data=array_map(__FUNCTION__,array_values($data),$keys);  
        return '{'.join(',',$data).'}';  
    }  
}  
function json_format_scalar($value) {  
    if (is_bool($value)) {  
        $value = $value?'true':'false';  
    } else if (is_int($value)) {  
        $value = (int)$value;  
    } else if (is_float($value)) {  
        $value = (float)$value;  
    } else if (is_string($value)) {  
        $value=addcslashes($value,"\n\r\"\/\\");  
        $value='"'.preg_replace_callback('|[^\x00-\x7F]+|','json_utf_slash_callback',$value).'"';  
    } else {  
        $value='null';  
    }  
    return $value;  
}  
function json_utf_slash_callback($data) {  
    if (is_array($data)) {  
        $chars=str_split(iconv("UTF-8","UCS-2",$data[0]),2);  
        $chars=array_map(__FUNCTION__,$chars);  
        return join("",$chars);  
    } else {  
        $char1=dechex(ord($data{0}));  
        $char2=dechex(ord($data{1}));  
        return sprintf("\u%02s%02s",$char1,$char2);  
    }  
}  
function json_utf_slash_strip($data) {  
    if (is_array($data)) {  
        return $data[1].iconv("UCS-2","UTF-8",chr(hexdec($data[2])).chr(hexdec($data[3])));  
    } else {  
        return preg_replace_callback('/(?<!\\\\)((?:\\\\\\\\)*)\\\\u([a-f0-9]{2})([a-f0-9]{2})/i',__FUNCTION__,$data);  
    }  
}  
function jsonDecode($data) {  
    static $strings,$count=0;  
    if (is_string($data)) {  
        $data=trim($data);  
        if ($data{0}!='{' && $data{0}!='[') return json_utf_slash_strip($data);  
        $strings=array();  
        $data=preg_replace_callback('/"([\s\S]*?(?<!\\\\)(?:\\\\\\\\)*)"/',__FUNCTION__,$data);  
        //简单的危险性检测  
        //echo $data;  
        $cleanData=str_ireplace(array('true','false','undefined','null','{','}','[',']',',',':','#'),'',$data);  
        if (!is_numeric($cleanData)) {  
            throw new Exception('Dangerous!The JSONString is dangerous!');  
            return NULL;  
        }  
        $data=str_replace(  
            array('{','[',']','}',':','null'),  
            array('array(','array(',')',')','=>','NULL')  
            ,$data);  
        $data=preg_replace_callback('/#\d+/',__FUNCTION__,$data);  
        //抑制错误,诸如{123###}这样错误的JSON是不能转换成PHP数组的  
        @$data=eval("return $data;");  
        $strings=$count=0;  
        return $data;  
    } elseif (count($data)>1) {//存储字符串  
        $strings[]=json_utf_slash_strip(str_replace(array('$','\\/'),array('\\$','/'),$data[0]));  
        return '#'.($count++);  
    } else {//读取存储的值  
        $index=substr($data[0],1);  
        return $strings[$index];  
    }  
}  