/*Filename: pops-tutorial_client.js
 *Author: Jean R Clemenceau
 *Date Created: 01/17/2017
 *Contains the javascript scripts necessary for the tutorial page
*/

$(document).ready(function(){
  //Image replacements
  $('.tut_block_img_selector').hover(function(){
    var theImg = $('#'+$(this).attr('img_node'));
    theImg.attr('src',$(this).attr('new_src'));
  },function(){
    var theImg = $('#'+$(this).attr('img_node'));
    theImg.attr('src',theImg.attr('default_src'));
  });
});
