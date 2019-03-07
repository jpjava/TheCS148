<?php
include "top.php";


// SECTION: 1a.
// variables for the classroom purposes to help find errors.



$debug = false;
if (isset($_GET["debug"])) {//only do this in a classroom environment
    $debug = true;
}
if ($debug) {
    print "<p>DEBUG MODE IS ON</p>";
}
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1b Security
//
// define security variable to be used in SECTION 2a.
$yourURL = $domain . $phpSelf;


//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1c form variables
//
// Initialize variables one for each form element
// in the order they appear on the form
$email = "jpappano@uvm.edu";
$firstName = "";
$lastName = "";
$gender = "NO";
$vegan = false;
$grandBuffet = false;
$skiing = false;
$hotelList = "Hotel California";



//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1d form error flags
//
//
//

$emailERROR = false;
$firstNameERROR = false;


//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1e misc variables
//
// my error message
$errorMsg = array();
//this is for the csv file
$dataRecord = array();


//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// SECTION: 2 Process for when the form is submitted
//
if (isset($_POST["btnSubmit"])) {
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
    // SECTION: 2a Security
// 

    if (!securityCheck($path_parts, $yourURL, true)) {
        $msg = "<p>Sorry you cannot access this page. ";
        $msg.="Security breach detected and reported</p>";
        die($msg);
    }


//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
    // SECTION: 2b Sanitize (clean) data 

    $email = filter_var($_POST["txtEmail"], FILTER_SANITIZE_EMAIL);
    $dataRecord[] = $email;
    $firstName = htmlentities($_POST["txtFirstName"], ENT_QUOTES, "UTF-8");
    $dataRecord[] = $firstName;

    $lastName = htmlentities($_POST["txtLastName"], ENT_QUOTES, "UTF-8");
    $dataRecord[] = $lastName;
    $gender = htmlentities($_POST["radGender"], ENT_QUOTES, "UTF-8");
    $dataRecord[] = $gender;


//Initialize: SECTION 1c.
//Sanitize: SECTION 2c.


    $hotelList = htmlentities($_POST["lsthotelList"], ENT_QUOTES, "UTF-8");
    $dataRecord[] = $hotelList;







    $vegan = true;    // checked
    $grandBuffet = false; // not cehcked
    $peanutFree = false;
    print $peanutFree . $grandBuffet;

    if (isset($_POST["chkHiking"])) {
        $hiking = true;
    } else {
        $hiking = false;
    }
    if (isset($_POST["chkKayaking"])) {
        $grandBuffet = true;
    } else {
        $grandBuffet = false;
    }
    if (isset($_POST["chkSkiing"])) {
        $peanutFree = true;
    } else {
        $peanutFree = false;
    }
    $dataRecord[] = $grandBuffet;
    $dataRecord[] = $vegan;
    $dataRecord[] = $peanutFree;

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
    // SECTION: 2c Validation
//
    if ($email == "") {
        $errorMsg[] = "Please enter your email address";
        $emailERROR = true;
    } elseif (!verifyEmail($email)) {
        $errorMsg[] = "Your email address appears to be incorrect.";
        $emailERROR = true;
    }
    if ($firstName == "") {
        $errorMsg[] = "Please enter your first name";
        $firstNameERROR = true;
    } elseif (!verifyAlphaNum($firstName)) {
        $errorMsg[] = "Your first name appears to have extra character.";
        $firstNameERROR = true;
    }

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
    // SECTION: 2d Process Form - Passed Validation
    if (!$errorMsg) {
        print_r($dataRecord);

        if ($debug) {
            print "<p>Form is Validated</p>";
        }



//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
        // SECTION: 2e Save Data
//Robert Erickson makes my life so damn hard and I am sick of it!
//This code below saves the data to a CSV file

        $query = "INSERT INTO tblGuest (fldEmail, fldFirstName, fldLastName, fldPresent,"
                . "fldPeanutFree, fldMealGrandBuffet, fldMeal, fldHotelID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
//('" .$firstName ."')";
        print_r($dataRecord);
        print "<P>sql:" . $query . "<P>";

        $info2 = $thisDatabaseWriter->insert($query, $dataRecord, 0, 0, 0, 0, false, false);

        print "<p>count:" . count($dataRecord);

        $info2 = $thisDatabaseReader->testquery($query, $dataRecord, 0, 0, 0, 0, false, false);


        $fileExt = ".csv";
        $myFileName = "data/registration";
        $filename = $myFileName . $fileExt;
//if ($debug)
//print "\n\n<p>filename is " . $filename;
//this code below opens a file for append
        $file = fopen($filename, 'a');
//write the forms information (whatever the fuck that means...)
        fputcsv($file, $dataRecord);
//WE CANNOT LEAVE THE FILE OPEN!! OH NOOO!! CLOSE THE FILE!!!
        fclose($file);
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
        // SECTION: 2f Create message
//THE FUN BEGINS!!!
//This is where I get to mail a message and send it to whoever filled out the form!
        $message = '<h2>Your information.</h2>';
        foreach ($_POST as $key => $value) {
            $message.= "<p>";
            $camelCase = preg_split('/?=[A-Z])/', substr($key, 3));

            foreach ($camelCase as $one) {
                $message .= $one . " ";
            }
            $message.= " = " . htmlentities($value, ENT_QUOTES, "UTF-8") . "</P>";
        }

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
        // SECTION: 2g Mail to user
//so the message was built in section 2f. and this is the process for mailing
//a message with the form data
        $to = $email; //this goes to the dipshit that actually filled out the form!!!
        $cc = "";
        $bcc = "";
        $from = "No Reply <noreply@jpappano.com>";
        $todaysDate = strftime("%x");
        $subject = "YOUR RSVP RESULTS: " . $todaysDate;

        if ($debug) {
            print $to;
        }

        $mailed = sendMail($to, $cc, $bcc, $from, $subject, $message);
    }
} // ends if form was submitted.
//#############################################################################
//
// SECTION 3 Display Form
//
?>

