<?php
/*Filename: pops-mutation_card.php
 *Author: Jean Clemenceau
 *Date Created: 10/20/2016
 *Displays the detailed mutation information for a given gene on specified cell lines.
*/

// require_once('pops_header.php');
require_once('pops_dbi.php');
require_once('pops_toolfunctions.php');

define('MUTANT',1); //value indicating mutant status
define('WILD_TYPE',0); //value indicating wild type status
$returnValues = Array(); //values for the jquery request

$na_val = "<span class='na_text'>NA</span>";

// Verify that user is logged in
$notice = require_login('index.php', true);
if (isset($notice)){
  $returnValues['mainDataString'] = "<div class='failAlert'>$notice</div>";
  echo json_encode($returnValues);
  exit;
}

// Validate the form token
$valid_token = true;
//$valid_token = false;
//if( isset($_POST['token']) && isset($_POST['formName']) && $_POST['formName']=='mutation_card_query_form' ){
//  $valid_token = validate_form_token($_POST['formName'],$_POST['token']);
//}
//if(!$valid_token){
//  $returnValues['mainDataString'] = "<div class='failAlert'><p>This form submission has expired or comes from an unrecognized referrer.</p><p>Close the page and try again</p><div>";
//  echo json_encode($returnValues);
//  exit;
//}

//Set DRC data constants
$mut_status_table = "features_mut d";
$mut_data_table  = "features_mut_info i";

//Setup parameters
$gene = isset($_REQUEST['gn'])? sanitize($_REQUEST['gn']) : NULL;
$cellLines = isset($_REQUEST['cl'])? parse_all_input( sanitize($_REQUEST['cl']) ) : Array();

if(!isset($gene)){
  $returnValues['mainDataString'] = "<div class='failAlert'><p>No gene was submitted. Please request at least one gene to load details.</p><div>";
  echo json_encode($returnValues);
  exit;
}

//Set query filter for the gene of interest
$sql_gene= '';
if($gene != ''){
    $sql_gene="(gene_official LIKE '$gene')";
}

//Set query filter for cell lines of interest
$sql_lines = '';
if(!empty($cellLines)){
  $sql_lines = "AND (cell_line IN('".implode("','",$cellLines)."'))";
}

//Display All cell line mutations for chosen gene
$allLineDataString ='';
if(empty($cellLines)){
  $mut_status_query="SELECT * FROM $mut_status_table WHERE marker LIKE ? LIMIT 1";
  $result = query_db($mut_status_query, 's', $gene, 'Mutation Status Query');
  $infoCellLines = Array();
  foreach($result->fetch_fields() as $f){
      $infoCellLines[$f->name] = $f->name;
  }
  array_shift($infoCellLines);//remove "marker" field
  $cellLineListDiv =Array();

 $allLineDataString.= "
 <div class='table-responsive' style='margin-bottom:0;'>
 <table id='mutationTable' class='table table-bordered table-hover table-striped results drcTable'>
 <tr class='resultRow' id='allCellLinesRow' style='height:15px;'>";

  while($mut_object = $result->fetch_object() ){
    // Print cell lines
    foreach($infoCellLines as $key){
      $status = $mut_object->$key;
      if(isset($status)){
        if($status == MUTANT){
          $status_name = 'Mutant';
          $bgcolor = 'black';
          $txtColor = 'white';
        }else{
          $status_name = 'Wild Type';
          $bgcolor = 'white';
          $txtColor = 'black';
        }
      }
      else{
        $status_name = 'NA';
        $bgcolor = 'gray';
        $txtColor = 'black';
      }
      $allLineDataString.= "<td class='tinycell results' style='background-color:$bgcolor;color:$txtColor;' title='$key: $status_name' cellLine='$key' gene='$gene' status='$status_name'></td>";

      if($status == MUTANT){
        $cellLineListDiv[]= "<div class='col-sm-3'><a href='#$key' cellLine='$key' class='cellLineList'>$key</a></div>";
      }
    }
    $allLineDataString.= "</tr></table></div>";
  }
  $result->free();

  $allLineDataString.="<div id='cellLineList' class='row'>".implode('',$cellLineListDiv)."</div><hr>";
}

//Query detailed data
$res_mut = query_db("SELECT * FROM $mut_data_table WHERE $sql_gene $sql_lines ORDER BY cell_line, position, allele_count",NULL,NULL,' Mutation Details query');
$total_real_rows = $res_mut->num_rows;

//Save mutation details data
$mutDetailsByCellLine = Array();

// Http container
$mainDataString='';
$mainGeneString='';
$mainGeneName='';
$tableFileName='';

