<?php
/*Filename: pops-mutation.php
 *Author: Jean Clemenceau
 *Date Created: 10/3/2016
 *Contains the main tool to query Mutation data.
*/

require_once('pops_header.php');
require_once('pops_dbi.php');

require_login('pops-mutation.php');
$mutation_query_token = request_form_token('mutation_query_form');

// Load genes
$gene_query = "SELECT DISTINCT(marker) FROM features_mut";
$gene_res= query_db($gene_query,'',NULL,'Gene name Query');
if($gene_res->num_rows >0 ){
  $all_genes = $gene_res->fetch_all();
}else{
  error_log('Mutation Query Error: gene query produced no results.');
}

// Load cell lines
$cellLine_query = "DESCRIBE features_mut";
$cellLine_res= query_db($cellLine_query,NULL,NULL,'Cell Line name Query');
if($cellLine_res->num_rows >0 ){
  $all_cellLines = $cellLine_res->fetch_all();
  array_shift($all_cellLines);
}else{
  error_log('Mutation Query Error: Cell line query produced no results.');
}
?>
<head>
  <?php
    add_setup('Query Mutation Features');
    add_styles('pops_bootstrap_query.css');
    add_scripts('bootstrap-3.3.6-dist/js/bootstrap-slider.min.js');
    add_scripts('result_manipulation.js');
    add_scripts('pops-mutation_client.js');
    add_styles('pops-mutation_card_style.css');
  ?>
  <link type='text/css' href='javascript/bootstrap-3.3.6-dist/css/bootstrap-slider.min.css' rel='stylesheet'>
  <?php     add_styles('pops_bootstrap_slider_custom.css');?>
  <style>
    .slider-selection{
      background: rgb(61, 112, 163);
    }
    .slider-track-low{
      background: transparent;
    }
    .slider-track-high{
      background: transparent;
    }
  </style>
</head>
<body>
<?php
  print_navigation('query POPS-Lung','mutation');
