<?php
/*Filename: pops-sks_process.php
 *Author: Jean Clemenceau
 *Date Created: 6/23/2016
 *Contains the query processing to query Scanning KS data in POPS.
*/

require_once('pops_header.php');
require_once('pops_dbi.php');
require_once('pops_toolfunctions.php');

define('TABLE_MAX',200); //Max number of displayed values
define('PLOTPERLINE',4); //Max number ks plots per line in results.
$returnValues = Array(); //values for the jquery request

// Verify that user is logged in
$notice = require_login('pops-sks.php', true);
if (isset($notice)){
  $returnValues['resultsTable'] = "<div class='failAlert'>$notice</div>";
  echo json_encode($returnValues);
  exit;
}

// Validate the form token
$valid_token = false;
if( isset($_POST['token']) && isset($_POST['formName']) && $_POST['formName']=='scanningks_query_form' ){
  $valid_token = validate_form_token($_POST['formName'],$_POST['token']);
}
if(!$valid_token){
  $returnValues['resultsTable'] = "<div class='failAlert'><p>This form submission has expired or comes from an unrecognized referrer.</p><p>Refresh the page to try again</p><div>";
  $returnValues['error'] = TRUE;
  echo json_encode($returnValues);
  exit;
}

//Set DRC data constants
$data_table = 'scanning_ks d';

//Setup parameters
$output_mode= isset($_POST['output_mode'])? sanitize($_POST['output_mode']):'table';
$result_offset=0;
$pageMultiplier=0;
$offset_clause='';
if(isset($_POST['pageMultiplier'])){
  $pageMultiplier= $_POST['pageMultiplier'];
  $result_offset = (TABLE_MAX * $pageMultiplier);
  $offset_clause =" OFFSET $result_offset";
}
$search_chems = parse_all_input(sanitize($_POST['input_scanningks']), 'scanningks_file' );
$search_parameter= isset($_POST['search_parameter'])? sanitize($_POST['search_parameter']) : 'SWID';
$genes=parse_all_input(sanitize($_POST['input_gene']));
$pvalue=isset($_POST['input_pval'])?sanitize($_POST['input_pval']):'';
$medrat=isset($_POST['input_medrat'])?sanitize($_POST['input_medrat']):'';
$metric=isset($_POST['query_metric'])?sanitize($_POST['query_metric']):'';

//Account for extra fields on query
if($search_parameter == 'common_name'){
  $data_table.= ' LEFT JOIN compound_info i USING (SWID)';
}

//Get compounds of interest
$clauses = array();

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
//Set query filter for genes of interest
$sql_genes = '';
if(!empty($genes)){
  $genes_list = array();
  foreach($genes as $aGene){
    $genes_list[] = "(marker LIKE '%$aGene%')";
  }
  $sql_genes = implode(' OR ',$genes_list) ;
  $clauses[] = "($sql_genes)";
}
//Get pvalue and set query WHERE clause
if($pvalue != ''){
    $clauses[]= "(pvalue <= '$pvalue')";
}
//Get Median Ratio and set query WHERE clause
if($medrat != ''){
    $clauses[]= "(med_ratio <= '$medrat')";
}
//Get metric type and set query WHERE clause
if($metric != ''){
    $clauses[] ="(metric LIKE '$metric')";
}

//Setup WHERE clause
$where_clause =(!empty($clauses))? "WHERE ".implode(" AND ",$clauses) : '';

//Find total number of results
$res_cnt = query_db("SELECT COUNT(*) FROM $data_table $where_clause",NULL,NULL,' Scanning KS count query');
$res_row = $res_cnt -> fetch_row();
$total_real_rows = $res_row[0];

//Query data
$cmpds_query="SELECT * FROM $data_table $where_clause ORDER BY rank,TCGA_CoOc,med_ratio DESC LIMIT ".TABLE_MAX." $offset_clause";
$result = query_db($cmpds_query, NULL, NULL, 'Scanning KS Query');
$current_rows = $result->num_rows;

//Get data fields and save header info
$table_header = Array('SWID','marker','med_ratio', 'pvalue','metric','TCGA_CoOc','mutant_count');
$total_col_num = count($table_header);

// Table container
$mainTableString='';

