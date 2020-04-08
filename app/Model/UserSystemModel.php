<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/27
 * Time: 14:03
 */

namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class UserSystemModel extends Model{

    protected $connection='mysql';
    protected $table = 'user_system';
    public $timestamps = false;


    public function userChatSystem($uuid,$word){
        $insert = array(
             'uuid'=>  $uuid,
             'whosay'=>1,
            'content'=>$word,
            'addtime'=>time(),
            'read'=>2
        );
        $this->insert($insert);
    }


    public function getUserSystemData($uuid){

        $notread = $this->where('uuid',$uuid)->where('status',1)->where('read',1)->count();
        $res = $this->where('uuid',$uuid)->where('status',1)->orderBy('addtime','asc')->get()->toArray();
         $data['notread'] = $notread;
         $data['data'] = $res;
        return $data;
    }


    public function getUserSystemNotRead($uuid){
        $notread = $this->where('uuid',$uuid)->where('status',1)->where('read',1)->count();
        return $notread;
    }

    public function setSystemNewsReaded($uuid){
        $update = array(
            'read'=>2
        );
        $this->where('uuid',$uuid)->update($update);
    }



    public function addNews($uuid,$word){
        $insert = array(
          'uuid'=>$uuid,
          'content'=>$word,
          'whosay'=>2,
          'addtime'=>time(),
          'status'=>1,
          'read'=>1,
        );
        $this->insert($insert);
    }

    public function addNewsWithAvatar($uuid,$word,$avatar){
        $insert = array(
            'uuid'=>$uuid,
            'content'=>$word,
            'whosay'=>2,
            'addtime'=>time(),
            'status'=>1,
            'read'=>1,
            'avatar'=>$avatar
        );
        $this->insert($insert);
    }
}