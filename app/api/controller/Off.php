<?php
declare (strict_types = 1);

namespace app\api\controller;


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

class Off extends ApiController
{
    //销售人员

	public function test()
	{
		return 'ok111';
	}

    public function off_card()
    {
        try {
            $user_id = $this->request->user_id;
            $param = $this->request->param();
            $sale=AppSale::where("user_id",$user_id)->find();
            if(!$sale){
              return $this->api_data('ERROR_PARAM', '', "不是销售人员");
           }
           if(!isset($param["text"])){
              return $this->api_data('ERROR_PARAM', '', "核销码错误");
           }
            $text=$param["text"];
            $coupon_id=Cache::get($text);
//            $coupon_id=41;
            if(!$coupon_id){
                return $this->api_data('ERROR_PARAM', '', "核销码错误");
            }
            $coupon_user_info=AppCouponUser::where("id",$coupon_id)->find();
            if($coupon_user_info["status"] != 1){
                return $this->api_data('ERROR_PARAM', '', "优惠券信息错误");
            }


//            Db::startTrans();
            $now=time();
//            $arr=[
//                "status"=>2,
//                "off_time"=>$now,
//                "off_sale_id"=>$sale["id"],
//                "off_address"=>$sale["address"],
//            ];
//            $save=AppCouponUser::where("id",$coupon_id)->save($arr);
//            if($save){
                $card_info=AppCard::where("id",$coupon_user_info["card_id"])->find();
                if($card_info["hid"] != $sale["hid"]){
                    return $this->api_data('ERROR_PARAM', '', "此优惠券不能在该酒店使用");
                }
                $return=[
                    "nickname"=>AppUser::where("id",$coupon_user_info["uid"])->value("nickname"),
                    "card_name"=>AppCard::where("id",$coupon_user_info["card_id"])->value("name"),
                    "card_no"=>AppCardUser::where("id",$coupon_user_info["card_user_id"])->value("card_no"),
                    "off_address"=>$sale["address"],
                    "off_time"=>date("Y-m-d H:i:s",$now),
                    "coupon_name"=>AppCoupon::where("id",$coupon_user_info["cou_id"])->value("name"),
                    "coupon_user_id"=>$coupon_id
                ];
//                Db::commit();
                return $this->api_data('SUCCESS',$return);
//            }else{
//                Db::rollback();
//                return $this->api_data('ERROR_PARAM', '', "保存失败");
//
//            }

        } catch (Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }
    }



    public function true_off()
    {
        try {
            $user_id = $this->request->user_id;
            $param = $this->request->param();
            $sale=AppSale::where("user_id",$user_id)->find();
            if(!$sale){
                return $this->api_data('ERROR_PARAM', '', "不是销售人员");
            }
//            if(!isset($param["text"])){
//                return $this->api_data('ERROR_PARAM', '', "核销码错误");
//            }
            $coupon_id=$param["coupon_user_id"]??false;
//            $text=$param["text"];
//            $coupon_id=Cache::get($text);
//            $coupon_id=41;
            if(!$coupon_id){
                return $this->api_data('ERROR_PARAM', '', "核销码错误");
            }
            $coupon_user_info=AppCouponUser::where("id",$coupon_id)->find();
            if($coupon_user_info["status"] != 1){
                return $this->api_data('ERROR_PARAM', '', "优惠券信息错误");
            }
            Db::startTrans();
            $card_info=AppCard::where("id",$coupon_user_info["card_id"])->find();
            if($card_info["hid"] != $sale["hid"]){
                Db::rollback();
                return $this->api_data('ERROR_PARAM', '', "此优惠券不能在该酒店使用");
            }
            $now=time();
            $arr=[
                "status"=>2,
                "off_time"=>$now,
                "off_sale_id"=>$sale["id"],
                "off_address"=>$sale["address"],
            ];
            $save=AppCouponUser::where("id",$coupon_id)->save($arr);
            if($save){

                $return=[
                    "nickname"=>AppUser::where("id",$coupon_user_info["uid"])->value("nickname"),
                    "card_name"=>AppCard::where("id",$coupon_user_info["card_id"])->value("name"),
                    "card_no"=>AppCardUser::where("id",$coupon_user_info["card_user_id"])->value("card_no"),
                    "off_address"=>$sale["address"],
                    "off_time"=>date("Y-m-d H:i:s",$now),
                    "coupon_name"=>AppCoupon::where("id",$coupon_user_info["cou_id"])->value("name"),
                ];
                    Db::commit();
                return $this->api_data('SUCCESS',$return);
            }else{
                Db::rollback();
                return $this->api_data('ERROR_PARAM', '', "保存失败");
            }
        } catch (Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }
    }






}
