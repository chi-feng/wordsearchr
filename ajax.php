<?php

header('Content-Type: application/json');

function generate_puzzle($words, $title, $width, $height, $allow_backwards) {
  
  $alphabet = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

  // initialize 2D puzzle array with spaces 
  $puzzle = array();
  for ($i = 0; $i < $height; $i++) {
    $puzzle[$i] = array();
    for ($j = 0; $j < $width; $j++) {
      $puzzle[$i][$j] = ' ';
    }
  }
  
  $words_data = array();
  
  // place words 
  for ($i = 0; $i < count($words); $i++) {
    $word = strtoupper(trim($words[$i]));
    if (strlen($word) < 1) continue;
    $okay = false;
    $iters = 0;
    $length = strlen($word);
    $letters = str_split($word);
    // scoping 
    $x = 0;
    $y = 0;
    $dx = 0;
    $dy = 0;
    // loop through random placement until success
    while (!$okay) {
      // increment iters for emergency break
      if ($iters++ > 10000) {
        break;
      }
      // choose random position
      $x = rand(0, $width - 1);
      $y = rand(0, $height - 1);
      // choose direction 
      $dx = rand(($allow_backwards) ? -1 : 0,1);
      $dy = rand(($allow_backwards) ? -1 : 0,1);
      // check if direction is valid 
      if ($dx == 0 && $dy == 0) continue;
      // compute end location 
      $x_end = $x + $length * $dx; 
      $y_end = $y + $length * $dy;
      // check if end location is in bounds 
      if ($x_end < 0 || $x_end >= $width) continue;
      if ($y_end < 0 || $y_end >= $height) continue;
      // check for overlap with other words
      $bad_overlap = false;
      for ($j = 0; $j < $length; $j++) {
        // get coordinate in puzzle space
        $p_i = $y + $j * $dy;
        $p_j = $x + $j * $dx;
        // check if spot in puzzle is occupied
        if ($puzzle[$p_i][$p_j] != ' ') {
          // check if letter matches ocupied location
          if ($letters[$j] != $puzzle[$p_i][$p_j]) {
            $bad_overlap = true;
            break;
          }
        }
      }
      if ($bad_overlap) continue;
      // word is in bound, and if overlap, is okay 
      $okay = true;
      break;
    }
    // if we broke out of loop without setting okay = true, report error
    if (!$okay) {
      die(json_encode(array('error'=>sprintf('Could not place %s', $word))));
    }
    // otherwise we are okay; add word placement data to words_data
    $coords = array();
    for ($j = 0; $j < $length; $j++) {
      // get coordinate in puzzle space
      $p_i = $y + $j * $dy;
      $p_j = $x + $j * $dx;
      $coords[$j] = array('x' => intval($p_j), 'y' => intval($p_i));
    } 
    $words_data[$i] = array(
      'id' => $i,
      'word' => $word,
      'x' => $x, 
      'y' => $y,
      'dx' => $dx,
      'dy' => $dy,
      'coords' => $coords
    );
    // add values to puzzle
    for ($j = 0; $j < $length; $j++) {
      $p_i = $y + $j * $dy;
      $p_j = $x + $j * $dx;
      $puzzle[$p_i][$p_j] = $letters[$j];
    }
  }
  // fill in puzzle with random letters
  for ($i = 0; $i < $height; $i++) {
    for ($j = 0; $j < $width; $j++) {
      if ($puzzle[$i][$j] == ' ')
        $puzzle[$i][$j] = strtoupper($alphabet[rand(0,count($alphabet)-1)]);
    }
  }
  return array(
    'puzzle' => array(
      'width'=>$width,
      'height'=>$height,
      'title'=>$title,
      'text'=>$puzzle), 
    'words_data' => $words_data);
}

if (isset($_GET['generate'])) {
  // get word list 
  $words = $_POST['words'];
  $lines = explode("\n", $words);
  $words = array();
  foreach ($lines as $line) {
    $tokens = explode(" ", $line);
    if (count($tokens) > 0) {
      foreach ($tokens as $token)
      $words[] = $token;
    }
  }
  $width = intval($_POST['width']);
  $height = intval($_POST['height']);
  $title = htmlentities($_POST['title']);
  $allow_backwards = isset($_POST['allow_backwards']) ? true : false;
  
  $arr = generate_puzzle($words, $title, $width, $height, $allow_backwards);
  
//  $puzzle_json = json_encode($arr['puzzle']);
//  $words_json = json_encode($arr['words_data']);
//  echo sprintf("<script>\nvar puzzle = %s;\n</script>", $puzzle_json);
//  echo sprintf("<script>\nvar words = %s;\n</script>", $words_json);
  
  echo json_encode($arr);

}

?>