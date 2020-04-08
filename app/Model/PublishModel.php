<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/2
 * Time: 15:05
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PublishModel extends Model{

    protected $connection = 'mysql';
    protected $table = 'user_publish';

    public $timestamps = false;

    public function mypublish($uuid){
        return $this->where('uuid',$uuid)->where('status',1)->orderBy('addtime','desc')->get();
    }
    public function mypublishuse($uuid){
        return $this->where('uuid',$uuid)->where('status',1)->where('spam','<>',1)->orderBy('addtime','desc')->get();
    }


    public function mypublishThree($uuid){
        return $this->where('uuid',$uuid)->where('status',1)->where('spam','<>',1)->orderBy('addtime','desc')->limit(3)->get();
    }


    public function goPublish($uuid,$word,$wordid,$voice,$type,$voicetime,$spam,$content,$file,$wordtype){
        $insert = array(
            'uuid'=>$uuid,
            'word'=>$word,
            'wordid'=>$wordid,
            'voice'=>$voice,
            'voicetime'=>$voicetime,
            'type'=>$type,
            'status'=>1,
            'addtime'=>time(),

            'year'=>date("Y",time()),
            'month'=>date("m-d",time()),
            'spam'=>$spam,
            'content'=>$content,
            'voicesrc'=>$file,
            'wordtype'=>$wordtype,
        );
        return $this->insertGetId($insert);
    }




    public function getMessageList($uuid){
        $result =  $this->where('status',1)->where('uuid',$uuid)->where('newestreplytime','>',0)->orderBy('newestreplytime','desc')->get()->toArray();

        return $result;
    }



    public function updateNewsReplyTime($publishid){
        $update = array(
            'newestreplytime'=>time()
        );
        $this->where('id',$publishid)->update($update);
    }

    public function getIndex(){
        $result = PublishModel::join('users',function ($join){
            $join->on('users.id','user_publish.uuid');
        })
            ->select('users.id as upid','users.sex','users.area','users.birthday','users.nickname','users.avatar','user_publish.type','user_publish.wordtype','user_publish.voicetime','user_publish.wordid','user_publish.word','user_publish.voice','user_publish.addtime','user_publish.id as id')
            ->where('user_publish.status',1)
            ->where('user_publish.spam',0)
            ->where('users.haveavatar',1)
//            ->orderBy (DB::raw('RAND()'))->limit(5)//随机取
                ->orderBy('addtime','desc')->limit(5)
            ->get();
        return $result;
    }


    public function getNum($wordid){
        return  $this->where('wordid',$wordid)->count();
    }


    public function getPublishInfo($id){
        return $this->where('id',$id)->first();
    }


    public function delComplaint($id){
        $update = array(
            'status'=>2
        );
        $this->where('id',$id)->update($update);
    }



    //主页推荐内容 最重要
    public function getData($uuid){
        $result = PublishModel::join('users',function ($join){
            $join->on('users.id','user_publish.uuid');
        })
            ->select('users.id as upid','users.sex','users.area','users.birthday','users.nickname','users.avatar','user_publish.type','user_publish.wordtype','user_publish.voicetime','user_publish.wordid','user_publish.word','user_publish.voice','user_publish.addtime','user_publish.id as id')
            ->where('user_publish.status',1)
            ->where('user_publish.spam',0)
            ->where('users.haveavatar',1)
            ->whereNotIn('user_publish.uuid',[$uuid])
            ->orderBy (DB::raw('RAND()'))->limit(5)//随机取
            ->get();
        return $result;
    }




    public function getUserPublishList($type,$begin,$end){
        $query = PublishModel::select();
        if(!empty($begin)){
            $query = $query->where('addtime','>=',$begin);
        }
        if(!empty($end)){
            $query = $query->where('addtime','<=',$end);
        }

        if(!empty($type)){
            $query = $query->where('type',$type);
        }

        return $query;
    }



    public function voiceExamineList($wordtype,$spam,$content){

        $query = PublishModel::select('id','uuid','addtime','word','wordtype','spam','spamreason','voicesrc','content');
        if(!empty($content)){
            $query = $query->where('word','like','%'.$content.'%');

        }
        if(!empty($spam)){
            $query = $query->where('spam',$spam);
        }

        if(!empty($wordtype)){
            $query = $query->where('wordtype',$wordtype);
        }

        return $query;
    }




    public function voiceExamine($id,$spamtype,$spamcontent){
        $update = array(
          'spam'=>$spamtype,
          'spamreason'=>$spamcontent,
        );
        $this->where('id',$id)->update($update);
    }



    public function editWord($id,$type,$wordtype){
        $update = array(
          'type'=>  $type,
          'wordtype'=>  $wordtype,
        );
        $this->where('wordid',$id)->update($update);
    }


    public function userInfo($publishid){
       $result =  PublishModel::join('users',function ($join){
            $join->on('users.id','user_publish.uuid');
        })->select('users.nickname','users.id')->where('user_publish.id',$publishid)->first();

       return $result;
    }


    public function updateContent($id,$content,$spam,$spamreason){
        $update = array(

            'content'=>  $content,
            'spam'=>  $spam,
            'spamreason'=>  $spamreason,
        );
        $this->where('id',$id)->update($update);
    }


    public function delMyPublish($id){
        $update = array(

            'status'=>  2,

        );
        $this->where('id',$id)->update($update);
    }
}