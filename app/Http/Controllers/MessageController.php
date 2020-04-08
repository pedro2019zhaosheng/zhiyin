<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/8
 * Time: 15:31
 */
namespace App\Http\Controllers;
use App\Model\ChatDetailModel;
use App\Model\ChatModel;
use App\Model\PublishModel;
use App\Model\PublishReplyModel;
use App\Model\UserFriendsModel;
use App\Model\UsersModel;
use App\Model\UserSystemModel;
use Illuminate\Http\Request;

class MessageController extends CommonController{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }


    /**
     * 获取消息-回应列表
     * @param Request $request
     *
     */
    public function getMessageList(Request $request){
        $uuid = $request->input('uuid','');
        $PublishModel = new PublishModel();
        $PublishReplyModel = new PublishReplyModel();
        $data = $PublishModel->getMessageList($uuid);
        foreach ($data as $k=>$value){
            $ret = $PublishReplyModel->getReplyNotReadNum($value['id']);
            $data[$k]['notreadnum'] = $ret;
            $publishTime = $this->getDetailTime($value['addtime']);
            $data[$k]['publishTime'] = $publishTime;
        }
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }






    /**
     * 获取发布信息的回复列表
     * @param Request $request
     */
    public function getMessageReplyList(Request $request){
        $pid = $request->input('pid','');
        $uuid = $request->input('uuid','');
        $PublishReplyModel = new PublishReplyModel();
        $UserFriendsModel = new UserFriendsModel();


        $data = $PublishReplyModel->getMessageReplyList($pid);
        foreach ($data as $k=>$val){
            $return = $UserFriendsModel->isFriend($uuid,$val['uuid']);
            if(empty($return)){
                $data[$k]['isFriend'] = 0;
            }else{
                $data[$k]['isFriend'] = 1;
            }
        }
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    /**
     * 将用户的回复信息设置为 已听过
     * @param Request $request
     * @return false|string
     */
    public function setReplyReaded(Request $request){
        $pid = $request->input('pid','');
        $PublishReplyModel = new PublishReplyModel();
        $PublishReplyModel->setReplyReaded($pid);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 消息-对话 中的系统小吱栏目 相关内容  包含 全部聊天内容，最近一条内容，未读数，三类信息
     * @param Request $request
     */
    public function getChatList(Request $request){
        $uuid = $request->input('uuid','');
        $ChatModel = new ChatModel();
        $data = $ChatModel->getChatList($uuid);
        foreach ($data as $k=>$val){
            $time = $this->getDayTime($val->addtime);
            $data[$k]->detailTime = $time;
        }
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }





    /**
     * 用户和每个人的聊天详情
     * @param Request $request
     */
    public function getEachChatList(Request $request){
        $uuid = $request->input('uuid','');
        $chatuuid = $request->input('chatuuid','');
        $ChatDetailModel = new ChatDetailModel();
        $data = $ChatDetailModel->getEachChat($uuid,$chatuuid);
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }

    /**
     * 删除聊天
     * @param Request $request
     */
    public function delChat(Request $request){
        $chatid = $request->input('chatid','');
        $ChatDetailModel = new ChatDetailModel();
        $ChatDetailModel->delChat($chatid);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    //没用
    public function setChatReaded(Request $request){

        $chatid = $request->input('chatid','');
        $ChatDetailModel = new ChatDetailModel();
        $ChatDetailModel->setChatReaded($chatid);
        return json_encode(['status'=>1,'message'=>'成功']);

    }


    /**
     * 批量获取用户信息
     * @param Request $request
     * @return false|string
     */
    public function getGroupUserData(Request $request){
        $usergroup = $request->input('usergroup','');
        $usergroup = explode(',',$usergroup);
        $UsersModel = new UsersModel();
        $data = $UsersModel->getGroupUserData($usergroup);
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    /**
     * 消息-对话 中的系统小吱栏目 相关内容  包含 全部聊天内容，最近一条内容，未读数，三类信息
     * @param Request $request
     * @return false|string
     */

    public function getUserSystemData(Request $request){
        $uuid = $request->input('uuid','');

        $UserSystemModel = new UserSystemModel();
        $res = $UserSystemModel->getUserSystemData($uuid);

        if(empty($res['data'])){
            $data['newest'] = '';
        }else{
            $len = count($res['data']);
            $data['newest'] = $res['data'][$len-1];
        }

        $data['list'] = $res['data'];
        $data['notRead'] = $res['notread'];

        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }


    /**
     * 用户对系统小吱说的话
     * @param Request $request
     */
    public function userChatSystem(Request $request){
        $uuid = $request->input('uuid','');
        $word = $request->input('word','');

        $UserSystemModel = new UserSystemModel();
        $UserSystemModel->userChatSystem($uuid,$word);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 消息-对话 将系统消息中所有消息设置为已读
     * @param Request $request
     * @return false|string
     */
    public function setSystemNewsReaded(Request $request){
        $uuid = $request->input('uuid','');
        $UserSystemModel = new UserSystemModel();
        $UserSystemModel->setSystemNewsReaded($uuid);
        return json_encode(['status'=>1,'message'=>'成功']);
    }


    /**
     * 记录用户的聊天详情
     * @param Request $request
     * @return false|string
     */
    public function recordChatDetail(Request $request){
        $sender = $request->input('sender','');
        $receiver = $request->input('receiver','');
        $chatType = $request->input('chatType','');
        $content = $request->input('content','');
        $ChatDetailModel = new ChatDetailModel();
        $ChatDetailModel->addChatRecord($sender,$receiver,$chatType,$content);

        return json_encode(['status'=>1,'message'=>'成功']);
    }
}
