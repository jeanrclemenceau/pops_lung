<?php
/*Filename: pops-mutation_process.php
 *Author: Jean Clemenceau
 *Date Created: 10/11/2016
 *Contains the query processing to query cell line mutation data in POPS.
*/

require_once('pops_header.php');
require_once('pops_dbi.php');
require_once('pops_toolfunctions.php');

define('TABLE_MAX',200); //Max number of displayed values
define('MUTANT',1); //value indicating mutant status
define('WILD_TYPE',0); //value indicating wild type status
define('COL_THRESHOLD',10); //value indicating number of columns before collapsing results
$returnValues = Array(); //values for the jquery request

// Verify that user is logged in
$notice = require_login('pops-mutation.php', true);
if (isset($notice)){
  $returnValues['resultsTable'] = "<div class='failAlert'>$notice</div>";
  echo json_encode($returnValues);
  exit;
}

// Validate the form token
$valid_token = false;
if( isset($_POST['token']) && isset($_POST['formName']) && $_POST['formName']=='mutation_query_form' ){
  $valid_token = validate_form_token($_POST['formName'],$_POST['token']);
}
if(!$valid_token){
  $returnValues['resultsTable'] = "<div class='failAlert'><p>This form submission has expired or comes from an unrecognized referrer.</p><p>Refresh the page to try again</p><div>";
  echo json_encode($returnValues);
  exit;
}

//Set DRC data constants
$mut_status_table = "features_mut d";
$mut_data_table  = "features_mut_info i";

//Setup parameters
$search_parameter= isset($_POST['search_parameter'])? sanitize($_POST['search_parameter']) : 'exact';
$cellLines =isset($_POST['cell_line_list'])?sanitize($_POST['cell_line_list']): array();

$result_offset=0;
$pageMultiplier=0;
$offset_clause='';
if(isset($_POST['pageMultiplier'])){
  $pageMultiplier= $_POST['pageMultiplier'];
  $result_offset = (TABLE_MAX * $pageMultiplier);
  $offset_clause =" OFFSET $result_offset";
}

//Get compounds of interest
$search_genes = parse_all_input(sanitize($_POST['input_mutation']), 'mutation_file' );
//Set query filter for genes of interest
$sql_genes = '';
if(!empty($search_genes)){
  if($search_parameter == 'exact'){
    $sql_genes = "(marker IN('".implode("','",$search_genes)."'))";
  }else{
    $name_list = array();
    foreach($search_genes as $aName){
      $name_list[] = "(marker LIKE '%$aName%')";
    }
    $sql_genes = implode(' OR ',$name_list) ;
  }
}
//Get cell lines
$queried_fields = '*';
if(!empty($cellLines)){
    $queried_fields= "marker,".implode(",",$cellLines);
}
// set query WHERE clause
$where_clause = empty($sql_genes) ? '' : " WHERE $sql_genes";

//Query data
$res_cnt = query_db("SELECT COUNT(*) FROM $mut_status_table$where_clause",NULL,NULL,' Mutation count query');
$res_row = $res_cnt -> fetch_row();
$total_real_rows = $res_row[0];


$mut_status_query="SELECT $queried_fields FROM $mut_status_table$where_clause";
$mut_status_query.= " LIMIT ".TABLE_MAX." $offset_clause";
$result = query_db($mut_status_query, NULL, NULL, 'Mutation Query');
// $fields = index_fields($result->fetch_fields() );
$cellLines = Array();
foreach($result->fetch_fields() as $f){
    $cellLines[$f->name] = $f->name;
}
$current_rows = $result->num_rows;

//Get data fields and save header info
array_shift($cellLines); // Remove "marker" field
$data_col_num = count($cellLines);
$table_header = array_merge(array('Gene'), $cellLines);
$total_col_num = count($table_header);
$few_cols = ($data_col_num > COL_THRESHOLD)? false : true;

// Table container
$mainTableString='';

