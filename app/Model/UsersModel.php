<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/7/29
 * Time: 15:46
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class UsersModel extends Model{
    protected $connection = 'mysql';

    protected $table = "users";

    public $timestamps = false;

    public function getPhone($phone){
        return $this->where('phone',$phone)->first();
    }
    public function register($phone,$sex,$nickname,$birthday,$password,$faceimage){

        $data = $this->select('nickname','avatar','signal','sex','birthday')->where('phone',$phone)->first();

        if(!empty($data)){
            return [];
        }

        $data=array(
            'phone'=>$phone,
            'sex'=>$sex,
            'nickname'=>$nickname,
            'birthday'=>$birthday,
            'regtime'=>time(),
            'regtimestr'=>date('Y-m-d'),
            'password'=>$password,
            'faceimage'=>$faceimage,


        );
        return $this->insertGetId($data);
    }



    public function userInfo($uuid){

        return $this->select('nickname','signal','sex','birthday','canseehome','phone','area','prohibition','password','hxinuname','faceimage')->where('id',$uuid)->first();
    }


    public function userlogin($phone,$password){
        return $this->select('id','nickname','signal','sex','birthday','canseehome','phone','area','prohibition','password','hxinuname','faceimage')->where(['phone'=>$phone,'password'=>$password])->first();
    }



    public function setPrivacy($uuid,$status){
        $update = array(
            'canseehome'=>$status
        );
        return $this->where('id',$uuid)->update($update);
    }


    public function editUserInfo($uuid,$type,$content){
        //1:修改头像；2：修改昵称；3：修改性别；4：修改生日；5：修改个性签名
        if($type == 1){
            $update = array(
                'avatar'=>$content
            );
        }elseif ($type == 2){
            $update = array(
                'nickname'=>$content
            );
        }elseif ($type == 3){
            $update = array(
                'sex'=>$content
            );
        }elseif ($type == 4){
            $update = array(
                'birthday'=>$content
            );
        }elseif ($type == 5){
            $update = array(
                'signal'=>$content
            );
        }
        $this->where('id',$uuid)->update($update);
    }


    public function getUserData($uuid,$upuuid){
        $u1 = $this->where('id',$uuid)->first();
        $u2 = $this->where('id',$upuuid)->first();
        return array(
          'uname'=>  empty($u1->nickname)?'未设置':$u1->nickname,
          'upuname'=>   empty($u2->nickname)?'未设置':$u2->nickname,
          'phone'=> $u1->phone,
            'status'=>$u2['prohibition']
        );
    }



    public function userList($begin,$end,$user){
        $query = UsersModel::select();
        if(!empty($begin)){
            $query = $query->where('regtimestr','>=',$begin);
        }
        if(!empty($end)){
            $query = $query->where('regtimestr','<=',$end);
        }

        if(!empty($user)){
            $query = $query->where('nickname','like','%'.$user.'%');
        }

        return $query;
    }

    public function changePass($phone,$password){
        $update = array(
            'password'=>$password
        );
        $this->where('phone',$phone)->update($update);
    }


    public function phoneChange($phone,$uuid){
        $update = array(
            'phone'=>$phone
        );
        $this->where('id',$uuid)->update($update);
    }


    public function addUserArea($uuid,$area){
        $update = array(
            'area'=>$area
        );
        $this->where('id',$uuid)->update($update);
    }


    public function prohibition($uuid,$status){
        $update = array(
            'prohibition'=>$status
        );
        $this->where('id',$uuid)->update($update);
    }


    public function getGroupUserData($usergroup){
        return $this->whereIn('id',$usergroup)->get();
    }



    public function updataFaceImage($uuid,$faceimage,$pass,$avatar){
        if($pass == 1){
            $update = array(
                'faceimage'=>$faceimage,
                'avatar'=>$avatar
            );
        }else{
            $update = array(
                'faceimage'=>$faceimage
            );
        }

        $this->where('id',$uuid)->update($update);
    }


    public function updateAvatar($uuid,$avatar){
        $update = array(
            'avatar'=>$avatar,
            'haveavatar'=>1
        );
        $this->where('id',$uuid)->update($update);
    }


    public function addUser($nickname,$phone,$avatar,$birthday,$area,$signal,$sex){
        $insert = array(
          'nickname'=>$nickname,
          'avatar'=>$avatar,
          'phone'=>$phone,
          'sex'=>$sex,
          'regtime'=>time(),
          'signal'=>$signal,
          'birthday'=>$birthday,
          'regtimestr'=>date('Y-m-d'),
          'password'=>md5('888888abc123'),
          'canseehome'=>1,
          'area'=>$area,
          'prohibition'=>1,
          'image'=>'',
          'faceimage'=>'',
          'is_real'=>0,
        );

        return $this->insertGetId($insert);
    }
}