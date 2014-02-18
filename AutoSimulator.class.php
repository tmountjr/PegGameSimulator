<?php

class AutoSimulator extends PegGameSimulator
{
	private $move_log = array();
	private $row_count = 0;

	public function __construct($row_count)
	{
		$this->row_count = $row_count;
		parent::__construct($row_count);
	}
	
	public function SimulateSingleGame($use_logic = false)
	{
		parent::MakeNewGameBoard();
		while (parent::GetRemainingMoveCount() > 0) {
			parent::MakeMove($use_logic);
			$this->move_log[] = parent::GetLastMove();
		}
		
		return true;
	}
	
	public function SimulateMultipleGames($game_count, $use_logic = false)
	{
		$statpack = array();
		for ($i = 0; $i < $game_count; $i++) {
			$this->SimulateSingleGame($use_logic);
			$statpack[] = $this->GetStatistics();
			$this->move_log = array();
		}
		return $statpack;
	}
	
	public function GetStatistics()
	{
		return array(
			"num_moves" => count($this->move_log),
			"move_log" => $this->move_log,
			"pegs_left" => parent::GetPegCount()
		);
	}
}