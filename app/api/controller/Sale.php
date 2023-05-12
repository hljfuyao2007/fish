<?php
declare (strict_types = 1);

namespace app\api\controller;


use app\admin\controller\app\CouponUser;
use app\admin\model\AppHotel;
use app\admin\model\AppSale;
use app\api\model\AppBanner as MAppBanner;
use app\api\model\AppNotice as MAppNotice;

use app\common\controller\ApiController;

use Exception;
use think\facade\Cache;
use think\facade\Db;
use think\Request;
use think\response\Json;
use app\admin\model\AppCard;
use app\admin\model\AppCardUser;
use app\admin\model\AppCoupon;
use app\admin\model\AppCouponType;
use app\admin\model\AppCouponUser;
use app\admin\model\AppUser;
//use app\extra\phpcode\QrCode;
use app\extra\QrCode;
use app\admin\model\AppCouponZhOrder;

class Sale extends ApiController
{
    //销售人员

	public function test()
	{
		return 'ok111';
	}

    public function card_number(){
        //我的卡片数量
        try {
            $user_id = $this->request->user_id;
            $param = $this->request->param();
            $sale=AppSale::where("user_id",$user_id)->find();
            if(!$sale){
                return $this->api_data('ERROR_PARAM', '', "不是销售人员");
            }
            $count_1=AppCardUser::where("sid",$sale["id"])->where("uid",0)->count();
            $count_2=AppCardUser::where("sid",$sale["id"])->where("uid",">",0)->count();

            $res=[
                "num_all"=>$count_1+$count_2,
                "num_1"=>$count_1,
                "num_2"=>$count_2,
            ];
            return $this->api_data('SUCCESS', $res);

        } catch (Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }

    }

    public function mycard(){
        //我的卡片
        try {
            $user_id = $this->request->user_id;
            $param = $this->request->param();
            $type=$param["type"]??0;
            $sale=AppSale::where("user_id",$user_id)->find();
            if(!$sale){
                return $this->api_data('ERROR_PARAM', '', "不是销售人员");
            }


            if(isset($param["keyword"])){
                $arr=[];
                if(is_numeric($param["keyword"])){
                    $arr[]=" u.card_no like '%".$param["keyword"]."%' ";
//                    $arr[]=" phone like '%".$param["keyword"]."%' ";
                }
                $arr[]=" c.name like '%".$param["keyword"]."%' ";
                $arr[]=" c.discount like '%".$param["keyword"]."%' ";

            }

            // return $this->api_data('SUCCESS', $sale);
            $res=AppCardUser::alias("u")
                ->join("app_card c","u.cid=c.id","left")
                ->where("u.sid",$sale["id"]);
            if($type && $type != 0){
                if($type == 1){
                    //未出售
                    $res=$res->field("u.*,c.name,c.long");
                    $res=$res->where("u.uid",0);
                }else{
                    if(isset($param["keyword"])){
                        if(is_numeric($param["keyword"])){
                            $arr[]=" user.phone like '%".$param["keyword"]."%' ";
                        }
                        $arr[]=" user.nickname like '%".$param["keyword"]."%' ";
//                        if( preg_match("/^d{4}-d{2}-d{2} d{2}:d{2}:d{2}$/s",$param["keyword"])){
//                            $arr[]=" c.create_time =".$param["keyword"];
//                        }
                    }
                    //已出售
                    $res=$res->field("u.*,c.name,c.long,user.nickname,user.phone");
                    $res=$res->join("app_user user","u.uid=user.id","left");
                    $res=$res->where("u.uid",">",0);
                }
            }
            if(isset($param["keyword"])){
                $where_str=implode(" or ",$arr);
                $res=$res->where($where_str);
            }
//
//            if(isset($param["keyword"])){
//                $str="u.card_no=".$param["keyword"]." or c.name like %".$param["keyword"]."%";
//                $res=$res->where($str);
//            }
            $res=$res->paginate()->each(function($item){
//                $card_info=AppCard::where("id",$item["cid"])->find();
                $item["long"] = ($item["long"] === 0)?"长期有效":($item["long"]."天");
                $item["card_type"]=strpos($item["name"],"金")===0?"金卡":"银卡";
                return $item;
            });
            return $this->api_data('SUCCESS', $res);

        } catch (Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }

    }


