<?php namespace Terbium\DbConfig\Interfaces;

interface DbProviderInterface {

	/**
	 * Load the given configuration collection.
	 *
	 * @param  string  $collection
	 * @return array
	 */
	public function load($collection = null);

	/**
	 * Save item to the database or update the existing one
	 *
	 * @param      $key
	 * @param      $value
	 * @param null $server
	 * @param null $database
	 *
	 * @return mixed
	 */
	public function store($key, $value , $server = null, $database = null);

	/**
	 * Remove item from the database
	 * @param      $key
	 * @param null $server
	 * @param null $database
	 *
	 * @return mixed
	 */
	public function forget($key , $server = null, $database = null);

	/**
	 * Clear the table with settings
	 *
	 * @param null $database
	 *
	 * @return mixed
	 */
	public function clear($database = null);

	/**
	 * Clear the table with settings
	 * @return mixed
	 */
	public function clearAll();


}
