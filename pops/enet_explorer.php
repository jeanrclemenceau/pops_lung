<?php
/*Filename: enet_explorer.php
 *Author: Jean Clemenceau
 *Date Created: 7/05/2016
 *Contains the main tool to explore Elastic Net data curves and heatmaps.
*/

require_once('pops_header.php');
require_once('pops_dbi.php');

// Verify that user is logged in
require_login('pops-enet.php');

// Validate the form token
$valid_token = true;
if( isset($_POST['token']) && isset($_POST['formName']) && $_POST['formName']=='enet_explorer_form' ){
  $valid_token = validate_form_token($_POST['formName'],$_POST['token']);
}
if(!$valid_token){
  $returnValues['resultsTable'] = "<div class='failAlert'><p>This form submission has expired or comes from an unrecognized referer.</p><p>Refresh the page to try again</p><div>";
  $returnValues['error'] = TRUE;
  echo json_encode($returnValues);
  exit;
}
$enet_explorer_token = request_form_token('enet_explorer_form');

//Get all data from form
$q_parameters=Array();
$q_parameters['SWID'] = isset($_REQUEST['id'])? sanitize($_REQUEST['id']) : NULL;
$q_parameters['type'] = isset($_REQUEST['tp'])? sanitize($_REQUEST['tp']) : NULL;
$q_parameters['metric']= isset($_REQUEST['mt'])? sanitize($_REQUEST['mt']) : 'ED50';
$q_parameters['scale']= isset($_REQUEST['sc'])? sanitize($_REQUEST['sc']) : NULL;

//Perform query if requested
$data_table = 'elastic_net d';
$q_output= NULL;
if( isset($q_parameters['SWID']) && isset($q_parameters['type'])){
  $q_output = explore_enet($q_parameters, $enetFeatureSetMap);
}else{
  //Get feature types if no query set
  $type_q = "SELECT DISTINCT(type) FROM $data_table;";
  $type_r = query_db($type_q, NULL,NULL,'All types query');
  $type_list = Array();
  while($row = $type_r->fetch_row()){
    $type_list[$row[0]] = $enetFeatureSetMap[$row[0]];
  }
  $q_output['type_list']=$type_list;
  $q_output['type_name']='';
  $q_output['swid']='';
}


?>
<head>
  <?php
    add_setup('Elastic Net Explorer');
    add_styles('pops_bootstrap_query.css');
    add_scripts('result_manipulation.js');
    // add_scripts('enet_explorer_client.js');
  ?>
  <style>
  .explorerTitle{
  font-size: xx-large;
  text-shadow: 1px 1px 1px #888888;
  }
  .plotTitle{
    font-size: x-large;
    text-decoration: underline;
    margin-bottom: 5px;
  }
  .glyphicon{
    font-size: small;
    margin-left: 5px;
  }
  .plotButton{
    margin-bottom: 5px;
  }
  </style>
  <script>
  $(function(){
    $('#ROCtitle').on('click',function(){
      $('.ROCplot').toggle();
      if($('.ROCplot').is(':visible')){
        $('.glyphicon.ROC').removeClass('glyphicon-triangle-left');
        $('.glyphicon.ROC').addClass('glyphicon-triangle-bottom');
      }else{
        $('.glyphicon.ROC').removeClass('glyphicon-triangle-bottom');
        $('.glyphicon.ROC').addClass('glyphicon-triangle-left');
      }
    });
    $('#freqmaptitle').on('click',function(){
      $('.freqmapPlot').toggle();
      if($('.freqmapPlot').is(':visible')){
        $('.glyphicon.freqmap').removeClass('glyphicon-triangle-left');
        $('.glyphicon.freqmap').addClass('glyphicon-triangle-bottom');
      }else{
        $('.glyphicon.freqmap').removeClass('glyphicon-triangle-bottom');
        $('.glyphicon.freqmap').addClass('glyphicon-triangle-left');
      }
    });
    $('#heatmaptitle').on('click',function(){
      $('.heatmapPlot').toggle();
      if($('.heatmapPlot').is(':visible')){
        $('.glyphicon.hmap').removeClass('glyphicon-triangle-left');
        $('.glyphicon.hmap').addClass('glyphicon-triangle-bottom');
      }else{
        $('.glyphicon.hmap').removeClass('glyphicon-triangle-bottom');
        $('.glyphicon.hmap').addClass('glyphicon-triangle-left');
      }
    });
  });
  </script>
