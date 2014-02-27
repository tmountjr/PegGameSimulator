<?php

class PegGameSimulator
{

	private $move_map = array();
	private $rows = array();
	private $game_board = array();
	
	private $row_count = 0;
	
	private $move_regex = "/(?<neighbor>\d+)+:(?<destination>\d+)/";
	
	private $last_move = "N/A";
	public function GetLastMove()
	{
		return $this->last_move;
	}
	
	public function GetPegCount()
	{
		$gameboard = $this->game_board;
		$p_count = array_count_values($gameboard);
		return $p_count['P'];
	}
	
	public function GetMaxPegs()
	{
		return (pow($this->row_count, 2) + $this->row_count) / 2;
	}
	
	public function GetRemainingMoveCount()
	{
		$p = $this->FindAllValidMoves();
		return count($p);
	}

	public function __construct($row_count)
	{
		$this->row_count = $row_count;
		for ($row = 1; $row <= $row_count; $row++) {
			$lower = 1 + ((pow($row, 2) - $row) / 2);
			$upper = (pow($row, 2) + $row) / 2;
			$cell_count = 1;
			for ($i = $lower; $i <= $upper; $i++) {
				$this->rows[$row][] = $i;
				$row_id = $row;
				$cell_id = $cell_count;
				$peg_id = $i;
				
				if ($row_id - 2 > 0) {
					if ($cell_id - 2 > 0)
						$this->move_map[$peg_id][] = sprintf("%s:%s", $this->GetPegID($row_id - 1, $cell_id - 1), $this->GetPegID($row_id - 2, $cell_id - 2));
					if ($cell_id <= $row_id - 2)
						$this->move_map[$peg_id][] = sprintf("%s:%s", $this->GetPegID($row_id - 1, $cell_id), $this->GetPegID($row_id - 2, $cell_id));
				}
				
				//horizontal neighbors and jump targets
				if ($cell_id - 2 > 0) $this->move_map[$peg_id][] = sprintf("%s:%s", $this->GetPegID($row_id, $cell_id - 1), $this->GetPegID($row_id, $cell_id - 2));
				if ($cell_id + 2 <= $row_id) $this->move_map[$peg_id][] = sprintf("%s:%s", $this->GetPegID($row_id, $cell_id + 1), $this->GetPegID($row_id, $cell_id + 2));
				
				//lower neighbors and jump targets
				if ($row_id + 2 <= $row_count) {
					$this->move_map[$peg_id][] = sprintf("%s:%s", $this->GetPegID($row_id + 1, $cell_id), $this->GetPegID($row_id + 2, $cell_id));
					if ($cell_id + 2 <= $row_id + 2)
						$this->move_map[$peg_id][] = sprintf("%s:%s", $this->GetPegID($row_id + 1, $cell_id + 1), $this->GetPegID($row_id + 2, $cell_id + 2));
				}

				$cell_count++;
			}
		}
	}
	
	private function GetPegID($row, $col)
	{
		$ret = false;
		$r = (int)$row;
		$c = (int)$col;
		if ($c <= $r) $ret = $c + ((pow($r, 2) - $r) / 2);
		return $ret;
	}
	
	private function FindAllValidMoves(array $gameboard = array())
	{
		if (count($gameboard) == 0) $gameboard = $this->game_board;
		$possible_moves = array();
		foreach ($gameboard as $peg_id=>$peg_value) {
			if (!$this->IsEmpty($peg_id, $gameboard)) {
				$this_possible = $this->FindValidSinglePegMoves($peg_id, $gameboard);
				if (count($this_possible) > 0) {
					foreach ($this_possible as $possible) {
						$possible_moves[] = array(
							"peg_id" => $peg_id,
							"move" => $possible,
						);
					}
				}
			}
		}
		return $possible_moves;
	}
	
