<?php
/**
 * phplogmon
 *
 * Copyright (c) 2012-2014 Holger de Carne and contributors, All Rights Reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class WebViewAbout extends WebView {

	private static $sFlatIcons = array(
		"log_download.png", "log_view.png", "map_link.png", "whois_link.png"
	);

	private static $sFlagIcons = array(
		"country/AD.png", "country/BM.png", "country/CU.png", "country/FR.png", "country/ID.png", "country/KZ.png", "country/MP.png",
		"country/PE.png", "country/SG.png", "country/TT.png", "country/AE.png", "country/BN.png", "country/CV.png", "country/GA.png",
		"country/IE.png", "country/LA.png", "country/MR.png", "country/PF.png", "country/SH.png", "country/TV.png", "country/AF.png",
		"country/BO.png", "country/CX.png", "country/GB.png", "country/IL.png", "country/LB.png", "country/MS.png", "country/PG.png",
		"country/SI.png", "country/TW.png", "country/AG.png", "country/BR.png", "country/CY.png", "country/GD.png", "country/IM.png",
		"country/LC.png", "country/MT.png", "country/PH.png", "country/SK.png", "country/TZ.png", "country/AI.png", "country/BS.png",
		"country/CZ.png", "country/GE.png", "country/IN.png", "country/LI.png", "country/MU.png", "country/PK.png", "country/SL.png",
		"country/UA.png", "country/AL.png", "country/BT.png", "country/DE.png", "country/GG.png", "country/IO.png", "country/LK.png",
		"country/MV.png", "country/PL.png", "country/SM.png", "country/UG.png", "country/AM.png", "country/BV.png", "country/DJ.png",
		"country/GH.png", "country/IQ.png", "country/LR.png", "country/MW.png", "country/PM.png", "country/SN.png", "country/US.png",
		"country/AO.png", "country/BW.png", "country/DK.png", "country/GI.png", "country/IR.png", "country/LS.png", "country/MX.png",
		"country/PN.png", "country/SO.png", "country/UY.png", "country/AR.png", "country/BY.png", "country/DM.png", "country/GL.png",
		"country/IS.png", "country/LT.png", "country/MY.png", "country/PR.png", "country/SR.png", "country/UZ.png", "country/AS.png",
		"country/BZ.png", "country/DO.png", "country/GM.png", "country/IT.png", "country/LU.png", "country/MZ.png", "country/PS.png",
		"country/ST.png", "country/VA.png", "country/AT.png", "country/CA.png", "country/DZ.png", "country/GN.png", "country/JM.png",
		"country/LV.png", "country/NA.png", "country/PT.png", "country/SV.png", "country/VC.png", "country/AU.png", "country/CC.png",
		"country/EC.png", "country/GQ.png", "country/JO.png", "country/LY.png", "country/NE.png", "country/PW.png", "country/SY.png",
		"country/VE.png", "country/AW.png", "country/CD.png", "country/EE.png", "country/GR.png", "country/JP.png", "country/MA.png",
		"country/NF.png", "country/PY.png", "country/SZ.png", "country/VG.png", "country/AZ.png", "country/CF.png", "country/EG.png",
		"country/GS.png", "country/KE.png", "country/MC.png", "country/NG.png", "country/QA.png", "country/TC.png", "country/VI.png",
		"country/BA.png", "country/CG.png", "country/ER.png", "country/GT.png", "country/KG.png", "country/MD.png", "country/NI.png",
		"country/RO.png", "country/TD.png", "country/VN.png", "country/BB.png", "country/CH.png", "country/ES.png", "country/GU.png",
		"country/KH.png", "country/ME.png", "country/NL.png", "country/RS.png", "country/TG.png", "country/VU.png", "country/BD.png",
		"country/CI.png", "country/ET.png", "country/GW.png", "country/KI.png", "country/MG.png", "country/NO.png", "country/RU.png",
		"country/TH.png", "country/WF.png", "country/BE.png", "country/CK.png", "country/EU.png", "country/GY.png", "country/KM.png",
		"country/MH.png", "country/NP.png", "country/RW.png", "country/TJ.png", "country/WS.png", "country/BF.png", "country/CL.png",
		"country/FI.png", "country/HK.png", "country/KN.png", "country/MK.png", "country/NR.png", "country/SA.png", "country/TL.png",
		"country/YE.png", "country/BG.png", "country/CM.png", "country/FJ.png", "country/HN.png", "country/KP.png", "country/ML.png",
		"country/NU.png", "country/SB.png", "country/TM.png", "country/YT.png", "country/BH.png", "country/CN.png", "country/FK.png",
		"country/HR.png", "country/KR.png", "country/MM.png", "country/NZ.png", "country/SC.png", "country/TN.png", "country/ZA.png",
		"country/BI.png", "country/CO.png", "country/FM.png", "country/HT.png", "country/KW.png", "country/MN.png", "country/OM.png",
		"country/SD.png", "country/TO.png", "country/ZM.png", "country/BJ.png", "country/CR.png", "country/FO.png", "country/HU.png",
		"country/KY.png", "country/MO.png", "country/PA.png", "country/SE.png", "country/TR.png", "country/ZW.png"
	);

	private static $sOxygenIcons = array(
		"user_generic.png", "user_invalid.png", "user_valid.png", "vendor_generic.png"
	);

	private static $sMiscIcons = array(
		"country_generic.png", "type_denied.png", "type_error.png", "type_granted.png"
	);

	public function __construct($dbh) {
		parent::__construct($dbh, false, false, false, false, false, false);
	}

	public function sendHtml() {
		$l12n = $this->l12n();
		$this->beginHtml();
		$title = $l12n->t("LogMon - About");
		$this->beginHeader($title);
		$this->endHeader();
		$this->beginBody();
		$this->printNavBar();
		$this->printLicense();
		$this->printAttribution();
		$this->endBody(false);
		$this->endHtml();
	}

	private function printLicense() {
		Html::out("phplogmon");
		print("<pre>");
		Html::out("Copyright (c) 2012-2014 Holger de Carne and contributors, All Rights Reserved.\n");
		Html::out("This program is free software: you can redistribute it and/or modify\n");
		Html::out("it under the terms of the GNU General Public License as published by\n");
		Html::out("the Free Software Foundation, either version 3 of the License, or\n");
		Html::out("(at your option) any later version.\n");
		Html::out("This program is distributed in the hope that it will be useful,\n");
		Html::out("but WITHOUT ANY WARRANTY; without even the implied warranty of\n");
		Html::out("MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\n");
		print("<a href=\"http://www.gnu.org/licenses\">");
		Html::out("GNU General Public License");
		print("</a>");
		Html::out(" for more details.");
		print("</pre>");
	}

	private function printAttribution() {
		$this->printImages(self::$sFlatIcons);
		print("<br/>");
		$this->printImages(self::$sFlagIcons);
		print("<br/>");
		$this->printImages(self::$sOxygenIcons);
		print("<br/>");
		$this->printImages(self::$sMiscIcons);
	}

	private function printImages($images) {
		foreach($images as $image) {
			$src = "img/{$image}";
			print("<img class=\"icon16\" src=\"${src}\" alt=\"icon\" title=\"{$src}\" /> ");
		}
	}

	private function printEventData() {
		$dbh = $this->dbh();
		$typeId = $this->getSessionType();
		$loghostId = $this->getSessionLoghost();
		$networkId = $this->getSessionNetwork();
		$select = $dbh->prepare(
			"SELECT a.typeid, b.id, b.loghost, c.id, c.network, d.id, d.hostip, d.host, d.countrycode, d.countryname, ".
				"SUM(a.count), MIN(a.first), MAX(a.last) ".
			"FROM event a, loghost b, network c, hostip d ".
			"WHERE a.loghostid = b.id AND a.networkid = c.id AND a.hostipid = d.id AND d.hostip <> '' ".
				"AND ('*' = ? OR a.typeid = ?) AND ('*' = ? OR b.id = ?) AND ('*' = ? OR c.id = ?) ".
			"GROUP BY a.typeid, b.id, c.id, d.id ".
			"ORDER BY MAX(a.last) DESC");
		$select->bindParam(1, $typeId, PDO::PARAM_STR);
		$select->bindParam(2, $typeId, PDO::PARAM_STR);
		$select->bindParam(3, $loghostId, PDO::PARAM_STR);
		$select->bindParam(4, $loghostId, PDO::PARAM_STR);
		$select->bindParam(5, $networkId, PDO::PARAM_STR);
		$select->bindParam(6, $networkId, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $typeId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghostId, PDO::PARAM_STR);
		$select->bindColumn(3, $loghost, PDO::PARAM_STR);
		$select->bindColumn(4, $networkId, PDO::PARAM_STR);
		$select->bindColumn(5, $network, PDO::PARAM_STR);
		$select->bindColumn(6, $hostipId, PDO::PARAM_STR);
		$select->bindColumn(7, $hostip, PDO::PARAM_STR);
		$select->bindColumn(8, $host, PDO::PARAM_STR);
		$select->bindColumn(9, $countrycode, PDO::PARAM_STR);
		$select->bindColumn(10, $countryname, PDO::PARAM_STR);
		$select->bindColumn(11, $count, PDO::PARAM_INT);
		$select->bindColumn(12, $first, PDO::PARAM_INT);
		$select->bindColumn(13, $last, PDO::PARAM_INT);
		$l12n = $this->l12n();
		$this->beginEventTable(array(
			$l12n->t("Nr"),
			$l12n->t("Status"),
			$l12n->t("Log"),
			$l12n->t("Network"),
			$l12n->t("Host"),
			$l12n->t("Count"),
			$l12n->t("When"),
			$l12n->t("Logs")
		));
		$rowNr = 1;
		$now = time();
		while($select->fetch(PDO::FETCH_BOUND) !== false) {
			$this->beginEventRow();
			$this->printEventRowNr($rowNr);
			$this->printEventType($typeId);
			$this->printEventLoghost($loghost);
			$this->printEventNetwork($network);
			$this->printEventHostip($hostipId, $hostip, $host, $countrycode, $countryname);
			$this->printEventCount($count);
			$this->printEventTimerange($now, $first, $last);
			$this->printEventLogLinks($typeId, $loghostId, $networkId, "*", $hostipId, "*", "*");
			$this->endEventRow();
			$rowNr++;
		}
		$this->endEventTable();
	}

}

?>
