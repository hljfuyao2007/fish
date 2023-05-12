<?php
declare (strict_types=1);

namespace app\api\controller;

use app\api\model\AppUser as MAppUser;
use app\api\model\AppUserFuns;
use app\api\model\AppTag as mAppTag;
use app\api\model\AppTagCate as mAppTagCate;
use app\api\model\AppTagUser as mAppTagUser;

use app\common\controller\ApiController;
use app\api\validate\User as VUser;
use app\api\model\AppUserAuth as MAppUserAuth;
use app\api\model\AppBalanceLog as MAppBalanceLog;

use think\Request;
use think\Response;
use think\response\Json;
use think\facade\Db;

class Search extends ApiController
{

    /**
	 * 获取用户列表(优先展示置顶用户)
	 *
	 * @param MAppUser $m_user
	 * @return Response
	 */
    public function get_list(MAppUser $m_user){
        $param = $this->request->param();
        
        try {
			$user=$m_user->where("id",$param["user_id"])->find();
            $s=$m_user
            ->field("id,nickname,avatar,sex,images,province,city,area,work,if(top_time>".time().",1,0) as is_top,id_num")
            ->where("sex","<>",$user["sex"])//异性
            ->where("id","<>",$user["id"])//屏蔽掉自己
            ->where("status",1)//屏蔽掉自己
            ->order("is_top asc,id desc")
            ->paginate();
			return $this->api_data('SUCCESS', $s);
		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
        
    }
    
    
    public function search(MAppUser $m_user){
        $param = $this->request->param();
        try {
			$user=$m_user->where("id",$param["user_id"])->find();
            $s=$m_user
            ->field("id,nickname,avatar,sex,images,province,city,area,work,if(top_time>".time().",1,0) as is_top,id_num");
            if(isset($param["sex"]) && ($param["sex"] ==0 || $param["sex"]==1)){
                $s=$s->where("sex",$param["sex"]);
            }
            if(isset($param["age_min"]) && $param["age_min"] !=""){
                $age_date=date("Y-m-d",strtotime("-".$param["age_min"]." year"));
                $s=$s->where("birthday","<=",$age_date);
            }
            
            if(isset($param["age_max"]) && $param["age_max"] !=""){
                $age_date=date("Y-m-d",strtotime("-".$param["age_max"]." year"));
                $s=$s->where("birthday",">=",$age_date);
            }
            
            
            if(isset($param["height_min"]) && $param["height_min"] !=""){
                $s=$s->where("height",">=",$param["height_min"]);
            }
            
            if(isset($param["height_max"]) && $param["height_max"] !=""){
                $s=$s->where("height","<=",$param["height_max"]);
            }
            
            // ->where("sex","<>",$user["sex"])
            $s=$s
            ->where("id","<>",$user["id"])//屏蔽掉自己
            ->order("is_top asc,id desc")
            ->where("status",1)//屏蔽掉自己
            ->paginate();
			return $this->api_data('SUCCESS', $s);
		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
        
    }
    
     /**
	 * 加关注
	 *
	 * @param MAppUser $m_user
	 * @return Response
	 */
    public function set_funs(){
        try {
            $param = $this->request->param();
            $funs=new AppUserFuns();
            if($funs->where("user_id",$param["user_id"])->where("for_id",$param["for_id"])->find()){
                return $this->api_data('CODE_EXCEPTION', '',"已经关注过了");
            }
            $funs->save([
                "user_id"=>$param["user_id"],
                "for_id"=>$param["for_id"],
                ]
            );
            	return $this->api_data('SUCCESS');
        } catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
        
    }
    
    /**
	 * 取消关注
	 *
	 * @param MAppUser $m_user
	 * @return Response
	 */
    public function out_funs(){
        try {
            $param = $this->request->param();
            $funs=new AppUserFuns();
            $funs->where("user_id",$param["user_id"])->where("for_id",$param["for_id"])->delete();
            // if($funs->where("user_id",$param["user_id"])->where("for_id",$param["for_id"])->find()){
            //     return $this->api_data('CODE_EXCEPTION', '',"已经关注过了");
            // }
            // $funs->save([
            //     "user_id"=>$param["user_id"],
            //     "for_id"=>$param["for_id"],
            //     ]
            // );
            	return $this->api_data('SUCCESS');
        } catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
        
    }
        
    /**
	 * 移除粉丝
	 *
	 * @param MAppUser $m_user
	 * @return Response
	 */
    public function out_follow(){
        try {
            $param = $this->request->param();
            $funs=new AppUserFuns();
            $funs->where("for_id",$param["user_id"])->where("user_id",$param["for_id"])->delete();
            	return $this->api_data('SUCCESS');
        } catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
    }
    
    /**
	 * 获取粉丝列表
	 *
	 * @param MAppUser $m_user
	 * @return Response
	 */
    public function get_funs(){
        try {
            $param = $this->request->param();
            $funs=new AppUserFuns();
            $res=$funs
            ->alias("f")
            ->join("app_user u","f.user_id=u.id")
            ->field(" u.id,u.avatar,u.nickname,u.id_num,u.sex,f.for_id as my_user_id,f.id as funs_id")
            ->where("for_id",$param["user_id"])
            
            ->where("u.status",1)//已关闭的账号屏蔽
            
            ->order("funs_id desc")
            ->select()->each(function($item){
                $funs_type=AppUserFuns::where("user_id",$item->id)->where("user_id",$item->my_user_id)->value("id");
                if($funs_type){
                    $item->funs_type=1;
                }else{
                    $item->funs_type=0;
                }
            });
            return $this->api_data('SUCCESS',$res);
        } catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
        
    }
    
    
    /**
	 * 我的关注列表
	 *
	 * @param MAppUser $m_user
	 * @return Response
	 */
    public function get_follow(){
        try {
            $param = $this->request->param();
            $funs=new AppUserFuns();
            $res=$funs
            ->alias("f")
            ->join("app_user u","f.for_id=u.id")
            ->field(" u.id,u.avatar,u.nickname,u.id_num,u.sex,f.user_id as my_user_id,f.id as funs_id")
            ->where("user_id",$param["user_id"])
            ->order("funs_id desc")
            
            ->where("u.status",1)//已关闭的账号屏蔽
            
            ->select()->each(function($item){
                $funs_type=AppUserFuns::where("user_id",$item->id)->where("for_id",$item->my_user_id)->value("id");
                if($funs_type){
                    $item->funs_type=1;
                }else{
                    $item->funs_type=0;
                }
            });
            return $this->api_data('SUCCESS',$res);
        } catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
        
    }
    
    /**
	 * 标签匹配
	 *
	 * @param MAppUser $m_user
	 * @return Response
	 */
    
    public function  mate_by_tag(){
        try {
            $user_id = $this->request->user_id;
    		$param = $this->request->param();
    	    
    	    $user=MAppUser::find($user_id);
    	    $tag_arr=mAppTagUser::where("user_id",$user_id)->column("tag_id");
    	    $user_arr=mAppTagUser::alias("tu")
    	    ->whereIn("tu.tag_id",$tag_arr)
    	    ->join("app_user u","tu.user_id=u.id")
    	    ->group("tu.user_id")
    	    ->field("SUM(1) as sum,u.id,u.avatar,u.nickname,u.id_num,u.sex,if(u.top_time>".time().",1,0) as is_top")
    	    //->where("tu.user_id","<>",$user_id)
    	    ->where("u.sex","<>",$user["sex"])//只匹配异性
    	    ->order("is_top,desc,sum desc")
    	    
    	    ->where("u.status",1)//已关闭的账号屏蔽
    	    
    	    ->select();

    	   // $return=mAppTag::whereIn("id",$tag_arr)->field("id,name,cate_id,hot")->select();
    	    return $this->api_data('SUCCESS',$user_arr);
        } catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
    
        
        
    }
    
    /**
	 * 择偶标准匹配 
	 *
	 * @param MAppUser $m_user
	 * @return Response
	 */
    public function  mate_by_fortype(){
        $user_id = $this->request->user_id;
		$param = $this->request->param();
	    $user=MAppUser::find($user_id);
	    $m_user=new MAppUser();
        try {
            $s=$m_user
            ->field("id,nickname,avatar,sex,images,province,city,area,work,if(top_time>".time().",1,0) as is_top,height,weight,birthday,id_num");
            // if(isset($param["sex"]) && ($param["sex"] ==0 || $param["sex"]==1)){
            //     $s=$s->where("sex",$param["sex"]);
            // }
            if($user["for_age_min"] !=""){
                $age_date=date("Y-m-d",strtotime("-".$user["for_age_min"]." year"));
                $s=$s->where("birthday","<=",$age_date);
            }
            
            if( $user["for_age_max"] !=""){
                $age_date=date("Y-m-d",strtotime("-".$user["for_age_max"]." year"));
                $s=$s->where("birthday",">=",$age_date);
            }
            
            
            if( $user["for_height_min"] !=""){
                $s=$s->where("height",">=",$user["for_height_min"]);
            }
            if( $user["height_max"] !=""){
                $s=$s->where("height","<=",$user["for_height_min"]);
            }
            
            // ->where("sex","<>",$user["sex"])
            $s=$s
            //->where("id","<>",$user["id"])//屏蔽掉自己
            ->where("sex","<>",$user["sex"])//只显示异性
            ->order("is_top asc,id desc")
            
            ->where("status",1)//已关闭的账号屏蔽
            
            ->paginate();
			return $this->api_data('SUCCESS', $s);
		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
        
        
    }
    
    
    public function look(){
        $user_id = $this->request->user_id;
		$param = $this->request->param();
		$for_id=$param["for_id"];
		$res=Db::name("look_for")->where("user_id",$user_id)->where("for_id",$for_id)->find();
		if($res){
		    Db::name("look_for")->where("id",$res["id"])->update(["update_time"=>time()]);
		}else{
		    Db::name("look_for")->where("user_id",$user_id)->where("for_id",$for_id)->insert([
		            "create_time"=>time(),
		            "update_time"=>time(),
		            "user_id"=>$user_id,
		            "for_id"=>$for_id,
		        ]);
		}
        return $this->api_data('SUCCESS');
    }
    
    
    public function get_look(){
        try {
            $param = $this->request->param();
            $user=new AppUser();
            $res=$user
            ->alias("u")
            ->join("look_for l","l.user_id=u.id")
            ->field(" u.id,u.avatar,u.nickname,u.id_num,u.sex,l.user_id as my_user_id,l.update_time as look_update_time")
            ->where("user_id",$param["user_id"])
            ->order("look_update_time desc")
            
            ->where("u.status",1)//已关闭的账号屏蔽
            ->paginate()->each(function($item){
               
            });
            return $this->api_data('SUCCESS',$res);
        } catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
        
    }
    
   


}