?>
<div class='container-fluid text-center' id='main_content' >
  <div class='pops-control-panel row'>

    <!-- This panel contains the form for query input -->
    <div class='col-md-6' id='control-panel-query'>
      <div class='control-panel-header '>Submit a New Mutation Query</div>

      <form id='mutation_query_form' class='form' action='#results-panel' method='post' role='form' enctype='multipart/form-data'>
        <div class='form-group'>
          <label class='control-label col-md-1' for='input_mutation'>Input:</label>
          <div class='col-md-11'>
            <input class='form-control' type='text' id='input_mutation' name='input_mutation' maxlength=254 list='geneList' required/>
            <datalist id='geneList'>
              <?php
              foreach ($all_genes as $value) {
                echo "<option value='{$value[0]}'/>";
              }
              ?>
            </datalist>
          </div>
        </div>

        <div class='col-md-offset-1 col-md-5'>
            <input type='file' id='mutation_file' name='mutation_file'/>
        </div>

        <div class='form-group col-md-offset-1 col-md-6'>
          <label class="radio-inline">
            <input type="radio" name="search_parameter" id="search_parameter_exact" value="exact" checked>Exact
          </label>
          <label class="radio-inline">
            <input type="radio" name="search_parameter" id="search_parameter_containing" value="contains" >Similar
          </label>
        </div>
        <div class='clearfix'></div>

        <div class='form-group col-md-6 col-xs-12'>
          <label class='select-label' for='cell_line_list'>Cell Line
            <input type='checkbox' id='select_all_cell_lines' title='Select all cell_lines'/>
          </label>
          <select multiple class='form-control' id='cell_line_list' name='cell_line_list[]' size=4>
            <?php
            foreach ($all_cellLines as $name) {
              echo "<option value='{$name[0]}'>{$name[0]}</option>";
            }
            ?>
          </select>
        </div>

        <input type='hidden' id='mutation_query_token' name='token' value='<?php echo $mutation_query_token; ?>' />
        <input type='hidden' name='formName' id='formName' value='mutation_query_form'>

        <div class='form-group'>
          <button class='btn btn-default col-md-6 col-xs-12' name='submit_q' id='submit_q'>Search</button>
        </div>
        <div class='form-group col-xs-12 hidden-lg hidden-md'><hr></div>
      </form>

    </div>
    <!-- This panel contains the form for result filters -->
    <div class='col-md-6' id='control-panel-filters'>
      <div class='control-panel-header hidden-sm hidden-xs'>Filter Your Results</div>

      <form id='query_results_filter_form' class='form' action='#' method='post' role='form' enctype='multipart/form-data'>

          <!--Download mutation status-->
        <div id='result_filters_1'>
          <div class='form-group'>
            <button class='btn btn-default col-md-6 col-xs-12' name='getMutation' id='getMutation' type='button' disabled>Download Mutation Data</button>
          </div>

          <!-- metric variant types -->
          <div class='form-group col-md-6 col-xs-12'>
            <select class='form-control' id='filter_variant' name='filter_variant'>
              <option value='' selected>Show All Variant Types</option>
            </select>
          </div>

          <!-- type datasets (matched/unmatched) -->
          <div class='form-group col-md-6 col-xs-12'>
              <select class='form-control' id='filter_dataset' name='filter_dataset'>
                <option value='' selected>Filter Data Sets</option>
              </select>
          </div>

          <!-- highlight cell lines -->
          <div class='form-group col-md-6 col-xs-12'>
              <select class='form-control' id='highlight_cellline' name='highlight_cellline'>
                <option value=''>Highlight Cell Line</option>
              </select>
          </div>

          <!-- weight slider -->
          <div class='form-group col-md-6 col-xs-12'>
            <label class='select-label' for='allele_freq_slider'>Filter Allele Frequency</label>
            <span id='allele_freq_slider_min_val' class='sliderValue'></span>
            <input id='allele_freq_slider' name='allele_freq_slider' type='text' class='sliderContainer'/>
            <span id='allele_freq_slider_max_val' class='sliderValue'></span>
          </div>

          <div class='form-group col-md-6 col-xs-12'>
          <input type='hidden' name='prevQuery' id='prevQuery' value=''>
          <button class='filter_mode_toggle btn btn-default' id='filter_mode_toggle' style='width:100%;margin:auto;'>Targeted Filters</button>
          </div>

        </div>

        <!-- This section of the panel contains row filters  by genes -->
        <div id='result_filters_2' >
        <div class='row'>
          <div class='form-group col-md-5 col-xs-12'>
            <label class='select-label' for='show_list'>Displayed Genes
              <input type='checkbox' id='select_all_shown' title='Select all displayed genes'/>
            </label>
            <select multiple class='form-control' id='show_list' name='show_list' size=7>
            </select>
          </div>

          <div class='show_list_spacer col-md-2 col-xs-12'>
            <!-- vertical align buttons -->
            <div class='hidden-xs hidden-sm' style='height:30px;'></div>
            <!-- buttons that hide genes -->
            <button id='hide_option' class='btn hide_option hidden-xs hidden-sm' title='Hide selected genes'><span class="glyphicon glyphicon glyphicon-chevron-right"></span><span class="glyphicon glyphicon glyphicon-chevron-right"></span></button>

            <button id='hide_option' class='btn hide_option hidden-md hidden-lg' title='Hide selected genes'><span class="glyphicon glyphicon glyphicon-chevron-down"></span><br><span class="glyphicon glyphicon glyphicon-chevron-down"></span></button>

            <!-- buttons that show genes -->
            <button id='show_option' class='btn show_option hidden-xs hidden-sm' title='Show selected genes'><span class="glyphicon glyphicon glyphicon-chevron-left"></span><span class="glyphicon glyphicon glyphicon-chevron-left"></span></button>

            <button id='hide_option' class='btn show_option hidden-md hidden-lg' 'title='Show selected genes''><span class="glyphicon glyphicon glyphicon-chevron-up"></span><br><span class="glyphicon glyphicon glyphicon-chevron-up"></span></button>
          </div>

          <div class='form-group col-md-5 col-xs-12'>
            <label class='select-label' for='show_list'>Hidden Genes
              <input type='checkbox' id='select_all_hidden' title='Select all hidden genes'/>
</label>
            <select multiple class='form-control' id='hide_list' name='hide_list' size=7>
            </select>
          </div>
        </div>
        <div class='row'>
          <!-- <div class='form-group col-xs-6'>
            <select class='form-control' id='row_filter_type' name='row_filter_type'>
              <option value='genes'>Filter by Gene</option>
              <option value='markers'>Filter by Marker</option>
            </select>
          </div> -->
          <button class='filter_mode_toggle btn btn-default col-xs-offset-6 col-xs-6' id='custom_output_toggle'>Customize Output</button>
        </div>
      </div>
      </form>
    </div>
  </div>


  <div class='container-fluid text-center pops-results-panel' id='results-panel'>
    <div class='placeHolderImg'>Mutation Query</div>
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
  <div id='mutation_details' class='info_box' style='display:none'></div>

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