    /**
     * 我的卡片->卡片详情
     * @return Json
     */
    public function card_detail()
    {
        try {
            $user_id = $this->request->user_id;
            $param = $this->request->param();

            $sale=AppSale::where("user_id",$user_id)->find();
            if(!$sale){
                return $this->api_data('ERROR_PARAM', '', "不是销售人员");
            }
            $card_user_id=$param["card_user_id"];
            $card_user_info=AppCardUser::where("id",$card_user_id)->find();
            $card_info=AppCard::where("id",$card_user_info["cid"])->find();
            $card_user_info["title"]=$card_info["name"];
            $card_user_info["img"]=$card_info["img"];
//            $card_user_info["sale"]=AppSale::where("id",$card_user_info["sid"])->value("name");
            $card_sale_info=AppSale::where("id",$card_user_info["sid"])->find();
            $card_user_info["sale"]=$card_sale_info["name"];
            $card_user_info["sale_phone"]=AppUser::where("id",$card_sale_info["user_id"])->value("phone");
            $card_user_info["sale_image"]=$card_sale_info["img"];
            if($card_user_info["uid"] == 0){
                $card_user_info["end_time"]=$card_info["long"] == 0?"长期有效":date("Y-m-d",strtotime("+".$card_info["long"]." day"));
            }else{
                $card_user_info["end_time"]=$card_user_info["end_time"] == 0?"长期有效":date("Y-m-d",$card_user_info["end_time"]);
            }


            $card_user_info["long"]=$card_info["long"] == 0?"长期有效":($card_info["long"]."天");
            $status_arr=[0=>"已停用",1=>"使用中",4=>"已过期"];
            $card_user_info["status_text"]=$status_arr[$card_user_info["status"]];
//            if($card_user_info["status"] == 1 && ($card_user_info["end_time"]!=0 && $card_user_info["end_time"]<time())){
//                $card_user_info["status_text"]="已过期";
//            }
            $cou=AppCouponUser::where("card_user_id",$param["card_user_id"])->where("status",2)->find();
            if($cou){
                $card_user_info["cou_used"]="已使用";
            }else{
                $card_user_info["cou_used"]="未使用";
            }
            $card_user_info["content_zhu"]=$card_info["content_zhu"];
            $card_user_info["content_chi"]=$card_info["content_chi"];
            $card_user_info["content_li"]=$card_info["content_li"];
            $card_user_info["content_zhe"]=$card_info["content_zhe"];

            if($card_user_info["uid"] > 0){
                $card_user_info["jihuo_type"]="已激活";
                    $card_user_info["sale_type"]="已销售";
                $card_user_info["sale_time"]=date("Y-m-d",$card_user_info["start_time"]);
                $card_user_info["user_info"]=AppUser::where("id",$card_user_info["uid"])
                    ->field("nickname,phone,if(openid!='',1,0) as if_wx,avatar")
                    ->find()->toArray();
            }else{
                $card_user_info["jihuo_type"]="未激活";
                $card_user_info["sale_type"]="未销售";
            }


            $card_user_info["continue"]=null;
            if($card_user_info["continue_id"] > 0){
                $continue=AppCardUser::where("id",$card_user_info["continue_id"])->find();
                $c=[];
                $card_user_info["continue"]=[];
                $c["end_time"]=$continue["end_time"] == 0?"长期有效":date("Y-m-d",$continue["end_time"]);
                $c["card_no"]=$continue["card_no"];
                $c["continue_time"]=date("Y-m-d",$card_user_info["continue_time"]);
                $card_user_info["continue"]=$c;
            }


            return $this->api_data('SUCCESS', $card_user_info);

        } catch (Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }
    }



