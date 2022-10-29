<?php
/*Filename: pops_tutorial.php
 *Author: Jean Clemenceau
 *Date Created: 5/19/2016
 *Contains the page with Instructions on how to use the POPS tools.
*/

require_once('pops_header.php');

check_login();
?>
<head>
  <?php
  add_setup('Tutorial');
  add_styles('pops_bootstrap_infopage.css');
  add_scripts('pops-tutorial_client.js');
  ?>
</head>
<body>
<?php
  print_navigation('tutorial');
?>
<div class='container-fluid text-center' id='main_content' >

<div class=''>
  <ul class='nav nav-tabs subnav'>
    <li class='active'><a data-toggle='tab' href='#register'>Register</a></li>
    <li><a data-toggle='tab' href='#activityQ'>Activity Query</a></li>
    <li><a data-toggle='tab' href='#elasticNQ'>Elastic Net Query</a></li>
    <li><a data-toggle='tab' href='#scanningQ'>Scanning KS Query</a></li>
    <li><a data-toggle='tab' href='#mutationQ'>Mutation Query</a></li>
    <li><a data-toggle='tab' href='#userSetng'>User Settings</a></li>
  </ul>
</div>

<div class='tab-content'>

  <!--Registration Tutorial-->
  <div id='register'  class='tut_block tab-pane fade in active'>
    <ol class='tut_block_list'>
      <li>In the navigation menu, click on <span class='navbar_sample'><span class='glyphicon glyphicon-log-in' style='padding-right:5px;'></span>Log In</span> to go to the log-in page.</li>
      <div class='tut_block_img_wrapper'>
        <img id='imgRegisterLogin' class='tut_block_img img-responsive' alt='POPS Lung Log In Page' src='images/tutorial/register_login.png'  default_src='images/tutorial/register_login.png'/>
      </div>
      <li class='tut_block_img_selector' img_node='imgRegisterLogin' new_src='images/tutorial/register_login_register.png'>Click <button class='btn btn-default' type='button'>Register</button> in the log-in page.</li>
      <div class='tut_block_img_wrapper'>
        <img id='imgRegister' class='tut_block_img img-responsive' alt='POPS Lung Registration' src='images/tutorial/register.png'  default_src='images/tutorial/register.png'/>
      </div>
      <li class='tut_block_img_selector' img_node='imgRegister' new_src='images/tutorial/register_fields.png'>Fill out the required <span class='accent'>fields</span> on the form.</li>
      <li class='tut_block_img_selector' img_node='imgRegister' new_src='images/tutorial/register_register.png'>Click <button class='btn btn-default' type='button'>Register</button> in the registration form.</li>
      <li>Check your registered email account for an email from <span class='accent'>POPS-Lung Admin</span>.
        <ul>
          <li>The message may be found in your junk folder.</li>
        </ul>
      <li>Open the <span class='accent'>Confirmation Link</span>.</li>
      <div class='tut_block_img_wrapper'>
        <img id='imgRegisterActivate' class='tut_block_img img-responsive' alt='POPS Lung Registration Activation' src='images/tutorial/register_login_finish.png'  default_src='images/tutorial/register_login_finish.png'/>
      </div>
      <li class='tut_block_img_selector' img_node='imgRegisterActivate' new_src='images/tutorial/register_login_finish_login.png'><span class='accent'>Log in</span> using your registered email address and chosen password.</li>
      <li>To log out, click on the <span class='navbar_sample'><span class='glyphicon glyphicon-log-out' style='padding-right:5px;'></span>Log Out</span> link in the navigation menu.</li>
    </ol>
  </div>

  <!--Activity Query Tutorial-->
  <div id='activityQ' class='tut_block tab-pane fade'>
    <p style='font-size: small; color: grey;'>*Place cursor over text for more information.</p>
    <p class='block_description'><b>Description:</b>
    Query all screening data, including dose response curves, associated AUC and ED50 values. All resulting analyses were run using both ED50 and AUC as sensitivity metrics.
    </p>
    <ol class='tut_block_list'>
    <img id='imgActivitySearch' class='tut_block_img img-responsive' alt='Activity Query Search Field' src='images/tutorial/activity_search.png'  default_src='images/tutorial/activity_search.png'/>
      <li class='tut_block_img_selector' img_node='imgActivitySearch' new_src='images/tutorial/activity_search_text.png'>Enter <span class='accent'>search</span> input using the text box or upload a file. Separate unique items by comma, space, or new line.
        <ul>
          <li class='tut_block_img_selector' img_node='imgActivitySearch' new_src='images/tutorial/activity_search_file.png'>The uploaded <span class='accent'>file</span> must comply with the following:</li>
            <ul>
              <li class='tut_block_img_selector' img_node='imgActivitySearch' new_src='images/tutorial/activity_search_file.png'>It must be a text file (.txt)</li>
              <li class='tut_block_img_selector' img_node='imgActivitySearch' new_src='images/tutorial/activity_search_file.png'>It must be smaller than 2kB</li>
            </ul>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgActivitySearch' new_src='images/tutorial/activity_search_type.png'>Select input <span class='accent'>type</span> from the following:
        <ul>
          <li class='tut_block_img_selector' img_node='imgActivitySearch' new_src='images/tutorial/activity_search_type.png'><span class='accent2'>SWID</span>: A chemical's unique UT Southwestern ID.</li>
          <li class='tut_block_img_selector' img_node='imgActivitySearch' new_src='images/tutorial/activity_search_type.png'><span class='accent2'>Common Name</span>: A chemical's common name. (Limited availability)</li>
        </ul>
      </li>
    <img id='imgActivityResult' class='tut_block_img img-responsive' alt='Activity Result Table' src='images/tutorial/activity_result.png'  default_src='images/tutorial/activity_result.png'/>
      <li>The <span class='accent'>Results Table</span> will be displayed below the search panel. Each row will contain:
        <ul>
          <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/activity_result_SWID.png'><span class='accent2'>SWID</span>: The chemical's unique ID. Click to see its structure.</li>
          <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/activity_result_sens_med.png'><span class='accent2'>Sens. Med.</span>: Median value of the sensitive chemicals</li>
          <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/activity_result_resist_med.png'><span class='accent2'>Resist. Med.</span>: Median value of the resitant chemicals</li>
          <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/activity_result_med_ratio.png'><span class='accent2'>Median Ratio</span>: Ratio of the median sensitivity and median resistance values</li>
          <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/activity_result_metric.png'><span class='accent2'>Metric</span>: Sensitivity metric (ED50 or AUC)
            <ul>
              <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/activity_result_metric.png'>ED50: Median Effective Dose (ED<sub>50</sub>).</li>
              <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/activity_result_metric.png'>AUC: Area Under the Curve.</li>
            </ul>
          </li>
          <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/activity_result_mean_val.png'><span class='accent2'>Median Value</span>: The heatmap representing calculated AUC/ED50 values.
            <ul>
              <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/activity_result_infobox_highlighted.png'>Hover over a cell to see the specific value and cell line in the information box in the lower left.</li>
              <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_result_curve.png' title='See Below'>Click on a cell to open its cell line's dose response curve.
            </ul>
          </li>
        </ul>
      </li>
      </li>
    <img id='imgActivityCustom' class='tut_block_img img-responsive' alt='Activity Result Customization' src='images/tutorial/activity_custom.png'  default_src='images/tutorial/activity_custom.png'/>
      <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_custom_download.png'>Download your results in <span class='accent'>CSV</span> format by using the buttons in the <span class='control-panel-header'>Filter Your Results</span> panel.</li>
      <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_custom_metrics.png'>Toggle the <span class='accent'>visibility</span> of the metrics by using the appropriate checkboxes.</li>
      <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_custom_sort.png'><span class='accent'>Sort</span> the heatmap according to your metric of choice by selecting an option from the drop-down menu.</li>
      <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_custom_highlight.png'><span class='accent'>Highlight</span> a specific cell line throughout the heatmap by selecting it from the drop-down menu.</li>
      <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_custom_addit_data.png'>Check <span class='panel_sample'><label class='checkbox-inline'><input type='checkbox' id='showInfo' checked>Show Additional Data</label></span> to <span class='accent'>display</span> relevant links for each chemical.</li>
        <ul>
          <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_custom_addit_data.png'><span class='accent2'>PubChem</span>: Open a chemical structure search in PubChem.</li>
        </ul>
      <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_custom_filter_btn.png'>Click <button>Filter Compounds</button> to filter individual chemicals from the list.
        <ul>
          <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_filter_>>.png'>Select items from the <span class='select-label tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_filter_disp.png'>Displayed Compounds</span> list and click the chevrons <span class='panel_sample'><span class="glyphicon glyphicon glyphicon-chevron-right"></span><span class="glyphicon glyphicon glyphicon-chevron-right"></span></span> to hide them.</li>
          <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_filter_<<.png'>Select items from the <span class='select-label tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_filter_hdn.png'>Hidden Compounds</span> list and click the other chevrons <span class='panel_sample'><span class="glyphicon glyphicon glyphicon-chevron-left"></span><span class="glyphicon glyphicon glyphicon-chevron-left"></span></span> to show them.</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/activity_filter_customize_btn.png'>Click <button>Customize Output</button> to return to the previous panel.</li>
    </ol>
  </div>

  <!--Elastic Net Query Tutorial-->
  <div id='elasticNQ' class='tut_block tab-pane fade'>
    <p style='font-size: small; color: grey;'>*Place cursor over text for more information.</p>
    <p class='block_description'><b>Description:</b>
    Query quantitative predictive features assigned from measures of (1) Illumina Bead Array Expression Data (2) RNAseq (3) whole exome sequencing mutation calls (4) Illumina SNP array-based copy number quantification (5) reverse-phase protein arrays (RPPA) (5) carbon-tracing metabolomics flux analyses. Chemical/genetic relationships for 171 chemicals passed significance thresholds and are available.
    </p>
    <ol class='tut_block_list'>
      <img id='imgElasticNetSearch' class='tut_block_img img-responsive' alt='Elastic Net Query Search Field' src='images/tutorial/enet_search.png'  default_src='images/tutorial/enet_search.png'/>
      <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_text.png'>Enter <span class='accent'>search</span> input using the text box or upload a file. Separate unique items by comma, space, or new line.
        <ul>
          <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_file.png'>The uploaded <span class='accent'>file</span> must comply with the following:</li>
            <ul>
              <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_file.png'>It must be a text file (.txt)</li>
              <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_file.png'>It must be smaller than 2kB</li>
            </ul>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_type.png'>Select corresponding search <span class='accent'>type</span> from the following:
        <ul>
          <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_type.png'><span class='accent2'>Marker</span>: A predicted feature (eg. gene name).</li>
          <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_type.png'><span class='accent2'>SWID</span>: A chemical's unique UT Southwestern ID.</li>
          <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_type.png'><span class='accent2'>Common Name</span>: A chemical's common name. (Limited availability)</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'>Select one or more <span class='accent'>feature sets</span> (optional).
        <ul>
          <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'>If no feature set is selected, the query will include all feature sets available</li>
          <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'>Activate the checkmark next to the title to select/unselect all feature sets.</li>
          <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'>The following feature sets are available in the Elastic Net analysis:
            <ul>
              <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'><span class='accent2'>Copy Number</span>: Copy Number Variation of a gene from arrayCGH arrays.</li>
              <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'><span class='accent2'>Expression</span>: Illumina V3 beadchip microarray expression values.</li>
              <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'><span class='accent2'>Metabolomics</span>: Cells were pre-incubated with media containing either heavy labeled (<sup>13</sup>C) glucose or glutamine for either 6 or 24 hours. Mass spectrometry analysis was used to look at incorporation of heavy label into the metabolites fumarate, citrate, malate, lactate. All metabolites traces are in the form XXXYZZmN:
                <ul>
                  <li>XXX = Metabolite (cit: citrate; mal: malate; lac: lactate; fum: fumarate)</li>
                  <li>Y = Carbon source (Q: glutamine; G: glucose)</li>
                  <li>ZZ = Time of incubation (24: 24 hours; 6: 6 hours) </li>
                  <li>N = Number of labeled carbons (0: no labeled carbons, 1: 1 labeled carbon, etc)</li>
                </ul>
              </li>
              <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'><span class='accent2'>Mutation</span>: Mutations defined by whole exome sequencing. Cell lines that did not have a matched normal pair were further filtered to identify most likely somatic variants. Values are binarized (1=mutant ; 0=wild-type).</li>
              <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'><span class='accent2'>RNA Seq</span>: Gene expression by RNA sequence analysis.</li>
              <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'><span class='accent2'>Rppa</span>: Protein expression by Reverse Phase Protein Array</li>
              <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'><span class='accent2'>SNP CNV</span>: Copy Number Variation defined by illumina SNP arrays.</li>
              <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_features.png'><span class='accent2'>SNP MUT</span>: Binarized value defining a gene's mutation, amplification, or deletion status. (1: mutant and/or amplified/deleted; 0: wild-type).</li>
            </ul>
          </li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgElasticNetSearch' new_src='images/tutorial/enet_search_metric.png'>Select a <span class='accent'>metric</span> of your interest (optional).
        <ul>
          <li class='tut_block_img_selector' img_node='imgElasticNetResult' new_src='images/tutorial/enet_search_metric.png'>ED50: Median Effective Dose (ED<sub>50</sub>).</li>
          <li class='tut_block_img_selector' img_node='imgElasticNetResult' new_src='images/tutorial/enet_search_metric.png'>AUC: Area Under the Curve.</li>
        </ul>
      </li>
      <img id='imgElasticNetResult' class='tut_block_img img-responsive' alt='Elastic Net Result Table' src='images/tutorial/enet_result.png'  default_src='images/tutorial/enet_result.png'/>
      <li>The <span class='accent'>Results Table</span> will be displayed below the search panel.
        <ul>
          <li>200 results will be listed at a time.</li>
          <li class='tut_block_img_selector' img_node='imgElasticNetResult' new_src='images/tutorial/enet_result_<<>>.png'>Request the previous or next set of 200 results using the <span class='accent'>navigation arrows</span> flanking the table's title.</li>
        </ul>
      </li>
      <li>Each entry will contain:
        <ul>
          <li class='tut_block_img_selector' img_node='imgElasticNetResult' new_src='images/tutorial/enet_result_SWID.png'><span class='accent2'>SWID</span>: The chemical's unique ID. Click to see its structure.</li>
          <li class='tut_block_img_selector' img_node='imgElasticNetResult' new_src='images/tutorial/enet_result_marker.png'><span class='accent2'>Marker</span>: The predictive feature from corresponding dataset.
	  <ul><li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_result_heatmap.png' title='See Below'>
		Click to see the heatmap.
	  </li></ul></li>
          <li class='tut_block_img_selector' img_node='imgElasticNetResult' new_src='images/tutorial/enet_result_weight.png'><span class='accent2'>Weight</span>: Elastic net derived weight.</li>
          <li class='tut_block_img_selector' img_node='imgElasticNetResult' new_src='images/tutorial/enet_result_frequency.png'><span class='accent2'>Frequency</span>: Bootstrapping frequency of occurrence (out of 100 permutations).</li>
          <li class='tut_block_img_selector' img_node='imgElasticNetResult' new_src='images/tutorial/enet_result_type.png'><span class='accent2'>Type</span>: Feature Set (as described above).</li>
          <li class='tut_block_img_selector' img_node='imgElasticNetResult' new_src='images/tutorial/enet_result_rocpval.png'><span class='accent2'>ROC pval</span>: A P-value assessing predictive capacity of assigned features from a particular feature set.
            <ul>
              <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_result_rocpval_card.png' title='See Below'>Click to reveal the ROC curve (left) and the AUC/ED50 for each cell line plotted as a function of the prediction value (right). Vertical red lines define manually annotated cutoffs for sensitivity and resistance to each chemical. A horizontal red line is indicated at y=0 to indicate the cutoff for elastic net predictions. y&gt;0 indicates predicted resistance and y&lt;0 indicates predicted sensitivity. Values in the top right quadrant are true resistant cell lines (<span style='color:darkorange;'>orange</span>) and values in the bottom left quadrant are true sensitive cell lines (<span style='color:blue;'>blue</span>).</li>
            </ul>
          </li>
          <li class='tut_block_img_selector' img_node='imgElasticNetResult' new_src='images/tutorial/enet_result_metric.png'><span class='accent2'>Metric</span>: Metric on which the analysis was run (ED<sub>50</sub> or AUC).</li>
          <li class='tut_block_img_selector' img_node='imgElasticNetResult' new_src='images/tutorial/enet_result_scale.png'><span class='accent2'>Scale</span>: Indicates whether the sensitivity vector was log-transformed (Log10) or not (linear) prior to running the Elastic Net.</li>
        </ul>
      </li>
      </li>
      <img id='imgElasticNetCustom' class='tut_block_img img-responsive' alt='Elastic Net Result Customization' src='images/tutorial/enet_custom.png'  default_src='images/tutorial/enet_custom.png'/>
      <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_custom_download.png'>Download your results in <span class='accent'>CSV</span> format by using the buttons in the <span class='control-panel-header'>Filter Your Results</span> panel.</li>
      <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_custom_metrics.png'>Filter different <span class='accent'>metrics</span> from the results by using the drop-down menu.</li>
      <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_custom_features.png'>Filter <span class='accent'>feature sets</span> by using the drop-down menu.</li>
      <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_custom_scale.png'>Filter the result's <span class='accent'>scale</span> by using the droop-down menu.</li>
      <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_custom_weight.png'>Filter <span class='accent'>weights</span> by adjusting the handles on the corresponding slider.
        <ul>
          <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_custom_weight.png'>Results with weight values between the minimum and maximum handles will be filtered out.</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_custom_frequency.png'>Filter <span class='accent'>frequency</span> by adjusting the handle on the corresponding slider.
        <ul>
          <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_custom_frequency.png'>Results with frequency values below the handle will be filtered out.</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_custom_filter_btn.png'>Click <button class='btn btn-default' type='button'>Targeted Filters</button> to filter individual chemicals from the list.</li>
      <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_filter_>>.png'>Select items from the <span class='select-label tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_filter_disp.png'>Displayed Compounds</span> list and click the chevrons <span class='panel_sample'><span class="glyphicon glyphicon glyphicon-chevron-right"></span><span class="glyphicon glyphicon glyphicon-chevron-right"></span></span> to hide them.</li>
      <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_filter_<<.png'>Select items from the <span class='select-label tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_filter_hdn.png'>Hidden Compounds</span> list and click the other chevrons <span class='panel_sample'><span class="glyphicon glyphicon glyphicon-chevron-left"></span><span class="glyphicon glyphicon glyphicon-chevron-left"></span></span> to show them.</li>
      <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_filter_type.png'>Select filter criteria from the drop-down menu.</li>
      <li class='tut_block_img_selector' img_node='imgElasticNetCustom' new_src='images/tutorial/enet_filter_customize_btn.png'>Click <button class='btn btn-default' type='button'>Customize Output</button> to return to the previous panel.</li>
    </ol>
  </div>

  <!--Scanning KS Query  Tutorial-->
  <div id='scanningQ' class='tut_block tab-pane fade'>
    <p style='font-size: small; color: grey;'>*Place cursor over text for more information.</p>
    <p class='block_description'><b>Description:</b>
    A modification to a Kolmogorov-Smirnov (KS) test was made to query single gene mutations or pairwise combinations of co-occurring gene mutations to rank those that can predict the best selective sensitivity to each chemical.
    </p>
    <ol class='tut_block_list'>
    <img id='imgScanningKSSearch' class='tut_block_img img-responsive' alt='Scanning KS Query Search Field' src='images/tutorial/sks_search.png'  default_src='images/tutorial/sks_search.png'/>
      <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_text.png'>Enter <span class='accent'>search</span> input using the text box or upload a file. Separate unique items by comma, space, or new line.
        <ul>
          <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_file.png'>The uploaded <span class='accent'>file</span> must comply with the following:</li>
            <ul>
              <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_file.png'>It must be a text file (.txt)</li>
              <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_file.png'>It must be smaller than 2kB</li>
            </ul>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_type.png'>Select input <span class='accent'>type</span> from the following:
        <ul>
          <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_type.png'><span class='accent2'>SWID</span>: A chemical's unique UT Southwestern ID.</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_type.png'><span class='accent2'>Common Name</span>: A chemical's common name. (Limited availability)</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_gene.png'>Enter a <span class='accent'>gene</span> of interest. (optional).</li>
      <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_pval.png'>Enter a maximum <span class='accent'>P-value</span> filter. (optional).</li>
      <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_medrat.png'>Enter a maximum <span class='accent'>median ratio</span> filter. (optional).</li>
      <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_metric.png'>Select a sensitivity <span class='accent'>metric</span> of interest (optional).
        <ul>
          <li class='tut_block_img_selector' img_node='imgScanningKSResult' new_src='images/tutorial/sks_search_metric.png'>ED50: Median Effective Dose (ED<sub>50</sub>).</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSResult' new_src='images/tutorial/sks_search_metric.png'>AUC: Area Under the Curve.</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_output.png'>Select <span class='accent'>output type</span> from the following:
        <ul>
          <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_output.png'><span class='accent2'>Table</span>: A text table listing all results.</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSSearch' new_src='images/tutorial/sks_search_output.png'><span class='accent2'>Plots</span>: A grid containing the plot images of all results.</li>
        </ul>
      </li>
      <img id='imgScanningKSResult' class='tut_block_img img-responsive' alt='Scanning KS Result Table' src='images/tutorial/sks_result.png'  default_src='images/tutorial/sks_result.png'/>
      <li>The <span class='accent'>Results Table</span> will be displayed below the search panel.
        <ul>
          <li>200 results will be listed at a time.</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSResult' new_src='images/tutorial/sks_result_<<>>.png'>Request the previous or next set of 200 results using the <span class='accent'>navigation arrows</span> flanking the result's title.</li>
        </ul>
      </li>
      <li>Each row will contain:
        <ul>
          <li class='tut_block_img_selector' img_node='imgScanningKSResult' new_src='images/tutorial/sks_result_SWID.png'><span class='accent2'>SWID</span>: The chemical's unique ID. Click to see its structure.</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSResult' new_src='images/tutorial/sks_result_marker.png'><span class='accent2'>Marker</span>: Single gene or pairwise combinations of co-occuring mutations. Click to see the corresponding ECDF plot (red=mutant; blue=wild-type) and activity heatmap.</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSResult' new_src='images/tutorial/sks_result_medrat.png'><span class='accent2'>Med Ratio</span>: log<sub>2</sub> of the median mutant ED50 (or AUC) value divided by the median wild-type ED50 (or AUC) value.</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSResult' new_src='images/tutorial/sks_result_pvalue.png'><span class='accent2'>Pvalue</span>: P-value determined by the Scanning KS test.</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSResult' new_src='images/tutorial/sks_result_metric.png'><span class='accent2'>Metric</span>: Sensitivity metric (ED<sub>50</sub> or AUC).</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSResult' new_src='images/tutorial/sks_result_tcga.png'><span class='accent2'>Frequency of Occurrence in TCGA</span>: Frequency in which mutations co-occurr in TCGA dataset.</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSResult' new_src='images/tutorial/sks_result_mutcnt.png'><span class='accent2'>Mutant Count</span>: Number of mutant cell lines in mutant distribution.</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSResult' new_src='images/tutorial/sks_result_plot.png'><span class='accent2'>Plots</span>: Corresponding ECDF plot. Click to enlarge the plot and show activity heatmap.</li>
        </ul>
      </li>
      </li>
    <img id='imgScanningKSCustom' class='tut_block_img img-responsive' alt='Scanning KS Result Customization' src='images/tutorial/sks_custom.png'  default_src='images/tutorial/sks_custom.png'/>
      <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_download.png'>Download your results in <span class='accent'>CSV</span> format by using the buttons in the <span class='control-panel-header'>Filter Your Results</span> panel.</li>
      <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_metrics.png'>Filter different <span class='accent'>metrics</span> from the results by using the drop-down menu.</li>
      <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_medrat.png'>Filter <span class='accent'>median ratio</span> by adjusting the handles on the corresponding slider.
        <ul>
          <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_medrat.png'>Results with median ratios <span class='accent2'>above</span> the handle will be filtered out.</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_tcga.png'>Filter <span class='accent'>occurrence in TCGA</span>percentage by adjusting the handles on the corresponding slider.
        <ul>
          <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_tcga.png'>Results with percentages <span class='accent2'>below</span> the handle will be filtered out.</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_mutcnt.png'>Filter <span class='accent'>mutant count</span> by adjusting the handle on the corresponding slider.
        <ul>
          <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_mutcnt.png'>Results with mutant counts <span class='accent2'>below</span> the handle will be filtered out.</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_pvalue.png'>Filter <span class='accent'>P-values</span> by adjusting the handle on the corresponding slider.
        <ul>
          <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_pvalue.png'>Results with mutant counts <span class='accent2'>above</span> the handle will be filtered out.</li>
          <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_pvalue.png'>If no results above <span class='accent2'>2x10<sup>-5</sup></span> are available, the slider will be disabled.</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_custom_filter_btn.png'>Click <button class='btn btn-default' type='button'>Targeted Filters</button> to filter individual chemicals from the list.</li>
      <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_filter_>>.png'>Select items from the <span class='select-label tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_filter_disp.png'>Displayed Compounds</span> list and click the chevrons <span class='panel_sample'><span class="glyphicon glyphicon glyphicon-chevron-right"></span><span class="glyphicon glyphicon glyphicon-chevron-right"></span></span> to hide them.</li>
      <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_filter_<<.png'>Select items from the <span class='select-label tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_filter_hdn.png'>Hidden Compounds</span> list and click the other chevrons <span class='panel_sample'><span class="glyphicon glyphicon glyphicon-chevron-left"></span><span class="glyphicon glyphicon glyphicon-chevron-left"></span></span> to show them.</li>
      <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_filter_type.png'>Select filter criteria from the drop-down menu.</li>
      <li class='tut_block_img_selector' img_node='imgScanningKSCustom' new_src='images/tutorial/sks_filter_customize_btn.png'>Click <button class='btn btn-default' type='button'>Customize Output</button> to return to the previous panel.</li>
    </ol>
  </div>

  <!--Mutation Query Tutorial-->
  <div id='mutationQ' class='tut_block tab-pane fade'>
    <p style='font-size: small; color: grey;'>*Place cursor over text for more information.</p>
    <p class='block_description'><b>Description:</b>
    A panel of 124 NSCLC cell lines was subjected to whole exome sequencing (average 60X coverage). 34/124 have corresponding tumor and matched B-cell non-tumorigenic DNA from which definitive somatically acquired lesions can be determined. For the remaining 90 cell lines, a series of filters leveraging the tumor/normal matched dataset and publically available data were used to filter out probable germline alterations and enrich for somatically acquired mutations. This mutation query page displays searchable filtered mutation calls for all 124 cell lines. Lollipop plots also compare positional mutation calls in the UTSW cell line panel to LUAD and LUSC tumors in the TCGA and in an MD Anderson (MDACC) tumor panel.
    </p>
    <ol class='tut_block_list'>
    <img id='imgMutationSearch' class='tut_block_img img-responsive' alt='Mutation Query Search Field' src='images/tutorial/mut_search.png'  default_src='images/tutorial/mut_search.png'/>
      <li class='tut_block_img_selector' img_node='imgMutationSearch' new_src='images/tutorial/mut_search_text.png'>Enter <span class='accent'>search</span> input using the text box or upload a file. Separate unique items by comma, space, or new line.
        <ul>
          <li class='tut_block_img_selector' img_node='imgMutationSearch' new_src='images/tutorial/mut_search_file.png'>The uploaded <span class='accent'>file</span> must comply with the following:</li>
            <ul>
              <li class='tut_block_img_selector' img_node='imgMutationSearch' new_src='images/tutorial/mut_search_file.png'>It must be a text file (.txt)</li>
              <li class='tut_block_img_selector' img_node='imgMutationSearch' new_src='images/tutorial/mut_search_file.png'>It must be smaller than 2kB</li>
            </ul>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgMutationSearch' new_src='images/tutorial/mut_search_type.png'>Select input <span class='accent'>type</span> from the following:
        <ul>
          <li class='tut_block_img_selector' img_node='imgMutationSearch' new_src='images/tutorial/mut_search_type.png'><span class='accent2'>Exact</span>: Search for the exact gene symbol.</li>
          <li class='tut_block_img_selector' img_node='imgMutationSearch' new_src='images/tutorial/mut_search_type.png'><span class='accent2'>Similar</span>: Search for a gene symbol that contains the queried text.</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgMutationSearch' new_src='images/tutorial/mut_search_celllines.png'>Select one or more <span class='accent'>cell lines</span> (optional).
        <ul>
          <li class='tut_block_img_selector' img_node='imgMutationSearch' new_src='images/tutorial/mut_search_celllines.png'>If no cell line is selected, the query will include all cell lines available</li>
          <li class='tut_block_img_selector' img_node='imgMutationSearch' new_src='images/tutorial/mut_search_celllines.png'>Activate the checkmark next to the title to select/unselect all cell lines.</li>
        </ul>
      </li>
      <img id='imgMutationResult' class='tut_block_img img-responsive' alt='Mutation Result Table' src='images/tutorial/mut_result.png'  default_src='images/tutorial/mut_result.png'/>
      <li>The <span class='accent'>Results Table</span> will be displayed below the search panel.
        <ul>
          <li>200 results will be listed at a time.</li>
          <li class='tut_block_img_selector' img_node='imgMutationResult' new_src='images/tutorial/mut_result_<<>>.png'>Request the previous or next set of 200 results using the <span class='accent'>navigation arrows</span> flanking the table's title.</li>
        </ul>
      </li>
      <li>Each row will contain:
        <ul>
          <li class='tut_block_img_selector' img_node='imgMutationResult' new_src='images/tutorial/mut_result_gene.png'><span class='accent2'>Gene</span>: Gene's name.
            <ul>
		<li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_result_pegplot.png' title='See Below'>
		Click to see lolliplot plots comparing mutation positional information in the UTSW cell line panel to tumors in the TCGA and an MD Anderson Cancer Center (MDACC) dataset.
		</li>
		<li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_result_pegplot.png' title='See Below'>
		The top panel indicates PFAM annotated domain for a given gene. The remaining second through fourth panels indicate mutation frequency (y-axis) as a function of amino acid position along the protein (x-axis) in the UTSW cell line panel, TCGA, and MDACC tumor panels, respectively. A ‘M’ or a ‘U’ in the UTSW panel indicates the corresponding cell line was derived from the tumor/matched (34 cell lines) or tumor cell line dataset (90 cell lines). Pegs are colored according to mutation type (red = non-sense; black=missense) and circles are colored according to tumor of origin.
	    </li></ul>
	  </li>
          <li class='tut_block_img_selector' img_node='imgMutationResult' new_src='images/tutorial/mut_result_mutstat.png'><span class='accent2'>Mutation Status</span>: Gene mutation status for all cell lines requested.
            <ul>
              <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/mut_result_cell.png'><span class='accent'>Black</span> cells are mutated cell lines for the given gene.</li>
              <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/mut_result_cell_white.png'><span class='accent'>White</span> cells are wild type cell lines for the given gene.</li>
              <li class='tut_block_img_selector' img_node='imgActivityResult' new_src='images/tutorial/mut_result_cell_infobox.png'>Hover over a cell to see the mutation summary (number of mutations in gene, location).</li>
              <li class='tut_block_img_selector' img_node='imgActivityCustom' new_src='images/tutorial/mut_result_curve.png' title='See Below'>Click on a cell to open the mutation details.</li>
            </ul>
          </li>
        </ul>
      </li>
      </li>
    <img id='imgMutationCustom' class='tut_block_img img-responsive' alt='Mutation Result Customization' src='images/tutorial/mut_custom.png'  default_src='images/tutorial/mut_custom.png'/>
      <li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_custom_download.png'>Download your results' mutation status in <span class='accent'>CSV</span> format by using the buttons in the <span class='control-panel-header'>Filter Your Results</span> panel.</li>
      <li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_custom_vartype.png'>Highlight the <span class='accent'>variant type</span> of interest by using the drop-down menu.</li>
      <li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_custom_dataset.png'>Highlight the <span class='accent'>data set</span> of interest by using the drop-down menu.</li>
      <li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_custom_cline.png'>Highlight the <span class='accent'>cell line</span> of interest by using the droop-down menu.</li>
      <li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_custom_allele.png'>Adjust <span class='accent'>allele frequencies</span> by adjusting the handles on the corresponding slider.
        <ul>
          <li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_custom_allele.png'>Results with allele frequency values outside the minimum and maximum handles will be grayed out.</li>
        </ul>
      </li>
      <li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_custom_filter_btn.png'>Click <button class='btn btn-default' type='button'>Targeted Filters</button> to filter individual genes from the list.</li>
      <li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_filter_>>.png'>Select items from the <span class='select-label tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_filter_disp.png'>Displayed Genes</span> list and click the chevrons <span class='panel_sample'><span class="glyphicon glyphicon glyphicon-chevron-right"></span><span class="glyphicon glyphicon glyphicon-chevron-right"></span></span> to hide them.</li>
      <li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_filter_<<.png'>Select items from the <span class='select-label tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_filter_hdn.png'>Hidden Genes</span> list and click the other chevrons <span class='panel_sample'><span class="glyphicon glyphicon glyphicon-chevron-left"></span><span class="glyphicon glyphicon glyphicon-chevron-left"></span></span> to show them.</li>
      <li class='tut_block_img_selector' img_node='imgMutationCustom' new_src='images/tutorial/mut_filter_customize_btn.png'>Click <button class='btn btn-default' type='button'>Customize Output</button> to return to the previous panel.</li>
    </ol>
  </div>

  <!--User Settings Tutorial-->
  <div id='userSetng' class='tut_block tab-pane fade'>
    <ol class='tut_block_list'>
      <li class='tut_block_img_selector' img_node='imgUserSettings' new_src='images/tutorial/register_login_finish_login.png'><span class='accent'>Log In</span> to your POPS account.</li>
      <li>In the navigation menu, click on <span class='navbar_sample'><span class='glyphicon glyphicon-user' style='padding-right:5px;'></span>Your Name</span> to access the User Settings page.</li>
      <div class='tut_block_img_wrapper'>
        <img id='imgUserSettings' class='tut_block_img img-responsive' alt='POPS User Settings Page' src='images/tutorial/usetting_page.png'  default_src='images/tutorial/usetting_page.png'/>
      </div>
      <li>The fields will be automatically populated with your information.</li>
      <li class='tut_block_img_selector' img_node='imgUserSettings' new_src='images/tutorial/usetting_fields.png'>Replace the appropriate <span class='accent'>personal information</span> field with your updated data.</li>
      <li class='tut_block_img_selector' img_node='imgUserSettings' new_src='images/tutorial/usetting_newpasswd.png'>To change your password, enter your <span class='accent'>new password</span> and confirm it.</li>
      <li class='tut_block_img_selector' img_node='imgUserSettings' new_src='images/tutorial/usetting_curpasswd.png'>Enter your <span class='accent'>current password</span> to confirm your changes.</li>
      <li class='tut_block_img_selector' img_node='imgUserSettings' new_src='images/tutorial/usetting_update.png'>Click <button class='btn btn-default' type='button'>Update Profile</button> in the form to save changes.</li>
      <li>Check your registered email account for an email confirmation from <span class='accent'>POPS-Lung Admin</span>.
        <ul>
          <li>The message may be found in your junk folder.</li>
        </ul>
    </ol>
  </div>
</div>

</div>

<?php
  print_footer();
?>
</body>