<article id="main">

    <?php
//####################################
//
// SECTION 3a.
    if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) { // closing of if marked with: end body submit
        print "<h1>Your Request has ";

        if (!$mailed) {
            print "not ";
        }

        print "been processed</h1>";

        print "<p>A copy of this message has ";
        if (!$mailed) {
            print "not ";
        }
        print "been sent</p>";
        print "<p>To: " . $email . "</p>";
        print "<p>Mail Message:</p>";

        print $message;
    } else {


        //####################################
        //
        // SECTION 3b Error Messages
        //
        // display any error messages before we print out the form

        if ($errorMsg) {
            print '<div id="errors">';
            print "<ol>\n";
            foreach ($errorMsg as $err) {
                print "<li>" . $err . "</li>\n";
            }
            print "</ol>\n";
            print '</div>';
        }
        //####################################
        //
        // SECTION 3b Error Messages
        //
        // 
        //####################################
        //
        // SECTION 3c html Form
        //
        /* Display the HTML form. note that the action is to this same page. $phpSelf
          is defined in top.php

         */
        ?>


        <form action= "<?php print $phpSelf; ?>"
              method = "post"
              id="frmRegister"> 


            <fieldset class ="wrapper">
                <legend>RSVP Today</legend>
                <p>Welcome to the wedding party website. Please RSVP!!!</p>
                <fieldset class="wrapperTwo">
                    <legend>Please complete the following form</legend>

                    <fieldset class="contact">
                        <legend>Contact Information</legend>

                        <label for="txtEmail" class="required">Email
                            <input type="text" id="txtEmail" name="txtEmail"
                                   value="<?php print $email; ?>"
                                   tabindex="120" maxlength="45" placeholder="Enter a valid email address"
                                   <?php
                                   if ($emailERROR) {
                                       print 'class="mistake"';
                                   }
                                   ?>
                                   onfocus="this.select()" 
                                   autofocus>
                        </label>
                        <label for="txtFirstName" class="required">First Name
                            <input type="text" id="txtFirstName" name="txtFirstName"
                                   value="<?php print $firstName; ?>"
                                   tabindex="100" maxlength="45" placeholder="Enter your first name"
                                   <?php
                                   if ($firstNameERROR) {
                                       print 'class="mistake"';
                                   }
                                   ?>
                                   onfocus="this.select()"
                                   autofocus>
                        </label>
                        <label  class="required">Last Name
                            <input type="text" id="txtLastName" name="txtLastName" 
                                   value="<?php print $lastName; ?>"
                                   tabindex="100" maxlength="45" placeholder="Enter your last name"
                                   <?php
                                   if ($lastNameERROR) {
                                       print 'class="mistake"';
                                   }
                                   ?>
                                   onfocus="this.select()"
                                   autofocus>
                        </label>

                    </fieldset> <!-- ends contact -->
                </fieldset> <!-- ends wrapper Two -->
                <fieldset class="radio">
                    <legend>Are you coming? </legend>
                    <label><input type="radio"
                                  id="radGenderMale"
                                  name="radGender"
                                  value="NO"
                                  <?php
                                  if ($gender == "NO") {
                                      print 'checked';
                                  }
                                  ?>
                                  tabindex="330">NO</label>
                    <label><input type="radio"
                                  id="radGenderFemale"
                                  name="radGender"
                                  name="radGender"
                                  value="YES"
                                  <?php
                                  if ($gender == "YES") {
                                      print 'checked';
                                  }
                                  ?>
                                  tabindex="340">YES</label>
                    <label><input type="radio"
                                  id="radGenderAlien"
                                  name="radGender"
                                  value="Alien"
                                  <?php
                                  if ($gender == "MAYBE")
                                      print 'checked'
                                      ?>
                                  tabindex="350" > MAYBE </label>



                </fieldset>
                <fieldset class ="checkbox">
                    <legend>What food will you eat?</legend>
                    <label><input type="checkbox"
                                  id="chkVegan"
                                  name="chkVegan"
                                  value="Vegan"
                                  <?php
                                  if ($vegan) {
                                      print 'checked';
                                  }
                                  ?>
                                  tabindex="420">Vegan</label>
                    <label><input type="checkbox"
                                  id="chkGrandBuffet"
                                  name="chkGrandBuffet"
                                  value="Grand Buffet"
                                  <?php
                                  if ($grandBuffet) {
                                      print 'checked';
                                  }
                                  ?>
                                  tabindex="430">Grand Buffet</label>
                    <label><input type="checkbox"
                                  id="chkPeanutFree"
                                  name="chkPeanutFree"
                                  value="Peanut-free & gluten-free"
                                  <?php
                                  if ($peanutFree) {
                                      print 'checked';
                                  }
                                  ?>
                                  tabindex="440">Peanut-free & gluten-free</label>
                </fieldset>
                <?php
                $hotelList = "Hotel California";
                require_once('../bin/Database.php');
                $dbUserName = get_current_user() . '_reader';

                $whichPass = "r";

                $dbName = strtoupper(get_current_user()) . '_FinalProject';

                $thisDatabase = new Database($dbUserName, $whichPass, $dbName);

                $query = "SELECT DISTINCT fldHotelName ";
                $query .= "FROM tblHotel ";
                $query .= "ORDER BY fldHotelName";
                //So I am going to user RObert Erickson's select function with
                //my own variable
                // $hotelList = htmlentities($_POST["lsthotelList"], ENT_QUOTES, "UTF-8");
                //$dataRecord[] = $hotelList;
                $hotelLists = $thisDatabase->select($query, "", 0, 1, 0, 0, false, false);

                print "<h2>List box built from Database</h2>";

                print '<label for ="lstHotelName">What Hotel Will you by staying at?? ';
                print '<select id = "lstHotelName"';
                print '     name = "lstHotelName"';
                print '     tabindex="300">';

                foreach ($hotelLists as $row) {
                    print '<option ';
                    if ($hotelLists == $row["fldHotelName"])
                        print " selected='selected' ";

                    print 'value ="' . $row["fldHotelName"] . '">' . $row["fldHotelName"];

                    print '</option>';
                }
                print '</select></label>';
                print '</form>';
                ?>
                <!--
                
                                <fieldset class = "listbox">
                                    <label for = "lsthotelList">Hotel You are staying at</label>
                                    <select id = "lsthotelList"
                                            name = "lsthotelList"
                                            tabindex = "520" >
                                        <option <?php
            if ($hotelList == "Hotel California") {
                print " selected ";
            }
                ?>
                                            value="Hotel California">Hotel California</option>
                
                                        <option <?php
            if ($hotelList == "Bates Motel") {
                print " selected ";
            }
                ?>
                                            value="Bates Motel"
                                            >Bates Motel</option>
                
                                        <option <?php
            if ($hotelList == "Robert's Bed & Breakfast") {
                print " selected ";
            }
                ?>
                                            value="Robert's Bed & Breakfast"
                                            >Robert's Bed & Breakfast</option>
                
                                        <fieldset class="buttons">
                                            <legend></legend>-->
                <input type="submit" id="btnSubmit" name="btnSubmit" value="Register" tabindex="900" class="button">
            </fieldset> <!-- ends buttons -->

            </fieldset> <!-- Ends Wrapper -->
        </form>



    </article>
    <?php
}
include "footer.php";
?>

</body>