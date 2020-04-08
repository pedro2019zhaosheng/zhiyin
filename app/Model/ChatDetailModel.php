<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/12
 * Time: 17:35
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class ChatDetailModel extends Model{
    protected $connection = 'mysql';
    protected $table = 'chats_detail';

    public $timestamps = false;

    public function getEachChat($uuid,$chatuuid){
        $result = ChatDetailModel::join('users',function ($join){
            $join->on('users.id','chats_detail.chatuuid');
        })
            ->where(function ($query) use ($uuid,$chatuuid){
                $query->where('chats_detail.uuid',$uuid)->where('chats_detail.chatuuid',$chatuuid)->where('chats_detail.status',1);
            })
            ->orwhere(function ($quert) use ($uuid,$chatuuid){
                $quert->where('chats_detail.uuid',$chatuuid)->where('chats_detail.chatuuid',$uuid)->where('chats_detail.status',1);
            })
            ->select('chats_detail.uuid','chats_detail.id','chats_detail.chatuuid','chats_detail.hascheck','chats_detail.chatType','chats_detail.content','users.nickname','users.avatar','chats_detail.addtime')
            ->orderBy('chats_detail.addtime','desc')->get();
        return $result;
    }



    public function delChat($chatid){
        $update = array(
          'status'=>0
        );
        $this->whereIn('id',$chatid)->update($update);
    }

    public function setChatReaded($chatid){
        $update = array(
            'hascheck'=>1
        );
        $this->whereIn('id',$chatid)->update($update);
    }



    public function addChatRecord($sender,$receiver,$chatType,$content){
        $add = array(
            'uuid'=>$sender,
            'chatuuid'=>$receiver,
            'addtime'=>time(),
            'chatType'=>$chatType,
            'status'=>1,
            'hascheck'=>2,
            'content'=>$content,

        );
        $this->insert($add);
    }

}