<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/7/26
 * Time: 15:23
 */
namespace App\Http\Controllers;

use App\Model\AvatarModel;
use App\Model\PublishModel;
use App\Model\PublishReplyModel;
use App\Model\UsersModel;
use App\Model\UserSystemModel;
use Illuminate\Http\Request;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

use DB;
class UserController extends CommonController{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }


    public function huanTest(Request $request){

        include 'Easemob.class.php';
        $options['client_id']='YXA6MgLvpACAQCW7tTP9xuwWZA';
        $options['client_secret']='YXA6Afw4irY2YRgPIW8TdIgqmzUVswA';
        $options['org_name']='1169190726083059';
        $options['app_name']='zhiyin-edition-1';
        $h = new \Easemob($options);
        $data = $h->createUser("lisi111","123456");
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }

    /**
     * @param Request $request
     * 用户注册s
     */
    public function register(Request $request){
        $phone = $request->input('phone','');
        $sex = $request->input('sex','');
        $nickname = $request->input('nickname','');
        $avatar = $request->input('avatar','');
        $faceimage = $request->input('faceimage','');
        $birthday = $request->input('birthday','');
        $password = $request->input('password','');
        $password = md5($password.'abc123');


        $UsersModel = new UsersModel();
        $result = $UsersModel->getPhone($phone);

        if(empty($result)){
//            include 'Easemob.class.php';
//            $options['client_id']='YXA6MgLvpACAQCW7tTP9xuwWZA';
//            $options['client_secret']='YXA6Afw4irY2YRgPIW8TdIgqmzUVswA';
//            $options['org_name']='1169190726083059';
//            $options['app_name']='zhiyin-edition-1';
//            $h = new \Easemob($options);
//
//            $return = $h->createUser($phone."zy" ,$password);


            $result1 = $UsersModel->register($phone,$sex,$nickname,$birthday,$password,$faceimage);
            $AvatarModel = new AvatarModel();
            $id = $AvatarModel->addPartData($result1,$avatar);
            $data=array(
                'uuid'=>  $result1,
                'avatar'=>  $avatar,
                'avatarid'=>  $id,

            );
            return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
        }else{
            return json_encode(['status'=>0,'message'=>'该手机号已注册']);
        }

    }

    /**
     * 用户登录
     * @param Request $request
     */
    public function login(Request $request){
        $phone = $request->input('phone','');
        $password = $request->input('password','');
        $password = md5($password.'abc123');
        $UsersModel = new UsersModel();
        $result = $UsersModel->userlogin($phone,$password);
        if(empty($result)){
            return json_encode(['status'=>0,'message'=>'账号或者密码错误']);
        }else{
            $result['token'] = $this->token($result->id);
            return json_encode(['status'=>1,'message'=>'成功','data'=>$result]);
        }

    }


    /**
     * 发送验证码
     * @param Request $request
     */
    public function sendCode(Request $request){
        $phone = $request->input('phone','');
        $type = $request->input('type','');//注册 修改密码 忘记密码 //修改手机号
        if($type == 1){
            $userInfo = DB::table('users')->where('phone',$phone)->first();
            if(!empty($userInfo)){
                return json_encode(['status'=>0,'message'=>'用户已注册']);
            }
        }

        $code = rand('1000','9999');

        //查询用户发送的验证码信息，10分钟之内只能发送三次，24小时只能发送5次
        $wheretime = time()-600;
        $wheretime24 = time()-86400;
        $res = DB::table('vertifycode')->where('phone',$phone)->where('type',$type)->where('addtime','>',$wheretime)->get();
        if(count($res) <3){
            //判断24小时是否大于5次
            $res24 = DB::table('vertifycode')->where('phone',$phone)->where('type',$type)->where('addtime','>',$wheretime24)->get();
            if(count($res24) <5){//可以发送


                $codeReturn =  $this->codeSend($phone,$code);
                if(!empty($codeReturn)){

                    $mess = array(
                        'phone' => $phone,
                        'code' => $code,
                        'type' => $type,
                        'addtime' => time(),
                    );
                    DB::table('vertifycode')->insert($mess);

                    return json_encode(['status'=>1,'message'=>'发送成功','data'=>$code]);
                }else{
                    return json_encode(['status'=>0,'message'=>'发送失败','data'=>'']);
                }


            }else{//不能发送
                return json_encode(['status'=>0,'message'=>'发送次数过多','data'=>'']);
            }
        }else{
            return json_encode(['status'=>0,'message'=>'发送次数过多','data'=>'']);
        }



    }


    public function codeSend($phone,$code){

        AlibabaCloud::accessKeyClient('LTAI4FgXdQHpBrb7RQnSr5re', 'tNfKS2mXC1c264jMEXMOTXVTnHfGN5')
            ->regionId('cn-suzhou') // replace regionId as you need
            ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "default",
                        'PhoneNumbers' => $phone,
                        'SignName' => "吱音",
                        'TemplateCode' => "SMS_173343294",
                        'TemplateParam' => "{\"code\":$code}",
                    ],
                ])
                ->request();
