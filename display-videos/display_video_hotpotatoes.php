<?php
session_start();

if (!isset($_SESSION['mysid'])) {
    header("Location: main_login.php");
    exit;
}

if (!isset($_GET['videoid'])) {
    header("Location: main_login.php");
    exit;
}

require_once '../config/config.php';

$sessionid = $_SESSION['mysid'];
$videoid   = intval($_GET['videoid']); 
$sql    = "SELECT * FROM videos WHERE videoid='$videoid'";
$result = mysqli_query($conn, $sql);
$row    = mysqli_fetch_assoc($result);

$videotitle = $row['videotitle'] ?? '';
$videourl   = $row['videourl'] ?? '';

$sql    = "SELECT * FROM sessions WHERE sessionid='$sessionid'";
$result = mysqli_query($conn, $sql);
$row    = mysqli_fetch_assoc($result);
$userid = $row['user_id'] ?? null;

class urls_store {
    public $urlid;
    public $start;
    public $endl;
    public $text;
    public $isEmbedCode;
    public $alldone;
}

$data_urls     = [];
$data_urls_2   = [];

$sql    = "SELECT * FROM video_url_code_potatoes WHERE videoid='$videoid' ORDER BY urlid";
$result = mysqli_query($conn, $sql);
$num_rows = mysqli_num_rows($result);

while ($row = mysqli_fetch_assoc($result)) {
    $urlobj              = new urls_store();
    $urlobj->urlid       = $row['urlid'];
    $urlobj->start       = $row['start'];
    $urlobj->endl        = $row['end'];
    $urlobj->text        = $row['text'];
    $urlobj->isEmbedCode = $row['isembedcode'];

    $sql_2    = "SELECT * FROM session_potatoes WHERE exerciseid={$urlobj->urlid} AND alldone=1";
    $result_2 = mysqli_query($conn, $sql_2);
    $urlobj->alldone = (mysqli_num_rows($result_2) > 0) ? 1 : 0;

    $data_urls_2[] = $urlobj;

    $data_urls = [
        "videoid" => $videoid,
        "numurls" => $num_rows,
        "urls"    => $data_urls_2
    ];
}

class quiz_store {
    public $qid;
    public $start;
    public $endl;
    public $Question;
    public $Question_type;
    public $Answer1;
    public $Answer2;
    public $Answer3;
    public $Answer4;
    public $Answer5;
    public $Submitted;
}

$data_questions   = [];
$data_questions_2 = [];

$sql    = "SELECT * FROM video_quiz WHERE videoid='$videoid' ORDER BY qid";
$result = mysqli_query($conn, $sql);
$num_rows = mysqli_num_rows($result);

while ($row = mysqli_fetch_assoc($result)) {
    $quizobj               = new quiz_store();
    $quizobj->qid          = $row['qid'];
    $quizobj->start        = $row['start'];
    $quizobj->endl         = $row['end'];
    $quizobj->Question     = $row['question'];
    $quizobj->Question_type= $row['questionType'];
    $quizobj->Answer1      = $row['answer1'];
    $quizobj->Answer2      = $row['answer2'];
    $quizobj->Answer3      = $row['answer3'];
    $quizobj->Answer4      = $row['answer4'];
    $quizobj->Answer5      = $row['answer5'];

    $sql2     = "SELECT * FROM session_video_quiz WHERE questionid='{$quizobj->qid}' AND userid='$userid'";
    $result2  = mysqli_query($conn, $sql2);
    $quizobj->Submitted = (mysqli_num_rows($result2) > 0) ? 1 : 0;

    $data_questions_2[] = $quizobj;

    $data_questions = [
        "videoid"           => $videoid,
        "num_quiz_questions"=> $num_rows,
        "quiz_questions"    => $data_questions_2
    ];
}

$sql = "INSERT INTO session_videos (sessionid, videoid, startdate, starttime) 
        VALUES ($sessionid, $videoid, CURDATE(), CURTIME())";
$result = mysqli_query($conn, $sql);
$vid    = mysqli_insert_id($conn);

$_SESSION['vid'] = $vid;

$section_bool = 1;

class section_store {
    public $sectionid;
    public $start;
    public $endl;
    public $title;
}

$data_sections     = [];
$data_time_sections= [];
$data_sections_2   = [];
$data_time_sections_2 = [];

$sql    = "SELECT * FROM videos WHERE videoid='$videoid'";
$result = mysqli_query($conn, $sql);
$row    = mysqli_fetch_assoc($result);

