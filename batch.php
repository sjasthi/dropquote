<?php
  $nav_selected = "LIST";
  $left_buttons = "NO";
  $left_selected = "";

  include('batch-helper.php');
  include('nav.php');
  include('puzzlemaker.php');
  include_once 'db_credentials.php';
?>

<script type="text/javascript" src="js/main.js"></script>
<script type="text/javascript" src="js/html2canvas.js"></script>
<script type="text/javascript" src="batch-service.js"></script>
<script src="https://raw.githack.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>
<style>

.puzzleHeader{
  background-color: rgb(55,95,145);
  color: white;
  height: 55px;
  padding-top: 10px;
}
</style>

<link rel="stylesheet" href="batch.css">

<?php
  $removeForm = false;
  if(isset($_POST['remove']) && isset($_POST['selectedPuzzle'])
  && (isset($_POST['startID']) && $_POST['startID'] != '')
  && (isset($_POST['endID']) && $_POST['endID'] != '')) {
    if(validResponse($_POST['selectedPuzzle'], $_POST['startID'], $_POST['endID']) == '1'){
      $removeForm = true;
    }
  }

  if($removeForm == true){
    echo '<div onload="createDropdownOption()" class="container" style="display:none">';
  }else{
    echo '<div onload="createDropdownOption()" class="container">';
  }
?>
  <h1>Batch</h1>
  <form method="post">
    <select class="dropContainer" id="dropContainer" name="selectedPuzzle" style="padding-left:19px">
      <option value ="">Select puzzle type</option>
    </select>
    <select name="printType" class="dropContainer">
      <option value="A">A</option>
      <option value="B">B</option>
      <option value="C">C</option>
    </select>
    <br>
    <label>START</label>
    <input name="startID" type="textarea" placeholder="Enter quote id" id="start" style="border-radius:4px;
    margin-right:23px;margin-top:3px" onkeypress="return isNumberKey(event)">
    <br>
    <label>END</label>
    <!-- Input fields only accepts numerical characters -->
    <input name="endID" type="textarea" placeholder="Enter quote id" id="end" style="border-radius:4px;"
     onkeypress="return isNumberKey(event)"><br>

    <input name="remove" type="hidden"><br>
    <input type="submit" value="Generate">
  </form>
</div>

