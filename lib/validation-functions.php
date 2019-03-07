<?php
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//these are series of functions that will supposedly help validate my data. It 
//is important to note that each function will return true or false

function verifyAlphaNum ($testString) {
    // Check for Letters, numbers and dash, period, space and sigle quote only.
    return (preg_match("/^([[:alnum:]]|-|\.| |')+$/", $testString));
}

function verifyEmail ($testString) {
    // Check for a valid email address 
    return filter_var($testString, FILTER_VALIDATE_EMAIL);
}

function verifyNumeric ($testString) {
    // Check for numbers and period.
    return (is_numeric ($testString));
}

function verifyPhone ($testString) {
    //Check for a usa phone number 
 $regex = '/^(?:1(?:[. -])?)?(?:\((?=\d{3}\)))?([2-9]\d{2})(?:(?<=\(\d{3})\))? ?(?:(?<=\d{3})[.-])?([2-9]\d{2})[. -]?(\d{4})(?: (?i:ext)\.? ?(\d{1,5}))?$/';
return (preg_match($regex, $testString));
}
?>