$video_duration     = $row['videolength'];
$time_sections      = $row['time_section_threshold'];
$video_sections_bool= $row['video_sections_bool'];

if ($video_sections_bool == 1) {
    $sql    = "SELECT * FROM video_sections WHERE videoid='$videoid' ORDER BY sectionid";
    $result = mysqli_query($conn, $sql);
    $num_rows = mysqli_num_rows($result);

    while ($row = mysqli_fetch_assoc($result)) {
        $sectionobj          = new section_store();
        $sectionobj->sectionid= $row['sectionid'];
        $sectionobj->start   = $row['start'];
        $sectionobj->endl    = $row['end'];
        $sectionobj->title   = $row['title'];

        $data_sections_2[] = $sectionobj;

        $data_sections = [
            "videoid"    => $videoid,
            "numsections"=> $num_rows,
            "sections"   => $data_sections_2
        ];
    }
}


$intervals = round($video_duration / $time_sections);
$end = 0;

for ($i = 0; $i < $intervals; $i++) {
    $sectionid = $i + 1;
    $start     = $end + 1;
    $end       = $start + $time_sections - 1;
    $title     = $sectionid . '_section';

    $sectionobj          = new section_store();
    $sectionobj->sectionid= $sectionid;
    $sectionobj->start   = $start;
    $sectionobj->endl    = $end;
    $sectionobj->title   = $title;

    $data_time_sections_2[] = $sectionobj;

    $data_time_sections = [
        "videoid"    => $videoid,
        "numsections"=> $intervals,
        "sections"   => $data_time_sections_2
    ];
}
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
 <HEAD>
 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-7">
  <!--script src="http://code.jquery.com/jquery-1.7.1.min.js"></script-->
  <script src="../build/jquery.js"></script>	
  <!--script src="../build/mediaelement.js"></script-->
  <script src="../build/mediaelement-and-player.min.js"></script>
  <script src="testforfiles.js"></script>

  <style type="text/css">
  
   
   .style1{
	font-size: 14px;
	
   }

   .style2 {
	font-size: 14px;
   }

  
  


 
  
  </style>
  
                        
  
  <link rel="stylesheet" href="jquery-3.css" type="text/css" />
  <link rel="stylesheet" href="../build/mediaelementplayer.min.css" />

  
	  <title><?php echo $videotitle; ?></title>

                    
                
       
	<meta charset="utf-8" />
 </HEAD>




<div id="video" style="float:left; width:50%;">
<h2>Video Title: <?php echo $videotitle; ?></h2><br>


<video id="player1" width="500" height="380">
    <source src="<?php echo $videourl; ?>" type="video/youtube" >
	 <!--track kind="subtitles" src="../media/graphic_design.srt" srclang = "el" /-->
	 

</video>


<br>
<span id="label"></span><br>
<span id="label_2"></span><br>
<span id="time"></span><br>
<span id="percent"></span><br>
<span id="event"></span><br>
<span id="duration"></span><br>


</div>


<div id="player2" class="style1" style="float:right; width:50%" >
<!--font size="1"-->
	<span id="contents"></span>

    <span id="linebreak">
	
	
	<span id="questions"></span>
	<!--/font-->
</div>


<br><br><br>



                
<script>
var timetext;
var durationtext;



var gComment='';
var gMe;
var gi=0;

var count=0;
//var timestamps = [];
var all=0;

var last = 0,
now,
old;
var eventype=0;
var oldeventtype=0;
var sectionid=0;
var oldsectionid=0;
var time_sectionid=1;
var old_time_sectionid=0;
var percent=0;

var CONST_LOADSTART=1;
    CONST_SECTION_ENTER=2,
	CONST_PLAY=3,
	CONST_PAUSE=4,
	CONST_END=5,
	CONST_SEEK=6,
	CONST_MUTE=7,
	CONST_VOLUME_CHANGE=8,
	CONST_JUMP=9,
	CONST_LAND=10,
	CONST_TIME_SECTION_ENTER=11;


var dummy=0;

var timestamp_time_sections = <?php echo json_encode($data_time_sections); ?>;
var timestamp_sections = <?php echo json_encode($data_sections); ?>;
var timestamp_questions = <?php echo json_encode($data_questions); ?>;
var timestamp_urls = <?php echo json_encode($data_urls); ?>;






var vid= <?php echo $vid ?>;
var sessionid= <?php echo $sessionid ?>;
var userid= <?php echo $userid ?>;

