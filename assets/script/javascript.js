function printDiv() {
    var divContents = document.getElementById("printdata").innerHTML;
    var windowUrl = ' ';
    //set print document name for gridview
    var uniqueName = new Date();
    var windowName = 'Print_' + uniqueName.getTime();
    var prtWindow = window.open(windowUrl, windowName, 'left=0,top=0,right=0,bottom=0,width=screen.width,height=screen.height,margin=0,0,0,0');
    //var a = window.open('', '', 'height=1000, width=1000');
    //a.document.write('<html>');
    //a.document.write('<body > <h1>Div contents are <br>');
    prtWindow.document.write(divContents);
    //a.document.write('</body></html>');
    //var $div = $('.printdata');
    //$div.contents().remove();
    prtWindow.document.close();
    prtWindow.print();
}

$(document).ready(function(){
    var folderName = './docs/';
    var fileName = '';
    var localerrmessage = '';
    var statesrepository,districtrepository,talukrepository,officerepository,grievancerepository;
    var currentpageurl = window.location.href;
    var CONSTINDEX = 'index';
    var CONSTGRIEVANCE = 'grienvancegenerator';
    var CONSTARCHIVES = 'archives';
    var CONSTGOVERNMENTCONTACTS = 'governmentcontactlist';
    var CONSTCONTACTUS = 'contactus';
    var CONSTGOVERNMENTDECISIONS= 'governmentdecisions';
    var CONSTJUDICIALDECISIONS = 'judicialdecisions';
/*var winRef;
    var divContents = document.getElementById("container").innerHTML;
    //var a = window.open();
      //      a.document.write(divContents);
       //     a.document.close();
      //      a.print();
      try {
        if (!winRef || winRef.closed) {
            winRef = window.open('', '', 'left=0,top=0,width=300,height=400,toolbar=0,scrollbars=0,status=0,dir=ltr');
        } else {
            winRef.focus();
        }
    
        winRef.document.open();
        winRef.document.write(divContents);
    
    
        winRef.document.close();
        winRef.focus();
        winRef.print();
    } catch { }
    finally {
        if (winRef && !winRef.closed) winRef.close();
    }
    
    /*function encode(r) {
        return r.replace(/[\x26\x0A\x3c\x3e\x22\x27]/g, function(r) {
          return "&#" + r.charCodeAt(0) + ";";
        });
      }

    var doc = new jsPDF();
    var specialElementHandlers = {
        '#print-btn': function (element, renderer) {
            return true;
        }
    };
    doc.fromHTML(encode('<h1>தண்ணீர் இல்லை</h1>'), 15, 15, {
        'width': 170,
            'elementHandlers': specialElementHandlers
    });
    doc.save('pdf-version.pdf');*/

    $( ".scrollcontents" ).scroll();
    /*$(document).on("click", '.menuitem', function() {
        alert($(this).text());
    });*/


    $(document).on("click", '#generate', function() {
        var printabledata = validate($(this).attr('pageid'));
        if(printabledata != ''){
            content = generatehtml(printabledata);
        }
    });

    $(".faqcontent").hide();

    $( ".faqheading" ).click(function() {
        $(".faqcontent").hide( "slow" );
        //var vid = document.getElementByClass("faqvideo"); 
        //vid.pause(); 
        $('video').trigger('pause');
        var currentheadingid = $(this).attr('id')
        $("#" + currentheadingid + "content").show( "slow");
    });
    $(document).on("click", '.popuperrormessageclose', function() {
    //$(".popuperrormessageclose").click(function() {
        /*var href = $(".popuplink").attr('href');
        if(href != undefined && href.length > 0)
        {
            var filename = href.split('/').pop().split('#')[0].split('?')[0];
            $.ajax
            ({
                type: "POST",
                url: "domainservices.php",
                data: 
                    { 
                        datarequestedby: 'deletefile',
                        filename :  filename
                    },
                success: function(retstatus)
                {
                    $(".popuplink").attr('');
                },
                error:function(retudata)
                {
                   
                }
            });
        }*/

        var parent = $(this).attr('parentcontrol');
        $("." + parent).css("display","none");
    });

    function generatehtml(printabledata){
        $.ajax
            ({
                type: "POST",
                url: "domainservices.php",
                data: 
                    { datarequestedfrom : $(this).attr('pagename'),
                        datarequestedby: 'generatagrievance',
                        language :  $('#ddllanguage').val(),
                        fromname :  $('#txtfromname').val(),
                        fromhousenumber :  $('#txtfromhousenumber').val(),
                        fromhousename :  $('#txtfromhousename').val(),
                        fromstreetname :  $('#txtfromstreetname').val(),
                        fromcity :  $('#txtfromcity').val(),
                        fromvillagename :  $('#txtfromvillagename').val(),
                        frompostalname :  $('#txtfrompostalname').val(),
                        districtname :  $('#txtdistrictname').val(),
                        fromstatename :  $('#txtfromstatename').val(),
                        frompostalcode :  $('#txtfrompostalcode').val(),
                        mobilenumber :  $('#txtmobilenumber').val(),
                        emailaddress :  $('#txtemailaddress').val(),
                        grievanceid : $('#ddlgrievance').val(),
                        grievancename : $('#ddlgrievance option:selected').text(),
                        state: $("#ddlstate").val(), 
                        district: $("#ddldistrict").val(), 
                        taluk: $("#ddltaluk").val(), 
                        village :$("#ddlvillage").val()
                    },
                success: function(retdata)
                {
                    var $div = $('.printdata');
                    $div.contents().remove();
                    
                    $('.popup').css("display", "block");
                    $('.popuplink').css("display", "none");
                    $('.printdata').css("display", "none");
                    $('.popuperrormessage').css("display", "none");

                    var jsondata = $.parseJSON(retdata);
                    var printable_data = jsondata['data'];
                    var alert_data = jsondata['popup'];
                    $("#printdata").html(printable_data);
                    $(".popup").html(alert_data);
                  //  $(".popup").html(jsondata['popup']);
                    if(jsondata['status'] == '1')
                    {
                        $('.popuplink').css("display", "block");
                    }
                    else
                    {
                         $('.popup').css("display", "block");
                         $('.popuperrormessage').css("display", "block");
                    }
                    
                    //if(retdata.length > 0 && retdata.substr(0, 15) === '<!DOCTYPE html>')
                   //{
                        //$("#container").html(retdata);
                       /* var divContents = document.getElementById("container").innerHTML;
                    var a = window.open('', '', 'height=500, width=500');
                            a.document.write(divContents);
                            a.document.close();
                            a.print();*/

                        //var headers =  $(".container").html();
                       // var w = window.open();
                       // document.write(retdata);
                       //$(".container").print();


                        //$('.popuplink').attr("href", returl);
                   //     $('.popuplink').css("display", "block");
                   // }
                  //  else
                  //  {
                   //     $('.popup').css("display", "block");
                   //     $('.popuperrormessage').css("display", "block");
                   // }
                },
                error:function(retudata)
                {
                    $('.popup').css("display", "block");
                    $('.popuperrormessage').css("display", "block");
                }
            });
    }

    $(document).on("change", 'select', function() {
    //$("select").change (function(){ 
        
        if($(this).val() == "select") return;
        var haschild = $(this).attr('haschild');
        var childcontrolexternalfile = $(this).attr('childcontrolexternalfile');
        var childcontrolid = $(this).attr('childcontrolid');
        var childfilecontentid = $(this).attr('childfilecontentid');
        var childfilecontentname = $(this).attr('childfilecontentname');
        var prefixkeywithfile = $(this).attr('prefixkeywithfile');
        var ctrlid = $(this).attr('id');
        var shouldprocessdomaincall = false;

        switch(ctrlid) { 
            case 'ddllanguage':
            {
                shouldprocessdomaincall = true;
                $("#ddlgrievance").find("option").remove(); 
            }
            case 'ddlstate':
            {
                shouldprocessdomaincall = true;
                $("#ddldistrict").find("option").remove(); 
                $("#ddltaluk").find("option").remove(); 
                $("#ddlvillage").find("option").remove(); 
                $("#ddloffice").find("option").remove(); 
                break;
            }
            case 'ddldistrict':
            {
                shouldprocessdomaincall = true;
                $("#ddltaluk").find("option").remove(); 
                $("#ddlvillage").find("option").remove(); 
                $("#ddloffice").find("option").remove(); 
                break;
            }
            case 'ddltaluk':
            {
                shouldprocessdomaincall = true;
                $("#ddlvillage").find("option").remove(); 
                $("#ddloffice").find("option").remove(); 
                break;
            }
            case 'ddlvillage':
            {
                shouldprocessdomaincall = haschild == "1";
                $("#ddloffice").find("option").remove(); 
                break;
            }
            //$('#grievances').find("option").remove(); 
            //districtrepository = statesrepository.filter(obj=> obj.id == $("#states").val())[0]['districts'];
            //loadDroptownFromRepository('','districts', districtrepository);
            //loadDroptown($("#states").val() +  'districts','districts');
        }
        if(shouldprocessdomaincall)
        {
            $.ajax
            ({
                type: "POST",
                url: "domainservices.php",
                data: { datarequestedfrom : $(this).attr('pagename'), datarequestedby: ctrlid, 
                                            language: $("#ddllanguage").val(), 
                                            state: $("#ddlstate").val(), 
                                            district: $("#ddldistrict").val(), 
                                            taluk: $("#ddltaluk").val(), 
                                            village :$("#ddlvillage").val(), 
                                            haschild : haschild,
                                            childcontrolid :childcontrolid, 
                                            childcontrolexternalfile :childcontrolexternalfile,
                                            childfilecontentid : childfilecontentid,
                                            childfilecontentname : childfilecontentname,
                                            prefixkeywithfile : prefixkeywithfile},
                success: function(retdata)
                {
                    switch(ctrlid) {  
                        case 'ddllanguage':
                        {
                            var $div = $('.languagespecific');
                            $div.contents().remove();
                            $(".languagespecific").append(retdata);
                            //$("#ddlgrievance").append(retdata);
                            break;
                        }
                        case 'ddlstate':
                        {
                            $("#ddldistrict").append(retdata);
                            break;
                        }
                        case 'ddldistrict':
                        {
                            $("#ddltaluk").append(retdata);
                            break;
                        }
                        case 'ddltaluk':
                        {
                            $("#ddlvillage").append(retdata);
                            break;
                        }
                        case 'ddlvillage':
                        {
                            $("#ddloffice").append(retdata);
                            break;
                        }
                    }

                    
                },
                error: function(retdata)
                {
                    alert (retdata);
                }
            });
        }
    });

    $("#districts").change (function(){   
        $("#taluks").find("option").remove(); 
        $("#villages").find("option").remove(); 
        $("#officers").find("option").remove(); 
        $('#grievances').find("option").remove(); 
    });

    $("#taluks").change (function(){ 
        $("#villages").find("option").remove();   
        $("#officers").find("option").remove(); 
        $('#grievances').find("option").remove(); 
    });

    $("#villages").change (function(){  
        $("#officers").find("option").remove();  
        $('#grievances').find("option").remove(); 
    });

    $("#officers").change (function(){  
        $('#grievances').find("option").remove(); 
    });

    function validate(pageid){
        var isvalid = true;

        var formdata =  '';

        $('.' + pageid + 'data').each(function(){
            var localid = $(this).attr('id');
            if($(this).attr('required') != undefined && 
                (
                    (
                        ($(this).prop('nodeName') == "SELECT" || $(this).prop('nodeName') == "select") && 
                        ($(this).val() == 'select' || $(this).val() == '--- Select ---' || $(this).val() == null)
                    ) ||
                    (($(this).prop('nodeName') == "INPUT" || $(this).prop('nodeName') == "input") && $(this).val() == '')
                )
                )
            {
                $('#' + localid).addClass('redborder');
                $('#' + localid + 'errormessage').css("display", "block");
                isvalid = false;
            }
            else {
                if(formdata.trim().length > 0) {formdata = formdata + ','}
                formdata = formdata + $(this).attr('id') + ' : ' + $(this).val()
                $('#' + localid).removeClass('redborder');
                $('#' + localid + 'errormessage').css("display", "none");
            }
        });
        if(isvalid){
            return formdata;
        }
        else {
            return '';
        }
    }
});