</head>
<body>
<?php
  print_navigation('query POPS-Lung','Elastic Net');
?>
<!-- This panel contains the form for query input -->
<div class='container-fluid text-center' id='main_content'>
  <div class='pops-control-panel row'>
    <div class='explorerTitle'><?php
      if(isset($q_output['plots'])){
        print("{$q_parameters['SWID']}: {$q_output['type_name']}-{$q_parameters['metric']}");
      }
    ?></div><hr>
    <form id='enet_explorer_form' class='form' action='enet_explorer.php' method='post' role='form' enctype='multipart/form-data'>

      <div class='form-group'>
        <label class='control-label col-md-1' for='id'>Input:</label>
        <div class='col-md-2'>
          <input class='form-control' type='text' id='id' name='id' maxlength=15 value='<?php echo $q_output['swid'];?>' required/>
        </div>
      </div>

      <label class='control-label col-md-1' for='tp'>Feature&nbsp;Set:</label>
      <div class='form-group col-md-2'>
        <select class='form-control col-md-2' id='tp' name='tp'>
          <?php
          foreach ($q_output['type_list'] as $abrv => $name) {
            $formatName = ucwords( str_replace('_',' ',$name) );
            $selected = ($formatName == $q_output['type_name'])?'selected':'';
            echo "<option value='$abrv' $selected>$formatName</option>";
          }
          ?>
        </select>
      </div>

      <div class='form-group col-md-2 col-xs-12'>
        <label class="radio-inline">
          <input type="radio" name="mt" id="metric_ED50" value="ED50"
          <?php print(($q_output['metric']=='ED50')?'checked':'') ?> >ED50
        </label>
        <label class="radio-inline">
          <input type="radio" name="mt" id="metric_AUC" value="AUC"
          <?php print(($q_output['metric']=='AUC')?'checked':'') ?> >AUC
        </label>
      </div>

      <div class='form-group col-md-2 col-xs-12'>
        <label class="radio-inline">
          <input type="radio" name="sc" id="scale_linear" value="Linear"
          <?php print(($q_output['scale']=='Linear')?'checked':'') ?> >Linear
        </label>
        <label class="radio-inline">
          <input type="radio" name="sc" id="scale_log10" value="Log10"
          <?php print(($q_output['scale']=='Log10')?'checked':'') ?> >Log10
        </label>
      </div>

      <div class='form-group'>
        <button class='btn btn-default col-md-2 col-xs-12' name='submit_q' id='submit_q'>Request</button>
      </div>

      <input type='hidden' id='enet_explorer_token' name='token' value='<?php echo $enet_explorer_token; ?>' />
      <input type='hidden' name='formName' id='formName' value='enet_explorer_form'>

    </form>
  </div>
</div>

<div class='container-fluid text-center pops-results-panel' id='results-panel'>
  <?php
  if(isset($q_output['plots'])){
    print $q_output['plots'];
  }else{
  ?>
    <div>No plots available under these parameters.</div>
    <div class='placeHolderImg'>Elastic Net Explorer</div>
    <img class='img-responsive placeHolderImg' src='images/POPS_lung_logo.png'/>
</div>
<?php
  }
  print_footer();
?>
</body>

