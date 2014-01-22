function clearOptions() {
	var vInputs = [
		'typefilter',
		'loghostfilter',
		'networkfilter',
		'servicefilter'
	];
	var vInputIndex;
	var vInput;

	for(vInputIndex = 0; vInputIndex < vInputs.length; vInputIndex++) {
		vInput = document.getElementsByName(vInputs[vInputIndex]);
		if(vInput.length > 0) {
			vInput[0].value = '*';
		}
	}
	document.getElementsByName('request')[0].submit();
}

function applyOption(pCmd, pInput, pValue) {
	if(pCmd != '*') {
		document.getElementsByName('cmd')[0].value = pCmd;
	}
	document.getElementsByName(pInput)[0].value = pValue;
	document.getElementsByName('request')[0].submit();
}
