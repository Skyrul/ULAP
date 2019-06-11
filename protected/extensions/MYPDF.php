<?php
/**
 * @abstract This Component Class is created to access TCPDF plugin for generating reports.
 * @example You can refer http://www.tcpdf.org/examples/example_011.phps for more details for this example.
 * @todo you can extend tcpdf class method according to your need here. You can refer http://www.tcpdf.org/examples.php section for 
 *       More working examples.
 * @version 1.0.0
 */
Yii::import('ext.tcpdf.tcpdf',true);
class MYPDF extends TCPDF {
 
    // Load table data from file
    public function LoadData($file) {
        // Read file lines
        $lines = file($file);
        $data = array();
        foreach($lines as $line) {
            $data[] = explode(';', chop($line));
        }
        return $data;
    }
 
    // Colored table
    public function ColoredTable($header,$data) {
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        // Header
        $w = array(40, 35, 40, 45);
        $num_headers = count($header);
        for($i = 0; $i < $num_headers; ++$i) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $fill = 0;
        foreach($data as $row) {
            $this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, $row[1], 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 6, number_format($row[2]), 'LR', 0, 'R', $fill);
            $this->Cell($w[3], 6, number_format($row[3]), 'LR', 0, 'R', $fill);
            $this->Ln();
            $fill=!$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
	
	public function Header() {
	
		$date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/New_York'));

		$date->setTimezone(new DateTimeZone('America/Denver'));
	
		$html = '
			<table style="width:100%">
				<tr><td align="center">'.CHtml::image(Yii::app()->request->baseUrl.'/images/engagex-logo.jpg', '', array('width'=>150)).'</td></tr>
				<tr><td align="right" style="font-weight:normal; font-size:11px;">'.$date->format('m/d/Y g:i A').'</td></td>
			</table>
		';

		$this->writeHTML($html, true, false, false, false, '');
	}
	
	public function Footer() { 
		
		$html = '<span style="text-align:center; color:#0068B1; font-size:10px;">585 East 1860 South, Provo, Utah 84606 &bull; 800-515-8734 &bull; <a style="color:#0068B1; text-decoration:none;" href="mailto:info@engagex.com">info@engagex.com</a> &bull; <a style="color:#0068B1; text-decoration:none;" href="http://engagex.com">engagex.com</a></span>';
		
		$this->writeHTML($html, true, false, false, false, '');
		
		
		#FCB245 orange
		#0068B1 blue
		
		//START OF FOOTER TEXT AND LINE
		$orangeStyle = array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(252, 178, 69));
		
		$this->Line(5, $this->y, $this->w - 5, $this->y, $orangeStyle);
		
		$blueEtyle = array('width' => 5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 104, 177));
		
		$this->Line(5, $this->y+3, $this->w - 5, $this->y+3, $blueEtyle);
		
		
		//START OF PAGE NUMBER
		$this->SetY(-10);
		
		 // Set font
        $this->SetFont('helvetica', 'I', 8);
		
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}
?>