	private function FindValidSinglePegMoves($peg_id, array $gameboard = array())
	{
		if (count($gameboard) === 0) $gameboard = $this->game_board;
		$this_movemap = $this->move_map[$peg_id];
		$valid_moves = array();
		foreach ($this_movemap as $move) {
			preg_match($this->move_regex, $move, $matches);
			$neighbor = $matches['neighbor'];
			$destination = $matches['destination'];
			if (!$this->IsEmpty($neighbor, $gameboard) && $this->IsEmpty($destination, $gameboard)) {
				$valid_moves[] = $move;
			}
		}
		return $valid_moves;
	}
	
	private function IsEmpty($peg_id, array $gameboard = array())
	{
		if (count($gameboard) === 0) $gameboard = $this->game_board;
		return ($gameboard[$peg_id] === "O" ? true : false);
	}
	
	public function MakeNewGameBoard()
	{
		$this->game_board = array();
		$max = (pow($this->row_count, 2) + $this->row_count) / 2;

		for ($i = 1; $i <= $max; $i++) {
			$this->game_board[$i] = "P";
		}
		//choose a random spot to empty.
		$this->game_board[mt_rand(1, $max)] = "O";
	}
	
	public function DisplayGameBoard()
	{
		$max_col = ($this->row_count * 2) - 1;
		$cell_id = 1;
		
		$r = "<table>\n\t<tbody>";
		foreach ($this->rows as $row_id => $row) {
			$r .= "\t\t<tr>\n";
			$pcell = ($this->row_count + 1) - $row_id;
			$pcount = 0;
			for ($i = 1; $i <= $max_col; $i++) {
				$r .= "\t\t\t<td>";
				if ($pcount < $row_id && $i == $pcell) {
					$r .= "<p class='cell'>" . $this->game_board[$cell_id] . "</p>";
					$cell_id++;
					$pcount++;
					$pcell += 2;
				}
				$r .= "</td>\n";
			}
			$r .= "\t\t</tr>\n";
		}
		$r .= "\t</tbody>\n</table>\n";
		
		return $r;
	}
	
	public function MakeMove($use_logic = false)
	{
		$r = false;
		//find all possible moves
		$possible_moves = $this->FindAllValidMoves();
		$possible_move_count = count($possible_moves);

		if ($possible_move_count > 0) {
			//simulate all moves and see what yields the most valid moves after that.
			if ($use_logic) {
				foreach ($possible_moves as $possible_move_id => $possible_move) {
					$move_count[$possible_move_id] = $this->SimulateMove($this->game_board, $possible_move['peg_id'], $possible_move['move']);
				}
				arsort($move_count);
				reset($move_count);
				$move_id = key($move_count);
			} else {
				$move_id = mt_rand(0, $possible_move_count - 1);
			}
			//source peg set to "O", neighbor peg set to "O", destination peg set to "P"
			$peg_id = $possible_moves[$move_id]['peg_id'];
			$move = $possible_moves[$move_id]['move'];
			preg_match($this->move_regex, $move, $matches);
			$this->game_board[$peg_id] = "O";
			$this->game_board[$matches['neighbor']] = "O";
			$this->game_board[$matches['destination']] = "P";
			$r = true;
			$this->last_move = sprintf("Peg %s: jumped %s to %s", $peg_id, $matches['neighbor'], $matches['destination']);
		}
		return $r;
	}
	
	private function SimulateMove($gameboard, $peg_id, $move)
	{
		//take a $gameboard, and $move $peg_id. Then, count how many moves are available to the new gameboard and return that.
		$new_gb = $gameboard;
		preg_match($this->move_regex, $move, $matches);
		$new_gb[$peg_id] = "O";
		$new_gb[$matches['neighbor']] = "O";
		$new_gb[$matches['destination']] = "P";
		$vm = $this->FindAllValidMoves($new_gb);
		return count($vm);
	}
	
	public function SerializedGameBoard()
	{
		return serialize($this->game_board);
	}
	
	public function UnserializeGameBoard($s)
	{
		$this->game_board = unserialize($s);
	}
	
	protected function GetGameBoard()
	{
		return $this->game_board;
	}
	
}	