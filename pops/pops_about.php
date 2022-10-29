<?php
/*Filename: pops_about.php
 *Author: Jean Clemenceau
 *Date Created: 5/19/2016
 *Contains the page with background information about the POPS project.
*/

require_once('pops_header.php');

check_login();
?>
<head>
  <?php
  add_setup('About POPS'); 
  add_styles('pops_bootstrap_infopage.css');
  add_scripts('pops-about_client.js');
  ?>
  <style>
    .completedItem{
      text-decoration: line-through;
    }
  </style>
</head>
<body>
<?php
  print_navigation('about POPS');
?>
<div class='container-fluid row row-centered'>
  <div class=''>
    <ul class='nav nav-tabs subnav'>
      <li class='active'><a data-toggle='tab' href='#whatpops'>What is POPS?</a></li>
      <li><a data-toggle='tab' href='#credits'>Credits</a></li>
      <li><a data-toggle='tab' href='#release'>Release Notes</a></li>
    </ul>
  </div>
</div>
<div class='container-fluid' id='main_content' >
<div class='tab-content'>

  <!--What is POPS tab-->
  <div id='whatpops'  class='tut_block tab-pane fade in active'>
    <h2 class='text-center pageTitle'>What is POPS?</h2>
    <p class='about_main'>
    Lung cancer is the leading cause of cancer-related deaths in the United States, however due to extreme clinical and molecular heterogeneity, it remains plagued by a lack of characterized pharmaceutical intervention targets. The precision oncology probe set (POPS-lung) represents a de-novo chemistry-first screening approach designed to discover new, druggable targets in a cancerous setting. To do this, a diversity oriented chemical library (~200,000 chemicals) was profiled across a heavily annotated test-bed of cell line models to enable the identification of a subset of chemicals (171) tightly linked to robust features specifying target sensitivities. These features not only enable mechanism of action hypotheses but also allow for the potential to act as enrollment biomarkers to enable selection of patient populations predicted to respond. To enable community based hypothesis testing and independent data analysis, chemical sensitivity patterns and pre-computed associated feature relationships have been integrated into a web-based graphical user interface with the ability to query the following data:
    </p>
    <ul>
      <li><h3>Activity Query</h3>
        <p class='about_details'>
	All screening data, including dose response curves, associated AUC and ED50. All resulting analyses were run using both ED50 and AUC as sensitivity metrics.
        </p>
      </li>
      <li><h3>Elastic Net Regression</h3>
        <p class='about_details'>
Quantitative predictive features were assigned from measures of (1) Illumina Bead Array Expression Data (2) RNAseq (3) whole exome sequencing mutation calls (4) Illumina SNP array-based copy number quantification (5) reverse-phase protein arrays (RPPA) (5) carbon-tracing metabolomics flux analyses. Chemical/genetic relationships for 171 chemicals passed significance thresholds and are queryable.
	</p>
      </li>
      <li><h3>Scanning Kolmogorov-Smirnov Statistic</h3>
        <p class='about_details'>
	A modification to a Kolmogorov-Smirnov (KS) test was made to query single gene mutations or pairwise combinations of co-occurring gene mutations to rank those that can predict the best selective sensitivity to each chemical.	
	</p>
      </li>
      <li><h3>Mutation Query</h3>
        <p class='about_details'>
A panel of 124 NSCLC cell lines was subjected to whole exome sequencing (average 60X coverage). 34/124 have corresponding tumor and matched B-cell non-tumorigenic DNA from which definitive somatically acquired lesions can be determined. For the remaining 90 cell lines, a series of filters leveraging the tumor/normal matched dataset and publically available data were used to filter out probable germline alterations and enrich for somatically acquired mutations. The mutation query page displays searchable filtered mutation calls for all 124 cell lines. Lollipop plots also compare positional mutation calls in the UTSW cell line panel to LUAD and LUSC tumors in the TCGA and in an MD Anderson (MDACC) tumor panel.
	</p>
      </li>
    </ul>
  <p style='font-size: small;'>*Please refer to our <a href='pops_tutorial.php' title='Click for Tutorial'>tutorial</a> for more information.
  </div>

  <!--Credits tab-->
  <div id='credits'  class='tut_block tab-pane fade'>
    <p>We thank the following individuals for their contributions to this project:</p>
    <ul>
      <li><span class='accent'>Elizabeth A. McMillan</span></li>
      <li><span class='accent'>Caroline H. Diep</span></li>
      <li><span class='accent'>Saurabh Mendiratta</span></li>
      <li><span class='accent'>Myung-Jeon Ryu</span></li>
      <li><span class='accent'>Jean R. Clemenceau</span></li>
      <li><span class='accent'>Rachel M. Vaden</span></li>
      <li><span class='accent'>Kyle R.  Covington</span></li>
      <li><span class='accent'>Michael Peyton</span></li>
      <li><span class='accent'>Kenneth Huffman</span></li>
      <li><span class='accent'>Xiaofeng Wu</span></li>
      <li><span class='accent'>Luc Girard</span></li>
      <li><span class='accent'>Ju-Hwa Kim</span></li>
      <li><span class='accent'>Yeojin Sung</span></li>
      <li><span class='accent'>Pei-Hsaun Chen</span></li>
      <li><span class='accent'>Joo Young Lee</span></li>
      <li><span class='accent'>Jordan Hanson</span></li>
      <li><span class='accent'>Yunku Yu</span></li>
      <li><span class='accent'>Sunho Park</span></li>
      <li><span class='accent'>Jessica Sudderth</span></li>
      <li><span class='accent'>Christopher DeSevo</span></li>
      <li><span class='accent'>Donna M. Muzny</span></li>
      <li><span class='accent'>HarshaVardhan Doddapaneni</span></li>
      <li><span class='accent'>Richard A. Gibbs</span></li>
      <li><span class='accent'>Tae-Hyun Hwang</span></li>
      <li><span class='accent'>John V.  Heymach</span></li>
      <li><span class='accent'>Ignacio Wistuba</span></li>
      <li><span class='accent'>Kevin R. Coombes</span></li>
      <li><span class='accent'>Noelle S. Williams</span></li>
      <li><span class='accent'>David A.  Wheeler</span></li>
      <li><span class='accent'>John B. MacMillan</span></li>
      <li><span class='accent'>John D. Minna</span></li>
      <li><span class='accent'>Ralph J. Deberardinis</span></li>
      <li><span class='accent'>Michael G. Roth</span></li>
      <li><span class='accent'>Bruce A. Posner</span></li>
      <li><span class='accent'>Hyunseok Kim</span></li>
      <li><span class='accent'>Michael A. White</span></li>
    </ul>
  </div>

  <!--Release Notes tab-->
  <div id='release'  class='tut_block tab-pane fade'>
    <ol class='tut_block_list'>
      <li>04/25/2017
      <ul>
      <li>POPS v1.0 released</li>
      </ul>
      </li>
    </ol>
  </div>
</div>


</div>

</div>

<?php
  print_footer();
?>
</body>
