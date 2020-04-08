<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/5
 * Time: 10:08
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class PublishReplyModel extends Model{

    protected $connection = 'mysql';
    protected $table='user_publish_reply';

    public $timestamps = false;

    public function getReplyNum($pid){
        return $this->where(['status'=>1,'pid'=>$pid])->count();
    }


    public function getReplyNotReadNum($id){
        return $this->where(['status'=>1,'pid'=>$id,'hascheck'=>2])->count();
    }


    public function getMessageReplyList($pid){
        $return = PublishReplyModel::join('users',function ($join){
            $join->on('user_publish_reply.uuid','users.id')
                ->Where('user_publish_reply.status' , '=',1);
        })
            ->where('user_publish_reply.pid',$pid)
            ->select('user_publish_reply.uuid','user_publish_reply.id','users.avatar','users.nickname','user_publish_reply.reply','user_publish_reply.voicetime','user_publish_reply.replyvoice','user_publish_reply.hascheck')
            ->orderBy('user_publish_reply.addtime','desc')
            ->get()->toArray();
        return $return;
    }


    public function setReplyReaded($replyid){
        $update = array(
          'hascheck'=>1
        );
        return $this->where('pid',$replyid)->update($update);
    }

    public function reply($publishid,$uuid,$replyvoice,$replytime,$spam,$file,$wordid,$word,$type,$wordtype){
        $data = array(
          'pid' => $publishid,
          'uuid' => $uuid,
          'addtime' => time(),
          'status' => 1,
          'hascheck' => 2,
          'replyvoice' => $replyvoice,
          'voicetime' => $replytime,
          'spam' => $spam,
          'replyvoicesrc' => $file,
          'wordid' => $wordid,
          'word' => $word,
          'type' => $type,
          'wordtype' => $wordtype,
        );
       return $this->insertGetId($data);
    }

    public function voiceReplyExamineList($wordtype,$spam,$content){
        $query = PublishReplyModel::select('id','uuid','addtime','word','wordtype','spam','spamreason','replyvoicesrc');
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


    public function getPublishReplyInfo($id){
        return $this->where('id',$id)->first();
    }


    public function updateContent($id,$content,$spam){
        $update = array(

            'content'=>  $content,
            'spam'=>  $spam,
        );
        $this->where('id',$id)->update($update);
    }


    public function getPublishReplyNum($pid){
        return $this->whereIn('pid',$pid)->where('hascheck',2)->count();
    }
}