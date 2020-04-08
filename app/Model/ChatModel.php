<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/12
 * Time: 10:42
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class ChatModel extends Model{

    protected $connection  = 'mysql';
    protected $table = 'chats_users';


    public $timestamps = false;

    public function getChatList($uuid){
        $data = ChatModel::join('users',function ($join){
            $join->on('chats_users.chatuuid','users.id');
        })
            ->where('chats_users.uuid',$uuid)
            ->select('users.id as uuid','chats_users.id as chatid','users.nickname','users.avatar','chats_users.addtime','chats_users.notchecknum','chats_users.chatType','chats_users.content')

            ->orderBy('chats_users.addtime','desc')
            ->get();

        return $data;
    }



    public function addChatRecord($uuid,$puuid,$content){
        $add = array(
          'uuid'=>$uuid,
          'chatuuid'=>$puuid,
          'addtime'=>time(),
          'chatType'=>1,
          'content'=>$content,
          'notchecknum'=>1,
        );
        $this->insert($add);
    }



    public function ifChat($uuid,$puuid){
        return $this->where(['uuid'=>$uuid,'chatuuid'=>$puuid])->first();
    }

}