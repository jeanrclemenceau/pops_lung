<?php
/*Filename: pops-enet.php
 *Author: Jean Clemenceau
 *Date Created: 5/19/2016
 *Contains the main tool to query Elastic Net data.
*/

require_once('pops_header.php');
require_once('pops_dbi.php');

require_login('pops-enet.php');
$elasticnet_query_token = request_form_token('elasticnet_query_form');

?>
<head>
  <?php
    add_setup('Query Elastic Net');
    add_styles('pops_bootstrap_query.css');
    add_scripts('bootstrap-3.3.6-dist/js/bootstrap-slider.min.js');
    add_scripts('result_manipulation.js');
    add_scripts('pops-enet_client.js');
    add_styles('pops-mutation_card_style.css');
  ?>
  <link type='text/css' href='javascript/bootstrap-3.3.6-dist/css/bootstrap-slider.min.css' rel='stylesheet'>
  <?php     add_styles('pops_bootstrap_slider_custom.css'); ?>
</head>
<body>
<?php
  print_navigation('query POPS-Lung','elastic net');
?>
<div class='container-fluid text-center' id='main_content' >
  <div class='pops-control-panel row'>

    <!-- This panel contains the form for query input -->
    <div class='col-md-6' id='control-panel-query'>
      <div class='control-panel-header '>Submit a New Elastic Net Query</div>

      <form id='elasticnet_query_form' class='form' action='#results-panel' method='post' role='form' enctype='multipart/form-data'>
        <div class='form-group'>
          <label class='control-label col-md-1' for='input_elasticnet'>Input:</label>
          <div class='col-md-11'>
            <input class='form-control' type='text' id='input_elasticnet' name='input_elasticnet' maxlength=254 required/>
          </div>
        </div>

        <div class='col-md-offset-1 col-md-5'>
            <input type="hidden" name="MAX_FILE_SIZE" value="20000" />
            <input type='file' id='elasticnet_file' name='elasticnet_file'/>
        </div>

        <div class='form-group col-md-offset-1 col-md-11'>
          <label class="radio-inline">
            <input type="radio" name="search_parameter" id="search_parameter_marker" value="marker" checked>Marker
          </label>
          <label class="radio-inline">
            <input type="radio" name="search_parameter" id="search_parameter_SWID" value="SWID" >SWID
          </label>
          <label class="radio-inline">
            <input type="radio" name="search_parameter" id="search_parameter_name" value="common_name">Compound Name
          </label>
        </div>
        <div class='clearfix'></div>

        <div class='form-group col-md-6 col-xs-12'>
          <label class='select-label' for='feature_list'>Feature Set
            <input type='checkbox' id='select_all_features' title='Select all feature sets'/>
          </label>
          <select multiple class='form-control' id='feature_list' name='feature_list[]' size=4>
            <?php
            foreach ($enetFeatureSetMap as $abrv => $name) {
              $formatName = ucwords( str_replace('_',' ',$name) );
              echo "<option value='$abrv'>$formatName</option>";
            }
            ?>
          </select>
        </div>

        <div class='form-group col-md-6 col-xs-12'>
          <select class='form-control' id='query_metric' name='query_metric'>
            <option value='%' selected>All Metrics</option>
            <option value='ED50'>ED50</option>
            <option value='AUC'>AUC</option>
          </select>
        </div>

        <input type='hidden' id='elasticnet_query_token' name='token' value='<?php echo $elasticnet_query_token; ?>' />
        <input type='hidden' name='formName' id='formName' value='elasticnet_query_form'>

        <div class='form-group'>
          <button class='btn btn-default col-md-3 col-md-offset-3 col-xs-12' name='submit_q' id='submit_q'>Search</button>
        </div>
        <div class='form-group col-xs-12 hidden-lg hidden-md'><hr></div>
      </form>

    </div>
    <!-- This panel contains the form for result filters -->
    <div class='col-md-6' id='control-panel-filters'>
      <div class='control-panel-header hidden-sm hidden-xs'>Filter Your Results</div>

      <form id='query_results_filter_form' class='form' action='#' method='post' role='form' enctype='multipart/form-data'>

        <div id='result_filters_1'>
          <div class='form-group'>
            <button class='btn btn-default col-md-6 col-xs-12' name='getENET' id='getENET' type='button' disabled>Download Markers</button>
          </div>

          <!-- metric filter -->
          <div class='form-group col-md-6 col-xs-12'>
            <select class='form-control' id='filter_metric' name='filter_features'>
              <option value='' selected>Show All Metrics</option>
              <option value='ED50'>Show ED50</option>
              <option value='AUC'>Show AUC</option>
            </select>
          </div>

          <!-- type filter -->
          <div class='form-group  col-md-6 col-xs-12'>
              <select class='form-control' id='filter_type' name='filter_type'>
                <option value='' selected>Filter Feature Sets</option>
              </select>
          </div>

          <!-- scale filter -->
          <div class='form-group col-md-6 col-xs-12'>
            <select class='form-control' id='filter_scale' name='filter_scale'>
              <option value='' selected>Show All Scales</option>
              <option value='Linear'>Show Linear</option>
              <option value='Log10'>Show Log10</option>
            </select>
          </div>

          <!-- weight slider -->
          <div class='form-group col-md-6 col-xs-12'>
            <label class='select-label' for='weight_slider'>Filter Weight</label>
            <span id='weight_slider_min_val' class='sliderValue'></span>
            <input id='weight_slider' name='weight_slider' type='text' class='sliderContainer'/>
            <span id='weight_slider_max_val' class='sliderValue'></span>
          </div>

          <!-- frequency slider -->
          <div class='form-group col-md-6 col-xs-12'>
            <label class='select-label' for='freq_slider'>Filter Frequency</label>
            <input id='freq_slider' name='freq_slider' type='text' class='sliderContainer'/>
            <span id='freq_slider_val' class='sliderValue'></span>
          </div>

          <input type='hidden' name='prevQuery' id='prevQuery' value=''>
          <button class='filter_mode_toggle btn btn-default col-xs-offset-6 col-xs-6' id='filter_compounds_toggle'>Targeted Filters</button>

        </div>

        <!-- This section of the panel contains row filters  by compounds -->
        <div id='result_filters_2' >
        <div class='row'>
          <div class='form-group col-md-5 col-xs-12'>
            <label class='select-label' for='show_list'>Displayed Compounds
              <input type='checkbox' id='select_all_shown' title='Select all displayed compounds'/>
            </label>
            <select multiple class='form-control' id='show_list' name='show_list' size=7>
            </select>
          </div>

          <div class='show_list_spacer col-md-2 col-xs-12'>
            <!-- vertical align buttons -->
            <div class='hidden-xs hidden-sm' style='height:30px;'></div>
            <!-- buttons that hide compounds -->
            <button id='hide_option' class='btn hide_option hidden-xs hidden-sm' title='Hide selected compounds'><span class="glyphicon glyphicon glyphicon-chevron-right"></span><span class="glyphicon glyphicon glyphicon-chevron-right"></span></button>

            <button id='hide_option' class='btn hide_option hidden-md hidden-lg' title='Hide selected compounds'><span class="glyphicon glyphicon glyphicon-chevron-down"></span><br><span class="glyphicon glyphicon glyphicon-chevron-down"></span></button>

            <!-- buttons that show compounds -->
            <button id='show_option' class='btn show_option hidden-xs hidden-sm' title='Show selected compounds'><span class="glyphicon glyphicon glyphicon-chevron-left"></span><span class="glyphicon glyphicon glyphicon-chevron-left"></span></button>

            <button id='hide_option' class='btn show_option hidden-md hidden-lg' 'title='Show selected compounds''><span class="glyphicon glyphicon glyphicon-chevron-up"></span><br><span class="glyphicon glyphicon glyphicon-chevron-up"></span></button>
          </div>

          <div class='form-group col-md-5 col-xs-12'>
            <label class='select-label' for='show_list'>Hidden Compounds
              <input type='checkbox' id='select_all_hidden' title='Select all hidden compounds'/>
