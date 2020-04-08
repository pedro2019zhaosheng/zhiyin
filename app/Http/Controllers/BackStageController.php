<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/8
 * Time: 13:32
 */
namespace App\Http\Controllers;
use App\Model\AvatarModel;
use App\Model\BlackListModel;
use App\Model\ChatModel;
use App\Model\ComplaintModel;
use App\Model\PublishModel;
use App\Model\PublishReplyModel;
use App\Model\UserFriendsModel;
use App\Model\UserLovePublish;
use App\Model\UsersModel;
use App\Model\UserSystemModel;
use App\Model\WordModel;
use App\Model\WordTypeModel;
use Illuminate\Http\Request;

class BackStageController extends CommonController{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }


    /**
     * 话题类型配置 列表
     * @return false|string
     */
    public function getWordTypeList(){
        $WordTypeModel = new WordTypeModel();
        $data = $WordTypeModel->getWordTypeList();
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }
    /**
     * 话题类型配置 新增
     * @param Request $request
     */

    public function addWordType(Request $request){
        $name = $request->input('name','');
        $WordTypeModel = new WordTypeModel();
        $WordTypeModel->addWordType($name);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 话题类型配置 编辑
     * @param Request $request
     * @return false|string
     */
    public function editWordDetail(Request $request){
        $name = $request->input('name','');
        $id = $request->input('id','');
        $WordTypeModel = new WordTypeModel();
        $WordTypeModel->editWord($name,$id);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 话题配置 新增
     * @param Request $request
     * @return false|string
     */
    public function addWords(Request $request){
        $name = $request->input('name','');
        $wordtype = $request->input('wordtype',1);
        $status = $request->input('status',1);
        $type = $request->input('type',1);
        $WordModel = new WordModel();
        $WordModel->addWord($name,$wordtype,$status,$type);
        return json_encode(['status'=>1,'message'=>'成功']);
    }

    /**
     * 话题配置 编辑
     * @param Request $request
     */
    public function editWord(Request $request){
        $name = $request->input('name','');
        $id = $request->input('id','');
        $type = $request->input('type','');
        $wordtype = $request->input('wordtype','');
        $WordModel = new WordModel();
        $WordModel->editWord($name,$id,$type,$wordtype);

        //同时修改 user_publish 和user_publish_reply 的type和wordtype
        $PublishModel = new  PublishModel();
        $PublishReplyModel = new PublishReplyModel();
        $PublishModel->editWord($id,$type,$wordtype);
        $PublishReplyModel->editWord($id,$type,$wordtype);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 话题配置 全部列表
     * @param Request $request
     * @return false|string
     */
    public function getWordList(Request $request){
        $wordtype = $request->input('wordtype',0);
        $type = $request->input('type',0);
        $content = $request->input('content',"");
        $page = $request->input('page',1);
        $size = $request->input('size',20);
        $WordModel = new WordModel();
        $WordTypeModel = new WordTypeModel();
        $PublishModel = new PublishModel();
        $query = $WordModel->getWordLists($wordtype,$type,$content);
        $all = $query->count();
        $data = $query->orderBy('addtime', 'desc')->skip(($page-1) * $size)
            ->take($size)->get();
        foreach ($data as $k=>$val){
            $num = $PublishModel->getNum($val->id);
            $data[$k]->num = $num;

            $typename = $WordTypeModel->getTypeName($val->wordtype);
            $data[$k]->typename = $typename->name;
            $data[$k]->time = date('Y-m-d H:i:s',$val->addtime);
        }
        $res['list'] = $data;
        $res['all'] = $all;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$res]);
    }


    /**
     * 改变话题内容状态
     * @param Request $request
     * @return false|string
     */
    public function setWordStatus(Request $request){
        $status = $request->input('status','');
        $id = $request->input('id','');
        $WordModel = new WordModel();
        $WordModel->setWordStatus($status,$id);
        return json_encode(['status'=>1,'message'=>'成功']);
    }





    /**
     * 获取举报列表
     * @param Request $request
     * @return false|string
     */
    public function getComplaintList(Request $request){
        $begin = $request->input('begin','');
        $end = $request->input('end','');
        $page = $request->input('page',1);
        $size = $request->input('size',20);
        $content = $request->input('content','');


        $ComplaintModel = new ComplaintModel();
        $UsersModel = new UsersModel();
        $PublishModel = new PublishModel();
        $UserFriendsModel = new UserFriendsModel();
        $res = $ComplaintModel->getComplaintList($begin,$end,$content);
        $all = $res->where('status',1)->count();

        $data = $res->where('status',1)->orderBy('addtime', 'desc')->skip(($page-1) * $size)
            ->take($size)->get()->toArray();

        foreach ($data as $k=>$val){
            $cuser = $UsersModel->getUserData($val['uuid'],$val['upuuid']);
            $data[$k]['uname'] = $cuser['uname'];
            $data[$k]['phone'] = $cuser['phone'];
            $data[$k]['upuname'] = $cuser['upuname'];
            $pInfo = $PublishModel->getPublishInfo($val['upid']);
            $data[$k]['voice'] = $pInfo->voicesrc;
            $data[$k]['isclose'] = $cuser['status'];
            $ret = $UserFriendsModel->isFriend($val['uuid'],$val['upuuid']);
            if(empty($ret)){
                $data[$k]['isFriend'] = 0;
            }else{
                $data[$k]['isFriend'] = 1;
            }

        }

        $return['list'] = $data;
        $return['all'] = $all;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$return]);
    }


    /**
     * 删除音频 表示管理员同意该举报信息
     * @param Request $request
     * @return false|string
     */
    public function delComplaint(Request $request){
        $id = $request->input('id','');
        $upid = $request->input('upid','');
        $upuuid = $request->input('upuuid','');
        $complaint = $request->input('complaint','');
        $PublishModel = new PublishModel();
        $ComplaintModel = new  ComplaintModel();

        $ComplaintModel->delComplaint($id);

        $PublishModel->delComplaint($upid);

        $publishData = $PublishModel->getPublishInfo($upid);
        $UserSystemModel = new UserSystemModel();
        $cont = '您对#'.$publishData->word.'#话题发布的音频包含'.$complaint.'，已被屏蔽。';
        $UserSystemModel->addNews($upuuid,$cont);

        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 用户列表
     * @param Request $request
     * @return false|string
     */
    public function userList(Request $request){

        $begin = $request->input('begin','');
        $end = $request->input('end','');
        $page = $request->input('page',1);
        $size = $request->input('size',20);

        $user = $request->input('user','');

        $UsersModel = new  UsersModel();
        $query = $UsersModel->userList($begin,$end,$user);
        $all = $query->count();
        $data = $query->orderBy('id', 'asc')->skip(($page-1) * $size)
            ->take($size)->get();
        $res['list'] = $data;
        $res['all'] = $all;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$res]);
    }


    /**
     * 用户朋友列表
     * @param Request $request
     */
    public function userFriend(Request $request){
        $uuid = $request->input('uuid','');
        $page = $request->input('page',1);
        $size = $request->input('size',20);
        $status = $request->input('status',0);
//        $user = $request->input('user',0);

        $UserFriendsModel = new UserFriendsModel();
        $UsersModel = new  UsersModel();
        $BlackListModel = new BlackListModel();
        $ChatModel = new ChatModel();
        $query  = $UserFriendsModel->getMyFriendsList($status);
        $data = $query->where('uuid',$uuid)->orderBy('id', 'asc')->skip(($page-1) * $size)
            ->take($size)->get()->toArray();
        foreach ($data as $k=>$val){
            $userInfo = $UsersModel->userInfo($val['fid']);
            $data[$k]['nickname'] = $userInfo->nickname;
            $black = $BlackListModel->ifJoinFriend($val['uuid'],$val['fid']);

            if(!empty($black)){
                $data[$k]['status'] = 2;
            }

            $chat = $ChatModel->ifChat($val['uuid'],$val['fid']);
            if(!empty($chat)){
                $data[$k]['hachat'] = 1;
            }else{
                $data[$k]['hachat'] = 0;
            }
        }
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    /**
     * 用户发布列表
     * @param Request $request
     * @return false|string
     */

    public function userPublishList(Request $request){
        $uuid = $request->input('uuid','');
        $page = $request->input('page',1);
        $size = $request->input('size',20);
        $type = $request->input('type','');
        $begin = $request->input('begin','');
        $end = $request->input('end','');
        $PublishModel = new PublishModel();
        $UserLovePublish = new UserLovePublish();
        $PublishReplyModel = new PublishReplyModel();
        $query = $PublishModel->getUserPublishList($type,$begin,$end);


        $all = $query->where('uuid',$uuid)->count();

        $data = $query->where('uuid',$uuid)->orderBy('addtime', 'desc')->skip(($page-1) * $size)
            ->take($size)->get()->toArray();

        foreach ($data as $k=>$val){
            $num = $UserLovePublish->getLoveNum($val['id']);
            $data[$k]['lovenum'] = $num;
            $replynum = $PublishReplyModel->getReplyNum($val['id']);
            $data[$k]['replynum'] = $replynum;
        }
        $res['list'] = $data;
        $res['all'] = $all;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$res]);
    }


    /**
     * 封禁用户
     * @param Request $request
     * @return false|string
     */
    public function prohibition(Request $request){
        $uuid = $request->input('uuid','');
        $status = $request->input('status',0);
        $UsersModel = new UsersModel();
        $UsersModel->prohibition($uuid,$status);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 音频审核列表 用户发布的音频
     * @param Request $request
     * @return false|string
     */
    public function voiceExamineList(Request $request){
        $page = $request->input('page','');
        $size = $request->input('size',20);
        $wordtype = $request->input('wordtype','');
        $spam = $request->input('spam','');
        $content = $request->input('content','');


        $PublishModel = new PublishModel();
        $query = $PublishModel->voiceExamineList($wordtype,$spam,$content);
        $all = $query->where('status',1)->count();

        $data = $query->where('status',1)->orderBy('addtime', 'desc')->skip(($page-1) * $size)
            ->take($size)->get()->toArray();



        $UsersModel = new UsersModel();

        foreach ($data as $k=>$val){
            $user = $UsersModel->userInfo($val['uuid']);
            $data[$k]['nickname'] = $user->nickname;
        }

        $res['list'] = $data;
        $res['all'] = $all;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$res]);
    }



    /**
     * 音频审核列表 用户回复的音频
     * @param Request $request
     * @return false|string
     */
    public function voiceReplyExamineList(Request $request){
        $page = $request->input('page',1);
        $size = $request->input('size',20);
        $wordtype = $request->input('wordtype','');
        $spam = $request->input('spam','');
        $content = $request->input('content','');


        $PublishModel = new PublishReplyModel();
        $query = $PublishModel->voiceReplyExamineList($wordtype,$spam,$content);
        $all = $query->where('status',1)->count();
        $data = $query->where('status',1)->orderBy('addtime', 'desc')->skip(($page-1) * $size)
            ->take($size)->get()->toArray();

        $UsersModel = new UsersModel();

        foreach ($data as $k=>$val){
            $user = $UsersModel->userInfo($val['uuid']);
            $data[$k]['nickname'] = $user->nickname;
        }


        $res['list'] = $data;
        $res['all'] = $all;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$res]);
    }


    /**
     * 音频审核操作
     * @param Request $request
     * @return false|string
     */
    public function voiceExamine(Request $request){
        $id =   $request->input('id','');
        $type =   $request->input('type','');//1：发布列表用；2：回复列表用
        $spamtype = $request->input('spam','');
        $spamcontent = $request->input('spamreason','');


        if($type == 1){

            $PublishModel = new PublishModel();

            if($spamtype == 1 && $spamcontent == '包含敏感词'){
                $publishData = $PublishModel->getPublishInfo($id);
                $uuid = $publishData->uuid;
                $word = $publishData->word;
                $UserSystemModel = new UserSystemModel();
                $cont = '您对#'.$word.'#话题发布的音频包含敏感词汇，已被屏蔽。';
                $UserSystemModel->addNews($uuid,$cont);
            }

            $PublishModel->voiceExamine($id,$spamtype,$spamcontent);
        }elseif($type == 2){

            $PublishReplyModel = new PublishReplyModel();
            if($spamtype == 1 && $spamcontent == '包含敏感词'){
                $PublishModel = new PublishModel();
                $PublishReplyData = $PublishReplyModel->getPublishReplyInfo($id);
                $publishid = $PublishReplyData->pid;
                $userInfo = $PublishModel->userInfo($publishid);
                $UserSystemModel = new UserSystemModel();
                $uuid = $userInfo->id;
                $cont = '您对'.$userInfo->nickname.'说的话包含敏感词汇，已被屏蔽。';
                $UserSystemModel->addNews($uuid,$cont);
            }


            $PublishReplyModel->voiceExamine($id,$spamtype,$spamcontent);
        }

        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 头像审核列表
     * @param Request $request
     * @return false|string
     */
    public function avatarExamineList(Request $request){
        $page = $request->input('page',1);
        $size = $request->input('size',20);
        $AvatarModel = new AvatarModel();
        $data = $AvatarModel->getAvatarExamineList($page,$size);
        $all = $AvatarModel->getAllNum();
        $UsersModel = new UsersModel();
        foreach ($data as $k=>$val){
            $user = $UsersModel->userInfo($val['uuid']);
            $data[$k]['nickname'] = $user['nickname'];
            $data[$k]['faceimage'] = $user['faceimage'];
        }
        $res['list'] = $data;
        $res['all'] = $all;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$res]);
    }


    /**
     * 头像审核
     * @param Request $request
     * @return false|string
     */
    public function avatarExamine(Request $request){
        $id =   $request->input('id','');
        $status=   $request->input('status','');
        $AvatarModel = new AvatarModel();
        $UsersModel = new UsersModel();
        $UserSystemModel = new UserSystemModel();
        //查询用户和头像信息
        $data = $AvatarModel->getAvatar($id);
        $uuid = $data->uuid;
        $avatar = $data->avatar;


        if($status == 1){//更新用户的头像 1：表示通过

            $cont = '恭喜！您上传的头像已认证通过~';
            $UserSystemModel->addNewsWithAvatar($uuid,$cont,$avatar);
            $UsersModel->updateAvatar($uuid,$avatar);
        }else{
            $cont = '抱歉，您上传的头像未认证通过，请重新上传本人真实头像。';
            $UserSystemModel->addNewsWithAvatar($uuid,$cont,$avatar);
        }

        $AvatarModel->avatarExamine($id,$status);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 后台新增假用户
     * @param Request $request
     * @return false|string
     */
    public function addUser(Request $request){
        $nickname =   $request->input('nickname','');
        $phone =   $request->input('phone','');
        $avatar =   $request->input('avatar','');
        $birthday =   $request->input('birthday','');
        $area =   $request->input('area','');
        $signal =   $request->input('signal','');
        $sex =   $request->input('sex','');

        $UsersModel = new UsersModel();
        $id = $UsersModel->addUser($nickname,$phone,$avatar,$birthday,$area,$signal,$sex);

        $AvatarModel = new AvatarModel();
        $AvatarModel->addData($id,$avatar,100,1);
        return json_encode(['status'=>1,'message'=>'成功']);
    }




}