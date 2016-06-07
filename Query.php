<?php

/**
 * Created by PhpStorm.
 * User: jworsley
 * Date: 5/26/2016
 * Time: 9:34 AM
 */

class Query
{
	/**
	 * @param $column
	 * @param $filter
	 * @return null|string
	 */
	function buildSearchStr($column, $filter)
	{
		if ($filter) {
			return "LOWER($column) LIKE LOWER('%$filter%')";
		} else {
			return NULL;
		}
	}

	/**
	 * @param $column
	 * @param $start_date
	 * @param null $end_date
	 * @return null|string
	 */
	function buildDateStr($column, $start_date, $end_date = NULL)
	{
		if ($start_date || $end_date) {
			if ($start_date) {
				if ($end_date) {
					return "$column BETWEEN '$start_date' AND '$end_date'";
				} else {
					return "$column >= '$start_date'";
				}
			} else {
				return "$column <= '$end_date'";
			}
		} else {
			return NULL;
		}
	}

	/**
	 * @param $filters
	 * @return null|string
	 */
	function buildQueryStr($filters)
	{
		$query_str = NULL;
		foreach ($filters as $filter) {
			if ($filter && $query_str) {
				$query_str .= ' AND ' . $filter;
			} else if ($filter) {
				$query_str = $filter;
			}
		}
		return $query_str;
	}
}