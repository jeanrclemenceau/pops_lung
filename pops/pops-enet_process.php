<?php
/*Filename: pops-enet_process.php
 *Author: Jean Clemenceau
 *Date Created: 6/23/2016
 *Contains the query processing to query elastic net data in POPS.
*/

require_once('pops_header.php');
require_once('pops_dbi.php');
require_once('pops_toolfunctions.php');

define('TABLE_MAX',200); //Max number of displayed values
$returnValues = Array(); //values for the jquery request

// Verify that user is logged in
$notice = require_login('pops-enet.php', true);
if (isset($notice)){
  $returnValues['resultsTable'] = "<div class='failAlert'>$notice</div>";
  echo json_encode($returnValues);
  exit;
}

// Validate the form token
$valid_token = false;
if( isset($_POST['token']) && isset($_POST['formName']) && ($_POST['formName']=='elasticnet_query_form' || $_POST['formName']=='query_results_filter_form') ){
  $valid_token = validate_form_token($_POST['formName'],$_POST['token']);
}
if(!$valid_token){
  $returnValues['resultsTable'] = "<div class='failAlert'><p>This form submission has expired or comes from an unrecognized referer.</p><p>Refresh the page to try again</p><div>";
  $returnValues['error'] = TRUE;
  echo json_encode($returnValues);
  exit;
}

//Set main query table
$data_table = 'elastic_net d';

//Setup parameters
$search_parameter= isset($_POST['search_parameter'])? sanitize($_POST['search_parameter']) : 'marker';
$result_offset=0;
$pageMultiplier=0;
$offset_clause='';
if(isset($_POST['pageMultiplier'])){
  $pageMultiplier= $_POST['pageMultiplier'];
  $result_offset = (TABLE_MAX * $pageMultiplier);
  $offset_clause =" OFFSET $result_offset";
}
$type=isset($_POST['feature_list'])?sanitize($_POST['feature_list']): array();
$metric=isset($_POST['query_metric'])?sanitize($_POST['query_metric']):'';

//Account for extra fields on query
if($search_parameter == 'common_name'){
  $data_table.= ' LEFT JOIN compound_info i USING (SWID)';
}

//Get compounds of interest
$clauses = array();

$search_chems = parse_all_input(sanitize($_POST['input_elasticnet']), 'elasticnet_file' );
//Set query filter for chemicals of interest
$sql_chems = '';
if(!empty($search_chems)){
  if($search_parameter == 'SWID'){
    $sql_chems = "($search_parameter IN('".implode("','",$search_chems)."'))";
  }else{
    $name_list = array();
    foreach($search_chems as $aName){
      $name_list[] = "($search_parameter LIKE '%$aName%')";
    }
    $sql_chems = implode(' OR ',$name_list) ;
  }
  $clauses[] = $sql_chems;

}
//Get data types and set query WHERE clause
$sql_data = '';
if(!empty($type)){
    $sql_data = "(type IN('".implode("','",$type)."'))";
    $clauses[] = $sql_data;
}
//Get metric type and set query WHERE clause
$sql_metric= '';
if($metric != ''){
    $sql_metric="(metric LIKE '$metric')";
    $clauses[] = $sql_metric;
}

//Setup WHERE clause
$where_clause =(!empty($clauses))? "WHERE ".implode(" AND ",$clauses) : '';

//Find total number of results
$res_cnt = query_db("SELECT COUNT(*) FROM $data_table $where_clause",NULL,NULL,' Elastic Net count query');
$res_row = $res_cnt -> fetch_row();
$total_real_rows = $res_row[0];

//Query data
$cmpds_query="SELECT * FROM $data_table $where_clause ORDER BY metric,type,scale,SWID,roc_pval,weight>0 DESC,abs(weight) DESC,frequency DESC LIMIT ".TABLE_MAX." $offset_clause";

$result = query_db($cmpds_query, NULL, NULL, 'Elastic Net Query');
$current_rows = $result->num_rows;

//Get data fields and save header info
$table_header = Array('SWID','marker','weight', 'frequency','type','ROC_pval','metric','scale');
$total_col_num = count($table_header);

// Table container
$mainTableString='';

