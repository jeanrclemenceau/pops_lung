<?php
/*Filename: pops-activity_process.php
 *Author: Jean Clemenceau
 *Date Created: 5/27/2016
 *Contains the query processing to query cell line activity data in POPS.
*/

require_once('pops_header.php');
require_once('pops_dbi.php');
require_once('pops_toolfunctions.php');

define('TABLE_MAX',200); //Max number of displayed values
$returnValues = Array(); //values for the jquery request

// Verify that user is logged in
$notice = require_login('pops-activity.php', true);
if (isset($notice)){
  $returnValues['resultsTable'] = "<div class='failAlert'>$notice</div>";
  echo json_encode($returnValues);
  exit;
}

// Validate the form token
$valid_token = false;
if( isset($_POST['token']) && isset($_POST['formName']) && ($_POST['formName']=='activity_query_form' || $_POST['formName']=='query_results_filter_form') ){
  $valid_token = validate_form_token($_POST['formName'],$_POST['token']);
}
if(!$valid_token){
  $returnValues['resultsTable'] = "<div class='failAlert'><p>This form submission has expired or comes from an unrecognized referrer.</p><p>Refresh the page to try again</p><div>";
  echo json_encode($returnValues);
  exit;
}

//Set DRC data constants
$ed50_table = "cell_activity_ED50";
$auc_table  = "cell_activity_AUC";
$info_table = 'compound_info';
$ed50_color_table= 'ED50_colormap';
$auc_color_table = 'AUC_colormap';

//Setup parameters
$sort_param = isset($_POST['sortby_metric'])? sanitize($_POST['sortby_metric']) : 'ED50';
$sort_lines = ($sort_param != 'NONE')? TRUE : FALSE;

$sort_param_isset = isset($_POST['sortby_metric'])? "sortby_metric IS set" : "sortby_metric NOT set";

$search_parameter= isset($_POST['search_parameter'])? sanitize($_POST['search_parameter']) : 'SWID';
$pageMultiplier = isset($_POST['pageMultiplier'])? " OFFSET ".(TABLE_MAX * $_POST['pageMultiplier']) : '';

//Get compounds of interest
$search_chems = parse_all_input(sanitize($_POST['input_activity']), 'activity_file' );
//Set query filter for chemicals of interest
$sql_chems = '';
if(!empty($search_chems)){
  if($search_parameter != 'common_name'){
    $sql_chems = "($search_parameter IN('".implode("','",$search_chems)."'))";
  }else{
    $name_list = array();
    foreach($search_chems as $aName){
      $name_list[] = "($search_parameter LIKE '%$aName%')";
    }
    $sql_chems = implode(' OR ',$name_list) ;
  }
}

//Find unique cell lines of interest
$res_lines = query_db("SELECT * FROM $ed50_table LIMIT 1",NULL,NULL,'Cell line activity fields query');
foreach($res_lines->fetch_fields() as $f){
    $drc_keys[$f->name] = $f->name;
}

array_shift($drc_keys); // Remove SWID
$data_col_num = count($drc_keys);

//Query data
$cmpds_query="SELECT * FROM $info_table i RIGHT JOIN $ed50_table d USING (SWID) JOIN $auc_table c USING (SWID)";
$cmpds_query .= empty($sql_chems) ? '' : " WHERE $sql_chems";

$result = query_db($cmpds_query, NULL, NULL, 'Activity Query');
$fields = index_fields($result->fetch_fields() );
$total_real_rows = $result->num_rows;

//Get data fields and save header info
$table_header = array_merge(array('SWID', 'sens med. ED50', 'resist med. ED50', 'ratio ED50'), $drc_keys);
$total_col_num = count($table_header) + 3;//add name,analyses column and metric label
$table_header_AUC = array_merge(array('SWID'), $drc_keys);

// Table container
$mainTableString='';

