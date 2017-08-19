<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
      //获得参数 signature nonce token timestamp echostr
      $nonce          = $_GET['nonce'];
      $token          = 'imooc';
      $timestamp      = $_GET['timestamp'];
      $echostr        = $_GET['echostr'];
      $signature      = $_GET['signature'];
      //形成数组，然后按字典序排序
      $array = array();
      $array = array($nonce, $timestamp, $token);
      sort($array);
      //拼接成字符串，sha1加密，然后与signature进行校验
      $str = sha1( implode( $array ) );
      if( $str == $signature && $echostr ){
        //第一次接入weixin api接口的时候
        echo $echostr;
        exit;
      }else{
        $this->reponseMsg();
      }
    }

    // 接受事件推送并回复
    public function reponseMsg(){
      //1. 获取到微信推送过来的post数据（xml格式)
      $postArr = $GLOBALS['HTPP_RAW_POST_DATA'];
      $tmpstr = $postArr;
      //2.处理消息类型，并设置回复类型和内容
      $postObj = simplexml_load_string( $postArr );
      //判断该数据包是否是订阅的事件推送
      if( strtolower($postObj->MsgType ) == 'event'){
        //如果是关注 subscribe 事件
        if( strtolower($postObj->Event == 'subscribe') ){
          //回复用户消息
          $toUser     = $postObj->FromUserName;
          $fromUser   = $postObj->toUserName;
          $time       = time();
          $MsgType    = 'text';
          $content    = '欢迎关注我们的微信公众账号';
          $template   = "<xml>
                         <ToUserName><![CDATA[%s]]></ToUserName>
                         <FromUserName><![CDATA[%s]]></FromUserName>
                         <CreateTime>%s/CreateTime>
                         <MsgType><![CDATA[%s]]></MsgType>
                         <Content><![CDATA[%s]]></Content>
                         </xml>";
          $info       = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
          echo $info;

        }
      }//if end
     }//resonseMsg end

    //  function http_curl(){
    //    //获取imooc
    //    //1.初始化curl
    //    $ch = curl_init();
    //    $url = 'http://www.baidu.com';
    //    //2.设置curl的参数
    //    curl_setopt($ch, CURLOPT_URL, $url);
    //    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    //    //3.采集
    //    $output = curl_exec($ch);
    //    //4.关闭
    //    curl_close($ch);
    //    var_dump($output);
    //  }

    /**
     * $url 接口url string
     * $type 请求类型 string
     * $res 返回数据类型 string
     * $arr post请求参数 string
     */
    function http_curl($url, $type='get', $res='json', $arr=''){
      //1.初始化curl
      $ch = curl_init();
      //2.设置curl的参数
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
      if($type == 'post' ){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
      }
      //3.采集
      $output = curl_exec($ch);
      //4.关闭
      curl_close($ch);
      if($res=='json'){
        if( curl_errno($ch) ){
          //请求失败，返回错误信息
          return curl_error($ch);
        }else{
          //请求成功
          return json_decode($output, true);
        }
      }
    }

    //  function getWxAccessToken(){
    //    //1.请求url地址
    //    $appid = 'wx29ce29e1eb671505';
    //    $appsecret = '21f2683c879a555c1503e822f47a2a9d';
    //    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
    //    //2.初始化
    //    $ch = curl_init();
    //    //3.设置参数
    //    curl_setopt($ch, CURLOPT_URL, $url);
    //    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //    //4.调用接口
    //    $res = curl_exec($ch);
    //    //5.关闭url
    //    curl_close( $ch );
    //    if( curl_error($ch) ){
    //      var_dump( curl_error($ch) );
    //    }
    //    $arr = json_decode($res, true);
    //    var_dump( $arr );
    //  }

     function getWxServerIp(){
       $accessToken = "DaSKgL9TF3Byq0uthDr3GafHxc94KAdl-Mkb2fFH09g71hcdzU-ieW8JJzz_jdDz_mQM9jACvO6mAv0fXho3ZK4ChCeX-AvbhjilBq67p_IHPVaAAASAG";
       $url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=".$accessToken;
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       $res = curl_exec($ch);
       curl_close($ch);
       if(curl_error($ch)){
         var_dump(curl_error($ch));
       }
       $arr = json_decode($res, true);
       echo "<pre>";
       var_dump( $arr );
       echo "</pre>";
     }

     //返回access_token *session解决方法 存mysql memcache
     public function getWxAccessToken(){
       //将access_token 存在seession/cookie中
       if( $_SESSION['access_token'] && $_SESSION['expire_time']>time()){
         //如果access_token在session并没有过期
         return $_SESSION['access_token'];
       }else{
         //如果access_token不存在或者已经过期，重新取access_token
         $appid = 'wx29ce29e1eb671505';
         $appsecret = '21f2683c879a555c1503e822f47a2a9d';
         $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
         $res = $this->http_curl($url, 'get', 'json');
         $access_token = $res['access_token'];
         //将重新获取到的access_token存到session
         $_SESSION['access_token'] = $access_token;
         $_SESSION['expire_time'] = time()+7000;

         return $access_token;
       }
     }

     public function definedItem(){
       //创建微信菜单
       //目前微信接口的调用方式都是通过curl post/get
       echo $access_token = $this->getWxAccessToken();
       echo '<br />';
       $url = " https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
       $postArr = array(
         'button'=>array(
           array(
             'name'=>urlencode('菜单一'),
             'type'=>'click',
             'key'=>'item ',
           ), //第一个一级菜单
           array(
             'name'=>urlencode('菜单二'),
             'sub_button'=>array(
               array(
                 'name'=>urlencode('歌曲'),
                 'type'=>'click',
                 'key'=>'songs',
               ),//第一个二级菜单
               array(
                 'name'=>urlencode('电影'),
                 'type'=>'view',
                 'url'=>'http://www.baidu.com',
               ),//第二个二级菜单
             ),
           ), //第二个一级菜单
           array(
             'name'=>urlencode('菜单三'),
             'type'=>'view',
             'url'=>'http://www.qq.com'
           )//第三个一级菜单
         ),
       );
       echo '<hr />';
       var_dump($postArr);
       echo '<hr />';
       echo $postJson = urldecode( json_encode( $postArr ) );
       $res = $this->http_curl($url, 'post', 'json', $postJson);
       echo "<hr />";
       var_dump($res);
     }
}//class end