var video_duration= <?php echo $video_duration ?>;






function handleSubmitquiz(lquestionid,lsessionid,lvid,luserid) {

var radios;
var elements
var lanswerInt=0;
var lanswerText='';
var i;
var lquestionType;
	   
	    
		radios = document.getElementById("questionform").getElementsByTagName("input");
		textareaelement = document.getElementById("textarea");
		

        if ((radios!=null) && (radios[0].type!="button"))
			{
			//alert("hello2");
			lquestionType=1;
			for(i = 0; i < radios.length; i++) 
				{
				if(radios[i].checked == true) {lanswerInt=radios[i].value;}
				}

			}

		if (textareaelement!=null)
			{
			//alert("hello");
			lquestionType=2;
			lanswerText= document.getElementById("textarea").value;
			}


 
 $.post("sendanswers.php", {
	   
		sessionid:lsessionid,
		vid:lvid,
		userid:luserid,
		questionid:lquestionid,
		questionType:lquestionType,
		answerInt:lanswerInt,
		answerText:lanswerText
		
		
	}, function(data) {
		//alert(data);
	});
	   

gMe.play();
timestamp_questions.quiz_questions[gi].Submitted=1;

}



function showquestion(t){
var Question, Answer1,Answer2,Answer3,Answer4,Answer5;
var AnswerallText='';

var lSubmitted;
var comment='';
var i=0;
var lquestionid;
now = parseInt(gMe.currentTime);
t=now;




      for(i=0;i< timestamp_questions.num_quiz_questions; i++){
        if(t >=  timestamp_questions.quiz_questions[i].start && t <=  timestamp_questions.quiz_questions[i].endl){
			 Question= timestamp_questions.quiz_questions[i].Question;
			 Question_type= timestamp_questions.quiz_questions[i].Question_type;

			 if  (Question_type==2)
			{

			 lquestionid=i+1;

             var formstart=' <form name="form1" id="questionform">';
			 var Question='<b>������� '+lquestionid+'</b><br>'+Question+'<br><br>';
			 
			 //AnswerallText=AnswerallText+'<input type="text"  name="textbox" id="textbox"><br><br>';
			 AnswerallText=AnswerallText+'<textarea name="textarea"  id="textarea" cols="40" rows="5"  ></textarea><br><br>';
			

		     var formend='<input name="SubmitQuiz"  type="button" value="Submit" onClick="JavaScript:handleSubmitquiz('+lquestionid+','+sessionid+','+vid+','+userid+')"></form>';
			}

			else 
			{
			 Answer1= timestamp_questions.quiz_questions[i].Answer1;
			 Answer2= timestamp_questions.quiz_questions[i].Answer2;
			 Answer3= timestamp_questions.quiz_questions[i].Answer3;
			 Answer4= timestamp_questions.quiz_questions[i].Answer4;
			 Answer5= timestamp_questions.quiz_questions[i].Answer5;
			 lquestionid=i+1;

			
			
             var formstart=' <form name="form1" id="questionform">';
			 var Question='<b>������� '+lquestionid+'</b><br>'+Question+'<br><br>';
			 
			 if (Answer1!='') {AnswerallText=AnswerallText+'<input type="radio" name="radio" value="1">'+Answer1+'<BR>';}
			 if (Answer2!='') {AnswerallText=AnswerallText+'<input type="radio" name="radio" value="2">'+Answer2+'<BR>';}
			 if (Answer3!='') {AnswerallText=AnswerallText+'<input type="radio" name="radio" value="3">'+Answer3+'<BR>';}
			 if (Answer4!='') {AnswerallText=AnswerallText+'<input type="radio" name="radio" value="4">'+Answer4+'<BR>';}
			 if (Answer5!='') {AnswerallText=AnswerallText+'<input type="radio" name="radio" value="5">'+Answer5+'<BR>';}
			

		     var formend='<br><input name="SubmitQuiz"  type="button" value="Submit" onClick="JavaScript:handleSubmitquiz('+lquestionid+','+sessionid+','+vid+','+userid+')"></form>';

			}
           


			if (timestamp_questions.quiz_questions[i].Submitted==0)
			{
				 
				comment=formstart+Question+ AnswerallText+formend;
				gMe.pause();
				gi=i;
				
				

			}
			
		 
           } 

		  }

    if (comment!=gComment)
    {
	document.getElementById('questions').innerHTML = comment;
	
	gComment=comment;
	
    }

	
      
    };



