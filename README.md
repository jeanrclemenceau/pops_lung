# pops_lung 
<img src="pops/images/POPS_lung_logo.png" width="200px" align="right" />
Web Portal for the Precision Oncology Probe Set (POPS) for Lung Cancer.

This is the implementation for the data portal associated with the following publication:

> McMillan, E. A., Ryu, M.-J., Diep, C. H., Mendiratta, S., Clemenceau, J. R., Vaden, R. M., Kim, J.-H., Motoyaji, T., Covington, K. R., Peyton, M., Huffman, K., Wu, X., Girard, L., Sung, Y., Chen, P.-H., Mallipeddi, P. L., Lee, J. Y., Hanson, J., Voruganti, S., … White, M. A. (2018). **Chemistry-first approach for nomination of personalized treatment in lung cancer**. *Cell*, 173(4). https://doi.org/10.1016/j.cell.2018.03.028 

## Setup

This website is built based on a LAMP stack (Linux, Apache, MySQL, PHP). In order to set it up in your server environment, please install the following:

1. Apache: version 2.4+
1. MySQL: version 5.1+
1. PHP: version 5.5+

Once dependencies are intalled do the following:
1. Clone the `pops/` directory to your server
1. Contact @jeanrclemenceau for access to the data files
1. Replace the `placeholder.txt` file with the corresponding content for the following directories:

   * `pops/curves/`
   * `pops/gsea/`
   * `pops/pegplots/`
   * `pops/roc-curves/`
   * `pops/scanningks/`
   * `pops/elasticnet/`
   * `pops/images/compounds/`

1. Build the MySQL POPS database using the given dump file (`POPS_LUNG_DB_<version>.sql`)
1. Copy the contents of `SecuritySettings.php` and replace line 10 of `pops/pops_conf.php'
1. Update any relevant email addresses and passwords in `pops/pops_conf.php`
1. Setup your server for sending STMP emails
1. Point your httpd apache server to the code directory
1. Start your httpd server
