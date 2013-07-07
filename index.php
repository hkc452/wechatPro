<?php
/**
  * wechat php test
  */

//define your token
define("TOKEN", "hkc");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();

class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }

    public function responseMsg()
    {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $fileFolder="StoreXml";
        if (!file_exists($fileFolder)) {
            mkdir($fileFolder);
        }
        $fp=fopen("$fileFolder".DIRECTORY_SEPARATOR.date("YmdHis").".xml", "a+");
        fwrite($fp, $postStr);
        fclose($fp);
      	//extract post data
		if (!empty($postStr)){
                
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $subcribe=$postObj->Event;
				$textHaderTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <Content><![CDATA[]]></Content>
                    <ArticleCount>%d</ArticleCount>
                    <Articles>";
                $textContentTpl = "<item>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    <PicUrl><![CDATA[%s]]></PicUrl>
                    <Url><![CDATA[%s]]></Url>
                    </item>";
                $textFooterTpl = "</Articles>
                    <FuncFlag>1</FuncFlag>
                    </xml>";   
                $noTpl="<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";       
                $cdTpl="<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[image]]></MsgType>
                    <PicUrl><![CDATA[%s]]></PicUrl>
                    <MsgId>1234567890123456</MsgId>
                    </xml>";
				if(!empty( $keyword ))
                {
                    if ($keyword=='大学城') {
                        $cdUrl="http://www.xiaohomehome.com/kc/wechat/images/cd.jpg";
                        // $resultCd=sprintf($cdTpl, $fromUsername, $toUsername, $time,$cdUrl);
                        // echo $resultCd;
                         $resultHaderStr = sprintf($textHaderTpl, $fromUsername, $toUsername, $time, 1);
                         $resultContentStr = sprintf($textContentTpl, '禁基的大学城', '图片来自菜菜xw童鞋', $cdUrl, $cdUrl);
                         $resultFooterStr = sprintf($textFooterTpl);
                         echo $resultStr = $resultHaderStr,$resultContentStr,$resultFooterStr;
                    }else{
                        $array = getWeather($keyword);
                        if (isset($array[0]['title'])) {
                            $resultHaderStr = sprintf($textHaderTpl, $fromUsername, $toUsername, $time, count($array));
                            foreach ($array as $key => $value) {
                               $resultContentStr .= sprintf($textContentTpl, $value['title'], $value['des'], $value['pic'], $value['url']);
                            }
                            $resultFooterStr = sprintf($textFooterTpl);
                            echo $resultStr = $resultHaderStr,$resultContentStr,$resultFooterStr;
                        }else{
                            // $text="呵呵";
                            $key="c1065024-3b1e-46e7-b0bc-7c96354bac96";
                            $url="http://sandbox.api.simsimi.com/request.p?key=$key&lc=ch&ft=1.0&text=$keyword";
                            $file=file_get_contents($url);
                            $obj=json_decode($file);
                            $response=$obj->response;
                            $result=$obj->result;
                            if ($result!=100) $response="对不起，网络异常，请重新输入";
                            $resultCd=sprintf($noTpl, $fromUsername, $toUsername, $time,$response);
                            echo $resultCd;
                        }
                    }
                }else if ($subcribe=='subscribe'){
                         $firstRes=sprintf($noTpl, $fromUsername, $toUsername, $time,"谢谢关注小开助手,回复城市查看天气预报，如：“广州”");
                         echo $firstRes;
                    }else{
                	echo "Input something...";
                }

        }else {
        	echo "";
        	exit;
        }
    }
		
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}
function getWeather($keyword) {
    include 'city.php';
    $code=$weather_code[$keyword];
    $url="http://m.weather.com.cn/data/".$code.".html";
    $file=file_get_contents($url);
    $obj=json_decode($file);
    $weatherinfo = $obj -> weatherinfo;
    $city = $weatherinfo -> city;
    $temp1=$weatherinfo->temp1;
    $temp2=$weatherinfo->temp2;
    $temp3=$weatherinfo->temp3;
    $img1=$weatherinfo->img1;
    $img2=$weatherinfo->img3;
    $img3=$weatherinfo->img5;
    $weather1=$weatherinfo->weather1;
    $weather2=$weatherinfo->weather2;
    $weather3=$weatherinfo->weather3;
    $wind1=$weatherinfo->wind1;
    $wind2=$weatherinfo->wind2;
    $wind3=$weatherinfo->wind3;
    $index=$weatherinfo->index;
    $index_d=$weatherinfo->index_d;
    $date_y=$weatherinfo->date_y;
    $array = array(
            array("title"=>$city,"des"=>"testdes","pic"=>"http://api.itcreating.com/weather/image.jpg"),
            array("title"=>$index_d,"des"=>"testdes"),
            array("title"=>$date_y." ".$temp1." ".$weather1." ".$wind1,"des"=>"testdes","pic"=>"http://api.itcreating.com/weather/images/".$img1.".png"),
            array("title"=>"明天 ".$temp2." ".$weather2." ".$wind2,"des"=>"testdes","pic"=>"http://api.itcreating.com/weather/images/".$img2.".png"),
            array("title"=>"后天 ".$temp3." ".$weather3." ".$wind3,"des"=>"testdes","pic"=>"http://api.itcreating.com/weather/images/".$img3.".png"),
    );
    return $array;
}
?>