<?php
	ini_set('max_execution_time', 300);
	session_name('peggame');
	@session_start;
	
	function PGSAutoload($classname)
	{
		include("$classname.class.php");
	}
	
	spl_autoload_register("PGSAutoload");
	
	if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) header('X-UA-Compatible: IE=edge,chrome=1');
	
	$row_count = 5;
	$pegs = new AutoSimulator($row_count);
	
	$display_stats = false;
	
	if (isset($_SESSION['gameboard'])) {
		$pegs->UnserializeGameBoard($_SESSION['gameboard']);
	}

	$use_logic = false;
	
	if (!isset($_GET['do_next']) && !isset($_GET['do_all'])) {
		$pegs->MakeNewGameBoard();
		$_SESSION['gameboard'] = $pegs->SerializedGameBoard();
		$_SESSION['move_count'] = 0;
	} else {
		if (isset($_GET['do_next'])) {
			$use_logic = isset($_GET['use_logic']);
			$start_tick = microtime(true);
			if ($pegs->MakeMove($use_logic)) {
				$end_tick = microtime(true);
				$_SESSION['move_count']++;
				$elapsed = $end_tick - $start_tick;
			}
		} elseif (isset($_GET['do_all'])) {
			$display_stats = true;
			$game_count = (int)$_GET['game_count'];
			$use_logic = isset($_GET['use_logic_auto']);
			$start_tick = microtime(true);
			$stats = $pegs->SimulateMultipleGames($game_count, $use_logic);
			$end_tick = microtime(true);
			$elapsed = $end_tick - $start_tick;
			for ($i = $pegs->GetMaxPegs(); $i > 0; $i--) $remaining_count[$i] = 0;
			foreach ($stats as $game_log) $remaining_count[$game_log['pegs_left']]++;
		}
	}
	$can_continue = ($pegs->GetRemainingMoveCount() > 0 ? true : false);
?>
<!DOCTYPE html>
<!--[if lt IE 7]>	<html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>		<html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>		<html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->	<html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<title>Peg Game Simulator</title>
		<meta name="description" content="Peg Game Simulator">
		<meta name="viewport" content="width=device-width">
		
		<link rel="stylesheet" href="css/normalize.min.css">
		<link rel="stylesheet" href="css/main.css">
		
		<script src="js/vendor/modernizr-2.6.2.min.js"></script>
	</head>
	<body>
		<!--[if lt IE 7]>
		    <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
		<![endif]-->
		<?php echo $pegs->DisplayGameBoard2(); ?>
		<p>Peg Count: <?php echo $pegs->GetPegCount(); ?></p>
		<p>Move Count: <?php echo $_SESSION['move_count']; ?></p>
		<p>Last Move: <?php echo $pegs->GetLastMove(); ?></p>
		<?php if (isset($elapsed)) { ?>
		<p>Elapsed Time: <?php echo $elapsed; ?></p>
		<?php } ?>
		<p>Possible Moves: <?php echo $pegs->GetRemainingMoveCount(); ?></p>
		<?php if ($can_continue) { $_SESSION['gameboard'] = $pegs->SerializedGameBoard(); ?>
		<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<fieldset>
				<legend>Single Moves</legend>
				<input type="checkbox" id="use_logic" name="use_logic" value="use_logic" <?php if ($use_logic) echo "checked"; ?> />
				<label for="use_logic">Use Logic</label>
				<button type="submit" name="do_next" value="next">Next Move</button>
			</fieldset>
		</form>
		<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<fieldset>
				<legend>Automatic Play</legend>
				<label for="game_count">Game count:</label>
				<input type="text" name="game_count" id="game_count" value="10" />
				<button type="submit" name="do_all" id="do_all" value="all">Run Simulation</button>
				<input type="checkbox" id="use_logic_auto" name="use_logic_auto" value="use_logic" <?php if ($use_logic) echo "checked"; ?> />
				<label for="use_logic_auto">Use Logic</label>
			</fieldset>
		</form>
		<?php } else { ?>
		<p>No more valid moves. <a href="index.php">Click here to restart.</a></p>
		<?php } ?>
		<?php if ($display_stats) { ?>
		<pre><?php print_r($remaining_count); ?></pre>
		<?php } ?>
		
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.10.1.min.js"><\/script>')</script>
		
		<script src="js/plugins.js"></script>
		<script src="js/main.js"></script>
	</body>
</html>
