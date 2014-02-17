<?php

class PegGameSimulator
{

    private $move_map = array(
        1 => array("2:4", "3:6"),
        2 => array("4:7", "5:9"),
        3 => array("5:8", "6:10"),
        4 => array("2:1", "5:6", "7:11", "8:13"),
        5 => array("8:12", "9:14"),
        6 => array("3:1", "5:4", "9:13", "10:15"),
        7 => array("4:2", "8:9"),
        8 => array("5:3", "9:10"),
        9 => array("5:2", "8:7"),
        10 => array("6:3", "9:8"),
        11 => array("7:4", "12:13"),
        12 => array("8:5", "13:14"),
        13 => array("8:4", "9:6", "12:11", "14:15"),
        14 => array("9:5", "13:12"),
        15 => array("10:6", "14:13"),
    );
    
    private $rows = array(
        array(1),
        array(2, 3),
        array(4, 5, 6),
        array(7, 8, 9, 10),
        array(11, 12, 13, 14, 15),
    );
    
    private $game_board = array();
	protected function GetGameBoard()
	{
		return $this->game_board;
	}
    
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

    public function __construct()
    {
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
        for ($i = 1; $i < 16; $i++) {
            $this->game_board[$i] = "P";
        }
        //choose a random spot to empty.
        $this->game_board[mt_rand(1, 15)] = "O";
    }
    
    public function DisplayGameBoard()
    {
        $r = "<pre>\n";
        foreach ($this->rows as $row) {
            $this_row = array();
            foreach ($row as $space_id) {
                $this_row[] = $this->game_board[$space_id];
            }
            $r .= str_pad(implode(" ", $this_row), 10, " ", STR_PAD_BOTH) . "\n";
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