<?php

namespace ctbuh\History;

use Illuminate\Database\Eloquent\Model;

// http://www.laravel-auditing.com/docs/5.0/getting-audits
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
		return $this;
	}
	
	public function setModel($model){
		$this->model()->associate($model);
		return $this;
	}
	
	// history()->setUser("gdfgdf")->setModel("gdfgdfg")->logAction("msg", "type", "extra")
	public function logAction($message, $type = 'custom', $extra = array() ){
		return $this->log($message, $type, $extra);
	}
	
	public function log($message, $type = 'custom', $extra = array() ){
		$this->type = $type;
		$this->data = $message;
		$this->extra = $extra;
		$this->save();
		
		return $this;
	}
}

