<?php

namespace App;

use \Illuminate\Database\Eloquent\Builder;
use Psr\Log\InvalidArgumentException;

/**
 * Used for generation of UUID primary keys in Eloquent models.
 * Requires users to set $model->incrementing to false
 * and set the column type to binary.
 */

trait RandomUuid {
	/**
 	 * Generates random IDs
 	 *
 	 * @param type
 	 * @return binary
	 */
	private function generateRandomId(){
		do {
			$id = random_bytes(16);
			$id[6] = chr(ord($id[6]) & 0x0f | 0x40); // set version to 0100 (version 4 = random bytes)
    		$id[8] = chr(ord($id[8]) & 0x3f | 0x80); // set bits 6-7 to 10
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
 	 * Typical hexadeical UUID representation with dashes.
 	 *
 	 * @return string
	 */

	public function getIdRepresentation(){
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($this->getKey()), 4));
	}

	/**
 	 * Transforms hexadecimal UUID representation into binary representation.
	 *
 	 * @return binary
	 */

	public static function getIdFromRepresentation($representation){
		return hex2bin(str_replace("-", "", $representation));
	}

}
