<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/2
 * Time: 11:03
 */
namespace App\Http\Controllers;
use App\Model\UserFriendsModel;
use App\Model\UserLovePublish;
use App\Model\ChatDetailModel;
use App\Model\ChatModel;
use App\Model\ComplaintModel;
use App\Model\PublishModel;
use App\Model\PublishReplyModel;
use App\Model\UserSystemModel;
use App\Model\WordModel;
use Illuminate\Http\Request;
use DB;
class PublishController extends CommonController{

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }



    public function homePage(Request $request){

        $uuid = $request->input('uuid','');
        $PublishModel = new PublishModel();

        if(empty($uuid)){
            $data = $PublishModel->getIndex();
        }else{
            $begin = time();
            $data = $PublishModel->getData([$uuid]);
            $end = time();
        }
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }

    public function userSetLove(Request $request){
        $uuid = $request->input('uuid','');//68
        $puuid = $request->input('puuid','');//71
        $pid = $request->input('pid','');
        $UserLovePublish = new UserLovePublish();
        $result = $UserLovePublish->getInfo($puuid,$uuid);


        if(empty($result) || count($result) == 0){

            $love = 0;


        }else{

            $result1 = $UserLovePublish->getInfo($uuid,$puuid);


            if(empty($result1) ||count($result1)==0){//已经匹配过不再匹配
                $love = 1;
            }else{
                $love = 0;
            }




            //加好友
            $UserFriendsModel = new UserFriendsModel();
            $res = $UserFriendsModel->isFriend($uuid,$puuid);
            if(empty($res)){
                $UserFriendsModel->addFriends($uuid,$puuid);
            }
        }
        $UserLovePublish->addLove($uuid,$puuid,$pid);
        return json_encode(['status'=>1,'message'=>'成功','data'=>$love]);

    }

    /**
     * 用户发布吱音
     * @param Request $request
     * @return false|string
     */
    public function userPublish(Request $request){
        $uuid = $request->input('uuid','');
        $word = $request->input('word','');
        $wordid = $request->input('wordid','');
        $voice = $request->input('voice','');
        $type = $request->input('type','');
        $wordtype = $request->input('wordtype','');
        $voicetime = $request->input('voicetime','');
        $content = $request->input('content','');


        $filevoice = base64_decode($voice);
        $filename = $uuid.time().'.wav';

        $file = dirname(dirname(dirname(dirname(__FILE__)))).'/public/voice/'.$filename;

        if(!file_exists($file)){
            fopen($file, "w");
        }
        file_put_contents($file,$filevoice);
        $addressData = DB::table('address')->where('id',1)->first();
        $address = $addressData->address;
        $filesrc = $address.'/voice/'.$filename;
        $PublishModel = new  PublishModel();
        $pid = $PublishModel->goPublish($uuid,$word,$wordid,$voice,$type,$voicetime,2,$content,$filesrc,$wordtype);

        $data['id'] = $pid;
        $data['file'] = $filesrc;
        $data['word'] = $word;

        return json_encode(['status'=>1,'message'=>'发布成功','data'=>$data]);

    }


    public function userPublishAsync(Request $request){
        $id = $request->input('id','');
        $file = $request->input('file','');
        $word = $request->input('word','');
        $uuid = $request->input('uuid','');

        $PublishModel = new  PublishModel();
        require_once dirname(dirname(__FILE__))."/AipFace/AipSpeech.php";
        $APP_ID = '17061204';
        $API_KEY = 'Vv9CtUvL8fVRUpe29mRc7BM4';
        $SECRET_KEY = 'BSFwIC5kAmYw9qkZNzESB1iroCYlvFsF';
        $client = new \AipSpeech($APP_ID, $API_KEY, $SECRET_KEY);
        $addressData = DB::table('address')->where('id',1)->first();
        $address = $addressData->address;
        $output_url = $address."/voice/".time().'.mp3';
        $arr = explode('/',$file);
        $file_dir = public_path().'/voice/'.end($arr);
        $output_dir = public_path().'/voice/'.time().'.mp3';
        $command = "ffmpeg -i {$file_dir} -f mp3 -acodec libmp3lame -y {$output_dir}";
        exec($command);
        $PublishModel->where('id','=',$id)->update(['mp3_url'=>$output_url]);
        $data = $client->asr(file_get_contents($file), 'wav', 16000, array(
            'dev_pid' => 1536,
        ));

        if($data['err_msg'] == 0){
            $content = implode(',',$data['result']);


            $spam = $this->wordDetect($content);
            $spamreason='';
            if($spam ==1){//审核不通过，给该用户发一条系统消息
                $UserSystemModel = new UserSystemModel();
                $cont = '您对#'.$word.'#话题发布的音频包含敏感词汇，已被屏蔽。';
                $UserSystemModel->addNews($uuid,$cont);
                $spamreason='您的语音内容包含敏感词汇，已屏蔽';
            }

            $PublishModel->updateContent($id,$content,$spam,$spamreason);
        }





        return json_encode(['status'=>1,'message'=>'成功']);


    }




    /**
     * 获取文字滚动页
     * @param Request $request
     */
    public function getWordLists(Request $request){

        $page = $request->input('page',1);
        $size = $request->input('size',10);

        $WordModel = new WordModel();
        $res = $WordModel->getWordList($page,$size);
        $num = $WordModel->getAllNum();
        $data['data'] = $res;
        $data['num'] = $num;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    /**
     * 搜索文案
     * @param Request $request
     * @return false|string
     */
    public function searchWords(Request $request){
        $page = $request->input('page',1);
        $size = $request->input('size',10);
        $name = $request->input('name',10);
        $WordModel = new WordModel();
        $data = $WordModel->getSearchList($page,$size,$name);
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    /**
     * 举报发布的内容
     * @param Request $request
     */
    public function accusate(Request $request){
        $wordid = $request->input('wordid','');
        $complaint = $request->input('complaint','');//举报内容；
        $upuuid = $request->input('upuuid',1);//发布者用户id
        $uuid = $request->input('uuid',1);//举报者id
        $ComplaintModel = new ComplaintModel();
        $ComplaintModel->accusate($wordid,$complaint,$upuuid,$uuid);
        return json_encode(['status'=>1,'message'=>'成功']);

    }

    /**
     * 话题下的用户回应
     * @param Request $request
     */
    public function userReply(Request $request){
        $publishid = $request->input('publishid','');//发布的 话题id
        $uuid = $request->input('uuid','');//回应者id
        $replyvoice = $request->input('replyvoice','');//回应的声音 二进制
        $replytime = $request->input('voicetime','');
//        $content = $request->input('content','');
        $wordid = $request->input('wordid','');
        $word = $request->input('word','');
        $type = $request->input('type','');
        $wordtype = $request->input('wordtype','');

//        if(empty($content)){
//            $spam = 2;
//        }else{
//            $spam = $this->wordDetect($content);
//        }

        $PublishModel = new PublishModel();

//        if($spam ==1){//审核不通过 包含敏感词汇
//
//            $userInfo = $PublishModel->userInfo($publishid);
//            $UserSystemModel = new UserSystemModel();
//            $cont = '您对'.$userInfo->nickname.'说的话包含敏感词汇，已被屏蔽。';
//            $UserSystemModel->addNews($uuid,$cont);
//        }

        $filevoice = base64_decode($replyvoice);
        $filename = $uuid.time().'.wav';
        $file = dirname(dirname(dirname(dirname(__FILE__)))).'/public/voice/'.$filename;
        $addressData = DB::table('address')->where('id',1)->first();
        $address = $addressData->address;
        $filesrc = $address.'/voice/'.$filename;

        if(!file_exists($file)){
            fopen($file, "w");
        }
        file_put_contents($file,$filevoice);


        $PublishReplyModel = new PublishReplyModel();
        $id = $PublishReplyModel->reply($publishid,$uuid,$replyvoice,$replytime,2,$filesrc,$wordid,$word,$type,$wordtype);

        $PublishModel->updateNewsReplyTime($publishid);
        $data['id'] =$id;
        $data['publishid'] =$publishid;
        $data['file'] =$filesrc;

        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    public function userReplyPublishAsync(Request $request){
        $id = $request->input('id','');
        $file = $request->input('file','');
        $publishid = $request->input('publishid','');
        $uuid = $request->input('uuid','');


        require_once dirname(dirname(__FILE__))."/AipFace/AipSpeech.php";
        $APP_ID = '17061204';
        $API_KEY = 'Vv9CtUvL8fVRUpe29mRc7BM4';
        $SECRET_KEY = 'BSFwIC5kAmYw9qkZNzESB1iroCYlvFsF';
        $client = new \AipSpeech($APP_ID, $API_KEY, $SECRET_KEY);
        $data = $client->asr(file_get_contents($file), 'wav', 16000, array(
            'dev_pid' => 1536,
        ));
        $PublishModel = new  PublishModel();
        $PublishReplyModel = new  PublishReplyModel();
        if($data['err_msg'] == 0){
            $content = implode(',',$data['result']);
            $spam = $this->wordDetect($content);
            $PublishReplyModel->updateContent($id,$content,$spam);
            if($spam ==1){//审核不通过，给该用户发一条系统消息
                $userInfo = $PublishModel->userInfo($publishid);
                $UserSystemModel = new UserSystemModel();
                $cont = '您对'.$userInfo->nickname.'说的话包含敏感词汇，已被屏蔽。';
                $UserSystemModel->addNews($uuid,$cont);
            }

        }

        return json_encode(['status'=>1,'message'=>'成功']);

    }

    public function wordDetect($content){


        $post_data['grant_type']= 'client_credentials';
        $post_data['client_id']= 'Vv9CtUvL8fVRUpe29mRc7BM4';
        $post_data['client_secret'] = 'BSFwIC5kAmYw9qkZNzESB1iroCYlvFsF';
        $o = "";
        foreach ( $post_data as $k => $v )
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);
        $postUrl = 'https://aip.baidubce.com/oauth/2.0/token';
        $curlPost = $post_data;
        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($curl, CURLOPT_HEADER, 0);//设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($curl);//运行curl
        curl_close($curl);

        $return = json_decode($data,1);
        $token = $return['access_token'];

        $Url = 'https://aip.baidubce.com/rest/2.0/antispam/v2/spam';
        $p = "";
        $postData['content'] = $content;
        $postData['access_token'] = $token;
        foreach ( $postData as $k => $v )
        {
            $p.= "$k=" . urlencode( $v ). "&" ;
        }
        $postData = substr($p,0,-1);
        $curl_Post = $postData;
        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL,$Url);//抓取指定网页
        curl_setopt($curl, CURLOPT_HEADER, 0);//设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_Post);
        $dataReturn = curl_exec($curl);//运行curl
        curl_close($curl);


        $res = json_decode($dataReturn,1);

        if(count($res) ==3){
            return 2;
        }elseif(count($res) ==2){
            return $res['result']['spam'];//0:审核通过；1：审核不通过；2：审核需复查
        }else{
            return 2;
        }

    }




    //声音二进制流 转成文件 测试
    public function voicetest(){
        $PublishModel = new PublishModel();
        $data = $PublishModel->getPublishInfo(44);
        $voice = $data->voice;
        $temp = base64_decode($voice);

        file_put_contents('E:\phpStudy\PHPTutorial\WWW\zhiyin\test1.wav',$temp);
        return json_encode(['status'=>1,'message'=>'成功']);
    }
}