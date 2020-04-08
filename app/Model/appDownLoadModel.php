<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/14
 * Time: 10:23
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class appDownLoadModel extends Model{

    protected $connection = 'mysql';
    protected $table = 'stat_app_download';

    public $timestamps = false;

}