<div id="PuzzleContainer">
  <?php
      if($removeForm){
        echo '<div id="colorScheme" style="display:inline-block;">
                  <input type="button" id="cs1" value="Export to Powerpoint" onclick="setScheme(this)"></input>
                  <button class="genButton" onclick="generatePDF()">Export to PDF</button>
              </div>';

        $flagged = true;
        $db->set_charset("utf8");

        $modeType = "SELECT * FROM preferences WHERE name = 'FEELING_LUCKY_MODE'";
        $modeResult = mysqli_query($db, $modeType);
        $mode = mysqli_fetch_array($modeResult);

        $columnCountSQL = "SELECT * from preferences WHERE name = 'DEFAULT_COLUMN_COUNT'";
        $columnResult = mysqli_query($db, $columnCountSQL);
        $columnCount = mysqli_fetch_array($columnResult);

        $chunkSizeSQL = "SELECT * FROM preferences WHERE name = 'DEFAULT_CHUNK_SIZE'";
        $cSizeResult = mysqli_query($db, $chunkSizeSQL);
        $chunkSize = mysqli_fetch_array($cSizeResult);

        $punctuationSQL = "SELECT * FROM preferences WHERE name = 'KEEP_PUNCTUATION_MARKS'";
        $punctResult = mysqli_query($db, $punctuationSQL);
        $punctuation = mysqli_fetch_array($punctResult);

        $gridHeightSQL = "SELECT * FROM preferences WHERE name = 'GRID_HEIGHT'";
        $heightResult = mysqli_query($db, $gridHeightSQL);
        $height = mysqli_fetch_array($heightResult);

        $gridWidthSQL = "SELECT * FROM preferences WHERE name = 'GRID_WIDTH'";
        $widthResult = mysqli_query($db, $gridWidthSQL);
        $width = mysqli_fetch_array($widthResult);

        $count = 1;
        for ($i = parseInt( $_POST['startID'] ); $i <= parseInt( $_POST['endID'] ); $i++ ){
          //Begin generating puzzles
          $quoteSQL = "SELECT * FROM quote_table WHERE id = ".$i;
          $quoteResult = mysqli_query($db, $quoteSQL);
          if($quoteResult->num_rows > 0){

            $quoteline = mysqli_fetch_array($quoteResult);
            $quote = $quoteline['quote'];

            if($punctuation['value'] == 'FALSE'){
              $regex = array('?', '!', "'", '.', '-', ';', ':', '[', ']',
        			 ',', '/','{', '}', ')', '(');
              $quote = str_replace($regex, '', $quote);
            }

            switch($_POST['selectedPuzzle']){
              case 'Drop':
                echo '<h2 class="puzzleHeader" id="title">Drop Puzzle: '.$count++.'</h2><br>';
                DropPrint($quote, $columnCount['value'],  $_POST['printType']);
                break;
              case 'Float':
                echo '<h2 class="puzzleHeader" id="title">Float Puzzle: '.$count++.'</h2><br>';
                error_reporting(1);
                FloatPrint($quote, $columnCount['value'], $_POST['printType']);
                break;
              case 'Drop-Float':
                error_reporting(0);
                echo'<h2 class="puzzleHeader">Drop-Float: '.$count++.'</h2>';

                if($mode != null){
                  $quoteline2 = '';
                  $invalid = true;

                  if($mode['value'] == "FIRST"){
                    $index = 2;
                    while($invalid){
                      $sql = "SELECT * FROM quote_table WHERE id = ".$index;
                      $result = mysqli_query($db, $sql);
                      $candidate = mysqli_fetch_array($result);
                      if($candidate->num_rows > 0){
                        $quoteline2 = $candidate['quote'];
                        if($quoteline2 != ''){
                          $invalid = false;
                        }
                      }
                      $index++;
                    }
                  } else{
                      $length = "SELECT * FROM quote_table order by id DESC limit 1";
                      $result = mysqli_query($db,$length);
                      $lastID = mysqli_fetch_array($result);

                      if($mode['value'] == "LAST"){
                        $index = $lastID['id'] - 1;
                        while($invalid){
                          $sql = "SELECT * FROM quote_table WHERE id = ".$index;
                          $result = mysqli_query($db, $sql);
                          $candidate = mysqli_fetch_array($result);
                          $quoteline2 = $candidate['quote'];
                          if($quoteline2 != ''){
                            $invalid = false;
                          }
                          $index--;
                        }
                      } else if($mode['value'] == "RANDOM"){
                        $index = mt_rand(1,$lastID['id']);
                        while($invalid){
                          $sql = "SELECT * FROM quote_table WHERE id = ".$index;
                          $result = mysqli_query($db, $sql);
                          $candidate = mysqli_fetch_array($result);
                          $quoteline2 = $candidate['quote'];
                          if($quoteline2 != ''){
                            $invalid = false;
                          }
                          $index = mt_rand(1,$lastID['id']);
                        }
                      }
                  }
                }
                FloatDropPrint($quote, $quoteline2, $columnCount['value'], $_POST['printType']);
                break;
              case 'Scramble':
                echo '<h2 class="puzzleHeader" id="title">Scramble Puzzle: '.$count++.'</h2><br>';
                ScramblePrint($quote, $_POST['printType']);
                break;
              case 'Split':
                echo '<h2 class="puzzleHeader" id="title">Split Puzzle: '.$count++.'</h2><br>';
                SplitPrint($quote, $chunkSize['value'], $_POST['printType']);
                break;
              case 'Slider-16':
                echo'<h2 class="puzzleHeader" id="title">Slider-16 Puzzle: '.$count.'</h2><br>';
                slider16Print($quote, $_POST['printType'], $count++);
                break;
              case 'Catch-A-Phrase':
                echo'<h2 class="puzzleHeader" id="title">Catch a Phrase: '.$count.'</h2>';
                phrasePrint($quote, $width['value'], $height['value'], $count++, $_POST['printType']);
                break;
          }
        } else {
          echo "<br><br><div><div style='width:200px; height: 25px; background-color: rgb(205,55,25);
          color:white; margin: auto; border-radius: 5px;'>Invalid puzzle ID: ".$_POST["startID"]."</div>";
          echo "<br><a href='batch.php'><input type='submit' value='return'/></a></div>";
        }
      }//end of for loop
      ?>
      </div>
      <?php
    }
  ?>