<?php
function explore_enet($q_parameters, $enetFeatureSetMap){
  $return_val = Array();

  // Setup where clause
  $clauses = array();
  if(isset($q_parameters['SWID'])){
    $clauses[]= "SWID like '{$q_parameters['SWID']}'";
  }
  if(isset($q_parameters['metric'])){
    $clauses[]= "metric like '{$q_parameters['metric']}'";
  }
  // if(isset($q_parameters['type'])){
  //   $clauses[]= "type like '{$q_parameters['type']}'";
  // }
  if(isset($q_parameters['scale'])){
    $clauses[]= "scale like '{$q_parameters['scale']}'";
  }

  $where_clause= (count($clauses) >0)? 'WHERE '.implode($clauses,' AND '):'';

  // Query data
  $query = "SELECT * FROM $data_table $where_clause";
  $res = query_db($query,NULL,NULL,'Elastic Net Explorer Query');

  // if result num < 1, exit
  if($res->num_rows  < 1){
    return NULL;
  }

  $type_list=array();
  $plots_found = false;
  $heatmap='';
  $freqmap='';
  $roc='';
  while($data = $res->fetch_object()){
    //if metric requested check for it, else only check type
    $met_check = (isset($q_parameters['metric']) && $data->metric != $q_parameters['metric'])?FALSE:TRUE;

    //Save types available for metric if not encountered previously
    if(!isset($type_list[$data->type]) && $met_check){
      $type_dir = $enetFeatureSetMap[$data->type];
      $type_list[$data->type] = $type_dir;

      if($data->type == $q_parameters['type']){
        $plots_found = true;
        $heatmap = "elasticnet/{$data->metric}/$type_dir/{$data->file}";
        $roc= "roc-curves/{$data->ROC_file}";
        $freqmap="elasticnet/{$data->metric}/$type_dir/freqmap/".str_replace('heatmap','freqmap',$data->file);
      }
    }
  }

  if($plots_found){
    $return_val['plots'] ='';
    // $iframe_width = "this.style.height=this.contentDocument.body.scrollWidth + 'px'";
    if(file_exists($roc)){
      $return_val['plots'].= "<div id='ROCtitle' class='container-fluid plotTitle'>ROC Plot<span class='glyphicon glyphicon-triangle-bottom ROC' title='Hide Plot'></span></div>";
      $return_val['plots'].= "<iframe class='enet-exp-plot ROCplot' alt='ROC plot could not be loaded' src='$roc' style='width:650px;height:400px'></iframe>";
      $return_val['plots'].= "<div class='container-fluid plotButton ROCplot'><a target='_BLANK' href='$roc'><button class='btn btn-default'>Open in New Window</button></a></div>";
    }else{
      $return_val['plots'].= "<div class='failAlert'>No ROC Plot Available</div>";
    }
    $return_val['plots'].= "<hr>";

    if(file_exists($freqmap)){
      $return_val['plots'].= "<div id='freqmaptitle' class='container-fluid plotTitle'>Frequency Plot<span class='glyphicon glyphicon-triangle-bottom freqmap' title='Hide Plot'></span></div>";
      $return_val['plots'].= "<iframe class='enet-exp-plot freqmapPlot' alt='Frequency plot could not be loaded' src='$freqmap' style='width:500px;height:500px'></iframe>";
      $return_val['plots'].= "<div class='container-fluid plotButton freqmapPlot'><a target='_BLANK' href='$freqmap'><button class='btn btn-default'>Open in New Window</button></a></div>";

    }else{
      $return_val['plots'].= "<div class='failAlert'>No Frequency Plot Available</div>";
    }
    $return_val['plots'].= "<hr>";

    if(file_exists($heatmap)){
      $return_val['plots'].= "<div id='heatmaptitle' class='container-fluid plotTitle'>Heatmap<span class='glyphicon glyphicon-triangle-bottom hmap' title='Hide Plot'></span></div>";
      $return_val['plots'].= "<iframe class='enet-exp-plot heatmapPlot'  alt='Elastic Net Heatmap plot could not be loaded' src='$heatmap' style='width:600px;height:650px'></iframe>";
      $return_val['plots'].= "<div class='container-fluid plotButton heatmapPlot'><a target='_BLANK' href='$heatmap'><button class='btn btn-default'>Open in New Window</button></a></div>";

    }else{
      $return_val['plots'].= "<div class='failAlert'>No Heatmap Available</div>";
    }
  }

  $return_val['swid']=$q_parameters['SWID'];
  $return_val['type_list']= $type_list;
  $return_val['metric'] = $q_parameters['metric'];
  $return_val['scale'] = $q_parameters['scale'];
  $return_val['type_name']=ucwords(str_replace('_',' ',$enetFeatureSetMap[$q_parameters['type']]));

  return $return_val;
}
?>
