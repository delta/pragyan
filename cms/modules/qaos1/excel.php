<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

/**
 * @package pragyan
 * @copyright (c) 2012 Pragyan Team
 * @author shriram<vshriram93@gmail.com>
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

function displayExcelForTable($table="") {
  if($table=="") $table="<table><thead></thead></table>";
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: private",false);
  header("Content-Type: application/vnd.ms-excel");
  header("Content-Disposition: attachment; filename=\"Event.xls\";" );
  header("Content-Transfer-Encoding: binary");
  echo $table;
  exit(1);

  

}

function readExcelSheet($excelFile) {
  global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder;                                                         
  require_once($sourceFolder."/".$moduleFolder."/qaos1/excel/excel_reader2.php");
  $data = new Spreadsheet_Excel_Reader($excelFile);
  $dataArray = array();
  
  for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
    $dataArray[$i]=array();
    
    for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
      $dataArray[$i][$j] = (isset($data->sheets[0]['cells'][$i][$j]))?$data->sheets[0]['cells'][$i][$j]:NULL;
    }

}
  print_r($dataArray);
  return $dataArray;
}

function printContent($content = "") {
  global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder;                                                         
  $bgImage = $urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/images/bglogo.jpg";
  $printScript = $urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/styles/print.css";
  $ret=<<<PRINTSCRIPT
  <div id="printingSection" style="display:none">
     <style rel='stylesheet' type='text/css' media = 'print'>
      @media print {
       div {font-size: 10pt; line-height: 120%; background: white;}
     }
 </style>

    <div id="printThisDoc">
     <div style="background:url($bgImage);width:100%; height:100%;">$content
  </div>
</div>
     <script type="text/javascript"> 
        Popup($("#printThisDoc").html());
    function Popup(data) {
        var mywindow = window.open('', 'my div', 'height=700,width=800');
        mywindow.document.write('<html><head><title>DISCLAIMER</title>');
        mywindow.document.write('</head><body >');
        mywindow.document.write(data);
        mywindow.document.write('</body></html>');
        mywindow.print();
        mywindow.close();
        return true;
    }
    </script>
   </div>
PRINTSCRIPT;
return $ret;   
}
?>
