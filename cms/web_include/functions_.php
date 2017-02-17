<?php
//获取拼音以gbk编码为准
//$pinyin = GetPinyin(stripslashes($catalog_name));
function GetPinyin($str,$ishead=0,$isclose=1)
{
	return SpGetPinyin(utf82gb($str),$ishead,$isclose);
}