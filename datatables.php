<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Datatables
{
	var $ci;
	var $imported;

	public function __construct()
	{
		$this->ci=& get_instance();
	}

	public function generate($table, $columns, $index, $joins, $where, $search)
	{
		$sLimit = $this->get_paging();
		$sOrder = $this->get_ordering($columns);
		$sWhere = $this->get_filtering($columns, $where, $search);
		$rResult = $this->get_display_data($table, $columns, $sWhere, $sOrder, $sLimit, $joins, $where);
		$rResultFilterTotal = $this->get_data_set_length();
		$aResultFilterTotal = $rResultFilterTotal->result_array();
		$iFilteredTotal = $aResultFilterTotal[0]["FOUND_ROWS()"];
		$rResultTotal = $this->get_total_data_set_length($table, $index, $sWhere, $joins, $where);
		$aResultTotal = $rResultTotal->result_array();
		$iTotal = $aResultTotal[0]["COUNT($index)"];
		return $this->produce_output($columns, $iTotal, $iFilteredTotal, $rResult);

	}

	protected function get_paging()
	{
		if($this->ci->input->post("iDisplayStart") && $this->ci->input->post("iDisplayLength") !== "-1")
			$sLimit = "LIMIT " . $this->ci->input->post("iDisplayStart"). ", " .$this->ci->input->post("iDisplayLength");
		else
		{
			$iDisplayLength = $this->ci->input->post("iDisplayLength");

			if(empty($iDisplayLength)){
				$sLimit = "LIMIT " . "0,10";
			}else{

				$sLimit = "LIMIT " . "0,". $this->ci->input->post("iDisplayLength");
			}
		}

		return $sLimit;
	}

	protected function get_ordering($columns)
	{
		$sOrder = "";

		if($this->ci->input->post("iSortCol_0"))
		{
			$sOrder = "ORDER BY ";

			for($i = 0; $i < intval($this->ci->input->post("iSortingCols")); $i++)
				$sOrder .= $columns[intval($this->ci->input->post("iSortCol_" . $i))] . " " . $this->ci->input->post("sSortDir_" . $i) . ", ";

			$sOrder = substr_replace($sOrder, "", -2);
		}

		return $sOrder;
	}

	protected function get_filtering($columns, $where, $search)
	{
		$sWhere = "";

		if($this->ci->input->post("sSearch") != '')
		{
			$sWhere = "WHERE ";
			for($i = 0; $i < count($columns); $i++)
			$sWhere .= $columns[$i] . " LIKE '%" . $this->ci->input->post("sSearch") . "%' OR ";

			$sWhere = substr_replace($sWhere, "", -3);

		}

		if($sWhere == ''){
			if($where !== ''){
				$where = 'WHERE ' . $where;
			}
		}

		return $sWhere . $where;
	}

	protected function get_display_data($table, $columns, $sWhere, $sOrder, $sLimit, $joins, $where)
	{
		return $this->ci->db->query
			("
				SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . "
				FROM $table 
				$joins 
				$sWhere 
				$sOrder 
				$sLimit
			");
	}

	protected function get_data_set_length()
	{
		return $this->ci->db->query("SELECT FOUND_ROWS()");
	}

	protected function get_total_data_set_length($table, $index, $sWhere, $joins, $where)
	{
		return $this->ci->db->query
			("
				SELECT COUNT(" . $index . ") 
				FROM $table 
				$joins 
				$sWhere 

			");
	}

	protected function produce_output($columns, $iTotal, $iFilteredTotal, $rResult)
	{
		$aaData = array();

		foreach($rResult->result_array() as $row_key => $row_val)
		{
			foreach($row_val as $col_key => $col_val)
			{
				if($row_val[$col_key] == "version")
					$aaData[$row_key][$col_key] = ($aaData[$row_key][$col_key] == 0)? "-" : $col_val;
				else
				{
					switch($row_val[$col_key])
					{
						default: $aaData[$row_key][] = $col_val; break;
					}
				}
			}
		}

		$sOutput = array
		(
			"sEcho"                => intval($this->ci->input->post("sEcho")),
			"iTotalRecords"        => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData"               => $aaData
		);

		return json_encode($sOutput);
	}
} 
