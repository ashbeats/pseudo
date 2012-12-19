<?php
namespace Pseudo\Pdo;

class Result
{
    private $rows;
    private $isParameterized = false;
    private $errorCode;
    private $errorInfo;
    private $affectedRowCount = 0;
    private $insertId = 0;
    private $rowOffset = 0;

    public function __construct($rows = null)
    {
        if (is_array($rows)) {
            $this->rows = $rows;
        }
    }

    public function addRow(array $row, $params = null)
    {
        if ($params) {
            if ($this->isParameterized) {
                $this->rows[$this->stringifyParameterSet($params)][] = $row;
            } else if (!$this->parameterized && !$this->rows) {
                $this->rows[$this->stringifyParameterSet($params)][] = $row;
                $this->isParameterized = true;
            }
        } else {
            if (!$this->isParameterized) {
                $this->rows[] = $row;
            } else {
                throw new Exception("Cannot mix parameterized and non-parameterized rowsets");
            }
        }
    }

    public function getRows(array $params = [])
    {
        if ($params) {
            if ($this->isParameterized) {
                return $this->rows[$this->stringifyParameterSet($params)];
            }
            throw new Exception("Cannot get rows with parameters on a non-parameterized result");
        } else {
            if (!$this->isParameterized) {
                return $this->rows;
            }
            throw new Exception("Cannot get rows without parameters on a parameterized result");
        }
    }

    public function nextRow()
    {
        $row = $this->rows[$this->rowOffset];
        if ($row) {
            $this->rowOffset++;
            return $row;
        } else {
            return false;
        }
    }

    public function setInsertId($insertId)
    {
        $this->insertId = $insertId;
    }

    public function getInsertId()
    {
        return $this->insertId;
    }

    /**
     * @param $errorCode
     * @throws Exception
     */
    public function setErrorCode($errorCode)
    {
        if (ctype_alnum($errorCode) && strlen($errorCode) == 5) {
            $this->errorCode = $errorCode;
        } else {
            throw new Exception("Error codes must be in ANSI SQL standard format");
        }
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param $errorInfo
     */
    public function setErrorInfo($errorInfo)
    {
        $this->errorInfo = $errorInfo;
    }

    /**
     * @return string
     */
    public function getErrorInfo()
    {
        return $this->errorInfo;
    }

    public function setAffectedRowCount($affectedRowCount)
    {
        $this->affectedRowCount = $affectedRowCount;
    }

    public function getAffectedRowCount()
    {
        return $this->affectedRowCount;
    }

    public function isOrdinalArray(array $arr)
    {
        return !(is_string(key($arr)));
    }

    public function reset()
    {
        $this->rowOffset = 0;
    }

    private function stringifyParameterSet(array $params)
    {
        if ($this->isOrdinalArray($params)) {
            return implode(',', $params);
        } else {
            $returnArray = [];
            foreach ($params as $key => $value) {
                $returnArray[] = $key;
                $returnArray[] = $value;
            }
            return implode(',', $returnArray);
        }
    }
}