    /**
     * 我的卡片->卡片详情2
     * @return Json
     */
    public function card_detail2()
    {
        try {
            $user_id = $this->request->user_id;
            $param = $this->request->param();
//          $user_id=$param["user_id"];
            $this->coupon_over_day=sysconfig("app","coupon_over_day");
            $card_user_id=$param["card_user_id"];

            $card_info=AppCardUser::where("id",$card_user_id)->find();
            $hid=AppCard::where("id",$card_info["cid"])->value("hid");
            $this->hotel_name=AppHotel::where("id",$hid)->value("name");

            $response=(new AppCouponUser)
                ->alias("u")
                ->where("u.card_user_id",$card_user_id)
                ->join("app_coupon c","u.cou_id=c.id","left")
                ->field("u.id,c.name,c.img,u.create_time,u.create_time as start_time,u.end_time,u.status")
                ->select()
                ->each(function ($item){
                    $status_arr=[1=>"未使用",2=>"已使用",3=>"已转化",4=>"已过期"];
                    $item["start_time"]=date("Y-m-d H:i:s",$item["start_time"]);
                    $item["status_text"]=$status_arr[$item["status"]];
                    $item["hotel_name"]=$this->hotel_name;
                    $item["over_str"]='';
                    if($item["end_time"] == null){
                        $item["time_str"]=date("Y.m.d",strtotime($item["create_time"]))."-"."不限";
                    }else{
                        $item["time_str"]=date("Y.m.d",strtotime($item["create_time"]))."-".date("Y.m.d",$item["end_time"]);
                        $d= (int) $this->coupon_over_day;
                        if($item["end_time"] < (time()+$d*60*60*24)){
                            $item["over_str"]=floor(($item["end_time"]-time())/(60*60*24))."天后失效";
                        }
                    }
                    $item["end_time"]=$item["end_time"] == 0?"长期有效":date("Y-m-d H:i:s",$item["end_time"]);

                    return $item;
                });
//            if($response){
//                $response=$response->toArray();
//            }
            $coupon=[1=>[],2=>[],4=>[]];
            foreach ($response as $key=>$value){
                if(in_array($value["status"],[1,2,4])){
                    $coupon[$value["status"]][]=$value;
                }
            }
            $card_info["coupon"]=$coupon;
            return $this->api_data('SUCCESS', $card_info);

        } catch (Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }
    }


    public function zh_coupon_list()
    {
        try {
            $user_id = $this->request->user_id;
            $param = $this->request->param();
            $card_user_id=$param["coupon_user_id"];
            $CouponUser_info=AppCouponUser::where("id",$card_user_id)->find();
            $hotel_id=AppCard::where("id",$CouponUser_info["card_id"])->value("hid");

          $list=AppCoupon::alias("cou")
              ->join("app_card c","cou.card_id=c.id","left")
              ->where("c.hid",$hotel_id)
              ->field("cou.id,cou.name")
              ->where("cou.status",1)
              ->where("cou.id","<>",$CouponUser_info["cou_id"])
              ->select();
            return $this->api_data('SUCCESS', $list);

        } catch (Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }
    }

