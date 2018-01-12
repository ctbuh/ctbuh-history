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
	
	public function setUser($user){
		$this->user()->associate($user);
	}
	
	public function setModel($model){
		$this->model()->associate($model);
	}
	
	public function log($message, $type = 'custom', $extra = array() ){
		$this->type = $type;
		$this->data = $message;
		$this->extra = $extra;
		
		return $this->save();
	}
}