if($current_rows > 0){
  // Containers to save data
  $enet_table_results = array();//contains enet data
  $swid_list = array(); //List of all matched SWIDs.
  $marker_list = array(); //List of all matched metrics.
  $type_list = array(); //List of all matched feature types.
  $max_freq = 0;        //Maximum frequency value found in current query
  $weight_extrema = array(0,0); //Maximum and minimum weights in current query

  $backpage_allowed= ($result_offset > 0);
  $fwdpage_allowed= ($total_real_rows-$result_offset >= TABLE_MAX);
  $titleSpan = $total_col_num-2;//account for pagination cells

  // Output table
  $mainTableString.= "
  <div class='table-responsive' style='margin-bottom:0;'>
  <table id='enetTable' class='table table-bordered table-hover table-striped results enetTable'>
  </thead>
  <tr><th id='paginationBwd'>";
  if($backpage_allowed){
    $mainTableString.="
    <button id='goto_first_page' class='btn pagination' title='Get First Page' ><span class='glyphicon glyphicon-step-backward'></span></button>
    <button id='goto_prev_page' class='btn pagination' title='Get Previous Page' ><span class='glyphicon glyphicon-backward'></span></button>";
  }

    $mainTableString.= "</th>
    <th colspan='$titleSpan' id= headTitle class='results'>Elastic Net Search Results: $result_offset-".($result_offset+$current_rows)."/<span id='maxResultCount'>$total_real_rows</span></th>
    <th id='paginationFwd'>";

    if($fwdpage_allowed){
      $mainTableString.="
      <button id='goto_next_page' class='btn pagination' title='Get Next Page' ><span class='glyphicon glyphicon-forward'></span></button>
      <button id='goto_last_page' class='btn pagination' title='Get Last Page' ><span class='glyphicon glyphicon-step-forward'></span></button>";
    }
    $mainTableString.="</th></tr> <tr>";

  foreach($table_header as $col_title){
    $header_content = ucwords(str_replace('_',' ',$col_title));
    $mainTableString.="<th class='results'>$header_content</th>";
  }

  $mainTableString.="</tr> </thead><tbody>";

  while($enet_object = $result->fetch_assoc() ){
    //Save to ID and metric list for selective display
    $swid_list[$enet_object["SWID"]] = $enet_object["SWID"];
    $marker_list[$enet_object["marker"]] = $enet_object["marker"];
    $type_title = ucwords( str_replace('_',' ',$enetFeatureSetMap[$enet_object["type"]]) );
    $type_list[$enet_object["type"]] = $type_title;
    $max_freq = ($enet_object["frequency"] > $max_freq)? $enet_object["frequency"]: $max_freq;
    if($enet_object["weight"] < $weight_extrema[0]){
      $weight_extrema[0] = $enet_object["weight"];
    }if($enet_object["weight"] > $weight_extrema[1]){
      $weight_extrema[1] = $enet_object["weight"];
    }

    $SWID = $enet_object["SWID"];

    //get data
    $row = array();
    foreach($table_header as $k){
      $row[$k] = $enet_object[$k];
    }

    //Save row
    $enet_table_results[] = $row;

    // TODO write compound name
    $cmpdName= isset($enet_object["common_name"])? $enet_object["common_name"] : "NA";

    //Print compound row 
    $row_id=$SWID.'-'.$enet_object['marker'].'-'.$enet_object['type'];

    // Get structure image
    $stctr_img = "images/compounds/$SWID";
    if( file_exists($stctr_img.'.png') ){
      $stctr_img = $stctr_img.'.png';
    }elseif( file_exists($stctr_img.'.jpg') ){
      $stctr_img = $stctr_img.'.jpg';
    }else{
      $stctr_img = "";
    }

    //Print swid and structure link
    $mainTableString.=  "
    <tr class='resultRow' id='$row_id'";

    // Add row attributes
    foreach($table_header as $key){
      $content = isset($enet_object[$key])?$enet_object[$key]:'NA';
      $mainTableString.= "$key='$content' ";
    }

    // print SWID cell with link to structure
    $mainTableString.=" >
        <td class='results res_info' title='$cmpdName' >";

    if($stctr_img != ''){
      $mainTableString.= "<a href='#$SWID' mod_title='$SWID' img_path='$stctr_img' class='cmpd_strct_show' data-toggle='modal' data-target='#cmpd_structure_modal'>$SWID</a>";
    }else{
      $mainTableString.= $SWID;
    }
    $mainTableString.=  "</td>";


    //Print marker and heatmap modal link
    $type_dir=$enetFeatureSetMap[$enet_object["type"]];
    $pdf_path = "elasticnet/{$enet_object['metric']}/$type_dir/{$enet_object['file']}";
    $pdf_path= file_exists($pdf_path)?$pdf_path:'';

    $mainTableString.="<td class='results res_info'>";
    if($pdf_path != ''){
      $mainTableString.= "<a href='#' pdf_path='$pdf_path' mod_title='$type_title: $SWID-{$enet_object["marker"]}' explorer='enet_explorer.php?id=$SWID&tp={$enet_object["type"]}&mt={$enet_object["metric"]}&sc={$enet_object["scale"]}' class='enet_mrkr_show' data-toggle='modal' data-target='#cmpd_structure_modal' >{$enet_object["marker"]}</a>";
    }else{
      $mainTableString.= $enet_object["marker"];
    }
    $mainTableString.=  "</td>";

    // print The rest of the Enet data
    foreach($table_header as $key){
      if($key == 'SWID' || $key == 'marker'){continue;}
      $content = "<span class='na_val'>NA</span>";

      // Determine cell contents
      if( isset($enet_object[$key]) ){
        $cell_data = $enet_object[$key];
        if( is_numeric($cell_data) ){
          if($key == 'ROC_pval'){
            //Print pvalue and ROC curve modal link
            $val = sprintf('%.2e',$cell_data);
            $pdf_path = "roc-curves/{$enet_object['ROC_file']}";
            if(file_exists($pdf_path)){
              $content = "<a href='#' pdf_path='$pdf_path' mod_title='ROC Curve: $SWID-{$enet_object["marker"]}' explorer='enet_explorer.php?id=$SWID&tp={$enet_object["type"]}' class='ROC_curve_show' data-toggle='modal' data-target='#cmpd_structure_modal' >$val</a>";
            }else{
              $content= $val;
            }
          }else{
            $content = sprintf('%.4f',$cell_data);
          }
        }elseif($key == 'type'){
          if($enet_object[$key] == 'mut'){
            $content = "<a href='#' class='mut_card_modal_trigger' gene='{$enet_object['marker']}' data-toggle='modal' data-target='#gene_card_modal' >$type_title</a>";
          }else{
            $content= $type_title;
          }
        }else{
          $content = ucwords($cell_data);
        }
      }
      // print cell
      $mainTableString.=  "<td class='results res_info'>$content</td>";
    }

    $mainTableString.= "</tr>";

  }
  $mainTableString.= "</tbody></table>
  <input type='hidden' id='mutation_card_query_token' name='token' value='".request_form_token('mutation_card_query_form')."' />
  </div>";

  $result->free();


  //Save all values for return.
  $returnValues['resultsTable'] = $mainTableString;

  //Return pagination status
  $returnValues['pageMultiplier'] = $pageMultiplier;

  //Export Compounds in table
  $returnValues['compounds'] = json_encode($swid_list);

  //Export Markers in table
  $returnValues['markers'] = json_encode($marker_list);

  //Export feature types in table
  $returnValues['types'] = json_encode($type_list);

  //Export maximum frequency value
  $returnValues['max_freq'] = $max_freq;

  //Export max and min weight values
  $returnValues['weight_extrema'] = json_encode($weight_extrema);

  //Export data to CSV file //TODO send query parameters
  $returnValues['ENET_fname'] = export_CSV($table_header, $enet_table_results, 'pops_enet_');

  $returnValues['error'] = FALSE;

}else{
    //NO COMPOUNDS FOUND
    $returnValues['resultsTable'] =  "<div class='failAlert' >No Elastic Net Results Available.</div>";
    $returnValues['error'] = TRUE;
}

  //Reset the token
  $returnValues['newToken'] = request_form_token($_POST['formName']);

// Return results in JSON format
echo json_encode($returnValues);
?>
