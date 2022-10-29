/*Filename: pops-sks_client.js
 *Author: Jean R Clemenceau
 *Date Created: 05/31/2015
 *Contains the javascript scripts necessary for the sks query process
 *Assumes client has loaded jQuery
 * Assumes client has loaded javascript/result_manipulation.js
*/


//Bind events to static elements
$(document).ready(function(){

  //submit Main Query
  $('#submit_q').on('click', function(){
    executeQuery('pops-sks_process.php',queryResponseSetup,new FormData($('#scanningks_query_form')[0]), 'scanningks_query_token');
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
  $('#results-panel').on('click','.sks_mrkr_show', function(){
    var modal_content = "<img style='width:100%;height:600;' alt='Scanning KS Plot could not be loaded' src='"+$(this).attr('img_path')+"'/>";
    if($(this).attr('pdf_path') != ''){
      modal_content=modal_content+"<hr><iframe style='width:100%;height:400;' alt='Scanning KS Heatmap could not be loaded' src='"+$(this).attr('pdf_path')+"'/>";
    }

    $('#structure_modal_title').text($(this).attr('mod_title') );
    $('#structure_modal_body').html(modal_content);
  });

  //Populate plot modal
  $('#results-panel').on('click','.sks_plot_show', function(){
    var modal_content ='';
    if($(this).attr('pdf_path') != ''){
      modal_content="<iframe style='width:100%;height:400;' alt='Scanning KS Heatmap could not be loaded' src='"+$(this).attr('pdf_path')+"'/><hr>";
    }
    modal_content=modal_content+"<table class='sksDataModal'><tr><th class='sksDataModalHead'>"+$(this).parent().attr('SWID')+"</th><th class='sksDataModalHead'>"+$(this).parent().attr('marker')+"</th><th  class='sksDataModalHead'>"+$(this).parent().attr('metric')+"</th></tr><tr><th>Median Ratio:</th><td>"+$(this).parent().attr('med_ratio')+"</td></tr><tr><th>Mutant Count:</th><td>"+$(this).parent().attr('mutant_count')+"</td></tr><tr><th>Occurrence in TCGA: </th><td>"+$(this).parent().attr('TCGA_CoOc')+
    "</td></tr><tr><th>P-Value:</th><td>"+$(this).parent().attr('pvalue')+"</td></tr></tr></table><hr>";
    modal_content=modal_content+"<a href='"+$(this).attr('img_path')+"' target='_BLANK'><img style='width:100%;height:600;' alt='Scanning KS Plot could not be loaded' src='"+$(this).attr('img_path')+"'/></a>";

    $('#structure_modal_title').text($(this).attr('mod_title') );
    $('#structure_modal_body').html(modal_content);
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
    $('.resultSKS:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).show();
  });

  //Enable Pagination buttons
  $('#results-panel').on('click','#goto_first_page', function(){
    var newForm = new FormData($('#scanningks_query_form')[0]);
    newForm.append('pageMultiplier',0);
    executeQuery('pops-sks_process.php',queryResponseSetup,newForm, 'scanningks_query_token');
    return false; //prevent redirect
  });

  $('#results-panel').on('click','#goto_prev_page', function(){
    var pMult = parseInt($('#pageMultiplier').val())-1
    var newForm = new FormData($('#scanningks_query_form')[0]);
    newForm.append('pageMultiplier',pMult);
    executeQuery('pops-sks_process.php',queryResponseSetup,newForm, 'scanningks_query_token');
    return false; //prevent redirect
  });

  $('#results-panel').on('click','#goto_next_page', function(){
    var pMult = parseInt($('#pageMultiplier').val())+1
    var newForm = new FormData($('#scanningks_query_form')[0]);
    newForm.append('pageMultiplier',pMult);
    executeQuery('pops-sks_process.php',queryResponseSetup,newForm, 'scanningks_query_token');
    return false; //prevent redirect
  });

  $('#results-panel').on('click','#goto_last_page', function(){
    var pMult = Math.floor(parseInt($('#maxResultCount').text())/200);
    var newForm = new FormData($('#scanningks_query_form')[0]);
    newForm.append('pageMultiplier',pMult);
    executeQuery('pops-sks_process.php',queryResponseSetup,newForm, 'scanningks_query_token');
    return false; //prevent redirect
  });

///////////FILTER RESULTS//////////

  //Incorporate all filters to determine if an element should be shown.
  function selectiveShow(aRow){
    var show = true;

    if($('#filter_metric').val() != ''){
      show = show && (aRow.attr('metric') == $('#filter_metric').val());
    }
    show = show && (aRow.attr('med_ratio') <= $('#medRat_slider').slider('getValue') );
    show = show && (aRow.attr('TCGA_CoOc') >= $('#TCGA_CoOc_slider').slider('getValue') );
    show = show && (aRow.attr('mutant_count') >= $('#mut_cnt_slider').slider('getValue') );
    show = show && (aRow.attr('pvalue') <= $('#pvalue_slider').slider('getValue') );

    return show;
  }

  //Transfer selected options between select elements
  $('.hide_option').on('click',function(){
    $('#hide_list').prepend( $('#show_list option:selected') );
    $('#hide_list option:selected').each(function(){
      $( '.resultSKS[id*='+$(this).val()+']' ).hide();
      $( '.resultSKS[id*='+$(this).val()+']' ).addClass('filtered');
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
      $( '.resultSKS[id*='+$(this).val()+']').removeClass('filtered');
      $('.resultSKS:not(.filtered)').filter(function(){
        return selectiveShow($(this));
      }).show();
    });
    $('#hide_list option:selected').removeAttr('selected');
    return false;
  });

  // Setup TCGA Cooccurrence slider filters
  $('#medRat_slider').on('slide',function(){
    var val = $(this).slider('getValue');
    $('.resultSKS:visible:not(.filtered)').filter(function(){
      return ($(this).attr('med_ratio') > val)
    }).hide();
    $('.resultSKS:hidden:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).show();
  });
  $('#medRat_slider').on('slideStop',function(){
    $('#medRat_slider_val').text( $(this).slider('getValue') );
  });

  // Setup TCGA Cooccurrence slider filters
  $('#TCGA_CoOc_slider').on('slide',function(){
    var val = $(this).slider('getValue');
    $('.resultSKS:visible:not(.filtered)').filter(function(){
      return ($(this).attr('TCGA_CoOc') < val)
    }).hide();
    $('.resultSKS:hidden:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).show();
  });
  $('#TCGA_CoOc_slider').on('slideStop',function(){
    $('#TCGA_CoOc_slider_val').text( $(this).slider('getValue') );
  });

  // Setup mutant count slider filters
  $('#mut_cnt_slider').on('slide',function(){
    var val = $(this).slider('getValue');
    $('.resultSKS:visible:not(.filtered)').filter(function(){
      return ($(this).attr('mutant_count') < val)
    }).hide();
    $('.resultSKS:hidden:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).show();
  });
  $('#mut_cnt_slider').on('slideStop',function(){
    $('#mut_cnt_slider_val').text( $(this).slider('getValue') );
  });

  // Setup P-value slider filters
  $('#pvalue_slider').on('slide',function(){
    var val = $(this).slider('getValue');
    $('.resultSKS:visible:not(.filtered)').filter(function(){
      return ($(this).attr('pvalue') > val)
    }).hide();
    $('.resultSKS:hidden:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).show();
  });
  $('#pvalue_slider').on('slideStop',function(){
    $('#pvalue_slider_val').text( $(this).slider('getValue') );
  });

  // Setup metric filter
  $('#filter_metric').on('change',function(){
    if($(this).val() != ''){
      $(".resultSKS[metric!='"+$(this).val()+"']:visible:not(.filtered)").hide();
    }
    $('.resultSKS:hidden:not(.filtered)').filter(function(){
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

  //Set up filters
  addIdList('swids', response.compounds);
  populateSelect('show_list', 'swids');
  addIdList('markers', response.markers);

  //Setup sliders
  $('#medRat_slider').slider({
    min: response.min_medRat - 0.005,
    max: 0,
    value: 0,
    step:  0.005,
    handle: 'custom',
    reversed: true
  });
  var tcga_extrema = JSON.parse(response.tcga_extrema);
  $('#TCGA_CoOc_slider').slider({
    min: tcga_extrema[0] + 0.005,
    max: tcga_extrema[1] + 0.005,
    value: tcga_extrema[0],
    step:  0.005,
    handle: 'custom'
  });
  $('#mut_cnt_slider').slider({
    min: 0,
    max: response.max_mut_cnt,
    value: 0,
    step:  1,
    handle: 'custom'
  });
  var pvalSliderenabled = response.max_pval != 0 ? true : false;
  $('#pvalue_slider').slider({
    min: 0,
    max: response.max_pval + 0.005,
    value: response.max_pval,
    step:  0.005,
    handle: 'custom',
    enabled: pvalSliderenabled
  });

  //Activate download buttons
  setCSVlink(response.SKS_fname, 'getSKS');

  // Reset selected objects in lists
  $('#filter_metric').val('');

  $('#control-panel-filters').show();

}
