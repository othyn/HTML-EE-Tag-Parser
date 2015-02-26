<?php
	
	// ------------------------------------------------ //
	//													//
	//             THE FORM IS NOT VALIDATED            //
	//                   OR SANITISED                   //
	//													//
	// ------------------------------------------------ //
	
	// I am running on a local server for personal testing/use only.
	// Validation and Sanitation, as well as splitting the php away
	// from the markup will come at a later date or if I want to place
	// the tool as an online resource for public usage.
	
	// Error Outputting to Page
	error_reporting(0);
	$debug = 0;
	
	// Debug Mode - Outputs Runtime Errors and Regex Patterns if they fail
	if ($_GET['debug'] == 1) {
		error_reporting(E_ALL);
		$debug = 1;
		$urlVars = "?debug=$debug";
	}
	
	// Init Variables
	$urlVars	= ($urlVars!=""?$urlVars:"");
	$submitted	= ($_POST['submitted']?$_POST['submitted']:0);
	$html		= ($_POST['html']?$_POST['html']:'');
	$eeDelim	= ($_POST['tagDelim']?$_POST['tagDelim']:'_:');
	$tabAmount	= ($_POST['tabAmount']?$_POST['tabAmount']:0);
	$tagLimit	= ($_POST['tagLimit']?$_POST['tagLimit']:'');
	$tabArrVar	= ($_POST['tagArrayVariable']?$_POST['tagArrayVariable']:"");
	$tabAsscArr = ($_POST['tagAssociativeArray']?$_POST['tagAssociativeArray']:0);
	$tabs		= "";
	$regexError = 0;
	$match		= '';
	$newMatch	= '';
	$error		= array();
	$success	= array();
	
	// Associative Array Dropdown
	$assocArrSelect = array(
		0 => array(
			'value'		=> 0,
			'selected'	=> '',
			'text'		=> 'No'
		),
		1 => array(
			'value'		=> 1,
			'selected'	=> '',
			'text'		=> 'Yes'
		)
	);
	$assocArrSelect[$tabAsscArr]['selected'] = 'selected';
	
	// Tab Amount Dropdown
	$tabMax = 5;
	for ($i = 0; $i <= $tabMax; ++$i) {
		
		switch ($i) {
			case 0:
				$text = 'No Tabs';
				break;
			case ($i > 0 && $i <= $tabMax):
				$text = $i.(($i==1)?' Tab':' Tabs');
				break;
			default:
				$text = 'Error';
				break;
		}
		
		$tabSelect[$i] = array(
			'value'		=> $i,
			'selected'	=> '',
			'text'		=> $text
		);
	}
	$tabSelect[$tabAmount]['selected'] = 'selected';
	
	// If there is HTML submitted to parse, lets go!
	if (isset($html) && $html != '' && $submitted == 1) {
		
		// Set the amount of tabs to use, as a string
		if ($tabAmount >= 1 && $tabAmount <= 5) {
			for ($i = 0; $i < $tabAmount; ++$i) {
				$tabs .= "\t";
			}
			echo $tabs;
		} else {
			$tabs = "";
		}
		
		// Regex Patterns with the relevant inputs affecting them
		$pattern 	= '/(\{'.$tagLimit.'[a-zA-Z0-9'.$eeDelim.']+\})/';
		$pattern2 	= '/\{'.$tagLimit.'([a-zA-Z0-9'.$eeDelim.']+)\}/';
		
		// String to search, in this case the HTML input
		$search = $html;
		
		// This is the initial regex parse, it takes the tags out of the html
		//	markup and places them in string with the tags end to end
		$match 	= preg_split($pattern,$search,NULL,PREG_SPLIT_DELIM_CAPTURE);
		$match 	= preg_grep($pattern,$match);
		$match 	= implode('', $match);
		
		// The match has failed as the string is empty
		if ($match == '' && $debug == 1) {
			
			// Increment Count for regex errors
			++$regexError;
			
			if ($debug == 1) {
				// Display the regex pattern if debugging
				$error[] = "First pattern failed! - Pattern: $pattern";
			}
			
		} else {
			
			// Build the array
			//	The array changes the array formatting on whether the array is associative or not, defined by the user
			$newMatch = preg_replace($pattern2,"\t'$1'".($tabAsscArr==1?"$tabs=> ''":"").",\n",$match);
			
			// If there is no change before and after the array build, it has failed
			if ($newMatch == '' || $newMatch == $match) {
				
				// Increment Count for regex errors
				++$regexError;
				
				if ($debug == 1) {
					// Display the regex pattern if debugging
					$error[] = "Second pattern failed! - Pattern: $pattern2";
				}
				
			} else {
				
				// If all is successful, remove the trailing comma and newline
				$match = rtrim($newMatch,",\n");
				
				// Then build the array and if the users array variable is set - append it to the front of the output
				$match = ($tabArrVar!=""?"$$tabArrVar = ":"")."array(\n$match\n);";
			}
		}
		
		// OPTIONAL: Then just (with the Alignment plugin installed) open Sublime Text 3, paste the output, and hit CMD + CTRL + A and you are done
		
		// Decide error messages to send to the page based on the state of both the output & debug mode
		if (isset($match) && $match != '' && $regexError == 0) {
			$success[] = 'Success! The output is in the box below...';
		} elseif (isset($_POST['submitted']) && $_POST['submitted'] == 1 && $regexError > 0) {
			$error[] = 'No match! Please check your input is valid.';
		}		
		
	} elseif (isset($_POST['submitted']) && $_POST['submitted'] == 1) {
		
		// No HTML submitted to be parsed
		$error[] = 'Please enter something to parse!';
	}
