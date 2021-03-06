<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/7/26
 * Time: 15:23
 */
namespace App\Http\Controllers;

use App\Model\AvatarModel;
use App\Model\UsersModel;
use App\Model\UserSystemModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Model\UserLoginLogModel;
use App\Model\appDownLoadModel;


use DB;
class StatController extends CommonController{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }
    static $LC_DATE = [1,2, 3, 4, 5, 6, 7, 14, 30];
    function prDates($start, $end)
    {
        $dt_start = strtotime('-1 day', strtotime($start));
        $dt_end = strtotime('-1 day', strtotime($end));
        $date = [];
        $year = [];
        $month = [];
        while ($dt_start <= $dt_end) {
            $dt_start = strtotime('+1 day', $dt_start);
            $date[] = date('Y-m-d', $dt_start);
            $year[] = date('Y', $dt_start);
            $month[] = date('Y-m', $dt_start);
        }

        $month = array_values(array_unique($month));
        $year = array_values(array_unique($year));
        return [
            'date' => $date,
            'month' => $month,
            'year' => $year,
        ];
    }

    /**
     * @param date_type ,start,end
     * @return json
     * @desc 新增用户统计
     */
    public function addUserStat(Request $request){
        $date_type = $request->input('date_type',1);
        $start = $request->input('start',1);
        $end = $request->input('end',1);
        $model = new UsersModel();
        $prDate = $this->prDates($request->start, $request->end);
        $insert_string = 'where 1=1 ';
        if (1 == $date_type) {
            $select1 = "FROM_UNIXTIME(regtime, '%Y-%m-%d') as date,FROM_UNIXTIME(regtime, '%Y-%m-%d') as dm_date, count(id) as nums";
            $date_arr = $prDate['date'];
            $s_start = reset($date_arr);
            $s_end = end($date_arr);
            $insert_string = " where FROM_UNIXTIME(regtime, '%Y-%m-%d') between '{$s_start}' and '{$s_end}' ";
        }
        if (2 == $date_type) {
            $select1 = "FROM_UNIXTIME(regtime,'%Y-%m') as date, FROM_UNIXTIME(regtime, '%Y-%m') as dm_date, count(id) as nums";
            $date_arr = $prDate['month'];
            $s_start = $date_arr[0];
            $insert_string = " where FROM_UNIXTIME(regtime,'%Y-%m')  between '{$s_start}' and '{$s_start}' ";
        }

        if (3 == $date_type) {
            $select1 = "id,FROM_UNIXTIME(regtime, '%Y') as date,FROM_UNIXTIME(regtime, '%Y') as dm_date, count(id) as nums";
            $date_arr = $prDate['year'];
            $s_start = $date_arr[0];
            $insert_string = " where FROM_UNIXTIME(regtime, '%Y') between '{$s_start}' and '{$s_start}' ";
        }

        $subQuery = $model->selectRaw($select1)->groupBy("date");
        $sql = $subQuery->toSql();
        $start = strpos($sql, 'group');
        $sql = substr_replace($sql, $insert_string, $start, 0);

        $list = DB::select($sql);
        $array_column = array_column($list,null,'date');
        $data = [];
        foreach($date_arr as $k=> $v){
            if(isset($array_column[$v])){
                $val = $array_column[$v];
                $data[] = (array)$val;
            }else{
                $data[] = array('date'=>$v,'nums'=>0);
            }
        }
        $i=1;
        foreach($data as $k=> &$v){
            if($date_type==1){
                $before_day = date('Y-m-d',strtotime('-1 day',strtotime($v['date'])));
                $sql = "select FROM_UNIXTIME(regtime, '%Y-%m-%d') as date,FROM_UNIXTIME(regtime, '%Y-%m-%d') as dm_date, count(id) as nums
                from users
                 where FROM_UNIXTIME(regtime, '%Y-%m-%d') between '{$before_day}' and '{$before_day}' ";
                $before_data = DB::select($sql);
            }
            if($date_type==2){
                $before_day = date('Y-m',strtotime('-1 month',strtotime($v['date'])));
                $sql = "select FROM_UNIXTIME(regtime, '%Y-%m') as date,FROM_UNIXTIME(regtime, '%Y-%m') as dm_date, count(id) as nums
                from users
                 where FROM_UNIXTIME(regtime, '%Y-%m') between '{$before_day}' and '{$before_day}' ";
                $before_data = DB::select($sql);
            }
            if($date_type==3){
                $before_day = date('Y',strtotime('-1 year',strtotime($v['date'])));
                $sql = "select FROM_UNIXTIME(regtime, '%Y') as date,FROM_UNIXTIME(regtime, '%Y') as dm_date, count(id) as nums
                from users
                 where FROM_UNIXTIME(regtime, '%Y') between '{$before_day}' and '{$before_day}' ";
                $before_data = DB::select($sql);
            }
            if($v['nums']>0){
                $v['rate'] = $before_data[0]->nums>0?(($v['nums']-$before_data[0]->nums)/$before_data[0]->nums):0;
            }else{
                $v['rate'] = 0;
            }
            $v['rate'] = sprintf("%.2f",$v['rate']);
        }
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }

    /**
     * @param date_type ,start,end
     * @return json
     * @desc 活跃用户统计
     */
    public function activeUserStat(Request $request){
        $date_type = $request->input('date_type',1);
        $start = $request->input('start',1);
        $end = $request->input('end',1);
        $model = new UserLoginLogModel();
        $prDate = $this->prDates($request->start, $request->end);
        $insert_string = 'where 1=1 ';
        if (1 == $date_type) {
            $select1 = "FROM_UNIXTIME(ctime, '%Y-%m-%d') as date,FROM_UNIXTIME(ctime, '%Y-%m-%d') as dm_date, count(id) as nums";
            $date_arr = $prDate['date'];
            $s_start = reset($date_arr);
            $s_end = end($date_arr);
            $insert_string = " where FROM_UNIXTIME(ctime, '%Y-%m-%d') between '{$s_start}' and '{$s_end}' ";
        }
        if (2 == $date_type) {
            $select1 = "FROM_UNIXTIME(ctime,'%Y-%m') as date, FROM_UNIXTIME(ctime, '%Y-%m') as dm_date, count(id) as nums";
            $date_arr = $prDate['month'];
            $s_start = $date_arr[0];
            $insert_string = " where FROM_UNIXTIME(ctime,'%Y-%m')  between '{$s_start}' and '{$s_start}' ";
        }

        if (3 == $date_type) {
            $select1 = "id,FROM_UNIXTIME(ctime, '%Y') as date,FROM_UNIXTIME(ctime, '%Y') as dm_date, count(id) as nums";
            $date_arr = $prDate['year'];
            $s_start = $date_arr[0];
            $insert_string = " where FROM_UNIXTIME(ctime, '%Y') between '{$s_start}' and '{$s_start}' ";
        }

        $subQuery = $model->selectRaw($select1)->groupBy("date");
        $sql = $subQuery->toSql();
        $start = strpos($sql, 'group');
        $sql = substr_replace($sql, $insert_string, $start, 0);
        $list = DB::select($sql);
        $array_column = array_column($list,null,'date');
        $data = [];
        foreach($date_arr as $k=> $v){
            if(isset($array_column[$v])){
                $val = $array_column[$v];
                $data[] = (array)$val;
            }else{
                $data[] = array('date'=>$v,'nums'=>0);
            }
        }
        $i=1;
        foreach($data as $k=> &$v){
            if($date_type==1){
                $before_day = date('Y-m-d',strtotime('-1 day',strtotime($v['date'])));
                $sql = "select FROM_UNIXTIME(ctime, '%Y-%m-%d') as date,FROM_UNIXTIME(ctime, '%Y-%m-%d') as dm_date, count(id) as nums
                from stat_user_login_log
                 where FROM_UNIXTIME(ctime, '%Y-%m-%d') between '{$before_day}' and '{$before_day}' ";
                $before_data = DB::select($sql);
            }
            if($date_type==2){
                $before_day = date('Y-m',strtotime('-1 month',strtotime($v['date'])));
                $sql = "select FROM_UNIXTIME(ctime, '%Y-%m') as date,FROM_UNIXTIME(ctime, '%Y-%m') as dm_date, count(id) as nums
                from stat_user_login_log
                 where FROM_UNIXTIME(ctime, '%Y-%m') between '{$before_day}' and '{$before_day}' ";
                $before_data = DB::select($sql);
            }
            if($date_type==3){
                $before_day = date('Y',strtotime('-1 year',strtotime($v['date'])));
                $sql = "select FROM_UNIXTIME(ctime, '%Y') as date,FROM_UNIXTIME(ctime, '%Y') as dm_date, count(id) as nums
                from stat_user_login_log
                 where FROM_UNIXTIME(ctime, '%Y') between '{$before_day}' and '{$before_day}' ";
                $before_data = DB::select($sql);
            }
            if($v['nums']>0){
                $v['rate'] = $before_data[0]->nums>0?(($v['nums']-$before_data[0]->nums)/$before_data[0]->nums):0;
            }else{
                $v['rate'] = 0;
            }

            $v['rate'] = sprintf("%.2f",$v['rate']);
        }
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }
    /**
     * @param  start,end
     * @return json
     * @desc 留存率统计
     */
    public function xzlcStat(Request $request){
        $start = $request->input('start','');
        $end = $request->input('end','');
        $arr = self::$LC_DATE;
        foreach ($arr as $index) {
            $total["d" . $index . "_total"] = 0;
        }
        $s_timestamp = strtotime($start);
        $e_timestamp = strtotime($end);
        $days = ($e_timestamp - $s_timestamp) / 86400;
        $rows = [];
        for ($i = $days; $i >= 0; $i--) {
            $row = [];
            $current_day = date("Y-m-d", strtotime("+$i day", $s_timestamp));
            $current_day_start = date("Y-m-d 00:00:00", strtotime("+$i day", $s_timestamp));
            $current_day_end = date("Y-m-d 23:59:59", strtotime("+$i day", $s_timestamp));


            $activeuser_sql = [];
            foreach ($arr as $index) {
                $next = $i + $index;
                $next_day_start = date("Y-m-d 00:00:00", strtotime("+$next day", $s_timestamp));
                $next_day_end = date("Y-m-d 23:59:59", strtotime("+$next day", $s_timestamp));
                //活跃用户
                $activeuser_sql = "SELECT COUNT(DISTINCT user_id) val FROM `stat_user_login_log` 
                                                where FROM_UNIXTIME(ctime,'%Y-%m-%d %H:%i:%s') between '$next_day_start' and '$next_day_end' ";
                $data = DB::select($activeuser_sql);
                $active[] = $data[0]->val;
            }

            $newuser_sql = "SELECT COUNT(id) val FROM `users` 
                                        WHERE  FROM_UNIXTIME(regtime,'%Y-%m-%d %H:%i:%s') between '$current_day_start' and '$current_day_end'  ";
            $newuser = DB::select($newuser_sql);

            $row['newuser'] = $newuser[0]->val;

            foreach ($arr as $key => $item) {
                if (!isset($total["newuser" . $item . "_total"])){
                    $total["newuser" . $item . "_total"] = 0;
                }
                $total["d" . $item . "_total"] += $row["d" . $item] = isset($active[$key]) ? $active[$key] : 0;
                //newuser_total应该抛弃那些时间未到的，今天有新增，但是$key代表的日期还没到，不应该把新增算到$key对应的日期里，
                //因为留存 = 对应key的活跃/对应key的新增
                $next = $i + $item - 1;
                if (strtotime(date("Y-m-d 23:59:59", strtotime("+$next day", $s_timestamp))) < time()){
                    $total["newuser" . $item . "_total"] += $row['newuser'];
                }
            }

            foreach ($arr as $index) {
//                $total["d{$index}_total_lc"] = $total['newuser_total'] > 0 ? (round($total["d{$index}_total"] / $total['newuser_total'], 4) * 100) . '%' : '0%';
                $row["d{$index}_lc"] = $row['newuser'] > 0 ? (round($row["d{$index}"] / $row['newuser'], 4) * 100) . '%' : '0%';

            }


            $row['date'] = $current_day;
            $rows[$current_day] = $row;
        }
        $data = array_values($rows);
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }
    /**
     * @param platform,channel ,start,end
     * @return json
     * @desc 下载统计
     */
    public function appDownLoadStat(Request $request){
        $start = $request->input('start','');
        $end = $request->input('end','');
        $platform = $request->input('platform',0);
        $channel = $request->input('channel',0);
        $string = '';
        if($platform>0){
            $string .= " and platform={$platform}";
        }
        if($channel>0){
            $string .= " and channel={$channel}";
        }
        $s_timestamp = strtotime($start);
        $e_timestamp = strtotime($end);
        $days = ($e_timestamp - $s_timestamp) / 86400;
        $rows=[];
        for ($i = $days; $i >= 0; $i--) {
            $row = [];
            $current_day = date("Y-m-d", strtotime("+$i day", $s_timestamp));
            $current_day_start = date("Y-m-d 00:00:00", strtotime("+$i day", $s_timestamp));
            $current_day_end = date("Y-m-d 23:59:59", strtotime("+$i day", $s_timestamp));

            $total_sql = "SELECT COUNT(id) val FROM `stat_app_download` 
                                        WHERE  FROM_UNIXTIME(ctime,'%Y-%m-%d %H:%i:%s') between '$current_day_start' and '$current_day_end'   ";
            $total = DB::select($total_sql);
            $channel_sql = "SELECT COUNT(id) val,channel FROM `stat_app_download` 
                                        WHERE  FROM_UNIXTIME(ctime,'%Y-%m-%d %H:%i:%s') between '$current_day_start' and '$current_day_end'  group by channel ";
            $channel =DB::select($channel_sql);
            if($channel){
                $row['channel'] = $channel;
            }else{
                $row['channel'] = [];
            }

            $row['total'] = $total[0]->val;
            $row['date'] = $current_day;
            $rows[] = $row;
        }
        return json_encode(['status'=>1,'message'=>'成功','data'=>$rows]);
    }
    /**
     * @param date_type ,start,end
     * @return json
     * @desc 用户认证流失统计
     */
    public function approveStat(Request $request){
        $start = $request->input('start','');
        $end = $request->input('end','');
        $arr = self::$LC_DATE;
        foreach ($arr as $index) {
            $total["d" . $index . "_total"] = 0;
        }
        $s_timestamp = strtotime($start);
        $e_timestamp = strtotime($end);
        $days = ($e_timestamp - $s_timestamp) / 86400;
        $rows = [];
        for ($i = $days; $i >= 0; $i--) {
            $row = [];
            $current_day = date("Y-m-d", strtotime("+$i day", $s_timestamp));
            $current_day_start = date("Y-m-d 00:00:00", strtotime("+$i day", $s_timestamp));
            $current_day_end = date("Y-m-d 23:59:59", strtotime("+$i day", $s_timestamp));


            $activeuser_sql = [];
            foreach ($arr as $index) {
                $next = $i + $index;
                $next_day_start = date("Y-m-d 00:00:00", strtotime("+$next day", $s_timestamp));
                $next_day_end = date("Y-m-d 23:59:59", strtotime("+$next day", $s_timestamp));
                //活跃用户
                $activeuser_sql = "SELECT COUNT(DISTINCT user_id) val FROM `stat_user_login_log` 
                                                where FROM_UNIXTIME(ctime,'%Y-%m-%d %H:%i:%s') between '$next_day_start' and '$next_day_end' ";
                $data = DB::select($activeuser_sql);
                $active[] = $data[0]->val;
            }

            $newuser_sql = "SELECT COUNT(id) val FROM `avatar` 
                                        WHERE  FROM_UNIXTIME(addtime,'%Y-%m-%d %H:%i:%s') between '$current_day_start' and '$current_day_end'  ";

            $newuser = DB::select($newuser_sql);
            $fail_sql = "SELECT COUNT(id) val FROM `avatar` 
                                        WHERE  FROM_UNIXTIME(addtime,'%Y-%m-%d %H:%i:%s') between '$current_day_start' and '$current_day_end' and pass=0 ";
            $failuser = DB::select($fail_sql);
            $row['failuser'] = $failuser[0]->val;
            $row['newuser'] = $newuser[0]->val;
            foreach ($arr as $key => $item) {
                if (!isset($total["newuser" . $item . "_total"])){
                    $total["newuser" . $item . "_total"] = 0;
                }
                $total["d" . $item . "_total"] += $row["d" . $item] = isset($active[$key]) ? $active[$key] : 0;
                //newuser_total应该抛弃那些时间未到的，今天有新增，但是$key代表的日期还没到，不应该把新增算到$key对应的日期里，
                //因为留存 = 对应key的活跃/对应key的新增
                $next = $i + $item - 1;
                if (strtotime(date("Y-m-d 23:59:59", strtotime("+$next day", $s_timestamp))) < time()){
                    $total["newuser" . $item . "_total"] += $row['newuser'];
                }
            }
//            foreach ($arr as $index) {
////                $total["d{$index}_total_lc"] = $total['newuser_total'] > 0 ? (round($total["d{$index}_total"] / $total['newuser_total'], 4) * 100) . '%' : '0%';
//                $row["d{$index}_lc"] = $row['newuser'] > 0 ? (round($row["d{$index}"] / $row['newuser'], 4) * 100) . '%' : '0%';
//
//            }


            $row['date'] = $current_day;
            $rows[$current_day] = $row;
        }
        $data = array_values($rows);
        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }

    /**
     * @param  start,end
     * @return json
     * @desc 头像审核通过率统计
     */
    public function avatarCheckStat(Request $request){
        $start = $request->input('start','');
        $end = $request->input('end','');
        $page = $request->input('page',1);
        $limit = 10;
        $offset = $page*$limit-$limit;
        $arr = self::$LC_DATE;
        foreach ($arr as $index) {
            $total["d" . $index . "_total"] = 0;
        }
        $s_timestamp = strtotime($start);
        $e_timestamp = strtotime($end);
        $days = ($e_timestamp - $s_timestamp) / 86400;
        $rows = [];
        $start = $start." 00:00:00";
        $end = $end." 23:59:59";
        $sql = "(select sum(count) as num,date from ( 
select count(*) count, FROM_UNIXTIME(a.addtime,'%Y-%m-%d') date from avatar a
where FROM_UNIXTIME(a.addtime, '%Y-%m-%d %H:%i:%s')>='{$start}' and FROM_UNIXTIME(a.addtime, '%Y-%m-%d %H:%i:%s')<='{$end}'
GROUP BY date
UNION ALL
select @uu:=0 as count,date from (
select @num:=@num+1 as number,date_format(adddate('{$start}', INTERVAL @num DAY),'%Y-%m-%d') as date
from avatar a ,(select @num:=-1) t
where adddate('$start', INTERVAL @num DAY) < date_format('$end','%Y-%m-%d')
order by date ) rr
) sss GROUP BY sss.date ORDER BY date) as cc";
//        $data = DB::select($sql);
//        $data = DB::table(DB::raw('select * from users'))->paginate($limit);
//        $sql = '(select * from users) cc';
        $data = \DB::table(\DB::raw($sql))->paginate(10);
        foreach($data as &$v){
            $success_sql = "select count(id) as count  FROM avatar where FROM_UNIXTIME(addtime, '%Y-%m-%d') between '{$v->date}' and '{$v->date}' and status=1";
            $success = DB::select($success_sql);
            $fail_sql = "select count(id) as count  FROM avatar where FROM_UNIXTIME(addtime, '%Y-%m-%d') between '{$v->date}' and '{$v->date}' and status=0";
            $fail = DB::select($fail_sql);
            $v->success_num = $success[0]->count;
            $v->fail_num = $fail[0]->count;
            $total_rate = $v->num > 0 ? (round($v->success_num / $v->num, 4) * 100) : 0;
            $rate = sprintf("%.2f",round($total_rate,2));
            $v->rate = $rate;

        }

        return json_encode(['status'=>1,'message'=>'成功','data'=>$data]);
    }

    /**
     * @desc用户登录日志
     * @param sig，time，user_id
     */
    public function recordLogin(Request $request){
        $user_id = $request->input('user_id',0);
        $loginModel = new UserLoginLogModel();
        $userModel = new UsersModel();
        $user = $userModel->where(['id'=>$user_id])->first();
        if(!$user){
            return json_encode(['status'=>0,'message'=>'用户不存在！','data'=>new \stdClass()]);
        }
        //查询log是否存在
        $date = date('Y-m-d');
        $userLog = DB::table(DB::raw("(select * from stat_user_login_log where from_unixtime(ctime,'%Y-%m-%d') between '{$date}' and '{$date}' and user_id={$user_id}) as cc"))->first();
        $status= 0;
        if($userLog){
            $status = 1;
        }else{
            $data = [
                'user_id'=>$user_id,
                'ctime'=>time()
            ];
            $loginModel->insert($data);
            $status = 1;
        }

        return json_encode(['status'=>1,'message'=>'操作成功','data'=>new \stdClass()]);
    }

    /**
     * @desc用户登录日志
     * @param sig，time，user_id
     */
    public function recordAppDownLoad(Request $request){
        $platform = $request->input('platform',0);//1：android 2：ios
        $channel = $request->input('channel',0);//渠道
        $source = $request->input('source',0);//1：官网2：h5
        $model = new appDownLoadModel();

        $data = [
            'source'=>$source,
            'platform'=>$platform,
            'channel'=>$channel,
            'ctime'=>time()
        ];
        $model->insert($data);
        return json_encode(['status'=>1,'message'=>'操作成功','data'=>new \stdClass()]);
    }
    
    /**
    *@desc 用户登录日志
    *$param sig time user_id
    */
    public function recordAppDownLoad(Request,$request)
    {
        $platform = $request->input('platform',0);
        $channel = $request->input('channel',0);
        $source = $request->input('source',0);
        
        $data = [
            'source'=>$source,
            'platfrom'=>$platform,
            'channel'=>$channel,
            'ctime'=>time(),
        ];        

        $model->insert($data);
        return json_endoce(['status'=>1,'message'=>'操作成功','data'=>new \stdClass()]);
    }
}