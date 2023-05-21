<?php

// +----------------------------------------------------------------------
// | EasyAdmin
// +----------------------------------------------------------------------
// | PHP交流群: 763822524
// +----------------------------------------------------------------------
// | 开源协议  https://mit-license.org 
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zhongshaofa/EasyAdmin
// +----------------------------------------------------------------------

namespace app\admin\service;

use app\admin\model\FishUser;
use app\admin\model\SystemAdmin;
use think\facade\Cache;

class AutoSeek
{


    public function auto_seek_go(){
        //自动分配未被分配的
        $last_user=FishUser::field("id,seek_admin_id")->where("seek_admin_id IS NUll")->select();
        foreach ($last_user as $v){
            $seek_admin_id=$this->auto_seek();
            if($seek_admin_id){
                Cache::set("new_".$seek_admin_id,1);
                FishUser::where("id",$v["id"])->update(["seek_admin_id"=>$seek_admin_id]);
            }
        }
    }

    /*自动分配咨询*/
    public function auto_seek(){
        /*查询所有咨询人员*/
        $admin_all=SystemAdmin::where("auth_ids REGEXP '(^|,)(9|10)($|,)'")->select();
        $admin_login=Cache::get("login_admin");
        $now=time();$admin_array=[];
        foreach ($admin_all as $key=>$value){
            $keep=$admin_login[$value["id"]]??0;
            if($keep>($now-300)){
                //超过五分钟未获取登陆状态视为离线
                $admin_array[]=$value["id"];
            }
        }
        if(empty($admin_array)){
            //无人在线,返回false
            return false;
        }
        //根据算法，算出最空闲的人
        $admin_id=$this->get_last_admin($admin_array);

        return $admin_id;
    }

    public function get_last_admin($admin_array){
        //排除算法，从后向前剔除数组内的人，直至剩下最后一个
        $last_fish_array=Cache::get("last_fish_array");
        if(!is_array($last_fish_array)){$last_fish_array=[];}
        $last_fish_array=array_reverse($last_fish_array);
        foreach ($last_fish_array as $v){
            if(in_array($v,$admin_array)){
                $admin_array=array_diff($admin_array, [$v]);
                //删除数组后，如果数组空，则数据为最后一条，直接返回
                if(empty($admin_array)){
                    //$admin_id 存入接客名单，下次优先排除
                    $last_fish_array[]=$v;
                    if(count($last_fish_array)>20){
                        //接客名单维持在20个就好，多了没用
                        array_shift($last_fish_array);
                    }
                    Cache::set("last_fish_array",$last_fish_array);
                    return $v;
                }
            }
        }
        //排出后，数组内还有值，则随机指定
        $res=array_rand($admin_array);
        //$admin_id 存入接客名单，下次优先排除
        $last_fish_array[]= $admin_array[$res];
        if(count($last_fish_array)>20){
            //接客名单维持在20个就好，多了没用
            array_shift($last_fish_array);
        }
        Cache::set("last_fish_array",$last_fish_array);
        return $admin_array[$res];
    }

}