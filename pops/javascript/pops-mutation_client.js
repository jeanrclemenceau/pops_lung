/*Filename: pops-mutation_client.js
 *Author: Jean R Clemenceau
 *Date Created: 05/31/2015
 *Contains the javascript scripts necessary for the mutation query process
 *Assumes client has loaded jQuery
 * Assumes client has loaded javascript/result_manipulation.js
*/

//Bind events to static elements
$(document).ready(function(){

  //submit Main Query
  $('#submit_q').on('click', function(){
    executeQuery('pops-mutation_process.php',queryResponseSetup,new FormData($('#mutation_query_form')[0]), 'mutation_query_token');
    return false; //prevent redirect
  });

  //////////MODALS/////////
  //Populate pegplot modal
  $('#results-panel').on('click','a.res_info', function(e){
    if($(this).attr('plot_url') != ''){
      var modal_content="<iframe style='width:100%;height:75%' alt='Curve could not be loaded' src='"+$(this).attr('plot_url')+"'/>";

      $('#structure_modal_title').text($(this).attr('gene'));
      $('#structure_modal_body').html(modal_content);
      $('#structure_modal_footer').html("<a target='_BLANK' href='"+$(this).attr('plot_url')+"'><button class='btn btn-default'>Open in new window</button>" );
      $('#structure_modal_footer').show();
    }else{
      return false;
    }
  });

  //Populate mutation card modal
  $('#results-panel').on('click','.tinycell', function(){
    var theFormName = 'mutation_card_query_form';
    var token_id = 'mutation_card_query_token';
    var cellData ={
        formName: theFormName,
        token: $('#'+token_id).val(),
        gn: $(this).attr('gene'),
        cl: $(this).attr('cellLine')
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

  });


  /////////BUTTON ACTIONS///////
  //Enable "select all" buttons
  $('#select_all_cell_lines').on('change',function(){
    if( $(this).is(':checked') == true ){
      $('#cell_line_list option').prop('selected',true);
    }else{
      $('#cell_line_list option').removeAttr('selected');
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
    if($('#result_filters_1:visible').length > 0){
      $(this).text('Customize Output');
    }else{
      $(this).text('Targeted Filters');
    }
    return false;
  });

  //Enable Pagination buttons
  $('#results-panel').on('click','#goto_first_page', function(){
    var newForm = new FormData($('#mutation_query_form')[0]);
    newForm.append("pageMultiplier",0);
    executeQuery('pops-mutation_process.php',queryResponseSetup,newForm, 'mutation_query_token');
    return false; //prevent redirect
  });

  $('#results-panel').on('click','#goto_prev_page', function(){
    var pMult = parseInt($('#pageMultiplier').val())-1
    var newForm = new FormData($('#mutation_query_form')[0]);
    newForm.append("pageMultiplier",pMult);
    executeQuery('pops-mutation_process.php',queryResponseSetup,newForm, 'mutation_query_token');
    return false; //prevent redirect
  });

  $('#results-panel').on('click','#goto_next_page', function(){
    var pMult = parseInt($('#pageMultiplier').val())+1
    var newForm = new FormData($('#mutation_query_form')[0]);
    newForm.append("pageMultiplier",pMult);
    executeQuery('pops-mutation_process.php',queryResponseSetup,newForm, 'mutation_query_token');
    return false; //prevent redirect
  });

  $('#results-panel').on('click','#goto_last_page', function(){
    var pMult = Math.floor(parseInt($('#maxResultCount').text())/200);
    var newForm = new FormData($('#mutation_query_form')[0]);
    newForm.append("pageMultiplier",pMult);
    executeQuery('pops-mutation_process.php',queryResponseSetup,newForm, 'mutation_query_token');
    return false; //prevent redirect
  });

  ///////////FILTER RESULTS//////////
  //Incorporate all filters to determine if an element should be shown.
  function selectiveShow(aRow){
    var show = true;

    if($('#filter_variant').val() != ''){
      show = show && (aRow.attr('variant') == $('#filter_variant').val());
    }
    if($('#filter_dataset').val() != ''){
      show = show && (aRow.attr('dataset') == $('#filter_dataset').val());
    }

    var allele_freqPass = (aRow.attr('allele_freq') >= $('#allele_freq_slider').slider('getValue')[0] ) && (aRow.attr('allele_freq') <= $('#allele_freq_slider').slider('getValue')[1] );
    show = show && allele_freqPass;

    return show;
  }

  //Highlight cell lines
  $('#highlight_cellline').on('change',function(){
    var cl = $(this).val();
    $(".tinycell.highlightedRed").removeClass('highlightedRed');
    $(".tinycell[cellLine='"+cl+"']").addClass('highlightedRed');
  });

  //Transfer selected options between select elements & hide/show
  $('.hide_option').on('click',function(){
    $('#hide_list').prepend( $('#show_list option:selected') );
    $('#hide_list option:selected').each(function(){
      $( 'tr.resultRow[id='+$(this).val()+']' ).hide();
    });
    $('#show_list option:selected').removeAttr('selected');
    return false;
  });
  $('.show_option').on('click',function(){
    $('#show_list').prepend( $('#hide_list option:selected') );
    $('#show_list option:selected').each(function(){
      $('tr.resultRow[id='+$(this).val()+']').show();
    });
    $('#hide_list option:selected').removeAttr('selected');
    return false;
  });

  // Setup allele freqency slider filters
  $('#allele_freq_slider').on('slide',function(){
    var valMin = $(this).slider('getValue')[0];
    var valMax = $(this).slider('getValue')[1];
    $('td.tinycell:not(.blurred):not(.filtered)').filter(function(){
      return ($(this).attr('allele_freq')<valMin || $(this).attr('allele_freq')>valMax)
    }).addClass('blurred');
    $('td.tinycell.blurred:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).removeClass('blurred');
  });
  $('#allele_freq_slider').on('slideStop',function(){
    $('#allele_freq_slider_min_val').text( $(this).slider('getValue')[0].toFixed(3));
    $('#allele_freq_slider_max_val').text( $(this).slider('getValue')[1].toFixed(3));
  });

  // Setup variant type filter
  $('#filter_variant').on('change',function(){
    if($(this).val() != ''){
      $("td.tinycell[variant!='"+$(this).val()+"']:not(.blurred):not(.filtered)").addClass('blurred');
    }
    $('td.tinycell.blurred:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).removeClass('blurred');
  });

  // Setup variant type filter
  $('#filter_dataset').on('change',function(){
    if($(this).val() != ''){
      $("td.tinycell[dataset!='"+$(this).val()+"']:not(.blurred):not(.filtered)").addClass('blurred');
    }
    $('td.tinycell.blurred:not(.filtered)').filter(function(){
      return selectiveShow($(this));
    }).removeClass('blurred');
  });

  //TODO change from hiding to shading

});


//Add functions once the AJAX table has been loaded
function queryResponseSetup( response ) {

  // Load table and show filter panel
  $('#results-panel').html(response.resultsTable);
  $('#control-panel-filters').show();

  if( $('#pageMultiplier').length==0){
    $('#results-panel').append("<input type='hidden' name='pageMultiplier' id='pageMultiplier' value='"+response.pageMultiplier+"'>");
  }else{
    $('#pageMultiplier').val(response.pageMultiplier);
  }


  //Reset and Set up filters
  $('#hide_list option').remove();
  $('#show_list option').remove();
  $("#filter_dataset option[value!='']").remove();
  $("#filter_variant option[value!='']").remove();
  addIdList('genes', response.genes);
  populateSelect('show_list', 'genes');
  addIdList('clines', response.clines);
  populateSelect('highlight_cellline', 'clines');
  addIdList('datasets', response.datasets);
  populateSelect('filter_dataset', 'datasets');
  addIdList('variants', response.variants);
  populateSelect('filter_variant', 'variants');

  //Setup sliders
  var allele_freq_extrema = JSON.parse(response.allele_freq_extrema);
  $('#allele_freq_slider').slider({
    min: allele_freq_extrema[0]-0.005,
    max: allele_freq_extrema[1]+0.005,
    range: true,
    value: [allele_freq_extrema[0]-0.005,allele_freq_extrema[1]+0.005],
    step:  0.005,
    tooltip_split: true,
    handle: 'custom',
  });

  //Activate download buttons
  setCSVlink(response.Mut_fname, 'getMutation');

  // Reset select lists
  $('#highlight_cellline').val('');

  // Add floating info box
  $('#mutation_details').empty();
  $('#mutation_details').append(response.info_box);
  $('#mutation_details').show();

///////////////////

  //Show data in info box from point in heatmap
  $('.tinycell').hover(
    function(){
      var newTxtColor = getHighContrast($(this).css('background-color'));
      var newBgColor= ($(this).css('background-color') == 'transparent')?'white':$(this).css('background-color');
      var extraMutCnt = ($(this).attr('count')-1 > 0)? "+ "+($(this).attr('count')-1)+" more..." : '';

      $('#hover_gene').html($(this).attr('gene'));
      $('#hover_cline').html($(this).attr('cellLine'));
      $('#hover_status').html($(this).attr('status'));
      $('#hover_mut1').html($(this).attr('mut1'));
      $('#hover_mutcnt').html(extraMutCnt);
      $('.info_box').css('background-color', newBgColor);
      $('.info_box table').css('color', newTxtColor);
      $('.info_box').show();
    },
    function(){
      $('.info_box').hide();
      $('#hover_gene').html('');
      $('#hover_cline').html('');
      $('#hover_status').html('');
      $('#hover_mut1').html('');
      $('#hover_mutcnt').html('');
    }
   );
}
