<?php

class Db {
	//database connection
	private static $connection;
	
	//default setting of PDO driver
	private static $settings = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	
	//connect to db - singeltonously
	public static function connect($host, $user, $password, $database) {
		if (!isset(self::$connection)) {
			self::$connection = @new PDO("mysql:host=$host;dbname=$database", $user, $password, self::$settings);
			
			return true;
		}
		
		return false;
	}
	
	//return one row
	/**
	 * @param string $query
	 * @param array  $parameters
	 *
	 * @return array
	 */
	public static function queryOne($query, $parameters = []) {
		try {
			$result = self::$connection->prepare($query);
			$result->execute($parameters);
			
			return $result->fetch();
		} catch (PDOException $e) {
			self::reportProblem($e);
			
			return false;
		}
	}
	
	//return all result as array of associative arrays
	/**
	 * @param string $query
	 * @param array  $parameters
	 *
	 * @return array
	 */
	public static function queryAll($query, $parameters = []) {
		try {
			$result = self::$connection->prepare($query);
			$result->execute($parameters);
			
			return $result->fetchAll();
		} catch (PDOException $e) {
			self::reportProblem($e);
			
			return false;
		}
	}
	
	//return first column from first row in result
	/**
	 * @param string $query
	 * @param array  $parameters
	 *
	 * @return string
	 */
	public static function querySingleOne($query, $parameters = []) {
		try {
			$result = Db::queryOne($query, $parameters);
			
			return $result[0];
		} catch (PDOException $e) {
			self::reportProblem($e);
			
			return false;
		}
	}
	
	//method for INSERT and UPDATE
	/**
	 * @param string $query
	 * @param array  $parameters
	 *
	 * @return bool
	 */
	public static function queryModify($query, $parameters = []) {
		try {
			$result = self::$connection->prepare($query);
			$result->execute($parameters);
			
			return true;
		} catch (PDOException $e) {
			self::reportProblem($e);
			
			return false;
		}
	}
	
	//automatic reporting errors into DB (what about errors in DB? ok... :D)
	private static function reportProblem(PDOException $e) {
		$trace = $e->getTrace();
		$DBcall = $trace[1];
		$functionCall = $trace[2];
		$type = 'error with DB';
		$function = $DBcall['function'].' into '.$functionCall['function'].' in file '.$functionCall['file'];
		$message = serialize($DBcall['args']);
		Db::queryModify('INSERT INTO tickets (`type`, `title`, `message`, `timestamp`)
                         VALUES (?,?,?,NOW())', [$type, $function, $message]);
	}
}