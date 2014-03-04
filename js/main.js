$(document).ready(function() {
  
    /*
     * In this file:
     * --hover over a piece, show available moves (neighbor and destination in bold; change current market to 'O', neighbor marker to 'x', and destination marker to 'P'
     * --track two clicks: source and destination.
     * --new form for a move string (kinda like chess)
     * --create move string from clicks and put it in text box
     * --submit form with that text box
     * --submit move to class for execution
     * --return as normal
     */
    
    $(".cell").on('click', function() {
        var cell_id = $(this).attr('id');
        //alert(possible_moves[cell_id]);
        if (possible_moves[cell_id].length > 0) {
            $("#" + cell_id).css('font-style: italic;');
            $.each(possible_moves[cell_id], function() {
                //format: peg_to_be_jumped:peg_destination
                var move_map = this.split(":");
                $("#" + cell_id).css("font-style", "italic").text("O");
                $("#" + move_map[0]).css("font-style", "italic").text("O");
                $("#" + move_map[1]).css("font-style", "italic").text("P");
            });
        }
    });
    
});