    //转化转化优惠券
    public function zh_coupon()
    {
        try {
            $user_id = $this->request->user_id;
            $param = $this->request->param();
            $sale=AppSale::where("user_id",$user_id)->find();
            if(!$sale){
                return $this->api_data('ERROR_PARAM', '', "不是销售人员");
            }
            $coupon_user_id=$param["coupon_user_id"];
            $new_id=$param["new_id"];
            $CouponUser_info=AppCouponUser::where("id",$coupon_user_id)->find();
            if($CouponUser_info["status"] != 1){
                return $this->api_data('ERROR_PARAM', '', "优惠券状态错误");
            }
            AppCouponUser::where("id",$coupon_user_id)->save(["status"=>3]);

            $new_coupon_info=AppCoupon::where("id",$new_id)->find();
            $new_info=(new AppCouponUser)->create([
                "cou_id"=>$new_id,
                "uid"=>$CouponUser_info["uid"],
                "end_time"=>$CouponUser_info["end_time"],
                "status"=>1,
                "card_id"=>$new_coupon_info["card_id"],
                "type_id"=>$new_coupon_info["type_id"],
                "card_user_id"=>$CouponUser_info["card_user_id"],
                "zh_id"=>$CouponUser_info["id"],
            ]);
            AppCouponZhOrder::create([
                "from_cou_id"=>$CouponUser_info["cou_id"],
                "to_cou_id"=>$new_id,
                "from_coupon_user_id"=>$CouponUser_info["id"],
                "to_coupon_user_id"=>$new_info["id"],
                "uid"=>$CouponUser_info["uid"],
                "card_id"=>$new_coupon_info["card_id"],
                "card_user_id"=>$CouponUser_info["card_user_id"],
                "sale_id"=>$sale["id"]

            ]);
            return $this->api_data('SUCCESS',$new_info);

        } catch (Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }
    }
    /*获取名下的用户列表*/
    public function user_list(){
        try {
            $user_id = $this->request->user_id;
            $param = $this->request->param();
            $sale=AppSale::where("user_id",$user_id)->find();
            if(!$sale){
                return $this->api_data('ERROR_PARAM', '', "不是销售人员");
            }
            $res=AppUser::where("sale_id",$sale["id"])->field("id,nickname,phone,avatar,sex,live_address");
            if(isset($param["is_page"]) && $param["is_page"] == 1){
                $res=$res->paginate();
            }else{
                $res=$res->select();
            }
            $res->each(function($item){
                $item["card"]=AppCardUser::where("uid",$item["id"])->column("card_no");
            });
            return $this->api_data('SUCCESS',$res);
        } catch (Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }
    }

    /*名下的卡片给到用户*/
    public function card_to_user(){
        try {
            $user_id = $this->request->user_id;
            $param = $this->request->param();
            $sale=AppSale::where("user_id",$user_id)->find();
            if(!$sale){
                return $this->api_data('ERROR_PARAM', '', "不是销售人员");
            }


            if(isset($param["uid"])){
                $uid=$param["uid"];
            }elseif($param["phone"]){
                $uid=AppUser::where("phone",$param["phone"])->value("id")??false;
                if(!$uid){
                    return $this->api_data('ERROR_PARAM', '', "未查询到用户或手机号输入错误");
                }
            }else{
                return $this->api_data('ERROR_PARAM', '', "未获取到用户信息");
            }
            $user_sale=AppUser::where("id",$uid)->value("sale_id");
            if($user_sale == 0){
                //空白用户绑定到自己名下( 不许绑定20220827日改 )
                return $this->api_data('ERROR_PARAM', '', "该用户不在你名下");
//                AppUser::where("id",$uid)->save(["sale_id"=>$sale["id"]]);
            }elseif($user_sale !== $sale["id"]){
                return $this->api_data('ERROR_PARAM', '', "该用户已绑定其他销售人员");
            }

            if(isset($param["card_user_id"])){
                $res=AppCardUser::where("id",$param["card_user_id"])->find();
            }elseif(isset($param["card_no"])){
                $res=AppCardUser::where("card_no",$param["card_no"])->find();
            }else{
                return $this->api_data('ERROR_PARAM', '', "卡片信息错误");
            }
            if($res["uid"]!=0 || $res["sid"] !=$sale["id"]){
                return $this->api_data('ERROR_PARAM', '', "此卡片不是你名下的未售卡片");
            }

            $card_info=AppCard::where("id",$res["cid"])->find();
            Db::startTrans();
//            $card_no=get_cardno($card_info["hid"]);

            $arr=[
                "uid"=>$uid,
                "discount"=>$card_info["discount"],
                "end_time"=>$card_info["long"]==0?0:(time()+$card_info["long"]*24*60*60),
                "start_time"=>time(),
            ];

            $save=AppCardUser::where("id",$res["id"])->save($arr);

            if(!$save){
                Db::rollback();
                return $this->api_data('ERROR_PARAM', '', "保存失败");
            }
            $coupon=AppCoupon::where("card_id",$card_info["id"])->select();
            //为用户添加优惠券
            $c_arr=[];

            foreach ($coupon as $v){
                for($i=0;$i<$v["num"];$i++){
                    $c_arr[]=[
                        "cou_id"=>$v["id"],
                        "uid"=>$uid,
                        "end_time"=>$v["long"]==0?0:(time()+$v["long"]*24*60*60),
                        "start_time"=>time(),
                        "card_id"=>$card_info["id"],
                        "type_id"=>$v["type_id"],
                        "card_user_id"=>$res["id"]
                    ];
                }
            }
//  print_r($c_arr) ;exit;
            $save = (new AppCouponUser)->saveAll($c_arr);
            Db::commit();

            return $this->api_data('SUCCESS');
        } catch (Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }
    }


