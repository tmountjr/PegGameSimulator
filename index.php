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
	$pegs = new AutoSimulator($row_count);
	
	$display_stats = false;
	
	if (isset($_SESSION['gameboard'])) {
		$pegs->UnserializeGameBoard($_SESSION['gameboard']);
	}

	if (!isset($_GET['do_next']) && !isset($_GET['do_all'])) {
		$pegs->MakeNewGameBoard();
		$_SESSION['gameboard'] = $pegs->SerializedGameBoard();
		$_SESSION['move_count'] = 0;
	} else {
		if (isset($_GET['do_next'])) {
			if ($pegs->MakeMove()) {
				$_SESSION['move_count']++;
			}
		} elseif (isset($_GET['do_all'])) {
			$display_stats = true;
			$game_count = (int)$_GET['game_count'];
			$stats = $pegs->SimulateMultipleGames($game_count);
			
			for ($i = $pegs->GetMaxPegs(); $i > 0; $i--) $remaining_count[$i] = 0;
			foreach ($stats as $game_log) $remaining_count[$game_log['pegs_left']]++;
		}
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
		<?php if ($can_continue) { $_SESSION['gameboard'] = $pegs->SerializedGameBoard(); ?>
		<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<fieldset>
				<legend>Single Moves</legend>
				<button type="submit" name="do_next" value="next">Next Move</button>
			</fieldset>
		</form>
		<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<fieldset>
				<legend>Automatic Play</legend>
				<label for="game_count">Game count:</label>
				<input type="text" name="game_count" id="game_count" value="10" />
				<button type="submit" name="do_all" id="do_all" value="all">Run Simulation</button>
			</fieldset>
		</form>
		<?php } else { ?>
		<p>No more valid moves. <a href="index.php">Click here to restart.</a></p>
		<?php } ?>
		<?php if ($display_stats) { ?>
		<pre><?php print_r($remaining_count); ?></pre>
		<?php } ?>
	</body>
</html>