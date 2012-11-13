<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>wordsearchr.com</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="css/font.css" media="all" />
<link rel="stylesheet" type="text/css" href="css/screen.css" media="screen" />
<link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
<script src="js/jquery.js"></script>
<script src="js/jquery.form.js"></script>
</head>
<body>
  <div id="controls">
    <div id="logo"><h1>WordSearchr.com</h1><a id="toggle" onclick="toggle();">[ - ]</a></div>
    <form id="generate-form" action="ajax.php?generate" method="post">
      <p>Title <span class="desc">(displayed at the top)</span></p>
      <input class="title-textbox textbox" type="text" name="title" />
      <p>Dimensions <span class="desc">(width &times; height)</span></p>
      <input class="width-textbox textbox" type="text" name="width" maxlength="2" /> &times;
      <input class="height-textbox textbox" type="text" name="height" maxlength="2" /><br />
      <p>Words <span class="desc">(separate by spaces)</span></p>
      <textarea class="words-textarea textarea" name="words"></textarea><br />
      <p><input class="backwards-checkbox checkbox" type="checkbox" name="allow_backwards" checked="checked" /><label for="allow_backwards">Allow backwards words</label></p>
      <input class="submit-button button" id="generate-button" type="submit" value="Generate!" />
    </form>
  </div>
  
  <div id="puzzle-area" style="display:none">
    <div id="puzzle">
      
    </div>
  </div>
  
  <div id="wordlist" style="display:none">
    <h3>Word List <span class="desc">(Hover to see solution)</span></h3>
    <div id="wordlist-words">
    
    </div>
    <br style="clear:both;" />
  </div>

<script>

function display_puzzle(puzzle, words) {
  $('#puzzle-area').fadeOut(500);
  $('#puzzle').html('');
  
  $('#wordlist').fadeOut(500);
  $('#wordlist-words').html('');
  
  width = puzzle.width;
  height = puzzle.height;
  console.log(width + " " + height);
  var title = $('<h2>' + puzzle.title + '</h2><span id="print"><a href="#" onclick="window.print()"><img class="print-icon" src="css/printer-icon.png" /> Print</a></span><br style="clear:both;" />');
  title.appendTo($('#puzzle'));
  var table = $('<table id="puzzle-table"></table>');
  for (var i = 0; i < height; i++) {
    var row = $('<tr id="row-'+i+'"></tr>');
    for (var j = 0; j < width; j++) {
      var cell = $('<td id="'+i+'-'+j+'">' + puzzle.text[i][j] + '</td>');
      cell.appendTo(row);
    }
    row.appendTo(table);
  }
  title.appendTo($('#puzzle'));
  table.appendTo($('#puzzle'));

  var wordlist = $('#wordlist');
  var container = $('#wordlist-words');
  for (var i = 0; i < words.length; i++) {
    word = words[i];
    for (var j = 0; j < word.coords.length; j++) {
      var cell = $('#' + word.coords[j].y + '-' + word.coords[j].x);
      cell.addClass('word-' + word.id);
    }
    var worddiv = $('<div class="word" id="word-'+word.id+'">'+word.word+'</div>');
    worddiv.data('word-id', word.id);

    worddiv.hover(function() {
      var id = $(this).data('word-id');
      $('#puzzle td').removeClass('selected');
      $('#puzzle td').css('color','#ccc');
      var selected = $('#puzzle td.word-'+id);
      selected.css('color', '#000');
      selected.addClass('selected');
    }, function() {
      var id = $(this).data('word-id');
      $('#puzzle td').removeClass('selected');
      $('#puzzle td').css('color','#000');
    });
      
    worddiv.appendTo(container);
      
  }
    
  $('#puzzle-area').delay(500).fadeIn(500);
  $('#wordlist').delay(750).fadeIn(500);
}

function ajax_form() {
  var form = '#generate-form';
  var form_message = form + '-message';
 
  // en/disable submit button
  var disableSubmit = function(val){
    $(form + ' input[type=submit]').attr('disabled', val);
    $('#generate-button').val('Loading');
  };
  
  // setup jQuery Plugin 'ajaxForm'
  var options = {
    dataType:  'json',
    beforeSubmit: function(){
      // run form validations if they exist
      if(typeof form_validations == "function" && !form_validations()) {
        // this will prevent the form from being subitted
        return false;
      }
      disableSubmit(true);
    },
    success: function(json){
      var puzzle = json.puzzle;
      var words = json.words_data;
      console.log(json);
      display_puzzle(puzzle, words);
      disableSubmit(false);
      $('#generate-button').val('Generate!');
    }
  };

  $(form).ajaxForm(options);

}

function toggle() {
  $('#controls form').toggle(500);
  if ($('#controls form').css('opacity') < 1.0) {
    $('#controls').animate({ opacity: 1.0 }, 500);
    $('#toggle').html('[ - ]');
  } else {
    $('#controls').animate({ opacity: 0.6 }, 500);
    $('#toggle').html('[ + ]');
  }
}

$(document).ready(function() {
  ajax_form();
});
</script>