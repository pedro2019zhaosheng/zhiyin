<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/6
 * Time: 16:59
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class BlackListModel extends Model{

    protected $connection = 'mysql';
    protected $table='blacklist';

    public function myBlack($uuid){
        $result = BlackListModel::join('users',function ($join){
            $join->on('blacklist.blackuuid','users.id');
        })->select('blacklist.blackuuid','users.nickname','users.avatar')->where('blacklist.uuid',$uuid)->where('blacklist.status',1)->get();
        return $result;
    }


    public function addBlacklist($uuid,$othersuuid){
        $result = $this->where(['uuid'=>$uuid,'blackuuid'=>$othersuuid])->first();
        if(empty($result)){
            $insert = array(
                'uuid'=>  $uuid,
                'blackuuid'=>  $othersuuid,
                'status'=>  1,
                'addtime'=>  time(),
            );
            $this->insert($insert);
        }

    }


    public function delBlacklist($uuid,$othersuuid){
        $this->where(['uuid'=>$uuid,'blackuuid'=>$othersuuid])->delete();
    }



    public function ifJoinFriend($uuid,$fid){
       return $this->where(['uuid'=>$uuid,'blackuuid'=>$fid,'status'=>1])->first();
    }
}