<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/7/26
 * Time: 13:37
 */
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class CommonController extends Controller {

    public function __construct(Request $request)
    {

        $time = $request->time;
        $sig = $request->sig;
        $action = $request->path();
        if(empty($time) || empty($sig) || $sig != md5('sc%7*g' . $time . '@!$%') ){
            echo json_encode(['status' => -1, 'message' => '验证失败']);
            exit();
        }


    }
    public function token($id)
    {
        return $this->encrypt(json_encode(['time' => time(), 'id' => $id]), 'E');
    }

    public function parseToken($str)
    {
        $arr = $this->encrypt($str, 'D');
        $arr = json_decode($arr, true);
        return $arr['id'];
    }

    /*
     * $operation：判断是加密还是解密，E表示加密，D表示解密
     */
    function encrypt($string, $operation, $key = '#ED@!')
    {
        $key = md5($key);
        $key_length = strlen($key);
        $string = $operation == 'D' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
        $string_length = strlen($string);
        $rndkey = $box = array();
        $result = '';
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($key[$i % $key_length]);
            $box[$i] = $i;
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'D') {
            if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
                return substr($result, 8);
            } else {
                return '';
            }
        } else {
            return str_replace('=', '', base64_encode($result));
        }
    }

    /**
     * 取英汉字的第一个字的首字母
     * @param type $str
     * @return string|null
     */
    public function _getFirstCharter($str){

        if(empty($str)){return '';}

        $fchar=ord($str{0});

        if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});

        $s1=iconv('UTF-8','GBK//IGNORE',$str);

        $s2=iconv('GBK','UTF-8',$s1);

        $s=$s2==$str?$s1:$str;

        $asc=ord($s{0})*256+ord($s{1})-65536;

        if($asc>=-20319&&$asc<=-20284) return 'A';
        if($asc>=-20283&&$asc<=-19776) return 'B';
        if($asc>=-19775&&$asc<=-19219) return 'C';
        if($asc>=-19218&&$asc<=-18711) return 'D';
        if($asc>=-18710&&$asc<=-18527) return 'E';
        if($asc>=-18526&&$asc<=-18240) return 'F';
        if($asc>=-18239&&$asc<=-17923) return 'G';
        if($asc>=-17922&&$asc<=-17418) return 'H';
        if($asc>=-17417&&$asc<=-16475) return 'J';
        if($asc>=-16474&&$asc<=-16213) return 'K';
        if($asc>=-16212&&$asc<=-15641) return 'L';
        if($asc>=-15640&&$asc<=-15166) return 'M';
        if($asc>=-15165&&$asc<=-14923) return 'N';
        if($asc>=-14922&&$asc<=-14915) return 'O';
        if($asc>=-14914&&$asc<=-14631) return 'P';
        if($asc>=-14630&&$asc<=-14150) return 'Q';
        if($asc>=-14149&&$asc<=-14091) return 'R';
        if($asc>=-14090&&$asc<=-13319) return 'S';
        if($asc>=-13318&&$asc<=-12839) return 'T';
        if($asc>=-12838&&$asc<=-12557) return 'W';
        if($asc>=-12556&&$asc<=-11848) return 'X';
        if($asc>=-11847&&$asc<=-11056) return 'Y';
        if($asc>=-11055&&$asc<=-10247) return 'Z';

        return "#";
    }


    public function getDetailTime($time){
        if(time()-$time < 60){
            $return = time()-$time."秒前";
        }
        if(time()-$time < 3600){
            $return = intval((time()-$time)/60)."分前";
        }
        if(time()-$time < 3600*24 && time()-$time >= 3600){
            $return = intval((time()-$time)/3600)."小时前";
        }
        if(time()-$time >= 3600*24){
            $return = intval((time()-$time)/86400)."天前";
        }
        return $return;
    }

    public function getDayTime($time){
        $timeDay = date('Y-m-d',$time);
        $today = date('Y-m-d',time());
        $yesterday = date('Y-m-d',strtotime("-1 day"));
        if($timeDay == $today){
            $return = date("H:i",$time);
        }
        if($yesterday == $timeDay){
            $return = "昨天";
        }
        if($timeDay !=$today && $yesterday!=$timeDay){
            $return  = date('m-d',$time);
        }

        return $return;
    }
}