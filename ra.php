<?php

/*
 * Relationnal Algebra classes for PHP
 */

abstract class raRelationAbstract
{
    var $name = '';
    var $columns = array();

    abstract public function sql();
    abstract public function getColumnList();
}

/**
 * A relation
 */
class raRelation extends raRelationAbstract {
	public function __construct($name, $columns = array()) {
		$this->name = $name;
		$this->columns = $columns;
	}

	public function sql() {
		$sql = sprintf('SELECT * FROM %s', $this->name);
		return $sql;
	}

	public function getColumnList() {
		return $this->columns;
	}
}

/**
 * The Projection operator
 */
class raProjection extends raRelationAbstract {
	var $r = null;

	public function __construct($columns = array(), $r) {
		$this->r = $r;
		$this->columns = $columns;
		$this->name = $this->r->name;
	}

	public function sql() {
		$sql = 'SELECT ';
		$columnListIn = $this->r->getColumnList();
		$columnListOut = array();
		foreach ($columnListIn as & $column) {
			if (!in_array($column, $columnListIn)) {
				throw new Exception(sprint("Error: unknown column '%s' on relation '%s'!\n", $column, $this->r->name));
			}
			$columnListOut []= $column;
		}
		$sql .= join(', ', $columnListOut);
		$sql .= ' FROM ' . $this->r->name;
		return $sql;
	}


	public function getColumnList() {
		return $this->columns;
	}
}

/**
 * The Selection operator
 *
 * (currently, only 'AND' combination is supported)
 */
class raSelection extends raRelationAbstract {
	var $r = null;
	var $atoms = array();

	public function __construct($atoms = array(), $r) {
		$this->r = $r;
		$this->atoms = $atoms;
		$this->name = $this->r->name;
	}

	public function sql() {
		$sql = sprintf('SELECT * FROM (%s) AS %s', $this->r->sql(), $this->r->name);
		$sql .= ' WHERE ';
		$condList = array();
		$columnList = $this->r->getColumnList();
		foreach ($this->atoms as $atom) {
			if (!in_array($atom->subject, $columnList)) {
				throw new Exception(sprintf("Error: unknown column '%s' on relation '%s'!\n", $atom->subject, $this->r->name));
			}
			$condList []= sprintf("%s %s %s", $atom->subject, $atom->predicate, $atom->object);
		}
		$sql .= join(' AND ', $condList);
		return $sql;
	}

	public function getColumnList() {
		return $this->r->getColumnList();
	}
}

/**
 * A Selection atom (condition)
 */
class raAtom {
	var $subject = '';
	var $predicate = '';
	var $object = '';

	public function __construct($subject, $predicate, $object) {
		$this->subject = $subject;
		$this->predicate = $predicate;
		$this->object = $object;
	}
}

/**
 * The Rename operator
 */
class raRename extends raRelationAbstract {
	var $r = null;
	var $renames = array();

	public function __construct($renames = array(), $r) {
		$this->r = $r;
		$this->renames = $renames;
		$this->name = $this->r->name;
	}

	public function sql() {
		$sql = 'SELECT ';
		$columnListIn = $this->r->getColumnList();
		$columnListOut = array();
		foreach ($columnListIn as $column) {
			if (array_key_exists($column, $this->renames)) {
				$columnListOut []= sprintf('%s AS %s', $column, $this->renames[$column]);
			} else {
				$columnListOut [] = $column;
			}
		}
		$sql .= join(', ', $columnListOut);
		$sql .= sprintf(' FROM (%s) AS %s', $this->r->sql(), $this->r->name);
		return $sql;
	}

	public function getColumnList() {
		$columnListIn = $this->r->getColumnList();
		$columnListOut = array();
		foreach ($columnListIn as $column) {
			if (array_key_exists($column, $this->renames)) {
				$columnListOut []= $this->renames[$column];
			} else {
				$columnListOut [] = $column;
			}
		}
		return $columnListOut;
	}
}