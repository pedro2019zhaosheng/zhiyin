<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/14
 * Time: 10:23
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class WordModel extends Model{

    protected $connection = 'mysql';
    protected $table = 'words';

    public $timestamps = false;

    public function getWordList($page,$size){

     return   $this->where('status',1)
                ->skip(($page-1) * $size)
            ->take($size)
         ->orderBy('addtime','desc')
            ->get();
    }


    public function addWord($name,$wordtype,$status,$type){
        $data = array(
            'word'=>$name,
            'addtime'=>time(),
            'status'=>$status,
            'wordtype'=>$wordtype,
            'type'=>$type,
        );
        $this->insert($data);
    }



    public function getSearchList($page,$size,$name){
        return   $this->where('status',1)->where('word', 'like', '%'.$name . '%')
            ->skip(($page-1) * $size)
            ->take($size)
            ->orderBy('addtime','desc')
            ->get();
    }



    public function getWordLists($wordtype,$type,$content){
        $query = WordModel::select();
        if($type && !empty($type)){
            $query = $query->where('type',$type);
        }
        if($wordtype && !empty($wordtype)){
            $query = $query->where('wordtype',$wordtype);
        }
        if($content && !empty($content)){
            $query = $query->where('word','like','%'.$content . '%');
        }

        return $query;
    }


    public function getAllNum(){
        return $this->where('status',1)->count();
    }



    public function editWord($name,$id,$type,$wordtype){
        $update = array(
            'word'=>  $name,
            'type'=>$type,
            'wordtype'=>$wordtype
        );
        $this->where('id',$id)->update($update);
    }



    public function setWordStatus($status,$id){
        $update = array(
            'status'=>  $status,

        );
        $this->where('id',$id)->update($update);
    }
}