<?php
/*Filename: pops-sks.php
 *Author: Jean Clemenceau
 *Date Created: 5/19/2016
 *Contains the main tool to query Scanning KS data.
*/

require_once('pops_header.php');
require_once('pops_dbi.php');
// require_once('pops_toolfunctions.php');

require_login('pops-sks.php');
$scanningks_query_token = request_form_token('scanningks_query_form');

?>
<head>
  <?php
  add_setup('Query Scanning KS');
  add_styles('pops_bootstrap_query.css');
  add_styles('pops_bootstrap_sks.css');
  add_scripts('bootstrap-3.3.6-dist/js/bootstrap-slider.min.js');
  add_scripts('result_manipulation.js');
  add_scripts('pops-sks_client.js');
  ?>
  <link type='text/css' href='javascript/bootstrap-3.3.6-dist/css/bootstrap-slider.min.css' rel='stylesheet'>
  <?php     add_styles('pops_bootstrap_slider_custom.css'); ?>
</head>
<body>
<?php
  print_navigation('query POPS-Lung','scanning KS');
?>
<div class='container-fluid text-center' id='main_content' >
  <div class='pops-control-panel row'>

    <!-- This panel contains the form for query input -->
    <div class='col-md-6' id='control-panel-query'>
      <div class='control-panel-header '>Submit a New Scanning KS Query</div>

      <form id='scanningks_query_form' class='form' action='#results-panel' method='post' role='form' enctype='multipart/form-data'>
        <div class='form-group'>
          <label class='control-label col-md-1' for='input_scanningks'>Input:</label>
          <div class='col-md-11'>
            <input class='form-control' type='text' id='input_scanningks' name='input_scanningks' maxlength=254 required/>
          </div>
        </div>

        <div class='col-md-offset-1 col-md-11'>
            <input type='file' id='scanningks_file' name='scanningks_file'/>
        </div>
        <div class='col-md-offset-1 col-md-5'>
          <label class="radio-inline">
            <input type="radio" name="search_parameter" id="search_parameter_SWID" value="SWID" checked>SWID
          </label>
          <label class="radio-inline">
            <input type="radio" name="search_parameter" id="search_parameter_name" value="common_name">Compound Name
          </label>
        </div>
        <div class='clearfix'></div>
        <div class='form-group'>
          <label class='control-label col-md-1' for='input_scanningks'>Gene:</label>
          <div class='col-md-5'>
            <input class='form-control' type='text' id='input_gene' name='input_gene'/>
          </div>
        </div>

        <div class='form-group'>
          <label class='control-label col-md-1' for='input_scanningks'>P-value:</label>
          <div class='col-md-5'>
            <input class='form-control' type='number' id='input_pval' name='input_pval' placeholder='Max P-Value'/>
          </div>
        </div>

        <div class='form-group'>
          <label class='control-label col-md-1' for='input_scanningks'>Median Ratio:</label>
          <div class='col-md-5'>
            <input class='form-control' type='number' id='input_medrat' name='input_medrat' placeholder='Max Median Ratio'>
          </div>
        </div>

        <div class='form-group col-md-offset-1 col-md-5 col-xs-12'>
          <select class='form-control' id='query_metric' name='query_metric'>
            <option value='%' selected>All Metrics</option>
            <option value='ED50'>ED50</option>
            <option value='AUC'>AUC</option>
          </select>
        </div>

        <div class='col-md-offset-1 col-md-5'>
          <label class="radio-inline">
            <input type="radio" name="output_mode" id="output_mode_table" value="table" checked>Show Table
          </label>
          <label class="radio-inline">
            <input type="radio" name="output_mode" id="output_mode_table" value="plots">Show Plots
          </label>
        </div>

        <div class='form-group'>
          <button class='btn btn-default col-md-6 col-xs-12' name='submit_q' id='submit_q'>Search</button>
        </div>

        <input type='hidden' id='scanningks_query_token' name='token' value='<?php echo $scanningks_query_token; ?>' />
        <input type='hidden' name='formName' id='formName' value='scanningks_query_form'>


        <div class='form-group col-xs-12 hidden-lg hidden-md'><hr></div>
      </form>
    </div>

  <!-- This panel contains the form for result filters -->
  <div class='col-md-6' id='control-panel-filters'>
    <div class='control-panel-header hidden-sm hidden-xs'>Filter Your Results</div>

    <form id='query_results_filter_form' class='form' action='#' method='post' role='form' enctype='multipart/form-data'>

      <div id='result_filters_1'>
        <div class='form-group'>
          <button class='btn btn-default col-md-6 col-xs-12' name='getSKS' id='getSKS' type='button' disabled>Download Markers</button>
        </div>

        <!-- metric filter -->
        <div class='form-group col-md-6 col-xs-12'>
          <select class='form-control' id='filter_metric' name='filter_features'>
            <option value='' selected>Show All Metrics</option>
            <option value='ED50'>Show ED50</option>
            <option value='AUC'>Show AUC</option>
          </select>
        </div>

        <!-- Med Ratio slider -->
        <div class='form-group col-md-6 col-xs-12'>
          <label class='select-label' for='medRat_slider'>Filter Median Ratio</label>
          <input id='medRat_slider' name='medRat_slider' type='text'/>
          <span id='medRat_slider_val' class='sliderValue'></span>
        </div>

        <!-- TCGA CoOc slider -->
        <div class='form-group col-md-6 col-xs-12'>
          <label class='select-label' for='TCGA_CoOc_slider'>Filter TCGA Co-Occurrence</label>
          <input id='TCGA_CoOc_slider' name='TCGA_CoOc_slider' type='text'/>
          <span id='TCGA_CoOc_slider_val' class='sliderValue'></span>
        </div>

        <!-- Mut count slider -->
        <div class='form-group col-md-6 col-xs-12'>
          <label class='select-label' for='mut_cnt_slider'>Filter Mutant Count</label>
          <input id='mut_cnt_slider' name='mut_cnt_slider' type='text'/>
          <span id='mut_cnt_slider_val' class='sliderValue'></span>
        </div>

        <!-- P-value slider -->
        <div class='form-group col-md-6 col-xs-12'>
          <label class='select-label' for='pvalue_slider'>Filter P-Value</label>
          <input id='pvalue_slider' name='pvalue_slider' type='text'/>
          <span id='pvalue_slider_val' class='sliderValue'></span>
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
  <div class='placeHolderImg'>Scanning KS Query</div>
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

<?php
  print_footer();
?>
</body>
