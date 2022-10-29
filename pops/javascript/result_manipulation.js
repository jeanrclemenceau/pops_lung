/*Filename: result_manipulation.js
 *Author: Jean R Clemenceau
 *Date Created: 06/15/2015
 *Contains the javascript scripts used to manilupate query results in POPS
 *Assumes client has loaded jQuery
*/

//Global variable saves Id lists.
table_elementIDs = {};

// Global variable keeps track of result paginatoin
pageMultiplier = 0;

/*Deserializes a string into a json object
 *  -aString: a string containing a serialized form
 */
function deserialize(aString){
  var rawArray = aString.split('&');
  var object = {};
  $.each(rawArray,function(key,val){
    data = val.split('=');
    object[data[0]] = decodeURIComponent(data[1]);
  });
  return object;
}

/*Adds a list of IDs for table rows
 *  -listName: Identifier for list to be added.
 *  -list: Associative array containing the IDs for table rows.
 */
function addIdList(listName, list){
    table_elementIDs[listName]= JSON.parse(list);
}

/*Populates a given Select element with values from an array
 * Input:
 *  -selectID: The ID of the select element
 *  -listName: Name of list of IDs to be used.
 */
function populateSelect(selectID, listName){
  var theList = table_elementIDs[listName];
  for(var id in theList){
    $('#'+selectID).append("<option value='"+id+"'>"+theList[id]+"</option>");
  }
}

/*Sets the reference url for an html link (<a>).
 *Link will be opened in new page.
 * Input:
 *  -fileURL: URL to be set.
 *  -link_id: The link's id.
 */
function setCSVlink(filename, link_id){
  $('#'+link_id).click(function(e){
    e.preventDefault();
    open(filename,'_self');
  });
  $('#'+link_id).removeAttr('disabled')
}

// /*Sets the reference url for an html link (<a>).
//  *Link will be opened in new page.
//  * Input:
//  *  -fileURL: URL to be set.
//  *  -link_id: The link's id.
//  */

function getHighContrast(rgbColor){
  var color1_brightness = 255; //white
  var color2_brightness = 0; //black

  // find hex color
  var rgbArray = rgbColor.match(/rgb\((\d+),\s?(\d+),\s?(\d+)\)/);

  //Handle unexpected values.
  if(rgbArray == null){
    return 'black';
  }else{
    // convert to YIQ color scheme brightness
    for(i=1; i<4; i++){
      rgbArray[i] = parseInt(rgbArray[i]);
    }
    var brightness_val= ((rgbArray[1]*299)+(rgbArray[2]*587)+(rgbArray[3]*114)) / 1000;
    var colorChooser = Math.abs(color1_brightness-brightness_val) > Math.abs(color2_brightness-brightness_val)

    // determine color with greatest brighness difference
    return ( colorChooser )? 'white':'black';  
  }
}

/*Resets POPS form token through AJAX according.
 *Input:
 *  -formName: A string containing the name of from to be renewed
 *  -tokenHolder:  The token id for the form (form name)
*/
function renewToken(formName,tokenHolder){
  $.ajax({
    url: 'access_functions.php',
    data: {func:'renew_token',form:formName},
    type: 'POST',
    async: false,
    dataType: 'json',
    success: function(response){
      if( jQuery.isEmptyObject(response) ){
        console.log("AJAX_token_renew_ERROR: Token is empty");
      }else{
        $('#'+tokenHolder).val(response.token);
      }
    },
    error: function(xhr, status, errorThrown){
      console.log("AJAX_token_renew_ERROR: "+ xhr.status+" - "+ xhr.statusText );
    }
  });
}

//Execute main ajax request
/*Executes a POPS query through AJAX according to a given callback function.
 *Displays a "loading" gif while the request is processed.
 *Input:
 *  -processScript: URL for the script that processes the query
 *  -queryResponse: call back function to process the results.
 *  -form_data: A FormData object containing the from to be sent
 *  -token_id:  The token id for the form (form name)
*/
function executeQuery(processScript,queryResponse,form_data,token_id){
  $.ajax({
    url:  processScript,
    data: form_data,
    type: "POST",
    dataType: "json",
    contentType: false,
    processData: false,
    beforeSend: function(){
        $('#results-panel').html("<img id='loadingGif' style='margin:auto' alt='Processing Query' src='images/loading.gif'/>");
    },
    success: function(response){
        console.log("AJAX: request Results - External content loaded successfully");
        $('#'+token_id).val(response.newToken);
        queryResponse(response);
    },
    error: function(xhr, status, errorThrown){
      console.log("AJAX: requestResults() - Error: " + xhr.status + ' - ' + xhr.statusText );
      if(xhr.status == 500){
        renewToken(deserialize(form_serialized)['formName'],token_id);
        $('#results-panel').html("<div class='failAlert'><p>Error: Our server could not process your request. Please try again.</p><p>If the error persist, please submit a ticket <a href='pops_contact.php?s=4'>HERE</a> </div>");
      }else{
        $('#results-panel').html("<div class='failAlert'>Error: Results could not be loaded</div>");
      }
    }
  });
}

/*Resets the row span for a certain class according to AUC and ED50 data
 *Input:
 *  -theSelector: the JQuery selector which rowspan will be changed
*/
function adjustRowSpan(theSelector){
  //Adjust row span for info cells
  var totalRowSpan = 1;
  if($('tr.AUC:visible').length > 0 ){
    totalRowSpan = totalRowSpan+1;
  }
  if($('tr.ED50:visible').length > 0 ){
    totalRowSpan = totalRowSpan+1;
  }
  $(theSelector).attr('rowspan',totalRowSpan);
}
