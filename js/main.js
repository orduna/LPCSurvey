function Nav()
{
    var inst = $("#Nav").val();
    if(inst == "") return;
    document.location.href = 'display.php?inst=' + inst;
}

var semaphore = false;
var currentId = false;

function ComplateFunc()
{
    $('#button' + currentId).text('Update');
    $('#button' + currentId).animate({backgroundColor:'#9AC766'}, 500);
}

function Post(data)
{
    semaphore = true;
    $.post('data.php', data, function(data)
                             {
                                 semaphore = false;
                                 $('#button' + currentId).animate({backgroundColor:'#FFAD00'}, 200, ComplateFunc);
                                 //console.log(data);
                                 if(currentId == 'new') location.reload(); 
                             }
          );
}

function GetPass(){return $('#pass').val();}
function GetInst(){return $('#inst').val();}

// of course, we are doing the same checking process for each insert query
function PassCheck()
{
    if(GetPass() == "")
    {
        alert('Please enter password to update and save information.');
        return false;
    }
    var ret  = false;
    var dict = {'pass' : GetPass(), 'inst' : GetInst(), 'op' : 'passCheck'};
    $.ajax({url : 'data.php', 
            async : false,
            type : 'POST',
            data : dict,
            success : function(data){if(data == 'OK!') ret = true;}});
    if(!ret) alert('The password you entered is incorrect.');
    return ret;
}

/*
*/

function PassCheckDyn()
{
    if(GetPass() == "")
    {
        $('#passStatus').html('&#215;'); $('#passStatus').css({color:'#a00'});
        return;
    }

    var ret  = false;
    var dict = {'pass' : GetPass(), 'inst' : GetInst(), 'op' : 'passCheck'};
    $.ajax({url : 'data.php',
            async : true,
            type : 'POST',
            data : dict,
            success : function(data){if(data == 'OK!') {ret = true; $('#passStatus').html('&#10004;'); $('#passStatus').css({color:'#0a0'});} }});

    if(!ret) {$('#passStatus').html('&#215;'); $('#passStatus').css({color:'#a00'});}
}

function changePass()
{
    var currentPass = GetPass();
    var inst        = GetInst();
    var newPass1    = $('#newpass1').val();
    var newPass2    = $('#newpass2').val();

    if(newPass1 != newPass2)
    {
        alert('Passwords do not match.');
        return;
    }

    if(newPass1.length < 6)
    {
        alert('You must have a minimum of 6 characters.');
        return;
    }

    if(!PassCheck())
    {
        alert('The current password you entered is incorrect.');
        return;
    }

    var dict = {'op' : 'pass', 'inst' : inst, 'pass' : currentPass, 'new' : newPass1};
    var ret;
    $.ajax({url : 'data.php',
            async : false,
            type : 'POST',
            data : dict,
            success : function(data){if(data == 'OK!') ret = true;}});
    if(ret)
    {
         alert('Your password successfully changed. This window will be closed.');
         window.close();
    }
    else alert('Your password could not be changed.');
}


function extraInfo()
{
    var OSName = "Unknown OS";
    if (navigator.appVersion.indexOf("Win")!=-1) OSName="Windows";
    if (navigator.appVersion.indexOf("Mac")!=-1) OSName="MacOS";
    if (navigator.appVersion.indexOf("X11")!=-1) OSName="UNIX";
    if (navigator.appVersion.indexOf("Linux")!=-1) OSName="Linux";
    var message = "<b>Updated on:</b> ";
    var d = new Date();
    message += d.toString();
    message += "<br><b>User agent:</b> ";
    message += navigator.userAgent;
    var ua = navigator.userAgent, tem,
    M = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
    if(/trident/i.test(M[1])){
        tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
        return 'IE '+(tem[1] || '');
    }
    if(M[1]=== 'Chrome'){
        tem= ua.match(/\bOPR\/(\d+)/);
        if(tem!= null) return 'Opera '+tem[1];
    }
    M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
    if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);
    message += "<br><b>Web browser:</b> ";
    message +=  M.join(' ');
    return message;
}

function Save(id)
{
    if(semaphore) return;

    if(!PassCheck())
    {
        return;
    }

    var name = $('#'+id).find('b').text();
    //if(!confirm('Are you sure that you want to save the changes for ' + name + '?')) return;
    var dict    = {'op' : 'update', 'pass' : GetPass(), 'inst' : GetInst()};
    dict['pid'] = id.replace("row",""); // yeah, you are right this is not that nice method but that was quick development so fix it!
    currentId   = dict['pid'];
    var answers = {};
    $('#'+id).find('td').find('input,select,textarea').each(function(){
        if($(this).val() == "" || $(this).val() == " ") return;
        answers[$(this).attr('name')] = $(this).val();
    });
    answers[9] = osName();
    dict['answers'] = answers;
    Post(dict);
    confirm('Saved the changes for ' + name + '.');
    location.reload();
    //console.log(dict);
}

function Add()
{
    if($('#fname').val() == '' || $('#lname').val() == '') return;

    if(!PassCheck())
    {
        alert('The password you entered is incorrect.');
        return;
    }

    if(!confirm('Page will be refreshed! Your changes which are not saved will be lost! Are you sure to continue?')) return;

    currentId = 'new';

    var dict    = {'op' : 'add', 'pass' : GetPass(), 'inst' : GetInst()};
    var answers = {};
    $('#new').find('td').find('input,select,textarea').each(function(){
        if($(this).val() == "" || $(this).val() == " ") return;
        if($(this).attr('id') == 'lname' || $(this).attr('id') == 'fname') return;
        answers[$(this).attr('name')] = $(this).val();
    });
    answers[9] = osName();
    dict['answers'] = answers;
    dict['firstName'] = $('#fname').val();
    dict['lastName']  = $('#lname').val();
    console.log(dict);
    Post(dict);
}

$(function() {
    $(".UICalendar").datepicker({dateFormat: 'dd MM yy'});
});
