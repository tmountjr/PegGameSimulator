<?php

class AutoSimulator extends PegGameSimulator
{
	private $move_log = array();

	public function __construct($row_count)
	{
		parent::__construct($row_count);
	}
	
	public function SimulateSingleGame()
	{
		parent::MakeNewGameBoard();
		while (parent::GetRemainingMoveCount() > 0) {
			parent::MakeMove();
			$this->move_log[] = parent::GetLastMove();
		}
		
		return true;
	}
	
	public function GetStatistics()
	{
		return array(
			"num_moves" => count($this->move_log),
			"move_log" => $this->move_log,
			"pegs_left" => $this->CountPegs()
		);
	}
	
	private function CountPegs() {
		$gameboard = $this->GetGameBoard();
		$p_count = array_count_values($gameboard);
		return $p_count['P'];
	}
}