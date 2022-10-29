<?php
/*Filename: pops-activity.php
 *Author: Jean Clemenceau
 *Date Created: 5/19/2016
 *Contains the main tool to query cell line activity data.
*/

require_once('pops_header.php');
require_once('pops_dbi.php');

require_login('pops-activity.php');

$activity_query_token = request_form_token('activity_query_form');
$query_results_filter_token = request_form_token('query_results_filter_form');

?>
<head>
  <?php
    add_setup('Query Activity');
    add_styles('pops_bootstrap_query.css');
    add_scripts('result_manipulation.js');
    add_scripts('pops-activity_client.js');
  ?>
</head>
<body>
<?php
  print_navigation('query POPS-Lung','activity');
?>
<div class='container-fluid text-center' id='main_content' >
  <div class='pops-control-panel row'>

    <!-- This panel contains the form for query input -->
    <div class='col-md-6' id='control-panel-query'>
      <div class='control-panel-header '>Submit a New Activity Query</div>

      <form id='activity_query_form' class='form' action='#results-panel' method='post' role='form' enctype='multipart/form-data'>
        <div class='form-group'>
          <label class='control-label col-md-1' for='input_activity'>Input:</label>
          <div class='col-md-11'>
            <input class='form-control' type='text' id='input_activity' name='input_activity' maxlength=254 required/>
          </div>
        </div>

        <div class='col-md-offset-1 col-md-11'>
            <input type='file' id='activity_file' name='activity_file'/>
        </div>

        <div class='form-group col-md-offset-1 col-md-11'>
          <label class="radio-inline">
            <input type="radio" name="search_parameter" id="search_parameter_SWID" value="SWID" checked>SWID
          </label>
          <label class="radio-inline">
            <input type="radio" name="search_parameter" id="search_parameter_name" value="common_name">Compound Name
          </label>
        </div>
        <div class='clearfix'></div>

        <input type='hidden' id='activity_query_token' name='token' value='<?php echo $activity_query_token; ?>' />
        <input type='hidden' name='formName' id='formName' value='activity_query_form'>

        <div class='form-group'>
          <button class='btn btn-default col-md-3 col-md-offset-8 col-xs-12' name='submit_q' id='submit_q'>Search</button>
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
            <button class='btn btn-default col-md-6 col-xs-12' name='getED50' id='getED50' type='button' disabled>Download ED50</button>
          </div>

          <div class='form-group'>
            <button class='btn btn-default col-md-6 col-xs-12' name='getAUC' id='getAUC' type='button' disabled>Download AUC</button>
          </div>

          <div class='form-group col-md-6 col-xs-12'>
            <label class="checkbox-inline">
              <input type="checkbox" name="showED50" id="showED50" value="" checked disabled>Show ED50
            </label>
          </div>

          <div class='form-group col-md-6 col-xs-12'>
            <label class="checkbox-inline">
              <input type="checkbox" name="showAUC" id="showAUC" value="" disabled>Show AUC
            </label>
          </div>

          <div class='form-group'>
              <select class='form-control' id='sortby_metric' name='sortby_metric'>
                <option value='ED50' selected>Sort cell lines by ED50</option>
                <option value='AUC'>Sort cell lines by AUC</option>
                <option value='NONE'>Do NOT sort cell lines</option>
              </select>
          </div>

          <div class='form-group'>
              <select class='form-control' id='highlight_cellline' name='highlight_cellline'>
                <option value='' id='highlight_cellline_blank'>Highlight Cell Line</option>
              </select>
          </div>

          <div class='form-group' style='float:left;margin-left:10%;'>
            <label class="checkbox-inline">
              <input type="checkbox" name="showInfo" id="showInfo" value="" disabled>Show Additional Data
            </label>
          </div>

          <input type='hidden' name='formName' id='formName' value='query_results_filter_form'>
          <input type='hidden' id='query_results_filter_token' name='token' value='<?php echo $query_results_filter_token; ?>' />
          <input type='hidden' name='prevQuery' id='prevQuery' value=''>
          <input type='hidden' name='pageMultiplier' id='pageMultiplier' value='0'>
        </div>

        <!-- This section of the panel contains row filters  by compounds -->
        <div id='result_filters_2' class='row'>
          <div class='form-group col-md-5 col-xs-12'>
            <label class='select-label' for='show_list'>Displayed Compounds
              <input type='checkbox' id='select_all_shown' title='Select all displayed compounds'/>
            </label>
            <select multiple class='form-control' id='show_list' name='show_list' size=7>
            </select>
          </div>

          <div class='show_list_spacer col-md-2 col-xs-12'>
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
      </form>
      <button id='filter_mode_toggle'>Filter Compounds</button>
    </div>
  </div>


  <div class='container-fluid text-center pops-results-panel' id='results-panel'>
    <div class='placeHolderImg'>Activity Query</div>
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
  <div id='compound_details' class='info_box' style='display:none'></div>
</div>
<?php
  print_footer();
?>
</body>