if($current_rows > 0){
  // Containers to save data
  $sks_table_results = array();//contains sks data
  $swid_list = array();    //List of all matched SWIDs.
  $marker_list = array();  //List of all matched metrics.
  $min_medRat = 0;         //Minimum median ratio value found in current query
  $tcga_extrema = array(0,0); //Maximum and minimum TCGA CoOc in current query
  $max_mut_cnt = 0;        //Maximum mutation count value found in current query
  $max_pval = 0;           //Maximum P-value found in current query

  $backpage_allowed= ($result_offset > 0);
  $fwdpage_allowed= ($total_real_rows-$result_offset >= TABLE_MAX);
  $titleSpan = $total_col_num-2;

  // Output table
  if($output_mode=='table'){
    //Output title row w/ pagination
    $mainTableString.= "
    <div style='margin-bottom:0;'>
    <table id='sksTable' class='table table-bordered results sksTable table-hover table-striped'>
    </thead>
    <tr><th id='paginationBwd'>";
    if($backpage_allowed){
      $mainTableString.="
      <button id='goto_first_page' class='btn pagination' title='Get First Page' ><span class='glyphicon glyphicon-step-backward'></span></button>
      <button id='goto_prev_page' class='btn pagination' title='Get Previous Page' ><span class='glyphicon glyphicon-backward'></span></button>";
    }

    $mainTableString.= "</th>
    <th colspan='$titleSpan' id='headTitle' class='results'>Scanning KS Search Results: $result_offset-".($result_offset+$current_rows)."/<span id='maxResultCount'>$total_real_rows</span></th>
    <th id='paginationFwd'>";

    if($fwdpage_allowed){
      $mainTableString.="
      <button id='goto_next_page' class='btn pagination' title='Get Next Page' ><span class='glyphicon glyphicon-forward'></span></button>
      <button id='goto_last_page' class='btn pagination' title='Get Last Page' ><span class='glyphicon glyphicon-step-forward'></span></button>";
    }

    //Output column headers
    $mainTableString.="</th></tr> <tr>";
    foreach($table_header as $col_title){
      $header_content = ucwords(str_replace('_',' ',$col_title));
      $header_content = ($col_title=='TCGA_CoOc')? 'Frequency of Occurrence in TCGA' :$header_content;
      $mainTableString.="<th class='results'>$header_content</th>";
    }
    $mainTableString.="</tr> </thead><tbody>";

    // Process each entry
    while($sks_object = $result->fetch_assoc() ){
      //Save to ID and metric list for selective display
      $genes = preg_split("/[\\s,]+/",$sks_object["marker"],-1,PREG_SPLIT_NO_EMPTY);
      foreach( $genes as $a_gene){
      $marker_list[$a_gene] = $a_gene;
      }
      $swid_list[$sks_object["SWID"]] = $sks_object["SWID"];
      // $marker_list[$sks_object["marker"]] = $sks_object["marker"];
      $min_medRat = ($sks_object["med_ratio"] < $min_medRat)? $sks_object["med_ratio"]: $min_medRat;
      $max_pval = ($sks_object["pvalue"] > $max_pval)? $sks_object["pvalue"]: $max_pval;
      $max_mut_cnt = ($sks_object["mutant_count"] > $max_mut_cnt)? $sks_object["mutant_count"]: $max_mut_cnt;
      if($sks_object["TCGA_CoOc"] < $tcga_extrema[0]){
        $tcga_extrema[0] = $sks_object["TCGA_CoOc"];
      }if($sks_object["TCGA_CoOc"] > $tcga_extrema[1]){
        $tcga_extrema[1] = $sks_object["TCGA_CoOc"];
      }

      $SWID = $sks_object["SWID"];
      $marker = $sks_object["marker"];
      $metric = $sks_object["metric"];

      //get data
      $row = array();
      foreach($table_header as $k){
        $row[$k] = preg_replace('/,/',';',$sks_object[$k]);
      }

      //Save row
      $sks_table_results[] = $row;

      //Generate element attributes
      $cmpdName= isset($sks_object["common_name"])? $sks_object["common_name"] : "NA";//TODO add
      $row_id="$SWID-$marker-$metric";

      //row attributes
      $row_attribute='';
      foreach($table_header as $key){
        $content = isset($sks_object[$key])?$sks_object[$key]:'NA';
        $row_attribute.= "$key='$content' ";
      }

      // Get structure image
      $stctr_img = "images/compounds/$SWID";
      if( file_exists($stctr_img.'.png') ){
        $stctr_img = $stctr_img.'.png';
      }elseif( file_exists($stctr_img.'.jpg') ){
        $stctr_img = $stctr_img.'.jpg';
      }else{
        $stctr_img = "";
      }

      //get sks image
      $img_path = "scanningks/$metric/{$sks_object['img_src']}";
      $img_path = file_exists($img_path)?$img_path:'';

      //get sks heatmap PDF
      $pdf_path = "scanningks/$metric/Heatmap/HM_{$sks_object['img_src']}";
      $pdf_path = preg_replace('/.png$/','.pdf',$pdf_path);
      $pdf_path = file_exists($pdf_path)?$pdf_path:'';

      ////Output row
      $mainTableString.=  "<tr class='resultRow resultSKS' id='$row_id' $row_attribute>";

      //Print swid and structure link
      $mainTableString.="<td class='results res_info' title='$cmpdName' >";
      if($stctr_img != ''){
        $mainTableString.= "<a href='#$SWID' mod_title='$SWID' img_path='$stctr_img' class='cmpd_strct_show' data-toggle='modal' data-target='#cmpd_structure_modal'>$SWID</a>";
      }else{
        $mainTableString.= $SWID;
      }
      $mainTableString.=  "</td>";

      //Print marker and heatmap modal link
      $mainTableString.="<td class='results res_info'>";
      if($img_path != ''){
        $mainTableString.= "<a href='#' img_path='$img_path' pdf_path='$pdf_path' mod_title='$metric: $SWID-$marker' explorer='sks_explorer.php?id={$sks_object['sks_id']}' class='sks_mrkr_show' data-toggle='modal' data-target='#cmpd_structure_modal' >$marker</a>";
      }else{
        $mainTableString.= $marker;
      }
      $mainTableString.=  "</td>";

      // print The rest of the sks data
      foreach($table_header as $key){
        if($key == 'SWID' || $key == 'marker'){continue;}
        $content = "<span class='na_value'>NA</span>";// TODO add formatting

        // Populate cell contents
        if( isset($sks_object[$key]) ){
          $cell_data = $sks_object[$key];
          if( is_float($cell_data) ){

            //if p-value is marked as 0, show real value
            if($key=='pvalue' && $sks_object[$key] == 0){
              $content = "&lt; 2x10<sup>-5</sup>";
            }else{
              $content = sprintf('%.4f',$cell_data);
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
    $mainTableString.= "</tbody></table></div>";

/////START PLOT IMAGE MODE
  }elseif($output_mode=='plots'){

    $mainTableString.= "
    <div id='sksTable' class='container-fluid sksGrid' style='margin-bottom:0;'>
      <div class='row sksGridHeader'>";
    $headerOffset='';
    if($backpage_allowed){
      $mainTableString.="
      <div id='paginationBwd' class='paginationBwd_plot'>
      <button id='goto_first_page' class='btn pagination' title='Get First Page' ><span class='glyphicon glyphicon-step-backward'></span></button>
      <button id='goto_prev_page' class='btn pagination' title='Get Previous Page' ><span class='glyphicon glyphicon-backward'></span></button>
      </div>";
    }else{
      $mainTableString.="<div id='paginationBwd'></div>";
    }

    $mainTableString.= "
    <div id='headTitle' class='headTitle_plot'>Scanning KS Search Results: $result_offset-".($result_offset+$current_rows)."/<span id='maxResultCount'>$total_real_rows</span> </div>";

    if($fwdpage_allowed){
      $mainTableString.="
      <div id='paginationFwd' class='paginationFwd_plot'>
      <button id='goto_next_page' class='btn pagination' title='Get Next Page' ><span class='glyphicon glyphicon-forward'></span></button>
      <button id='goto_last_page' class='btn pagination' title='Get Last Page' ><span class='glyphicon glyphicon-step-forward'></span></button>
      </div>";
    }else{
      $mainTableString.="<div id='paginationFwd'></div>";
    }
    //close header 'row'
    $mainTableString.="</div>";
    $mainTableString.="<div class='row sksGridContent'>";


    // Process each entry
    while($sks_object = $result->fetch_assoc() ){
      //Save to ID and metric list for selective display
      $genes = preg_split("/[\\s,]+/",$sks_object["marker"],-1,PREG_SPLIT_NO_EMPTY);
      foreach( $genes as $a_gene){
      $marker_list[$a_gene] = $a_gene;
      }
      $swid_list[$sks_object["SWID"]] = $sks_object["SWID"];
      // $marker_list[$sks_object["marker"]] = $sks_object["marker"];
      $min_medRat = ($sks_object["med_ratio"] < $min_medRat)? $sks_object["med_ratio"]: $min_medRat;
      $max_pval = ($sks_object["pvalue"] > $max_pval)? $sks_object["pvalue"]: $max_pval;
      $max_mut_cnt = ($sks_object["mutant_count"] > $max_mut_cnt)? $sks_object["mutant_count"]: $max_mut_cnt;
      if($sks_object["TCGA_CoOc"] < $tcga_extrema[0]){
        $tcga_extrema[0] = $sks_object["TCGA_CoOc"];
      }if($sks_object["TCGA_CoOc"] > $tcga_extrema[1]){
        $tcga_extrema[1] = $sks_object["TCGA_CoOc"];
      }

      $SWID = $sks_object["SWID"];
      $marker = $sks_object["marker"];
      $metric = $sks_object["metric"];

      //get data
      $row = array();
      foreach($table_header as $k){
        $row[$k] = preg_replace('/,/',';',$sks_object[$k]);
      }

      //Save row
      $sks_table_results[] = $row;

      //Generate element attributes
      $cmpdName= isset($sks_object["common_name"])? $sks_object["common_name"] : "NA";//TODO add
      $plot_id="$SWID-$marker-$metric";

      //row attributes
      $plot_attribute='';
      foreach($table_header as $key){
        $content = isset($sks_object[$key])?$sks_object[$key]:'NA';
        $plot_attribute.= "$key='$content' ";
      }

      //get sks image
      $img_path = "scanningks/$metric/{$sks_object['img_src']}";
      $img_path = file_exists($img_path)?$img_path:'';

      //get sks heatmap PDF
      $pdf_path = "scanningks/$metric/Heatmap/HM_{$sks_object['img_src']}";
      $pdf_path = preg_replace('/.png$/','.pdf',$pdf_path);
      $pdf_path = file_exists($pdf_path)?$pdf_path:'';

      // output images
      $mainTableString.="
      <div id='$plot_id' class='resultSKS col-lg-3 col-md-4 col-sm-6' $plot_attribute>
        <a href='#' img_path='$img_path' pdf_path='$pdf_path' mod_title='$metric: $SWID-$marker' explorer='sks_explorer.php?id={$sks_object['sks_id']}' class='sks_plot_show' data-toggle='modal' data-target='#cmpd_structure_modal'>
          <img class='img-responsive img-rounded sksPlotImg' src='$img_path' alt='IMAGE NOT FOUND: $plot_id'/>
        </a>
      </div>
      ";
    }

    //close content 'row'
    $mainTableString.="</div>";
    //Close Grid
    $mainTableString.="</div>";
  }
  $result->free();


  //Save all values for return.
  $returnValues['resultsTable'] = $mainTableString;

  //Return pagination status
  $returnValues['pageMultiplier'] = $pageMultiplier;

  //Export Compounds in table
  $returnValues['compounds'] = json_encode($swid_list);

  //Export Markers in table
  $returnValues['markers'] = json_encode($marker_list);

  //Export maximum frequency value
  $returnValues['min_medRat'] = $min_medRat;

  //Export maximum frequency value
  $returnValues['max_mut_cnt'] = $max_mut_cnt;

  //Export maximum frequency value
  $returnValues['max_pval'] = $max_pval;

  //Export max and min weight values
  $returnValues['tcga_extrema'] = json_encode($tcga_extrema);

  //Export data to CSV file
  $returnValues['SKS_fname'] = export_CSV($table_header, $sks_table_results, 'pops_sks_');

  $returnValues['error'] = FALSE;

}else{
    //NO COMPOUNDS FOUND
    $returnValues['resultsTable'] =  "<div class='failAlert' >No Scanning KS Results Available.</div>";
    $returnValues['error'] = TRUE;
}

//Reset the token
$returnValues['newToken'] = request_form_token($_POST['formName']);


// Return results in JSON format
echo json_encode($returnValues);
?>
