/*Filename: pops-activity_client.js
 *Author: Jean R Clemenceau
 *Date Created: 05/31/2016
 *Contains the javascript scripts necessary for the activity query process
 *Assumes client has loaded jQuery
 * Assumes client has loaded javascript/result_manipulation.js
*/

//Bind events to static elements
$(document).ready(function(){

  //submit Main Query
  $('#submit_q').on('click', function(){
    executeQuery('pops-activity_process.php',queryResponseSetup,new FormData($('#activity_query_form')[0]), 'activity_query_token');
    return false; //prevent redirect
  });

  //submit re-sort Query
  $('#sortby_metric').on('change', function(){
    var newForm = new FormData($('#activity_query_form')[0]);
    newForm.append('sortby_metric',$('#sortby_metric').val());
    executeQuery('pops-activity_process.php',queryResponseSetup,newForm,'activity_query_token');
    return false; //prevent redirect
  });

  // Toggle Metrics
  $('#showED50').on('change',function(){
    $(".ED50:not(.filtered)").toggle();
    adjustRowSpan('.res_info');
  });

  $('#showAUC').on('change',function(){
    $(".AUC:not(.filtered)").toggle();
    adjustRowSpan('.res_info');
  });

  //Toggle "Additonal info" columns
  $('#showInfo').on('change',function(){
    $(".extra_data").toggle();
  });

  //Highlight cell lines
  $('#highlight_cellline').on('change',function(){
    var cl = $(this).val();
    $(".tinycell.highlighted").removeClass('highlighted');
    $(".tinycell[cellLine='"+cl+"']").addClass('highlighted');
  });

  //Transfer selected options between select elements
  $('.hide_option').on('click',function(){
    $('#hide_list').prepend( $('#show_list option:selected') );
    $('#hide_list option:selected').each(function(){
      $( '[id|='+$(this).val()+']' ).hide();
      $( '[id|='+$(this).val()+']' ).addClass('filtered');
    });
    $('#show_list option:selected').removeAttr('selected');
    return false;
  });
  $('.show_option').on('click',function(){

    var selectorFilter = '';
    if(!$('#showAUC').is(':checked')){
      selectorFilter = selectorFilter + ':not(.AUC)';
    }
    if(!$('#showED50').is(':checked')){
      selectorFilter = selectorFilter + ':not(.ED50)';
    }

    $('#show_list').prepend( $('#hide_list option:selected') );
    $('#show_list option:selected').each(function(){
      $( '[id|='+$(this).val()+']'+selectorFilter ).show();
      $( '[id|='+$(this).val()+']').removeClass('filtered');
    });
    $('#hide_list option:selected').removeAttr('selected');
    return false;
  });

  //Enable "select all compounds"
  $('#select_all_shown').on('change',function(){
    if( $(this).is(':checked') == true ){
      $('#show_list option').prop('selected',true);
    }else{
      $('#show_list option').removeAttr('selected');
    }
  });
  $('#select_all_hidden').on('change',function(){
    if( $(this).is(':checked') == true ){
      $('#hide_list option').prop('selected',true);
    }else{
      $('#hide_list option').removeAttr('selected');
    }
  });


  //Populate compound structure modal
  $('#results-panel').on('click','.cmpd_strct_show', function(){
    $('#structure_modal_title').text($(this).attr('SWID') );
    $('#structure_modal_body').html("<img alt='Compund Sctructure could not be loaded' src='"+$(this).attr('img_path')+"'/>" )
    $('#structure_modal_footer').hide();
  });

  //Populate marker heatmap  modal
  $('#results-panel').on('click','.tinycell', function(){
    // var modal_content = 'Curve could not be loaded';
    if($(this).attr('curve_url') != ''){
      var modal_content="<iframe style='width:100%;height:75%' alt='Curve could not be loaded' src='"+$(this).attr('curve_url')+"'/>";

      $('#structure_modal_title').text($(this).attr('SWID')+": "+$(this).attr('cellLine') );
      $('#structure_modal_body').html(modal_content);
      $('#structure_modal_footer').html("<a target='_BLANK' href='"+$(this).attr('curve_url')+"'><button class='btn btn-default'>Open in new window</button>" );
      $('#structure_modal_footer').show();
    }else{
      return false;
    }

  });

  //Toggle filtering mode
  $('#filter_mode_toggle').on('click',function(){
    $('#result_filters_1').toggle();
    $('#result_filters_2').toggle();
    if($('#result_filters_1:visible').length > 0){
      $(this).text('Filter Compounds');
    }else{
      $(this).text('Customize Output');
    }
    return false;
  });

});


//Add functions once the AJAX table has been loaded
function queryResponseSetup( response ) {

  // Load table and show filter panel
  $('#results-panel').html(response.resultsTable);
  $('#control-panel-filters').show();

  //Reset and Set up filters
  $('#hide_list option').remove();
  $('#show_list option').remove();
  $('#highlight_cellline option:not(#highlight_cellline_blank)').remove();
  addIdList('swids', response.compounds);
  populateSelect('show_list', 'swids');
  addIdList('clines', response.clines);
  populateSelect('highlight_cellline', 'clines');

  //Activate download buttons
  setCSVlink(response.ED50_fname, 'getED50');
  setCSVlink(response.AUC_fname, 'getAUC');

  // Reset select lists
  $('#highlight_cellline').val('');

  // Add floating info box
  $('#compound_details').empty();
  $('#compound_details').append(response.info_box);
  $('#compound_details').show();

///////////////////
  $('#showInfo').removeAttr('checked');

  //Set AUC toggle control initial state
  if($('tr.AUC:visible').length > 0 ){
    $('#showAUC').attr('checked','TRUE'); //TODO FIX THIS
  }else{
    $('#showAUC').removeAttr('checked');
  }

  //Activate metric toggle controls
  if($("tr.AUC").length < 1 ){
      $('#showAUC').attr('disabled',true);
  }else{
      $('#showAUC').removeAttr('disabled');
  }
  if($("tr.ED50").length < 1 ){
      $('#showED50').attr('disabled',true);
  }else{
      $('#showED50').removeAttr('disabled');
  }

  //Activate extra info toggle controls
  $('#showInfo').removeAttr('disabled');

  //Show data in info box from point in heatmap
  $('.tinycell').hover(
    function(){
      var newTxtColor = getHighContrast($(this).css('background-color'));

      $('#hover_id').html($(this).attr('SWID'));
      if( $(this).attr('common_name') != 'NA'){
        $('#hover_name').html($(this).attr('common_name'));
      }
      $('#hover_cline').html($(this).attr('cellLine'));
      $('#hover_data').html($(this).attr('micromolar'));
      $('#hover_extra').html($(this).attr('logData'));
      $('.info_box').css('background-color', $(this).css('background-color'));
      $('.info_box table').css('color', newTxtColor);
      $('.info_box').show();
    },
    function(){
      $('.info_box').hide();
      $('#hover_id').html('');
      $('#hover_name').html('');
      $('#hover_cline').html('');
      $('#hover_data').html('');
      $('#hover_extra').html('');
    }
   );
}