if($current_rows > 0){

  // Containers to save data
  $table_results = array();//contains drc data ordered by cell line
  $gene_list = array(); //List of all matched SWIDs.
  $vartype_list = array(); //List of all mutation variant types present.
  $dataset_list = array(); //List of all datasets represented (matched/unmatched).
  $allele_freq_extrema = array(0,0); //Maximum and minimum allele frequencies in current query


  $backpage_allowed= ($result_offset > 0);
  $fwdpage_allowed= ($total_real_rows-$result_offset >= TABLE_MAX);
  $titleSpan = $total_col_num-2;//account for pagination cells

  // Output table //If too many lines, blanket, if not, split
  $mainTableString.= "
  <div class='table-responsive' style='margin-bottom:0;'>
  <table id='mutationTable' class='table table-bordered table-hover table-striped results drcTable'>
  </thead>
  <tr>
  <th colspan='$total_col_num'>
  <span id='paginationBwd' style='display:inline-block;width: 15%;'>";
  if($backpage_allowed){
    $mainTableString.="
    <button id='goto_first_page' class='btn pagination' title='Get First Page' ><span class='glyphicon glyphicon-step-backward'></span></button>
    <button id='goto_prev_page' class='btn pagination' title='Get Previous Page' ><span class='glyphicon glyphicon-backward'></span></button>";
  }

  $mainTableString.= "</span>
     <span id='headTitle' colspan='$titleSpan' class='results'style='display:inline-block;width: 69%;'>Mutations ($data_col_num cell lines) Search Results: $result_offset-".($result_offset+$current_rows)."/<span id='maxResultCount'>$total_real_rows</span></span>
     <span id='paginationFwd' style='display:inline-block;width: 15%;'>";

  if($fwdpage_allowed){
  $mainTableString.="
    <button id='goto_next_page' class='btn pagination' title='Get Next Page' ><span class='glyphicon glyphicon-forward'></span></button>
    <button id='goto_last_page' class='btn pagination' title='Get Last Page' ><span class='glyphicon glyphicon-step-forward'></span></button>";
  }

  $mainTableString.="
  </span>
  </th>
  </tr> <tr>
    <th class='results' >Gene</th>";

  if($few_cols){
    foreach($cellLines as $line){
      $mainTableString.="<th class='results'>$line</th>";
    }
  }else{
    $mainTableString.="<th class='results' colspan='$data_col_num'>Mutation Status</th>";
  }

  $mainTableString.="</tr>
  </thead><tbody>
  ";

  while($mut_object = $result->fetch_assoc() ){
    //Save to ID list for selective display
    $gene_list[$mut_object['marker']] = $mut_object['marker'];//TODO add entrez?

    //selectively save and display  info
    $row_info = array($mut_object['marker']);
    $gene = $row_info[0];

    //get data info
    $row = array();
    foreach($cellLines as $field=>$index){
        $row[$field] = $mut_object[$field];
    }

    //Save row
    $table_results[] = array_merge($row_info, $row );

    //sort lines, nulls at end
    $sorted_lines = $row;
    if(isset($sort_param)){
      $sorted_lines = sortLines($row);
    }

    $row_id=$gene;

    //Print swid and common name
    $mainTableString.=  "
    <tr class='resultRow' id='$row_id' style='border-top:2px solid black;'>
        <td class='results res_info' style='border-right:2px solid black;'>";

    if(true){
      $plot_url = "pegplots/{$gene}_PegPlot.pdf";
      $mainTableString.= "<a href='#$gene' gene='$gene' plot_url='$plot_url' class='res_info' data-toggle='modal' data-target='#cmpd_structure_modal'>$gene</a>";
    }else{
      $mainTableString.= $gene;
    }

    // Print cell lines
    foreach($sorted_lines as $key=>$status){
      if(isset($row[$key])){
        if($status == MUTANT){
          $specific_mut_query = "SELECT aa_change,allele_freq,variant_type,dataset,b.* FROM $mut_data_table JOIN (SELECT MAX(allele_count) as max_allele_count,count(*) as cnt FROM $mut_data_table WHERE gene_official = '$gene' AND cell_line = '$key') as b ON (allele_count = b.max_allele_count) WHERE gene_official = '$gene' AND cell_line = '$key';";
          $res = query_db($specific_mut_query,NULL,NULL,' Mutation details query');
          $res_row = $res -> fetch_object();

          $vartype_list[$res_row->variant_type] = preg_replace('/_/',' ',$res_row->variant_type);
          $dataset_list[$res_row->dataset] = $res_row->dataset;
          if($res_row->allele_freq < $allele_freq_extrema[0]){
            $allele_freq_extrema[0] = $res_row->allele_freq;
          }if($res_row->allele_freq > $allele_freq_extrema[1]){
            $allele_freq_extrema[1] = $res_row->allele_freq;
          }

          $var_type = $res_row->variant_type;
          $allele_freq= $res_row->allele_freq;
          $dataset =$res_row->dataset;
          $mutcnt = $res_row->cnt;
          $firstMut = $res_row->aa_change;
          $status = 'Mutant';
          $bgcolor = 'black';
          $txtColor = 'white';
        }else{
          $mutcnt = 0;
          $status = 'Wild Type';
          $bgcolor = 'white';
          $txtColor = 'black';
          $firstMut='';
          $mutcnt='';
          $var_type = '';
          $allele_freq= '';
          $dataset ='';
        }
        $fill = (isset($few_cols) && $few_cols == true)? "<span class='hidden-xs'>$status</span>" : '';
      }
      else{
        $status = 'NA';
        $bgcolor = 'gray';
        $txtColor = 'black';
        $fill = '';
        $firstMut='';
        $mutcnt='';
        $var_type = '';
        $allele_freq= '';
        $dataset ='';
      }
      $mainTableString.= "<td class='tinycell results' data-toggle='modal' data-target='#gene_card_modal' style='background-color:$bgcolor;color:$txtColor;' title='$key: $status' cellLine='$key' gene='$gene' status='$status' mut1='$firstMut' count='$mutcnt' variant='$var_type' dataset='$dataset' allele_freq='$allele_freq'>$fill</td>";
    }
    $mainTableString.= "</tr>";

  }
  //table end
  $mainTableString.= "</tbody></table>
  <input type='hidden' id='mutation_card_query_token' name='token' value='".request_form_token('mutation_card_query_form')."' />
  </div>";
  $result->free();


  //Save all values for return.
  $returnValues['resultsTable'] = $mainTableString;

  //Return pagination status
  $returnValues['pageMultiplier'] = $pageMultiplier;

  //Export Compounds in table
  $returnValues['genes'] = json_encode($gene_list);

  //Export Cell Lines in table
  $returnValues['clines'] = json_encode($cellLines);

  //Export Compounds in table
  $returnValues['datasets'] = json_encode($dataset_list);

  //Export Compounds in table
  $returnValues['variants'] = json_encode($vartype_list);

  //Export max and min allele frequency values
  $returnValues['allele_freq_extrema'] = json_encode($allele_freq_extrema);

  //Export data to CSV file //TODO remove
  $returnValues['Mut_fname'] = export_CSV($table_header, $table_results, 'pops_mut_');

  // Output detailed info data box
  $returnValues['info_box']= "<table>
  <tr><td id='hover_gene' style='text-align:center;font-weight:bold;'></td</tr>
  <tr><td id='hover_cline' style='text-align:center;'></td</tr>
  <tr><td id='hover_status' style='text-align:center;font-style:italic;'></td</tr>
  <tr><td id='hover_mut1'style='font-size:small;'></td</tr>
  <tr><td id='hover_mutcnt' style='font-size:x-small;'></td</tr>
  </table>";
  }else{
      //NO COMPOUNDS FOUND
      $returnValues['resultsTable'] =  "<div class='failAlert' >No Mutation Results Available.</div>";
      $returnValues['error'] = TRUE;
  }

  //Reset the token
  $returnValues['newToken'] = request_form_token($_POST['formName']);

// Return results in JSON format
echo json_encode($returnValues);
   ?>
