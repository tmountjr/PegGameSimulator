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
        $peg_count = 0;
        foreach ($this->game_board as $peg_value) {
            if ($peg_value === 'P') $peg_count++;
        }
        return $peg_count;
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
    
    private function FindAllValidMoves()
    {
        $possible_moves = array();
        foreach ($this->game_board as $peg_id=>$peg_value) {
            if (!$this->IsEmpty($peg_id)) {
                $this_possible = $this->FindValidSinglePegMoves($peg_id);
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
    
    private function FindValidSinglePegMoves($peg_id)
    {
        $this_movemap = $this->move_map[$peg_id];
        $valid_moves = array();
        foreach ($this_movemap as $move) {
            preg_match($this->move_regex, $move, $matches);
            $neighbor = $matches['neighbor'];
            $destination = $matches['destination'];
            if (!$this->IsEmpty($neighbor) && $this->IsEmpty($destination)) {
                $valid_moves[] = $move;
            }
        }
        return $valid_moves;
    }
    
    private function IsEmpty($peg_id)
    {
        return ($this->game_board[$peg_id] === "O" ? true : false);
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
        $r = "<pre>\n";
        foreach ($this->rows as $row) {
            $this_row = array();
            foreach ($row as $space_id) {
                $this_row[] = $this->game_board[$space_id];
            }
            $r .= str_pad(implode(" ", $this_row), $this->row_count * 2, " ", STR_PAD_BOTH) . "\n";
        }
        $r .= "</pre>";
        return $r;
    }
    
    public function MakeMove() {
        $r = false;
        //find all possible moves
        $possible_moves = $this->FindAllValidMoves();
        $possible_move_count = count($possible_moves);

        if ($possible_move_count > 0) {
            $move_id = mt_rand(0, $possible_move_count - 1);
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
    
    public function SerializedGameBoard()
    {
        return serialize($this->game_board);
    }
    
    public function UnserializeGameBoard($s)
    {
        $this->game_board = unserialize($s);
    }
    
}