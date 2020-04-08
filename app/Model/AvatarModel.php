<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/22
 * Time: 14:11
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class AvatarModel extends Model{

    protected $connection = 'mysql';

    protected $table = 'avatar';


    public $timestamps=false;

    public function addData($uuid,$avatar,$score,$pass){
        $data = array(
            'uuid'=>  $uuid,
            'avatar'=>  $avatar,
            'status'=>  1,
            'score'=>  $score,
            'addtime'=>  time(),
            'pass'=>  $pass,
        );
         $this->insert($data);
    }


    public function addPartData($uuid,$avatar){
        $data = array(
            'uuid'=>  $uuid,
            'avatar'=>  $avatar,
            'status'=>  1,
            'score'=>  '',
            'addtime'=>  time(),
            'pass'=>2
        );
        return $this->insertGetId($data);
    }



    public function userAvatarInfo($uuid){
        $ret = $this->where(['uuid'=>$uuid,'status'=>1])->orderBy('addtime','desc')->first();

        if(!empty($ret) && $ret->pass !=0){//审核通过 或者审核中
            $data['avatar'] = $ret;
        }else{
            $ret = $this->where('uuid',$uuid)->where('status',1)->where('pass','<>',0)->orderBy('addtime','desc')->first();//查询最近一张 审核中或者审核通过的头像
            $data['avatar'] = empty($ret)?'':$ret;
        }



        $res = $this->where(['uuid'=>$uuid,'status'=>1,'pass'=>1])->get();//判断用户是否有审核通过的头像
        if(empty($res) ||count($res) ==0){
            $passed = 0;
        }else{
            $passed = 1;
        }
        $data['passed'] = $passed;


        return $data;
    }


    public function userAvatarList($uuid){
        return $this->where('uuid',$uuid)->where('status',1)->orderBy('addtime','desc')->get();
    }



    public function delUserAvatar($avataraid){
        $update = array(
          'status'=>0
        );
        return $this->where('id',$avataraid)->update($update);
    }



    public function updateData($avatarid,$score,$pass){
        $update = array(
          'score'=>$score,
          'pass'=>$pass,
        );
        $this->where('id',$avatarid)->update($update);
    }


    public function getAvatarExamineList($page,$size){
        $result = $this->where('status',1)->where('pass',2)
            ->skip(($page-1) * $size)
            ->take($size)
            ->orderBy('addtime','desc')
            ->get()->toArray();
        return $result;
    }


    public function getAllNum(){
        return $this->where('status',1)->where('pass',2)->count();
    }

    public function avatarExamine($id,$status){
        $update = array(
            'pass'=>$status
        );
        $this->where('id',$id)->update($update);
    }


    public function getPassAvatar($othersuuid){
        return  $this->where(['uuid'=>$othersuuid,'status'=>1,'pass'=>1])->orderBy('addtime','desc')->first();
    }



    public function getUserAvatar($uuid,$avataraid){
        return $this->where('uuid',$uuid)->where('status',1)->where('pass',1)->where('id','<>',$avataraid)->get();
    }


    public function getAvatar($avataraid){
        return $this->where('id',$avataraid)->first();
    }

    public function updateTime($avatarid){

        $update = array(
            'addtime'=>time()
        );
        $this->where('id',$avatarid)->update($update);
    }
}