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
      }
     }
}