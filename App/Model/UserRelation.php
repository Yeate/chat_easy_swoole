<?php
namespace App\Model;


class UserRelation extends Model
{
	protected $table = 'user_relation';

	protected $fillable = ['a_user_id','b_user_id'];

	public function scopeIsFriend($query,$a_user_id,$b_user_id){
		return $query->where(function($where)use($a_user_id,$b_user_id){
			$where->where('a_user_id',$a_user_id)->where('b_user_id',$b_user_id);
		})->orWhere(function($where)use($a_user_id,$b_user_id){
			$where->where('b_user_id',$a_user_id)->where('a_user_id',$b_user_id);
		});
	}
}