function showcontents(){
	
var title='';
var strtitle='<h3 align="center">Table of Contents</h3><p align="center" class="style1">You can be tranfered in video sections by clicking on the tabl of contents links </p><br>';

var str='';

      for(i=0;i<timestamp_sections.numsections; i++){
			 sectionid=timestamp_sections.sections[i].sectionid;
			 title=timestamp_sections.sections[i].title;
			
			str=str+'<div  align="center" class="style2" id=content"'+i+'"> <a onclick="jsfunction('+i+')" href="#">'+ sectionid +' '+ title+'</div><br>';
			//str='hello';
			str=str.replace('section', '�������');
		  }
    
	document.getElementById('contents').innerHTML=strtitle+str;
    
  
    };


function jsfunction(lsectionid){
 
  var ltime=timestamp_sections.sections[lsectionid].start;
  gMe.setCurrentTime(ltime);
  now=ltime;
  sectionid=lsectionid+1;
  searchsection();
  percent=parseInt(ltime/video_duration *100);
  storeevent(sessionid,vid,sectionid,time_sectionid,CONST_SECTION_ENTER,ltime,percent,1);
 
}

function storeevent(lsessionid,lvid,lsectionid,ltime_sectionid,leventtype,lvideotime,lpercent,lcontent_table_bool)
{
  
	$.post("sendevents.php", {
	    sessionid:lsessionid,
		vid:lvid,
		sectionid:lsectionid,
		time_sectionid:ltime_sectionid,
		eventtype: leventtype,
		videotime:lvideotime,
		percent:lpercent,
		content_table_bool:lcontent_table_bool
		
	}, function(data) {
		//alert(data);
	});
	
}



function showtime(ltime){
	var timetmp=ltime;
	var hrs = Math.floor(timetmp/3600);
    timetmp = timetmp - 3600 * hrs;
	var mins = Math.floor(timetmp/60);
	timetmp = timetmp - 60 * mins;
	var secs = Math.floor(timetmp);
	var ltimetext = (hrs < 10 ? "0" : "" ) + hrs + ":" 
				 + (mins < 10 ? "0" : "" ) + mins + ":" 
				 + (secs < 10 ? "0" : "" ) + secs;
	return ltimetext;


}

function searchsection(){

	now = parseInt(gMe.currentTime);
    percent=parseInt(gMe.currentTime/gMe.duration *100);

	for(i=0;i<timestamp_time_sections.numsections; i++){
        if( now>= timestamp_time_sections.sections[i].start && now <= timestamp_time_sections.sections[i].endl){
			 time_sectionid=timestamp_time_sections.sections[i].sectionid;
           } 
	   }

	for(i=0;i<timestamp_sections.numsections; i++){
        if( now>= timestamp_sections.sections[i].start && now <= timestamp_sections.sections[i].endl){
			 sectionid=timestamp_sections.sections[i].sectionid;
           } 
	   }


}

function showsection(t){

now = parseInt(gMe.currentTime);
percent=parseInt(gMe.currentTime/gMe.duration *100);
	
var title_sections='';
var title_time_sections='';

      for(i=0;i<timestamp_time_sections.numsections; i++){
        if(t >= timestamp_time_sections.sections[i].start && t <= timestamp_time_sections.sections[i].endl){
			 time_sectionid=timestamp_time_sections.sections[i].sectionid;
			 title_time_sections='<h2>'+timestamp_time_sections.sections[i].title+'<h2>';
			 
           } 

		  }


	   for(i=0;i<timestamp_sections.numsections; i++){
        if(t >= timestamp_sections.sections[i].start && t <= timestamp_sections.sections[i].endl){
			 sectionid=timestamp_sections.sections[i].sectionid;
			 title_sections='<h2>'+timestamp_sections.sections[i].title+'<h2>';
			 
           } 

		  }

   document.getElementById('label').innerHTML = title_sections+' '+title_time_sections;

    
	if ((sectionid!=oldsectionid) && (eventtype==CONST_PLAY)) 
		{ storeevent(sessionid,vid,sectionid,time_sectionid,CONST_SECTION_ENTER,now,percent,0);}
    oldsectionid=sectionid;

	if ((time_sectionid!=old_time_sectionid) && (eventtype==CONST_PLAY)) 
		{ storeevent(sessionid,vid,sectionid,time_sectionid,CONST_TIME_SECTION_ENTER,now,percent,0);}
    old_time_sectionid=time_sectionid;

   
	if (text=='')
    {
	document.getElementById('label').innerHTML = ' ';
    }
      
    };

