<?php
include "top.php";


// SECTION: 1a.
// variables for the classroom purposes to help find errors.



$debug = true;
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
$gender = "Female";
$hiking = false;
$kayaking = false;
$skiing = false;
$mountain = "Camels Hump";



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
    if (!securityCheck(true)) {
        $msg = "<p>Sorry you cannot access this page. ";
        $msg.="Security breach detected and reported</p>";
        die($msg);
    }
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //
    // SECTION: 2b Sanitize (clean) data 

    $email = filter_var($_POST["txtEmail"], FILTER_SANITIZE_EMAIL);
    $dataRecord[] = $email;
    $lastName = htmlentities($_POST["txtLastName"], ENT_QUOTES, "UTF-8");
    $firstName = htmlentities($_POST["txtFirstName"], ENT_QUOTES, "UTF-8");
    $dataRecord[] = $firstName;
    $dataRecord[] = $lastName;
    $gender = htmlentities($_POST["radGender"], ENT_QUOTES, "UTF-8");
    $dataRecord[] = $gender;
    if (isset($_POST["chkHiking"])) {
        $hiking = true;
    } else {
        $hiking = false;
    }
    if (isset($_POST["chkKayaking"])) {
        $kayaking = true;
    } else {
        $kayaking = $kayaking;
    }
    $dataRecord[] = $hiking;

    $mountain = htmlentities($_POST["lstMountains"], ENT_QUOTES, "UTF-8");
    $dataRecord[] = $mountain;

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
    //
    // 
    //
    
      if (!$errorMsg) {
        if ($debug) {
            print "<p>Form is Validish</p>";
        }



        //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        //
        // SECTION: 2e Save Data
        //Robert Erickson makes my life so damn hard and I am sick of it!
        //This code below saves the data to a CSV file
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
        $subject = "Research Study: " . $todaysDate;

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
                <legend>Register Today</legend>
                <p>Please participate in this form. Participation will prove your 
                    commitment to the UVM community. The UVM community is devoted 
                    to increasing diversity, social justice, and making the world 
                    a better place. After you participate, you will receive VIP
                    invitations to our seminars regarding cultural pluralism!! Making
                    the world a better place starts with 2 things: Divestment & 
                    filling out this form.</p>
                <fieldset class="wrapperTwo">
                    <legend>Please complete the following form</legend>

                    <fieldset class="contact">
                        <legend>Contact Information</legend>

                        <label for="txtEmail" class="required">Email
                            <input type="text" id="txtEmail" name="txtEmail"
                                   value="<?php print $email; ?>"
                                   tabindex="120" maxlength="45" placeholder="Enter a valid email address"
    <?php if ($emailERROR) {
        print 'class="mistake"';
    } ?>
                                   onfocus="this.select()" 
                                   autofocus>
                        </label>
                        <label for="txtFirstName" class="required">First Name
                            <input type="text" id="txtFirstName" name="txtFirstName"
                                   value="<?php print $firstName; ?>"
                                   tabindex="100" maxlength="45" placeholder="Enter your first name"
    <?php if ($firstNameERROR) {
        print 'class="mistake"';
    } ?>
                                   onfocus="this.select()"
                                   autofocus>
                        </label>
                        <label  class="required">Last Name
                            <input type="text" id="txtLastName" name="txtLastName" 
                                   value="<?php print $lastName; ?>"
                                   tabindex="100" maxlength="45" placeholder="Enter your last name"
    <?php if ($lastNameERROR) {
        print 'class="mistake"';
    } ?>
                                   onfocus="this.select()"
                                   autofocus>
                        </label>

                    </fieldset> <!-- ends contact -->

                </fieldset> <!-- ends wrapper Two -->
                <fieldset class="radio">
                    <legend>What is your gender?</legend>
                    <label><input type="radio" 
                                  id="radGenderMale" 
                                  name="radGender" 
                                  value="Male"
    <?php if ($gender == "Male") {
        print 'checked';
    } ?>
                                  tabindex="330">Male</label>
                    <label><input type="radio" 
                                  id="radGenderFemale" 
                                  name="radGender" 
                                  value="Female"
    <?php if ($gender == "Female") {
        print 'checked';
    } ?>
                                  tabindex="340">Female</label>
                    <label><input type="radio"
                                  id="radGenderAlien"
                                  name="radGender"
                                  value="Alien"
                                  <?php if ($gender == "Alien") {
                                      print 'checked';
                                  } ?>
                                  tabindex="350">Alien</label>



                </fieldset>
                <fieldset class ="checkbox">
                    <legend>Do you enjoy (check all that apply):</legend>
                    <label><input type="checkbox"
                                  id="chkHiking"
                                  name="chkHiking"
                                  value="Hiking"
    <?php if ($hiking) {
        print 'checked';
    } ?>
                                  tabindex="420">Hiking</label>
                    <label><input type="checkbox"
                                  id="chkKayaking"
                                  name="chkKayaking"
                                  value="Kayaking"
    <?php if ($kayaking) {
        print 'checked';
    } ?>
                                  tabindex="430">Kayaking</label>
                    <label><input type="checkbox"
                                  id="chkSkiing"
                                  name="chkSkiing"
                                  value="Skiing"
    <?php if ($skiing) {
        print 'checked';
    } ?>
                                  tabindex="440">Skiing</label>
                </fieldset>
                <fieldset  class="listbox">	
                    <label for="lstMountains">Favorite Mountain</label>
                    <select id="lstMountains" 
                            name="lstMountains" 
                            tabindex="520" >
                        <option <?php if ($mountain == "HayStack Mountain") {
        print " selected ";
    } ?>
                            value="HayStack Mountain">HayStack Mountain</option>

                        <option <?php if ($mountain == "Camels Hump") {
        print " selected ";
    } ?>
                            value="Camels Hump" 
                            >Camels Hump</option>

                        <option <?php if ($mountain == "Laraway Mountain") {
        print " selected ";
    } ?>
                            value="Laraway Mountain" >Laraway Mountain</option>
                    </select>
                </fieldset>
                <fieldset class="buttons">
                    <legend></legend>
                    <input type="submit" id="btnSubmit" name="btnSubmit" value="Register" tabindex="900" class="button">
                </fieldset> <!-- ends buttons -->

            </fieldset> <!-- Ends Wrapper -->
        </form>



    </article>
    <?php 
    }
    include "footer.php"; ?>

</body>