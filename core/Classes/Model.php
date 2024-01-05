<?php


class Model
{
	protected string $table = "";
	private array $tableColumns = [];
	private array|null $rawData = [];
	private ReflectionClass $classReflection;
	private array $classProperties = [];
	private array $classPropertiesTypes = [];
	private array $nullProperties = [];
	private bool $instanceInitialized = false;
	private array $protectedProperties = [
		"table",
		"rawData",
		"classReflection",
		"classProperties"
	];



	public function isValid()
	{
		return $this->instanceInitialized;
	}

	public function __construct($idValue = null, $column = null)
	{
		// Creating reflection from the git-go
		$this->classReflection = new ReflectionClass(get_class($this));

		// Storing up properties of a class that corresponds with table columns
		$this->setClassPropertiesNamesAndTypes();

		// Getting instance by requested column and value if requested
		if ($idValue)
		{
			$this->getInstanceByColumn($column, $idValue);
		}
	}

	private function getInstanceByColumn($column, $value)
	{
		// Search either by pure id or by specified column
		$statement = (is_null($column)) ? "id = '{$value}'" : "{$column} = '{$value}'";

		// Getting the table columns
		$this->tableColumns = Pocket::SQL()->getTableColumns($this->table);

		// Setting up a connection between class properties and table columns
		$this->setClassPropertiesNamesAndTypes();

		// Grabbing the original data
		$this->rawData = Pocket::SQL()->selectRow($this->table, ["statement" => $statement]);

		// Setting up properties of model with correct values
		if ($this->rawData) {
			$this->setClassPropertiesWithRawDataValues();
		}
	}

	private function getClassPropertiesNames()
	{
		return $this->classProperties;
	}

	private function getClassPropertiesTypes()
	{
		return $this->classProperties;
	}

	private function setClassPropertiesNamesAndTypes()
	{
		foreach ($this->classReflection->getProperties() as $property) {
			if (in_array($property->name, $this->protectedProperties))
				continue;

			$propertyName = $property->name;
			$propertyName = ($this->rawData[$property->name]) ? $property->name : ucfirst($property->name);
			$propertyType = $property->getType();

			$this->classProperties[] = $propertyName;
			$this->classPropertiesTypes[$propertyName] = $propertyType;
		}
	}

	private function setClassPropertiesWithRawDataValues()
	{
		foreach ($this->classPropertiesTypes as $propertyName => $propertyType) {
			// Setting up value to property by it's type
			switch ($propertyType) {
				case "int": $this->{$propertyName} = intval($this->rawData[$propertyName]); break;
				case "string": $this->{$propertyName} = strval($this->rawData[$propertyName]); break;
				case "boolean": $this->{$propertyName} = boolval($this->rawData[$propertyName]); break;
				default: $this->{$propertyName} = $this->rawData[$propertyName]; break;
			}

			// And providing null validation for value
			$this->nullValidation($propertyName, $this->rawData[$propertyName]);
		}

		$this->instanceInitialized = true;
	}

	public function get($propertyName)
	{
		// Just in case we use first upper letter in the name of the model property
		$propertyName = lcfirst($propertyName);

		return (in_array($propertyName, $this->getClassPropertiesNames())) ? $this->{$propertyName} : null;
	}

	public function set($propertyName, $propertyValue)
	{
		// Just in case we use first upper letter in the name of the model property
		$propertyName = lcfirst($propertyName);

		if (! in_array($propertyName, $this->getClassPropertiesNames()))
			return;

		if (gettype($propertyValue) !== $this->classProperties[$propertyName])
			return;

		// Setting up value for property
		$this->{$propertyName} = $propertyValue;

		// And executing null validation
		$this->nullValidation($propertyName, $propertyValue);
	}

	private function nullValidation($propertyName, $propertyValue)
	{
		// Adding true "null" value if we're actually setting it to null
		if (is_null($propertyValue)) {
			$this->nullProperties[] = $propertyName;
		} else if ($index = array_search($propertyName, $this->nullProperties) !== false) {
			unset($this->nullProperties[$index]);
		}
	}

	public static function all()
	{
		$class = get_called_class();
		$classInstance = new $class;

		return Pocket::SQL()->select($classInstance->getTableName());
	}

	public static function select($statement = null)
	{
		$class = get_called_class();
		$classInstance = new $class();

		return Pocket::SQL()->select($classInstance->getTableName(), [
			"statement" => ($statement) ?: ""
		]);
	}

	public static function selectOne($statement = null)
	{
		$class = get_called_class();
		$classInstance = new $class();

		return Pocket::SQL()->selectRow($classInstance->getTableName(), [
			"statement" => ($statement) ?: ""
		]);
	}

	public static function instance($statement = null)
	{
		$class = get_called_class();
		$classInstance = new $class();

		$instanceId = Pocket::SQL()->selectRow($classInstance->getTableName(), [
			"statement" => ($statement) ?: ""
		])['Id'];

		return new $class($instanceId);
	}

	public static function create($properties = null)
	{
		if (is_null($properties))
			return false;

		$class = get_called_class();
		$classInstance = new $class();

		$id = Pocket::SQL()->insert($classInstance->getTableName(), $properties);

		return ($id) ? new $class($id) : false;
	}

	public static function update($statement = null, $properties = null)
	{
		if (is_null($properties))
			return false;

		$class = get_called_class();
		$classInstance = new $class();

		Pocket::SQL()->update($classInstance->getTableName(), $properties, [
			"statement" => ($statement) ?: ""
		]);
	}

	public static function delete($statement = null)
	{
		$class = get_called_class();
		$classInstance = new $class();

		Pocket::SQL()->delete($classInstance->getTableName(), [
			"statement" => ($statement) ?: ""
		]);
	}

	public function getTableName()
	{
		return $this->table;
	}

	public function toArray()
	{
		$array = [];

		foreach ($this->getClassPropertiesNames() as $propertyName) {
			if (in_array($propertyName, $this->protectedProperties))
				continue;

			$array[$propertyName] = $this->{$propertyName};
		}

		return $array;
	}

	public static function __callStatic($method, $args)
	{
		// If number of arguments is more than 1 - we're gonna return nothing
		if (count($args) < 1 || count($args) > 1) {
			return null;
		}

		$class = get_called_class();
		$column = $method;
		$value = $args[0];

		return new $class($value, $column);
	}

	public function __get($propertyName)
	{
		return $this->get($propertyName);
	}
}
