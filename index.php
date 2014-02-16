<?php
    session_name('peggame');
    @session_start;
    
    //var_dump($_SESSION);
    
    include('PegGameSimulator.class.php');
    $pegs = new PegGameSimulator;
    
    if (isset($_SESSION['gameboard'])) {
        $pegs->UnserializeGameBoard($_SESSION['gameboard']);
        //echo "set game board from session";
    }

    if (!isset($_GET['do_next'])) {
        $pegs->MakeNewGameBoard();
        $_SESSION['gameboard'] = $pegs->SerializedGameBoard();
        //echo "created new game board and saved to session";
    } else {
        //$pegs->MakeMove();
    }
?>
<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
        <?php echo $pegs->DisplayGameBoard(); ?>
        <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">    
            <button type="submit" name="do_next" value="next">Next Move</button>
        </form>
    </body>
</html>