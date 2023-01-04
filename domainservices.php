<?php
if( empty(session_id()) && !headers_sent()){
  session_start();
}
      
/*use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;*/
require ('assets/libraries/Exception.php');
require ('assets/libraries/PHPMailer.php');
require 'assets/libraries/SMTP.php';


global $brandname,$brandurl,
        $rootpath, $rulespath, $assetspath, 
        $page_rules, $defaultimage, 
        $page_data, $districtsinfo, $taluksinfo, 
        $physical_output_folder, $villagesinfo,$selectdefaultvlue,$g_language;

$brandname = "SamakalaNigalvugal";
$brandurl = "SamakalaNigalvugal.com";

if(isset($_POST['datarequestedby']) && $_POST['datarequestedby'] == 'generatagrievance'){
  $pdfname = downloadpdf();
}
else if (isset($_POST['datarequestedfrom'])) {
    return processrequest();
}
else if (isset($_POST['datarequestedby']) && $_POST['datarequestedby'] == 'deletefile') {
    return deletefile($_POST['filename']);
}

//fetch defaultvalues
function getdefaultvalues()
{
    global $rootpath, $rulespath, $assetspath,$datapath,$page_data;
    $rootpath = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']).'/';
    
    if(!isset($_SESSION["defaultvalues"]) || $_SESSION["defaultvalues"] == null)
    {
      $default_file = $rootpath.'/defaultvalues.json';
      $_SESSION["defaultvalues"] = json_decode(file_get_contents($default_file),true);
    }
    
    
    $assetspath = $rootpath .$_SESSION["defaultvalues"]['assetspath'];
    $rulespath = $rootpath .$_SESSION["defaultvalues"]['rulespath'];
    $datapath = $rootpath .$_SESSION["defaultvalues"]['datapath'];
    $page_rules = $datapath ."pages.json";
    $defaultimage = "assets/images/default.png";
    $page_data = json_decode(file_get_contents($page_rules),true);
}


