<!DOCTYPE html>
<html>

<?php
include "cDB.php";
include "survey.php";
$db   = new cDB();
$inst = false;
if(isset($_GET["inst"])) $inst = $_GET["inst"];


function ListInstitutes()
{
    global $db, $inst;
    $insts = $db->GetInstitutes();
    $names = array();
    echo '<select onchange="Nav()" id="Nav">';
    echo '<option value="">  </option>';
    foreach($insts as $i)
    {
        $i = $i['Name'];
        $names[] = $i;
        if($inst == $i )echo "<option value=\"$i\" selected> $i </option>\n";
        else echo "<option value=\"$i\"> $i </option>\n";
    }
    echo "</select>";
    if(!in_array($inst, $names)) $inst = false;
}

function ListQuestions($questions)
{
    echo '<ul class="questions">';
    $counter = 1;
    foreach($questions as $question)
    {
        $q    = $question['Question'];
        $desc = $question['Desc'];
        echo "<li> <b>Q$counter.</b> $q";
        if(strlen($desc) > 0)
        {
            echo '<ul>';
            $lines = explode("\n", $desc);
            foreach($lines as $line) if(strlen($line)) echo "<li>$line</li>";
            echo '</ul>';
        }
        echo " </li>";
        $counter = $counter + 1;
    }
    echo '</ul>';
}

function Survey($inst, $type = 1)
{
    global $db;
    $questions = $db->GetQuestions(false, $type);
    $persons   = $db->GetPersons($inst, $type);

    ListQuestions($questions);

    echo '<table class="survey"><thead><tr>';
    if($type == 1) echo '<th>Person</th>';
    else echo '<th></th>';
    $counter = 1;
    foreach($questions as $question)
    {
        if ( $counter == 9 ) {
            echo "<th></th><th>Last Update</th>";
        } else {
            $title = $question['Title'];
            echo "\n<th>Q$counter<br>$title</th>";
        }
        $counter++;
    }
    echo "</tr></thead><tbody>";

    foreach($persons as $person)
    {
        $NameCMS   = $person['NameCMS'];
        $NamfCMS   = $person['NamfCMS'];
        $ActivName = $person['ActivName'];
        $PersonId  = $person['Id'];
        echo "\n<tr class=\"rows\" id=\"row$PersonId\">";
        echo "<td><b>$NamfCMS $NameCMS</b> <span class=\"AN\">$ActivName</span></td>"; // do not delete b tag. it is used to get name (you can also do better!)
        $questionsP= $db->GetQuestions($PersonId, $type);
        $updateFlag= false;
        $counter = 1;
        foreach($questionsP as $question)
        {
            $html = $question['HTML'];
            if(!$updateFlag) $updateFlag = $question['UpdateFlag'];
            if ($counter < 9) {
                echo "<td>$html</td>";
            } else {
                if(!$updateFlag) {
                    echo "<td> <a class=\"button\" onclick=\"Save('row$PersonId')\" id=\"button$PersonId\"> Save </a></td>";
                } else {
                    echo "<td> <a class=\"button\" onclick=\"Save('row$PersonId')\" id=\"button$PersonId\"> Update </a></td>";
                }
                echo "<td>$html</td>";
            }
            $counter++;
        }
        echo '</tr>';
    }

    if($type == 1)
    {
        echo '<tr class="rows" id="new"> <td>';
        echo '<input type="text" placeholder="First Name" id="fname" style="width:100px;"> <br>';
        echo '<input type="text" placeholder="Last Name" id="lname" style="width:100px"> <br>';
        echo "Institute: <span id=\"institute\">$inst</span>";
        echo '</td>';
        $counter = 1;
        foreach($questions as $question)
        {
            $html = $question['HTML'];
            if(!$updateFlag) $updateFlag = $question['UpdateFlag'];
            if ($counter < 9) {
                echo "<td>$html</td>";
            } else {
                echo '<td> <a class="button" onClick="Add()"> Add New User </a> </td><td></td>';
            }
            $counter++;
        }
        echo '</tr>';
    }
    echo '</tbody></table>';
}

?>

<head>
    <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.10.4.custom.js"></script>
    <script type="text/javascript" src="js/jquery.animate-colors-min.js"></script>
    <script type="text/javascript" src="js/main.js"></script>
    <script type="text/javascript">
    $( document ).ready(function() {
        $("#pass").on('input',PassCheckDyn);
    });
    </script>
    <link rel ="stylesheet" type="text/css" href="img/ss.css" title="style">
    <link href="img/smoothness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
    <title>LPC Survey System</title>
</head>

<body>
<center>
    <div class="top">
    <h1> LPC Survey System <?php if($inst) echo "($inst)"; ?> </h1>
<!--    <div style="width: 100%; text-align: left; margin:10px">Institute: <?php // ListInstitutes(); ?> </div> -->
    </div>
    <div class="content">
    <?php if($inst)
{
?>
    <div style="width:100%">
        <div style="width:55%; float:left;" align="right">
            <input type="hidden" value="<?php echo $inst;?>" id="inst">
            Password: <input type="password" id="pass" style="width:120px">
            <span id="passStatus" style="font-weight: bold; color: #a00; font-size:13pt">&#215;</span>
        </div>
        <div style="width:45%; float:right;" align="right">
            <table>
            <tr>
            <td> <a href="pass.php?inst=<?php echo $inst;?>" class="button" target="_blank" style="width:210px;">Change Institute Password</a> </td>
            <td> <a href="mailto:jjesus@fnal.gov" class="button" style="width:140px;">Forgot password?</a> </td>
            </tr>
            </table>
        </div>
        <div style="clear:both"> </div>
    </div>
    <div class="warning"> Please enter password to update and save  information. <br>
                          <span style="color:#900"><b>Please enter SAVE (or UPDATE) button at the end of each line to save the information.</b></span>
    <!--<?php if($db->IsInitialPass($inst)){ ?> <br> <br> Please update your password to ensure confidentiality of your responses. <?php } ?> --></div> 
<?php
    Survey($inst);
    echo '<br><br><h2> Group Survey </h2>';
    Survey($inst, 2);
}
else echo '';
    ?>
    </div>
    <div class="footer"> <span> jjesus(SpamNot)fnal.gov </span> </div>
</center>
</body>

</html>
