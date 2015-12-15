/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Checks wether a string contains a given substring.
 * @param {type} it Substring to be searched for
 * @returns {Boolean} true if substring is contained in the string. else returns false.
 */
String.prototype.contains = function(it) { return this.indexOf(it) != -1; };

$(document).ready(function() {
    $('.click').click(function(){
        var temp = $(this).attr('id') + "-clicked";
        //console.log(temp);
        var temp2 = $('#' + temp).attr('class');
        //console.log(temp2);
        if (temp2.contains('active')) {
            return;
        } else {
            $('.active').animate({
                left: '500px',
            }, 300);
            $('.active').animate({
                left: '-500px',
            }, 0);
            
            $('#' + temp).animate({
                left: '0px',
            }, 300);
            
            $('.slide').removeClass('active');
            $('#' + temp).addClass('active');
        }
    });
});
