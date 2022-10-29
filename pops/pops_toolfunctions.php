<?php
/*Filename: pops_toolfunctions.php
 *Author: Jean Clemenceau
 *Date Created: 5/27/2016
 *Contains the query common functions needed to process queries on the POPS database.
*/

require_once('pops_conf.php');
require_once('pops_dbi.php');

/*parse_all_input
 *Parses comma & whitespace separated values from a string
 *and/or a file. Returns an array containing all unique elements.
 *Input:
 * -$inputlist: String containing list of values.
 * -$file_id: Name of file received in form.
 * -$get_id: Name of GET Input.
 *Output:
 * -Array of parsed and sanitized values.
 */
function parse_all_input($inputlist = '', $file_id = '', $get_id = ''){
    $all_input = Array();
    $errors = Array();
    //parse actual values from string
    if ( isset ($inputlist) ){
        $list_values=preg_split("/[\\s,]+/",sanitize($inputlist),-1,PREG_SPLIT_NO_EMPTY);
        $all_input = array_merge($all_input, $list_values);
    }

    //parse from get
    if ( $get_id!='' && isset($_GET[$get_id]) ) {
        $get_values=preg_split("/[,]+/",sanitize($_GET[$get_id]),-1,PREG_SPLIT_NO_EMPTY);
        $all_input = array_merge($all_input, $get_values);
    }

    //parse from files
    if ( $file_id != '' && isset($_FILES[$file_id]) && 0 < $_FILES[$file_id]['size'] ) {
        if($_FILES[$file_id]["type"] == "text/plain"){
        if($_FILES[$file_id]["size"] < 20000) {
            if ($_FILES[$file_id]["error"] > 0) {
                $errors[]= "Error: " . $_FILES[$file_id]["error"] . "<br />";
            }
            else {
                if (is_uploaded_file($_FILES[$file_id]['tmp_name'])){
                    $fileData=file_get_contents($_FILES[$file_id]['tmp_name']);
                    $file_values=preg_split("/[\\s,]+/", sanitize($fileData), -1, PREG_SPLIT_NO_EMPTY);
                    $all_input=array_merge($all_input, $file_values);
                }
            }
        }
        else { $errors[]= "File is too large."; }
        }
        else{ $errors[]= "File type is not allowed."; }
    }

    $all_input = array_unique($all_input);
    return array_values($all_input);
}

/*Matches a field name to its numeric index from a fetch_array()
 *Output array indices will have format "{table alias}.{field name}".
 *Input:
 *  -$field_objects: Array of field objects (obtained from $result->fetch_fields();)
 *Output:
 *  -Associative array that maps table.fieldname -> numeric index
*/
function index_fields($field_objects){
    $simple_fields = ARRAY();

    for($i=0; $i < count($field_objects); $i++){
        $indx = $field_objects[$i]->table . '.' . $field_objects[$i]->name;
        $simple_fields[$indx] = $i;
    }
    return $simple_fields;
}


/*Finds the color associated with a value according to a mapping using binary search.
 *Input:
 * $data: Value to be mapped to a color
 * $map: Matrix contining the ranges and corresponding color.
 *      -$map[0] : Lower limit of color ranges
 *      -$map[1] : Higher limit of color ranges
 *      -$map[2] : Color value for range
 *Output:
 *  -$data_color: array containing corresponding colors.
*/
function get_color($data,$map){
    $lows = $map[0];    //Low limits of color
    $highs = $map[1];   //High limits of color
    $colors = $map[2];   //Colors
    $size_limit = 5;      //Minimum halving length before linear search;
    $min = 0;           //Segment's lower limit
    $max = count($map[2])-1; //Segment's upper limit
    $i = $max/2;        //pointer
    $the_color = '';

    while( $the_color == '' && $max-$min > 0 && $max < count($map[2]) && $min >= 0 ){
        //Check low end
        if($data < $lows[$i]){
            $max = $i;
            if($max-$min > $size_limit){
                $i = $min +(($max-$min)/2);
            }else{
                if( $data < $lows[0]){ //If lower than min, return first
                    $the_color = $colors[0];
                }else{
                    $i -= 1;
                }
            }
            continue;
        }
        //Check high end
        else if($data > $highs[$i]){
            $min = $i;
            if($max-$min > $size_limit){
                $i = $min +(($max-$min)/2);
            }else{
                if( $data > end($highs) ){ //If greater than max, return last
                    $the_color = end($colors);
                }else{
                    $i += 1;
                }
            }
            continue;
        }
        //Color found
        else{
            $the_color = $colors[$i];
        }
    }
    return $the_color;
}

/*This fuction exports the given data to a CSV file
 *Input:
 *  -$col_hdrs: Array containing the headers for the table.
 *  -$table: The two-dimensional array containing the data.
 *  -$rootName: Base name for the generated file.
 *  -$dec: Numbers of decimal places for numeric values.
 *  -$dir: Directory where the file will be stored.
 *Output:
 *  -$tmpfname: the name and location of the CSV file (Relative to server directory root).
 *
*/
function export_CSV($col_hdrs, $table, $rootName = "murics_", $dec=4, $dir= CSV_TEMP){
    system("find $dir -type f -atime +1 -exec rm {} \;"); //Remove all files not used for more than two days

    $extension = '.csv';
    $txt = '';
    //set headers
    $txt .= implode(',', $col_hdrs) . "\n";

    //populate data
    for ( $n=0;$n<count($table);$n++) {
        $line = '';
        foreach($table[$n] as $data_fix){
            if (!isset($data_fix) ) { $datum = ",NA";}
            elseif(!is_numeric($data_fix) ){ $datum = sprintf(",%s",$data_fix); }
            else{
                $datum= sprintf(",%.{$dec}f",$data_fix);
            }
            $line .= $datum;
        }
        $line = ltrim($line, ',');
        $txt .= $line . "\n";
    }

    // Create file
    $tmpfname = tempnam($dir, $rootName);
    exec("mv $tmpfname $tmpfname$extension");
    $tmpfname .= $extension;

    // Write to file
    $handle = fopen($tmpfname, "w");
    fwrite($handle, $txt);
    fclose($handle);

    return $dir.basename($tmpfname);
}