var gText='';
function showurls(t){
	
var text='';
var result=0;
var isEmbedCode=0;
     for(i=0;i<timestamp_urls.numurls; i++){
        if(t >= timestamp_urls.urls[i].start && t <= timestamp_urls.urls[i].endl){
			//alert (timestamp_urls.urls[i].alldone);
	 		 if (timestamp_urls.urls[i].alldone==0 ||  timestamp_urls.urls[i].alldone==1) 
				 {
					 text=timestamp_urls.urls[i].text;
					 isEmbedCode=timestamp_urls.urls[i].isEmbedCode;
					 gMe.pause();
					 
					 if (isEmbedCode==0)
					 {
					 text="<a href='"+ text+"' target='_blank'>"+text+"</a>";
					 } 
					 else
					 {
					 text = text.replace("&lt;", "<");
					 }
			   } 
			 
           } 

		  }

    if (text!=gText)
    {
	document.getElementById('contents').innerHTML = text;
	gText=text;
    }
};



//new MediaElement('player1', {success: function(me,domNode) {
$('video').mediaelementplayer({
	//framesPerSecond: 20,
	features: ['playpause','progress','current','duration','tracks','volume'],
		
    // Hide controls when playing and mouse is not over the video
    
	
	
	success: function(me, node, player) {
		

		
old=0;
now=0;
eventtype=0;

//showcontents();

gMe=me;
 

//time_cuepoints = parseInt(me.duration/interval);

var events = ['loadstart', 'play','pause', 'ended','seeking','volumechange', 'muted'];
		
		for (var i=0, il=events.length; i<il; i++) {
			
			var eventName = events[i];
			
			me.addEventListener(events[i], function(e) {
				//document.getElementById('event').innerHTML = e.type;
                
				if (e.type=='loadstart') { eventtype=CONST_LOADSTART;}
				else if (e.type=='play') { eventtype=CONST_PLAY;}
				else if(e.type=='pause') { eventtype=CONST_PAUSE;}
				else if(e.type=='ended') { eventtype=CONST_END;}
				else if(e.type=='seeking') { eventtype=CONST_SEEK;}
				else if(e.type=='muted') { eventtype=CONST_MUTE;}
				else if(e.type=='volumechange') { eventtype=CONST_VOLUME_CHANGE;}

				searchsection();
				
				
				
				if  (eventtype==1) {
					sectionid=1; 
					oldsectionid=1;
					time_sectionid=1;
					old_time_sectionid=1;
					}
				
				if ((eventtype==CONST_SEEK || eventtype==CONST_VOLUME_CHANGE || eventtype==CONST_PAUSE  || eventtype==CONST_JUMP ) && (eventtype==oldeventtype ))
				{
					//nothing
				}
				else { storeevent(sessionid,vid,sectionid,time_sectionid,eventtype,now,percent,0);}

				if  (eventtype==CONST_LOADSTART) { 
					storeevent(sessionid,vid,sectionid,time_sectionid,CONST_TIME_SECTION_ENTER,now,percent,0);
					storeevent(sessionid,vid,sectionid,time_sectionid,CONST_SECTION_ENTER,now,percent,0);
					
					}

				oldeventtype=eventtype;
			});
			
		}
	
	//me.play();
	me.addEventListener('timeupdate', function() {
        
		
		
		

        //timetext=showtime(me.currentTime);
		now = parseInt(me.currentTime);
		timetext=showtime(me.currentTime);
		durationtext=me.duration;
        //document.getElementById('duration').innerHTML = 'Duration: '+durationtext;
		document.getElementById('time').innerHTML = 'Time : '+ timetext;
		percent=parseInt(me.currentTime/me.duration *100);
		document.getElementById('percent').innerHTML = 'percent : '+ percent;
		if ((Math.abs(now-old)>=5)){storeevent(sessionid,vid,sectionid,time_sectionid,CONST_JUMP,old,percent,0);
		                           searchsection();
		                           storeevent(sessionid,vid,sectionid,time_sectionid,CONST_LAND,now,percent,0);}

		
		
		

        if (now!=old)
        {
		old=now;

		//showquestion(now);
		showurls(now);
		showsection(now);
        }
		
		
		

		


		

	}, false);

}});




</script>

 

 

 </BODY>
</HTML>


