<?php

	abstract class Collection implements Countable, IteratorAggregate, ArrayAccess
	{
		protected $type;

		protected $collection = array();

		private function validateType($object)
		{
			if (!($object instanceof $this->type))
				throw new InvalidArgumentException("Объект типа " . get_class($object) . " не может быть добавлен в коллекцию объектов типа " . $this->type);
		}
		//----------------------------------------------------------------------------------

		public function add($object)
		{
			$this->offsetSet(null, $object);

			return $this;
		}
		//----------------------------------------------------------------------------------

		public function remove($object)
		{
			$offset = array_search($object, $this->collection, true);

			if ($offset !== false)
				$this->offsetUnset($offset);

			return $this;
		}
		//----------------------------------------------------------------------------------

		public function isEmpty()
		{
			return empty($this->collection);
		}
		//----------------------------------------------------------------------------------

		public function getIterator()
		{
			return new ArrayIterator($this->collection);
		}
		//----------------------------------------------------------------------------------

		public function offsetExists($offset)
		{
			return isset($this->collection[$offset]);
		}
		//----------------------------------------------------------------------------------

		public function offsetGet($offset)
		{
			if (!$this->offsetExists($offset))
				return null;

			return $this->collection[$offset];
		}
		//----------------------------------------------------------------------------------

		public function offsetSet($offset, $object)
		{
			$this->validateType($object);

			if ($offset === null)
				$offset = $this->isEmpty() ? 0 : max(array_keys($this->collection)) + 1;

			$this->collection[$offset] = $object;
		}
		//----------------------------------------------------------------------------------

		public function offsetUnset($offset)
		{
			unset($this->collection[$offset]);
		}
		//----------------------------------------------------------------------------------

		public function count()
		{
			return sizeof($this->collection);
		}
		//----------------------------------------------------------------------------------
	}

?>