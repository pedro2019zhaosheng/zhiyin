<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/14
 * Time: 10:30
 */

namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class WordTypeModel extends Model{

    protected $connection = 'mysql';

    protected $table = 'word_type';


    public $timestamps = false;

    public function addWordType($name){
        $data = array(
          'name'=>$name,
          'addtime'=>time(),
          'status'=>1,

        );
        $this->insert($data);
    }


    public function getWordTypeList(){
        return $this->where('status',1)->orderBy('id','asc')->get()->toArray();
    }


    public function getTypeName($type){
        return $this->where('id',$type)->first();
    }


    public function editWord($name,$id){
        $update = array(
          'name'=>  $name,

        );
        $this->where('id',$id)->update($update);
    }
}