if($total_real_rows > 0){

  while($mut_object = $res_mut->fetch_object() ){
    //Save Gene data
    if($mainGeneString==''){
      $entrez = (isset($mut_object->entrez_id) && $mut_object->entrez_id!='')?
        "<a href='http://www.ncbi.nlm.nih.gov/gene/?term={$mut_object->entrez_id}' target='_BLANK'>{$mut_object->entrez_id}</a>"
        :$na_val;
      $trans = (isset($mut_object->transcript_id) && $mut_object->transcript_id!='')?
        "<a href='http://www.ensembl.org/Homo_sapiens/Transcript/Summary?t={$mut_object->transcript_id}' target='_BLANK'>{$mut_object->transcript_id}</a>"
        :$na_val;
      $mainGeneString.="<div class='gene_block row'>";
      $mainGeneString.="<div class='th col-sm-3'>Entrez ID</div>";
      $mainGeneString.="<div class='td col-sm-3'>$entrez</div>";
      $mainGeneString.="<div class='th col-sm-3'>Transcript ID</div>";
      $mainGeneString.="<div class='td col-sm-3'>$trans</div>";
      $mainGeneString.="<div class='th col-sm-3 chromosome_field'>Chromosome</div>";
      $mainGeneString.="<div class='td col-sm-3 chromosome_field'>{$mut_object->chromosome}</div>";
      $mainGeneString.="<div class='th col-sm-3 gene_location_field' style='display:none'>Location</div>";
      $mainGeneString.="<div class='td col-sm-3 gene_location_field' style='display:none' id='gene_location_field_txt'></div>";
      $mainGeneString.="<div class='th col-sm-3'>AA Length</div>";
      $mainGeneString.="<div class='td col-sm-3'>{$mut_object->aa_length}</div>";
      $mainGeneString.="</div><hr>";

      //Output card title
      $cline_title = (count($cellLines) == 1)?": {$cellLines[0]}":'';
      $mainGeneName="<a href='http://www.genecards.org/cgi-bin/carddisp.pl?gene={$mut_object->gene_official}' target='_BLANK'>{$mut_object->gene_official}</a>$cline_title";
    }

    // Initialize block list
    if(!isset($mutDetailsByCellLine[$mut_object->cell_line])){
      $mutDetailsByCellLine[$mut_object->cell_line] = Array();
    }
    $block_count = count($mutDetailsByCellLine[$mut_object->cell_line]);
    $mut_block ="<div class='mut_block' id='{$mut_object->cell_line}_$block_count'>";

    $mut_block.="<div class='mut_info_block row'>";
    $mut_block.="<div class='th col-sm-3'>Variant Type</div>";
    $mut_block.="<div class='td col-sm-3'>".PREG_REPLACE('/_/',' ',$mut_object->variant_type)."</div>";
    $mut_block.="<div class='th col-sm-3'>Dataset</div>";
    $mut_block.="<div class='td col-sm-3'>{$mut_object->dataset}</div>";
    $mut_block .="</div>";

    $mut_block.="<div class='mut_info_block row'>";
    $val = isset($mut_object->domain_name)?$mut_object->domain_name:$na_val;
    $mut_block.="<div class='th col-sm-3'>Domain Name</div>";
    $mut_block.="<div class='td col-sm-3'>".$val."</div>";
    $val = isset($mut_object->position)?"<a href='http://www.ensembl.org/Homo_sapiens/Location/View?r={$mut_object->chromosome}:{$mut_object->position}' target='_BLANK'>{$mut_object->position}</a>":$na_val;
    $mut_block.="<div class='th col-sm-3'>Position</div>";
    $mut_block.="<div class='td col-sm-3'>".$val."</div>";
    $val = isset($mut_object->dom_start)?$mut_object->dom_start:$na_val;
    $mut_block.="<div class='th col-sm-3'>Domain Start</div>";
    $mut_block.="<div class='td col-sm-3'>".$val."</div>";
    $val = isset($mut_object->dom_end)?$mut_object->dom_end:$na_val;
    $mut_block.="<div class='th col-sm-3'>Domain End</div>";
    $mut_block.="<div class='td col-sm-3'>".$val."</div>";
    $mut_block .="</div>";

    $mut_block.="<div class='mut_info_block row'>";
    $val = isset($mut_object->aa_change)?$mut_object->aa_change:$na_val;
    $mut_block.="<div class='th col-sm-3'>AA Change</div>";
    $mut_block.="<div class='td col-sm-3'>".$val."</div>";
    $val = isset($mut_object->cds_change)?$mut_object->cds_change:$na_val;
    $mut_block.="<div class='th col-sm-3'>CDS Change</div>";
    $mut_block.="<div class='td col-sm-3'>".$val."</div>";
    $val = isset($mut_object->allele_freq)?sprintf('%.4f',$mut_object->allele_freq):$na_val;
    $mut_block.="<div class='th col-sm-3'>Allele Frequency</div>";
    $mut_block.="<div class='td col-sm-3'>".$val."</div>";
    $val = isset($mut_object->allele_count)?$mut_object->allele_count:$na_val;
    $mut_block.="<div class='th col-sm-3'>Read Depth</div>";
    $mut_block.="<div class='td col-sm-3'>".$val."</div>";
    $mut_block .="</div>";

    $mut_block .="</div>";

    // Save block
    $mutDetailsByCellLine[$mut_object->cell_line][] = $mut_block;
  }

  //Print all mutation blocks by cell line.
  foreach($mutDetailsByCellLine as $cline => $mut_block_list){
    $mutcounterstring = (count($mut_block_list) >1)?"<sub>(".count($mut_block_list).")</sub>":'';
    $mainDataString .= "<div id='$cline' class='mutCardCLineTitle'>$cline$mutcounterstring</div>";
    $mainDataString .= implode("\n",$mut_block_list);
  }

  //Save card name title link.
  $returnValues['mainGeneName'] = $mainGeneName;

  //Save cell lines details.
  $returnValues['allCellLineData'] = $allLineDataString;

  //Save gene details block.
  $returnValues['mainGeneString'] = $mainGeneString;

  //Save all blocks for mutation details.
  $returnValues['mainDataString'] = $mainDataString;

}else{
    //NO MUTATIONS FOUND
    $cline_title = (count($cellLines) == 1)?": {$cellLines[0]}":'';
    $returnValues['mainGeneName'] =  "$gene$cline_title";
    $returnValues['mainDataString'] =  "<div class='failAlert' >No Mutation Details Available.</div>";
    $returnValues['error'] = TRUE;
}

$res_mut-> free();

//Reset the token
$returnValues['newToken'] = request_form_token($_POST['formName']);

// Return results in JSON format
echo json_encode($returnValues);
?>
