<?php
	ini_set('max_execution_time', 300);
	session_name('peggame');
	@session_start;
	
	function PGSAutoload($classname)
	{
		include("$classname.class.php");
	}
	
	spl_autoload_register("PGSAutoload");
	
	$row_count = 5;
	$game_count = 1;
	$pegs = new AutoSimulator($row_count);
	
	if (isset($_SESSION['gameboard'])) {
		$pegs->UnserializeGameBoard($_SESSION['gameboard']);
	}

	if (!isset($_GET['do_next'])) {
		$pegs->MakeNewGameBoard();
		$_SESSION['gameboard'] = $pegs->SerializedGameBoard();
		$_SESSION['move_count'] = 0;
	} else {
		
		if ($pegs->MakeMove()) {
			$_SESSION['move_count']++;
		}
		
		/*
		$stats = $pegs->SimulateMultipleGames($game_count);
		for ($i = $pegs->GetMaxPegs(); $i > 0 ; $i--) $remaining_count[$i] = 0;
		
		foreach ($stats as $game_log) {
			$remaining_count[$game_log['pegs_left']]++;
		}
		*/
	}
	$can_continue = ($pegs->GetRemainingMoveCount() > 0 ? true : false);
?>
<!DOCTYPE html>
<html>
	<head>
	</head>
	<body>
		<?php echo $pegs->DisplayGameBoard(); ?>
		<p>Peg Count: <?php echo $pegs->GetPegCount(); ?></p>
		<p>Move Count: <?php echo $_SESSION['move_count']; ?></p>
		<p>Last Move: <?php echo $pegs->GetLastMove(); ?></p>
		<p>Possible Moves: <?php echo $pegs->GetRemainingMoveCount(); ?></p>
	<pre>
		<?php print_r($pegs->FindAllValidMoves()); ?>
	</pre>
		<?php if ($can_continue) { $_SESSION['gameboard'] = $pegs->SerializedGameBoard(); ?>
		<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">	
			<button type="submit" name="do_next" value="next">Next Move</button>
		</form>
		<?php } else { ?>
		<p>No more valid moves. <a href="index.php">Click here to restart.</a></p>
			<?php if (method_exists($pegs, 'GetStatistics')) { ?>
		<pre>
		<?php //var_dump($pegs->GetStatistics()); ?>
		<?php var_dump($remaining_count); ?>
		</pre>
			<?php } ?>
		<?php } ?>
	</body>
</html>