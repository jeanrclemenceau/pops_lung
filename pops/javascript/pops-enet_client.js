/*Filename: pops-enet_client.js
 *Author: Jean R Clemenceau
 *Date Created: 05/31/2016
 *Contains the javascript scripts necessary for the enet query process
 *Assumes client has loaded jQuery
 * Assumes client has loaded javascript/result_manipulation.js
*/

//Bind events to static elements
$(document).ready(function(){

  //submit Main Query
  $('#submit_q').on('click', function(){
    executeQuery('pops-enet_process.php',queryResponseSetup,new FormData($('#elasticnet_query_form')[0]), 'elasticnet_query_token');
    return false; //prevent redirect
  });

//////////MODALS/////////

  //Populate compound structure modal
  $('#results-panel').on('click','.cmpd_strct_show', function(){
    $('#structure_modal_title').text($(this).attr('mod_title') );
    $('#structure_modal_body').html("<img alt='Compund Sctructure could not be loaded' src='"+$(this).attr('img_path')+"'/>" )
    $('#structure_modal_footer').hide();

  });

  //Populate marker heatmap  modal
  $('#results-panel').on('click','.enet_mrkr_show', function(){
    $('#structure_modal_title').text($(this).attr('mod_title') );
    $('#structure_modal_body').html("<iframe style='width:100%;height:600;' alt='Elastic Net Heatmap could not be loaded' src='"+$(this).attr('pdf_path')+"'/>" )
    $('#structure_modal_footer').show();
    $('#structure_modal_footer').html("<a target='_BLANK' href='"+$(this).attr('explorer')+"'><button class='btn btn-default'>Open in Elastic Net Explorer</button>" );
  });

  //Populate ROC curve modal
  $('#results-panel').on('click','.ROC_curve_show', function(){
    $('#structure_modal_title').text($(this).attr('mod_title') );
    $('#structure_modal_body').html("<iframe style='width:100%;height:35%;' alt='ROC Curve could not be loaded' src='"+$(this).attr('pdf_path')+"'/>" )
    $('#structure_modal_footer').show();
    $('#structure_modal_footer').html("<a target='_BLANK' href='"+$(this).attr('pdf_path')+"'><button class='btn btn-default'>Open in new window</button>" );
  });

  //Populate mutation card modal
  $('#results-panel').on('click','.mut_card_modal_trigger', function(){
    var theFormName = 'mutation_card_query_form';
    var token_id = 'mutation_card_query_token';
    var cellData ={
        formName: theFormName,
        token: $('#'+token_id).val(),
        gn: $(this).attr('gene').replace('_MUT',''),
    };

    //AJAX Call to generate content
    $.ajax({
      url: 'pops-mutation_card.php',
      data: $.param(cellData),
      type: 'POST',
      dataType: 'json',
      success: function(response){
        console.log("AJAX: request Results - External content loaded successfully");
        $('#'+token_id).val(response.newToken);
        $('#gene_card_modal_title').html(response.mainGeneName);
        $('#gene_card_modal_cellinemuts').html(response.allCellLineData);
        $('#gene_card_modal_genedetails').html(response.mainGeneString);
        $('#gene_card_modal_mutdetails'). html(response.mainDataString);
      },
      error: function(xhr, status, errorThrown){
        console.log("AJAX: requestMutCard - Error: " + xhr.status + ' - ' + xhr.statusText );
        $('#gene_card_modal_title').text('Error');
        if(xhr.status == 500){
          renewToken(deserialize(form_serialized)['formName'],token_id);
          $('#gene_card_modal_body').html("<div class='failAlert'><p>Error: Our server could not process your request. Please try again.</p><p>If the error persist, please submit a ticket <a href='pops_contact.php?s=4'>HERE</a> </div>");
        }else{
          $('#gene_card_modal_body').html("<div class='failAlert'>Error: Results could not be loaded</div>");
        }
      }
    });

    //AJAX Call to replace Chromosome field with more specific location
    $.ajax({
      url:"http://rest.genenames.org/fetch/symbol/"+cellData['gn'],
      dataType:"json",
      success: function(data){
        $('#gene_location_field_txt').text(data.response.docs[0].location);
        $('.chromosome_field').hide();
        $('.gene_location_field').show();
      } 
    });

  });

/////////BUTTON ACTIONS///////

  //Enable "select all compounds"
  $('#select_all_features').on('change',function(){
    if( $(this).is(':checked') == true ){
      $('#feature_list option').prop('selected',true);
    }else{
      $('#feature_list option').removeAttr('selected');
    }
  });
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

  //Toggle filtering mode
  $('.filter_mode_toggle').on('click',function(){
    $('#result_filters_1').toggle();
    $('#result_filters_2').toggle();
    return false;
  });

  //Toggle filter criteria
  $('#row_filter_type').on('change',function(){
    $('#show_list').empty();
    $('#hide_list').empty();
    populateSelect('show_list', $(this).val());
    $('.filtered').removeClass('filtered');
    $('tr.resultRow:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).show();
  });

  //Enable Pagination buttons
  $('#results-panel').on('click','#goto_first_page', function(){
    var newForm = new FormData($('#elasticnet_query_form')[0]);
    newForm.append('pageMultiplier',0);
    executeQuery('pops-enet_process.php',queryResponseSetup,newForm, 'elasticnet_query_token');
    return false; //prevent redirect
  });

  $('#results-panel').on('click','#goto_prev_page', function(){
    var pMult = parseInt($('#pageMultiplier').val())-1
    var newForm = new FormData($('#elasticnet_query_form')[0]);
    newForm.append('pageMultiplier',pMult);
    executeQuery('pops-enet_process.php',queryResponseSetup,newForm, 'elasticnet_query_token');
    return false; //prevent redirect
  });

  $('#results-panel').on('click','#goto_next_page', function(){
    var pMult = parseInt($('#pageMultiplier').val())+1
    var newForm = new FormData($('#elasticnet_query_form')[0]);
    newForm.append('pageMultiplier',pMult);
    executeQuery('pops-enet_process.php',queryResponseSetup,newForm, 'elasticnet_query_token');
    return false; //prevent redirect
  });

  $('#results-panel').on('click','#goto_last_page', function(){
    var pMult = Math.floor(parseInt($('#maxResultCount').text())/200);
    var newForm = new FormData($('#elasticnet_query_form')[0]);
    newForm.append('pageMultiplier',pMult);
    executeQuery('pops-enet_process.php',queryResponseSetup,newForm, 'elasticnet_query_token');
    return false; //prevent redirect
  });

  //Mutation card Modal highlighting
  $('#gene_card_modal').on('mouseenter mouseleave',"#mutationTable .tinycell",function(event){
    if(event.type == 'mouseenter'){
      $(".cellLineList[cellline='"+$(this).attr('cellline')+"']").css('background','lightgrey');
    }
    if(event.type == 'mouseleave'){
      $(".cellLineList[cellline='"+$(this).attr('cellline')+"']").css('background','none');
    }
  });

///////////FILTER RESULTS//////////

  //Incorporate all filters to determine if an element should be shown.
  function selectiveShow(aRow){
    var show = true;

    if($('#filter_type').val() != ''){
      show = show && (aRow.attr('type') == $('#filter_type').val());
    }
    if($('#filter_metric').val() != ''){
      show = show && (aRow.attr('metric') == $('#filter_metric').val());
    }
    if($('#filter_scale').val() != ''){
      show = show && (aRow.attr('scale') == $('#filter_scale').val());
    }

    show = show && (aRow.attr('frequency') >= $('#freq_slider').slider('getValue') );

    var wghtPass = (aRow.attr('weight') <= $('#weight_slider').slider('getValue')[0] ) || (aRow.attr('weight') >= $('#weight_slider').slider('getValue')[1] );
    show = show && wghtPass;

    return show;
  }

  //Transfer selected options between select elements
  $('.hide_option').on('click',function(){
    $('#hide_list').prepend( $('#show_list option:selected') );
    $('#hide_list option:selected').each(function(){
      $( "tr.resultRow[id*='"+$(this).val()+"']" ).hide();
      $( "tr.resultRow[id*='"+$(this).val()+"']" ).addClass('filtered');
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
      $( "tr.resultRow[id*='"+$(this).val()+"']").removeClass('filtered');
      $('tr.resultRow:not(.filtered)').filter(function(){
        return selectiveShow($(this));
      }).show();
    });
    $('#hide_list option:selected').removeAttr('selected');
    return false;
  });

  // Setup frequency slider filters
  $('#freq_slider').on('slide',function(){
    var val = $(this).slider('getValue');
    $('tr.resultRow:visible:not(.filtered)').filter(function(){
      return ($(this).attr('frequency') < val)
    }).hide();
    $('tr.resultRow:hidden:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).show();
  });
  $('#freq_slider').on('slideStop',function(){
    $('#freq_slider_val').text( $(this).slider('getValue') );
  });

  // Setup weight slider filters
  $('#weight_slider').on('slide',function(){
    var valMin = $(this).slider('getValue')[0];
    var valMax = $(this).slider('getValue')[1];
    $('tr.resultRow:visible:not(.filtered)').filter(function(){
      return ($(this).attr('weight')>valMin || $(this).attr('weight')<valMax)
    }).hide();
    $('tr.resultRow:hidden:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).show();
  });
  $('#weight_slider').on('slideStop',function(){
    $('#weight_slider_min_val').text( $(this).slider('getValue')[0].toFixed(3));
    $('#weight_slider_max_val').text( $(this).slider('getValue')[1].toFixed(3));
  });

  // Setup feature type filter
  $('#filter_type').on('change',function(){
    if($(this).val() != ''){
      $("tr.resultRow[type!='"+$(this).val()+"']:visible:not(.filtered)").hide();
    }
    $('tr.resultRow:hidden:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).show();
  });

  // Setup metric filter
  $('#filter_metric').on('change',function(){
    if($(this).val() != ''){
      $("tr.resultRow[metric!='"+$(this).val()+"']:visible:not(.filtered)").hide();
    }
    $('tr.resultRow:hidden:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).show();
  });

  // Setup scale filter
  $('#filter_scale').on('change',function(){
    if($(this).val() != ''){
      $("tr.resultRow[scale!='"+$(this).val()+"']:visible:not(.filtered)").hide();
    }
    $('tr.resultRow:hidden:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).show();
  });

});

////////
//Add functions once the AJAX table has been loaded
function queryResponseSetup( response ) {

  // Load table and show filter panel
  $('#results-panel').html(response.resultsTable);

  $('#results-panel').append("<input type='hidden' name='pageMultiplier' id='pageMultiplier' value='"+response.pageMultiplier+"'>");

  //Stop executon after displaying error message
  if(response.error == true){
    //TODO Reset token
    return;
  }

  // Reset list contents
  $('#show_list').empty();
  $('#hide_list').empty();
  $('#filter_type').empty();
  $('#filter_type').append("<option value='' selected>Filter Feature Sets</option>");

  //Set up filters
  addIdList('types', response.types);
  populateSelect('filter_type', 'types');
  addIdList('swids', response.compounds);
  populateSelect('show_list', 'swids');
  addIdList('markers', response.markers);

  //Setup sliders
  $('#freq_slider').slider({
    min: 0,
    max: response.max_freq + 0.005,
    value: 0,
    step:  0.005,
    handle: 'custom'
  });
  var weight_extrema = JSON.parse(response.weight_extrema);
  $('#weight_slider').slider({
    min: weight_extrema[0]-0.005,
    max: weight_extrema[1]+0.005,
    range: true,
    value: [0,0],
    step:  0.005,
    tooltip_split: true,
    handle: 'custom'
  });

  //Activate download buttons
  setCSVlink(response.ENET_fname, 'getENET');

  // Reset selected objects in lists
  $('#filter_metric').val('');
  $('#filter_scale').val('');
  $('#filter_type').val('');

  $('#control-panel-filters').show();

}