</label>
            <select multiple class='form-control' id='hide_list' name='hide_list' size=7>
            </select>
          </div>
        </div>
        <div class='row'>
          <div class='form-group col-xs-6'>
            <select class='form-control' id='row_filter_type' name='row_filter_type'>
              <option value='swids'>Filter by SWID</option>
              <option value='markers'>Filter by Marker</option>
            </select>
          </div>
          <button class='filter_mode_toggle btn btn-default col-xs-6' id='custom_output_toggle'>Customize Output</button>
        </div>
      </div>
      </form>
    </div>
  </div>


  <div class='container-fluid text-center pops-results-panel' id='results-panel'>
    <div class='placeHolderImg'>Elastic Net Query</div>
    <img class='img-responsive placeHolderImg' src='images/POPS_lung_logo.png'/>
  </div>

  <!-- Additional dialog boxes -->
  <div id='cmpd_structure_modal' class='modal fade' role='dialog'>
    <div class='modal-dialog'>
      <div class='modal-content'>
        <div class='modal-header' id='structure_modal_hdr'>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h3 class='modal-title' id='structure_modal_title'></h3>
        </div>
        <div class='modal-body' id='structure_modal_body'>
        </div>
        <div class='modal-footer' id='structure_modal_footer' style='display:none;text-align:center;'>
        </div>
      </div>
    </div>
  </div>
  <!-- <div id='compound_details' class='info_box' style='display:none'></div> TODO remove-->
  <div id='gene_card_modal' class='modal fade' role='dialog'>
    <div class='modal-dialog'>
      <div class='modal-content'>
        <div class='modal-header' id='gene_card_modal_hdr'>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h3 class='modal-title' id='gene_card_modal_title'></h3>
        </div>
        <div class='modal-body' id='gene_card_modal_body'>
          <div id='gene_card_modal_genedetails'> </div>
          <div id='gene_card_modal_cellinemuts'> </div>
          <div id='gene_card_modal_mutdetails'> </div>
        </div>
        <div class='modal-footer' id='gene_card_modal_footer' style='display:none;text-align:center;'>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<?php
  print_footer();
?>
</body>