function downloadpdf()
{
      global $rootpath, $rulespath, $assetspath,$datapath;
      getdefaultvalues();
      $physical_output_folder = $rootpath  .'/outputdocs/';
      
      $language = $_POST['language'];
      
      $header = '';
      $toname = ''; 
      $streetno = ''; 
      $streetname = '';
      $city = '';
      $postal = '';
      $district = '';
      $postalcode = '';
      $header ='';
      $fromheader = $_SESSION["defaultvalues"][$language .'fromheader'];
      $toheader = $_SESSION["defaultvalues"][$language .'toheader'];
      $iyyaheader = $_SESSION["defaultvalues"][$language .'iyyaheader'];
      $subjectheader = $_SESSION["defaultvalues"][$language .'subjectheader'];
      $subject ='';
      $placeheader = $_SESSION["defaultvalues"][$language .'placeheader'];
      $dateheader = $_SESSION["defaultvalues"][$language .'dateheader'];
      $currentdate ='';
      $ippadikkuheader = $_SESSION["defaultvalues"][$language .'ippadikkuheader'];
      $url = '';
      $grievance_contents_file_name = '';
      $fontfamily = '';
      $fontfamily_filename = '';

      $grievance_file_name = $datapath .$language. '/' . $language. 'grievancedata.json'; 
      $grievance_file_data =  json_decode(file_get_contents($grievance_file_name),true);
                          
      for($i=0; $i< count($grievance_file_data); $i++)
      { 
          if($grievance_file_data[$i]['grievanceid'] == $_POST['grievanceid'] )
          {
              if(isset($grievance_file_data[$i]['addresses']))
              {
                  $addresskey = $_POST['state'].$_POST['language'].$_POST['district'].$_POST['taluk'].$_POST['village'];
                  $address = $grievance_file_data[$i]['addresses'];
                  if(isset($address[0]))
                  { 
                      foreach($address[0] as $json_key => $json_value)       
                      {
                          if($json_key == $addresskey)
                          {
                              $value = $json_value[0];
                              if(isset($value['header']))$header = $value['header'];
                              if(isset($value['grievance_contents_file_name'])) $grievance_contents_file_name = $value['grievance_contents_file_name'];
                              if(isset($value['subject'])) $subject = $value['subject'];
                              if(isset($value['toname'])) $toname = $value['toname'];
                              if(isset($value['streetno'])) $streetno = $value['streetno'];
                              if(isset($value['streetname'])) $streetname = $value['streetname'];
                              if(isset($value['city'])) $city = $value['city'];
                              if(isset($value['postal'])) $postal = $value['postal'];
                              if(isset($value['district']))$district = $value['district'];
                              if(isset($value['postalcode']))$postalcode = $value['postalcode'];
                          }
                      } 
                  }

                  $template_file_name = $rulespath .'templates/' .'pettition.html';
                  $page = file_get_contents($template_file_name);
                  
                  $fromaddress = $_POST['fromname'].',<br>' .$_POST['fromhousenumber'] .', ' .$_POST['fromhousename'].',<br>' ;
                  $fromaddress .= $_POST['fromcity'] .', ' .$_POST['fromvillagename'].',<br>' ;
                  $fromaddress .= $_POST['frompostalname'] .', ' .$_POST['districtname'].',<br>' ;
                  $fromaddress .= $_POST['fromstatename'] .' - ' .$_POST['frompostalcode'].',<br><br>' ;
                  
                  $toaddress = $toname .', <br>' .$streetno.',<br>' .', ' .$streetname.',<br>' ;
                  $toaddress .= $city .', ' .$postal.',<br>' ;
                  $toaddress .= $district .', '. $postalcode .' - ' .$postalcode.',<br><br>' ;
                  $page = str_replace('{title}', $header, $page);
                  $page = str_replace('{fromheader}', $fromheader, $page);
                  $page = str_replace('{fromaddress}', $fromaddress, $page);
                  $page = str_replace('{toheader}', $toheader, $page);
                  $page = str_replace('{toaddress}', $toaddress, $page);
                  $page = str_replace('{iyyaheader}', $iyyaheader, $page);
                  $page = str_replace('{subject}', $subjectheader . ' : ' .$subject, $page);
             
                  if($grievance_contents_file_name == '')
                  {
                    $grievance_contents = $grievance_contents_file_name;//'No contents found.';
                  }
                  else{
                    $grievance_contents = file_get_contents($datapath .$grievance_contents_file_name);
                  }

                  $page = str_replace('{maincontent}', $grievance_contents, $page);
                  $page = str_replace('{placedata}', $placeheader . ' : ' . $_POST['fromvillagename'], $page);
                  $page = str_replace('{datedata}', $dateheader . ' : ' .date('d-m-Y'), $page);
                  $page = str_replace('{ippadikkuheader}', $ippadikkuheader, $page);
                  $page = str_replace('{ippadikkuname}', $_POST['fromname'], $page);

                  $output_file = $_POST['grievanceid'] .'_' .$_POST['mobilenumber'] . '_' . date("Ymdhis") ;
                  $url.= $_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) .'/'."outputdocs/" .$output_file;
                  $pdf_file_name =  $physical_output_folder .$output_file.'.html';

                  //file_put_contents($pdf_file_name, $page);
                  print_r (custompdf($page, 1));
              }
              else{
                global $g_nodatafoundvalue;
                print_r (custompdf($g_nodatafoundvalue, 0));
              }
          }
      }
  } 

  function custompdf($page, $status)
  {
    
    $g_language = $_POST['language'];
    //fetch default values
    $default_data = getdefaultvalues();
    $popupthankyou = $_SESSION["defaultvalues"][$g_language.'popupthankyou'];
    $popupdownload = $_SESSION["defaultvalues"][$g_language.'popupdownload'];
    $popuperror = $_SESSION["defaultvalues"][$g_language.'popuperror'];

    $retrun_data = '<div class="popupcontent">
                <span parentcontrol="popup" class="popuperrormessageclose">X</span>
                <p class="popuptext">' .$popupthankyou .'</p>
                <p> 
                  <a  onclick="printDiv()" 
                  class="popuplink" 
                  style="display:none" 
                  parentcontrol="popup" 
                  target="_blank" 
                  shoulddeletethefile="1" >' . $popupdownload . '</a>
                </p>
                <p  style="display:none" class="popuperrormessage">'
                .$popupdownload .'</p></div>';
    $array = array(
      "data" => $page,
      "status" => "$status",
      "popup" => "$retrun_data"
    );
    
    return json_encode($array);
  }
  
    
    /*  // the message
    $msg = "First line of text\nSecond line of text";

    // use wordwrap() if lines are longer than 70 characters
    $msg = wordwrap($msg,70);

    // send email
    //mail("samakalanigalvugal.gmail.com","My subject",$msg);

    $to = "xyz@somedomain.com";
         $subject = "This is subject";
         
         $message = "<b>This is HTML message.</b>";
         $message .= "<h1>This is headline.</h1>";
         
         $header = "From:abc@somedomain.com \r\n";
         $header .= "Cc:afgh@somedomain.com \r\n";
         $header .= "MIME-Version: 1.0\r\n";
         $header .= "Content-type: text/html\r\n";

         ini_set("SMTP","localhost");
   ini_set("smtp_port","25");
   ini_set("sendmail_from","samakalanigalvugal.gmail.com");
   ini_set("sendmail_path", "C:\xampp\sendmail\sendmail.exe -t");
         
         $retval = mail ("samakalanigalvugal.gmail.com","My subject",$msg);
         
         if( $retval == true ) {
            echo "Message sent successfully...";
         }else {
            echo "Message could not be sent...";
         }*/

    
    //sendemail();


  function sendemail($subject, $fileAttachment)
  {

    $email_file = 'assets/rules/data/emailcontents.json';    
    $email_content = json_decode(file_get_contents($email_file),true);

    $fromaddress           = $email_content['sendmail_from'];
    $fromfullname           = $email_content['sendmail_name'];
    $toAddress           = $_POST['emailaddress'];
    $sendername           = $_POST['fromname'];
    $pathInfo       = pathinfo($fileAttachment);
    $attchmentName  = $subject;
    $bcc = $email_content['bcc'] ;
   
    $attachment    = chunk_split(base64_encode(file_get_contents($fileAttachment)));
    $boundary      = "PHP-mixed-".md5(time());
    $boundWithPre  = "\n--".$boundary;
   
    $headers   = "From: $fromaddress \r\n Bcc: $bcc \r\n";
    $headers  .= "Reply-To: $fromaddress \r\n";
    $headers  .= "Content-Type: multipart/mixed; boundary=\"".$boundary."\"";
   
    $message   = $boundWithPre;
    $message  .= "\n Content-Type: text/plain; charset=UTF-8\n";
    //$message  .= "\n $mailMessage";
   
    $message .= $boundWithPre;
    $message .= "\nContent-Type: application/octet-stream; name=\"".$subject."\"";
    $message .= "\nContent-Transfer-Encoding: base64\n";
    $message .= "\nContent-Disposition: attachment\n";
    $message .= $attachment;
    $message .= $boundWithPre."--";


    //PHPMailer Object
$mail = new PHPMailer(true); //Argument true in constructor enables exceptions

//From email address and name
$mail->From = $fromaddress;
$mail->FromName = $fromfullname;

//To address and name
$mail->addAddress($toAddress, $sendername);
//$mail->addAddress("recepient1@example.com"); //Recipient name is optional

//Address to which recipient will reply
$mail->addReplyTo("reply@yourdomain.com", "Reply");

//CC and BCC
$mail->addCC($bcc);
$mail->addBCC($bcc);

//Send HTML or Plain Text email
$mail->isHTML(true);

$mail->Subject = $subject;
$mail->Body = "<i>Mail body in HTML</i>";
$mail->AltBody = "This is the plain text version of the email content";

try {
    $mail->send();
    echo "Message has been sent successfully";
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}

    /*ini_set("SMTP", $email_content['smtp']);
    ini_set("smtp_port",$email_content['smtp_port']);
    ini_set("sendmail_from", $email_content['sendmail_from']);
    ini_set("sendmail_path", $email_content['sendmail_path']);
    ini_set("auth_username",$email_content['auth_username']);
    ini_set("auth_password",$email_content['auth_password']);
   
    $status = mail($toAddress, $subject, $message, $headers);*/

    /*ini_set("SMTP","localhost");
    ini_set("smtp_port","25");
    ini_set("sendmail_from","samakalanigalvugal@gmail.com");
    ini_set("sendmail_path", "C:\xampp\sendmail\sendmail.exe -t");
    ini_set("auth_username","samakalanigalvugal@gmail.com");
    ini_set("auth_password","#password11#");
         
    $retval = mail ("samakalanigalvugal@gmail.com","My subject","Message sent successfully...");
    
    if( $retval == true ) {
      echo "";
    }
    else {
      echo "";
    }

    /*$mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Mailer = "smtp";

    $mail->SMTPDebug  = 0;  
    $mail->SMTPAuth   = TRUE;
    $mail->SMTPSecure = "tls";
    $mail->Port       = 465;
    $mail->Host       = "smtp.gmail.com";
    $mail->Username   = "samakalanigalvugal@gmail.com";
    $mail->Password   = "#password11#";
    $mail->IsHTML(true);
    $mail->AddAddress("samakalanigalvugal@gmail.com", "recipient-name");
    $mail->SetFrom("samakalanigalvugal@gmail.com", "from-name");
    $mail->AddReplyTo("samakalanigalvugal@gmail.com", "reply-to-name");
    $mail->AddCC("samakalanigalvugal@gmail.com", "cc-recipient-name");
    $mail->Subject = "Test is Test Email sent via Gmail SMTP Server using PHP Mailer";
    $content = "<b>This is a Test Email sent via Gmail SMTP Server using PHP mailer class.</b>";
    $mail->MsgHTML($content); 
    if(!$mail->Send()) {
      echo "Error while sending Email.";
      var_dump($mail);
    } else {
      echo "Email sent successfully";
    }*/
  }  
  
  function deletefile($filename){
    $physical_output_folder = $_SERVER['DOCUMENT_ROOT'] .dirname($_SERVER['PHP_SELF']) .'/outputdocs/';
     $status = unlink($physical_output_folder .$filename);
  }

  function processrequest()
  {
    global $rootpath, $rulespath, $assetspath,$datapath;
    global $page_data;
    $g_language = $_POST['language'];

    if($_POST['datarequestedfrom'] == 'grievancegenerator'){
      switch($_POST['datarequestedby'])
      {
        case 'ddllanguage':
        {
          $g_language = $_POST['language'];
          $returndata = getgrievancepage();
          print_r($returndata);
          break;
        }
        case 'ddlstate':
        {
          $returndata = getcontrollist();
          //$returndata = getdistrictslist($_POST['datarequestedfrom'], $_POST['state']);
          print_r($returndata);
          break;
        }
        case 'ddldistrict':
        {
          //$returndata = gettalukslist($_POST['datarequestedfrom'], $_POST['state'],$_POST['district']);
          $returndata = getcontrollist();
          print_r($returndata);
          break;
        }
        case 'ddltaluk':
        {
          //$returndata = getvillageslist($_POST['datarequestedfrom'], $_POST['state'],$_POST['district'],$_POST['taluk']);
          $returndata = getcontrollist();
          print_r($returndata);
          break;
        }
        case 'ddlvillage':
        {
          //$returndata = getofficeslist($_POST['datarequestedfrom'], $_POST['state'],$_POST['district'],$_POST['taluk'],$_POST['village']);
          $returndata = getcontrollist();
          print_r($returndata);
          break;
        }
      }
    }
  }

  function getcontrollist()
  {

    getdefaultvalues();

    global $rootpath, $rulespath, $assetspath,$datapath;
    $prefixkeywithfile = '';
    if(isset($_POST['prefixkeywithfile']) && isset($_POST[$_POST['prefixkeywithfile']]))
    {
      $prefixkeywithfile = $_POST[$_POST['prefixkeywithfile']];
    }
    $fieldid = $_POST['childfilecontentid'];
    $fieldname = $_POST['childfilecontentname'];
    $hasdata = false; 
    $language = $_POST['language'];
    $alreadydefultset = false;

    $contentfile = $datapath . $language. '/'. $language.$prefixkeywithfile.$_POST['childcontrolexternalfile'] .'.json';
    $file_data = json_decode(file_get_contents($contentfile),true); 
    $local_data = '';
    
    for($i=0; $i< count($file_data); $i++)
    { 
        if(isset($file_data[$i][$fieldid]) && 
        isset($file_data[$i][$fieldname]))
        { 
          $hasdata = true;
          if($alreadydefultset == false)
          { 
            $alreadydefultset = true;
            $local_data .= '<option value="' . $_SESSION["defaultvalues"]['defaultid'] .'" selected>' . $_SESSION["defaultvalues"][$language .'defaultvalue'] .'</option>';  
          }
          $local_data .= '<option value="' . $file_data[$i][$fieldid] .'">' . $file_data[$i][$fieldname] .'</option>';
        }
    }
    if($hasdata === false ){ echo 1;
      $local_data = '<option id="'. $_SESSION["defaultvalues"]['nodatafoundid'] .'" selected>' .$_SESSION["defaultvalues"][$language .'nodatafoundvalue'] .'</option>';
    }

    return $local_data ;
  }

  function contentfilename($pageid){
    for($i=0; $i< count($page_data); $i++)
    { 
      if($page_data[$i]['pageid'] == $pageid)
      {
        return $page_data[$i]['pagecontent'];
      }
    }     
  }
   
  function getgrievancepage()
  {
      global $rootpath, $rulespath, $assetspath,$datapath;
      getdefaultvalues();

    
      $pageid = $_POST['datarequestedfrom'];
      $language = $_POST['language'];

      $local_data = '';
      $pagename = 'grievancegenerator';
      $contentfile = $rulespath.'controls/'.$language.'/'.$language.'grievancecontrols.json';

      $grievance_data = json_decode(file_get_contents($contentfile),true);
      $hasdata = false;
      $alreadydefultset = false;
                                
      for($i=0; $i< count($grievance_data); $i++) //for 1
      {  
              $grievance_controls = $grievance_data[$i]['Controls'];
              for($j=0; $j< count($grievance_controls); $j++) //for 2
              {
                  if((isset($grievance_controls[$j]['display']) && 
                    $grievance_controls[$j]['display'] == '1')) //if 2
                  { 
                      $local_data .= '<div>';

                      if(isset($grievance_controls[$j]['controltype']) &&
                         $grievance_controls[$j]['controltype'] != 'button')
                      {
                        $local_data .= '<p><i>' . $grievance_controls[$j]['title'] .':</i></p>';
                      }
                      if($grievance_controls[$j]['controltype'] == 'dropdown')
                      { 
                          $hasdata = false;
                          $local_data .= 
                                  '<select pagename="'. $pagename .'" class="grievancegeneratordata"' 
                                  .'  required="' . $grievance_controls[$j]['required'] .'"'
                                  .' id="' . $grievance_controls[$j]['controlid'] .'"';

                          if(isset($grievance_controls[$j]['haschild']) &&
                              $grievance_controls[$j]['haschild'] == "1") 
                          {
                            if((isset($grievance_controls[$j]['childcontrolexternalfile']))) {
                              $local_data .= ' childcontrolexternalfile ="' .$grievance_controls[$j]['childcontrolexternalfile'] .'"';
                            }
                            if((isset($grievance_controls[$j]['childcontrolid']))) {
                              $local_data .= ' childcontrolid ="' .$grievance_controls[$j]['childcontrolid'] .'"';
                            }
                            if((isset($grievance_controls[$j]['childfilecontentid']))) {
                              $local_data .= ' childfilecontentid ="' .$grievance_controls[$j]['childfilecontentid'] .'"';
                            }
                            if((isset($grievance_controls[$j]['childfilecontentname']))) {
                              $local_data .= ' childfilecontentname ="' .$grievance_controls[$j]['childfilecontentname'] .'"';
                            }
                            if((isset($grievance_controls[$j]['prefixkeywithfile']))) {
                              $local_data .= ' prefixkeywithfile ="' .$grievance_controls[$j]['prefixkeywithfile'] .'"';
                            }
                          }  
                          $local_data .= '>'; //end close for select element/
                          
                          if(isset($grievance_controls[$j]['controldatatype']))
                          {
                              if($grievance_controls[$j]['controldatatype'] == 'text')
                              {
                                if(isset($grievance_controls[$j]['controldata']))
                                {
                                  $hasdata = true;
                                  foreach($grievance_controls[$j]['controldata'] as $json_key => $json_value)       
                                  {
                                    $local_data .= '<option value=' . $json_key .'>' . $json_value .'</option>';
                                  } 
                                }
                              }               
                              
                              else if($grievance_controls[$j]['controldatatype'] == 'file')
                              {
                                
                                $file_name = $datapath.$language .'/'.$language.$grievance_controls[$j]['controldatafile'];
                                $file_data = json_decode(file_get_contents($file_name),true);
                                $filecontentid = $grievance_controls[$j]['filecontentid'];
                                $filecontentname = $grievance_controls[$j]['filecontentname'];
                                $alreadydefultset = false;
                                for($l=0; $l< count($file_data); $l++)
                                { 
                                  $hasdata = true;
                                  if($alreadydefultset == false)
                                  {
                                    $alreadydefultset = true;
                                    $local_data .= '<option value="' . $_SESSION["defaultvalues"]['defaultid'] .'" selected>' . $_SESSION["defaultvalues"][$language .'defaultvalue'] .'</option>';  
                                  }
                                  $local_data .= '<option value="' . $file_data[$l][$filecontentid] .'">'. $file_data[$l][$filecontentname] .'</option>';
                                }
                              }
                          }
                          else
                          {
                            if($hasdata === false ){
                              $local_data .= '<option id="'. $_SESSION["defaultvalues"]['nodatafoundid'] .'" selected>' .$_SESSION["defaultvalues"][$language .'nodatafoundvalue'] .'</option>';
                            }
                          }
                          $local_data .= '</select>';
                          if($grievance_controls[$j]['required']  == true )
                          {
                            $local_data .= 
                                  '<div class="outererrormessage" style="display:none;" id="' . $grievance_controls[$j]['controlid'] .'errormessage">'. $grievance_controls[$j]['errormsg'] . '</div>';
                          }
                      }
                      else if($grievance_controls[$j]['controltype'] == 'input')
                      {
                          $local_data .='<input class="input ' .$pagename .'data" type="text" required="' . $grievance_controls[$j]['required'] .'" errmsg="' . $grievance_controls[$j]['errormsg'] .'" id="' . $grievance_controls[$j]['controlid'] .'">';
                          if($grievance_controls[$j]['required']  == true )
                          {
                                $local_data .= 
                                      '<div class="outererrormessage" style="display:none;" id="' . $grievance_controls[$j]['controlid'] .'errormessage">'. $grievance_controls[$j]['errormsg'] . '</div>';
                          } 
                      } 
                      else if($grievance_controls[$j]['controltype'] == 'button')
                      {
                        $local_data .= '<div class="button black section" id="' . $grievance_controls[$j]['controlid'] .'" pageid="' .$pagename .'">'
                                        . $grievance_controls[$j]['title']
                                        .'</div>';
                      }
                      $local_data .= '</div>';
                  }//if 2
              }//for 2
      }//for 1
      return $local_data;
  }
              /*$page_data = json_decode($grievance[$i]['Controls'],true);
              for($i=0; $i< count($page_data); $i++)
              {  
                  if((isset($page_data[$i]['display']) && $page_data[$i]['display'] == '1') ||
                            !isset($page_data[$i]['display']))
                  {
                      $local_data .= '<div>';

                      if($page_data[$i]['controltype'] != 'button')
                      {
                        $local_data .= '<p><i>' . $page_data[$i]['title'] .':</i></p>';
                      }
                      if($page_data[$i]['controltype'] == 'dropdown')
                      {
                        
                          $local_data .= 
                                  '<select pagename="'. $pagename .'" class="grievancegeneratordata" required=' . $page_data[$i]['required'] .' id="' . $page_data[$i]['controlid'] .'">';
                          
                          if(isset($page_data[$i]['controldatatype'] ) )
                          {
                              if($page_data[$i]['controldatatype'] == 'text')
                              {
                                foreach($page_data[$i]['controldata'] as $json_key => $json_value)       
                                {
                                  $local_data .= '<option value=' . $json_key .'>' . $json_value .'</option>';
                                } 
                              }               
                              
                              else if($page_data[$i]['controldatatype'] == 'file'){
                                $filename = $rootpath .$page_data[$i]['controldata'];
                                $file_data = json_decode(file_get_contents($filename),true);
                                $local_data .= '<option id="select">--- Select ---</option>';
                                for($k=0; $k < count($file_data); $k++)
                                { 
                                  if(isset($page_data[$i]['filecontentid']) && isset($page_data[$i]['filecontentname'] ))
                                  {
                                    $local_data .= '<option value=' . $file_data[$k][$page_data[$i]['filecontentid']] .'>' . $file_data[$k][$page_data[$i]['filecontentname'] ] ;'</option>';
                                  }
                                }
                              }
                          }
                          else if($page_data[$i]['controltype'] == 'file')
                          {
                                
                          }
                          $local_data .= '</select>';
                          if($page_data[$i]['required']  == true ){
                            $local_data .= 
                                  '<div class="outererrormessage" style="display:none;" id="' . $page_data[$i]['controlid'] .'errormessage">'. $page_data[$i]['errormsg'] . '</div>';
                          } 
                      }
                      else if($page_data[$i]['controltype'] == 'input'){
                          $local_data .='<input class="input ' .$pagename .'data" type="text" required=' . $page_data[$i]['required'] .' errmsg=' . $page_data[$i]['errormsg'] .' id="' . $page_data[$i]['controlid'] .'">';
                          if($page_data[$i]['required']  == true ){
                            $local_data .= 
                                  '<div class="outererrormessage" style="display:none;" id="' . $page_data[$i]['controlid'] .'errormessage">'. $page_data[$i]['errormsg'] . '</div>';
                        }                
                    }
                  else if($page_data[$i]['controltype'] == 'button'){
                    $local_data .= '<div class="button black section" id="' . $page_data[$i]['controlid'] .'" pageid="' .$pagename .'">'
                                    . $page_data[$i]['title']
                                    .'</div>';
                  }
                  $local_data .= '</div>';
                }
            }
          }
      }

      $file_name = $rootpath ."data/grievancelists.json";
      $json_data = json_decode(file_get_contents($file_name),true);
      $hasdata = false;
      $g_defaultid ='';
      $g_defaultvalue ='';

      for($i=0; $i< count($json_data); $i++)
      { 
        if(isset($json_data[$i]['nodatafound']))
        {
          $g_defaultid = $json_data[$i]['grievanceid'];
          $g_defaultvalue = $json_data[$i][$language.'grievancename'];
        }
        if(isset($json_data[$i]['grievanceid']))
        {
          $grievance_names = $json_data[$i]["grievancename"];
          foreach($grievance_names as $json_key => $json_value)       
          { 
              if($json_key == $language)
              {
                $hasdata = true;
                $local_data .= '<option value="' . $json_data[$i]['grievanceid'] .'">'. $json_value .'</option>';
              }
          }
        }
      }
      if($hasdata === false ){
        $local_data = '<option id="'. $g_defaultid.'">' .$g_defaultvalue .'</option>';
      }
      return $local_data ;*/
  function getstatecontents()
  {
    global $rootpath, $rulespath, $assetspath,$datapath;
    $contentfile = 'assets/rules/data/states.json';    
    return  json_decode(file_get_contents($contentfile),true);
  } 
  function getdistrictslist($pageid, $state)
  {
    global $rootpath, $rulespath, $assetspath,$datapath;
    global $g_language;

    getdefaultvalues();

    $hasdata = false;
    $alreadydefultset = false;
    $contentfile = 'assets/rules/'. $_POST['childcontrolexternalfile'] .'.json';    
    $state_data = json_decode(file_get_contents($contentfile),true);
    $local_data = '';


    for($i=0; $i< count($state_data); $i++)
    { 
      if(isset($state_data[$i]['stateid']) && $state_data[$i]['stateid'] == $state)
      {
        if(isset($state_data[$i]['districts']))
        {
          $_SESSION["districtsinfo"] = $state_data[$i]['districts'];
          //$local_data .= '<option value="select">--- Select ---</option>';
          $districtsinfo = $_SESSION["districtsinfo"];
          
          for($k=0; $k < count($districtsinfo); $k++)
          { 
            if(isset($districtsinfo[$k]['districtid']) && 
            isset($districtsinfo[$k][$g_language.'districtname']))
            { 
              $hasdata = true;
              if($alreadydefultset == false)
              { 
                $alreadydefultset = true;
                $local_data .= '<option value="' . $_SESSION["defaultvalues"][$language .'defaultid'] .'" selected>' . $_SESSION["defaultvalues"][$language .'defaultvalue'] .'</option>';  
              }
              $local_data .= '<option value="' . $districtsinfo[$k]['districtid'] .'">' . $districtsinfo[$k]['districtname'] .'</option>';
            }
          }
        } 
      }
    }
    if($hasdata === false ){
      $local_data = '<option id="'. $_SESSION["defaultvalues"][$language .'nodatafoundid'] .'" selected>' .$_SESSION["defaultvalues"][$language .'nodatafoundvalue'] .'</option>';
    }

    return $local_data ;
  }

  function gettalukslist($pageid, $state, $district)
  {
    global $rootpath, $rulespath, $assetspath,$datapath;
    global $g_language;
    $local_data = '';
    $districtsinfo = $_SESSION["districtsinfo"];
    $alreadydefultset = false;
    $hasdata = false;

    getdefaultvalues();
    
    if(count($districtsinfo) > 0)
    {
      for($i=0; $i< count($districtsinfo); $i++)
      { 
        if($districtsinfo[$i]['districtid'] ==  $district && isset($districtsinfo[$i]['taluks']))
        {
          $_SESSION['talukinfo'] = $districtsinfo[$i]['taluks'];       
          for($k=0; $k < count($_SESSION['talukinfo']); $k++)
          { 
            
            if(isset($_SESSION['talukinfo'][$k]['talukid']) && 
            isset($_SESSION['talukinfo'][$k][$g_language.'talukname']))
            {
              $hasdata = true;
              if($alreadydefultset == false)
              { 
                $alreadydefultset = true;
                $local_data .= '<option value="' . $_SESSION["defaultvalues"][$language .'defaultid'] .'" selected>' . $_SESSION["defaultvalues"][$language .'defaultvalue'] .'</option>';  
              }
                $local_data .= '<option value=' . $_SESSION['talukinfo'][$k]['talukid'] .'>' . $_SESSION['talukinfo'][$k][$g_language.'talukname'] .'</option>';
            }
          }
        } 
      }
    }
    if($hasdata === false ){
      $local_data = '<option id="'. $_SESSION["defaultvalues"][$language .'nodatafoundid'] .'" selected>' .$_SESSION["defaultvalues"][$language .'nodatafoundvalue'] .'</option>';
    }
    return $local_data ;
  }
  
  function getvillageslist($pageid, $state,$district,$taluk)
  {
    global $rootpath, $rulespath, $assetspath,$datapath;
    global $g_language;

    getdefaultvalues();
    $alreadydefultset = false;
    $hasdata = false;
    $local_data = '';
    $talukinfo = $_SESSION['talukinfo'];

    if(count($talukinfo) > 0)
    {
      for($i=0; $i< count($talukinfo); $i++)
      { 
        if($talukinfo[$i]['talukid'] ==  $taluk && isset($talukinfo[$i]['villages']))
        {
            $_SESSION['villageinfo'] = $talukinfo[$i]['villages'];
            $villageinfo =  $_SESSION['villageinfo'];
            for($k=0; $k < count($villageinfo); $k++)
            { 
              if(isset($villageinfo[$k]['villageid']) && isset($villageinfo[$k][$g_language.'villagename']))
              {
                $hasdata = true;
                if($alreadydefultset == false)
                { 
                  $alreadydefultset = true;
                  $local_data .= '<option value="' . $_SESSION["defaultvalues"][$language .'defaultid'] .'" selected>' . $_SESSION["defaultvalues"][$language .'defaultvalue'] .'</option>';  
                }
                  $local_data .= '<option value=' . $villageinfo[$k]['villageid'] .'>' . $villageinfo[$k][$g_language .'villagename'] .'</option>';
              }
            }
        } 
      }
    }
    if($hasdata === false ){
      $local_data = '<option id="'. $_SESSION["defaultvalues"][$language .'nodatafoundid'] .'" selected>' .$_SESSION["defaultvalues"][$language .'nodatafoundvalue'] .'</option>';
    }
    return $local_data ;
  }

  function getofficeslist($pageid, $state,$district,$taluk,$village)
  {
    global $rootpath, $rulespath, $assetspath,$datapath;
    $local_data = '';
    $villageinfo = $_SESSION['villageinfo'];
    if(count($villageinfo) > 0)
    {
      for($i=0; $i< count($villageinfo); $i++)
      { 
        if(isset($villageinfo[$i]['villageid']) && 
          isset($villageinfo[$i]['villagename']) && 
          $villageinfo[$i]['villageid'] == $village)
        {
          if(isset($villageinfo[$i]['offices']))
          {
            $officeInfo = $villageinfo[$i]['offices'];
            $local_data .= '<option value="select">--- Select ---</option>';
            for($k=0; $k < count($officeInfo); $k++)
            { 
              $local_data .= '<option value=' . $officeInfo[$k]['officeid'] .' address="' . $officeInfo[$k]['address'] .'">' . $officeInfo[$k]['issue'] .'</option>';
            }
          } 
          else{
            $local_data .= '<option id="select">No data found</option>';
          }
        } 
        else{
          $local_data .= '<option id="select">No data found</option>';
        }
      }
    }
    return $local_data ;
  }

  function buildPage($pagename) {
    global $page_heading; 
    global $page_content; 
    global $navcontent;
    global $defaultimage;
    global $page_data;

    return buildPageContent($pagename);
  }

  function buildheadercontent(){
    return '<div id="" class="header boxshadow">
                <div class="headerleft">
                    <img src="assets/images/logo.png">
                </div>
                <div class="headermiddle">
                   <a href="index.php" title="Home">
                        <img src="assets/images/logo.jpg">
                    </a>
                </div>
                <div class="headerright">
                    <img src="assets/images/logo.jpg" style="display:none">
                </div>
            </div>';
  }

  function buildPageContent($pagename)
  {
    global $page_heading, $navcontent, $page_data;
    $header_content = buildheadercontent();
    $column_left_content = buildcolumnleftcontent($pagename);
    $column_middle_content ='';
    switch($pagename)
    {
      case 'index':
      {
        $column_middle_content = buildHomePage($pagename, $navcontent);
        break;
      }
      case "archives" :
      {
        $column_middle_content = buildArchivePage();
        break;
      }
      case "judicialdecisions" :
      {
        //$column_middle_content = buildJudicialDecisionsPage();
        break;
      }
      case "governmentcontactlist" :
      {
        $column_middle_content = buildGovenmentContactListPage();
        break;
      }
      case "governmentdecisions" :
      {
        //$column_middle_content = buildGovernmentDecisionsPage();
        break;
      }
      case "contactus" :
      {
        $column_middle_content = buildContactUsPage();
        break;
      }
      case "faq" :
      {
        $column_middle_content = buildFAQPage();
        break;
      }
      case "aboutus" :
      {
        $column_middle_content = buildAboutUsPage($pagename);
        break;
      }
      default :// "grievancegenerator"
      {
        $column_middle_content = buildGrievanceGenerator($pagename);
        break;
      }
    }
    $retrun_data =  $header_content 
                    .'<div class="content">'
                      .$column_left_content
                      .'<div class="column middle"><p class="pageheader">'. $page_heading .'</p>'. $column_middle_content .'</div>' 
                      .'<div class="column right">' .buildcolumnrightcontent() .'</div>'
                    .'</div>';

    return $retrun_data;
  }

  function buildFAQPage()
  {
    getdefaultvalues();
    global $datapath;
    $file_name = $datapath ."faq.json";
    $json_data = json_decode(file_get_contents($file_name),true);
    $page_content ='';
    for($i=0; $i< count($json_data); $i++)
    { 
      $page_content .='<div class="faqsection"><div class="faqheading" id="' .$json_data[$i]['faqid'] . '">' . ($i+1) . '). '.$json_data[$i]['faqname'].'</div>'
                    .'<div class="faqcontent" id="' .$json_data[$i]['faqid'] . 'content">' .$json_data[$i]['faqcontent'] ;
      if(isset($json_data[$i]['video']))
      {
        $page_content .='<div>
                          <video class="faqvideo" id="' .$json_data[$i]['faqid'] . 'video" controls>
                            <source src="'. $json_data[$i]['video'] .'" type=video/mp4>
                          </video>
                        </div>';
      }
      $page_content .='</div></div>';
    }
    
    return $page_content;
  }

  function buildcolumnleftcontent($pagename)
  {
    global $page_data;
    getdefaultvalues();
    $local_data = '<div class="column left">';

    global $page_heading, $page_content, $page_data; 
    
    for($i=0; $i< count($page_data); $i++)
    { 
      if($page_data[$i]['pageid'] == $pagename)
      { 
        $page_heading = $page_data[$i]['pagename'];
        if(isset($page_data[$i]['pagecontenttype']) && $page_data[$i]['pagecontenttype'] == 'text')
        {
          $page_content = $page_data[$i]['pagecontent'];
        }
        $local_data .=  
        '<div class="menuitem ' . $page_data[$i]['pageid'] . ' selected"><a href="#" id="' . $page_data[$i]['pageid'] . '">' . $page_data[$i]['pagename'] . '</a></div>';
      }
      else{
        $local_data .=  
        '<div class="menuitem ' . $page_data[$i]['pageid'] . '"><a href=' . $page_data[$i]['pageid'] . '.php id="' . $page_data[$i]['pageid'] . '">' . $page_data[$i]['pagename'] . '</a></div>';
      }
    }
    return $local_data .'</div>';
  }

  function buildcolumnrightcontent()
  {
    return '
        <h3>Updates</h3>
        <div class="scrollcontents">
            <div class="menuitem index selected">
                <a href="#" id="index">Home</a>
            </div>
            <div class="menuitem grievancegenerator">
                <a href="grievancegenerator.php" id="grievancegenerator">Grievance Generator</a>
            </div>
            <div class="menuitem archives">
                <a href="archives.php" id="archives">Archives</a>
            </div>
            <div class="menuitem governmentcontactlist">
                <a href="governmentcontactlist.php" id="governmentcontactlist">Government Contacts</a>
            </div>
            <div class="menuitem judicialdecisions">
                <a href="judicialdecisions.php" id="judicialdecisions">Judicial Decisions</a>
            </div>
            <div class="menuitem governmentdecisions">
                <a href="governmentdecisions.php" id="governmentdecisions">Government Decisions</a>
            </div>
            <div class="menuitem contactus">
                <a href="contactus.php" id="contactus">Contact Us</a>
            </div>
        </div>';
  }

  function buildAboutUsPage($pagename)
  {
    global $page_data;
    getdefaultvalues();
    global $datapath,$brandurl; 
    $file_name = $datapath .$pagename .".html";
    $page_content = file_get_contents($file_name);
    return str_replace('{$brandurl}',$brandurl,$page_content);
  }

  function buildHomePage($pagename, $navcontent)
  {    
    getdefaultvalues();
    global $datapath,$brandurl; 
    $file_name = $datapath .$pagename .".html";
    $page_content = file_get_contents($file_name);
    return str_replace('{$brandurl}',$brandurl,$page_content);
  }

  function buildArchivePage()
  {
    getdefaultvalues();
    global $datapath; 
    global $defaultimage;
    $file_name = $datapath ."archivelist.json";
    $json_data = json_decode(file_get_contents($file_name),true);
    $page_content ='';
    for($i=0; $i< count($json_data); $i++)
    { 
      $imagefilename = $defaultimage;
      if(isset($json_data[$i]['imagename']) && $json_data[$i]['imagename']  != ''){
        
        $imagefilename = $json_data[$i]['filepath'] . $json_data[$i]['imagename'];
      }
      if(isset($json_data[$i]['starting'])){
        $page_content .= '<div class="row archivelist">';
        $page_content .= '<p class="tilesection">' .$json_data[$i]['starting'] .'</p>';
      }
      if(isset($json_data[$i]['filename'])){
          $page_content .= 
            '<div class="column">
                <div class="card" 
                style ="background-image: url(' .$imagefilename . ');width:100%;">
                  <a href="' .$json_data[$i]['filepath'] . $json_data[$i]['filename'] . '"  target="_blank">
                  <h3>' . $json_data[$i]['filetitle'] . '</h3>
                  <p> - by ' . $json_data[$i]['fileauthor'] . '</p>
                  <p>' . $json_data[$i]['fileclassified'] .'</p></a>
                </div> 
            </div>';
        }

        if(isset($json_data[$i]['ending'])){
          $page_content .= '</div>';
        }
    }
    
    return $page_content;
  }

  function buildGrievanceGenerator($pagename)
  { 
    global $rootpath, $rulespath, $assetspath,$datapath;;
    $contentfile = $datapath.'languages.json';
    
    $language_data = json_decode(file_get_contents($contentfile),true);
    $local_data = '<div><p><i>Language :</i></p>';
    $local_data .= 
      '<select pagename="'. $pagename .'" class="'. $pagename .'data" required="true" id="ddllanguage">';
    for($i=0; $i< count($language_data); $i++)
    {  
      $local_data .= '<option value=' . $language_data[$i]['languageid'] .'>' . $language_data[$i]['languagevalue'] .'</option>';
    }
    $local_data .= '</select>';
    $local_data .= 
                  '<div class="outererrormessage" style="display:none;" id="ddllanguageerrormessage">Please select your language</div>';
                  
    $local_data .= '</div>';
    return $local_data .'<div class="languagespecific"></div>';
  }

  function buildJudicialDecisionsPage()
  {

  }

  function buildGovenmentContactListPage()
  {
    getdefaultvalues();
    global $datapath, $defaultimage; 
    $file_name = $datapath ."governmentcontactlist.json";
    $json_data = json_decode(file_get_contents($file_name),true);
    $page_content ='';
    for($i=0; $i< count($json_data); $i++)
    { 
      $imagefilename = $defaultimage;
      if(isset($json_data[$i]['imagename']) && $json_data[$i]['imagename']  != ''){
        $imagefilename = $json_data[$i]['imagepath'] . $json_data[$i]['imagename'];
      }
      
      if(isset($json_data[$i]['starting'])){
        $page_content .= '<div class="row governmentcontactlist">';
        $page_content .= '<p class="tilesection">' .$json_data[$i]['starting'] .'</p>';
      }

      if(isset($json_data[$i]['url'])){
          $page_content .= 
            '<div class="column">
                <div class="card" 
                style ="background-image: url(' .$imagefilename . ');">
                  <a href="' .$json_data[$i]['url'] . '"  target="_blank">
                  <h3>' . $json_data[$i]['title'] . '</h3></a>
                </div> 
            </div>';
        }

        if(isset($json_data[$i]['ending'])){
          $page_content .= '</div>';
        }
    }
    
    return $page_content;
  }
  function buildGovernmentDecisionsPage()
  {
    
  }
  function buildContactUsPage()
  {
    global $page_content;
    global $page_heading;  
    return $page_content; 
  }

  function generatefooter(){
      return '<p>Copyright &copy; 2022 Samakala Nigalvugal. All rights reserved. Design by <a href="www.samakalanigalvugal.com/">Samakala Nigalvugal</a>.</p>';
  }
?>