<?php
namespace App\Model;


class UserMessage extends Model
{
	protected $table = 'user_message';

	protected $fillable = ['from_user_id','to_user_id','msg','type','status'];

	public function scopeIsSendAdd($query,$a_user_id,$b_user_id){
		return $query->where(function($where)use($a_user_id,$b_user_id){
			$where->where('from_user_id',$a_user_id)->where('to_user_id',$b_user_id)->where('type','2')->where('status',0);
		})->orWhere(function($where)use($a_user_id,$b_user_id){
			$where->where('to_user_id',$a_user_id)->where('from_user_id',$b_user_id)->where('type','2')->where('status',0);
		});
	}
}