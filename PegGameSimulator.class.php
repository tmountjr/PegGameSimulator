<?php

class PegGameSimulator
{
    private $neighbors = array(
        1 => array(2, 3),
        2 => array(4, 5),
        3 => array(5, 6),
        4 => array(2, 5, 7, 8),
        5 => array(8, 9),
        6 => array(3, 5, 9, 10),
        7 => array(4, 8),
        8 => array(5, 9),
        9 => array(5, 8),
        10 => array(6, 9),
        11 => array(7, 12),
        12 => array(8, 13),
        13 => array(8, 9, 12, 14),
        14 => array(9, 13),
        15 => array(10, 14),
    );
    
    private $destinations = array(
        1 => array(4, 6),
        2 => array(7, 9),
        3 => array(8, 10),
        4 => array(1, 6, 8, 13),
        5 => array(12, 14),
        6 => array(1, 4, 13, 15),
        7 => array(2, 9),
        8 => array(3, 10),
        9 => array(2, 7),
        10 => array(3, 8),
        11 => array(4, 13),
        12 => array(5, 14),
        13 => array(4, 6, 8, 15),
        14 => array(5, 12),
        15 => array(6, 13),
    );
    
    private $rows = array(
        array(1),
        array(2, 3),
        array(4, 5, 6),
        array(7, 8, 9, 10),
        array(11, 12, 13, 14, 15),
    );
    
    private $game_board = array();

    public function __construct()
    {
    }
    
    private function FindValidMoves($peg_id)
    {
        $this_neighbors = $this->neighbors($peg_id);
        
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
    
    public function SerializedGameBoard()
    {
        return serialize($this->game_board);
    }
    
    public function UnserializeGameBoard($s)
    {
        $this->game_board = unserialize($s);
    }
}