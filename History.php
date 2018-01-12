<?php

namespace ctbuh;

use Illuminate\Database\Eloquent\Model;

class History extends Model {
	
	protected $table = 'history';
	protected $fillable = array('data', 'extra');
	
	protected $casts = array(
		'data' => 'array',
		'extra' => 'array'
	);
	
	// better return Authenticatable
	public function user(){
		return $this->morphTo();//->withTrashed();
	}
	
	// return Eloquent\Model
	public function model(){
		return $this->morphTo();//->withTrashed();
	}
}