if($total_real_rows > 0){
  //Get Color Schemes for hearmaps
  $color_ed50 = query_colors($ed50_color_table, "ED50");
  $color_auc = query_colors($auc_color_table, "AUC");

  #determine if AUC is shown by default
  $AUC_disp = ($total_real_rows > 1)? 'display:none;' : '';
  // $AUC_rowspan = ($total_real_rows > 1)? 1 : 2;
  $AUC_rowspan = ($total_real_rows > 1)? 2 : 3;

  // Containers to save data
  $ed50_table_results = array();//contains drc data ordered by cell line
  $auc_table_results = array();//contains AUC drc data ordered by cell line
  $swid_list = array(); //List of all matched SWIDs.

  // Output table
  $mainTableString.= "
  <div class='table-responsive' style='margin-bottom:0;'>
  <table id='drcTable' class='table table-bordered table-hover table-striped results drcTable'>
  </thead>
  <tr>
       <th colspan='$total_col_num' class='results'>Activity ($data_col_num cell lines) Search Results: $total_real_rows</th>
  </tr>

  <tr>
       <th class='results' style='font-size:x-small;' >SWID</th>
       <th class='results res_info extra_data' style='font-size:x-small;display:none;' id=';Supp' >Common Name</th>
       <th class='results' style='font-size:x-small;' title='Median value for sensitive cell lines' >Sens Med.</th>
       <th class='results' style='font-size:x-small;' title='Median value for resistant cell lines'>Resist Med.</th>
       <th class='results' style='font-size:x-small;' title='Sens med. value / Resist med value' >Median Ratio</th>
       <th class='results res_info extra_data' style='font-size:x-small;display:none;' id=';Supp' >Analyses</th>
       <th class='results' style='font-size:x-small;' title='Metric'>Metric</th>
       <th class='results' colspan='$data_col_num'>Mean Value</th>
  </tr>
  </thead><tbody>
  ";
//TODO removed
// <th class='results' style='font-size:x-small;' title='Metric'>Metric</th>
// colspan='$data_col_num'

  while($drc_object = $result->fetch_row() ){ //TODO changename to activityline
    //Save to ID list for selective display
    $swid_list[$drc_object[$fields["d.SWID"]]] = $drc_object[$fields["d.SWID"]];

    //selectively save and display compound info TODO add name
    $row_info = array($drc_object[$fields["d.SWID"]],$drc_object[$fields["i.sen_med_ED50"]],$drc_object[$fields["i.res_med_ED50"]],$drc_object[$fields["i.med_ratio_ED50"]]);
    $SWID = $row_info[0];

    //get data info
    $row_ed50 = array();
    $row_auc = array();
    foreach($drc_keys as $k){
        if( isset($fields["d.$k"]) ){
            $row_ed50[$k] = $drc_object[$fields["d.$k"]];
        }
        if( isset($fields["c.$k"]) ){
            $row_auc[$k] = $drc_object[$fields["c.$k"]];
        }
    }

    //Save row
    $ed50_table_results[] = array_merge($row_info, $row_ed50 );
    $auc_table_results[] = array_merge(Array($row_info[0]), $row_auc);

    //sort lines, nulls at end
    $sorted_lines = $row_ed50;
    if($sort_lines){
        if($sort_param == 'ED50'){
            $sorted_lines = sortLines($row_ed50);
        }else if($sort_param == 'AUC'){
            $sorted_lines = sortLines($row_auc);
        }
    }

    // TODO write compound name
    $cmpdName= isset($drc_object[$fields["i.common_name"]])? $drc_object[$fields["i.common_name"]] : "NA";

    // Get structure image
    $stctr_img = "images/compounds/$SWID";
    if( file_exists($stctr_img.'.png') ){
      $stctr_img = $stctr_img.'.png';
    }elseif( file_exists($stctr_img.'.jpg') ){
      $stctr_img = $stctr_img.'.jpg';
    }else{
      $stctr_img = "";
    }

    //Print compound row
    // $row_id=$drc_object[$fields['d.SWID']];
    $row_id=$SWID;

    //Print swid and common name
    $mainTableString.=  "
    <tr class='resultRow' id='$row_id' style='border-top:2px solid black;' sen='{$row_info[1]}' res='{$row_info[2]}'>
        <td class='results res_info' title='$cmpdName' rowspan=$AUC_rowspan >";

    if($stctr_img != ''){
      $mainTableString.= "<a href='#$SWID' SWID='$SWID' img_path='$stctr_img' class='cmpd_strct_show' data-toggle='modal' data-target='#cmpd_structure_modal'>$SWID</a>";
    }else{
      $mainTableString.= $SWID;
    }

    // Print compound name
    $mainTableString.=  "
        </td>
        <td class='results res_info extra_data' id=';Supp' style='font-size:x-small;display:none;' rowspan=$AUC_rowspan >$cmpdName</td>
        ";
    // print additional info about the compund
    for($i=1; $i < count($row_info); $i++){
        if(isset($row_info[$i])){
            if( is_numeric($row_info[$i]) ){
              $valueFormat = ($row_info[$i]!=0 && $row_info[$i] < 0.01)? '%.2E' : '%.2f';
              $value = sprintf($valueFormat,$row_info[$i]);// number_format($row_info[$i], 2, '.', '');
            }else{
              $value = $row_info[$i];
            }
        }else{$value = "<span class='na_text'>NA</span>";}
        $mainTableString.=  "<td class='results res_info' rowspan=$AUC_rowspan>$value</td>";
    }

    //Print hidden analyses
    $mainTableString.=  "<td class='results res_info extra_data' id=';Supp' style='display:none;' rowspan=$AUC_rowspan >";
    //$mainTableString.=  get_ElasticNet_link($SWID);
    //$mainTableString.=  get_ScanningKS_link($SWID);
    $mainTableString.=  get_GSEA_link($SWID,'ED50');
    $mainTableString.=  get_GSEA_link($SWID,'AUC') ;

    if( isset($drc_object[$fields["i.smile"]])){
      $mainTableString.=  "<a  href='http://pubchem.ncbi.nlm.nih.gov/search/index.html#collection=compounds&query_type=structure&query_subtype=similarity&query=".urlencode($drc_object[$fields["i.smile"]])."' target='_Blank title='Structure based search on PubChem'>PubChem</a>";
      }
    $mainTableString.=  "</td>";
    $mainTableString.=  "</tr>";


    //Print ED50 data
    $mainTableString.=  "<tr id='$row_id-ED50' class='ED50 heatmap'>";
    $mainTableString.= "<td class='results' style='font-size:x-small;'>ED50</td>";
    foreach($sorted_lines as $key=>$mean){
      $valueFormat = ($row_ed50[$key] < 0.01)? '%.2E' : '%.2f';
      $meanMFormat = ($row_ed50[$key] < 0.0001)? '%.2E' : '%.4f';

      if(isset($row_ed50[$key])){
        $value = "x&#772=".sprintf($valueFormat,$row_ed50[$key]).' &mu;M';
        $meanM = "x&#772=".sprintf($meanMFormat,$row_ed50[$key]);
        $color = get_color($row_ed50[$key],$color_ed50);
        $curve = get_drc_curve($SWID,$key,$drc_object[$fields["i.salvo_num"]],"ED50");
      }else{
        $value = 'x&#772=NA';
        $meanM = 'x&#772=NA';
        $color = 'gray';
        $curve = '';
      }
      //TODO removed ED50 class from tinycell
      $mainTableString.= "<td class='tinycell results' data-toggle='modal' data-target='#cmpd_structure_modal' style='background-color:$color;' title='$key: $value' cellLine='$key' SWID='$SWID' micromolar='$meanM &mu;M' common_name='$cmpdName' curve_url='$curve' ></td>";
    }
    $mainTableString.= "</tr>";

    //Print area under curve data
    $mainTableString.=  "<tr id='$row_id-AUC' class='AUC heatmap' style='$AUC_disp' >";
    $mainTableString.= "<td class='results' style='font-size:x-small;'>AUC</td>";
    foreach($sorted_lines as $key=>$mean){
      if(isset($row_auc[$key])){
          $value = "AUC=". number_format($row_auc[$key], 2, '.', '');
          $meanM = "AUC=". number_format($row_auc[$key], 4, '.', '');
          $color = get_color($row_auc[$key],$color_auc);
          $curve = get_drc_curve($SWID,$key,$drc_object[$fields["i.salvo_num"]],"AUC");
      }else{
          $value = 'AUC= NA';
          $meanM = 'AUC= NA';
          $color = 'gray';
          $curve = '';
      }
      //TODO removed AUC class from tinycell
      $mainTableString.= "<td class='tinycell results' data-toggle='modal' data-target='#cmpd_structure_modal' style='background-color:$color;' title='$key: $value' SWID='$SWID' cellLine='$key' micromolar='$meanM' common_name='$cmpdName' curve_url='$curve'></td>";
    }
    $mainTableString.= "</tr>";

  }
  //table end
  $mainTableString.= "</tbody></table></div>";
  $result->free();

  //Print color key tables
  //ED50 color key
  $min_color = sprintf("%.1f",$color_ed50[0][0]);
  $max_color = sprintf("%.1f",end($color_ed50[1]) );
  $color_data= range($min_color,$max_color,0.08);
  $special = array('0'=>'0', '2'=>'2&mu;M', '15.04'=>'15&mu;M', '30'=>'30&mu;M',);
  $special_color = array('0'=>'color:black', '2'=>'color:white', '15.04'=>'color:black', '30'=>'color:white');

  $mainTableString.= "<div class='table-responsive'> ";
  $mainTableString.= " <table id='heatmapkey' class='table heatmapkey drcTable ED50'> <tr>";
  foreach($color_data as $key=>$datum){
    $theColor = get_color($datum,$color_ed50);
    $txt= isset($special["$datum"])? $special["$datum"] : '';
    $font= isset($special_color["$datum"])? $special_color["$datum"] : '';
    $mainTableString.=  "<td class='heatmapkey' style='background-color:$theColor;$font'>$txt</td>";
  }
  $mainTableString.=  "<td class='heatmapkey' style='background-color:gray;'>NA</td>";
  $mainTableString.= "</tr></table>";

  //AUC color key
  $min_color = sprintf("%.1f",$color_auc[0][0]);
  $max_color = sprintf("%.1f",end($color_auc[1]) );
  $color_data= range($min_color,$max_color,5);
  $special = array('0'=>'0', '100'=>'100', '200'=>'200', '400'=>'400','600'=>'600');
  $special_color = array('600'=>'color:white','200'=>'color:white');

  $mainTableString.= " <table id='heatmapkey' class='table heatmapkey drcTable'> <tr class='AUC' style='$AUC_disp'>";
  foreach($color_data as $key=>$datum){
    $theColor = get_color($datum,$color_auc);
    $txt= isset($special["$datum"])? $special["$datum"] : '';
    $font= isset($special_color["$datum"])? $special_color["$datum"] : '';
    $mainTableString.=  "<td class='heatmapkey' style='background-color:$theColor;$font'>$txt</td>";
  }
  $mainTableString.=  "<td class='heatmapkey' style='background-color:gray;'>NA</td>";
  $mainTableString.= "</tr></table>";

  $mainTableString.= "</div> ";


  //Save all values for return.
  $returnValues['resultsTable'] = $mainTableString;

  //Export Compounds in table
  $returnValues['compounds'] = json_encode($swid_list);

  //Export Cell Lines in table
  $returnValues['clines'] = json_encode($drc_keys);

  //Export data to CSV file
  $returnValues['ED50_fname'] = export_CSV($table_header, $ed50_table_results, 'POPS_ED50_');
  $returnValues['AUC_fname'] = export_CSV($table_header_AUC, $auc_table_results, 'POPS_AUC_');

  // Output detailed info data box
  $returnValues['info_box']= "<table>
  <tr><td id='hover_id' style='text-align:center;font-weight:bold;'></td</tr>
  <tr><td id='hover_name' style='text-align:center;font-style:italic;'></td</tr>
  <tr><td id='hover_cline' style='text-align:center;'></td</tr>
  <tr><td id='hover_data'></td</tr>
  <tr><td id='hover_extra'></td</tr>
  </table>";
  }else{
      //NO COMPOUNDS FOUND
      $returnValues['resultsTable'] =  "<div class='failAlert' >No Activity Results Available.</div>";
      $returnValues['error'] = TRUE;
  }

  //Reset the token
  $returnValues['newToken'] = request_form_token($_POST['formName']);

// Return results in JSON format
echo json_encode($returnValues);
?>
