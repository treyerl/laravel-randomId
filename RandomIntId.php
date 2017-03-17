<?php

namespace App;

use \Illuminate\Database\Eloquent\Builder;

/**
 * RandomId: Adds random generation of integer primary keys. To be used in model classes only.
 * Requires users to set $model->incrementing to false and adapt the primary key type to bigInteger
 * for an id length >= 10 (32bit). Undefined behaviour for id length >= 20 (64bit).
 */

trait RandomIntId {

	/**
 	 * Defines the length of the generated ids. Can be overriden by classes using this trait.
 	 *
 	 * @return int
	 */

	protected function getIdLength(){
		return 12;
	}

	/**
 	 * Generates random IDs
 	 *
 	 * @param type
 	 * @return int
	 */
	private function generateRandomId(){
		$l = $this->getIdLength();
		do {
			$id = random_int("1".str_repeat("0", $l-1), str_repeat("9", $l));
		} while( parent::where($this->getKeyName(), $id)->exists() );
		return $id;
	}

	/**
     * Perform a model insert operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
	protected function performInsert(Builder $query){
		$this->setAttribute($this->getKeyName(), $this->generateRandomId());
		return parent::performInsert($query);
	}
}
