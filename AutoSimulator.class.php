<?php

class AutoSimulator extends PegGameSimulator
{
	private $move_log = array();

	public function __construct()
	{
		parent::__construct();
	}
	
	public function SimulateSingleGame()
	{
		parent::MakeNewGameBoard();
		while (parent::GetRemainingMoveCount() > 0) {
			parent::MakeMove();
			$move_log[] = parent::last_move;
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
		$gameboard = parent::game_board;
		$p_count = array_count_values($gameboard);
		return $p_count['P'];
	}
}