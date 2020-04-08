<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/15
 * Time: 16:05
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class UserLovePublish extends Model{
    protected $connection = 'mysql';
    protected $table = 'user_love_publish';



    public function getInfo($uuid,$puuid){
        $data = $this->where(['uuid'=>$uuid,'puuid'=>$puuid])->get();
        return $data;
    }


    public function addLove($uuid,$puuid,$pid){
        $add = array(
          'uuid'=>  $uuid,
          'puuid'=>  $puuid,
          'pid'=>  $pid,
          'addtime'=>  time(),
        );
        $this->insert($add);
    }


    public function getLoveNum($id){
        return $this->where('pid',$id)->count();
    }
}