    /*续卡*/
    public function continue_card(){
        try {
            $user_id = $this->request->user_id;
            $param = $this->request->param();
            $sale=AppSale::where("user_id",$user_id)->find();
            if(!$sale){
                return $this->api_data('ERROR_PARAM', '', "不是销售人员");
            }
//           $uid=$param["uid"];
            if(isset($param["card_user_id"])){
                $res=AppCardUser::where("id",$param["card_user_id"])->find();
            }elseif(isset($param["card_no"])){
                $res=AppCardUser::where("card_no",$param["card_no"])->find();
            }else{
                return $this->api_data('ERROR_PARAM', '', "卡片信息错误");
            }
            if(!$res || $res["status"] != 1){
                return $this->api_data('ERROR_PARAM', '', "卡片信息错误");
            }

            $card_info=AppCard::where("id",$res["cid"])->find();
            $uid=$res["uid"];
            Db::startTrans();
            $card_no=get_cardno($card_info["hid"]);
            $now=time();
            $c_time=$res["end_time"]>$now?$res["end_time"]:$now;//上一张卡如果没到期，在上一张卡的基础上续时间
            $arr=[
                "card_no"=>$card_no,
                "cid"=>$card_info["id"],
                "uid"=>$uid,
                "sid"=>$sale["id"],
                "discount"=>$card_info["discount"],
                "end_time"=>$card_info["long"]==0?0:($c_time+$card_info["long"]*24*60*60),
                "continue_time"=>time(),
                "continue_num"=>($res["continue_num"]+1),
                "continue_id"=>$res["id"],
                "start_time"=>time(),
            ];

            AppCardUser::where("id",$res["id"])->save(["status"=>0]);//停用之前的饿卡片
            $save=AppCardUser::create($arr);
            if(!$save){
                Db::rollback();
                $this->error('保存失败');
            }
            $coupon=AppCoupon::where("card_id",$card_info["id"])->select();
            //为用户添加优惠券
            $c_arr=[];
            foreach ($coupon as $v){
                for($i=0;$i<$v["num"];$i++){
                    $c_arr[]=[
                        "cou_id"=>$v["id"],
                        "uid"=>$uid,
                        "end_time"=>$v["long"]==0?0:(time()+$v["long"]*24*60*60),
                        "start_time"=>time(),
                        "card_id"=>$card_info["id"],
                        "type_id"=>$v["type_id"],
                        "card_user_id"=>$res["id"]
                    ];
                }
            }
            $save = (new AppCouponUser)->saveAll($c_arr);
            Db::commit();

            return $this->api_data('SUCCESS');
        } catch (Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }
    }








}
