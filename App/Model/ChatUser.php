<?php
namespace App\Model;


class ChatUser extends Model
{
	protected $table = 'chat_users';

	protected $fillable = ['name','email','nickname','password'];

	protected $hidden = ['password','created_at','updated_at'];
}