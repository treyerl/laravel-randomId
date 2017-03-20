<?php

namespace App;

use \Illuminate\Database\Eloquent\Builder;
use Psr\Log\InvalidArgumentException;


/**
 * Used for generation of binary primary keys. Requires users to set $model->incrementing to false
 * and adapt the primary key type to binary.
 */

trait RandomBinId {
	/**
 	 * Defines the length of the byte array ids to be generated.
	 *  Can be overriden by classes using this trait.
 	 *
 	 * @return int
	 */

	protected function getIdLength(){
		return 16;
	}

	/**
 	 * Generates random IDs
 	 *
 	 * @param type
 	 * @return binary
	 */
	private function generateRandomId(){
		do {
			$id = random_bytes($this->getIdLength());
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
		$this->getConnection()->transaction(function(){
			$this->setAttribute($this->getKeyName(), $this->generateRandomId());
			return parent::performInsert($query);
		});
	}

	/**
 	 * Get hexadecimal representation of this key.
 	 *
 	 * @return string
	 */

	public function getIdRepresentation(){
		return bin2hex($this->getKey());
	}

	/**
 	 * Convert hexadecimal representation to byte array to be used in queries.
 	 *
 	 * @return binary
	 */
	public static function getIdFromRepresentation($representation){
		return hex2bin($representation);
	}

}