/*This fuction sorts an associative array conserving
 *key-value relation and placess null values at end
 *Input:
 *  -$lines: Array containing the values to be sorted.
 *Output:
 *  -$lines: Sorted array.
*/
function sortLines($lines){
    asort($lines,SORT_NUMERIC);
    $no_more_nulls = FALSE;

    foreach( $lines as $key=>$value){
        if( !isset($value) ){
            unset($lines[$key]);
            $lines[$key] = $value;
        }
    }
    return $lines;
}

/*Returns a link to GSEA results if they are available.
 *Results directory must be located in 'gsea/' with format 'GSEA_$SWID'.
 *Input:
 *  -$SWID: The ID of the compound of interest.
 *  -$metric: The name of the metric used to calculate GSEA.
 *Output:
 *  -$link: The link to results.
*/
function get_GSEA_link($SWID,$metric='ED50'){
    $link = '';
    $directory = "gsea/$metric/GSEA_$SWID/";

    if( file_exists($directory) ){
        $link = "<a href='$directory' target='_blank'>GSEA($metric)</a> ";
    }

    return $link;
}

/*Returns a link to Elastic Net results if they are available.
 *Input:
 *  -$SWID: The ID of the compound of interest.
 *  -$enet_table: Name of table containing Elastic Net data. (default: 'elastic_net')
 *Output:
 *  -$link: The link to results.
*/
function get_ElasticNet_link($SWID,$enet_table='elastic_net'){
    $link = '';
    $eNetExplorer = POPS_HOME."enet_explorer.php";
    $fileFinder_q = "SELECT metric,type,file FROM $enet_table WHERE SWID = ? AND file IS NOT NULL";
    $file_res = query_db($fileFinder_q,'s',$SWID,'Find ENET file Query');
    if($file_res->num_rows > 0){
      $enet_entry = $file_res->fetch_row();
      if( file_exists("elastic_net/{$enet_entry[0]}/{$enet_entry[1]}/{$enet_entry[2]}") ){
        $link= "<a href='$eNetExplorer?id=$SWID&met={$enet_entry[0]}&typ={$enet_entry[1]}' target='_Blank' title='Elastic Net'>E. Net</a>";
      }
    }
    $file_res->close();
    return $link;
}

/*Returns a link to Scanning KS results if they are available.
 *Input:
 *  -$SWID: The ID of the compound of interest.
 *Output:
 *  -$link: The link to results.
*/
function get_ScanningKS_link($SWID){
    $link = '';
    $sksExplorer = POPS_HOME."pops-sks.php";
    $fileFinder_q = "SELECT metric,img_src FROM scanning_ks WHERE SWID = ? AND img_src IS NOT NULL";
    $file_res = query_db($fileFinder_q,'s',$SWID,'Find SKS file Query');
    if($file_res->num_rows > 0){
      $sks_entry = $file_res->fetch_row();
      if( file_exists("scanning_ks/{$sks_entry[0]}/{$sks_entry[1]}") ){
        $link= "<a href='$sksExplorer?id=$SWID' target='_Blank' title='Scanning KS'>SKS</a>";
      }
    }
    $file_res->close();
    return $link;
}

/*Returns the url of the corresponding dose response curve.
 *Results directory must be located in 'curves/'.
 *Input:
 *  -$ID: The ID of the compound of interest (eg. SWID,WLID).
 *  -$clin: the name of the cell line of interest.
 *  -$metric: metric of interest.
 *Output:
 *  -$link: The link to the curve.
*/
// function get_drc_curve($ID,$clin,$metric='ED50'){
//     $link = '';
//     $exists = false;
//     $basename = "DRC";
//     $url = "curves/$metric/$ID/curves_$basename-$ID-$clin.pdf";
//     if( file_exists($url) ){
//       $link = $url;
//     }
//
//     return $link;
// }


function get_drc_curve($ID,$clin,$salvo=1,$metric='ED50'){
    $link = '';
    $exists = false;
    $basename = "DRC$salvo";
    $url = "curves/$metric/$ID/curves_$basename-$ID-$clin.pdf";
    if( file_exists($url) ){
      $link = $url;
    }

    return $link;
}
// /*Returns a link to Elastic Net results if they are available.
//  *Results directory must be located in 'elasticnet/' with format.
//  *Only results of features in the '$enetFeatureSetMap' array
//  *defined in pops-header.php will be checked.
//  *Input:
//  *  -$SWID: The ID of the compound of interest.
//  *  -$salvo: the DRC salvo of the compound of interest.
//  *  -$screen: The MuRiCS screen where the dataset belongs.
//  *       -Default: Chemical screen (DRC1&2)
//  *Output:
//  *  -$link: The link to results.
// */
// function get_ElasticNet_link($SWID,$metric,$type){
// }
?>