//            print_r($result->toArray());
            $data = $result->toArray();
            if($data['Code'] =='OK'){

                return 1;
            }else{
                return 0;
            }
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }



    }

    function msectime_feiqi() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }
    public function codeSend_feiqi($phone,$type){

        $time = $this->msectime();
        return $time;
        $sig = md5('7f5ec494ef9148e3bb620330dc015e12be57d097abdc4ad1c7fb1f37d6ff6f10'.$time);
        $post_data = array(
            'to' => $phone,
            'accountSid' => '7f5ec494ef9148e3bb620330dc015e12',
            'templateid' => '',//模板ID，和短信内容必传一个
            'smsContent' => '',//短信内容，和模板ID必传一个
            'timestamp' => $time,
            'sig' => $sig,
//            'content' => $code,
        );
        $domain =  'https://openapi.miaodiyun.com/distributor/sendSMS';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $domain);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // 设置请求为post类型
        curl_setopt($ch, CURLOPT_POST, 1);
        // 添加post数据到请求中
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        // 执行post请求，获得回复
        $response= curl_exec($ch);
        curl_close($ch);
        return $response;
        if(!empty($response)){
            $mess=array(
                'phone' =>$phone,
                'code' =>$code,
                'type'=>$type,
                'addtime' =>time(),
            );
            DB::table('vertifycode')->insert($mess);
            return 1;
        }else{
            return 0;
        }
    }
    public function faceDetect(Request $request){
        require_once dirname(dirname(__FILE__))."/AipFace/AipFace.php";
        $faceimage = $request->input('faceimage','');
        $avatar = $request->input('avatar','');
        $uuid = $request->input('uuid','');
        // 你的 APPID AK SK
        $APP_ID = '17061204';
        $API_KEY = 'Vv9CtUvL8fVRUpe29mRc7BM4';
        $SECRET_KEY = 'BSFwIC5kAmYw9qkZNzESB1iroCYlvFsF';

        $client = new \AipFace($APP_ID, $API_KEY, $SECRET_KEY);
        $imageType = 'URL';
        $faceimageReturn = $client->detect($faceimage, $imageType);
        $avatarReturn = $client->detect($avatar, $imageType);//检测是否是人脸

        if($faceimageReturn['error_code'] == 0 && $avatarReturn['error_code'] == 0){//是人脸
            //去对比人脸
            $result = $client->match(array(
                array(
                    'image' => $faceimage,
                    'image_type' => 'URL',
                ),
                array(
                    'image' => $avatar,
                    'image_type' => 'URL',
                ),
            ));
            if($result['error_code'] == 0){
                if($result['result']['score'] >=80){//认证通过
                    $pass = 1;
                }else if($result['result']['score'] <80 && $result['result']['score'] >=30){//待人工审核 显示审核中
                    $pass = 2;
                }else{//审核失败
                    $pass = 0;
                }
                $score = $result['result']['score'];
            }else{
                $pass = 0;
                $score = 0;
            }
            $UsersModel = new UsersModel();
            $UsersModel->updataFaceImage($uuid,$faceimage,$pass,$avatar);

            $AvatarModel = new AvatarModel();
            $AvatarModel->addData($uuid,$avatar,$score,$pass);

            return json_encode(['status'=>1,'message'=>'成功']);
        }else{
            $AvatarModel = new AvatarModel();
            $AvatarModel->addData($uuid,$avatar,0,0);
            return json_encode(['status'=>0,'message'=>'失败']);
        }

    }




    public function userAvatarInfo(Request $request){
        $uuid = $request->input('uuid','');
        $AvatarModel = new AvatarModel();
        $data = $AvatarModel->userAvatarInfo($uuid);

        $UserSystemModel = new UserSystemModel();
        $res = $UserSystemModel->getUserSystemNotRead($uuid);//消息-对话 中的系统小吱栏目 多少未读

        $PublishModel = new PublishModel();

        $message = $PublishModel->getMessageList($uuid);

        $pid = array();
        if(empty($message)){

            $message = 0;
        }else{
            foreach ($message as $k=>$value){
                $pid[] = $value['id'];
            }
            $PublishReplyModel = new PublishReplyModel();
            $message=$PublishReplyModel->getPublishReplyNum($pid);
        }


        $data['system'] = $res;
        $data['message'] = $message;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    /**
     * 添加用户头像
     * @param Request $request
     *
     * 8.28修改逻辑 只将图片入库 不做校验
     */
    public function addUserAvatar(Request $request){
        $uuid = $request->input('uuid','');
        $avatar = $request->input('avatar','');

        $AvatarModel = new AvatarModel();
        $id = $AvatarModel->addPartData($uuid,$avatar);
        $data['id']=$id;
        $data['uuid']=$uuid;
        $data['avatar']=$avatar;
        $data['status']=1;
        $data['score']='';
        $data['addtime']='';
        $data['pass']=2;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);



    }


    public function addUserAvatarAsync(Request $request){
        $uuid = $request->input('uuid','');
        $avatar1 = $request->input('avatar','');
        $avatarid = $request->input('avatarid','');
        $UsersModel = new  UsersModel();
        $userInfo = $UsersModel->userInfo($uuid);
        $faceimage = $userInfo->faceimage;
        $addressData = DB::table('address')->where('id',1)->first();
        $address = $addressData->address;
        $faceimage =  $address.$faceimage;
        $avatar =  $address.$avatar1;

        require_once dirname(dirname(__FILE__))."/AipFace/AipFace.php";
        // 你的 APPID AK SK
        $APP_ID = '17061204';
        $API_KEY = 'Vv9CtUvL8fVRUpe29mRc7BM4';
        $SECRET_KEY = 'BSFwIC5kAmYw9qkZNzESB1iroCYlvFsF';

        $client = new \AipFace($APP_ID, $API_KEY, $SECRET_KEY);
        $imageType = 'URL';
        $faceimageReturn = $client->detect($faceimage, $imageType);
        $avatarReturn = $client->detect($avatar, $imageType);//检测是否是人脸
        $UserSystemModel = new UserSystemModel();
        if($faceimageReturn['error_code'] == 0 && $avatarReturn['error_code'] == 0){//是人脸
            //去对比人脸
            $result = $client->match(array(
                array(
                    'image' => $faceimage,
                    'image_type' => 'URL',
                ),
                array(
                    'image' => $avatar,
                    'image_type' => 'URL',
                ),
            ));
            if($result['error_code'] == 0){
                if($result['result']['score'] >=70){//认证通过
                    $pass = 1;

                    $cont = '恭喜！您上传的头像已认证通过~';
                    $UserSystemModel->addNewsWithAvatar($uuid,$cont,$avatar1);
                    //更新用户的头像
                    $UsersModel->updateAvatar($uuid,$avatar1);
                }else if($result['result']['score'] <70 && $result['result']['score'] >=30){//待人工审核 显示审核中
                    $pass = 2;
                    $cont = '您上传的头像正在认证中，请耐心等待。';
                    $UserSystemModel->addNewsWithAvatar($uuid,$cont,$avatar1);
                }else{//审核失败
                    $pass = 0;
                    $cont = '抱歉，您上传的头像未认证通过，请重新上传本人真实头像。';
                    $UserSystemModel->addNewsWithAvatar($uuid,$cont,$avatar1);
                }
                $score = $result['result']['score'];
            }else{
                $pass = 0;
                $score = 0;
            }

        }else{
            $score=0;
            $pass=0;

            $cont = '抱歉，您上传的头像未认证通过，请重新上传本人真实头像。';
            $UserSystemModel->addNewsWithAvatar($uuid,$cont,$avatar1);
        }
        $AvatarModel = new AvatarModel();
        $AvatarModel->updateData($avatarid,$score,$pass);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 上传头像
     * @param Request $request
     * @return false|string
     */
    public function uploadAvatar(Request $request){
        $file = $request->file('file');
        if ($file && $file->isValid()) {
            // 获取文件相关信息
            $originalName = $file->getClientOriginalName(); // 文件原名
            $ext = $file->getClientOriginalExtension();     // 扩展名
            $realPath = $file->getRealPath();   //临时文件的绝对路径
            $type = $file->getClientMimeType();     // image/jpeg
            // 上传文件
            $filename = time() . uniqid() . '.' . $ext;
            // 使用我们新建的uploads本地存储空间（目录）
            file_put_contents('images/' . $filename, file_get_contents($realPath));
            $data['img'] = '/images/' . $filename;
            return json_encode(['status'=>1,'message'=>'成功','data'=>$data ]);
        }else{
            return json_encode(['status'=>0,'message'=>'失败' ]);
        }
    }



}