<?php

namespace ctbuh\History;

use Illuminate\Database\Eloquent\Model;
use ctbuh\History\History;
use Auth;

trait HistoryTrait {
	
	// EARLIEST values we remember existing on this model
	public $super_original = array();
	
	// basically super_original except including only changed fields even ones that don't necessarily exist in database: $model->custom_field...
	public $super_dirty = array();
	
	public function getSuperOriginal($key = null, $default = null){
		$original = !empty($this->super_original) ? $this->super_original : $this->getOriginal();
		
		if($key === null){
			return $original;
		}
		
		return array_key_exists($key, $original) ? $original[$key] : $default;
	}
	
	public function getSuperDirty($key = null, $default = null){
		$dirty = !empty($this->super_dirty) ? $this->super_dirty : $this->getDirty();
		
		if($key === null){
			return $dirty;
		}
		
		return array_key_exists($key, $dirty) ? $dirty[$key] : $default;
	}
	
	// https://github.com/laravel/framework/blob/9f7b062bc9c4dbe1c0b6076eed6997fc18f82bf2/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php#L1048
	public function getActualDirty($ignore = array() ){
		$dirty = $this->getDirty();
		$actual_dirty = array();
		
		foreach($dirty as $name => $value){
			$before = $this->getOriginal($name);
			
			// changing 0 to false or null to 0 does not make it dirty!
			if($before != $value){
				$actual_dirty[$name] = $value;
			}
		}
		
		return $actual_dirty;
	}
	
	public function history(){
		return $this->morphMany(History::class, 'model')->orderBy('created_at', 'desc');
    }
	
	public function logHistory($message, $type = 'custom', $extra = array() ){
		return static::doLog($this, $message, array(
			'type' => $type,
			'extra' => $extra
		));
	}
	
	protected static function doLog($model, $data, $params = array() ){
		
		// we want to allow empty to log empty package/tour selections
		if(empty($data) && !isset($params['allow_empty']) ){
			return null;
		}
		
		$record = new History();
		
		// who did it?
		if(Auth::check()){
			$record->setUser(Auth::user());
		}
		
		// to whom?
		if($model instanceof Model){
			$record->setModel($model);
		}
		
		// what sort of record is this? usually "update"
		$type = isset($params['type']) ? (string)$params['type'] : 'custom';
		
		$extra = array();
		if(isset($params['extra'])){
			$extra = array_merge($extra, $params['extra']);
		}
		
		return $record->logAction($data, $type, $extra);
	}
	
	// https://github.com/VentureCraft/revisionable/blob/master/src/Venturecraft/Revisionable/RevisionableTrait.php#L53
	public static function boot(){
		parent::boot();
		
		if(!method_exists(get_called_class(), 'bootTraits')){
			static::bootHistoryTrait();
		}
	}
	
	// not supported in laravel 4
	public static function bootHistoryTrait(){
		
		// is called when the model is saved for the first time.
		static::created(function($model){
			static::doLog($model, $model, array('type' => 'create'));
		});
		
		// save() or update() => saving updating updated saved
		//  save() or update() without any changes => saving saved
		static::updated(function($model){
			
			// https://github.com/laravel/framework/issues/4698
			if(empty($model->super_original)){
				$model->super_original = $model->getOriginal();
			}
			
			// dirty! -- TODO: move this below?
			foreach($model->getDirty() as $field => $value){
				// isset returns if key exist OR NULL!
				if(!array_key_exists($field, $model->super_dirty)){
					$model->super_dirty[$field] = $model->getOriginal($field); // TODO maybe set this to whatever first change was?
				}
			}
			
			//todo: replace with getFillable instead?
			$ignore = array('updated_at'); //$model->getDates();
			$changes = array();
			
			foreach($model->getDirty() as $col => $value){
				$original = $model->getOriginal($col);
				
				if(!in_array($col, $ignore) && $value != $original){
					$changes[$col] = array(
						'before' => $original,
						'after' => $value
					);
				}
			}
			
			// TODO: extra = Request::ip
			static::doLog($model, $changes, array(
				'type' => 'update' // TODO: update
			));
			
		});
		
		static::deleted(function($model){
			static::doLog($model, $model, array('type' => 'delete'));
		});
	}
}
