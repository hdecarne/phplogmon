function applyOption(pCmd, pInput, pValue) {
	if(pCmd != '*') {
		document.getElementsByName('cmd')[0].value = pCmd;
	}
	document.getElementsByName(pInput)[0].value = pValue;
	document.getElementsByName('request')[0].submit();
}
