<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/5
 * Time: 10:23
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class UserFriendsModel extends Model{

    protected $connection = 'mysql';
    protected $table = 'user_friends';

    public $timestamps=false;

    public function isFriend($uuid,$othersuuid){
        return $this->where(['uuid'=>$uuid,'fid'=>$othersuuid,'status'=>1])->first();
    }

    public function beforeFriend($uuid,$othersuuid){
        return $this->where(['uuid'=>$uuid,'fid'=>$othersuuid])->first();
    }

    public function getMyFriends($uuid){
        $result = UserFriendsModel::join('users',function ($join){
            $join->on('user_friends.fid','users.id')
                ->Where('user_friends.status' , '=',1);

            })->Where('user_friends.uuid' , '=',$uuid)
            ->select('user_friends.uuid','user_friends.fid','users.nickname','users.avatar')
            ->get()
            ->toArray();
        return $result;
    }

    public function getMyFriendsList($status){
        $query = UserFriendsModel::select();
        if(!empty($status)){
            $query = $query->where('status','==',$status);
        }


//        if(!empty($user)){
//            $query = $query->where('nickname','like','%'.$user.'%');
//        }

        return $query;
    }




    public function addFriends($uuid,$othersuuid){
        $result = $this->where(['uuid'=>$uuid,'fid'=>$othersuuid])->first();
        if(!empty($result)){
            $this->where('id',$result->id)->delete();
        }
        $res = $this->where(['uuid'=>$othersuuid,'fid'=>$uuid])->first();
        if(!empty($res)){
            $this->where('id',$res->id)->delete();
        }

        $insert = array(
          'uuid'=>$uuid,
          'fid'=>$othersuuid,
          'addtime'=>time(),
          'status'=>1,
        );
        $this->insert($insert);
        $insert1 = array(
            'uuid'=>$othersuuid,
            'fid'=>$uuid,
            'addtime'=>time(),
            'status'=>1,
        );
        $this->insert($insert1);
    }


    public function delFriends($uuid,$othersuuid){
        $result = $this->where(['uuid'=>$uuid,'fid'=>$othersuuid,'status'=>1])->first();
        $update = array(
            'status'=>2
        );
        if(!empty($result)){
            $this->where('id',$result->id)->update($update);
        }
        $res = $this->where(['uuid'=>$othersuuid,'fid'=>$uuid,'status'=>1])->first();
        if(!empty($res)){
            $this->where('id',$res->id)->update($update);
        }
    }

}