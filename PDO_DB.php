<?php
/**
 * Created by PhpStorm.
 * User: jworsley
 * Date: 5/30/16
 * Time: 9:31 PM
 * Some code referenced from https://github.com/indieteq/indieteq-php-my-sql-pdo-database-class/blob/master/Db.class.php
 */

/** Example settings file
 * [database]
 * driver = driver (pgsql, mysql for ex.)
 * host = host (localhost for ex.)
 * port = 3306
 * db_name = db_name
 * username = username
 * password = password
 * [db_attr]
 * PDO::ATTRIBUTE_HERE = VALUE
 */

class PDO_DB
{
	// @object Database handle
	private $dbh;
	// @string Configuration file for database connection
	private $settings_file;
	// @array PDO settings
	private $settings;
	// @array PDO attributes
	private $attributes = array();
	// @bool Connection check
	private $connected = FALSE;
	// @obj PDO statement object
	private $statement;
	// @array SQL query parameters
	private $parameters;

	/**
	 * PDO_DB constructor.
	 * @param string $settings_file
	 */
	public function __construct($settings_file = 'settings.ini')
	{
		$this->settings_file = $settings_file;
		$this->connect($this->settings_file);
	}

	/**
	 * @param $settings_file
	 * @throws exception
	 */
	private function connect($settings_file) {
		if(!$this->settings = parse_ini_file($settings_file)) {
			throw new exception("Unable to open file: $settings_file");
		}
		$dns = "$this->settings['database']['driver']:host=$this->settings['database']['host']";
		if (!empty($this->settings['database']['port'])) {
			$dns .= ";port=$this->settings['database']['port']";
		}
		$dns .= ";dbname=$this->settings['database']['db_name']";
		if ($this->settings['db_attr']) {
			$this->attributes = $this->settings['db_attr'];
		}
		try {
			$this->dbh = new PDO($dns, $this->settings['user'], $this->settings['password'], $this->attributes);
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->connected = TRUE;
		}
		catch (PDOException $e) {
			echo $e->getMessage();
			die();
		}
	}

	/**
	 * Close the connection
	 */
	public function closeConnection() {
		$this->dbh = NULL;
	}


	public function bind($param, $value) {
		$this->parameters[sizeof($this->parameters)] = [":$param", $value];
	}

	/**
	 * @param $params
	 */
	private function bindParams($params) {
		if (empty($this->params) && is_array($params)) {
			foreach ($params as $column => $value) {
				$this->bind($column, $value);
			}
		}
	}

	/**
	 * @param $query
	 * @param string $parameters
	 * @throws exception
	 */
	private function init($query, $parameters = '') {
		if(!$this->connected) {
			$this->connect($this->settings_file);
		}
		try {
			$this->statement = $this->dbh->prepare($query);
			$this->bindParams($parameters);

			if (!empty($this->parameters)) {
				foreach ($this->parameters as $param => $value) {
					$type = PDO::PARAM_STR;
					
					switch ($value[1]) {
						case is_int($value[1]):
							$type = PDO::PARAM_INT;
							break;
						case is_bool($value[1]):
							$type = PDO::PARAM_BOOL;
							break;
						case is_null($value[1]):
							$type = PDO::PARAM_NULL;
							break;
					}
					$this->statement->bindValue($value[0], $value[1], $type);
				}
			}
			$this->statement->execute();
		}
		catch (PDOException $e) {
			echo $e->getMessage();
			echo $query;
			die();
		}
		$this->parameters = array();
	}

	/**
	 * @param $query
	 * @param null $params
	 * @param int $fetch_mode
	 * @return null
	 */
	public function query($query, $params = NULL, $fetch_mode = PDO::FETCH_ASSOC) {
		$query = trim(str_replace('\r', '', $query));

		$this->init($query, $params);

		$raw_statement = explode(' ', preg_replace("/\s+|\t+|\n+/", ' ', $query));
		$statement = strtolower($raw_statement[0]);

		if ($statement === 'select' || $statement === 'show') {
			return $this->statement->fetchAll($fetch_mode);
		} elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
			return $this->statement->rowCount();
		} else {
			return NULL;
		}
	}

	/**
	 * @return string
	 */
	public function lastInsertId() {
		return $this->dbh->lastInsertId();
	}

	/**
	 * @return boolean
	 */
	public function beginTransaction() {
		return $this->dbh->beginTransaction();
	}

	/**
	 * @return mixed
	 */
	public function commitTransaction() {
		return $this->dbh->commit();
	}

	/**
	 * @param $query
	 * @param null $params
	 * @return array|null
	 */
	public function column($query, $params = NULL) {
		$this->init($query, $params);
		$columns = $this->statement->fetchAll(PDO::FETCH_NUM);
		$column = NULL;

		foreach ($columns as $cells) {
			$column[] = $cells[0];
		}

		return $column;
	}

	/**
	 * @param $query
	 * @param null $params
	 * @param int $fetch_mode
	 * @return mixed
	 */
	public function row($query, $params = NULL, $fetch_mode = PDO::FETCH_ASSOC) {
		$this->init($query, $params);
		$result = $this->statement->fetchColumn();
		$this->statement->closeCursor();
		return $result;
	}
}