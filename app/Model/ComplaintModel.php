<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/14
 * Time: 11:23
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class ComplaintModel extends Model{
    protected $connection = 'mysql';
    protected $table = 'complaint';
    public $timestamps=false;

    public function accusate($wordid,$complaint,$upuuid,$uuid){
        $data = array(
            'uuid'=>$uuid,
            'upid'=>$wordid,
            'addtime'=>time(),
            'complaint'=>$complaint,
            'upuuid'=>$upuuid,
            'time'=>date('Y-m-d H:i:s'),
        );
        $this->insert($data);
    }



    public function getComplaintList($begin,$end,$content){
        $query = ComplaintModel::select();
        if(!empty($begin)){
            $query = $query->where('time','>=',$begin);
        }
        if(!empty($end)){
            $query = $query->where('time','<=',$end);
        }
        if(!empty($content)){
            $query = $query->where('complaint','==',$content);
        }


        return $query;
    }


    public function delComplaint($id){
        $update = array(
            'status'=>2
        );
        $this->where('id',$id)->update($update);
    }
}