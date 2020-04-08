<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/7/26
 * Time: 13:35
 */
namespace App\Http\Controllers;
use App\Model\AvatarModel;
use App\Model\BlackListModel;
use App\Model\CanSeeHomeModel;
use App\Model\PublishModel;
use App\Model\PublishReplyModel;
use App\Model\UserFriendsModel;
use Illuminate\Http\Request;
use App\Model\UsersModel;
use Validator;
class MineController extends CommonController{

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getPath(Request $request){
        return $request->path();
    }


    /**
     * 我的主页 包含我发布的 我的基本信息
     * @param Request $request
     */
    public function myHome(Request $request){
        $uuid = $request->input('uuid','');
        $UsersModel = new UsersModel();
        $AvatarModel = new AvatarModel();
        $Publish = new PublishModel();
        $PublishReplyModel = new PublishReplyModel();
        $userInfo = $UsersModel->userInfo($uuid);
        $avatar = $AvatarModel->userAvatarInfo($uuid);
        if(empty($avatar['avatar'])){
            $useravatar = '';
        }else{
            $useravatar = $avatar['avatar']->avatar;
        }
        $userInfo->avatar = $useravatar;
        $publishes = $Publish->mypublish($uuid);

        foreach ($publishes as $k=>$val){
            $num = $PublishReplyModel->getReplyNum($val->id);
            $publishes[$k]->replyNum = $num;
        }
        $data['userInfo'] = $userInfo;
        $data['publish'] = $publishes;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);

    }



    /**
     * 我的个人信息 包含昵称 性别 生日 个性签名 等信息
     * @param Request $request
     */
    public function userInfo(Request $request){
        $uuid = $request->input('uuid','');
        $UsersModel = new UsersModel();
        $data = $UsersModel->userInfo($uuid);
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    /**
     * 用户看其他人的主页
     * @param Request $request
     */
    public function othersHome(Request $request){
        $uuid = $request->input('uuid','');
        $othersuuid = $request->input('othersuuid','');

        //获取othersuuid信息
        $UsersModel = new UsersModel();
        $othersUserData = $UsersModel->userInfo($othersuuid);
        $AvatarModel = new AvatarModel();
        $avatar = $AvatarModel->getPassAvatar($othersuuid);
        if(empty($avatar)){
            $othersUserData->avatar = '';
        }else{
            $othersUserData->avatar = $avatar->avatar;
        }
        $data['userInfo'] = $othersUserData;
        if($othersUserData->canseehome == 1){
            //判断uuid是否为好友

            $UserFriendsModel = new UserFriendsModel();
            $isFriend = $UserFriendsModel->isFriend($uuid,$othersuuid);

            $Publish = new PublishModel();
            if(empty($isFriend)){//非好友 ,只能看3条记录
                $friend = 0;
                $publishes = $Publish->mypublishThree($othersuuid);
            }else{
                $friend = 1;
                $publishes = $Publish->mypublishuse($othersuuid);
            }

            $data['publishes'] = $publishes;
            $data['isFriend'] = $friend;
            return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
        }else{
            return json_encode(['status'=>2,'message'=>'该用户的个人主页设置为隐私','data'=>$data]);
        }


    }


    /**
     * 我的好友
     * @param Request $request
     */
    public function myFriends(Request $request){
        $uuid = $request->input('uuid','');
        $UserFriendsModel = new UserFriendsModel();
        $contact = $UserFriendsModel->getMyFriends($uuid);

        foreach ($contact as $k=>$val){
            $contact[$k]['chart'] = $this->_getFirstCharter($val['nickname']);
        }

        $data=[];
        //给所有数组进行A-Z分类
        foreach ($contact as $val) {
            if ( empty( $data[ $val['chart'] ] ) ) {
                $data[$val['chart']]=[];
            }
            $data[$val['chart']][]=$val;
        }
        //按照键名排序
        ksort($data);


        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    /**
     * 加好友，删除好友
     * @param Request $request
     */
    public function addordelFriend(Request $request){
        $uuid = $request->input('uuid','');
        $othersuuid = $request->input('othersuuid','');
        if($uuid == $othersuuid){
            return json_encode(['status'=>0,'message'=>'不能添加自己为好友']);
        }
        $UserFriendsModel = new UserFriendsModel();
        $type = $request->input('type','');//1:加好友；2：删好友
        if($type ==1){
            $UserFriendsModel->addFriends($uuid,$othersuuid);
        }else{
            $UserFriendsModel->delFriends($uuid,$othersuuid);
        }
        return json_encode(['status'=>1,'message'=>'成功']);

    }

    /**
     * 加入黑名单
     * @param Request $request
     */
    public function blackOperate(Request $request){
        $uuid = $request->input('uuid','');
        $othersuuid = $request->input('othersuuid','');
        $type = $request->input('type','');//1:加入黑名单；2：删除黑名单
        $BlackListModel = new BlackListModel();
        $UserFriendsModel = new UserFriendsModel();

        if($type == 1 ){
            $data = $UserFriendsModel->isFriend($uuid,$othersuuid);
            if(!empty($data)){
                $UserFriendsModel->delFriends($uuid,$othersuuid);
            }
            $BlackListModel->addBlacklist($uuid,$othersuuid);
        }else{
            $data = $UserFriendsModel->beforeFriend($uuid,$othersuuid);
            if(!empty($data)){
                $UserFriendsModel->addFriends($uuid,$othersuuid);
            }
            $BlackListModel->delBlacklist($uuid,$othersuuid);
        }

        return json_encode(['status'=>1,'message'=>'成功']);
    }



    /**
     * 我的黑名单列表
     * @param Request $request
     */
    public function blackList(Request $request){
        $uuid = $request->input('uuid','');
        $BlackListModel = new BlackListModel();
        $contact = $BlackListModel->myBlack($uuid);
        foreach ($contact as $k=>$val){
            $contact[$k]['chart'] = $this->_getFirstCharter($val['nickname']);
        }

        $data=[];
        //给所有数组进行A-Z分类
        foreach ($contact as $val) {
            if ( empty( $data[ $val['chart'] ] ) ) {
                $data[$val['chart']]=[];
            }
            $data[$val['chart']][]=$val;
        }
        //按照键名排序
        ksort($data);
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    /**
     *  修改密码 忘记密码
     * @param Request $request
     */
    public function passSet(Request $request){
        $phone = $request->input('phone','');
        $password = $request->input('password','');
        $password = md5($password.'abc123');
        $UsersModel = new UsersModel();
        $res = $UsersModel->getPhone($phone);
        if(empty($res)){
            return json_encode(['status'=>0,'message'=>'手机号不存在']);
        }else{
            $UsersModel->changePass($phone,$password);
        }
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 补全用户地区信息
     * @param Request $request
     * @return false|string
     */
    public function addUserArea(Request $request){
        $uuid = $request->input('uuid','');
        $area = $request->input('area','');
        $UsersModel = new UsersModel();
        $UsersModel->addUserArea($uuid,$area);
        return json_encode(['status'=>1,'message'=>'成功']);
    }

    /**
     * 修改手机号
     * @param Request $request
     */
    public function phoneChange(Request $request){
        $phone = $request->input('phone','');
        $uuid = $request->input('uuid','');
        $UsersModel = new UsersModel();
        $UsersModel->phoneChange($phone,$uuid);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 设置我的主页隐私
     * @param Request $request
     */
    public function setMyPrivacy(Request $request){
        $uuid = $request->input('uuid','');
        $status = $request->input('status','');
        $UsersModel = new UsersModel();
        $result = $UsersModel->setPrivacy($uuid,$status);
        return json_encode(['status'=>1,'message'=>'成功']);
    }

    /**
     * 修改用户信息
     * @param Request $request
     * @return false|string
     */
    public function editUserInfo(Request $request){
        $uuid = $request->input('uuid','');
        $type = $request->input('type','');//1:修改头像；2：修改昵称；3：修改性别；4：修改生日；5：修改个性签名
        $content = $request->input('content','');

        $avatarid = $request->input('avatarid','');//头像id
        $AvatarModel = new AvatarModel();
        $avatar = $AvatarModel->getAvatar($avatarid);
        $avatarstatus = $avatar->pass;
        //查询头像状态
        $user = new UsersModel();
        if($type == 1){
            if($avatarstatus == 1) {
                $user->editUserInfo($uuid,$type,$content);
            }elseif ($avatarstatus == 2){

                $AvatarModel->updateTime($avatarid);
            }
        }else{
            $user->editUserInfo($uuid,$type,$content);
        }

        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 用户头像列表
     * @param Request $request
     * @return false|string
     *
     */
    public function userAvatarList(Request $request){
        $uuid = $request->input('uuid','');
        $AvatarModel = new AvatarModel();
        $data = $AvatarModel->userAvatarList($uuid);
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    /**
     * @param Request $request
     * @return false|string
     * 删除用户头像
     */
    public function delUserAvatar(Request $request){
        $avataraid = $request->input('avataraid','');
        $uuid = $request->input('uuid','');

        $AvatarModel = new AvatarModel();

        $Avatar = $AvatarModel->getAvatar($avataraid);//判断头像是否有效 ，无效可以直接删除 认证中或者认证失败 去查询还有没有认证过的头像 有的话则可以删除 没有不可删除
        if($Avatar->pass == 0){
            $AvatarModel->delUserAvatar($avataraid);
        }else{
            $data = $AvatarModel->getUserAvatar($uuid,$avataraid);

            if(!empty($data) && count($data)>0){
                $AvatarModel->delUserAvatar($avataraid);
            }else{

                return json_encode(['status'=>0,'message'=>'不可删除']);
            }

        }



        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 个人主页删除自己发布的音频
     * @param Request $request
     * @return false|string
     */
    public function delMyPublish(Request $request){
        $id = $request->input('id','');
        $PublishModel = new PublishModel();
        $PublishModel->delMyPublish($id);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 是否是好友
     * @param Request $request
     * @return false|string
     */
    public function isFriend(Request $request){
        $uuid = $request->input('uuid','');
        $otheruuid = $request->input('otheruuid','');
        $UserFriendsModel = new UserFriendsModel();
        $res =  $UserFriendsModel->isFriend($uuid,$otheruuid);
        if(empty($res)){
            $isfriend = 0;
        }else{
            $isfriend = 1;
        }
        $data['isfriend'] = $isfriend;
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }

}