?>
<html>
	<head>
		<style type="text/css">
			body {
				margin: 0;
				padding: 0;
				padding: 25px 25px;
				font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
			}
			h1 {
				margin: 0;
				padding-bottom: 10px;
			}
			div,
			pre,
			label,
			input,
			textarea,
			button {
				display: block;
			}
			div.error {
				color: red;
				padding-bottom: 10px;
			}
			div.success {
				color: green;
				padding-bottom: 10px;
			}
			div.result {
				padding-top: 25px;
				font-size: 14pt;
				width: 525px;
			}
			div.result pre.output {
				font-size: 10pt;
				background: #DDD;
				border: 1px solid #BBB;
				width: 100%;
				padding: 10px;
				overflow: auto;
			}
			form {
				margin: 0;
				padding: 25px 0;
			}
			form div.input {
				padding-bottom: 45px;
				width: 525px;
			}
			form div.input label.input-title {
				font-size: 14pt;
				margin-bottom: 10px;
			}
			form div.input label.input-help {
				padding-top: 5px;
				font-size: 9pt;
				color: #BBB;
			}
			form div.input input,
			form div.input textarea,
			form div.input select {
				padding: 5px;
				width: 100%;
				margin-bottom: 5px;
			}
			form button {
				padding: 10px 25px;
			}
		</style>
	</head>
	<body>
		<h1>Returns an array of EE tags from HTML Markup</h1>
		
		<?php
			
			if (isset($error) && count($error) > 0) {
				foreach ($error as $text) {
					echo "<div class=\"error\">$text</div>";
				}
			}
			if (isset($success) && count($success) > 0) {
				foreach ($success as $text) {
					echo "<div class=\"success\">$text</div>";
				}
			}
			if (isset($match) && $match != '') {
				echo "<div class=\"result\">Result<pre class=\"output\">$match</pre></div>";
			}
			
		?>
		
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]).$urlVars; ?>" method="post" enctype="application/x-www-form-urlencoded">
			<input type="hidden" name="submitted" value="1">
			<div class="input">
				<label class="input-title">EE Tag Special Chars</label>
				<input type="text" name="tagDelim" placeholder="Not Required" value="<?=$eeDelim;?>">
				<label class="input-help">Any special characters used in the EE tag. I.e. _ . |</label>
			</div>
			<div class="input">
				<label class="input-title">EE Tag Limit</label>
				<input type="text" name="tagLimit" placeholder="Not Required" value="<?=$tagLimit;?>">
				<label class="input-help">Filter the results to ONLY match the specified tag. I.e. you'd use '<strong>something:</strong>' for only matching tags that the same as {something:<i>varName</i>}</label>
			</div>
			<div class="input">
				<label class="input-title">Array Variable</label>
				<input type="text" name="tagArrayVariable" placeholder="Not Required" value="<?=$tabArrVar;?>">
				<label class="input-help">A variable for the array to go into. I.e. arrayVarName would result in '$arrayVarName = array(...);'.</label>
			</div>
			<div class="input">
				<label class="input-title">Associative Array?</label>
				<select type="text" name="tagAssociativeArray">
				<?php foreach ($assocArrSelect as $tab => $val): ?>
					<option value="<?=$val['value'];?>" <?=$val['selected'];?>><?=$val['text'];?></option>
				<?php endforeach; ?>
				</select>
				<label class="input-help">Would you like the array to be associative or not?</label>
			</div>
			<div class="input">
				<label class="input-title">Array Tab Spacing</label>
				<select type="text" name="tabAmount">
				<?php foreach ($tabSelect as $tab => $val): ?>
					<option value="<?=$val['value'];?>" <?=$val['selected'];?>><?=$val['text'];?></option>
				<?php endforeach; ?>
				</select>
				<label class="input-help">Amount of tabs to use between the array key and assoc. seperator.<br>Only taken into account if the array is associative.</label>
			</div>
			<div class="input">
				<label class="input-title">HTML to be parsed</label>
				<textarea name="html" rows="25" placeholder="HTML Markup"><?=htmlentities($html);?></textarea>
				<label class="input-help">HTML that contains EE Tags to be parsed.</label>
			</div>
			<button type="submit">Submit</button>
		</form>
	</body>
</html>