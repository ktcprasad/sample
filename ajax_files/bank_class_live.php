<?php

if (!defined('STDERR'))
{
   define('STDERR', fopen('php://stderr', 'w'));
}

class bank
{
function ivrflow_bank($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language)
	{
	  
/*
        $link = mssql_connect('10.138.133.99','IVRS_TG', '1vr$tg')
    or die("Data base connection failed");
          mssql_select_db("SNBSTG_100316")
              or die("data base open failed");

*/
       // $link = mssql_connect('10.10.18.39','IVRS_TG', '1vr$tg')
		$link = mssql_connect('10.10.102.27','IVRSAPP_PROD', '!VR5@232sr')
    or die("Data base connection failed");
          mssql_select_db("SNBSTG")
              or die("data base open failed");

// $link = mssql_connect('10.10.18.28','evgenuser', 'evgen@123')
//            or die("Data base connection failed");
//          //mssql_select_db("SNBSAP_020714")
//          mssql_select_db("SNBSTG_290615")
//          //mssql_select_db("SNBSTG_030315")
//              or die("data base open failed");

		$language="telugu";

//Ashok Test Start
		$test_vonumber="";

    		if($caller=='9676697885' || $caller=='4066675144' || $caller=='4066678464' || $caller=='9550628050')
     		{
			$this->play_digit("122",$agi);
/*$filer="/home/vamshi/recordings/".$caller."-".$ivr_call_id."-".date('YmdHis');
$agi-> record_file($filer, 'wav', '#', '640000', '0', 1, '10');
$agi-> stream_file($filer,'#');*/

			//$test_vonumber="987676697885556";
			$GLOBALS['testing']="4066678464";
			########## TEST Numbers start ############

//			$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_number", 5000, 10);
//			$value=$res_dtmf ["result"];
			//$value='9912593928';
			 $value='8501097887';
			$test_vonumber=$value ;
			$value='';
			$GLOBALS['test_vonumber']=$test_vonumber; 
			########## TEST Numbers end ############
			$caller=$test_vonumber;	
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/ashok-test";
			#$agi-> stream_file($wfile, '#');
			#$this->play_digit($caller,$agi);

                	if($caller!=$test_vonumber)
                	{
                	}
                	else
                	{
                        $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/ashok-test";
                        #$agi-> stream_file($wfile, '#');
                	}


		//mssql_close($link);
		//$agi->Hangup();
		//exit;
     		}
//Ashok Test End

		$vo_name_array=mssql_query("select * from vo_info(nolock) where PRY_MOB_NO='$caller' and IS_ACTIVE!='N'");
		$status_valied= mssql_num_rows($vo_name_array);
		$vo_name_array=mssql_fetch_array($vo_name_array);
		$void_code=$vo_name_array['TRANS_VO_ID'];
		$is_mepma=$vo_name_array['IS_MEPMA'];
		$DISTRICT_ID=$vo_name_array['DISTRICT_ID'];
		$MANDAL_ID=$vo_name_array['MANDAL_ID'];
		$SEC_MOB_NO=$vo_name_array['SEC_MOB_NO'];

		$message="VOID(PRY): $void_code : is_mepma: $is_mepma ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

		//Start - verifying secondary mobile number and deactivating primary mobile number
		if($status_valied>0){
		$vo_id_array_vfy=mssql_query("select * from admin_snbs_imei_info admin inner join snbs_imei_info_new snbs on admin.vo_id=snbs.vo_id and snbs.user_1 is not null and snbs.user_2 is not null and snbs.user_3 is not null and snbs.AADHAR_1 is not null and snbs.AADHAR_2 is not null and snbs.AADHAR_3 is not null and snbs.user_1 != '' and snbs.user_2!= '' and snbs.user_3 != '' and snbs.AADHAR_1 != '' and snbs.AADHAR_2 != '' and snbs.AADHAR_3 != '' and snbs.user_1 != 'null' and snbs.user_2!= 'null' and snbs.user_3 != 'null' and snbs.AADHAR_1 != 'null' and snbs.AADHAR_2 != 'null' and snbs.AADHAR_3 != 'null' where snbs.vo_id='$void_code'");
		$status_rows_valied_vfy = mssql_num_rows($vo_id_array_vfy);
		$message="OBs registraion:$status_rows_valied_vfy ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
		if($status_rows_valied_vfy>='1' && $SEC_MOB_NO!='')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/callfromsecondary";
                $agi-> stream_file($wfile, '#');
		$agi->Hangup();
		exit;
		//call from secndary mobile number
		}		
		}
		//End - verifying secondary mobile number and deactivating primary mobile number
		
		//start - considering secondary mobile number
		
		$vo_name_array_sec=mssql_query("select * from vo_info(nolock) where SEC_MOB_NO='$caller' and IS_ACTIVE!='N'");
		$status_valied_sec= mssql_num_rows($vo_name_array_sec);
		if($status_valied>0 && $status_valied_sec>0){
		//if mobile number bearing with two vo_id's in primary and secondary
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/numer_notvalied";
		$message="Both primary and secondary mobile numbers are same";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
                $agi-> stream_file($wfile, '#');
		$agi->Hangup();
		exit;
		
		}
		if($status_valied_sec>0){//for secondary mobile validation
		
		$vo_name_array_sec=mssql_fetch_array($vo_name_array_sec);
		$void_code=$vo_name_array_sec['TRANS_VO_ID'];
		$is_mepma=$vo_name_array_sec['IS_MEPMA'];
		$DISTRICT_ID=$vo_name_array_sec['DISTRICT_ID'];
		$MANDAL_ID=$vo_name_array_sec['MANDAL_ID'];
		
		$message="VOID(SEC): $void_code : is_mepma: $is_mepma ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		$message=" $caller Authenticated in VO_INFO Secondary ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		$vo_id_array=mssql_query("select * from admin_snbs_imei_info admin inner join snbs_imei_info_new snbs on admin.vo_id=snbs.vo_id and snbs.user_1 is not null and snbs.user_2 is not null and snbs.user_3 is not null and snbs.AADHAR_1 is not null and snbs.AADHAR_2 is not null and snbs.AADHAR_3 is not null
and snbs.user_1 != '' and snbs.user_2!= '' and snbs.user_3 != '' and snbs.AADHAR_1 != '' and snbs.AADHAR_2 != '' and snbs.AADHAR_3 != '' where snbs.vo_id='$void_code'");
		$status_rows_valied= mssql_num_rows($vo_id_array);
	
		if($status_rows_valied!='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/callfromprimary";

		$message="OBs not registered:";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
                $agi-> stream_file($wfile, '#');
		$agi->Hangup();
		exit;
		//call from primary mobile number
		}
		}
		//end - considering secondary mobile number
		
		
		
		if($is_mepma=='Y')
		{
		$message="VO is a mepma,Hanging up the call. ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
		mssql_close($link);
		$agi->Hangup();
		exit;
		}

		if($status_valied>='1')
		{

		$message=" $caller Authenticated in VO_INFO Primary";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		 
		}
		else if($status_valied_sec=='0')
		{

	    $message=" $caller is not present in VO_INFO ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/numer_notvalied";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}
		
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/vo_pin", 5000, 5);
	        $vo_pin=$res_dtmf ["result"];
		
		$message="Caller entered pin : $vo_pin  ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$vo_name_pin_stat=mssql_query("select * from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code' and PIN='$vo_pin'");
		$stat_pin= mssql_num_rows($vo_name_pin_stat);
		
		
		if($caller=='8186970028')
		{
		$stat_pin=1;
		}
		

		if($stat_pin=='0')
		{
		
		$valid_pin_rs=mssql_fetch_array(mssql_query("select PIN from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
		$valied_pin=$valid_pin_rs['PIN'];
		
		$message="VO PIN is  $valied_pin , Caller entered $vo_pin ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		if($valied_pin==''||$valied_pin=='0')
		{	
		$vo_name_array=mssql_query("select * from vo_info(nolock) where TRANS_VO_ID='$void_code' and IVRS_PIN='$vo_pin' order by CREATED_DATE  desc");
		//$vo_name_array=mysql_fetch_array(mysql_query("select * from vo_info where vo_id='$void_code'"));
		$status_search= mssql_num_rows($vo_name_array);
		$pin_stat='main';
		
		$message="New VO in IVRS_VO_CREDIT_LIMIT ,Caller will be propmted for new pin ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		}
		 else
		 {
			 
	 if($x>='1')
	    {
	   	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/pin_not_valied";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->ivrflow_bank($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);
		}
		else
		{
		 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		$agi->Hangup();
		
		$message="PIN retry exceeded,Hangup the call. ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		mssql_close($link);
		
		exit;
		}
	 
	  }
		}
		elseif($stat_pin=='1')
		{
		   //Vo:Spandana,khammam Urban,8186969919
		  //if($void_code != '012236010010101' )
		   {
		$check_urban=mssql_num_rows(mssql_query("select VO_ID from VO_RURALTOURBAN(nolock) where VO_ID='$void_code'"));
                if($check_urban == "1"){
                $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rural_to_urban";
                $agi-> stream_file($wfile, '#');
                $agi->Hangup();
                
                $message="VO_RURALTOURBAN ,Hangup the call  ";
				$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
                
		mssql_close($link);
                exit;
                }
		    } //END Of Escape RU Condition
		$vo_name_array=mssql_query("select * from vo_info(nolock) where TRANS_VO_ID='$void_code' order by CREATED_DATE  desc");
		//$vo_name_array=mysql_fetch_array(mysql_query("select * from vo_info where vo_id='$void_code'"));
		$status_search= mssql_num_rows($vo_name_array);
		}
		 else
		 {	 
	 if($x>='1')
	    {
	   	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/pin_not_valied";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->ivrflow_bank($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);
		}
		else
		{
		$message="PIN retry exceeded,Hangup the call. ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}
	 
	  }


		//Start Putting conditions for Documets Verification By Ashok
		$ms_qry="select IS_VERIFIED from MS_DOCS_VERIFIED where DISTRICT_ID='$DISTRICT_ID' and MANDAL_ID='$MANDAL_ID'";
		$ms_qry_res=mssql_query($ms_qry);
		$ms_qry_row=mssql_fetch_array($ms_qry_res);

		$vo_qry="select IS_VERIFIED from VO_DOCS_VERIFIED where VO_ID='$void_code'";
		$vo_qry_res=mssql_query($vo_qry);
		$vo_qry_row=mssql_fetch_array($vo_qry_res);


$message="select * from vo_info(nolock) where PRY_MOB_NO='$caller' and IS_ACTIVE!='N'"."\nms_qry:$ms_qry\nvo_qry:$vo_qry\nDocumets submmition at ms and vo level. DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED],vo_IS_VERIFIED=$vo_qry_row[IS_VERIFIED]\n";	
		$this->php_log_ivr($ivr_call_id,$message);

		if($ms_qry_row[IS_VERIFIED]!='Y' && $vo_qry_row[IS_VERIFIED]!='Y')
		{
		$message="Documets not submmited at ms and vo level. DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED],vo_IS_VERIFIED=$vo_qry_row[IS_VERIFIED]\n";	
		$this->php_log_ivr($ivr_call_id,$message);

		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/newly_added/voice_blast_for_mstlf";
		$agi-> stream_file($wfile, '#');

		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/newly_added/voice_blast_for_voslf";
		$agi-> stream_file($wfile, '#');

		mssql_close($link);
		$agi->Hangup();
		exit;
	
		}
		else
		{
		  if($ms_qry_row[IS_VERIFIED]!='Y')
		   {
			$message="Documets not submmited at ms level. DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED]\n";
			$this->php_log_ivr($ivr_call_id,$message);

			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/newly_added/voice_blast_for_mstlf";
			$agi-> stream_file($wfile, '#');

			$agi->Hangup();
			exit;

		   }
		  if($vo_qry_row[IS_VERIFIED]!='Y')
		   {
			$message="Documets not submmited at vo level. DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED],vo_IS_VERIFIED=$vo_qry_row[IS_VERIFIED]\n";

			$this->php_log_ivr($ivr_call_id,$message);

			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/newly_added/voice_blast_for_voslf";
			$agi-> stream_file($wfile, '#');

			mssql_close($link);
			$agi->Hangup();
			exit;

		   }
		}
		//End Putting conditions for Documets Verification By Ashok


		$vo_name_array=mssql_fetch_array($vo_name_array);
		$vo_name=$vo_name_array['VO_NAME'];
	
		$vo_name=str_replace(' ','_',$vo_name);
    	$vo_name=str_replace('.','_',$vo_name);    
   		$vo_name=str_replace(' ','_',$vo_name); 
   		$vo_name=str_replace('__','_',$vo_name);
		$vo_name=strtolower($vo_name);
		if($status_search>='1')
		{
	
		
	$path="/var/lib/asterisk/sounds/vo_ivrs/vo_lower_edited/$vo_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);

if($list != $path)
  {
$vo_name=preg_replace("![^a-z0-9]+!i", "_", $vo_name);
$path="/var/lib/asterisk/sounds/vo_ivrs/vo_lower_edited/$vo_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
}



$message="Prompting VO Name : $path ";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

  if($list == $path)
  {
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_name";
		$agi-> stream_file($wfile, '#');
		if($caller=='9494464446')
		{
			$vo_name='adharsa';
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/vo_names/$vo_name";
		$agi-> stream_file($wfile, '#');

		}
		else{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/vo_lower_edited/$vo_name";
		$agi-> stream_file($wfile, '#');
		}
		
	}else{
		$message="No Audio file for VO name  $vo_name  ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	}
		
		if($pin_stat=='main')
		{
		
		$message="Setting the pin first_usage_change_pin ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
			
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_usage_change_pin";
		$agi-> stream_file($wfile, '#');
		$x=3;
		$this->change_password($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		}
		else
		{
		
		}
		//pin here
	
		 $x='3';
///////////start vo meeting dae validation start 16-08-2017
$total_deposit_rs=mssql_fetch_array(mssql_query("VO_MEETING_DAY_VALIDATION '$void_code'"));
        if($total_deposit_rs[FLAG]=='Y')
        {
	$message="Message:  Please update meeting date.";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_meeting_date_validation";
	$agi-> stream_file($wfile, '#');
	$agi->hangup();	
	exit;
	}
///////////start vo meeting dae validation end 16-08-2017
 
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		$agi->Hangup();
	 }
	 else
	 {
	 if($x>='1')
	    {
	   	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invalied_pin";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->ivrflow_bank($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);
		}
		else
		{
		 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}
	 }	
	
	
	}
	
	
	function vo_request($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$pin_1)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	$vo_name_pin=mssql_query("select * from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code' and PIN='$pin_1'");
		
		$status_pin= mssql_num_rows($vo_name_pin);
	$vo_name_array=mssql_query("select * from vo_info(nolock) where TRANS_VO_ID='$void_code'");
		
		$status_search= mssql_num_rows($vo_name_array);
		$vo_name_array=mssql_fetch_array($vo_name_array);
		//echo $status_pin.$status_search."adsfdsdf";
		
		$vo_name=$vo_name_array['VO_NAME'];
	
		$vo_name=str_replace(' ','_',$vo_name);
    	$vo_name=str_replace('.','_',$vo_name);    
   		$vo_name=str_replace(' ','_',$vo_name); 
   		$vo_name=str_replace('__','_',$vo_name);
		$vo_name=strtolower($vo_name);
		if($status_search>='1' && $status_pin>=1 )
		{
	$path="/var/lib/asterisk/sounds/vo_ivrs/vo_lower_edited/$vo_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/vo_lower_edited/$vo_name";
		$agi-> stream_file($wfile, '#');
		}
		 $x='3';
		 
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		$agi->Hangup();
		
		}
		else
	 {
	 if($x>='1')
	    {
	   	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invalied_pin";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->ivrflow_bank($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);
		}
		else
		{
		 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}
	 }	
		
		
	
	}
	
	function vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	//// get shg list in a VO
	
		$shg_id_query=mssql_query("select TRANS_SHG_ID from SHG_INFO(nolock) where  VO_ID='$void_code'");
$vo_shgs='';
while($shg_id_array=mssql_fetch_array($shg_id_query)){
    $vo_shg_id=$shg_id_array['TRANS_SHG_ID'];
    $vo_shgs.="'".$vo_shg_id."'".",";
	}
	$vo_shgs=substr($vo_shgs,0,-1);
	
	$message="Fetching SHGs in VO $void_code :  $vo_shgs ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	
	//// get shg list in a VO
	
	
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/loan_mode_new", 5000, 1);
	 $i=$res_dtmf ["result"];
	 
	$message="Prompting for loan_mode_new  Caller entered:  $i ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	 
		//$i='1';
		if($i=="1")
		{
			
	
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/sthreenidhi_or_other", 5000, 1);
	 	$sthreenidhi_or_other=$res_dtmf ["result"];

		
			$message="Prompting for sthreenidhi_or_other  Caller entered:  $sthreenidhi_or_other ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		
		if($sthreenidhi_or_other == "1"){
			
		$message="Caller selected Sthreenidhi Loan ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	 
			 
		

				
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/voice_Project_selection", 8000, 1);
	    $status_pop_fund=$res_dtmf ["result"];
	    
	    $message="Prompting caller for Loan type loan_request_type_new ,Caller entered : $status_pop_fund ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
		
		if($status_pop_fund=='1' || $status_pop_fund=='5' || $status_pop_fund=='6')
	 {
		 if($status_pop_fund=='1')
		$message="Caller Selected Normal Sthreenidhi Loan:  Project Type 1 ";
		 else if($status_pop_fund=='5')
		$message="Caller Selected Smartphone Loan:  Project Type 72 ";
		 else if($status_pop_fund=='6')
		$message="Caller Selected Bicycle Loan:  Project Type 74 ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);		 
		 
		$x='3';
		$health="NO";

//Ashok Added for allow vo if grade 'A','B'		
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE,LOAN_ELIGBLE from VO_CREDIT_LIMIT where VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs['GRADE'];
        $vo_loan_eligble=$vo_grade_rs['LOAN_ELIGBLE'];

	    $vo_dist_code=substr($void_code,2,2);
if($vo_grade == 'A' || $vo_grade == 'B' || $vo_grade == 'C' || ($status_pop_fund=='1' && $vo_grade == 'D'))
{
$allow_loan=1;
}
else{
			$allow_loan=$this->check_meeting_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}

	
		if($allow_loan == 0){
			
			$x=$x-1;
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/health_or_project_loan", 5000, 1);
	 	$health_loan=$res_dtmf ["result"];
		if($health_loan == "1")
		{

		$health="YES";
		
		}elseif($health_loan == "2")
		{
		$x='3';
		$this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
        	exit; 	 
		}
		else
		{
			if($x>='1')
		{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/no_access_today";
		$agi-> stream_file($wfile, '#');
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
		
		}
			
		}
		
		
		$vo_samrudhi_validation=$this->check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		if($vo_samrudhi_validation == 0)
		{		   
		
		$message="vo_samrudhi_below_80 : $vo_samrudhi_validation ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_80";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}else{
			$message="vo_samrudhi SUCCESS : $vo_samrudhi_validation ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		}
	
	
	$cif_recovery=$this->check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);
		if($cif_recovery<90)
		{
		
		$message=" cif_recovery less than 90 : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
			
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cif_recovery";
			   $agi-> stream_file($wfile, '#');
			   mssql_close($link);
			   $agi->Hangup();
			   exit;
		}else{
		$message=" cif_recovery SUCCESS : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
			//checking of NPA at VO level By Ashok
			$npa_qry="SELECT top 1 1 FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_DEMAND_STATUS WHERE FYEAR='2018' and BAL>0 and  DATEDIFF(dd,OVERDUE_SINCE,getdate()-1)>90 AND VO_ID='$void_code'";
			$npa_cnt=0;
			$npa_cnt=mssql_num_rows(mssql_query($npa_qry));
                        // New NPA Validation on 11-09-2017 START
                        $npa_qry="SELECT ISNULL(CONVERT(NUMERIC(10,2),((SUM(CASE WHEN DATEDIFF(DAY,ISNULL(OVERDUE_SINCE ,getdate()),getdate()) > 90 THEN OUTSTANDING ELSE 0 END))/(nullif(SUM(OUTSTANDING),0)))*100),0) NPA_PER FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_DEMAND_STATUS WHERE  VO_ID='$void_code' AND FYEAR=YEAR(dateadd(month, 9, GETDATE()))-1";
                        $npa_rslt = mssql_query($npa_qry);
                        $npa_cnt_array=mssql_fetch_array($npa_rslt);
                        $npa_cnt= $npa_cnt_array[NPA_PER];
                        // New NPA Validation on 11-09-2017 END
			if($caller==$test_vonumber)
			{
			$npa_cnt=0;
			}
//			if($npa_cnt >= 1)
			if($npa_cnt > 0.5)
			{
			  $message=" This VO is having NPA :$npa_qry  ";
			  $this->php_log_ivr($ivr_call_id,$message);
			
		   	  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_npa";
			  $agi-> stream_file($wfile, '#');
			  mssql_close($link);
			  $agi->Hangup();
			  exit;
			} 
		}


		$project=1;
 if($GLOBALS['testing']!='4066678464')//remove
$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'play',$project); 

		if($status_pop_fund==5 || $status_pop_fund==6)
		{
		$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		}
		else
		{
		$project=1;
		$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		}

		mssql_close($link);
		$agi->Hangup();
		exit;	
	 }
	 elseif($status_pop_fund=='2')
	 {
	 
		$message="Caller Selected IHHL Loan:  Project Type 71 ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);		 
		 
		$x='3';
		$health="NO";
//------------1 start---------

        $vo_name_array=mssql_query("select trans_vo_id,is_active from vo_info(nolock) where TRANS_VO_ID='$void_code'");
        $status_search= mssql_num_rows($vo_name_array);
        $vo_name_array=mssql_fetch_array($vo_name_array);

        if($vo_name_array['is_active']!='Y')
        {
                $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_account_validtion";
                $agi-> stream_file($wfile, '#');
                mssql_close($link);
                $agi->Hangup();
                exit;
        }

//------------1 end -----------

//Ashok Added for allow vo if grade 'A','B'		
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE,LOAN_ELIGBLE from VO_CREDIT_LIMIT where VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs['GRADE'];
        $vo_loan_eligble=$vo_grade_rs['LOAN_ELIGBLE'];
	
	    $vo_dist_code=substr($void_code,2,2);
if($vo_grade == 'A' || $vo_grade == 'B' || $vo_grade == 'C' || $vo_grade == 'D' )
{
$allow_loan=1;
}
else{
			$allow_loan=0;//$this->check_meeting_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}

	
		if($allow_loan == 0){
			
			$x=$x-1;
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/health_or_project_loan", 5000, 1);
	 	$health_loan=$res_dtmf ["result"];
		if($health_loan == "1")
		{

		$health="YES";
		
		}elseif($health_loan == "2")
		{
		$x='3';
		$this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
        	exit; 	 
		}
		else
		{
			if($x>='1')
		{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/no_access_today";
		$agi-> stream_file($wfile, '#');
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
		
		}
			
		}
		
		
		$vo_samrudhi_validation=$this->check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		

		if($vo_samrudhi_validation == 0)
		{		   
		
		$message="vo_samrudhi_below_80 : $vo_samrudhi_validation ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_80";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}else{
			$message="vo_samrudhi SUCCESS : $vo_samrudhi_validation ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		}
	
	
	$cif_recovery=$this->check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);
	$cif_recovery=100;	
		if($cif_recovery<90)
		{
		
		$message=" cif_recovery less than 90 : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
			
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cif_recovery";
			   $agi-> stream_file($wfile, '#');
			   mssql_close($link);
			   $agi->Hangup();
			   exit;
		}else{
		$message=" cif_recovery SUCCESS : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
			//checking of NPA at VO level By Ashok
			$npa_qry="SELECT top 1 1 FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_DEMAND_STATUS WHERE FYEAR='2017' and BAL>0 and  DATEDIFF(dd,OVERDUE_SINCE,getdate()-1)>90 AND VO_ID='$void_code'";
			$npa_cnt=0;
			$npa_cnt=mssql_num_rows(mssql_query($npa_qry));
                        // New NPA Validation on 11-09-2017 START
                        $npa_qry="SELECT ISNULL(CONVERT(NUMERIC(10,2),((SUM(CASE WHEN DATEDIFF(DAY,ISNULL(OVERDUE_SINCE ,getdate()),getdate()) > 90 THEN OUTSTANDING ELSE 0 END))/(nullif(SUM(OUTSTANDING),0)))*100),0) NPA_PER FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_DEMAND_STATUS WHERE  VO_ID='$void_code' AND FYEAR=YEAR(dateadd(month, 9, GETDATE()))-1";
                        $npa_rslt = mssql_query($npa_qry);
                        $npa_cnt_array=mssql_fetch_array($npa_rslt);
                        $npa_cnt= $npa_cnt_array[NPA_PER];
                        // New NPA Validation on 11-09-2017 END

			if($caller==$test_vonumber)
			{
			$npa_cnt=0;
			}
			if($npa_cnt > 0.5)
			{
			  $message=" This VO is having NPA :$npa_qry  ";
			  $this->php_log_ivr($ivr_call_id,$message);
			
		   	  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_npa";
			  $agi-> stream_file($wfile, '#');
			  mssql_close($link);
			  $agi->Hangup();
			  exit;
			} 
		}
	$project=71;
//	$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'play',$project); 
	
		$this->IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		exit;	
	/*	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/stop_pop_loan";
		$agi-> stream_file($wfile, '#'); 
		mssql_close($link);
		$agi->Hangup();
		exit;
*/
		 }
		 elseif($status_pop_fund=='7')
		 {

		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/numer_notvalied";
		$agi-> stream_file($wfile, '#'); 
		mssql_close($link);
		$agi->Hangup();
		exit;	
	
	$allow_loan=$this->check_meeting_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);	
	if($allow_loan == 0){
		if($x>='1')
		{
			$x=$x-1;
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/no_access_today";
		$agi-> stream_file($wfile, '#');
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }	
	}
	
	$vo_samrudhi_validation=$this->check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	
		if($vo_samrudhi_validation == 0)
		{		   
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_80";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}
	
	
	$cif_recovery=$this->check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);
		
		if($cif_recovery<90)
		{
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cif_recovery";
			   $agi-> stream_file($wfile, '#');
	                   mssql_close($link);
			   $agi->Hangup();
			   exit;
		}
	
	
	$project=11;
	$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'play',$project); 
		   	 
	$x='3'; 
	$this->loan_type_krushe($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$vo_shgs);
	mssql_close($link);
	$agi->Hangup();
	exit; 	 
		 }

elseif($status_pop_fund=='4')
		 {
		$message="Caller Selected Tablet LOAN:  Project Type 79";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);		 
		 
		$x='3';

	$vo_result=mssql_num_rows(mssql_query("select * from VO_INFO_TAB_LOAN where TRANS_VO_ID = '$void_code'"));
	$message=" VO ID : $void_code | Count in VO_INFO_TAB_LOAN : $vo_result";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	 
	
	if($vo_result==1)
	{
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE,LOAN_ELIGBLE from VO_CREDIT_LIMIT where VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs['GRADE'];
        $vo_loan_eligble=$vo_grade_rs['LOAN_ELIGBLE'];
	
	$mms_dist_code=substr($void_code,2,2);
	$mms_mandal_code=substr($void_code,4,2);
	$allow_loan = 0;
	$etime=date('Y-m-d H:i:s');	
	$message=" VO ID : $void_code | Grade : $vo_grade";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);



if($vo_grade == 'A' || $vo_grade == 'B' || $vo_grade == 'C' || $vo_grade == 'D')
{
$allow_loan=1;
}
	
		if($allow_loan == 0){
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/numer_notvalied";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}

/*
$shg_name_array=mssql_fetch_array(mssql_query("select TRANS_SHG_ID from shg_info(nolock) where vo_id = '$void_code' and vo_id = trans_shg_id"));
$shg_code=$shg_name_array['TRANS_SHG_ID'];

$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,IS_POP_MEM from shg_member_info(nolock) where shg_id = '$shg_code' and  shg_id = member_id"));
$member_id=$member_id_rs['MEMBER_ID'];
*/
$PROJECT_TYPE='79';

$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$void_code' and MEMBER_ID='$void_code'  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type='79'"));


$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$void_code'  and  MEMBER_LONG_CODE='$void_code'  and STATUS_ID='11' and project_type='79'")); 

	
$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$void_code' and a.MEMBER_LONG_CODE='$void_code' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'  and a.project_type='79'"));


if($member_before_loan == 0 ){
		 $member_repaid_loans=0;
		}

		$message = "member_before_loan($member_before_loan) = $member_before_loan - $member_rej_cnt_sht - $member_repaid_loans";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

		$member_before_loan=$member_before_loan-$member_rej_cnt_sht-$member_repaid_loans;

		if($member_before_loan >='1')
		{
	$message="Member loan_already_applied : member_prev_loan_count:".$member_prev_loan_count." member_repaid_loans : ".$member_repaid_loans;
	$this->log_ivr($ivr_call_id,$message);	
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
//	if($x>='1')	{	$x=$x-1;	$this-> vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);	}
	mssql_close($link);
	$agi->Hangup();
	exit;	
		}
		else{
		
	$loanReqInsertQry="insert into IVRS_LOAN_REQUEST(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,IS_POP,MOBILE,PURPOSE,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,PROJECT_TYPE,DISTRICT_ID,MANDAL_ID,MEMBER_ID) VALUES ('$void_code','$void_code','14766','36','$etime','-','$caller','Tablet','$ivr_call_id','$etime','open','$unique_id','1','$PROJECT_TYPE','$mms_dist_code','$mms_mandal_code','$void_code')";

$message = $loanReqInsertQry;

$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
	{
	if($caller!=$test_vonumber){
		if (!mssql_query($loanReqInsertQry)) {
		    $message ='MSSQL ERROR: ' . mssql_get_last_message();
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		}
		}
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	mssql_close($link);
	$message="Message: Loan applied successfully";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	$agi->Hangup();
	exit;
	}
		}
		mssql_close($link);
		$agi->Hangup();
		exit;	
	}
	else {
	  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/numer_notvalied";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
	}
	}
	 
		 
/* 	 elseif($status_pop_fund=='4')
		 {
		$message="Caller Selected veedi varthakula runa pathakam LOAN:  Project Type 62 ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);		 
		 
		$x='3';
		$health="NO";
//Ashok Added for allow vo if grade 'A','B'	
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE,LOAN_ELIGBLE from VO_CREDIT_LIMIT where VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs['GRADE'];
        $vo_loan_eligble=$vo_grade_rs['LOAN_ELIGBLE'];
	
	    $vo_dist_code=substr($void_code,2,2);
if($vo_grade == 'A' || $vo_grade == 'B')
{
$allow_loan=1;
}
else{
			$allow_loan=$this->check_meeting_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}
	

	
		if($allow_loan == 0){
			
			$x=$x-1;
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/health_or_project_loan", 5000, 1);
	 	$health_loan=$res_dtmf ["result"];
		if($health_loan == "1")
		{

		$health="YES";
		
		}elseif($health_loan == "2")
		{
		$x='3';
		$this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
                exit; 	 
		}
		else
		{
			if($x>='1')
		{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/no_access_today";
		$agi-> stream_file($wfile, '#');
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
		
		}
			
		}
		
		
		$vo_samrudhi_validation=$this->check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		
		if($vo_samrudhi_validation == 0)
		{		   
		
		$message="vo_samrudhi_below_80 : $vo_samrudhi_validation ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_80";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}else{
			$message="vo_samrudhi SUCCESS : $vo_samrudhi_validation ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		}
	
	
	$cif_recovery=$this->check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);
		
		if($cif_recovery<90)
		{
		
		$message=" cif_recovery less than 90 : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
			
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cif_recovery";
			   $agi-> stream_file($wfile, '#');
	                   mssql_close($link);
			   $agi->Hangup();
			   exit;
		}else{
		$message=" cif_recovery SUCCESS : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		}
	$project=62;
	$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'play',$project); 
	
		$this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		exit;	
		 }
*/
		 	 elseif($status_pop_fund=='5')
		 {

		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/numer_notvalied";
		$agi-> stream_file($wfile, '#'); 
		mssql_close($link);
		$agi->Hangup();
		exit;

		$allow_loan=$this->check_meeting_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);	
	if($allow_loan == 0){
		if($x>='1')
		{
			$x=$x-1;
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/no_access_today";
		$agi-> stream_file($wfile, '#');
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }	
	}
	
	$vo_samrudhi_validation=$this->check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	
		if($vo_samrudhi_validation == 0)
		{		   
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_80";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}
	
	
	$cif_recovery=$this->check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);
		
		if($cif_recovery<90)
		{
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cif_recovery";
			   $agi-> stream_file($wfile, '#');
	                   mssql_close($link);
			   $agi->Hangup();
			   exit;
		}
	
		 
	$x='3';
		
		$this->project_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
                exit; 	 
		 }
		  	 elseif($status_pop_fund=='6')
		 {

		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/numer_notvalied";
		$agi-> stream_file($wfile, '#'); 
		mssql_close($link);
		$agi->Hangup();
		exit;
			$allow_loan=$this->check_meeting_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);	
	if($allow_loan == 0){
		if($x>='1')
		{
			$x=$x-1;
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/no_access_today";
		$agi-> stream_file($wfile, '#');
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }	
	}
	
	$vo_samrudhi_validation=$this->check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	
		if($vo_samrudhi_validation == 0)
		{		   
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_80";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}
	
	
	$cif_recovery=$this->check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);
		
		if($cif_recovery<90)
		{
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cif_recovery";
			   $agi-> stream_file($wfile, '#');
	                   mssql_close($link);
			   $agi->Hangup();
			   exit;
		}
	
	
	$project=25;
	$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'play',$project); 
	 
	$x='3';
		$this->project_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
                exit; 	 
		 }elseif($status_pop_fund=='3')
		 {
		$message="Userselected Corpus loan: $status_pop_fund ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
			
		
		$vo_samrudhi_validation=$this->check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		
		if($vo_samrudhi_validation == 0)
		{		   
		
		$message="vo_samrudhi_below_80 : $vo_samrudhi_validation ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_80";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
		}else{
			$message="vo_samrudhi SUCCESS : $vo_samrudhi_validation ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		}
	
	
	$cif_recovery=$this->check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);
		
		if($cif_recovery<90)
		{
		
		$message=" cif_recovery less than 90 : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
			
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cif_recovery";


			   $agi-> stream_file($wfile, '#');
	                   mssql_close($link);
			   $agi->Hangup();
			   exit;
		}else{
		$message=" cif_recovery SUCCESS : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		}
	
	
	
	$x='3';
		$this->corpus_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
                exit; 	 
		 }
	 else
	 {
		
		if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
		
		 
	 }

		
		
			
			
		}elseif($sthreenidhi_or_other == "2"){

		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/numer_notvalied";
		$agi-> stream_file($wfile, '#'); 
		mssql_close($link);
		$agi->Hangup();
		exit;

	 	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/SCSP_or_TSP_IWMP", 5000, 1);
	 	$SCSP_or_TSP=$res_dtmf ["result"];
		
		if($SCSP_or_TSP == "1"){
			$x='3';
		$SC_T_type="26";	
		$this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		exit;

		}elseif($SCSP_or_TSP == "2"){

			$x='3';
		$SC_T_type="27";	
		$this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		exit;

		}elseif($SCSP_or_TSP == "3"){
			$x='3';
		$this->IWMP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		exit;
		
		}else{
	
		if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
		
		}
		}
		}
		if($i=="2")
		{

	 $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/get_repaysheet_record", 5000, 1);
	 $repay=$res_dtmf ["result"];	
	 if($repay=='1')
	 {
		 
		$this->record_pay_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		$agi->Hangup();
	 }
	if($repay=='2')
	 {
		 $x=$x-1;
		 $this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		 mssql_close($link);
		 $agi->Hangup();
		 exit; 
	 }else
		{
		if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
		
		}
		}
		if($i=="3")
		{
		$x='3';
		$this->shg_name_disbursement($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		$agi->Hangup();
		}
		if($i=="4")
		{
	
		$x='3';
		mssql_close($link);
		$agi->Hangup();
		exit;
		
		}
		if($i=="5")
		{
		$x='3';
		mssql_close($link);
		$agi->Hangup();
	        exit;
		}
		if($i=="7")
		{
		$this->change_password($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		}
		if($i=="9")
		{
		$agi-> set_variable("callcenter","trasfer_to_callcenter"); 	
		$this->dial_agent($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		}else
		{
		if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
		
		}
	}
	
function short_term_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs)
{
		$test_vonumber=$GLOBALS['test_vonumber'];	
	  if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	 $shg_code_short=$shg_code_rs;
	 
	  $shg_dist_code=substr($void_code,2,2);
	  $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}
	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	$bank_name_rs= mssql_fetch_array(mssql_query("select Bank_Code from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code'"));
	$bank_name=$bank_name_rs['Bank_Code'];

	if($overdue_amt>0 && $overdue_amt<=10000)
	{
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
		$agi-> stream_file($wfile, '#');
		$is_over_due='Y';	
	}

	  if($overdue_amt>10000)
	{	
	if($x>='1')
		{
			
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->short_term_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	
	}

    $shg_grade_dist_code=substr($void_code,2,2);
	$shg_grade_mandal_code=substr($void_code,4,2);
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT(nolock) where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
	$mms_grade=$vo_grade_rs['GRADE'];
	
	if($mms_grade=='D')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_dgrade";
		   $agi-> stream_file($wfile, '#');
	           mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($mms_grade=='C')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_cgrade";
		   $agi-> stream_file($wfile, '#');
	           mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	$vo_grade_rs_rej=mssql_fetch_array(mssql_query("select GRADE from VO_CREDIT_LIMIT(nolock) where VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs_rej['GRADE'];

	if($vo_grade=='E')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
		   $agi-> stream_file($wfile, '#');
                   mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($vo_grade=='F')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
		   $agi-> stream_file($wfile, '#');
	           mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
		if($vo_grade=='D')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ind";
		   $agi-> stream_file($wfile, '#');
	           mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($vo_grade=='C')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inc";
		   $agi-> stream_file($wfile, '#');
		   mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	 if($status>='1')
	 { 
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  
	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  }
		if($status=="1")
		{	
	
	
	
		$shg_overdue=$this->shg_overdue($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$shg_code);
		
		$dateValue=date('Ymd');
		
		
			$max_shg_overdue=0;
	
	
		if($shg_overdue != "no_loans"){
		if($shg_overdue > $max_shg_overdue ){
			
		$message=" SHG Overdue $shg_overdue Greater than $max_shg_overdue ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
			
	 		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue_no_shg_access";
			   $agi-> stream_file($wfile, '#');  
			   
			   
	                   mssql_close($link);
			   $agi->Hangup();
			   exit;		
		}	
		}else{
		$message=" SHG Overdue $max_shg_overdue ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);		
		}
	
	
		
	$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
    $shg_active_stat=$shg_active_rs['IS_VALID'];
	if($shg_active_stat=='Y')
	{
	
	}
	else
	{
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied_old";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		  $this->short_term_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		  mssql_close($link);
		  $agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	
	         
	
	}	
		
		$total_deposit_rs=mssql_fetch_array(mssql_query("select sum(DEPOSITED_AMOUNT) as TOTAL_SUM from SHG_DEPOSIT_INFO(nolock) where SHG_ID='$shg_code' and DEPOSIT_TYPE='1'"));
					$shg_total_deposit=$total_deposit_rs['TOTAL_SUM'];			
					//$etime=date('Y-m-d');		  
					$startdate = '2012-11';
					$enddate = date('Y-m'); 
					$timestamp_start = strtotime($startdate);
					$timestamp_end = strtotime($enddate);
					$difference = abs($timestamp_end - $timestamp_start);
					$months = floor($difference/(60*60*24*30));
					$months_to_validate=$months-2;
					$shg_to_deposit=$months_to_validate*100;
					
					if($shg_total_deposit>=$shg_to_deposit)
					{
	
					}
				else{
	  				 if($x>='1')
					{
						$pending_samrudhi_amount=$shg_to_deposit-$shg_total_deposit;
						$pending_samrudhi_amount=$pending_samrudhi_amount+200;
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_amount";
						$agi-> stream_file($wfile, '#');
						$this->play_amount($shg_total_deposit,$agi);
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_deposited";
						$agi-> stream_file($wfile, '#');
						$this->play_amount($pending_samrudhi_amount,$agi);
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_pending";
						$agi-> stream_file($wfile, '#');
						 $x=$x-1;
			          $this->short_term_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
						mssql_close($link);
						$agi->Hangup();
		 				 exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	}
	
	if($shg_active_stat=='Y')
	{
	  $x=3;
	  $shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where   SHG_ID='$shg_code' and PROJECT_TYPE='11'");
	  $mem_pending_live=mssql_num_rows($shg_mem_rs_live);
	  
$shg_mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE='11' and  SHG_ID='$shg_code'");
$mem_rejected=mssql_num_rows($shg_mem_rej_rs);
	  
	   //other
	  
	  $shg_mem_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where   SHG_ID='$shg_code' and PROJECT_TYPE not in ('11') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'");
	  $mem_pending=mssql_num_rows($shg_mem_rs);	
	  
	  $shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE not in ('11')");
	  $mem_pending_live_sn=mssql_num_rows($shg_mem_rs_live);
	  
	  

	//total
	  $total_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code'");
	  $total_members=mssql_num_rows($total_rs);
	  
$mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11'  and  SHG_ID='$shg_code'");
$mem_rejected_tot=mssql_num_rows($mem_rej_rs);
	  

	  
	  if($mem_pending_live>=0 && ($mem_pending==0||$mem_pending_live_sn==0))
	  {	 
	  $member_limit=6-$mem_pending_live+$mem_rejected; 
	  }
	  else
	  {
		$member_limit=6-$mem_pending_live+$mem_rejected;
		$total_remain=9-$total_members+$mem_rejected_tot; 
		
		 if($total_remain<=$member_limit)
	   {
		  $member_limit=$total_remain;
	   }else
	   {
		 $member_limit=$member_limit;  
	   }
	   
	  }
	  
	if($member_limit>=1)
	{	
	$this->short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	mssql_close($link);
	$agi->Hangup();
	exit;
	}
	else{
		  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_6_loans", 5000, 1);
	      $more_res=$res_dtmf ["result"];
		  if($more_res==1)
		  {
		  $x=3;
		  $this->short_term_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		  $agi->Hangup();
		    }
			else
			{
		         mssql_close($link);
			 $agi->Hangup();
			 $agi->Hangup();
			 exit;
			}
		   
		   	
	}
	
 		
	}
	else
	{
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->short_term_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	        
			}	
		}
	
		if($status=="2")
		{
		$this->short_term_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->short_term_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->short_term_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	     }
		
	
	
	
}

function short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs) 
{	
	$test_vonumber=$GLOBALS['test_vonumber'];
	$length='2';
	$play_msg='two_digit_member_id';
	$type='short_term';
	$x='3';
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
  $member_mobile_num=$member_id_rs['MOBILE_NUM'];
  

	$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id'"));
	
	if($member_prev_loan_count>=1)
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$this->short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs) ;
	mssql_close($link);
	$agi->Hangup();
	exit;	
	}
	
	if(strlen($member_mobile_num) == 10){
		
	}else{
	$length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    //$mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO  where  MOBILE_NUM='$member_mobile'"));
	$mobile_count=0;
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}else{	
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
	mssql_close($link);
	$agi->Hangup();	
	exit;	
	}
	
	}
  $db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
	 
		$db_filed="reason";
		$type='why_loan';
		$length='5';
		$play_msg="member_loan_reason_new";
		$x='3';
  $reason_loan =$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
  	 
	 
 
 if(($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Emergency Needs/Health'||$reason_loan=='Education'||$reason_loan=='Marriage'||$reason_loan=='Weavers') && strlen($member_short_code)==2 && $member_prev_loan_count=='0' && $loan_amount<=12000)
 {
	 
$PROJECT_TYPE='11';	

$vo_dist_code=substr($void_code,2,2);
$vo_mandal_code=substr($void_code,4,2);


//DISTRICT_ID,MANDAL_ID
$vo_dist_search=substr($void_code,0,4);
// VO_ID like '$vo_dist_search%' 


	$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select TOTOAL_FUND from DISTRICT_CREDIT_LIMIT(nolock)  where DISTRICT_ID='$vo_dist_code'"));
    $vo_actual_credit_lt_term = $vo_credit_pop_rs['TOTOAL_FUND'];
	
	
$vo_term_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID like '$vo_dist_search%' and PROJECT_TYPE='11'"));

		$vo_term_applied=$vo_term_applied_rs[0];
		//$vo_term_applied=$vo_term_applied+$loan_amount;
		
//adding rejected amount
		
	
$rej_id=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE='11' and DISTRICT_ID='$vo_dist_code'"));
$rej_amt_t2=$rej_id[0];	
	
	$vo_term_applied=$vo_term_applied-$rej_amt_t2;
	
	$vo_credit_lt_term=$vo_actual_credit_lt_term-$vo_term_applied;
	
	
$rej_shg=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE ='11' and SHG_ID='$shg_code'"));
$rej_amt_shg_term=$rej_shg[0];	
	 
	 
	 
$shg_term_lt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE='11'"));

		$shg_term_lt_max=$shg_term_lt_rs[0];
		$shg_term_lt_max=$shg_term_lt_max+$loan_amount-$rej_amt_shg_term;
			
$shg_term_lt_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE='11' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));

		$shg_term_lt_tcs=$shg_term_lt_tcs_rs[0];
		$shg_term_lt_tcs=$shg_term_lt_tcs+$loan_amount-$rej_amt_shg_term;
			
 $duration='12';
 $etime=date('Y-m-d H:i:s');	
// $PROJECT_TYPE='2';	  
 $member_type='N';
 //&& $vo_credit_lt_sn>=$fund_sn
 
  if($loan_amount>=1000 && $loan_amount<=12000 && $shg_term_lt_max<=72000 && $vo_credit_lt_term>=$loan_amount  && $shg_term_lt_tcs<=72000)
  {   
 //$marriage_amt='0';
  $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select SHG_IVRS_LOAN_NO from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE='11' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;
 // $reason_loan_code has to be added
 
if($caller!=$test_vonumber)
{ 
mssql_query("insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,COMBINED_ID,HLP_CODE,IS_OVERDUE,OVERDUE_ID,SHORT_CODE,MEMBER_ID,FUND_SN,FUND_POP) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$ca_id_new','$reason_loan_code','$is_over_due','$due_id_lst','$member_short_code','$member_id','$fund_sn','$fund_pop')");
}

$current_limit=$vo_credit_lt_term-$loan_amount;
//mssql_query("update IVRS_VO_POP_CREDIT_LIMIT set CURRENT_CREDIT_LIMIT='$current_limit'  where vo_id='$void_code'");
$vo_credit_limit=$current_limit;


  $member_limit=$member_limit-1; 
   if($member_limit>=1 && $vo_credit_limit>=1000)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{  
	$this->short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	}
	else
	{
	  if($vo_credit_limit>=1000)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{	
	$this->short_term_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}

 }
 else
 {
	   if($x>='1')
		{
			if($loan_amount>50000)		
			{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_50000";
	      $agi-> stream_file($wfile, '#');	
			}
			else
			{
 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
			}
			$x=$x-1;
	$this->short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
	         }
 
   }
	
 }
 else
 {
	    if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
			$x=$x-1;
	$this->short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
	         }
 }	
	
}
	
	
	
	function loan_type_krushe($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$vo_shgs)
	{
	$test_vonumber=$GLOBALS['test_vonumber'];		
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/krushe_request_type", 8000, 1);
	$krushe_req=$res_dtmf ["result"];
	
	    $krushe_stat_rs=mssql_fetch_array(mssql_query("select IS_KRUSHE,IS_KRUSHEMART from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
		$krushe_stat=$krushe_stat_rs['IS_KRUSHE'];
		$krushe_mart_stat=$krushe_stat_rs['IS_KRUSHEMART'];
		
	if($krushe_req=='1' && $krushe_mart_stat=='Y')	
		{
			$krushe_type="MART";
			$this->short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
			$agi->Hangup();
                exit;
			}
			elseif($krushe_req=='2' && $krushe_stat=='Y')
			{
				$krushe_type="PRODUCER";
				$this->short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
				$agi->Hangup();
                exit;
				}
				
				elseif($krushe_req=='3')
			{
				$krushe_type="KNOWLEDGE";
				$this->short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
				$agi->Hangup();
                exit;
				}
				elseif($krushe_req=='4')
			{
				$krushe_type="NEW_ENTERPRISE";
				$this->short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
				$agi->Hangup();
                exit;
				}
				elseif($krushe_req=='5')
			{
				$krushe_type="EXISTING_ENTERPRISE";
				$this->short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
				$agi->Hangup();
                exit;
				}
			else
			{
		if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->loan_type_krushe($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
		
		
				
				
			}
			
			
	}
	
	function project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
		$dateValue=date('YmdHis');
		if(1 == 2)
		{
		
		}
		else
		{
		if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/stop_diary_loan";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/stop_diary_loan";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
		}
			
		
		
		
		
		
		
		
			 	$vo_samrudhi=mssql_fetch_array(mssql_query("select vo_id from samrudhi_percentage() where samrudhi_percentage>=50 and VO_ID='$void_code'"));
	$vs_vo_id=$vo_samrudhi['vo_id'];
	

		if($vs_vo_id == $void_code)
			{
				
				}else
		{
		
				
			if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_80";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }	
				
				
		
			
		}

	      if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}

	
	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	$bank_name_rs= mssql_fetch_array(mssql_query("select Bank_Code from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code'"));
	$bank_name=$bank_name_rs['Bank_Code'];	

	if($overdue_amt>0 && $overdue_amt<=10000)
	{
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
		$agi-> stream_file($wfile, '#');
		$is_over_due='Y';	
	}
	 
	
	  if($overdue_amt>10000)
	{	
	if($x>='1')
		{
			
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
		$agi-> stream_file($wfile, '#');
		/*
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/proceed_through_option8";
		$agi-> stream_file($wfile, '#');
		*/
		$x=$x-1;
		$this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	
	}

    $shg_grade_dist_code=substr($void_code,2,2);
	$shg_grade_mandal_code=substr($void_code,4,2);
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT(nolock) where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
	$mms_grade=$vo_grade_rs['GRADE'];
	
	if($mms_grade=='E')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_egrade";
		   $agi-> stream_file($wfile, '#');
		   mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($mms_grade=='F')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
		   $agi-> stream_file($wfile, '#');
		   mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	//change table here
    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select GRADE,CREDIT_LIMIT from PROJECT_VO_CREDIT_LIMIT(nolock) where VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs_rej['GRADE'];
	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
	
		if($vo_grade=='E')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
		   $agi-> stream_file($wfile, '#');
		   mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($vo_grade=='F')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
		   $agi-> stream_file($wfile, '#');
		   mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	if($vo_actual_credit=='0')
	{
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   $agi-> stream_file($wfile, '#');
		   mssql_close($link);
		   $agi->Hangup();
		   exit;
	
		}	
	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  
	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  }
		if($status=="1")
		{	
		
			$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
    $shg_active_stat=$shg_active_rs['IS_VALID'];
	if($shg_active_stat=='Y')
	{
	
	}
	else
	{
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied_old";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		   $this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		   mssql_close($link);
		   $agi->Hangup();
		   exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	
	         
	
	}
	
$vo_name_array=mssql_query("select * from vo_info(nolock) where TRANS_VO_ID='$void_code' order by CREATED_DATE  desc");
$vo_name_array=mssql_fetch_array($vo_name_array);
$dist_id=$vo_name_array['DISTRICT_ID'];
$mandal_id=$vo_name_array['MANDAL_ID'];
	
$shg_samrudhi_query="select * from SHG_SAMRUDHI_PERCENTAGE() where shg_id = '$shg_code'";
$total_deposit_rs=mssql_fetch_array(mssql_query($shg_samrudhi_query));
$check_shg_samrudhi=$total_deposit_rs['DEPOSIT_PER'];
 
					if($check_shg_samrudhi > 99)
					{
					$message="SHG SAMRUDHI SUCCESS";
                       			$this->log_ivr($ivr_call_id,$message);
									}
				else{
	  				 if($x>='1')
					{

						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_denied";
						$agi-> stream_file($wfile, '#');
						$x=$x-1;
			            $this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
						$agi->Hangup();
		 				 exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	}
		

	if( $shg_active_stat=='Y')
	{


//current type

$shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where   SHG_ID='$shg_code' and PROJECT_TYPE in ('43')");
$mem_pending_live=mssql_num_rows($shg_mem_rs_live);
	  
$shg_mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('43') and  SHG_ID='$shg_code'");
$mem_rejected=mssql_num_rows($shg_mem_rej_rs);

$shg_mem_repaid_rs=mssql_query("select * from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where SHG_ID='$shg_code' and IS_CLOSED='1'");
$mem_repaid=mssql_num_rows($shg_mem_repaid_rs);


//other types
	  $shg_mem_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where   SHG_ID='$shg_code' and PROJECT_TYPE not in  ('43') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'");
	  $mem_pending=mssql_num_rows($shg_mem_rs);	

	  $shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE not in ('43')");
	  $mem_pending_live_sn=mssql_num_rows($shg_mem_rs_live);
	  
	 $mem_rej_others=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE not in  ('43') and  SHG_ID='$shg_code'");
	  $mem_rejected_others=mssql_num_rows($mem_rej_others); 
	  
	   $mem_pending=$mem_pending-$mem_rejected_others;
	   $mem_pending_live_sn=$mem_pending_live_sn-$mem_rejected_others;
	   
//total
$mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and  SHG_ID='$shg_code'");
$mem_rejected_tot=mssql_num_rows($mem_rej_rs);

	  $total_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code'");
	  $total_members=mssql_num_rows($total_rs);
	  
	  if($mem_pending_live>=0 && ($mem_pending==0||$mem_pending_live_sn==0))
	  {	 
	  $member_limit=9-$mem_pending_live+$mem_rejected+$mem_repaid; 
	  }
	  else
	  {
		$member_limit=9-$mem_pending_live+$mem_rejected+$mem_repaid;
		$total_remain=9-$total_members+$mem_rejected_tot+$mem_repaid; 
		
		 if($total_remain<=$member_limit)
	   {
		  $member_limit=$total_remain;
	   }else
	   {
		 $member_limit=$member_limit;  
	   }
	   
	  }		
	  
	
	 	
	if($member_limit>=1)
	{	
	$this->project_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	mssql_close($link);
 	$agi->Hangup();
	exit;
	}
	else{
		  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_6_loans", 5000, 1);
	      $more_res=$res_dtmf ["result"];
		  if($more_res==1)
		  {
		  $x=3;
		  $this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		  mssql_close($link);
		  $agi->Hangup();
		  exit;
		    }
			else
			{
	                 mssql_close($link);
			 $agi->Hangup();
			 $agi->Hangup();
			 exit;
			}
		   
		   	
	}
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	     
		}
	
		}
	
		if($status=="2")
		{
		$this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
	     }
		
	

	}
	
	
function project_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs)
{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$length='2';
	$play_msg='two_digit_member_id';
	$type='project';
	$x='3';
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,ACTIVITY,KRUSHE_AMOUNT,SP_ACTIVITY,MOBILE_NUM from SHG_MEMBER_INFO(nolock)  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $ACTIVITY=$member_id_rs['ACTIVITY'];
 $KRUSHE_AMOUNT=$member_id_rs['KRUSHE_AMOUNT'];
  $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 
 $member_outstanding_rs=mssql_fetch_array(mssql_query("select OUTSTANDING from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code'"));
 $member_outstanding=$member_outstanding_rs['OUTSTANDING'];
 
 if($ACTIVITY=='43' && $member_outstanding < 5000 )
 {
	 //change prompt
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/project_loan";
$agi-> stream_file($wfile, '#');
 }
 else
 {
	 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/not_eligible_for_project_loan";
	$agi-> stream_file($wfile, '#');
	$this->project_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;	 
 }
 
 	$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and PROJECT_TYPE='43'"));
	
	if($member_prev_loan_count>=1)
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$this->project_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;	
	}
  
    	if(strlen($member_mobile_num) == 10){
		
	}else{
	$length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
		mssql_close($link);
	$agi->Hangup();	
	exit;	
	}
	
	}
	
  
$db_filed="reason";
		$type='project';
		$length='5';
		//$play_msg="member_loan_reason_new";
		$play_msg="project_loan_reason_diary";
		$x='3';
$reason_loan =$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);	
	
	
	
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member";
$agi-> stream_file($wfile, '#');
$this->play_amount($KRUSHE_AMOUNT,$agi);
$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
$agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/applying_loan";
$agi-> stream_file($wfile, '#');

$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/krushe_amount_type", 8000, 1);
$krushe_req_amount=$res_dtmf ["result"];
if($krushe_req_amount=='1')
{
$loan_amount=$KRUSHE_AMOUNT;	
}

else{
		if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->project_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }	
				
			}	
	

	if($loan_request=='YES')
	{
  $db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->krushe_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$KRUSHE_AMOUNT);
	}
		
	

		
 
 //$hlp_loan_rs=mssql_fetch_array(mssql_query("SELECT ACTIVITY_NAME FROM  KRUSHE_ACTIVITY_MASTER where ACTIVITY_ID='$reason_loan_code'"));
 //$reason_loan=$hlp_loan_rs['ACTIVITY_NAME'];	

	


//KRUSHE_AMOUNT
 //echo $member_short_code."prev --".$member_prev_loan_count."loan--".$loan_amount."krushe".$KRUSHE_AMOUNT;
 if(strlen($member_short_code)==2 && $member_prev_loan_count=='0' && $loan_amount<=$KRUSHE_AMOUNT && $loan_amount>0 && strlen($reason_loan)!=0)
 {
	 
$PROJECT_TYPE=$ACTIVITY;

	
	$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select CREDIT_LIMIT from PROJECT_BASED_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
    $vo_actual_credit_lt_krushe = $vo_credit_pop_rs['CREDIT_LIMIT'];
	
//$vo_krushe_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code'  and PROJECT_TYPE in ('23')"));
$vo_krushe_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs)  and PROJECT_TYPE in ('43')"));

		$vo_krushe_applied=$vo_krushe_applied_rs[0];
		//$vo_krushe_applied=$vo_krushe_applied+$loan_amount;
		
//adding rejected amount
		
	
$rej_id=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('43') and shg_id in ($vo_shgs)"));
$rej_amt_t2=$rej_id[0];	
	
	$vo_krushe_applied=$vo_krushe_applied-$rej_amt_t2;
	
	$vo_credit_lt_krushe=$vo_actual_credit_lt_krushe-$vo_krushe_applied;
	
	
$rej_shg=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('43')  and SHG_ID='$shg_code'"));
$rej_amt_shg_krushe=$rej_shg[0];	
	 
$shg_krushe_lt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('43')"));

		$shg_krushe_lt_max=$shg_krushe_lt_rs[0];
		$shg_krushe_lt_max=$shg_krushe_lt_max+$loan_amount-$rej_amt_shg_krushe;
			
$shg_krushe_lt_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('43') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));

		$shg_krushe_lt_tcs=$shg_krushe_lt_tcs_rs[0];
		$shg_krushe_lt_tcs=$shg_krushe_lt_tcs+$loan_amount-$rej_amt_shg_krushe;
			
 $duration='36';
 $etime=date('Y-m-d H:i:s');	
// $PROJECT_TYPE='2';	  
 $member_type='N';
 //&& $vo_credit_lt_sn>=$fund_sn
 
 $shg_krushe_actual_lt=mssql_fetch_array(mssql_query("select SUM(KRUSHE_AMOUNT) from  SHG_MEMBER_INFO(nolock)  where SHG_ID='$shg_code'"));
 $shg_krushe_lt=$shg_krushe_actual_lt[0];
 
 // if($loan_amount>=1000 && $loan_amount<=$KRUSHE_AMOUNT && $shg_krushe_lt_max<=$shg_krushe_lt && $vo_credit_lt_krushe>=$loan_amount  && $shg_krushe_lt_tcs<=$shg_krushe_lt)
  
  if($loan_amount>=1000 && $loan_amount<=$KRUSHE_AMOUNT )
  {   
 //$marriage_amt='0';
  $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select SHG_IVRS_LOAN_NO from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE in ('43') order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;
 // $reason_loan_code has to be added
 
if($caller!=$test_vonumber)
{ 
mssql_query("insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,COMBINED_ID,HLP_CODE,IS_OVERDUE,OVERDUE_ID,SHORT_CODE,MEMBER_ID,FUND_SN,FUND_POP) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$ca_id_new','$reason_loan_code','$is_over_due','$due_id_lst','$member_short_code','$member_id','$fund_sn','$fund_pop')");
}

$current_limit=$vo_credit_lt_krushe-$loan_amount;
//mssql_query("update IVRS_VO_POP_CREDIT_LIMIT set CURRENT_CREDIT_LIMIT='$current_limit'  where vo_id='$void_code'");
$vo_credit_limit=$current_limit;


  $member_limit=$member_limit-1; 
   if($member_limit>=1 && $vo_credit_limit>=1000)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan_project", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{  
	$this->project_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	}
	else
	{
	  if($vo_credit_limit>=1000)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{	
	$this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
	mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}


 }
 else
 {
	   if($x>='1')
		{
			if($loan_amount>50000)		
			{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_50000";
	      $agi-> stream_file($wfile, '#');	
			}
			else
			{
 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
			}
			$x=$x-1;
	$this->project_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
	            mssql_close($link);
		    $agi->Hangup();
		    exit;
	         }
 
   }
	
 }
 else
 {
	    if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
			$x=$x-1;
	$this->project_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
	         }
 }	
}
	

	
	
	
	function krushe_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs)
	{	
	$test_vonumber=$GLOBALS['test_vonumber'];
	$length='2';
	$play_msg='two_digit_member_id';
	$type=$krushe_type;
	$x='3';
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
	$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code' "));
 $member_id=$member_id_rs['MEMBER_ID'];
   $member_mobile_num=$member_id_rs['MOBILE_NUM'];
	 $SP_ACTIVITY=$member_id_rs['SP_ACTIVITY'];	

	
	if($krushe_type=='MART')
	{
	$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,ACTIVITY,KRUSHE_AMOUNT from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code' and ACTIVITY='9'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $ACTIVITY=$member_id_rs['ACTIVITY'];
 $KRUSHE_AMOUNT=$member_id_rs['KRUSHE_AMOUNT'];
	}
	elseif($krushe_type=='PRODUCER')
	{
	$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,ACTIVITY,KRUSHE_AMOUNT from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code'  and SHORT_CODE='$member_short_code' and ACTIVITY='8'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $ACTIVITY=$member_id_rs['ACTIVITY'];
 $KRUSHE_AMOUNT=$member_id_rs['KRUSHE_AMOUNT'];
	}
	//KNOWLEDGE
	elseif($krushe_type=='KNOWLEDGE')
	{
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,ACTIVITY,KRUSHE_AMOUNT from SHG_MEMBER_INFO(nolock)  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $ACTIVITY=$member_id_rs['ACTIVITY'];
 $KRUSHE_AMOUNT=$member_id_rs['KRUSHE_AMOUNT'];
 if($ACTIVITY=='16')
 {
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/chetana_loan";
$agi-> stream_file($wfile, '#');
 }elseif($ACTIVITY=='17')
 {
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/prakrithi_loan";
$agi-> stream_file($wfile, '#');
 }else
 {
	 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/not_eligible_for_knowledge_loan";
	$agi-> stream_file($wfile, '#');
	$this->krushe_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	mssql_close($link);
	$agi->Hangup();
	exit;	 
 }
  
	}elseif($krushe_type=='NEW_ENTERPRISE')
	{
	$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,ACTIVITY,KRUSHE_AMOUNT from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code'   and SHG_ID='$shg_code'  and SHORT_CODE='$member_short_code' and ACTIVITY='30'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $ACTIVITY=$member_id_rs['ACTIVITY'];
 $KRUSHE_AMOUNT=$member_id_rs['KRUSHE_AMOUNT'];
	}elseif($krushe_type=='EXISTING_ENTERPRISE')
	{
	$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,ACTIVITY,KRUSHE_AMOUNT from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code'   and SHG_ID='$shg_code'  and SHORT_CODE='$member_short_code' and ACTIVITY='31'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $ACTIVITY=$member_id_rs['ACTIVITY'];
 $KRUSHE_AMOUNT=$member_id_rs['KRUSHE_AMOUNT'];
	}

	
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member";
$agi-> stream_file($wfile, '#');
$this->play_amount($KRUSHE_AMOUNT,$agi);
$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
$agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/applying_loan";
$agi-> stream_file($wfile, '#');

$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/krushe_amount_confirm", 8000, 1);
$krushe_req_amount=$res_dtmf ["result"];
if($krushe_req_amount=='1' && $ACTIVITY != '' && $KRUSHE_AMOUNT>0)
{
$loan_amount=$KRUSHE_AMOUNT;	
}
/*elseif($krushe_req_amount=='2')
{
$loan_request='YES';	
}*/
else{
		if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->krushe_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }	
				
			}	
	
	
	//$vo_id_mandal=substr($void_code,0,6);
	
	$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id'"));
	
	if($member_prev_loan_count>=1)
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$this->krushe_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	mssql_close($link);
	$agi->Hangup();
	exit;	
	}

    	if(strlen($member_mobile_num) == 10){
		
	}else{
	$length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
	mssql_close($link);
	$agi->Hangup();	
	exit;	
	}
	
	}
	
	
	if($krushe_type=='PRODUCER')
	{
		$db_filed="reason";
		$type='why_loan';
		$length='5';
		$play_msg="krushe_activity_code";
		$x='3';
 $reason_loan_code=$this->krushe_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
 
 $hlp_loan_rs=mssql_fetch_array(mssql_query("SELECT ACTIVITY_NAME FROM  KRUSHE_ACTIVITY_MASTER where ACTIVITY_ID='$reason_loan_code'"));
 $reason_loan=$hlp_loan_rs['ACTIVITY_NAME'];	
	}
	
	if($loan_request=='YES')
	{
  $db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->krushe_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$KRUSHE_AMOUNT);

 if(strlen($member_short_code)==2 && $member_prev_loan_count=='0' && $loan_amount<=$KRUSHE_AMOUNT && $loan_amount>0)
 {
	 

$PROJECT_TYPE=$ACTIVITY;

	
	$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select CREDIT_LIMIT from KRUSHE_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
    $vo_actual_credit_lt_krushe = $vo_credit_pop_rs['CREDIT_LIMIT'];
	
$vo_krushe_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs)  and PROJECT_TYPE in ('8','9','16','17','30','31')"));

		$vo_krushe_applied=$vo_krushe_applied_rs[0];
		//$vo_krushe_applied=$vo_krushe_applied+$loan_amount;
		
//adding rejected amount
		
	
$rej_id=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('8','9','16','17','30','31') and shg_id in ($vo_shgs)"));
$rej_amt_t2=$rej_id[0];	
	
	$vo_krushe_applied=$vo_krushe_applied-$rej_amt_t2;
	
	$vo_credit_lt_krushe=$vo_actual_credit_lt_krushe-$vo_krushe_applied;
	
	
$rej_shg=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('8','9','16','17','30','31') and VO_ID='$void_code' and SHG_ID='$shg_code'"));
$rej_amt_shg_krushe=$rej_shg[0];	
	 
$shg_krushe_lt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('8','9','16','17','30','31')"));

		$shg_krushe_lt_max=$shg_krushe_lt_rs[0];
		$shg_krushe_lt_max=$shg_krushe_lt_max+$loan_amount-$rej_amt_shg_krushe;
			
$shg_krushe_lt_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('8','9','16','17','30','31') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));

		$shg_krushe_lt_tcs=$shg_krushe_lt_tcs_rs[0];
		$shg_krushe_lt_tcs=$shg_krushe_lt_tcs+$loan_amount-$rej_amt_shg_krushe;
			
 $duration='36';
 $etime=date('Y-m-d H:i:s');	
// $PROJECT_TYPE='2';	  
 $member_type='N';
 //&& $vo_credit_lt_sn>=$fund_sn
 
 $shg_krushe_actual_lt=mssql_fetch_array(mssql_query("select SUM(KRUSHE_AMOUNT) from  SHG_MEMBER_INFO(nolock)  where SHG_ID='$shg_code'"));
 $shg_krushe_lt=$shg_krushe_actual_lt[0];
 
  if($loan_amount>=1000 && $loan_amount<=$KRUSHE_AMOUNT && $shg_krushe_lt_max<=$shg_krushe_lt && $vo_credit_lt_krushe>=$loan_amount  && $shg_krushe_lt_tcs<=$shg_krushe_lt)
  {   
 //$marriage_amt='0';
  $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select SHG_IVRS_LOAN_NO from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE in ('8','9','16','17','30','31') order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;
 // $reason_loan_code has to be added
 
if($caller!=$test_vonumber)
{ 
mssql_query("insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,COMBINED_ID,HLP_CODE,IS_OVERDUE,OVERDUE_ID,SHORT_CODE,MEMBER_ID,FUND_SN,FUND_POP) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$ca_id_new','$reason_loan_code','$is_over_due','$due_id_lst','$member_short_code','$member_id','$fund_sn','$fund_pop')");
}

$current_limit=$vo_credit_lt_krushe-$loan_amount;
//mssql_query("update IVRS_VO_POP_CREDIT_LIMIT set CURRENT_CREDIT_LIMIT='$current_limit'  where vo_id='$void_code'");
$vo_credit_limit=$current_limit;


  $member_limit=$member_limit-1; 
   if($member_limit>=1 && $vo_credit_limit>=1000)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{  
	$this->krushe_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	}
	else
	{
	  if($vo_credit_limit>=1000)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{	
	$this->short_term_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	if($krushe_type == "KNOWLEDGE"){
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_vo_ac";		
		}
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
	mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}


/*
 $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
  if(intval($value)=='1')
	{
  $this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
	}
	else
	{
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	if(intval($value_shg)=='1')
	{
	$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	
	}
	*/
	   

 }
 else
 {
	   if($x>='1')
		{
			if($loan_amount>50000)		
			{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_50000";
	      $agi-> stream_file($wfile, '#');	
			}
			else
			{
 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
			}
			$x=$x-1;
	$this->krushe_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
	         }
 
   }
	
 }
 else
 {
	    if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
			$x=$x-1;
	$this->krushe_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
	         }
 }
 
 
		



		
	}
	
	}
	
	function record_pay_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit)
	{
		
	$test_vonumber=$GLOBALS['test_vonumber'];
$play_msg='loan_paid_month';
$loan_pay_month=$this->get_shg_deposit_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$play_msg);
	
//$play_msg='shg_loan_month';
//$loan_pay_month=$this->get_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$play_msg);

 $month_now=date('m');
	if($loan_pay_month<=$month_now)
	{
	$year_now=date('Y');
	}
	else
	{
	$year_now=date('Y')-1;
	}
	$loan_pay_date='15';
if(checkdate($loan_pay_month,$loan_pay_date,$year_now))
{
}
else
{ 
	if($x>='1')
		{
			//echo "cuming hererere";
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->record_pay_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		mssql_close($link);
		exit;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
}
		
		
//$loan_pay_month='00';	
//$year_now='0000';

		
	
   $record="/var/lib/asterisk/sounds/loan/record_msg";
   $agi-> stream_file($record, '#');
   $start_t=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
   $record_file=$this->ivrflow_loan_record($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id);
          $end_t=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
          $callduration=$end_t-$start_t;
          $endtime=date('Y-m-d H:i:s',$end_t);
         $starttime=date('Y-m-d H:i:s',$starttime);
   $stat_bank_credit_rs=mssql_query("select BANK_REF_NO  from  VO_CREDIT_INFO(nolock)  where VO_ID='$void_code' and VO_REPAY_STATUS='Open'");
   $stat_bank_credit= mssql_num_rows($stat_bank_credit_rs);
   if($stat_bank_credit>='1')
   {
	$status="N";   
   }
   else
   {
	$status="NBD";      
   }
 
	  $DIST_ID=substr($void_code,2,2);
      $MANDAL_ID=substr($void_code,4,2);
		
         if($record_file==true)
        { 
		$link = mysql_connect("10.10.18.61","root", "evol@9944")
                or die("Data base connection failed");
                 mysql_select_db("sthreenidhi")
                         or die("data base open failed");
          $status="N";
		  if($callduration<'25')
		  {
			 $status="SF";
			 $lst_updated=date('Y-m-d H:i:s');  
		  }
          $wfile="/var/lib/asterisk/sounds/loan/sucess";
          $agi-> stream_file($wfile, '#');
	  if($caller!=$test_vonumber)
	  {
          mysql_query("insert into IVRS_REPAYMENT_RECORDINGS(CALLER,EXTENSION,CREATED_DATE,UNIQUE_ID,VOICE_FILE,UPDATE_STATUS,IVRS_ID,DURATION,VO_ID,DIST_ID,MANDAL_ID,LAST_UPDATEED_ON,REPAY_MONTH,REPAY_YEAR)values('$caller','$exten',now(),'$unique_id','$record_file','$status','$ivr_call_id','$callduration','$void_code','$DIST_ID','$MANDAL_ID','$lst_updated','$loan_pay_month','$year_now')");
	  }
		  mysql_close($link);
                  mssql_close($link);
		  $agi->Hangup();
		  exit;		   
         //$repeat="/var/lib/asterisk/sounds/loan/loan_repeat";
       //  $res_dtmf=$agi->get_data("$repeat",4000,1);
         //$repeat_result=$res_dtmf["result"];
      }
	  /*
         if($repeat_result=="1") 		  
         {
          $this->ivrflow_sthreenidhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id);
         }
         else
         {
           $wfile="/var/lib/asterisk/sounds/loan/thanku";
           $agi-> stream_file($wfile, '#');
         }   
		 */
	
	}
	function ivrflow_loan_record($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id)
{
            $test_vonumber=$GLOBALS['test_vonumber'];
            $recordfilename=$caller."-".$ivr_call_id."-".date('YmdHis');
            $filer="/tmp/".$recordfilename ;
            $i=2;
	    $retry=0;
	    while($i!='1' && $retry<'2')
	    {	
		$agi->record_file($filer, 'wav', '#', '640000', '0', 1, '10');
		//$agi->stream_file($filer,'#');
		$prompt="/var/lib/asterisk/sounds/loan/toconfirm1otherwise2";
		$res_dtmf=$agi->get_data($prompt, 5000, 1);
		$i=$res_dtmf ["result"];
		if($i=='1')
		{
                  $l="lame /tmp/".$recordfilename.".wav  /var/www/Beta_New/sthreenidhi/repayment/mp3/".$recordfilename.".mp3";
                   exec($l);

		   $move="mv /tmp/".$recordfilename.".wav /var/lib/asterisk/sounds/loan/loan_recording/".$recordfilename.".wav";
		   exec($move);
		}
	     elseif($i=='2')
		{
		  $i=2;
	        }
		$retry++;
	    }
		if($i != "1" && $retry=='2')
		{
		  $prompt="/var/lib/asterisk/sounds/loan/retry_exceeded";
		  $agi-> stream_file($prompt, 250, 1);
                  mssql_close($link);
		  exit;
		}
             return $recordfilename;			
}
	
	function shg_deposits_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/deposits_menu", 3000, 1);
	  $opt=$res_dtmf ["result"];
	if($opt=='1')
	{
	$x=3;
	$this->shg_complete_deposit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code);
	}
	if($opt=='2')
	{
	$x=3;
	$this->shg_partial_deposit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code);
	}
	/*
	if($opt=='3')
	{
	$x=3;
	$this->shg_backlog_deposit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code);
	}
	*/
	else
	{
	
		if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		mssql_close($link);
		exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
		
		
	}
	}
	
	
	
function shg_complete_deposit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code)
{

		$test_vonumber=$GLOBALS['test_vonumber'];
 $vo_shg_count = mssql_num_rows(mssql_query("select * from SHG_INFO(nolock)  where VO_ID='$void_code'"));
 $amount_to_pay=$vo_shg_count*100;
        $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/amount_to_pay_all_shg";
        $agi-> stream_file($wfile, '#');
		$this->play_amount($amount_to_pay,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');		
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_paid_press1", 5000, 1);
	$value=$res_dtmf ["result"];
 if($value=='1')
 {
$play_msg='shg_deposit_month';
$deposit_month=$this->get_shg_deposit_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$play_msg);

$month_now=date('m');
	if($deposit_month<=$month_now)
	{
	$year_now=date('Y');
	}
	else
	{
	$year_now=date('Y')-1;
	}	
	$loan_pay_date='15';
$chk_stat = mssql_num_rows(mssql_query("select * from IVRS_VO_DEPOSITS(nolock)  where VO_ID='$void_code' and DEPOSIT_MONTH='$deposit_month'  and DEPOSIT_YEAR='$year_now'"));	
	if($chk_stat=='0')
	{		
if(checkdate($deposit_month,$loan_pay_date,$year_now))
{
$current_time=date('Y-m-d H:i:s');
$unpaid_count='0';

if($caller!=$test_vonumber)
{
mssql_query("insert into IVRS_VO_DEPOSITS(VO_ID,MOBILE,CREATED_DATE,AMOUNT_PAID,SHG_COUNT_UNPAID,SHG_COUNT_TOTAL,DEPOSIT_MONTH,DEPOSIT_YEAR,IVRS_ID,IVRS_CALL_ID,SHG_COUNT_UNPAID_STATUS) values('$void_code','$caller','$current_time','$amount_to_pay','$unpaid_count','$vo_shg_count','$deposit_month','$year_now','$ivr_call_id','$unique_id','Y')");
}
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/successfully_entered";
	 $agi-> stream_file($wfile, '#');
		mssql_close($link);
	$agi->Hangup();
exit;
}
  else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
  $this->shg_complete_deposit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code);
  mssql_close($link);
  $agi->Hangup();
  exit;
  
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
	         }
	   
	}
	}
	else
	{

	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/already_entered_details";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
	}
  }
else
    {
   //$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/collect_amount";
		//$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;

     }
}


function shg_partial_deposit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code)
{
		$test_vonumber=$GLOBALS['test_vonumber'];
$vo_shg_count = mssql_num_rows(mssql_query("select * from SHG_INFO(nolock)  where VO_ID='$void_code'"));
$play_msg='shg_unpaid_count';
$length='3';
$x=3;
$shg_unpaid_count=$this->get_number($agi,$x,$language,$db_filed,$type,$length,$play_msg,$vo_shg_count);

$unpaid_count=$vo_shg_count-$shg_unpaid_count;
$amount_to_pay=$unpaid_count*100;
 
        $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/amount_remain_shg";
        $agi-> stream_file($wfile, '#');
		$this->play_amount($amount_to_pay,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');		
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_paid_press1", 5000, 1);
	$value=$res_dtmf ["result"];
 if($value=='1')
 {
$play_msg='shg_unpaid_month';
$deposit_month=$this->get_shg_deposit_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$play_msg);

$month_now=date('m');
	if($deposit_month<=$month_now)
	{
	$year_now=date('Y');
	}
	else
	{
	$year_now=date('Y')-1;
	}
		
	$loan_pay_date='15';
$chk_stat = mssql_num_rows(mssql_query("select * from IVRS_VO_DEPOSITS(nolock)  where VO_ID='$void_code' and DEPOSIT_MONTH='$deposit_month' and DEPOSIT_YEAR='$year_now' and SHG_COUNT_UNPAID_STATUS='Y'"));
if($chk_stat=='0')
{


$chk_stat_prev = mssql_num_rows(mssql_query("select * from IVRS_VO_DEPOSITS(nolock)  where VO_ID='$void_code' and DEPOSIT_MONTH='$deposit_month' and DEPOSIT_YEAR='$year_now' and SHG_COUNT_UNPAID_STATUS='N'"));
if($chk_stat_prev!='0')
{

$chk_prev_cnt = mssql_num_rows(mssql_query("select * from IVRS_SHG_DEPOSITS(nolock)  where VO_ID='$void_code' and DEPOSIT_MONTH='$deposit_month' and DEPOSIT_YEAR='$year_now'"));

$call_id_rs=mssql_query("select * from  IVRS_VO_DEPOSITS(nolock) where  VO_ID='$void_code' and DEPOSIT_MONTH='$deposit_month' and DEPOSIT_YEAR='$year_now'  order by CREATED_DATE  desc");
	$ivr_call=mssql_fetch_array($call_id_rs);
	$vo_depost_id=$ivr_call['ID'];
	$unpaid_cnt=$ivr_call['SHG_COUNT_UNPAID'];
	$shg_unpaid_count=$unpaid_cnt-$chk_prev_cnt;
$this->unpaid_shg_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$deposit_month,$year_now,$shg_unpaid_count);



}

if($chk_stat_prev=='0')
{
if(checkdate($deposit_month,$loan_pay_date,$year_now))
{

$current_time=date('Y-m-d H:i:s');
if($caller!=$test_vonumber)
{
mssql_query("insert into IVRS_VO_DEPOSITS(VO_ID,MOBILE,CREATED_DATE,AMOUNT_PAID,SHG_COUNT_UNPAID,SHG_COUNT_TOTAL,DEPOSIT_MONTH,DEPOSIT_YEAR,IVRS_ID,IVRS_CALL_ID,SHG_COUNT_UNPAID_STATUS) values('$void_code','$caller','$current_time','$amount_to_pay','$shg_unpaid_count','$vo_shg_count','$deposit_month','$year_now','$ivr_call_id','$unique_id','N')");
}

$call_id_rs=mssql_query("select ID from  IVRS_VO_DEPOSITS(nolock) where  VO_ID='$void_code' and DEPOSIT_MONTH='$deposit_month'  and DEPOSIT_YEAR='$year_now' order by CREATED_DATE  desc");
	$ivr_call=mssql_fetch_array($call_id_rs);
	$vo_depost_id=$ivr_call['ID'];

$this->unpaid_shg_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$deposit_month,$year_now,$shg_unpaid_count);

}
  else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->shg_partial_deposit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code);
		mssql_close($link);
  $agi->Hangup();
  exit;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;

	         }
	   
	}
 }
	
	}
	else
	{
	 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/already_entered_details";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
	}
	
  }
else
    {
		
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->shg_partial_deposit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code);
  mssql_close($link);
  $agi->Hangup();
  exit;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
	         }

     }

}

function shg_backlog_deposit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code)
{
		$test_vonumber=$GLOBALS['test_vonumber'];
 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/backlog_shg";
 $agi-> stream_file($wfile, '#');
 $x=3;
 $shg_id=$this->get_shg_single_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length);
 
 if($shg_id>=1)
 {
  
	$shg_backlog_count = mssql_num_rows(mssql_query("select * from IVRS_SHG_DEPOSITS(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_id' and PAID_STATUS='N'"));
		$shg_backlog_amt=$shg_backlog_count*100;
		
		
	if($shg_backlog_count!='0')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_backlog_amout";
	$agi-> stream_file($wfile, '#');	
	 $this->play_amount($shg_backlog_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
$play_msg='shg_paid_backlog_amout';
$length='4';
$upper_limit=$shg_backlog_amt;

$shg_backlog_paid_amt=$this->get_value($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit);

if($shg_backlog_paid_amt>=100)
{
$shg_count_update=$shg_backlog_paid_amt/100;
$current_time=date('Y-m-d H:i:s');

    $month_now=date('m');
	$year_now=date('Y');

if($caller!=$test_vonumber)
{	
mssql_query("insert into IVRS_SHG_BACKLOG_DEPOSITS(VO_ID,SHG_ID,MOBILE,AMOUNT_PAID,CREATED_DATE,DEPOSIT_MONTH,DEPOSIT_YEAR,IVRS_ID,IVRS_CALL_ID,IS_PROCESSED) values('$void_code','$shg_id','$caller','$shg_backlog_paid_amt','$current_time','$month_now','$year_now','$ivr_call_id','$unique_id','N')");
}

 $backlog_id_rs=mssql_query("select top 1 ID from  IVRS_SHG_BACKLOG_DEPOSITS(nolock) where  VO_ID='$void_code' and DEPOSIT_MONTH='$month_now' and SHG_ID='$shg_id' order by CREATED_DATE  desc");
  $backlog_id_rs2=mssql_fetch_array($backlog_id_rs);
  $backlog_id=$backlog_id_rs2['ID'];

if($caller!=$test_vonumber)
{	
mssql_query("update IVRS_SHG_DEPOSITS  set PAID_STATUS='Y',UPDATED_DATE='$current_time',BACKLOG_ID='$backlog_id' where ID  in (select top $shg_count_update ID  from IVRS_SHG_DEPOSITS(nolock)  where PAID_STATUS='N' and  VO_ID='$void_code' and SHG_ID='$shg_id' order by CREATED_DATE asc )");
}


 $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_backlog_one_moreshg", 7000, 1);
	$value=$res_dtmf ["result"];
	if($value=='1')
	{
	
	if($x>=1){
	   //$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_no_backlog_amount";
		//$agi-> stream_file($wfile, '#');
		 //$x=$x-1;
   $this->shg_backlog_deposit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code);
  mssql_close($link);
  $agi->Hangup();
  exit;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
	 $agi-> stream_file($wfile, '#');
	mssql_close($link);
	$agi->Hangup();
	 exit;
	         }
	}
	else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/successfully_entered";
	 $agi-> stream_file($wfile, '#');
	mssql_close($link);
	$agi->Hangup();
	 exit;
	         }

//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/successfully_entered";
	 //$agi-> stream_file($wfile, '#');
	//$agi->Hangup();
	 //exit;


}
	
	}
	else
	{
	
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_no_backlog_amount";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $this->shg_backlog_deposit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code);
  mssql_close($link);
  $agi->Hangup();
  exit;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		  exit;
	         }
	   
	
	
	}	
		
 
 }else
 {
		mssql_close($link);
 $agi->Hangup();
	 exit;
 }

}


function unpaid_shg_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$deposit_month,$year_now,$shg_unpaid_count)
{
		$test_vonumber=$GLOBALS['test_vonumber'];
 $length=$shg_unpaid_count*4;
 $x=3;
 $shg_id_rs_mix=$this->get_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length);
$code_multiple_chk=implode('*',array_unique(explode('*',$shg_id_rs_mix)));
 if(strlen($code_multiple_chk)==strlen($shg_id_rs_mix))
 {
 $shg_id_rs=explode('*',$shg_id_rs_mix);
 for($i='0'; $i<count($shg_id_rs); $i++)
 {
 
  $shg_id=$shg_id_rs[$i];
 if($shg_id>1)
 {
$shg_prev_count=mssql_num_rows(mssql_query("select * from IVRS_SHG_DEPOSITS(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_id' and DEPOSIT_MONTH='$deposit_month' and DEPOSIT_YEAR='$year_now'"));

if($shg_prev_count==0)
 {
$current_time=date('Y-m-d H:i:s');
if($caller!=$test_vonumber)
{
mssql_query("insert into IVRS_SHG_DEPOSITS(VO_ID,SHG_ID,MOBILE,VO_DEPOSITS_ID,CREATED_DATE,DEPOSIT_MONTH,DEPOSIT_YEAR,IVRS_ID,IVRS_CALL_ID) values('$void_code','$shg_id','$caller','$vo_depost_id','$current_time','$deposit_month','$year_now','$ivr_call_id','$unique_id')");
}
 }
}
 }
 
$shg_chk_count=mssql_num_rows(mssql_query("select * from IVRS_SHG_DEPOSITS(nolock)  where VO_ID='$void_code' and DEPOSIT_MONTH='$deposit_month' and DEPOSIT_YEAR='$year_now'"));
	if($shg_chk_count==$shg_unpaid_count)
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("update  IVRS_VO_DEPOSITS set  SHG_COUNT_UNPAID_STATUS='Y' where VO_ID='$void_code' and DEPOSIT_MONTH='$deposit_month' and DEPOSIT_YEAR='$year_now'");
		}
	}
	else
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("update  IVRS_VO_DEPOSITS set  SHG_COUNT_UNPAID_STATUS='N' where VO_ID='$void_code' and DEPOSIT_MONTH='$deposit_month' and DEPOSIT_YEAR='$year_now'");
		}
	}
	
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/successfully_entered";
	 $agi-> stream_file($wfile, '#');
	mssql_close($link);
	$agi->Hangup();
	 exit;
	 }
else
	{
	
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_no_backlog_amount";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $this->unpaid_shg_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$deposit_month,$year_now,$shg_unpaid_count);
  mssql_close($link);
  $agi->Hangup();
  exit;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		  exit;
	         }
	
	
	}	
}

function get_shg_single_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length)
{
		$test_vonumber=$GLOBALS['test_vonumber'];
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 3000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  
	  /*
	  $vo_shg_count = mssql_num_rows(mssql_query("select * from SHG_INFO  where VO_ID='$void_code'"));
	  if($vo_shg_count>99)
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='00'.$shg_code_rs;
	  }
	  if(strlen($shg_code_rs)=='2')
	  {
      $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
	  $shg_code_short=$shg_code_rs;
	  }
	  
	  }
	  else
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
      $shg_code_short=$shg_code_rs;
	  }
	 }
     */
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);
	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
	 //$path="/var/lib/asterisk/sounds/vo_ivrs/shg_names/05/19/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		//$wfile="/var/lib/asterisk/sounds/vo_ivrs/shg_names/05/19/$shg_name";
		//$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#'); 
	  }
	  else
	  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		
	  }
	  
	 
	   
	  
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
		
		if($status=="1")
		{
		return $shg_code;
		
		}
	
		if($status=="2")
		{
		$code=$this->get_shg_single_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length);
		return $code;
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$code=$this->get_shg_single_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length);
		return $code;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
                    mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
	}
	 else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$code=$this->get_shg_single_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length);
		return $code;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
}

function get_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length)
{
		$test_vonumber=$GLOBALS['test_vonumber'];
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/unpaid_shg_new", 10000, $length);
	  $shg_code_rs_main=$res_dtmf ["result"];
	  $code_multiple_chk_rs=implode('*',array_unique(explode('*',$shg_code_rs_main)));
	  if(strlen($shg_code_rs_main)==strlen($code_multiple_chk_rs))
	  {
	  $shg_code_rs_split=explode('*',$shg_code_rs_main);
	  $shg_prev_count=$length/4;
	  $pr_shg_count=count($shg_code_rs_split);
	  if($shg_prev_count==$pr_shg_count)
	  {
	  for($i='0'; $i<count($shg_code_rs_split); $i++)
	  {
	  $shg_code_rs=$shg_code_rs_split[$i];
	  
	  $vo_shg_count = mssql_num_rows(mssql_query("select * from SHG_INFO(nolock)  where VO_ID='$void_code'"));
	  if($vo_shg_count>99)
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='00'.$shg_code_rs;
	  }
	  if(strlen($shg_code_rs)=='2')
	  {
      $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
	  $shg_code_short=$shg_code_rs;
	  }
	  
	  }
	  else
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
      $shg_code_short=$shg_code_rs;
	  }
	 }
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);
	  //$status=1;
	  //$shg_name='bhagyasri';
	 if(strlen($shg_code)>15)
	 {
	 if($status>='1')
	 {
	 ///var/lib/asterisk/sounds/vo_ivrs/shg_names/05/19/
	 //$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
      $cmd="/bin/ls ".$path;
	 //$path="/var/lib/asterisk/sounds/vo_ivrs/shg_names/05/19/$shg_name.wav";
  //$cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		//$wfile="/var/lib/asterisk/sounds/vo_ivrs/shg_names/05/19/$shg_name";
		//$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    /*$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/group_grading";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/$shg_user_grade";
		$agi-> stream_file($wfile, '#');*/
		
	    //$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    //$status=$res_dtmf ["result"];
	  }
	  else
	  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		//$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    //$status=$res_dtmf ["result"];
		
	  }
	  
	  if(strlen($shg_code_mix)=='0')
		{
			$shg_code_mix=$shg_code;
		}
		else
		{ 
			$shg_code_mix=$shg_code_mix."*".$shg_code;
		}
	   }
	  }
	 }
	 	
	  if(strlen($shg_code_mix)>15)
	 {
	 if($status>='1')
	 {
	 
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
		
		if($status=="1")
		{
		return $shg_code_mix;
		
		}
	
		if($status=="2")
		{
		$code=$this->get_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length);
		return $code;
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$code=$this->get_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length);
		return $code;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$code=$this->get_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length);
		return $code;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
		 
		}
		else
		{
		mssql_close($link);
		 $agi->Hangup();
			exit;
		}
		
	}
	 else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$code=$this->get_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length);
		return $code;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
	
  }
  else
  {
		  if($x>='1')
		{
			//say multiple entries of same shg codes
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$code=$this->get_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$vo_depost_id,$length);
		return $code;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
}


function get_number($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	if($value>='1' && $value <= $upper_limit)
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
	 $value=$this->get_number($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit);
	 return $value;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->get_number($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit);
   return $value;
       }
	   else{$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
	   
	} 
	
	}
	else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
	$shg_log_id=$this->get_number($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit);
	return $shg_log_id;
		   }else
		   {$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
		}
	
	
	}
	
function get_value($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	$diff_loan_amt=intval(substr($value,-2,2));
	if($diff_loan_amt=='0')
	{
	if($value>='100' && $value <= $upper_limit)
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	$agi-> stream_file($wfile, '#');
	
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
	 $code=$this->get_value($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit);
	 return $code;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->get_value($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit);
   return $value;
       }
	   else{$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
	   
	} 
	
	}
	else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
	$shg_log_id=$this->get_value($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit);
	return $shg_log_id;
		   }else
		   {$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
		}
	}
	else
	{	
	if($x>='1')
		 		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/backlog_amount_multiples_100";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$shg_log_id2=$this->get_value($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit);
	    return $shg_log_id2;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
				 
	}
	}
	



function get_shg_deposit_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$play_msg)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, 2);
	$value=$res_dtmf ["result"];
	if(strlen($value)=='2')
	{
	$month_now=date('m');
	if($value<=$month_now)
	{
	$year_now=date('Y');
	}
	else
	{
	$year_now=date('Y')-1;
	}
	
	if($value>='0' && $value<='12' && $year_now!='2011')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/$value";
	$agi-> stream_file($wfile, '#');
	$month_now=date('m');
	if($value<=$month_now)
	{
	$year_now=date('Y');
	}
	else
	{
	$year_now=date('Y')-1;
	}
		
   $this->play_amount($year_now,$agi);
  
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
	 $value1=$this->get_shg_deposit_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$play_msg);
	 return $value1;
	}
	if($get_confirmation==1)
	{
	return $value;
	
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value2=$this->get_shg_deposit_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$play_msg);
   return $value2;
      $agi-> stream_file($wfile, '#');
		  $agi->Hangup();
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
	         }
	   
	} 
	
	}
	else
		{
		  if($x>='1')
		{
			
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_date_example2";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
	$shg_log_id=$this->get_shg_deposit_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$play_msg);
	return $shg_log_id;
	   $agi-> stream_file($wfile, '#');
		  $agi->Hangup();
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		}
	}
	else
	{
	
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_date_example2";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
	$shg_log_id=$this->get_shg_deposit_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$play_msg);
	return $shg_log_id;
	      $agi-> stream_file($wfile, '#');
		  $agi->Hangup();
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		
	
	}
	
	}
	
function pop_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	  //$member_limit=6;
	 
		 
	      if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  
	  if($shg_code_rs=='00')
	  {
		 $link = mysql_connect("localhost","root", "evol@9944")
    or die("Data base connection failed");
   mysql_select_db("shtri_nidhi")
    or die("data base open failed");
	$file=$this->record_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$record_confirm,$record_propmt,$language); 
//echo "insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())";
	if($caller!=$test_vonumber)
	{
	mysql_query("insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())");
	}
	
	mysql_close($link);
		mssql_close($link);
		 $agi->Hangup();
		  exit;
		  }
	  /*
	  $vo_shg_count = mssql_num_rows(mssql_query("select * from SHG_INFO  where VO_ID='$void_code'"));
	  if($vo_shg_count>99)
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='00'.$shg_code_rs;
	  }
	  if(strlen($shg_code_rs)=='2')
	  {
      $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
	  $shg_code_short=$shg_code_rs;
	  }
	  
	  }
	  else
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
      $shg_code_short=$shg_code_rs;
	  }
	 }
	 */
	  $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}

	
	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	
	if($overdue_amt>0 && $overdue_amt<=10000)
	{
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
		$agi-> stream_file($wfile, '#');
		$is_over_due='Y';	
	}
	 
	
	  if($overdue_amt>10000)
	{	
	if($x>='1')
		{
			
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
		$agi-> stream_file($wfile, '#');
		/*
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/proceed_through_option8";
		$agi-> stream_file($wfile, '#');
		*/
		$x=$x-1;
		$this->pop_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	}

 $shg_grade_dist_code=substr($void_code,2,2);
	$shg_grade_mandal_code=substr($void_code,4,2);
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT(nolock) where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
	$mms_grade=$vo_grade_rs['GRADE'];
	
	if($mms_grade=='E')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_egrade";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($mms_grade=='F')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select GRADE,ACTUAL_CREDIT_LIMIT from VO_POP_CREDIT_LIMIT(nolock) where VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs_rej['GRADE'];
	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
	
		if($vo_grade=='E')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($vo_grade=='F')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	if($vo_actual_credit=='0')
	{
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	
		}	
	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  
	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  

  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  }
		if($status=="1")
		{
			$pop_stat_rs=mssql_query("select TRANS_VO_ID from vo_info(nolock) where TRANS_VO_ID='$void_code' and IS_POP_NEW='Y'");
			$stat_pop=mssql_num_rows($pop_stat_rs);
			$pop_stat_shg_rs=mssql_query("select TRANS_SHG_ID from SHG_INFO(nolock) where TRANS_SHG_ID='$shg_code' and IS_POP_NEW='Y'");
			$pop_stat_shg=mssql_num_rows($pop_stat_shg_rs);
		if($stat_pop>=1 && $pop_stat_shg>=1)
   		{
			
	$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
    $vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	if($vo_active_stat=='Y')
	{
	$x=3;
	$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
 		
	}
	else
	{
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->pop_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         
	
	}
			
			}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_not_in_pop";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->pop_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	     
		}
	
		}
	
		if($status=="2")
		{
		$this->pop_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->pop_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->pop_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
	}		


function new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	 $project=1;
	 	
		 
	      if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  
	  $message="Prompting caller to enter SHG short code ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	  
	  
	  if($shg_code_rs=='00')
	  {
		 $link = mysql_connect("localhost","root", "evol@9944")
    or die("Data base connection failed");
   mysql_select_db("shtri_nidhi")
    or die("data base open failed");
	$file=$this->record_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$record_confirm,$record_propmt,$language); 
//echo "insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())";
	if($caller!=$test_vonumber)
	{
	mysql_query("insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())");
	}
	
	mysql_close($link);
		mssql_close($link);
		 $agi->Hangup();
		  exit;
		  }
		  
	 
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  
	  
	  $message="SHG DETAILS: TRANS_SHG_ID: $shg_code ,  SHG_NAME: $shg_name,SHG SHORT CODE: $shg_code_rs";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	  
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}

	//STARTING CHECKING SHG OVER DUE By Ashok
	$triggered_loan_count_res=mssql_fetch_array(mssql_query("select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'"));
		$triggered_loan_count=$triggered_loan_count_res[0];
		$message="Triggered Loans: select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

			$curr_odos=0;
		
			if($triggered_loan_count > 0)
			{
				$shg_overdue_res=mssql_fetch_array(mssql_query("select OVERDUE,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where OVERDUE>0 and SHG_ID='$shg_code'"));
				$shg_ovrdue=$shg_overdue_res[0];
				if($shg_ovrdue>0)
				{
				   $message="SHG HAS OVERDUE AMT $shg_ovrdue is gretaer than 0 in SAMSNBSTG.SN.SHG_OVERDUE_STATUS,system checks shg OD AND OS in SN";
				   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

				   $curr_shg_odos=0;
				   $curr_shg_odos=$shg_overdue_res[1]+$shg_overdue_res[2];
				      if($curr_shg_odos<=100)
				      {
					$curr_odos=$curr_shg_odos;
				  	$message="SHG HAS CURRENT_OD,CURRENT_OS:$curr_odos Less than 100, so for this shg we are allowing SN loan. ";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
				      }
				      else
				      { 
					if($x>='1')
					{
			
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_sn_overdue";
						$agi-> stream_file($wfile, '#');
						$this->play_amount($shg_ovrdue,$agi);
						$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    					$agi-> stream_file($wfile, '#');
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
						$agi-> stream_file($wfile, '#');
						$x=$x-1;
						$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
						mssql_close($link);
						$agi->Hangup();
		  				exit;
		   			}else
		   			{
		    				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    				$agi-> stream_file($wfile, '#');
						mssql_close($link);
		   				$agi->Hangup();
		   				exit;
		   			}
				     }
				
				}

			}
			
	//ENDING CHECKING SHG OVER DUE By Ashok

	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	$bank_name_rs= mssql_fetch_array(mssql_query("select Bank_Code from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code'"));
	$bank_name=$bank_name_rs['Bank_Code'];


	if($overdue_amt>0 && $overdue_amt<=10000)
	{
			$message="SHG OVERDUE AMT $overdue_amt is gretaer than 0 and lessthan or equal to 10000,Promting the caller and continue";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
		$agi-> stream_file($wfile, '#');
		$is_over_due='Y';	
	}
	 
	
	  if($overdue_amt>10000)
	{	
		$message="SHG OVERDUE AMT $overdue_amt is gretaer than 10000,Promting the caller and proceed to another SHG";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

	if($x>='1')
		{
			
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
		$agi-> stream_file($wfile, '#');
		/*
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/proceed_through_option8";
		$agi-> stream_file($wfile, '#');
		*/
		$x=$x-1;
		$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	}	
	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {

	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  

	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
  	  	$message="SUCCESS: SHG Audio file $path exists,Playing the SHG name ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	  	$message="FAIL: NO SHG Audio file $path ,Playing the SHG SHORT CODE ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  
		  }
		if($status=="1")
		{
			$status_pop_fund=2;
			/*
			$pop_stat_rs=mssql_query("select TRANS_VO_ID from vo_info where TRANS_VO_ID='$void_code' and IS_POP='Y'");
			$stat_pop=mssql_num_rows($pop_stat_rs);
			$pop_stat_shg_rs=mssql_query("select TRANS_SHG_ID from SHG_INFO where TRANS_SHG_ID='$shg_code' and IS_POP='Y'");
			$pop_stat_shg=mssql_num_rows($pop_stat_shg_rs);
		if($stat_pop>=1 && $pop_stat_shg>=1)
   		{
 		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_for_popfund", 8000, 1);
	    $status_pop_fund=$res_dtmf ["result"];
			}else
			{
				$status_pop_fund=2;
			}
			*/
			
			if($status_pop_fund=='1')
			{
				
				//$x=3;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/stop_pop_loan";
		$agi-> stream_file($wfile, '#');
		//$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
		mssql_close($link);
			$agi->Hangup();
			exit;	
			}
			elseif($status_pop_fund=='2')
			{
				//TRANS_VO_ID
	$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE,CIF_RECOVERY_PERCENTAGE from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
    $vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	
				
	$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
    $shg_active_stat=$shg_active_rs['IS_VALID'];
	if($shg_active_stat=='Y')
	{
	
	$message="SUCCESS: SHG SB Account Details are valid (select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	}
	else
	{
		$message="FAIL: SHG SB Account Details are invalid (select IS_VALID from SHG_INFO where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat ,prompting the caller and proceed to another SHG";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied_old";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         
	
	}
	
$check_shg_samrudhi=$this->check_shg_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);

					if($check_shg_samrudhi == 0)
					{
						$message="FAIL: SHG SAMRUDHI FAILED ";
						$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	  				 if($x>='1')
					{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_denied";
						$agi-> stream_file($wfile, '#');
						 $x=$x-1;
						$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
						$agi->Hangup();
		 				 exit;
		
					   }else
					   {
					    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
					    $agi-> stream_file($wfile, '#');
		mssql_close($link);
					   $agi->Hangup();
					   exit;
					   }
	}else{
		$message="SUCCESS: SHG SAMRUDHI PASSED ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	}




	$shg_grade_dist_code=substr($void_code,2,2);
	$shg_grade_mandal_code=substr($void_code,4,2);
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT(nolock) where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
	$mms_grade=$vo_grade_rs['GRADE'];
	
	
    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select A.GRADE,A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs_rej['GRADE'];
	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
	$message="Validating VO GRADE and ACTUAL_CREDIT_LIMIT,MMS Grade , VO Grade: $vo_grade,VO ACTUAL_CREDIT_LIMIT : $vo_actual_credit,MMS Grade :$mms_grade ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		if($vo_grade=='E')
	{
		$message="VO grade $vo_grade ,disconnecting ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($vo_grade=='F')
	{
		$message="VO grade $vo_grade ,disconnecting ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	if($vo_actual_credit=='0')
	{
		$message="vo_actual_credit $vo_actual_credit ,disconnecting ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	
		}


	
//	if(($mms_grade=='E' || $mms_grade=='F') && ($vo_grade=='C'||$vo_grade=='D'||$vo_grade=='F')) 
//	{
//		$message="VO grade $vo_grade and MMS grade $mms_grade,disconnecting ";
//		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
//		
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
	
$search_purpose_ig="'Agriculture','Dairy','Income generation Activity','Weavers'";
$search_purpose_non_ig="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
	

	   		$x=3;
		//current
		
		$message="SHG LIMITS in project $project at 1st place";
		$this->log_ivr($ivr_call_id,$message);
		
		$shg_limit_array=$this->shg_limits_suvidha($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code);
		$shg_max_loans_total=$shg_limit_array[$shg_code]['shg_max_loans_total'];
		$shg_max_loans_ivrs=$shg_limit_array[$shg_code]['shg_max_loans_ivrs'];
		$shg_max_credit_limit=$shg_limit_array[$shg_code]['shg_max_credit_limit'];
		
		$message="SHG LIMITS in project $project at 1st place shg_max_loans_total:$shg_max_loans_total,shg_max_loans_ivrs:$shg_max_loans_ivrs,shg_max_credit_limit:$shg_max_credit_limit";
		$this->log_ivr($ivr_call_id,$message);

	   $member_limit=$this->shg_member_limit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs);
	      $message="member_limit: $member_limit";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	   
	    
		if($member_limit>=1)
		  {
		  	
		  $message="SUCCESS: member_limit: $member_limit Greater Than or Equal to 1";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	   	
		$amt_stat='Y';
	$this->member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	       }
		   else
		   {
		   
		     $message="FAIL: member_limit: $member_limit Less Than 1 , Prompting morethan_".$shg_max_loans_ivrs."_loans ";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
		 	 
		  	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_".$shg_max_loans_ivrs."_loans", 5000, 1);
		  
	          $more_res=$res_dtmf ["result"];
		  if($more_res==1)
		  {
		  $x=3;
		  $this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		  $agi->Hangup();
		    }
			else
			{
		mssql_close($link);
			 $agi->Hangup();
			 $agi->Hangup();
			 exit;
			}
		   
		   }
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	     
		}
	
		}
	
		if($status=="2")
		{
		$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
	}

function smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];

		if($status_pop_fund==5)
		{
		 $project=72;
		}else if($status_pop_fund==6){
		 $project=74;
		}
		 
	      if($x=='3')
		{
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		}
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  
	  $message="Prompting caller to enter SHG short code ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	 
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  
	  fwrite(STDERR,"---select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'--");
	  $message="SHG DETAILS: TRANS_SHG_ID: $shg_code ,  SHG_NAME: $shg_name,SHG SHORT CODE: $shg_code_rs";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	  
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}

	//STARTING CHECKING SHG OVER DUE By Ashok
	$triggered_loan_count_res=mssql_fetch_array(mssql_query("select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'"));
		$triggered_loan_count=$triggered_loan_count_res[0];
		$message="Triggered Loans: select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

			$curr_odos=0;
		
			if($triggered_loan_count > 0)
			{
				$shg_overdue_res=mssql_fetch_array(mssql_query("select OVERDUE,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
				$shg_ovrdue=$shg_overdue_res[0];
				if($shg_ovrdue>0)
				{
				   $message="SHG HAS OVERDUE AMT $shg_ovrdue is gretaer than 0 in SAMSNBSTG.SN.SHG_OVERDUE_STATUS,system checks shg OD AND OS in SN";
				   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

				   $curr_shg_odos=0;
				   $curr_shg_odos=$shg_overdue_res[1]+$shg_overdue_res[2];
				      if($curr_shg_odos<=100)
				      {
					$curr_odos=$curr_shg_odos;
				  	$message="SHG HAS CURRENT_OD,CURRENT_OS:$curr_odos Less than 100, so for this shg we are allowing SN loan. ";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
				      }
				      else
				      { 
					if($x>='1')
					{
			
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_sn_overdue";
						$agi-> stream_file($wfile, '#');
						$this->play_amount($shg_ovrdue,$agi);
						$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    					$agi-> stream_file($wfile, '#');
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
						$agi-> stream_file($wfile, '#');
						$x=$x-1;
						$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
						mssql_close($link);
						$agi->Hangup();
		  				exit;
		   			}else
		   			{
		    				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    				$agi-> stream_file($wfile, '#');
						mssql_close($link);
		   				$agi->Hangup();
		   				exit;
		   			}
				     }
				
				}

			}
			
	//ENDING CHECKING SHG OVER DUE By Ashok

	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	$bank_name_rs= mssql_fetch_array(mssql_query("select Bank_Code from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code'"));
	$bank_name=$bank_name_rs['Bank_Code'];


	if($overdue_amt>0 && $overdue_amt<=10000)
	{
			$message="SHG OVERDUE AMT $overdue_amt is gretaer than 0 and lessthan or equal to 10000,Promting the caller and continue";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
		$agi-> stream_file($wfile, '#');
		$is_over_due='Y';	
	}
	 
	
	  if($overdue_amt>10000)
	{	
		$message="SHG OVERDUE AMT $overdue_amt is gretaer than 10000,Promting the caller and proceed to another SHG";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

	if($x>='1')
		{
			
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
		$agi-> stream_file($wfile, '#');
		/*
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/proceed_through_option8";
		$agi-> stream_file($wfile, '#');
		*/
		$x=$x-1;
		$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	}	
	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {

	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  

	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
  	  	$message="SUCCESS: SHG Audio file $path exists,Playing the SHG name ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	  	$message="FAIL: NO SHG Audio file $path ,Playing the SHG SHORT CODE ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  
		  }
		if($status=="1")
		{
				//TRANS_VO_ID
	$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE,CIF_RECOVERY_PERCENTAGE from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
    $vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	
				
	$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
    $shg_active_stat=$shg_active_rs['IS_VALID'];
	if($shg_active_stat=='Y')
	{
	
	$message="SUCCESS: SHG SB Account Details are valid (select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	}
	else
	{
		$message="FAIL: SHG SB Account Details are invalid (select IS_VALID from SHG_INFO where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat ,prompting the caller and proceed to another SHG";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied_old";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         
	
	}
	
$check_shg_samrudhi=$this->check_shg_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);

					if($check_shg_samrudhi == 0)
					{
						$message="FAIL: SHG SAMRUDHI FAILED ";
						$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	  				 if($x>='1')
					{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_denied";
						$agi-> stream_file($wfile, '#');
						 $x=$x-1;
						$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		mssql_close($link);
						$agi->Hangup();
		 				 exit;
		
					   }else
					   {
					    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
					    $agi-> stream_file($wfile, '#');
		mssql_close($link);
					   $agi->Hangup();
					   exit;
					   }
	}else{
		$message="SUCCESS: SHG SAMRUDHI PASSED ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	}




	$shg_grade_dist_code=substr($void_code,2,2);
	$shg_grade_mandal_code=substr($void_code,4,2);
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT(nolock) where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
	$mms_grade=$vo_grade_rs['GRADE'];
	
	
    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select A.GRADE,A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs_rej['GRADE'];
	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
	$message="Validating VO GRADE and ACTUAL_CREDIT_LIMIT,MMS Grade , VO Grade: $vo_grade,VO ACTUAL_CREDIT_LIMIT : $vo_actual_credit,MMS Grade :$mms_grade ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		if($vo_grade=='E')
	{
		$message="VO grade $vo_grade ,disconnecting ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($vo_grade=='F')
	{
		$message="VO grade $vo_grade ,disconnecting ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	if($vo_actual_credit=='0')
	{
		$message="vo_actual_credit $vo_actual_credit ,disconnecting ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	
		}

$search_purpose_ig="'Agriculture','Dairy','Income generation Activity','Weavers'";
$search_purpose_non_ig="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
	

	   		$x=3;
		//current
		
		$message="SHG LIMITS in project $project at 2nd place";
		$this->log_ivr($ivr_call_id,$message);
		
		$shg_limit_array=$this->shg_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code);
		$shg_max_loans_total=$shg_limit_array[$shg_code]['shg_max_loans_total'];
		$shg_max_loans_ivrs=$shg_limit_array[$shg_code]['shg_max_loans_ivrs'];
		$shg_max_credit_limit=$shg_limit_array[$shg_code]['shg_max_credit_limit'];
		
	$member_limit=$this->shg_member_limit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs);

fwrite(STDERR," member_limit: $member_limit | shg_max_loans_ivrs : $shg_max_loans_ivrs");
	    
	if($status_pop_fund==6)
	$this->bicycle_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund);
		if($member_limit>=1)
		  {
		  	
		  $message="SUCCESS: member_limit: $member_limit Greater Than or Equal to 1";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	   	
		$amt_stat='Y';
	if($status_pop_fund==5)
	$this->smartphone_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund);
		}
		   else
		   {
		   
		     $message="FAIL: member_limit: $member_limit Less Than 1 , Prompting morethan_".$shg_max_loans_ivrs."_loans ";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
		 	 
		  	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_".$shg_max_loans_ivrs."_loans", 5000, 1);
		  
	          $more_res=$res_dtmf ["result"];
		  if($more_res==1)
		  {
		  $x=3;
		  $this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		  $agi->Hangup();
		    }
			else
			{
		mssql_close($link);
			 $agi->Hangup();
			 $agi->Hangup();
			 exit;
			}
		   
		   }
		}
	
		if($status=="2")
		{
		$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
	}

	function IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	 $project=1;
	 	
		 
	      if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  
	  $message="Prompting caller to enter SHG short code ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	  
	  
	  if($shg_code_rs=='00')
	  {
		 $link = mysql_connect("localhost","root", "evol@9944")
    or die("Data base connection failed");
   mysql_select_db("shtri_nidhi")
    or die("data base open failed");
	$file=$this->record_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$record_confirm,$record_propmt,$language); 
//echo "insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())";
	if($caller!=$test_vonumber)
	{
	mysql_query("insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())");
	}
	
	mysql_close($link);
		mssql_close($link);
		 $agi->Hangup();
		  exit;
		  }
		  
	 
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  
	  
	  $message="SHG DETAILS: TRANS_SHG_ID: $shg_code ,  SHG_NAME: $shg_name,SHG SHORT CODE: $shg_code_rs";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	  
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}

//START CHECKING IN IHHL_TAGGED_MEMBER_DETAILS
$ihhl_member_count_res=mssql_query("select * from IHHL_TAGGED_MEMBER_DETAILS with (nolock) where SHG_ID='$shg_code'");
$ihhl_member_count = mssql_num_rows($ihhl_member_count_res);

	if($ihhl_member_count == 0){
		if($x>='1')
		{
			$x=$x-1;
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/voice_IHHL_SHG";
		$agi-> stream_file($wfile, '#');
		$this->IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }	
	}

//END CHECKING IN IHHL_TAGGED_MEMBER_DETAILS

	//STARTING CHECKING SHG OVER DUE By Ashok
	$triggered_loan_count_res=mssql_fetch_array(mssql_query("select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'"));
		$triggered_loan_count=$triggered_loan_count_res[0];
		$message="Triggered Loans: select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

			$curr_odos=0;
		
			if($triggered_loan_count > 0)
			{
				$shg_overdue_res=mssql_fetch_array(mssql_query("select OVERDUE,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
				$shg_ovrdue=$shg_overdue_res[0];
				if($shg_ovrdue>0)
				{
				   $message="SHG HAS OVERDUE AMT $shg_ovrdue is gretaer than 0 in SAMSNBSTG.SN.SHG_OVERDUE_STATUS,system checks shg OD AND OS in SN";
				   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

				   $curr_shg_odos=0;
				   $curr_shg_odos=$shg_overdue_res[1]+$shg_overdue_res[2];
				      if($curr_shg_odos<=100)
				      {
					$curr_odos=$curr_shg_odos;
				  	$message="SHG HAS CURRENT_OD,CURRENT_OS:$curr_odos Less than 100, so for this shg we are allowing SN loan. ";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
				      }
				      else
				      { 
					if($x>='1')
					{
			
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_sn_overdue";
						$agi-> stream_file($wfile, '#');
						$this->play_amount($shg_ovrdue,$agi);
						$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    					$agi-> stream_file($wfile, '#');
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
						$agi-> stream_file($wfile, '#');
						$x=$x-1;
						$this->IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
						mssql_close($link);
						$agi->Hangup();
		  				exit;
		   			}else
		   			{
		    				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    				$agi-> stream_file($wfile, '#');
						mssql_close($link);
		   				$agi->Hangup();
		   				exit;
		   			}
				     }
				
				}

			}
			
	//ENDING CHECKING SHG OVER DUE By Ashok

	//SHG CHECKING BANK LINKAGE
	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	$bank_name_rs= mssql_fetch_array(mssql_query("select Bank_Code from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code'"));
	$bank_name=$bank_name_rs['Bank_Code'];

	if($overdue_amt>0 && $overdue_amt<=10000)
	{
			$message="SHG OVERDUE AMT $overdue_amt is gretaer than 0 and lessthan or equal to 10000,Promting the caller and continue";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
		$agi-> stream_file($wfile, '#');
		$is_over_due='Y';	
	}
	 
	
	  if($overdue_amt>10000)
	{	
		$message="SHG OVERDUE AMT $overdue_amt is gretaer than 10000,Promting the caller and proceed to another SHG";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

	if($x>='1')
		{
			
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
		$agi-> stream_file($wfile, '#');
		/*
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/proceed_through_option8";
		$agi-> stream_file($wfile, '#');
		*/
		$x=$x-1;
		$this->IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	}	
	fwrite(STDERR,"--------$status----shg_code_short : $shg_code_short-");
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {

	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
  	  	$message="SUCCESS: SHG Audio file $path exists,Playing the SHG name ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg_TS_bifurcation/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	  	$message="FAIL: NO SHG Audio file $path ,Playing the SHG SHORT CODE ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  
		  }
		if($status=="1")
		{
			$status_pop_fund=2;
			/*
			$pop_stat_rs=mssql_query("select TRANS_VO_ID from vo_info where TRANS_VO_ID='$void_code' and IS_POP='Y'");
			$stat_pop=mssql_num_rows($pop_stat_rs);
			$pop_stat_shg_rs=mssql_query("select TRANS_SHG_ID from SHG_INFO where TRANS_SHG_ID='$shg_code' and IS_POP='Y'");
			$pop_stat_shg=mssql_num_rows($pop_stat_shg_rs);
		if($stat_pop>=1 && $pop_stat_shg>=1)
   		{
 		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_for_popfund", 8000, 1);
	    $status_pop_fund=$res_dtmf ["result"];
			}else
			{
				$status_pop_fund=2;
			}
			*/
			
			if($status_pop_fund=='1')
			{
				
				//$x=3;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/stop_pop_loan";
		$agi-> stream_file($wfile, '#');
		//$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
		mssql_close($link);
			$agi->Hangup();
			exit;	
			}
			elseif($status_pop_fund=='2')
			{
				//TRANS_VO_ID
	$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE,CIF_RECOVERY_PERCENTAGE from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
    $vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	
				
	$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
    $shg_active_stat=$shg_active_rs['IS_VALID'];

	if($shg_active_stat=='Y')
	{
	
	$message="SUCCESS: SHG SB Account Details are valid (select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	}
	else
	{
		$message="FAIL: SHG SB Account Details are invalid (select IS_VALID from SHG_INFO where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat ,prompting the caller and proceed to another SHG";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied_old";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         
	
	}
	
$check_shg_samrudhi=$this->check_shg_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);

					if($check_shg_samrudhi == 0)
					{
						$message="FAIL: SHG SAMRUDHI FAILED ";
						$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	  				 if($x>='1')
					{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_denied";
						$agi-> stream_file($wfile, '#');
						 $x=$x-1;
						$this->IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
						$agi->Hangup();
		 				 exit;
		
					   }else
					   {
					    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
					    $agi-> stream_file($wfile, '#');
		mssql_close($link);
					   $agi->Hangup();
					   exit;
					   }
	}else{
		$message="SUCCESS: SHG SAMRUDHI PASSED ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	}




	$shg_grade_dist_code=substr($void_code,2,2);
	$shg_grade_mandal_code=substr($void_code,4,2);
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT(nolock) where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
	$mms_grade=$vo_grade_rs['GRADE'];
	
	
    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select A.GRADE,A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs_rej['GRADE'];
	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
	$message="Validating VO GRADE and ACTUAL_CREDIT_LIMIT,MMS Grade , VO Grade: $vo_grade,VO ACTUAL_CREDIT_LIMIT : $vo_actual_credit,MMS Grade :$mms_grade ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		if($vo_grade=='E')
	{
		$message="VO grade $vo_grade ,disconnecting ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($vo_grade=='F')
	{
		$message="VO grade $vo_grade ,disconnecting ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	if($vo_actual_credit=='0')
	{
		$message="vo_actual_credit $vo_actual_credit ,disconnecting ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	
		}


	
//	if(($mms_grade=='E' || $mms_grade=='F') && ($vo_grade=='C'||$vo_grade=='D'||$vo_grade=='F')) 
//	{
//		$message="VO grade $vo_grade and MMS grade $mms_grade,disconnecting ";
//		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
//		
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}




$this->IHHL_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;

fwrite(STDERR,"---------____________-------------");
	/*
$search_purpose_ig="'Agriculture','Dairy','Income generation Activity','Weavers'";
$search_purpose_non_ig="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
	

	   		$x=3;
		//current
	
	
		$shg_limit_array=$this->shg_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code);
		$shg_max_loans_total=$shg_limit_array[$shg_code]['shg_max_loans_total'];
		$shg_max_loans_ivrs=$shg_limit_array[$shg_code]['shg_max_loans_ivrs'];
		$shg_max_credit_limit=$shg_limit_array[$shg_code]['shg_max_credit_limit'];
		
	   $member_limit=$this->shg_member_limit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs);
	   
	   
	      $message="member_limit: $member_limit";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	   
	    
		if($member_limit>=1)
		  {
		  	
		  $message="SUCCESS: member_limit: $member_limit Greater Than or Equal to 1";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	   	
		$amt_stat='Y';
	$this->member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	       }
		   else
		   {
		   
		     $message="FAIL: member_limit: $member_limit Less Than 1 , Prompting morethan_".$shg_max_loans_ivrs."_loans ";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
		 	 
		  	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_".$shg_max_loans_ivrs."_loans", 5000, 1);
		  
	          $more_res=$res_dtmf ["result"];
		  if($more_res==1)
		  {
		  $x=3;
		  $this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		  $agi->Hangup();
		    }
			else
			{
		mssql_close($link);
			 $agi->Hangup();
			 $agi->Hangup();
			 exit;
			}
		   
		   }*/
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	     
		}
	
		}
	
		if($status=="2")
		{
		$this->IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
	}		


function bicycle_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund){
		$test_vonumber=$GLOBALS['test_vonumber'];			
 
 		$ivrs_array='';
		$ivrs_array=array();
		$ivrs_array['file']="mem_category";
		$ivrs_array['retries']=3;
		$ivrs_array['max_digits']=1;
		$ivrs_array['timeout']=5000;
		$ivrs_array['no_input']="invaliddata";
		$ivrs_array['verification_string']="A^response";
				
		$ivrs_array['1']="pop";
		$ivrs_array['2']="poor";
		
		$member_type=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
		
		
		if($member_type == ''){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
			$agi->Hangup();
			exit;
		}
	
	if($member_type == "pop"){
		$member_type="Y";
	}elseif($member_type == "poor"){
		$member_type="N";
	}
		
	$length='2';
	$play_msg='two_digit_member_id';
	$type='bicycle';
	$x='3';
	$db_filed=$member_type;
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE,IS_POP_MEM from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
  $IS_POP_MEM=$member_id_rs['IS_POP_MEM'];
 				
$message="Member details:  MEMBER_ID: $member_id , Short Code:  $member_short_code,member_uid: $member_uid , member_age:$member_age , IS_POP_MEM:$IS_POP_MEM , member_mobile_num: $member_mobile_num";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
 				
 				if($IS_POP_MEM != $member_type){
					if($IS_POP_MEM =='N' && $member_type=='Y')
						{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_pop";
						$agi-> stream_file($wfile, '#');	
						
						}
						if($IS_POP_MEM == 'Y' && $member_type == 'N')
						{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_non_pop";
						$agi-> stream_file($wfile, '#');
						}
					
					if($x>='1')
					{
					$x=$x-1;
					$this->bicycle_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
		mssql_close($link);
					$agi->Hangup();
					exit;
					}else{
		mssql_close($link);
					$agi->Hangup();
					exit;		
					}
				
				}

//START CHECKING MEMBER_OVERDUE
$ihhl_member_overdue_res=mssql_fetch_array(mssql_query("select LOAN_DUE from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED=0"));

if($ihhl_member_overdue_res['LOAN_DUE']>0)
{
		if($x>='1')
		{
		$x=$x-1;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_sn_overdue"; //member overdue
		$agi-> stream_file($wfile, '#');
		$this->play_amount($ihhl_member_overdue_res[LOAN_DUE],$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;

	$this->bicycle_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
}
//END CHECKING MEMBER_OVERDUE

	$member_applied_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type='74'"));
	
	$member_prev_loan_count_live = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id' and project_type='74'"));

$member_rej_cnt_lng = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and REQUESTED_ID!='201314' and project_type='74'"));

$member_rej_cnt_lng_tcs = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and project_type='74'"));

$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1' and a.project_type='74'"));

$member_other_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));

		if($member_applied_loan_count == 0  && $member_prev_loan_count_tcs ==0){
		 $member_repaid_loans=0;
		}

	
	$message="member_applied_loan_count-member_rej_cnt_lng+member_prev_loan_count_live-member_repaid_loans-member_corpus_loans:$member_applied_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_corpus_loans:::member_prev_loan_count_tcs-member_rej_cnt_lng_tcs+member_prev_loan_count_live-member_repaid_loans-member_corpus_loans:$member_prev_loan_count_tcs-$member_rej_cnt_lng_tcs+$member_prev_loan_count_live-$member_repaid_loans-$member_corpus_loans ::member_other_repaid_loans:$member_other_repaid_loans";
        $this->log_ivr($ivr_call_id,$message);
	
        $member_prev_loan_count=$member_applied_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans;

	$message="Member loans ".$member_applied_loan_count."-".$member_rej_cnt_lng."+".$member_prev_loan_count_live."-".$member_repaid_loans."-".$member_other_repaid_loans." = ".$member_prev_loan_count;

	$this->log_ivr($ivr_call_id,$message);	

	if($member_prev_loan_count != 0)
	{
	
	$message="Member loan_already_applied : member_prev_loan_count:".$member_prev_loan_count." member_repaid_loans : ".$member_repaid_loans;
	$this->log_ivr($ivr_call_id,$message);	
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> bicycle_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
		mssql_close($link);
	$agi->Hangup();
	exit;	
	}	

	if(strlen($member_mobile_num) == 10){
		
	}else{
    $length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
	if($x>='1')
		{
	 $x=$x-1;
	 $this->bicycle_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
		mssql_close($link);
	 $agi->Hangup();
	exit;
	 }else{
		mssql_close($link);
	$agi->Hangup();
	exit;		
	}
	
	}
	}
		$loan_type = "CONSUMPTION";
		$loan_category="NONIG";	
		$reason_loan="bicycle";

		$db_filed="required_loan";
		$type="amount";
		$length="4";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->bicycle_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
	 fwrite(STDERR,"------>>($reason_loan!='')&&($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && $member_prev_loan_count=='0' && strlen($member_id)>1)<<--------");
	 if($loan_amount>='1')
	 {
 if(($reason_loan!='')&&($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && $member_prev_loan_count=='0' && strlen($member_id)>1))
 {
  $message="CONDITION 3 WILL CHECK HERE";
	$this->log_ivr($ivr_call_id,$message);
 
 $duration='12';
 $etime=date('Y-m-d H:i:s');

if($loan_category == "NONIG")
{
//$duration='24';
}


 $vo_actual_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
 $vo_actual_credit=$vo_actual_credit_rs['ACTUAL_CREDIT_LIMIT'];
 $vo_credit_pop=$vo_actual_credit_rs['POP_CREDIT_LIMIT'];
 $vo_credit_non_pop=$vo_actual_credit_rs['NONPOP_CREDIT_LIMIT']; 
 
 
 if($member_type=='Y')
	{
	 $member_cat='pop';
	 $search_cat='0';
	 $vo_cat_actual_limit=$vo_credit_pop;
	 //$tbl_filed='current_limit_pop';
	  //$vo_credit_pop=intval(ceil($vo_actual_credit/2));
	if($loan_category == "IG")
	 {
		$vo_fixed_credit=intval(ceil($vo_credit_pop*0.85));
		$tbl_filed='current_limit_pop_ig';
		$search_purpose="'Agriculture','Dairy','Income generation Activity','Weavers'";
		$search_cr_limit='credit_limit_pop_ig';
		$credit_lt_type="current_limit_pop_ig";
	 }
	if($loan_category == "NONIG")
         {
		$vo_fixed_credit=intval(floor($vo_credit_pop*0.15));
		$tbl_filed='current_limit_pop_non_ig';
		$search_cr_limit='credit_limit_pop_non_ig';
		$search_purpose="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
		$credit_lt_type="current_limit_pop_non_ig";
         }
		
	} //  End of if($member_type=='Y')
 if($member_type=='N')
	{
			
		//$vo_credit_non_pop=intval(floor($vo_actual_credit/2));	
		$member_cat='non-pop';
		$search_cat='1';
		$vo_cat_actual_limit=$vo_credit_non_pop;
		//$tbl_filed='current_limit_nonpop';
	if($loan_category == "IG")
	 {
		$vo_fixed_credit=intval(ceil($vo_credit_non_pop*0.85));	
		$tbl_filed='current_limit_nonpop_ig';
		$search_cr_limit='credit_limit_nonpop_ig';
		$search_purpose="'Agriculture','Dairy','Income generation Activity','Weavers'";
		$credit_lt_type="current_limit_nonpop_ig";
	 }
		
	if($loan_category == "NONIG")
         {
		$vo_fixed_credit=intval(floor($vo_credit_non_pop*0.15));
		$tbl_filed='current_limit_nonpop_non_ig';
		$search_cr_limit='credit_limit_nonpop_non_ig';
		$search_purpose="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
		$credit_lt_type="current_limit_nonpop_non_ig";
         }
	} // End of  if($member_type=='N')
	

	$applied_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and project_type not in('71','72','74','43') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'")); 

	
        $applied_amt = $applied_rs['AMT'];
		
		$applied_rs_live=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and project_type not in('71','72','74','43')"));
        $applied_amt_live = $applied_rs_live['AMT_LIVE'];
		$applied_amt_live=intval($applied_amt_live);

		
		$vo_rej_amt_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and STATUS_ID='11' and project_type not in('71','72','74','43')"));		
$vo_rej_amt=$vo_rej_amt_rs['AMT_REJ'];
$vo_rej_amt=intval($vo_rej_amt);



//commented for automation
/*
   $vo_fixed_credit_rs=mssql_fetch_array(mssql_query("select $search_cr_limit from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
       $vo_fixed_credit = $vo_fixed_credit_rs[$search_cr_limit];
	   */
	   
	   $vo_repaid_cat_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='$search_cat' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and project_type not in('71','72','74','43')"));
        $repaid_cat_total = $vo_repaid_cat_rs['AMT_REPAID'];
		//added for automation
		$applied_total=$applied_amt+$applied_amt_live-$vo_rej_amt-$repaid_cat_total;
		
	if($applied_total < 0){
			$applied_total=0;
			
	$message="VO Repaid AMT is greater than applied amt,resetting the outstanding to 0, applied_total :$applied_total";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					
		}
		
		
		$vo_cat_limit=$vo_cat_actual_limit-$applied_total;
			if($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Weavers')
		{
			// $repaid_cat_total=intval(ceil($repaid_cat_total*0.85));
			 //$repaid_cat_total=intval($repaid_cat_total);
			 $vo_credit_lt=intval(ceil($vo_cat_limit*0.85));
		}
		if($reason_loan=='Education'||$reason_loan=='Marriage'||$reason_loan=='Emergency Needs/Health')
		{
			 //$repaid_cat_total=intval(floor($repaid_cat_total*0.15));
			 //$repaid_cat_total=intval($repaid_cat_total);
			  $vo_credit_lt=intval(ceil($vo_cat_limit*0.15));
		}
		$project=74;
		
		$message="shg_outstanding_amt in project $project at 1st place";
		$this->log_ivr($ivr_call_id,$message);
		
	$tcs_shg_outstanding_amt=$this->shg_outstanding_amt($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code);

$tcs_shg_drawing_power=$shg_max_credit_limit-$tcs_shg_outstanding_amt;

 $message="VALIDATING SHG Drawing power tcs_shg_drawing_power: $tcs_shg_drawing_power = shg_max_credit_limit: $shg_max_credit_limit  - tcs_shg_outstanding_amt: $tcs_shg_outstanding_amt";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

		//vo_amount
//$vo_total_credit_rs=mssql_fetch_array(mssql_query("select  credit_limit_pop+credit_limit_nonpop from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        //$vo_total_credit=$vo_total_credit_rs[0];
        $vo_total_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	 $vo_total_credit=$vo_total_credit_rs['ACTUAL_CREDIT_LIMIT'];

 
//$vo_outstanding=$this->vo_outstanding($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'1');
	
//$vo_drawing_power=$vo_total_credit-$vo_outstanding;


//$vo_outstanding_tcs=$this->vo_outstanding_tcs($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'1');
	
//$vo_drawing_power_tcs=$vo_total_credit-$vo_outstanding_tcs;

//total drawing power from dp_calculation_ivrs() start
 $CreditLimitsQry="select * from dp_calculation_ivrs() where TRANS_VO_ID='$void_code'";
 $vo_actual_credit_rs=mssql_fetch_array(mssql_query($CreditLimitsQry));
 $current_limit_vo_dp=$vo_actual_credit_rs["VO_TOTAL_DP"];
 $vo_drawing_power=$current_limit_vo_dp;
//total drawing power from dp_calculation_ivrs() end

	
$vo_category_drawing_power=$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$credit_lt_type,1);			
//$vo_category_drawing_power=$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$credit_lt_type,$project);			

 //$message="VALIDATING LOAN AMOUNT vo_credit_lt:$vo_category_drawing_power Greater than Equal loan_amount:$loan_amount,Loan amount $loan_amount LESS THAN EQUAL VO Drawing power $vo_drawing_power,VO drawing power from TCS Tables $vo_drawing_power_tcs Greater than Equal Loan AMT $loan_amount,loan_amount: $loan_amount LESS THAN EQUAL tcs_shg_drawing_power: $tcs_shg_drawing_power";
 
 $message="VALIDATING LOAN AMOUNT vo_credit_lt:$vo_category_drawing_power Greater than Equal loan_amount:$loan_amount,Loan amount $loan_amount LESS THAN EQUAL VO Drawing power";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
			  
  //if($vo_credit_lt>=$loan_amount && $shg_lt_max<=150000 && $shg_limit_max_tcs<=150000 && $vo_credit_max_tcs<=$vo_total_credit && $mms_credit_max_tcs<=$mms_total_credit )
fwrite(STDERR,"\n------$vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount && $loan_amount <= $tcs_shg_drawing_power-------");
  //{ 
  //if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount && $loan_amount <= $tcs_shg_drawing_power)
  //if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount)
  if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power) 
  {  
	
	  $message="Drawing Power Validation SERP 1";
 	$this->log_ivr($ivr_call_id,$message);
 
 
 //exit;
 //$echo_cmd="/bin/echo ivr_id $ivr_call_id uni $unique_id  credit_limit $vo_credit_lt >> /tmp/ivr.txt";
 //exec($echo_cmd);
 
 
 $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select * from IVRS_LOAN_REQUEST_LIVE(nolock) where  SHG_ID='$shg_code' and PROJECT_TYPE='74' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;

//Ashok Adding Current od and os to this member Start
		
				if($curr_odos!=0)
				{
					$shg_pending_ods_count=mssql_num_rows(mssql_query("select TOP 1 1 from SHG_MEMBER_PENDING_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
					if($shg_pending_ods_count>=1)
					{
					  $curr_odos=0;
				   	  $message="Already Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS for shg $shg_code";
				   	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					}
				}

//Ashok Adding Current od and os to this member End
	 
 
	$PROJECT_TYPE='74';  

	$this->chk_ln_status($shg_code,$member_id,$PROJECT_TYPE,$ivr_call_id);

	$loan_amount=$loan_amount+$curr_odos;

	$mms_dist_code=substr($void_code,2,2);
      $mms_mandal_code=substr($void_code,4,2);

$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos')";	
fwrite(STDERR,"\n-------$loanRequestInsertQry------\n");

if($caller!=$test_vonumber)
{
mssql_query($loanRequestInsertQry);

}
$curr_odos=0;

                $message="Insertion of SN Lead : $loanRequestInsertQry";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

$current_limit=$vo_drawing_power-$loan_amount;
//$current_limit=$vo_credit_lt-$loan_amount;
if($caller!=$test_vonumber)
{
mssql_query("update  IVRS_VO_CREDIT_LIMIT set $tbl_filed='$current_limit'  where vo_id='$void_code'");
}

if($member_type=='Y')
     {
	$pop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_pop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$pop_lmt=$pop_lmt_rs['current_limit_pop'];
	
		 if($pop_lmt>=$loan_amount)
		 {
	 if($caller!=$test_vonumber)
	 {
	 mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_pop=current_limit_pop-$loan_amount  where vo_id='$void_code'");
	 }
		 }
	 }
	 if($member_type=='N')
		{
			
	$nonpop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_nonpop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$nonpop_lmt=$nonpop_lmt_rs['current_limit_nonpop'];
	
	if($nonpop_lmt>=$loan_amount)
	{
		if($caller!=$test_vonumber)
		{
		mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_nonpop=current_limit_nonpop-$loan_amount  where vo_id='$void_code'");
		}
	}
	   }
	   

$vo_credit_limit=$current_limit;

 $member_limit=$member_limit-1; 
fwrite(STDERR,"\n\n-- member_limit : $member_limit-----\n\n");
   if($member_limit>=1 && $vo_credit_limit>=0)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{
	$amt_stat='Y';
	$this->bicycle_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
	}
	else
	{
	  if($vo_credit_limit>=0)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{
			
	$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
	
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}
 }
 else
 {

if($mms_credit_max_tcs>$mms_total_credit)
	 {
		$mms_dist_code=substr($void_code,2,2);
        $mms_mandal_code=substr($void_code,4,2); 
		$share = mssql_fetch_array(mssql_query("SELECT  SHARE_AMOUNT FROM SN.SHARE_BAL_FUN() where DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code' "));
		$share_amount=$share['SHARE_AMOUNT'];
		if($share_amount<1000000)
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/share_captial";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($share_amount,$agi);
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/paid_share_captial";
		$agi-> stream_file($wfile, '#');	
		}
		else
		{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
		}
	 }
	 else
	 {
    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
	 }

 //$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	
	$this->bicycle_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
 
   }
	
 }
 //echo  $loan_amount.$reason_loan.$member_type;
 
		}
		else
		  {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		 $this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		$agi->Hangup();
		    }
		}


function bicycle_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name)
{

	$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	//$value='101';
	if($value>='0')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	$agi-> stream_file($wfile, '#');
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 5000, 1);
	$status=$res_dtmf["result"];
	//$status='1';
		if($status=='1')
		{
		$diff_loan_amt=intval(substr($value,-2,2));
		if($diff_loan_amt=='0')
		{fwrite(STDERR,"----$value-----");
		 if($value<=5000 && $value>=3000)
		     {
		return $value;
		           }
				   else
				   {
					if($value>5000)
					{
					$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/bicycle_loan_morethan_5000";
	                $agi-> stream_file($wfile, '#');
					 }
					 if($value<3000)
					 {
					 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/bicycle_loan_lessthan_3000";
	                $agi-> stream_file($wfile, '#');
					 }
					$x=$x-1;
		      $loan_amount=$this->bicycle_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
			  return  $loan_amount;
			  
				  }
				  }
				   else
				 {
				 if($x>='1')
		 		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_multiples_100";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->bicycle_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
				 }
	
			
		}
		
		if($status=="2")
		{
		$loan_amount=$this->bicycle_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		}
		
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->bicycle_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
		}
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->bicycle_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		}
}



function smartphone_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund){
		$test_vonumber=$GLOBALS['test_vonumber'];			
 
 		$ivrs_array='';
		$ivrs_array=array();
		$ivrs_array['file']="mem_category";
		$ivrs_array['retries']=3;
		$ivrs_array['max_digits']=1;
		$ivrs_array['timeout']=5000;
		$ivrs_array['no_input']="invaliddata";
		$ivrs_array['verification_string']="A^response";
				
		$ivrs_array['1']="pop";
		$ivrs_array['2']="poor";
		
		$member_type=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
		
		
		if($member_type == ''){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
			$agi->Hangup();
			exit;
		}
	
	if($member_type == "pop"){
		$member_type="Y";
	}elseif($member_type == "poor"){
		$member_type="N";
	}
		
	$length='2';
	$play_msg='two_digit_member_id';
	$type='smartphone';
	$x='3';
	$db_filed=$member_type;
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE,IS_POP_MEM from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
  $IS_POP_MEM=$member_id_rs['IS_POP_MEM'];
 				
$message="Member details:  MEMBER_ID: $member_id , Short Code:  $member_short_code,member_uid: $member_uid , member_age:$member_age , IS_POP_MEM:$IS_POP_MEM , member_mobile_num: $member_mobile_num";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
 				
 				if($IS_POP_MEM != $member_type){
					if($IS_POP_MEM =='N' && $member_type=='Y')
						{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_pop";
						$agi-> stream_file($wfile, '#');	
						
						}
						if($IS_POP_MEM == 'Y' && $member_type == 'N')
						{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_non_pop";
						$agi-> stream_file($wfile, '#');
						}
					
					if($x>='1')
					{
					$x=$x-1;
					$this->smartphone_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
		mssql_close($link);
					$agi->Hangup();
					exit;
					}else{
		mssql_close($link);
					$agi->Hangup();
					exit;		
					}
				
				}

############	Member Validation new Start project type 72
		$project = '72';
		$message="SHG LIMITS in project $project at 3rd place";
		$this->log_ivr($ivr_call_id,$message);
		$shg_limit_array=$this->shg_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code);
		$shg_max_loans_total=$shg_limit_array[$shg_code]['shg_max_loans_total'];
		$shg_max_loans_ivrs=$shg_limit_array[$shg_code]['shg_max_loans_ivrs'];
		$shg_max_credit_limit=$shg_limit_array[$shg_code]['shg_max_credit_limit'];
/*
$distinct_member_ids_query = "select distinct MEMBER_ID from (
select I.VO_ID,I.SHG_ID,I.IVRS_ID,I.PROJECT_TYPE,I.MEMBER_ID,I.SHORT_CODE,SLA.STATUS_ID,SMLS.IS_CLOSED,sla.MEMBER_SHORT_CODE
from evgen.IVRS_LOAN_REQUEST I (nolock) 
left join SHG_LOAN_APPLICATION SLA (nolock) on I.IVRS_ID=SLA.REQUESTED_ID and SLA.SHG_ID=I.SHG_ID and isnull(I.MEMBER_ID,'')=isnull(SLA.MEMBER_LONG_CODE,'')
left join SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) SMLS on SMLS.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type in ('1','72') and SMLS.IS_CLOSED='1'
where I.shg_id like '$shg_code' and I.PROJECT_TYPE in('1','72') and I.MEMBER_ID is not null
 and isnull(I.IS_PROCESSED,'NOTDUPLICATE') !='D'
 ) A where (A.STATUS_ID<>'11'or a.STATUS_ID is null) and (A.IS_CLOSED is null or A.IS_CLOSED<>'1') ";
$distinct_member_ids_result = mssql_query($distinct_member_ids_query);
$distinct_member_ids_count  = mssql_num_rows($distinct_member_ids_result);
		if($distinct_member_ids_count>=$shg_max_loans_ivrs)
		{
$distinct_member_ids_exist_query = "select distinct MEMBER_ID from (
select I.VO_ID,I.SHG_ID,I.IVRS_ID,I.PROJECT_TYPE,I.MEMBER_ID,I.SHORT_CODE,SLA.STATUS_ID,SMLS.IS_CLOSED,sla.MEMBER_SHORT_CODE
from evgen.IVRS_LOAN_REQUEST I (nolock) 
left join SHG_LOAN_APPLICATION SLA (nolock) on I.IVRS_ID=SLA.REQUESTED_ID and SLA.SHG_ID=I.SHG_ID and isnull(I.MEMBER_ID,'')=isnull(SLA.MEMBER_LONG_CODE,'')
left join SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) SMLS on SMLS.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type in ('1','72') and SMLS.IS_CLOSED='1'
where I.shg_id like '$shg_code' and I.member_id='$member_id' and I.PROJECT_TYPE in('1') and I.MEMBER_ID is not null
 and isnull(I.IS_PROCESSED,'NOTDUPLICATE') !='D'
 ) A where (A.STATUS_ID<>'11'or a.STATUS_ID is null) and (A.IS_CLOSED is null or A.IS_CLOSED<>'1') ";
*/
$distinct_member_ids_exist_query = "SP_SMARTPHONE_LOANSVALIDATION '$shg_code','$member_id','72','$ivr_call_id','$shg_max_loans_total'";
$distinct_member_ids_exist_result = mssql_query($distinct_member_ids_exist_query);
//$distinct_member_ids_exist_count  = mssql_num_rows($distinct_member_ids_exist_result);
$distinct_member_ids_exist_row = mssql_fetch_array($distinct_member_ids_exist_result);
        $message="SP_SMARTPHONE_LOANSVALIDATION '$shg_code','$member_id','1','$ivr_call_id','$shg_max_loans_ivrs'--$distinct_member_ids_exist_row[STATUS]";
        $this->log_ivr($ivr_call_id,$message);

                                if($distinct_member_ids_exist_row[STATUS]=='LOAN ALLOWED')
                                {
$member_overdue_query="select LOAN_DUE from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='0'";

$member_overdue_rslt =mssql_query($member_overdue_query);


$row_count = mssql_num_rows($member_overdue_rslt);
if($row_count>0)
	{
		$member_overdue_row = mssql_fetch_array($member_overdue_rslt);
$aa = "<pre>".print_r($member_overdue_row,1).count($member_overdue_row)."</pre>";
fwrite(STDERR,"\n--proceed72--(".$aa.")");
		if($member_overdue_row[LOAN_DUE]>0)
		{
				if($x>='1')
					{
			
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_sn_overdue";
						$agi-> stream_file($wfile, '#');
						$this->play_amount($member_overdue_row[LOAN_DUE],$agi);
						$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    					$agi-> stream_file($wfile, '#');
						#$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
						#$agi-> stream_file($wfile, '#');
						$x=$x-1;
						$this->smartphone_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund);
						mssql_close($link);
						$agi->Hangup();
		  				exit;
		   			}else
		   			{
		    				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    				$agi-> stream_file($wfile, '#');
						mssql_close($link);
		   				$agi->Hangup();
		   				exit;
		   			}

		}
	}
				}
				else
				{
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_".$shg_max_loans_ivrs."_loans", 5000, 1);
		$more_res=$res_dtmf ["result"];
		$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		mssql_close($link);
		$agi->Hangup();
				}
//		}
		
############	Member Validation new End project type 72
	           	
  if(($member_age =="" || $member_age=="NULL")){
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_not_update";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> smartphone_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
	mssql_close($link);
	$agi->Hangup();
	exit;
  }
 elseif($member_age >= 63 || $member_age <= 18)
 {
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_btw_1860";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> smartphone_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
	mssql_close($link);
	$agi->Hangup();
	exit;
 }
 

	$member_applied_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type='72'"));
	
	$member_prev_loan_count_live = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id' and project_type='72'"));

$member_rej_cnt_lng = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and REQUESTED_ID!='201314' and project_type='72'"));

$member_rej_cnt_lng_tcs = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and project_type='72'"));

$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1' and a.project_type='72'"));

$member_other_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));

		if($member_applied_loan_count == 0  && $member_prev_loan_count_tcs ==0){
		 $member_repaid_loans=0;
		}

	
	$message="member_applied_loan_count-member_rej_cnt_lng+member_prev_loan_count_live-member_repaid_loans-member_corpus_loans:$member_applied_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_corpus_loans:::member_prev_loan_count_tcs-member_rej_cnt_lng_tcs+member_prev_loan_count_live-member_repaid_loans-member_corpus_loans:$member_prev_loan_count_tcs-$member_rej_cnt_lng_tcs+$member_prev_loan_count_live-$member_repaid_loans-$member_corpus_loans ::member_other_repaid_loans:$member_other_repaid_loans";
        $this->log_ivr($ivr_call_id,$message);
	
        $member_prev_loan_count=$member_applied_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans;

	$message="Member loans ".$member_applied_loan_count."-".$member_rej_cnt_lng."+".$member_prev_loan_count_live."-".$member_repaid_loans."-".$member_other_repaid_loans." = ".$member_prev_loan_count;

	$this->log_ivr($ivr_call_id,$message);	

	if($member_prev_loan_count != 0)
	{
	
	$message="Member loan_already_applied : member_prev_loan_count:".$member_prev_loan_count." member_repaid_loans : ".$member_repaid_loans;
	$this->log_ivr($ivr_call_id,$message);	
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> smartphone_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
		mssql_close($link);
	$agi->Hangup();
	exit;	
	}	
	if(strlen($member_mobile_num) == 10){
		
	}else{
    $length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
	if($x>='1')
		{
	 $x=$x-1;
	 $this->smartphone_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
		mssql_close($link);
	 $agi->Hangup();
	exit;
	 }else{
		mssql_close($link);
	$agi->Hangup();
	exit;		
	}
	
	}
	}
		$loan_type = "CONSUMPTION";
		$loan_category="NONIG";	
		$reason_loan="smartphone";

		$db_filed="required_loan";

		$type="amount";
		$length="4";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->smartphone_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
	 fwrite(STDERR,"------>>($reason_loan!='')&&($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && $member_prev_loan_count=='0' && strlen($member_id)>1)<<--------");
	 if($loan_amount>='1')
	 {
 if(($reason_loan!='')&&($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && $member_prev_loan_count=='0' && strlen($member_id)>1))
 {
  $message="CONDITION 4 WILL CHECK HERE";
	$this->log_ivr($ivr_call_id,$message);
 
 
 $duration='12';
 $etime=date('Y-m-d H:i:s');

if($loan_category == "NONIG")
{
$duration='24';
}


 $vo_actual_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
 $vo_actual_credit=$vo_actual_credit_rs['ACTUAL_CREDIT_LIMIT'];
 $vo_credit_pop=$vo_actual_credit_rs['POP_CREDIT_LIMIT'];
 $vo_credit_non_pop=$vo_actual_credit_rs['NONPOP_CREDIT_LIMIT']; 
 
 
 if($member_type=='Y')
     {
	 $member_cat='pop';
	 $search_cat='0';
	 $vo_cat_actual_limit=$vo_credit_pop;
	 //$tbl_filed='current_limit_pop';
	  //$vo_credit_pop=intval(ceil($vo_actual_credit/2));
	
	 
	 	if($loan_category == "IG")
		{
		$vo_fixed_credit=intval(ceil($vo_credit_pop*0.85));
		$tbl_filed='current_limit_pop_ig';
		$search_purpose="'Agriculture','Dairy','Income generation Activity','Weavers'";
		$search_cr_limit='credit_limit_pop_ig';
		$credit_lt_type="current_limit_pop_ig";
		}
		if($loan_category == "NONIG")
        {
	  $vo_fixed_credit=intval(floor($vo_credit_pop*0.15));
	  $tbl_filed='current_limit_pop_non_ig';
	  $search_cr_limit='credit_limit_pop_non_ig';
	  $search_purpose="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
	  $credit_lt_type="current_limit_pop_non_ig";
        }
		
	    }
		if($member_type=='N')
		{
			
		//$vo_credit_non_pop=intval(floor($vo_actual_credit/2));	
		$member_cat='non-pop';
		$search_cat='1';
		$vo_cat_actual_limit=$vo_credit_non_pop;
		 //$tbl_filed='current_limit_nonpop';
		 
		 
		  if($loan_category == "IG")
		{
		$vo_fixed_credit=intval(ceil($vo_credit_non_pop*0.85));	
		$tbl_filed='current_limit_nonpop_ig';
		$search_cr_limit='credit_limit_nonpop_ig';
		$search_purpose="'Agriculture','Dairy','Income generation Activity','Weavers'";
		$credit_lt_type="current_limit_nonpop_ig";
		}
		
		if($loan_category == "NONIG")
        {
		$vo_fixed_credit=intval(floor($vo_credit_non_pop*0.15));
	    $tbl_filed='current_limit_nonpop_non_ig';
		$search_cr_limit='credit_limit_nonpop_non_ig';
		$search_purpose="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
		$credit_lt_type="current_limit_nonpop_non_ig";
        }
		
		 }
	

	$applied_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE='1' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'")); 

	
        $applied_amt = $applied_rs['AMT'];
		
		$applied_rs_live=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='1'"));
        $applied_amt_live = $applied_rs_live['AMT_LIVE'];
		$applied_amt_live=intval($applied_amt_live);

		
		$vo_rej_amt_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE='1'  and STATUS_ID='11'"));		
$vo_rej_amt=$vo_rej_amt_rs['AMT_REJ'];
$vo_rej_amt=intval($vo_rej_amt);




//commented for automation
/*
   $vo_fixed_credit_rs=mssql_fetch_array(mssql_query("select $search_cr_limit from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
       $vo_fixed_credit = $vo_fixed_credit_rs[$search_cr_limit];
	   */
	   
	   $vo_repaid_cat_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='$search_cat' and PROJECT_TYPE='1'"));
        $repaid_cat_total = $vo_repaid_cat_rs['AMT_REPAID'];
		//added for automation
		$applied_total=$applied_amt+$applied_amt_live-$vo_rej_amt-$repaid_cat_total;
		
	if($applied_total < 0){
			$applied_total=0;
			
	$message="VO Repaid AMT is greater than applied amt,resetting the outstanding to 0, applied_total :$applied_total";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					
		}
		
		
		$vo_cat_limit=$vo_cat_actual_limit-$applied_total;
			if($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Weavers')
		{
			// $repaid_cat_total=intval(ceil($repaid_cat_total*0.85));
			 //$repaid_cat_total=intval($repaid_cat_total);
			 $vo_credit_lt=intval(ceil($vo_cat_limit*0.85));
		}
		if($reason_loan=='Education'||$reason_loan=='Marriage'||$reason_loan=='Emergency Needs/Health')
		{
			 //$repaid_cat_total=intval(floor($repaid_cat_total*0.15));
			 //$repaid_cat_total=intval($repaid_cat_total);
			  $vo_credit_lt=intval(ceil($vo_cat_limit*0.15));
		}
		$project=72;
		
		$message="shg_outstanding_amt in project $project at 2nd place";
		$this->log_ivr($ivr_call_id,$message);
		
	$tcs_shg_outstanding_amt=$this->shg_outstanding_amt($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code);

$tcs_shg_drawing_power=$shg_max_credit_limit-$tcs_shg_outstanding_amt;

 $message="VALIDATING SHG Drawing power tcs_shg_drawing_power: $tcs_shg_drawing_power = shg_max_credit_limit: $shg_max_credit_limit  - tcs_shg_outstanding_amt: $tcs_shg_outstanding_amt";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

		//vo_amount
//$vo_total_credit_rs=mssql_fetch_array(mssql_query("select  credit_limit_pop+credit_limit_nonpop from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        //$vo_total_credit=$vo_total_credit_rs[0];
        $vo_total_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	 $vo_total_credit=$vo_total_credit_rs['ACTUAL_CREDIT_LIMIT'];

 
//$vo_outstanding=$this->vo_outstanding($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'1');
	
//$vo_drawing_power=$vo_total_credit-$vo_outstanding;


//$vo_outstanding_tcs=$this->vo_outstanding_tcs($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'1');
	
//$vo_drawing_power_tcs=$vo_total_credit-$vo_outstanding_tcs;
	
$vo_category_drawing_power=$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$credit_lt_type,1);			
//$vo_category_drawing_power=$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$credit_lt_type,$project);			

 //$message="VALIDATING LOAN AMOUNT vo_credit_lt:$vo_category_drawing_power Greater than Equal loan_amount:$loan_amount,Loan amount $loan_amount LESS THAN EQUAL VO Drawing power $vo_drawing_power,VO drawing power from TCS Tables $vo_drawing_power_tcs Greater than Equal Loan AMT $loan_amount,loan_amount: $loan_amount LESS THAN EQUAL tcs_shg_drawing_power: $tcs_shg_drawing_power";
 $message="VALIDATING LOAN AMOUNT vo_credit_lt:$vo_category_drawing_power Greater than Equal loan_amount:$loan_amount,Loan amount $loan_amount LESS THAN EQUAL VO Drawing power $vo_drawing_power,loan_amount: $loan_amount LESS THAN EQUAL tcs_shg_drawing_power: $tcs_shg_drawing_power";
 
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
			  
  //if($vo_credit_lt>=$loan_amount && $shg_lt_max<=150000 && $shg_limit_max_tcs<=150000 && $vo_credit_max_tcs<=$vo_total_credit && $mms_credit_max_tcs<=$mms_total_credit )
fwrite(STDERR,"\n------$vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount && $loan_amount <= $tcs_shg_drawing_power-------");
  //{ 
  //if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount && $loan_amount <= $tcs_shg_drawing_power)
  if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $loan_amount <= $tcs_shg_drawing_power)
  {  
 
 //exit;
 //$echo_cmd="/bin/echo ivr_id $ivr_call_id uni $unique_id  credit_limit $vo_credit_lt >> /tmp/ivr.txt";
 //exec($echo_cmd);
 
	$message="Drawing Power Validation SERP 2";
 	$this->log_ivr($ivr_call_id,$message); 	


 $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select * from IVRS_LOAN_REQUEST_LIVE(nolock) where  SHG_ID='$shg_code' and PROJECT_TYPE='72' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;

//Ashok Adding Current od and os to this member Start
		
				if($curr_odos!=0)
				{
					$shg_pending_ods_count=mssql_num_rows(mssql_query("select TOP 1 1 from SHG_MEMBER_PENDING_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
					if($shg_pending_ods_count>=1)
					{
					  $curr_odos=0;
				   	  $message="Already Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS for shg $shg_code";
				   	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					}
				}

//Ashok Adding Current od and os to this member End
	 
 
	$PROJECT_TYPE='72';  

	$this->chk_ln_status($shg_code,$member_id,$PROJECT_TYPE,$ivr_call_id);

	$loan_amount=$loan_amount+$curr_odos;

	$mms_dist_code=substr($void_code,2,2);
      $mms_mandal_code=substr($void_code,4,2);

$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos')";	


if($caller!=$test_vonumber)
{
mssql_query($loanRequestInsertQry);

}
$curr_odos=0;

                $message="Insertion of SN Lead : $loanRequestInsertQry";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

$current_limit=$vo_drawing_power-$loan_amount;
//$current_limit=$vo_credit_lt-$loan_amount;
if($caller!=$test_vonumber)
{
mssql_query("update  IVRS_VO_CREDIT_LIMIT set $tbl_filed='$current_limit'  where vo_id='$void_code'");
}

if($member_type=='Y')
     {
	$pop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_pop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$pop_lmt=$pop_lmt_rs['current_limit_pop'];
	
		 if($pop_lmt>=$loan_amount)
		 {
	 if($caller!=$test_vonumber)
	 {
	 mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_pop=current_limit_pop-$loan_amount  where vo_id='$void_code'");
	 }
		 }
	 }
	 if($member_type=='N')
		{
			
	$nonpop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_nonpop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$nonpop_lmt=$nonpop_lmt_rs['current_limit_nonpop'];
	
	if($nonpop_lmt>=$loan_amount)
	{
		if($caller!=$test_vonumber)
		{
		mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_nonpop=current_limit_nonpop-$loan_amount  where vo_id='$void_code'");
		}
	}
	   }
	   

$vo_credit_limit=$current_limit;

 $member_limit=$member_limit-1; 
   if($member_limit>=1 && $vo_credit_limit>=0)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{
	$amt_stat='Y';
	$this->smartphone_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
	}
	else
	{
	  if($vo_credit_limit>=0)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{
			
	$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
	
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}
 }
 else
 {

if($mms_credit_max_tcs>$mms_total_credit)
	 {
		$mms_dist_code=substr($void_code,2,2);
        $mms_mandal_code=substr($void_code,4,2); 
		$share = mssql_fetch_array(mssql_query("SELECT  SHARE_AMOUNT FROM SN.SHARE_BAL_FUN() where DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code' "));
		$share_amount=$share['SHARE_AMOUNT'];
		if($share_amount<1000000)
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/share_captial";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($share_amount,$agi);
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/paid_share_captial";
		$agi-> stream_file($wfile, '#');	
		}
		else
		{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
		}
	 }
	 else
	 {
    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
	 }

 //$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	
	$this->smartphone_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
 
   }
	
 }
 //echo  $loan_amount.$reason_loan.$member_type;
 
		}
		else
		  {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		 $this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		$agi->Hangup();
		    }
		}


function smartphone_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name)
{

	$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	//$value='101';
	if($value>='0')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	$agi-> stream_file($wfile, '#');
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 5000, 1);
	$status=$res_dtmf["result"];
	//$status='1';
		if($status=='1')
		{
		$diff_loan_amt=intval(substr($value,-2,2));
		if($diff_loan_amt=='0')
		{fwrite(STDERR,"----$value-----");
		 if($value<=6000 && $value>=1000)
		     {
		return $value;
		           }
				   else
				   {
					if($value>6000)
					{
					$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/smartphone_loan_morethan_6000";
	                $agi-> stream_file($wfile, '#');
					 }
					 if($value<1000)
					 {
					 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/smartphone_loan_lessthan_1000";
	                $agi-> stream_file($wfile, '#');
					 }
					$x=$x-1;
		      $loan_amount=$this->smartphone_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
			  return  $loan_amount;
			  
				  }
				  }
				   else
				 {
				 if($x>='1')
		 		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_multiples_100";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->smartphone_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
				 }
	
			
		}
		
		if($status=="2")
		{
		$loan_amount=$this->smartphone_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		}
		
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->smartphone_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
		}
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->smartphone_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		}
}

function IHHL_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) 

{

	$length='2';
	$play_msg='two_digit_member_id';
	$type='IHHL';
	$x='3';
	$db_filed=$member_type;
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE,IS_POP_MEM from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
  $IS_POP_MEM=$member_id_rs['IS_POP_MEM'];
 				
$message="Member details:  MEMBER_ID: $member_id , Short Code:  $member_short_code,member_uid: $member_uid , member_age:$member_age , IS_POP_MEM:$IS_POP_MEM , member_mobile_num: $member_mobile_num";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

//START CHECKING IN IHHL_TAGGED_MEMBER_DETAILS

$ihhl_member_count_res=mssql_query("select * from IHHL_TAGGED_MEMBER_DETAILS with (nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id'");
$ihhl_member_count = mssql_num_rows($ihhl_member_count_res);
	if($ihhl_member_count == 0){
		if($x>='1')
		{
			$x=$x-1;
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/voice_IHHL_MEMBER";
		$agi-> stream_file($wfile, '#');
	$this-> IHHL_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }	
	}

//END CHECKING IN IHHL_TAGGED_MEMBER_DETAILS

//START CHECKING MEMBER_OVERDUE
$ihhl_member_overdue_res=mssql_fetch_array(mssql_query("select LOAN_DUE from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED=0"));

if($ihhl_member_overdue_res['LOAN_DUE']>0)
{
		if($x>='1')
		{
		$x=$x-1;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_sn_overdue"; //member overdue
		$agi-> stream_file($wfile, '#');
		$this->play_amount($ihhl_member_overdue_res[LOAN_DUE],$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;

	$this-> IHHL_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    $agi->Hangup();
		    exit;
		   }
}
//END CHECKING MEMBER_OVERDUE

if(($member_age =="" || $member_age=="NULL")){
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_not_update";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> IHHL_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	mssql_close($link);
	$agi->Hangup();
	exit;
  }
 elseif($member_age >= 63 || $member_age <= 18)
 {
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_btw_1860";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> IHHL_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	mssql_close($link);
	$agi->Hangup();
	exit;
 }
 
	if(strlen($member_mobile_num) == 10){
		
	}else{
    $length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
	if($x>='1')
		{
	 $x=$x-1;
	 $this->IHHL_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		mssql_close($link);
	 $agi->Hangup();
	exit;
	 }else{
		mssql_close($link);
	$agi->Hangup();
	exit;		
	}
	
	}
	}
	$amt_stat='Y';
    if($amt_stat=='Y')
	{
		$db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
//	 $loan_amount=$this->IHHL_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name); //removing amount capture

	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount('12000',$agi);
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	$agi-> stream_file($wfile, '#');
	$loan_amount = 12000;
	 }

//  if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount && $loan_amount <= $tcs_shg_drawing_power)
  {  
 
 //exit;
 //$echo_cmd="/bin/echo ivr_id $ivr_call_id uni $unique_id  credit_limit $vo_credit_lt >> /tmp/ivr.txt";
 //exec($echo_cmd);
 
 
 $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select * from IVRS_LOAN_REQUEST_LIVE(nolock) where  SHG_ID='$shg_code' and PROJECT_TYPE='71' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;

//Ashok Adding Current od and os to this member Start
		
				if($curr_odos!=0)
				{
					$shg_pending_ods_count=mssql_num_rows(mssql_query("select TOP 1 1 from SHG_MEMBER_PENDING_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
					if($shg_pending_ods_count>=1)
					{
					  $curr_odos=0;
				   	  $message="Already Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS for shg $shg_code";
				   	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					}
				}

//Ashok Adding Current od and os to this member End
	 
 
	$PROJECT_TYPE='71';  

	$loan_amount=$loan_amount;//+$curr_odos;	

 $duration='12';
 $etime=date('Y-m-d H:i:s');
 $reason_loan = 'IHHL';
 $member_type='N';
	$mms_dist_code=substr($void_code,2,2);
      $mms_mandal_code=substr($void_code,4,2);

if($caller!=$test_vonumber)
{
$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$IS_POP_MEM','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos')";	
fwrite(STDERR,"------$loanRequestInsertQry-------");
mssql_query($loanRequestInsertQry);
}
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];

	if(intval($value)=='1')
	{
	$amt_stat='Y';
	$this->IHHL_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	}
	else
	{
	  if($vo_credit_limit>=0)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{
			
	$this->IHHL_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	//$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}
} 		
}

function IHHL_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name)
{

	$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	//$value='101';
	if($value>='0')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	$agi-> stream_file($wfile, '#');
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 5000, 1);
	$status=$res_dtmf["result"];
	//$status='1';
		if($status=='1')
		{
		$diff_loan_amt=intval(substr($value,-2,2));
		if($diff_loan_amt=='0')
		{fwrite(STDERR,"----$value-----");
		 if($value<=12000 && $value>=6000)
		     {
		return $value;
		           }
				   else
				   {
					if($value>12000)
					{
					$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/IHHL_loan_more_12000";
	                $agi-> stream_file($wfile, '#');
					 }
					 if($value<6000)
					 {
					 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/IHHL_loan_more_6000";
	                $agi-> stream_file($wfile, '#');
					 }
					$x=$x-1;
		      $loan_amount=$this->IHHL_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
			  return  $loan_amount;
			  
				  }
				  }
				   else
				 {
				 if($x>='1')
		 		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_multiples_100";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->IHHL_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
				 }
	
			
		}
		
		if($status=="2")
		{
		$loan_amount=$this->IHHL_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		}
		
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->IHHL_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
		}
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->IHHL_vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		}
}

function record_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$record_confirm,$record_propmt,$language){
		$test_vonumber=$GLOBALS['test_vonumber'];
$recordfilename=$caller."-".$ivr_call_id."-".date('YmdHis');
$record_confirm='1';
		    $filer="/tmp/".$recordfilename ;
		   $i=2;
		   $retry=0;
				while($i!='1' && $retry<'3')
				{	//echo $record_propmt;
					if($record_propmt == ""){
					$record_propmt="/var/lib/asterisk/sounds/vo_ivrs/$language/record_shgname_press_hash";
					}else{
					//$record_propmt='/var/lib/asterisk/sounds/messagebox/recordtone#';
					}
					
					$agi-> stream_file($record_propmt,'#');
					$agi-> record_file($filer, 'wav', '#', '640000', '0', 1, '10');
					$uhaverecorded_prompt="/var/lib/asterisk/sounds/vo_ivrs/$language/recorded_shg_name";
					$agi-> stream_file($uhaverecorded_prompt,'#');
					$agi-> stream_file($filer, '#');
					$i=2;
					
					if($record_confirm == "1"){
					$prompt="/var/lib/asterisk/sounds/vo_ivrs/$language/toconfirm1otherwise2";
					$res_dtmf=$agi->get_data($prompt, 5000, 1);
					$i=$res_dtmf ["result"];
					$confirmation=$i;
					}else{
					$i=1;	
					}
					$retry++;
				}
				if($i != "1" && $record_confirm == "1" && $retry=='3'){
					$prompt="/var/lib/asterisk/sounds/vo_ivrs/$language/retry_exceeded";
					$agi-> stream_file($prompt, 250, 1);
					$confirmation="0";
					//return '0';
					}
				$l="lame /tmp/".$recordfilename.".wav /var/www/shg_names_record/mp3/".$recordfilename.".mp3";
		        exec($l);
				//$recordfilename=$recordfilename."^".$confirmation;
				return $recordfilename;
 }	
	


function pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst)	
{
	$test_vonumber=$GLOBALS['test_vonumber'];
	
	$length='2';
	$play_msg='two_digit_member_id';
	$type='member_id';
	$x='3';
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
	$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$member_short_code' and IS_POP='Y'"));

 $member_id=$member_id_rs['MEMBER_ID'];
	 $SP_ACTIVITY=$member_id_rs['SP_ACTIVITY'];	
	   $member_mobile_num=$member_id_rs['MOBILE_NUM'];
// if($SP_ACTIVITY == "26" || $SP_ACTIVITY == "27" || $SP_ACTIVITY == "36" || $SP_ACTIVITY == "37" ){
//    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/eligible_for_other_sthreenidhi";
//	$agi-> stream_file($wfile, '#');
//	$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
//	$agi->Hangup();
//		mssql_close($link);
//	exit;	
//	
// }
 
	//$vo_id_mandal=substr($void_code,0,6);
	
	$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and PROJECT_TYPE in ('2','3','4')"));
	
	if($member_prev_loan_count>=1)
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
		mssql_close($link);
	$agi->Hangup();
	exit;	
	}
		
	$length='6';
	$play_msg='six_digit_ca_id';
	$type='ca_id';
	$x='3';
	$ca_id_new=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	$vo_id_mandal=substr($void_code,0,6);
	
	$prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID like '$vo_id_mandal%' and COMBINED_ID='$ca_id_new' and PROJECT_TYPE in ('2','3','4')"));
	if($prev_loan_count>=1)
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
		mssql_close($link);
	$agi->Hangup();
	exit;	
	}
	
		$db_filed="reason";
		$type='why_loan';
		$length='5';
		$play_msg="hlp_activity_code";
		$x='3';
 $reason_loan_code=$this->hlp_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
 
 $hlp_loan_rs=mssql_fetch_array(mssql_query("SELECT SN_GROUPING FROM  HLP_ACTIVITIES(nolock) where ACTIVITY_CODE='$reason_loan_code'"));
 $reason_loan=$hlp_loan_rs['SN_GROUPING'];
  if($caller=='9494464446')	
	  {
	  $reason_loan='Income generation Activity';
	  }
	
  $db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->get_pop_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
 
 if((intval($reason_loan_code)>0 && intval($reason_loan_code)<=126 && strlen($ca_id_new)==6 && strlen($member_short_code)==2 && $member_prev_loan_count=='0' && $prev_loan_count=='0' && $loan_amount<=50000 )&&($reason_loan=='Income generation Activity'||$reason_loan=='Dairy'||$reason_loan=='Agriculture'))
 {
	 
	 //reason master data
    //$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select CURRENT_CREDIT_LIMIT from IVRS_VO_POP_CREDIT_LIMIT  where vo_id='$void_code'"));
    //$vo_credit_lt_pop = $vo_credit_pop_rs['CURRENT_CREDIT_LIMIT'];
	
if($loan_amount<=25000)
{
$fund_sn=$loan_amount;
$fund_pop=0;
$PROJECT_TYPE='3';	
}
if($loan_amount>25000)
{
$fund_sn=25000;
$fund_pop=$loan_amount-$fund_sn;
$PROJECT_TYPE='4';
}

	
	$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select ACTUAL_CREDIT_LIMIT from VO_POP_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
    $vo_actual_credit_lt_pop = $vo_credit_pop_rs['ACTUAL_CREDIT_LIMIT'];
	
	$vo_pop_applied_rs=mssql_fetch_array(mssql_query("select SUM(FUND_SN) from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code'  and PROJECT_TYPE in ('2','3','4')"));

		$vo_pop_applied=$vo_pop_applied_rs[0];
		$vo_pop_applied=$vo_pop_applied+$fund_sn;
		
//adding rejected amount
		
$crdits_count=mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE='2' and VO_ID='$void_code' and LOAN_AMOUNT>5000"));
	
	$rej_id=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE='2' and VO_ID='$void_code' and LOAN_AMOUNT>5000"));
	
	
	$pop_applied_amount=$crdits_count*5000;
	$rej_amt_t2=$rej_id[0];
	$sn_rej_amount=$rej_amt_t2-$pop_applied_amount;
	
	$rej_amt_type3=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE='3' and VO_ID='$void_code'"));
	$rej_amt_t3=intval($rej_amt_type3[0]);
	
	$rej_cnt_type4=mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE='4' and VO_ID='$void_code'"));
	$rej_amt_t4=$rej_cnt_type4*25000;
	
	/*
	//ccl credits
	$vo_repaid_pop_t2=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_POP from SN.SHG_MEMBER_REPAY_VIEW  where vo_id='$void_code' and PRODUCT='POP' and PPR>5000"));
        $vo_repaid_amt_pop_t2 = $vo_repaid_pop_t2['AMT_REPAID_POP'];
	*/
	
	$vo_tot_sn_rej=	$sn_rej_amount+$rej_amt_t3+$rej_amt_t4;
	
	$vo_pop_applied=$vo_pop_applied-$vo_tot_sn_rej;
	
	$vo_credit_lt_pop=$vo_actual_credit_lt_pop-$vo_pop_applied;
	
	
	/*
	$vo_credit_sn_rs=mssql_fetch_array(mssql_query("select ACTUAL_CREDIT_LIMIT from VO_CREDIT_LIMIT  where vo_id='$void_code'"));
    $vo_actual_credit_lt_sn = $vo_credit_sn_rs['ACTUAL_CREDIT_LIMIT'];
	
	$vo_sn_applied_rs=mssql_fetch_array(mssql_query("select SUM(FUND_SN) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code'  and PROJECT_TYPE in ('2','3','4')"));

		$vo_sn_applied=$vo_sn_applied_rs[0];
		$vo_sn_applied=$vo_sn_applied+$fund_sn;
		
	$vo_credit_lt_sn=$vo_actual_credit_lt_sn-$vo_sn_applied;
		*/
	 
$shg_pop_lt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('2','3','4')"));

		$shg_pop_lt_max=$shg_pop_lt_rs[0];
		$shg_pop_lt_max=$shg_pop_lt_max+$loan_amount;
			
$shg_pop_lt_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('2','3','4') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));

		$shg_pop_lt_tcs=$shg_pop_lt_tcs_rs[0];
		$shg_pop_lt_tcs=$shg_pop_lt_tcs+$loan_amount;
		
$shg_sn_fund_rs=mssql_fetch_array(mssql_query("select SUM(FUND_SN) from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('2','3','4')"));

		$shg_sn_fund_lt_max=$shg_sn_fund_rs[0];
		$shg_sn_fund_lt_max=$shg_sn_fund_lt_max+$fund_sn;
			
$shg_pop_fund_lt_rs=mssql_fetch_array(mssql_query("select SUM(FUND_POP) from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('2','3','4')"));

		$shg_pop_fund_lt_max=$shg_pop_fund_lt_rs[0];
		$shg_pop_fund_lt_max=$shg_pop_fund_lt_max+$fund_pop;						

		
 $duration='36';
 $etime=date('Y-m-d H:i:s');	
// $PROJECT_TYPE='2';	  
 $member_type='Y';
 //&& $vo_credit_lt_sn>=$fund_sn
 
  if($loan_amount>=1000 && $loan_amount<=50000 && $shg_pop_lt_max<=400000 && $vo_credit_lt_pop>=$fund_sn  && $shg_pop_lt_tcs<=400000 && $shg_sn_fund_lt_max<=200000 && $shg_pop_fund_lt_max<=200000)
  {   
 //$marriage_amt='0';
  $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select SHG_IVRS_LOAN_NO from IVRS_LOAN_REQUEST_LIVE(nolock) where  SHG_ID='$shg_code' and PROJECT_TYPE in ('2','3','4') order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;
 // $reason_loan_code has to be added

if($caller!=$test_vonumber)
{
mssql_query("insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,COMBINED_ID,HLP_CODE,IS_OVERDUE,OVERDUE_ID,SHORT_CODE,MEMBER_ID,FUND_SN,FUND_POP) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$ca_id_new','$reason_loan_code','$is_over_due','$due_id_lst','$member_short_code','$member_id','$fund_sn','$fund_pop')");
}

$current_limit=$vo_credit_lt_pop-$loan_amount;
//mssql_query("update IVRS_VO_POP_CREDIT_LIMIT set CURRENT_CREDIT_LIMIT='$current_limit'  where vo_id='$void_code'");
$vo_credit_limit=$current_limit;


   if($vo_credit_limit>=1000)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{  
	$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
	}
	else
	{
	  if($vo_credit_limit>=1000)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{
			
	$this->pop_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}


/*
 $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
  if(intval($value)=='1')
	{
  $this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
	}
	else
	{
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	if(intval($value_shg)=='1')
	{
	$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
	$agi->Hangup();
		mssql_close($link);
	exit;
	}
	
	}
	*/
	   

 }
 else
 {
	   if($x>='1')
		{
			if($loan_amount>50000)		
			{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_50000";
	      $agi-> stream_file($wfile, '#');	
			}
			else
			{
 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
			}
			$x=$x-1;
	$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 
   }
	
 }
 else
 {
	    if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
			$x=$x-1;
	$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 }
 
 
		


}



function get_pop_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name)
	{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	if($value>='0')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	$agi-> stream_file($wfile, '#');
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 5000, 1);
	$status=$res_dtmf["result"];
	//$status='1';
		if($status=='1')
		{
		$diff_loan_amt=intval(substr($value,-2,2));
		if($diff_loan_amt=='0')
		{
		
		 if($value<=50000 && $value>=1000)
		     {

		return $value;
		           }
				   else
				   {
			
					if($value>50000)
					{
		            //$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_less_15000";
					$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_50000";
	                $agi-> stream_file($wfile, '#');
					 }
					 if($value<1000)
					 {
					 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_morethan_1000";
	                $agi-> stream_file($wfile, '#');
					 }
					$x=$x-1;
		      $loan_amount=$this->get_pop_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
			  return  $loan_amount;
			  
				  }
				  }
				   else
				 {
				 if($x>='1')
		 		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_multiples_100";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->get_pop_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
				 }
	
			
		}
		
		if($status=="2")
		{
		$loan_amount=$this->get_pop_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		}
		
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->get_pop_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
		}
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->get_pop_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		}
	
	}


function get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code)
	{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	$vo_mandal_code=substr($void_code,2,4);
	if($type=="ca_id")
	{
$vo_id_mandal=substr($void_code,0,6);
$status_valied=mssql_num_rows(mssql_query("select COMBINED_ID from POP_DATA(nolock)  where MANDAL_ID='$vo_mandal_code' and COMBINED_ID='$value'"));
$prev_ca_count=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID like '$vo_id_mandal%'  and COMBINED_ID='$value' and PROJECT_TYPE in ('2','3','4')"));
//$status_valied=mssql_num_rows(mssql_query("select COMBINED_ID from POP_DATA  where SN_VO_ID='$void_code' and COMBINED_ID='$value'"));
	}
	if($type=="member_id")
	{
	$status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and IS_POP_MEM='Y'"));	
//	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and PROJECT_TYPE in ('2','3','4')"));
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and PROJECT_TYPE in ('2','3','4') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
	}
	
//sthreenidhi

	if($type=="sthreenidhi")
	{
//	$status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$value' "));

	$rslt=mssql_query("select MEMBER_ID,AGE from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$value' ");
	$status_valied=mssql_num_rows($rslt);
	$member_id_rs=mssql_fetch_array($rslt);
 	$member_id=$member_id_rs['MEMBER_ID'];
	$member_age=$member_id_rs['AGE'];

//Member Age validation start 08-05-2017
	if($status_valied<1)
	{
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	}
  if(($member_age =="" || $member_age=="NULL")){
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_not_update";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	exit;
  }
 elseif($member_age >= 63 || $member_age <= 18)
 {
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_btw_1860";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	exit;
 }
//Member Age validation end 08-05-2017

	//$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and (SHORT_CODE='$value' ) and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));

	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id'  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and PROJECT_TYPE='1'"));

	//$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_SHORT_CODE='$value'  and STATUS_ID='11' and  PROJECT_TYPE in ('11','1')")); 

	//$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_SHORT_CODE='$value'  and STATUS_ID='11' ")); 

	$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and PROJECT_TYPE='1'")); 	

//	$member_repaid_loans = mssql_num_rows(mssql_query("select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$value' and IS_CLOSED='1'"));

	$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1' and PROJECT_TYPE='1'"));

	$member_repaid_loans_tsp = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));
	
	/*if($member_repaid_loans == 1){
		 $member_installments_rs=mssql_fetch_array(mssql_query("select INST_NO from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$value'"));
 $member_installments=$member_installments_rs['INST_NO'];
 if($member_installments < "20"){
 	$member_repaid_loans=0;
 }
 
	}*/
	
		if($member_before_loan == 0 ){
		 $member_repaid_loans=0;
		}

	$member_before_loan=$member_before_loan-$member_rej_cnt_sht-$member_repaid_loans;
	//$member_before_loan=$member_before_loan-$member_rej_cnt_sht-$member_repaid_loans-$member_repaid_loans_tsp;
	 
	if($member_before_loan < 0){
		$member_before_loan=0;
	}
	}	
	
if($type=="corpus_loan")
	{
	$rslt=mssql_query("select MEMBER_ID,AGE from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$value' and IS_POP_MEM='$db_filed' ");
	$status_valied=mssql_num_rows($rslt);
	$member_id_rs=mssql_fetch_array($rslt);
 	$member_id=$member_id_rs['MEMBER_ID'];
	$member_age=$member_id_rs['AGE'];

//Member Age validation start 08-05-2017
	if($status_valied<1)
	{
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	}
  if(($member_age =="" || $member_age=="NULL")){
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_not_update";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	exit;
  }
 elseif($member_age >= 63 || $member_age <= 18)
 {
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_btw_1860";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	exit;
 }
//Member Age validation end 08-05-2017



	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and (SHORT_CODE='$value' ) and project_type='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
	//$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_SHORT_CODE='$value'  and STATUS_ID='11' and  PROJECT_TYPE in ('11','1')")); 
	$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_SHORT_CODE='$value'  and STATUS_ID='11'  and project_type='53'")); 
	
	$member_repaid_loans = mssql_num_rows(mssql_query("select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$value' and IS_CLOSED='1'"));
	
	
	/*if($member_repaid_loans == 1){
		 $member_installments_rs=mssql_fetch_array(mssql_query("select INST_NO from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$value'"));
 $member_installments=$member_installments_rs['INST_NO'];
 if($member_installments < "20"){
 	$member_repaid_loans=0;
 }
 
	}*/
	
		if($member_before_loan == 0 ){
		 $member_repaid_loans=0;
		}

	
	
	$member_before_loan=$member_before_loan-$member_rej_cnt_sht-$member_repaid_loans;
	
	 
	if($member_before_loan < 0){
		$member_before_loan=0;
	}
	           	if($status_valied=='0' && $db_filed=='Y')
				{
				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_pop";
				$agi-> stream_file($wfile, '#');	
				}
				if($status_valied=='0' && $db_filed=='N')
				{
				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_non_pop";
				$agi-> stream_file($wfile, '#');
				}
	}	
	
	
	
	
if($type=="short_term")
	{
	$status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value'"));
	}
	
if($type=="project")
		{
    $status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and ACTIVITY='43' "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and PROJECT_TYPE='43'"));	
		}	
		
if($type=="project_others")
		{
    $status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and ACTIVITY='54' "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and PROJECT_TYPE='54'"));	
		}			

if($type=="project_vle")
		{
    $status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and ACTIVITY='25' "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and PROJECT_TYPE='25'"));	
		}	
		
		
			
	
	if($type=="MART" || $type=="PRODUCER"|| $type=="KNOWLEDGE" || $type=="NEW_ENTERPRISE" || $type == "EXISTING_ENTERPRISE")
	{
	if($type=="MART")	
		
		{
		$status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and ACTIVITY='9' "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value'"));	
		}
		if($type=="PRODUCER")
		{
    $status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and ACTIVITY='8' "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value'"));	
		}
			if($type=="KNOWLEDGE")
		{
    $status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and ACTIVITY in ('16','17') "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value'"));	
		}
		
		if($type=="NEW_ENTERPRISE"){
	$status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and ACTIVITY in ('30') "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value'"));			
			
		}
		
		if($type=="EXISTING_ENTERPRISE"){
	$status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and ACTIVITY in ('31') "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value'"));			
			
		}
	}
	
if($type=="mobile")
		{
			$start_digit=substr($value,0,1);
			
			if($value>='1' && strlen($value)=='10' && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
			{
			 $upper_limit='0';	
			}
			else
			{
       $upper_limit=$upper_limit-1; 
			}
	 }	
			
	if($type=="SCP_MEMBER")	
		
		{
//		$status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and sp_activity='$db_filed'"));	
//$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value'"));
//if($member_before_loan=='0')
//{	
//$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_SHORT_CODE='$value'"));	
//}

$status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$value' and sp_activity='$db_filed' "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and PROJECT_TYPE='$db_filed'"));
	$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where SHG_ID='$shg_code'  and  MEMBER_SHORT_CODE='$value'  and STATUS_ID='11' and  PROJECT_TYPE='$db_filed'")); 
	$member_before_loan=$member_before_loan-$member_rej_cnt_sht;


		}
		
		
		if($type=="IWMP_MEMBER")	
		{
		
		$status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$value' and sp_activity in ('38','39','40') "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and PROJECT_TYPE in ('38','39','40')"));
	$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where SHG_ID='$shg_code'  and  MEMBER_SHORT_CODE='$value'  and STATUS_ID='11' and  PROJECT_TYPE in ('38','39','40')")); 
//	$member_repaid_loans = mssql_num_rows(mssql_query("select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where  SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$value' and IS_CLOSED='1'"));
$member_before_loan=$member_before_loan-$member_rej_cnt_sht;
	
			
		}
		
		if($type=="CMSA_member")	
		
		{
//echo "select MEMBER_ID from SHG_MEMBER_INFO  where  SHG_ID='$shg_code' and SHORT_CODE='$value' and sp_activity='$db_filed'";
$status_valied=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$value' and sp_activity='$db_filed' "));	
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$value' and PROJECT_TYPE='$db_filed'"));
	$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_SHORT_CODE='$value'  and STATUS_ID='11' and  PROJECT_TYPE='$db_filed'")); 
	$member_repaid_loans = mssql_num_rows(mssql_query("select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where  SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED='1'"));
$member_before_loan=$member_before_loan-$member_rej_cnt_sht-$member_repaid_loans;

		}
	
		if($type=="BASE")	
		
		{
		$status_valied=mssql_num_rows(mssql_query("select BASELINE_ID from SP_BASELINE_INFO(nolock)  where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$upper_limit' and BASELINE_ID='$value' and SP_ACTIVITY='$db_filed'"));
		$member_before_loan=0;
		//$member_before_loan=mssql_num_rows(mssql_query("select BASELINE_ID from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code' and SHG_ID='$shg_code' and SHORT_CODE='$upper_limit' and BASELINE_ID='$value'"));		
		}
if($type=='IHHL')
	{
	$rslt=mssql_query("select MEMBER_ID,AGE from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$value' ");
	$status_valied=mssql_num_rows($rslt);
	$member_id_rs=mssql_fetch_array($rslt);
 	$member_id=$member_id_rs['MEMBER_ID'];
	$member_age=$member_id_rs['AGE'];

//Member Age validation start 08-05-2017
	if($status_valied<1)
	{
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	}
  if(($member_age =="" || $member_age=="NULL")){
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_not_update";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	exit;
  }
 elseif($member_age >= 63 || $member_age <= 18)
 {
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_btw_1860";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	exit;
 }
//Member Age validation end 08-05-2017

####### STRAT

	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id'  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type='71'"));

	$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and project_type='71'")); 	

	$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1' and project_type='71'"));

	$member_repaid_loans_tsp = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));

		if($member_before_loan == 0 ){
		 $member_repaid_loans=0;
		}
//	$member_before_loan=$member_before_loan-$member_rej_cnt_sht-$member_repaid_loans-$member_repaid_loans_tsp; 
	$member_before_loan=$member_before_loan-$member_rej_cnt_sht;// should not allow one more IHHL loan to member who availed ihhl loan already.
	 
	if($member_before_loan < 0){
		$member_before_loan=0;
	}
####### END
	}
if($type=='bicycle')
	{
	$rslt=mssql_query("select MEMBER_ID,AGE from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$value' ");
	$status_valied=mssql_num_rows($rslt);
	$member_id_rs=mssql_fetch_array($rslt);
 	$member_id=$member_id_rs['MEMBER_ID'];
	$member_age=$member_id_rs['AGE'];

//Member Age validation start 08-05-2017
	if($status_valied<1)
	{
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	}
  if(($member_age =="" || $member_age=="NULL")){
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_not_update";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	exit;
  }
 elseif($member_age >= 63 || $member_age <= 18)
 {
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_btw_1860";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	exit;
 }
//Member Age validation end 08-05-2017

####### STRAT

	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id'  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type='74'"));

	$member_before_loan_live=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id'  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type='74'"));
	$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and project_type='74'")); 	

	$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1' and project_type='74'"));

	$member_repaid_loans_tsp = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));

		if($member_before_loan == 0 ){
		 $member_repaid_loans=0;
		}
//	$member_before_loan=$member_before_loan-$member_rej_cnt_sht-$member_repaid_loans-$member_repaid_loans_tsp; 
	$member_before_loan=$member_before_loan+$member_before_loan_live-$member_rej_cnt_sht;// should not allow one more IHHL loan to member who availed ihhl loan already.
	 
	if($member_before_loan < 0){
		$member_before_loan=0;
	}
####### END
	}
if($type=='smartphone')
	{
	$member_before_loan=0;
	$rslt=mssql_query("select MEMBER_ID,AGE from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$value' ");
	$status_valied=mssql_num_rows($rslt);
	$member_id_rs=mssql_fetch_array($rslt);
 	$member_id=$member_id_rs['MEMBER_ID'];
	$member_age=$member_id_rs['AGE'];

//Member Age validation start 08-05-2017
	if($status_valied<1)
	{
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	}
  if(($member_age =="" || $member_age=="NULL")){
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_not_update";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	exit;
  }
 elseif($member_age >= 63 || $member_age <= 18)
 {
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_btw_1860";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
	mssql_close($link);
	$agi->Hangup();
	exit;
 }
//Member Age validation end 08-05-2017
####### STRAT

	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id'  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type='72'"));

	$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and project_type='72'")); 	

	$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1' and project_type='72'"));

	$member_repaid_loans_tsp = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));

		if($member_before_loan == 0 ){
		 $member_repaid_loans=0;
		}
	$member_before_loan=$member_before_loan-$member_rej_cnt_sht-$member_repaid_loans-$member_repaid_loans_tsp;
	 
	if($member_before_loan < 0){
		$member_before_loan=0;
	}
####### END
	}
	
if( (($type=="mobile")&& ($upper_limit<='0') && strlen($value) == "10") || (($type=="ca_id")&& ($value>='1' && strlen($value)=='6' && $status_valied>='1' && $prev_ca_count=='0')) || (($type=="BASE")&& ($value>='1' && strlen($value)=='3' && $status_valied>='1' && $member_before_loan=='0')) ||(($type=="member_id" || $type=="MART" ||$type=="KNOWLEDGE"|| $type=="PRODUCER" || $type=="NEW_ENTERPRISE" || $type == "EXISTING_ENTERPRISE" || $type=="sthreenidhi"||$type=="short_term" || $type=="project" || $type=="project_others" || $type=="SCP_MEMBER" || $type=="BASE" || $type=="project_vle" || $type=="CMSA_member" || $type=="IWMP_MEMBER" || $type == "corpus_loan" || $type=="IHHL" || $type=='bicycle' || $type=='smartphone')&& ($value>='1' && strlen($value)=='2' && $status_valied>='1' && $member_before_loan=='0')) )
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_digit($value,$agi);

//Member Validation in IB Database START 14-07-17

if($type=="sthreenidhi" || $type == "corpus_loan" || $type=="IHHL" || $type=='bicycle' || $type=='smartphone')
	{
        $rslt=mssql_query("select member_is_active from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$value'");
        $member_id_rs=mssql_fetch_array($rslt);
        $member_is_active=$member_id_rs['member_is_active'];
        if($member_is_active!='Y')
        {
        $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/MemberNotInIB";
        $agi-> stream_file($wfile, '#');
	$value = $this-> get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	if($value !== null)
	return $value;
        }
	}
//Member Validation in IB Database END

	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
	 $value=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	 return $value;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
   return $value;
       }
	   else{$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
	   
	} 
	
	}
	else
		{
		  if($x>='1')
		{
			
			//if($type=="member_id")
		//	{
		//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_id_wrong";
		//$agi-> stream_file($wfile, '#');
		//	}
			if(($type=="member_id"||$type=="sthreenidhi") && $status_valied=='0')
			{
				if($db_filed=='Y')
				{
				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_pop";
				$agi-> stream_file($wfile, '#');	
				}
				if($db_filed=='N')
				{
				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_non_pop";
				$agi-> stream_file($wfile, '#');
				}
				else
				{	
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_id_wrong";
		$agi-> stream_file($wfile, '#');
				}
		//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_id_wrong";
		//$agi-> stream_file($wfile, '#');
			}
			
		if(($type=="BASE") && $status_valied=='0' && $play_msg == "three_digit_family_baseline_id")
			{
					
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/three_digit_family_baseline_id_wrong";
		$agi-> stream_file($wfile, '#');
		//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_id_wrong";
		//$agi-> stream_file($wfile, '#');
			}	
			
		if($type == "SCP_MEMBER" && $status_valied=='0'){
			if($db_filed == "26"){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_scsp_not_eligible";	
				}elseif($db_filed == "27"){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_tsp_not_eligible";		
				}			
			$agi-> stream_file($wfile, '#');	
			}
			
		if($type == "IWMP_member" && $status_valied=='0'){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_IWMP_not_eligible";	
			$agi-> stream_file($wfile, '#');	
			}	
			
		if($type == "CMSA_member" && $status_valied=='0'){
			if($db_filed == "36"){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_CMSA_not_eligible";	
				}elseif($db_filed == "37"){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_CMSA_not_eligible";		
				}			
			$agi-> stream_file($wfile, '#');	
			}
			
			
		if($type == "project_vle" && $status_valied=='0'){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_vle_not_eligible";	
			$agi-> stream_file($wfile, '#');	
			}		
			
		if(($type=="member_id_mepma" || $type=="member_id" || $type=="ca_id" || $type=="MART" || $type=="PRODUCER" || $type=="NEW_ENTERPRISE"  || $type == "EXISTING_ENTERPRISE" || $type=="sthreenidhi"||$type=="IHHL"||$type=='bicycle'||$type=="smartphone"||$type=="KNOWLEDGE" || $type == "BASE" || $type=="SCP_MEMBER" || $type=="project_vle"|| $type=="project_others" || $type=="CMSA_member" || $type=="IWMP_MEMBER" || $type == "corpus_loan") && ($member_before_loan >='1' || $prev_ca_count>='1'))
			{
			
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
			}
			else
			{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		if(($type == "NEW_ENTERPRISE"  || $type == "EXISTING_ENTERPRISE" || $type=="IWMP_MEMBER") && $status_valied=='0'){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_eligible_for_loan";	
			}		
		$agi-> stream_file($wfile, '#');
			}
		 $x=$x-1;
	$shg_log_id=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	return $shg_log_id;
		   }else
		   {$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
		}
	
	}


function hlp_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg)
{
		$test_vonumber=$GLOBALS['test_vonumber'];
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, 3);
	$value=$res_dtmf ["result"];

if($value == '-1' || $value == ' ' || $value == '' ||$value == '0' || intval($value)>126 || strlen($value)<3) 
{
if($x>1)
  {
   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->hlp_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
   return $value;
   }
   else
   {
		mssql_close($link);
    $agi->Hangup();
	exit;
   }
}
else
{
	$hlp_details_rs=mssql_fetch_array(mssql_query("SELECT *  FROM  HLP_ACTIVITIES(nolock) where ACTIVITY_CODE='$value'"));
	$Activity_Code=$hlp_details_rs['ACTIVITY_CODE'];
    $Activity_Name=$hlp_details_rs['ENGLISH'];
    $SN_GROUPING=$hlp_details_rs['SN_GROUPING'];
	
	  $Activity_Name=str_replace('-','',$Activity_Name);
	  $Activity_Name=str_replace('/','',$Activity_Name);
	  $Activity_Name=str_replace(' ','_',$Activity_Name);
      $Activity_Name=str_replace('.','_',$Activity_Name);    
   	  $Activity_Name=str_replace(' ','_',$Activity_Name); 
   	  $Activity_Name=str_replace('__','_',$Activity_Name);
	  if($caller=='9494464446')	
	  {
	 $Activity_Name='Bag_making';
	  }
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_for";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/hlp/$Activity_Name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/applying";
		$agi-> stream_file($wfile, '#');
	
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
     $value=$this->hlp_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
	 return $value;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->hlp_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
   return $value;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		$agi->Hangup();
	         }
	   
	}
	}
	


}


function krushe_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg)
{
		$test_vonumber=$GLOBALS['test_vonumber'];
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, 2);
	$value=$res_dtmf ["result"];

if($value == '-1' || $value == ' ' || $value == '' ||$value == '0' || intval($value)>13 || strlen($value)<1) 
{
if($x>1)
  {
   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->krushe_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
   return $value;
   }
   else
   {
		mssql_close($link);
    $agi->Hangup();
	exit;
   }
}
else
{
	$hlp_details_rs=mssql_fetch_array(mssql_query("SELECT *  FROM  KRUSHE_ACTIVITY_MASTER(nolock)  where ACTIVITY_ID='$value'"));
	$Activity_Code=$hlp_details_rs['ACTIVITY_ID'];
    $Activity_Name=$hlp_details_rs['ACTIVITY_NAME'];
    //$SN_GROUPING=$hlp_details_rs['SN_GROUPING'];
	
	  $Activity_Name=str_replace('-','',$Activity_Name);
	  $Activity_Name=str_replace('/','',$Activity_Name);
	  $Activity_Name=str_replace('(','',$Activity_Name);
	  $Activity_Name=str_replace(')','',$Activity_Name);
	  $Activity_Name=str_replace(' ','_',$Activity_Name);
      $Activity_Name=str_replace('.','_',$Activity_Name);    
   	  $Activity_Name=str_replace(' ','_',$Activity_Name); 
   	  $Activity_Name=str_replace('__','_',$Activity_Name);
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_for";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/krushe/$Activity_Name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/applying";
		$agi-> stream_file($wfile, '#');
	
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
     $value=$this->krushe_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
	 return $value;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->krushe_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
   return $value;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		$agi->Hangup();
	         }
	   
	}
	}
	


}	

	
	function shg_name_disbursement($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit)
	{
	    $test_vonumber=$GLOBALS['test_vonumber'];
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 3000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  /*
	  $vo_shg_count = mssql_num_rows(mssql_query("select * from SHG_INFO  where VO_ID='$void_code'"));
	  if($vo_shg_count>99)
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='00'.$shg_code_rs;
	  }
	  if(strlen($shg_code_rs)=='2')
	  {
      $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
	  $shg_code_short=$shg_code_rs;
	  }
	  
	  }
	  else
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
      $shg_code_short=$shg_code_rs;
	  }
	 }
	 */
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);
	 if($status>='1')
	 {
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		//play vo_sanghamsangham
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    /*$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/group_grading";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/$shg_user_grade";
		$agi-> stream_file($wfile, '#');*/
		
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  
	  else
	  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
		
	    }
		if($status=="1")
		{
		
	  $shg_mem_rs=mssql_query("select * from IVRS_LOAN_REQUEST(nolock) where   SHG_ID='$shg_code' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'");
	  $mem_pending=mssql_num_rows($shg_mem_rs);	
	  $shg_disb_date_rs=mssql_fetch_array($shg_mem_rs);
	  $shg_disb_date=$shg_disb_date_rs['LOAN_DISBURSEMENT_DATE'];
	  
	  if($mem_pending>=1)
	  {
	  $x='3';
	   $this->shg_disbursement_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat);
	   
	       }
		   else
	      {
		  if($x>='1')
		{
		//play prompt here as no loan is registered againest shg
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		 $agi->Hangup();
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
		   
		   
		}
		else
		{
		$this-> vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		 $agi->Hangup();
		}
		
		
	}
	else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
	
	
	}
	
	

function shg_disbursement_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat)
{
		$test_vonumber=$GLOBALS['test_vonumber'];
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_date_example";
$agi-> stream_file($wfile, '#');

		$x='3';
$loan_pay_date=$this->get_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
if($loan_pay_date>=1)
  {
$play_msg='shg_loan_month';
$loan_pay_month=$this->get_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$play_msg);

 $month_now=date('m');
	if($loan_pay_month<=$month_now)
	{
	$year_now=date('Y');
	}
	else
	{
	$year_now=date('Y')-1;
	}
	
if(checkdate($loan_pay_month,$loan_pay_date,$year_now))
{
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_loan_complete_date";
$agi-> stream_file($wfile, '#');
  $this->play_amount($loan_pay_date,$agi);
   $wfile="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/$loan_pay_month";
  $agi-> stream_file($wfile, '#');
  $this->play_amount($year_now,$agi);
  
  $get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
	 $value=$this->shg_disbursement_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat);
	 $agi->Hangup();
	// return $value;
	}
	if($get_confirmation==1)
	{
	//updatedatabase
	
	$disb_date=$year_now."-".$loan_pay_month."-".$loan_pay_date;
	
	if($caller!=$test_vonumber)
	{
	mssql_query("update IVRS_LOAN_REQUEST set LOAN_DISBURSEMENT_DATE='$disb_date' where SHG_ID='$shg_code' and VO_ID='$void_code'");
	mssql_query("update IVRS_LOAN_REQUEST_LIVE set LOAN_DISBURSEMENT_DATE='$disb_date' where SHG_ID='$shg_code' and VO_ID='$void_code'");
	}

	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_date_success";
	$agi-> stream_file($wfile, '#');
	
	$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
	$agi->Hangup();
	//return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->shg_disbursement_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat);
   return $value;
       } 
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
	         }
 
  }
  }
  else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->shg_disbursement_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat);
   return $value;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
	         }
	   
	}


 }
 }
 
 
 function get_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$play_msg)
	{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, 2);
	$value=$res_dtmf ["result"];
	if($value>='0' && $value<='12')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/$value";
	$agi-> stream_file($wfile, '#');
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
	 $value=$this->get_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$play_msg);
	 return $value;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->get_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$play_msg);
   return $value;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
	         }
	   
	} 
	
	}
	else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
	$shg_log_id=$this->get_month($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$play_msg);
	return $shg_log_id;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		}
	
	
	}

function get_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit)
	{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_loan_date", 5000, 2);
	$value=$res_dtmf ["result"];
	
	if($value>='0'&& $value<='31')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
	 $value=$this->get_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
	 return $value;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->get_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
   return $value;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
	         }
	   
	} 
	
	}
	else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
	$shg_log_id=$this->get_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
	return $shg_log_id;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		}
	}	
		



	
	
	
function get_amount($agi,$x,$language,$db_filed,$type,$length,$play_msg,$loan_pay_limit)
	{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	
	if($value>'100' && $value < $loan_pay_limit)
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	$agi-> stream_file($wfile, '#');
	
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
	 $value=$this->get_amount($agi,$x,$language,$db_filed,$type,$length,$play_msg,$loan_pay_limit);
	 return $value;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->get_amount($agi,$x,$language,$db_filed,$type,$length,$play_msg,$loan_pay_limit);
   return $value;
       }
	   else{$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
	   
	} 
	
	}
	else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
	$shg_log_id=$this->get_amount($agi,$x,$language,$db_filed,$type,$length,$play_msg,$loan_pay_limit);
	return $shg_log_id;
		   }else
		   {$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
		}
	}
		
	
function change_password($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit)
   {
       		$test_vonumber=$GLOBALS['test_vonumber'];
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/new_pin", 9000, 5);
	    $pin_1=$res_dtmf ["result"];
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/new_pin_again", 9000, 5);
	    $pin_2=$res_dtmf ["result"];
		if(($pin_1==$pin_2) && ($pin_1!='' && $pin_2!='') && ($pin_1!='-1' && $pin_2!='-1'))
		{
		//echo $pin_1.$pin_2;
		//echo "update IVRS_VO_CREDIT_LIMIT set PIN='$pin_1' where vo_id='$void_code'";
		if($caller!=$test_vonumber)
		{
		mssql_query("update IVRS_VO_CREDIT_LIMIT set PIN='$pin_1' where vo_id='$void_code'");
		}
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/pin_update_successful";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/remember_5_pin";
		$agi-> stream_file($wfile, '#');
		$this->vo_request($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$pin_1);
		}
		else
		{
		
		if($x>=1)
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/two_incorrect_pin";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->change_password($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		  }
		  else
		  {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		  
		  }
		}
		
		//mysql check here
    }
		
	

	function request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) 	
{
		$test_vonumber=$GLOBALS['test_vonumber'];
	$project=1;
     		$db_filed="category";
		$type='mem_mode';
		$length='5';
		$play_msg="mem_category";
		$x='3';
 $member_type=$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
	
	
	$length='2';
	$play_msg='two_digit_member_id';
	$type='sthreenidhi';
	$x='3';
	$db_filed=$member_type;
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
 

//remove activity validation for member on 13092014 
// $SP_ACTIVITY=$member_id_rs['SP_ACTIVITY'];	
// if($SP_ACTIVITY == "26" || $SP_ACTIVITY == "27" || $SP_ACTIVITY == "36" || $SP_ACTIVITY == "37" ){
//    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/eligible_for_other_sthreenidhi";
//	$agi-> stream_file($wfile, '#');
//	$this-> request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
//		mssql_close($link);
//	$agi->Hangup();
//	exit;	
//	
// }
//remove activity validation for member on 13092014 
 
	//$vo_id_mandal=substr($void_code,0,6);
	
	//$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id'"));
	$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and ( MEMBER_ID='$member_id'  or SHORT_CODE='$member_short_code') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
	
	$member_prev_loan_count_live = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id'"));

$member_rej_cnt_lng = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11'"));

$member_repaid_loans = mssql_num_rows(mssql_query("select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED='1'"));


/*if($member_repaid_loans == 1){
		 $member_installments_rs=mssql_fetch_array(mssql_query("select INST_NO from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code'"));
 $member_installments=$member_installments_rs['INST_NO'];
 if($member_installments < "20"){
 	$member_repaid_loans=0;
 }
 
	}*/
		if($member_prev_loan_count == 0 ){
		 $member_repaid_loans=0;
		}


	$member_corpus_loans_applied=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and ( MEMBER_ID='$member_id'  or SHORT_CODE='$member_short_code') and project_type='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
	
	$member_corpus_loans_applied_live=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and ( MEMBER_ID='$member_id'  or SHORT_CODE='$member_short_code') and IVRS_ID='$ivr_call_id' and project_type='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
	
	$member_corpus_loans_rejected=mssql_num_rows(mssql_query("select VO_ID from shg_loan_application(nolock) where SHG_ID='$shg_code' and ( MEMBER_ID='$member_id'  or SHORT_CODE='$member_short_code') and project_type='53' and STATUS_ID='11' "));
	
	//$member_corpus_loans_repaid=mssql_num_rows(mssql_query("select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED='1' "));
	$member_corpus_loans_repaid=0;
	
	
	$member_corpus_loans=$member_corpus_loans_applied+$member_corpus_loans_applied_live-$member_corpus_loans_rejected-$member_corpus_loans_repaid;
	
	
	
	
	if($member_corpus_loans == 1){
		$member_outstanding_rs=mssql_fetch_array(mssql_query("select OUTSTANDING from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code'"));
 $member_outstanding=$member_outstanding_rs['OUTSTANDING'];
 if($member_outstanding < 10000 ){
 	$member_corpus_loans=0;
 }
	}
		


	$member_prev_loan_count=$member_prev_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_corpus_loans;
	
	$message="Member loans ".$member_prev_loan_count." : ".$member_rej_cnt_lng." : ".$member_prev_loan_count_live." : ".$member_repaid_loans.":".$member_corpus_loans;
	$this->log_ivr($ivr_call_id,$message);	
	
	if($member_prev_loan_count < 0 ){
	$member_prev_loan_count=0;
	}
	
	
	/*if($member_prev_loan_count>=1 || $member_repaid_loans>=1)*/
	if($member_prev_loan_count>=1)
	{
	
	$message="Member loan_already_applied : member_prev_loan_count:".$member_prev_loan_count." member_repaid_loans : ".$member_repaid_loans;
	$this->log_ivr($ivr_call_id,$message);	
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
		mssql_close($link);
	$agi->Hangup();
	exit;	
	}	

	if(strlen($member_mobile_num) == 10){
		
	}else{
    $length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
	if($x>='1')
		{
	 $x=$x-1;
	 $this->request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
		mssql_close($link);
	 $agi->Hangup();
	exit;
	 }else{
		mssql_close($link);
	$agi->Hangup();
	exit;		
	}
	
	}
	}
	
    if($amt_stat=='Y')
	{
		$db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
	 }
	 
	 if($loan_amount>='1')
	 {
		 if($health=='NO')
		 {
		$db_filed="reason";
		$type='why_loan';
		$length='5';
		$play_msg="member_loan_reason_new";
		$x='3';
  $reason_loan =$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		 }
		 else
		 {
			$reason_loan='health';
		 }
  //echo "came hetereerr";
 
  if($reason_loan=='Marriage')
   {
       //$db_filed="category";
		//$type='mem_mode';
		$length='5';
		$play_msg="get_marriage_amount";
		//$x='3';
  $marriage_amt=$this->get_marriage_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
  
  if($loan_amount<=$marriage_amt)
  {
  
  }
  else
  {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loanamt_greater_marriage_amt";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$amt_stat='Y';
	$this->request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		
  
  
  }
  
   }
 
 if(($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='health'||$reason_loan=='Education'||$reason_loan=='Marriage'||$reason_loan=='Weavers')&&($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && $member_prev_loan_count=='0' && strlen($member_id)>1))
 {
 $duration='12';
 $etime=date('Y-m-d H:i:s');
 
 if(($reason_loan=='Agriculture')&&$loan_amount>20000)
{
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_less_20000";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
}
 
 if(($reason_loan=='health')&&$loan_amount>15000)
{
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_less_25000";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
}

 /*
 if(($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Emergency Needs/Health')&&$loan_amount>15000)
{
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_less_25000";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
}
*/
if($reason_loan=='Education'||$reason_loan=='Marriage'||$reason_loan=='health')
{
$duration='24';
}

 //echo "insert into IVRS_LOAN_REQUEST(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,MMS_LOAN_ACC_NO,VO_LOAN_ACCNO,SHG_LOAN_ACC_NO,LOAN_SANCTIONED_DATE,LOAN_SANCTIONED_AMOUNT,LOAN_STATUS) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan', '$member_type','$unique_id','0','0','0','','0','open')";
 
 
 //echo "insert into IVRS_LOAN_REQUEST(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id')";
 

 $vo_actual_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
 $vo_actual_credit=$vo_actual_credit_rs['ACTUAL_CREDIT_LIMIT'];
 $vo_credit_pop=$vo_actual_credit_rs['POP_CREDIT_LIMIT'];
 $vo_credit_non_pop=$vo_actual_credit_rs['NONPOP_CREDIT_LIMIT']; 
 
 
// //commented for pop limits added in credit limit table
// $vo_actual_credit_rs=mssql_fetch_array(mssql_query("select ACTUAL_CREDIT_LIMIT from VO_CREDIT_LIMIT where VO_ID='$void_code'"));
// $vo_actual_credit=$vo_actual_credit_rs['ACTUAL_CREDIT_LIMIT'];
// $vo_actual_credit_cat= $vo_actual_credit/2;			   
//	$member_count_pop=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO  where   VO_ID='$void_code' and IS_POP_MEM='Y'"));
//	$member_count_non_pop=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO  where   VO_ID='$void_code' and IS_POP_MEM='N'"));	
//	
//// 
//// if($member_count_pop<$member_count_non_pop)
////{
////$pop_propesed_lt=$member_count_pop*25000;
////	if($pop_propesed_lt<$vo_actual_credit_cat)
////	{
////	$vo_credit_pop=$pop_propesed_lt;
////	$vo_credit_non_pop=$vo_actual_credit-$pop_propesed_lt;
////	}
////	else
////	{
////	$vo_credit_pop=intval(ceil($vo_actual_credit/2));
////	$vo_credit_non_pop=intval(floor($vo_actual_credit/2));	
////		
////	}
////
////}else if($member_count_non_pop < $member_count_pop)
////{
////	$non_pop_propesed_lt=$member_count_non_pop*25000;
////	if($non_pop_propesed_lt<$vo_actual_credit_cat)
////	{
////	$vo_credit_non_pop=$non_pop_propesed_lt;
////	$vo_credit_pop=$vo_actual_credit-$non_pop_propesed_lt;
////	}
////	else
////	{
////	$vo_credit_pop=intval(ceil($vo_actual_credit/2));
////	$vo_credit_non_pop=intval(floor($vo_actual_credit/2));	
////		
////	}
////
////	
////}else
////{
////$vo_credit_pop=intval(ceil($vo_actual_credit/2));
////$vo_credit_non_pop=intval(floor($vo_actual_credit/2));		
////}
//	
//	$members_in_vo=$member_count_non_pop+$member_count_pop;
//	$vo_credit_pop=intval(ceil($vo_actual_credit/2));
//	$vo_credit_non_pop=intval(floor($vo_actual_credit/2));	
//	
//	//condition 1 
//	//pop-0 mem,nonpop- 100% mem then pop cr= 0 ,nonpop cr = 1005
//	
//	if($member_count_pop == "0"){
//		$vo_credit_pop=0;
//		$vo_credit_non_pop=$vo_actual_credit;
//	}
//	
//	//pop-100% mem,nonpop- 0 mem then pop cr= 100% ,nonpop cr = 0
//	if($member_count_non_pop == "0"){
//		$vo_credit_non_pop=0;
//		$vo_credit_pop=$vo_actual_credit;
//	}
//	
//	//condition 1
//	
//	
//	//condition 2 
//	//pop-50% mem,nonpop- 50% mem then pop cr= 50% ,nonpop cr = 50%
//	
//	if($member_count_pop == $member_count_non_pop){
//		$vo_credit_pop=$vo_actual_credit_cat;
//		$vo_credit_non_pop=$vo_actual_credit_cat;
//	}
//	
//	
//	
//	//condiiton 2
//	
//	//CONDIITON 3
//	
//	
//	$member_pop_percentage=($members_in_vo/$member_count_pop)*100;
//	$member_pop_percentage=sprintf("%01.2f", $member_pop_percentage);
//	
//	if($member_pop_percentage >= "75"){
//		$vo_credit_pop=$vo_actual_credit_cat;
//		$vo_credit_non_pop=$vo_actual_credit_cat;
//	}
//	
//	
//	//condition 3
//	
//	
//	//condition 4
//	
//	if($member_pop_percentage >= "1" && $member_pop_percentage <= "75"){
//			
//	$vo_credit_SCSP_TSP_rs=mssql_fetch_array(mssql_query("select actual_credit_limit from vo_sp_credit_limit where vo_id='$void_code'"));
//    $vo_actual_credit_lt_SCSP_TSP = $vo_credit_SCSP_TSP_rs['actual_credit_limit'];	
//    
//    
//    $vo_credit_IWMP_rs=mssql_fetch_array(mssql_query("select actual_credit_limit from VO_IWMP_CREDIT_LIMIT  where vo_id='$void_code'"));
//    $vo_actual_credit_lt_IWMP = $vo_credit_IWMP_rs['actual_credit_limit'];
//    
//    $vo_credit_other_sthreenidhi=$vo_actual_credit_lt_SCSP_TSP+$vo_actual_credit_lt_IWMP;
//	
//	
//	$vo_pop_and_other=$vo_credit_pop+$vo_credit_other_sthreenidhi;
//	
//	if($vo_pop_and_other >= $vo_actual_credit_cat){
//		$vo_credit_pop=$vo_pop_and_other;
//	}else{
//		$required_for_pop=$vo_actual_credit_cat-$vo_pop_and_other;
//		$vo_credit_non_pop=$vo_credit_non_pop-$required_for_pop;
//		$vo_credit_pop=$vo_pop_and_other;
//	}
//	
//	
//	
//	}
//	
//	
//	//condiition 4
//
////commented for pop limits added in credit limit table

 
 if($member_type=='Y')
     {
	 $member_cat='pop';
	 $search_cat='0';
	 $vo_cat_actual_limit=$vo_credit_pop;
	 //$tbl_filed='current_limit_pop';
	  //$vo_credit_pop=intval(ceil($vo_actual_credit/2));
	
	 
	 	if($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Weavers')
		{
		$vo_fixed_credit=intval(ceil($vo_credit_pop*0.85));
		$tbl_filed='current_limit_pop_ig';
		$search_purpose="'Agriculture','Dairy','Income generation Activity','Weavers'";
		$search_cr_limit='credit_limit_pop_ig';
		$credit_lt_type="current_limit_pop_ig";
		}
		if($reason_loan=='Education'||$reason_loan=='Marriage'||$reason_loan=='health')
        {
	  $vo_fixed_credit=intval(floor($vo_credit_pop*0.15));
	  $tbl_filed='current_limit_pop_non_ig';
	  $search_cr_limit='credit_limit_pop_non_ig';
	  $search_purpose="'Education','Marriage','health','Emergency Needs'";
	  $credit_lt_type="current_limit_pop_non_ig";
        }
		
	    }
		if($member_type=='N')
		{
			
		//$vo_credit_non_pop=intval(floor($vo_actual_credit/2));	
		$member_cat='non-pop';
		$search_cat='1';
		$vo_cat_actual_limit=$vo_credit_non_pop;
		 //$tbl_filed='current_limit_nonpop';
		 
		 
		  if($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Weavers')
		{
		$vo_fixed_credit=intval(ceil($vo_credit_non_pop*0.85));	
		$tbl_filed='current_limit_nonpop_ig';
		$search_cr_limit='credit_limit_nonpop_ig';
		$search_purpose="'Agriculture','Dairy','Income generation Activity','Weavers'";
		$credit_lt_type="current_limit_nonpop_ig";
		}
		
		if($reason_loan=='Education'||$reason_loan=='Marriage'||$reason_loan=='health')
        {
		$vo_fixed_credit=intval(floor($vo_credit_non_pop*0.15));
	    $tbl_filed='current_limit_nonpop_non_ig';
		$search_cr_limit='credit_limit_nonpop_non_ig';
		$search_purpose="'Education','Marriage','health','Emergency Needs'";
		$credit_lt_type="current_limit_nonpop_non_ig";
        }
		
		 }
	
	//commented for automation	 
		 /*
        $vo_credit_rs=mssql_fetch_array(mssql_query("select $tbl_filed from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        $vo_credit_lt = $vo_credit_rs[$tbl_filed];
		*/
		
	$applied_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE='1' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' ")); 
	//removed and PURPOSE in ($search_purpose)
	
        $applied_amt = $applied_rs['AMT'];
		
		$applied_rs_live=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='1'"));
        $applied_amt_live = $applied_rs_live['AMT_LIVE'];

		$applied_amt_live=intval($applied_amt_live);

	//commented for automation		
		//$vo_rej_amt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) as AMT_REJ from IVRS_VO_REINITIATED_CREDITS where VO_ID='$void_code' and IS_POP='$member_type' and PURPOSE in ($search_purpose) "));
		
		$vo_rej_amt_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE='1'  and STATUS_ID='11'"));		
$vo_rej_amt=$vo_rej_amt_rs['AMT_REJ'];
$vo_rej_amt=intval($vo_rej_amt);



//commented for automation
/*
   $vo_fixed_credit_rs=mssql_fetch_array(mssql_query("select $search_cr_limit from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
       $vo_fixed_credit = $vo_fixed_credit_rs[$search_cr_limit];
	   */
	   
	   $vo_repaid_cat_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='$search_cat' and PROJECT_TYPE='1'"));
        $repaid_cat_total = $vo_repaid_cat_rs['AMT_REPAID'];
		//added for automation
		$applied_total=$applied_amt+$applied_amt_live-$vo_rej_amt-$repaid_cat_total;
		
	if($applied_total < 0){
			$applied_total=0;
			
	$message="VO Repaid AMT is greater than applied amt,resetting the outstanding to 0, applied_total :$applied_total";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					
		}
		
		
		$vo_cat_limit=$vo_cat_actual_limit-$applied_total;
			if($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Weavers')
		{
			// $repaid_cat_total=intval(ceil($repaid_cat_total*0.85));
			 //$repaid_cat_total=intval($repaid_cat_total);
			 $vo_credit_lt=intval(ceil($vo_cat_limit*0.85));
		}
		if($reason_loan=='Education'||$reason_loan=='Marriage'||$reason_loan=='health')
		{
			 //$repaid_cat_total=intval(floor($repaid_cat_total*0.15));
			 //$repaid_cat_total=intval($repaid_cat_total);
			  $vo_credit_lt=intval(ceil($vo_cat_limit*0.15));
		}
		
	//$vo_credit_lt=$vo_fixed_credit-$applied_total+$repaid_cat_total;	
	
		
		//commented for automataion
		/*
		if($applied_total>$vo_fixed_credit && $loan_amount>$vo_credit_lt)
		{
			$extra_applied_amt=$applied_total-$vo_fixed_credit;
			$vo_credit_lt=$repaid_cat_total-$extra_applied_amt;
		}
		if($loan_amount<$vo_credit_lt)
		{
			
			$vo_credit_lt=$vo_credit_lt;
		}
		else
		{
			$vo_credit_lt=$repaid_cat_total+$vo_credit_lt;
		}
		*/	
		
//	$shg_lt_max_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE='1'"));
//		$shg_lt_max=$shg_lt_max_rs[0];
//		
//	//commented for automation	
//			//$shg_rejected_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_VO_REINITIATED_CREDITS where VO_ID='$void_code' and SHG_ID='$shg_code'"));
//$shg_rejected_live_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where  SHG_ID='$shg_code'  and PROJECT_TYPE='1' and STATUS_ID='11'"));

//			
//$shg_live_rejected_amt=$shg_rejected_live_rs[0];
//
//$shg_repaid_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where SHG_ID='$shg_code' and IS_CLOSED='1'"));
//$shg_repaid=$shg_repaid_rs[0];
//
//$applied_actual=$shg_lt_max-$shg_live_rejected_amt-$shg_repaid;
//if($applied_actual>150000)
//{
//	$amt_live_to_add=$applied_actual-150000;
//	$amt_live_to_deduct=$shg_live_rejected_amt-$amt_live_to_add;
//}
//else
//{
//	$amt_live_to_deduct=$shg_live_rejected_amt+$shg_repaid;
//}
//	
//		$shg_lt_max=$shg_lt_max-$amt_live_to_deduct;
//		
//		if($shg_lt_max < 0){
//			$shg_lt_max=0;
//		}
//		
//		
//		
//		$shg_lt_max=$shg_lt_max+$loan_amount;
//		
//		//shg_amount
//		$shg_lt_max_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE='1'"));
//		$shg_lt_max_tcs=$shg_lt_max_tcs_rs[0];
//		
//		$shg_lt_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code' and SHG_ID='$shg_code' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='1'"));
//		$shg_lt_live=$shg_lt_live_rs[0];
//		
//	//commented for automation	
////$shg_rejected_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_VO_REINITIATED_CREDITS where VO_ID='$void_code' and SHG_ID='$shg_code' "));
////$shg_rejected_amt=$shg_rejected_rs[0];
//
//$shg_rejected_amt=$shg_live_rejected_amt;
//$applied_actual_tcs=$shg_lt_max_tcs+$shg_lt_live-$shg_rejected_amt-$shg_repaid;
//
//if($applied_actual_tcs>150000)
//{
//	$amt_to_add=$applied_actual_tcs-150000;
//	$amt_to_deduct=$shg_rejected_amt-$amt_to_add;
//}
//else
//{
//	$amt_to_deduct=$shg_rejected_amt+$shg_repaid;
//}
//	
//		
//		$shg_limit_max_tcs=$shg_lt_max_tcs+$shg_lt_live-$amt_to_deduct;
//		
//		if($shg_limit_max_tcs < 0){
//			$shg_limit_max_tcs=0;
//		}
//		
//		$shg_limit_max_tcs=$shg_limit_max_tcs+$loan_amount;

		$message="shg_outstanding_amt in project $project at 3rd place";
		$this->log_ivr($ivr_call_id,$message);
		
$tcs_shg_outstanding_amt=$this->shg_outstanding_amt($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code);

$tcs_shg_drawing_power=150000-$tcs_shg_outstanding_amt;

 $message="VALIDATING SHG Drawing power tcs_shg_drawing_power: $tcs_shg_drawing_power = 150000 - tcs_shg_outstanding_amt: $tcs_shg_outstanding_amt";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

		//vo_amount
//$vo_total_credit_rs=mssql_fetch_array(mssql_query("select  credit_limit_pop+credit_limit_nonpop from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        //$vo_total_credit=$vo_total_credit_rs[0];
        $vo_total_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	 $vo_total_credit=$vo_total_credit_rs['ACTUAL_CREDIT_LIMIT'];
	 
			//commented for automation
	   //$vo_amt_to_add_rs=mssql_fetch_array(mssql_query("select Amount_ADD from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
	   
//	    $vo_amt_to_add_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE='1' and STATUS_ID='11'"));
//       $vo_amt_to=$vo_amt_to_add_rs[0];
//	   $vo_total_credit=$vo_total_credit+$vo_amt_to;
//	   
//		
//		$vo_lt_max_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST where shg_id in ($vo_shgs) and PROJECT_TYPE='1'"));
//		$vo_lt_max_tcs=$vo_lt_max_tcs_rs[0];
//		
//		$vo_lt_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where shg_id in ($vo_shgs) and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='1'"));
//		$vo_lt_live=$vo_lt_live_rs[0];
//		
//	$vo_repaid_total_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_VO from SN.SHG_MEMBER_REPAY_VIEW  where shg_id in ($vo_shgs)  and PROJECT_TYPE='1'"));
//        $vo_repaid_total = $vo_repaid_total_rs['AMT_REPAID_VO'];
//		$vo_repaid_total=intval($vo_repaid_total);
//		
//		$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_live-$vo_repaid_total;
//
//		if($vo_credit_max_tcs < 0){
//			$vo_credit_max_tcs=0;
//		}
//
//		$vo_credit_max_tcs=$vo_credit_max_tcs+$loan_amount;
//
// 	$mms_dist_code=substr($void_code,2,2);
//      $mms_mandal_code=substr($void_code,4,2);
//	  $mms_search=substr($void_code,0,6);
//	  
//	  
//	$mms_total_credit_rs=mssql_fetch_array(mssql_query("select TOTAL_FUND from MMS_CREDIT_LIMIT where DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code'"));
//    $mms_total_credit=$mms_total_credit_rs[0];
//		
//		//commented for automation 
//	   //$mms_amt_to_add_rs=mssql_fetch_array(mssql_query("select sum(Amount_ADD) from IVRS_VO_CREDIT_LIMIT  where vo_id like '$mms_search%'"));
//	   
//	    $mms_amt_to_add_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where DISTRICT_ID='$mms_dist_code'  and MANDAL_ID='$mms_mandal_code' and  PROJECT_TYPE='1' and STATUS_ID='11'"));
//       $mms_amt_to=$mms_amt_to_add_rs[0];
//	   
//	   $mms_total_credit=$mms_total_credit+$mms_amt_to;
//
//
//	$mms_applied_additional=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID like '$mms_search%' and PROJECT_TYPE='1' and IS_ADDITIONAL_AMT='Y'"));
//	$mms_additional_amt=$mms_applied_additional[0];
//	

//	
//	
//	if($mms_additional_amt>=2500000)
//	   {
//		$mms_add_applied=2500000;
//		}
//		else if($mms_additional_amt>0 && $mms_additional_amt<2500000)
//		{
//		$mms_add_applied=$mms_additional_amt;
//		}
//		else
//		{
//		$mms_add_applied=0;
//		}
//
//	   
//$extra_credit_rs=mssql_fetch_array(mssql_query("select IS_ADDITIONAL_AMT  from VO_CREDIT_LIMIT  where  VO_ID='$void_code'"));	
//$is_eligible=$extra_credit_rs['IS_ADDITIONAL_AMT'];
//
//$extra_mms_amt=mssql_fetch_array(mssql_query("select ADDITIONAL_AMT  from MMS_CREDIT_LIMIT  where  DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code'"));
//$extra_amt_mms=$extra_mms_amt['ADDITIONAL_AMT'];
//
//
//		
//		$mms_lt_max_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST where VO_ID like '$mms_search%' and PROJECT_TYPE='1'"));
//		$mms_lt_max_tcs=$mms_lt_max_tcs_rs[0];
//		
//		$mms_lt_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID like '$mms_search%' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='1'"));
//		
//		$mms_lt_live=$mms_lt_live_rs[0];
//		
//		$mms_code=substr($void_code,2,4);
//		
//		$mms_repaid_total_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_MMS from SN.SHG_MEMBER_REPAY_VIEW  where MS_CODE='$mms_code'  and PROJECT_TYPE='1'"));
//        $mms_repaid_total = $mms_repaid_total_rs['AMT_REPAID_MMS'];
//		$mms_repaid_total=intval($mms_repaid_total);
//		
//		$mms_credit_max_tcs=$mms_lt_max_tcs+$mms_lt_live-$mms_repaid_total;
//		
//		if($mms_credit_max_tcs < 0){
//			$mms_credit_max_tcs=0;
//		}
//		
//		$mms_credit_max_tcs=$mms_credit_max_tcs+$loan_amount;
//		
//if($is_eligible=='Y')
//{
//$mms_total_credit=$mms_total_credit+$extra_amt_mms;
//	}
//	else
//	{
//	$mms_credit_max_tcs=$mms_credit_max_tcs-$mms_add_applied;
//	}
//	

 
$vo_outstanding=$this->vo_outstanding($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'1');
	
$vo_drawing_power=$vo_total_credit-$vo_outstanding;


$vo_outstanding_tcs=$this->vo_outstanding_tcs($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'1');
	
$vo_drawing_power_tcs=$vo_total_credit-$vo_outstanding_tcs;
	
$vo_category_drawing_power=$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$credit_lt_type);			
			  
  //if($vo_credit_lt>=$loan_amount && $shg_lt_max<=150000 && $shg_limit_max_tcs<=150000 && $vo_credit_max_tcs<=$vo_total_credit && $mms_credit_max_tcs<=$mms_total_credit )
  
  //mms credit limit removed 
//   if($vo_credit_lt>=$loan_amount && $shg_lt_max<=150000 && $shg_limit_max_tcs<=150000 && $vo_credit_max_tcs<=$vo_total_credit )
//  { 

 $message="VALIDATING LOAN AMOUNT vo_credit_lt:$vo_category_drawing_power Greater than Equal loan_amount:$loan_amount,Loan amount $loan_amount LESS THAN EQUAL VO Drawing power $vo_drawing_power,VO drawing power from TCS Tables $vo_drawing_power_tcs Greater than Equal Loan AMT $loan_amount,loan_amount: $loan_amount LESS THAN EQUAL tcs_shg_drawing_power: $tcs_shg_drawing_power";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
			  
  //if($vo_credit_lt>=$loan_amount && $shg_lt_max<=150000 && $shg_limit_max_tcs<=150000 && $vo_credit_max_tcs<=$vo_total_credit && $mms_credit_max_tcs<=$mms_total_credit )
  //{ 
  if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount && $loan_amount <= $tcs_shg_drawing_power)
  {
	
	$message="Drawing Power Validation SERP 3";
 	$this->log_ivr($ivr_call_id,$message);  
 
 //exit;
 //$echo_cmd="/bin/echo ivr_id $ivr_call_id uni $unique_id  credit_limit $vo_credit_lt >> /tmp/ivr.txt";
 //exec($echo_cmd);
 
 
 $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select * from IVRS_LOAN_REQUEST_LIVE(nolock) where  SHG_ID='$shg_code' and PROJECT_TYPE='1' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;
	  
	$PROJECT_TYPE='1';  
	
$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code')";	

$message="Query to insert: $loanRequestInsertQry";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

if($caller!=$test_vonumber)
{
mssql_query($loanRequestInsertQry);
}

/*
  $vo_credit_rs2=mssql_fetch_array(mssql_query("select $tbl_filed from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        $vo_credit_lt_prev = $vo_credit_rs2[$tbl_filed];
		$vo_credit_lt_prev=intval($vo_credit_lt_prev);
		
		if($vo_credit_lt_prev<$loan_amount)
		{
			$used_from_repay=$loan_amount-$vo_credit_lt_prev;
			$used_from_ivr=$loan_amount-$used_from_repay;
			$loan_amount=$used_from_ivr;
			$current_limit=$vo_credit_lt_prev-$used_from_ivr;
		}else
		{
		$current_limit=$vo_credit_lt-$loan_amount;
		}
		*/
$current_limit=$vo_drawing_power-$loan_amount;
//$current_limit=$vo_credit_lt-$loan_amount;
if($caller!=$test_vonumber)
{
mssql_query("update  IVRS_VO_CREDIT_LIMIT set $tbl_filed='$current_limit'  where vo_id='$void_code'");
}

if($member_type=='Y')
     {
	$pop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_pop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$pop_lmt=$pop_lmt_rs['current_limit_pop'];
	
		 if($pop_lmt>=$loan_amount)
		 {
	 if($caller!=$test_vonumber)
	 {
	 mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_pop=current_limit_pop-$loan_amount  where vo_id='$void_code'");
	 }
		 }
	 }
	 if($member_type=='N')
		{
			
	$nonpop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_nonpop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$nonpop_lmt=$nonpop_lmt_rs['current_limit_nonpop'];
	
	if($nonpop_lmt>=$loan_amount)
	{
		if($caller!=$test_vonumber)
		{
		mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_nonpop=current_limit_nonpop-$loan_amount  where vo_id='$void_code'");
		}
	}
	   }
	   
	   
		
 //echo  $shg_log_id.$reason_loan.$member_type;
 //$unique_id
 //$ivr_call_id
 
 
		/* 
   $txt=$shg_name."(".$member_cat."):". $loan_amount."/-";
   $demo="Your Loan request for ".$txt." is successful.
Thank you . --Sthri Nidhi";
	$finalmessage=$demo;
	$sms_url="http://www.9nodes.com/API/sendsms.php?username=praveenkumar9944@gmail.com&password=praveen&from=VCode&to=".$caller."&msg=".urlencode($finalmessage)."&type=1";
$h=fopen($sms_url,"r");
fclose($h);
*/

//mysql_query("update vo_info set credit_limit=credit_limit-$shg_amount where vo_id='$void_code'")
//$vo_credit_limit=16000;
$vo_credit_limit=$current_limit;

 $member_limit=$member_limit-1; 
   if($member_limit>=1 && $vo_credit_limit>=0)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{
	$amt_stat='Y';
	$this->request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
	}
	else
	{
	  if($vo_credit_limit>=0)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{
			
	$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}
 }
 else
 {

if($mms_credit_max_tcs>$mms_total_credit)
	 {
		$mms_dist_code=substr($void_code,2,2);
        $mms_mandal_code=substr($void_code,4,2); 
		$share = mssql_fetch_array(mssql_query("SELECT  SHARE_AMOUNT FROM SN.SHARE_BAL_FUN() where DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code' "));
		$share_amount=$share['SHARE_AMOUNT'];
		if($share_amount<1000000)
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/share_captial";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($share_amount,$agi);
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/paid_share_captial";
		$agi-> stream_file($wfile, '#');	
		}
		else
		{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
		}
	 }
	 else
	 {
    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
	 }

 //$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	
	$this->request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
 
   }
	
 }
 //echo  $loan_amount.$reason_loan.$member_type;
 
		}
		else
		  {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		 $this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		$agi->Hangup();
		    }
		


}

function get_marriage_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name)
{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	//$value='101';
	if($value>='0')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	$agi-> stream_file($wfile, '#');
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 5000, 1);
	$status=$res_dtmf["result"];
	
		if($status=='1')
		{
		return $value;	
		}
		
		if($status=="2")
		{
		$marriage_amount=$this->get_marriage_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $marriage_amount;
		}
		
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$marriage_amount=$this->get_marriage_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $marriage_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		$agi->Hangup();
		   $agi->Hangup();
		   }
		}
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$marriage_amount=$this->get_marriage_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $marriage_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		  $agi->Hangup();
		   }
		}
	
	

}

function krushe_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$KRUSHE_AMOUNT)
{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	if($value>='0')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	$agi-> stream_file($wfile, '#');
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 5000, 1);
	$status=$res_dtmf["result"];
	
		if($status=='1')
		{
		$diff_loan_amt=intval(substr($value,-2,2));
		if($diff_loan_amt=='0')
		{
		 if($value<=$KRUSHE_AMOUNT && $value>=1000)
		     {
		return $value;
		           }
				   else
				   {
					if($value>$KRUSHE_AMOUNT)
					{
					$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member";
					$agi-> stream_file($wfile, '#');
					$this->play_amount($KRUSHE_AMOUNT,$agi);
					$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
					$agi-> stream_file($wfile, '#');
					$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/krushe_more_credit";
	                $agi-> stream_file($wfile, '#');
					 }
					 if($value<1000)
					 {
					 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_morethan_1000";
	                $agi-> stream_file($wfile, '#');
					 }
					$x=$x-1;
		      $loan_amount=$this->krushe_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$KRUSHE_AMOUNT);
			  return  $loan_amount;
			  
				  }
				  }
				   else
				 {
				 if($x>='1')
		 		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_multiples_100";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->krushe_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$KRUSHE_AMOUNT);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
				 }
	
			
		}
		
		if($status=="2")
		{
		$loan_amount=$this->krushe_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$KRUSHE_AMOUNT);
		return $loan_amount;
		}
		
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->krushe_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$KRUSHE_AMOUNT);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
		}
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->krushe_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$KRUSHE_AMOUNT);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		}
	
		
	
}





function SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs)
	{
	$test_vonumber=$GLOBALS['test_vonumber'];
	  //$member_limit=6;
	  
	  $scsp_ineligible=mssql_num_rows(mssql_query("SELECT * FROM SP_VO_MOBILE(nolock) where VO_ID='$void_code'"));
	  if($scsp_ineligible == 1){
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/SCSP_apply_from_mms";
   	      $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		  }
	  
	  $SCSP_array=mssql_query("select SP_ACTIVITY,IS_ACTIVE from VO_INFO(nolock)  where  TRANS_VO_ID='$void_code' ");
	  $SCSP_status=mssql_num_rows($SCSP_array);	
	  $SCSP_name_array=mssql_fetch_array($SCSP_array);
	  $SCSP_activity=$SCSP_name_array['SP_ACTIVITY'];
	  $VO_IS_ACTIVE=$SCSP_name_array['IS_ACTIVE'];	  
	  
	  if($VO_IS_ACTIVE != "Y"){
	  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		mssql_close($link);
		$agi->Hangup();
		  exit;
	  }
	  if($SCSP_status > 1){
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_scsp_tsp";
		  $agi-> stream_file($wfile, '#');	
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_SCSP_not_eligible";
		  $agi-> stream_file($wfile, '#');
		  $x=$x-1;
		  $this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		mssql_close($link);
		  $agi->Hangup();
		  exit;		  	  
		  }
	  
	  if($SCSP_activity != $SC_T_type){
		  if($SC_T_type == "26" && ($SCSP_activity == "27" || $SCSP_activity == "")){
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_SCSP_not_eligible";
		  $agi-> stream_file($wfile, '#');
		  }elseif($SC_T_type == "27" && ($SCSP_activity == "26" || $SCSP_activity == "")){
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_TSP_not_eligible";
		  $agi-> stream_file($wfile, '#');	  
		  }
		  $x=$x-1;
		  $this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		  }
	  
	 
		 
	      if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  
	  if($shg_code_rs=='00')
	  {
		 $link = mysql_connect("localhost","root", "evol@9944")
    or die("Data base connection failed");
   mysql_select_db("shtri_nidhi")
    or die("data base open failed");
	$file=$this->record_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$record_confirm,$record_propmt,$language); 
//echo "insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())";
	if($caller!=$test_vonumber)
	{
	mysql_query("insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())");
	}
	
	mysql_close($link);
		mssql_close($link);
		 $agi->Hangup();
		  exit;
		  }
		  
	  /*
	  $vo_shg_count = mssql_num_rows(mssql_query("select * from SHG_INFO  where VO_ID='$void_code'"));
	  if($vo_shg_count>99)
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='00'.$shg_code_rs;
	  }
	  if(strlen($shg_code_rs)=='2')
	  {
      $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
	  $shg_code_short=$shg_code_rs;
	  }
	  
	  }
	  else
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
      $shg_code_short=$shg_code_rs;
	  }
	 }
	 */
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}

//no bank linkage for scsp	
	//$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES where SHG_ID='$shg_code' group by SHG_ID"));
//	$overdue_amt=$overdue_rs[0];
//	$bank_name_rs= mssql_fetch_array(mssql_query("select Bank_Code from SHG_OVER_DUES where SHG_ID='$shg_code'"));
//	$bank_name=$bank_name_rs['Bank_Code'];
//
//
//	if($overdue_amt>0 && $overdue_amt<=10000)
//	{
//	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
//		$agi-> stream_file($wfile, '#');
//		$this->play_amount($overdue_amt,$agi);
//		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
//	    $agi-> stream_file($wfile, '#');
//$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
//	    $agi-> stream_file($wfile, '#');
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
//		$agi-> stream_file($wfile, '#');
//		$is_over_due='Y';	
//	}
//	 
//	
//	  if($overdue_amt>10000)
//	{	
//	if($x>='1')
//		{
//			
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
//		$agi-> stream_file($wfile, '#');
//		$this->play_amount($overdue_amt,$agi);
//		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
//	    $agi-> stream_file($wfile, '#');
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
//	    $agi-> stream_file($wfile, '#');
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
//		$agi-> stream_file($wfile, '#');
//		/*
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/proceed_through_option8";
//		$agi-> stream_file($wfile, '#');
//		*/
//		$x=$x-1;
//		$this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
//		mssql_close($link);
//		$agi->Hangup();
//		  exit;
//		   }else
//		   {
//		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
//		    $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//		   }
//	
//	}	
//no bank linkage for scsp
	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  
		  
	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  
		  }
		if($status=="1")
		{
			$status_pop_fund=2;
			/*
			$pop_stat_rs=mssql_query("select TRANS_VO_ID from vo_info where TRANS_VO_ID='$void_code' and IS_POP='Y'");
			$stat_pop=mssql_num_rows($pop_stat_rs);
			$pop_stat_shg_rs=mssql_query("select TRANS_SHG_ID from SHG_INFO where TRANS_SHG_ID='$shg_code' and IS_POP='Y'");
			$pop_stat_shg=mssql_num_rows($pop_stat_shg_rs);
		if($stat_pop>=1 && $pop_stat_shg>=1)
   		{
 		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_for_popfund", 8000, 1);
	    $status_pop_fund=$res_dtmf ["result"];
			}else

			{
				$status_pop_fund=2;
			}
			*/
			
			if($status_pop_fund=='1')
			{
				
				//$x=3;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/stop_pop_loan";
		$agi-> stream_file($wfile, '#');
		//$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
		mssql_close($link);
			$agi->Hangup();
			exit;	
			}
			elseif($status_pop_fund=='2')
			{
				//TRANS_VO_ID
	$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE,CIF_RECOVERY_PERCENTAGE from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
    $vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	/*
	if($vo_active_stat=='Y')
	{
	
	}
	else
	{
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         
	
	}
	*/
	
//No SHG account validation for SCSP_TSP Loan
	
//				
//	$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
//    $shg_active_stat=$shg_active_rs['IS_VALID'];
//	if($shg_active_stat=='Y')
//	{
//	
//	}
//	else
//	{
//	   if($x>='1')
//		{
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied_old";
//		$agi-> stream_file($wfile, '#');
//		 $x=$x-1;
//		$this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
//		mssql_close($link);
//		$agi->Hangup();
//		  exit;
//		
//		   }else
//		   {
//		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
//		    $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//		   }
//	
//	         
//	
//	}
	
//No SHG account validation for SCSP_TSP Loan

	
//$cif_recovery=$vo_active_rs['CIF_RECOVERY_PERCENTAGE'];

///no cif validation for SCSP Loan
//$cif_count_res=mssql_fetch_array(mssql_query("select count(*) from SAMSNBSTG.SN.SHG_MEMBERLOAN with (nolock) where vo_id='$void_code' and d_status=0"));
//	$cif_count=$cif_count_res[0];
//	if($cif_count==0)
//	{
//	$cif_recovery=100;	
//	}else
//	{
//$cif=mssql_fetch_array(mssql_query("select  * from SAMSNBSTG.SN.VO_RECOVERY_STATUS where VO_ID='$void_code'"));
//$cif_recovery=$cif['RECOVERY'];
//if($cif_recovery>0 && $cif_recovery<90)
//{
//
//}
//
//if($cif_recovery=='')
//	{
// $cif_recovery=100;
//	
//}	

/*
	//$TO_DATE=date('d-M-Y');
	$TO_DATE='31-Mar-2013';
	$FROM_DATE='01-Apr-2012';

$cif=mssql_fetch_array(mssql_query("SELECT  DISTRICT_ID,MANDAL_ID,VO_ID,( ( CASE WHEN UCOLL >DMD THEN DMD ELSE UCOLL END /DMD) * 100) RECOVERY FROM ( SELECT   DISTRICT_ID,MANDAL_ID,VO_ID,SUM(DMD) DMD,SUM(COLL) COLL,CASE WHEN ISNULL((SELECT SUM(PAID_AMOUNT) FROM  SN.SN_RECOVERY  WHERE STATUS=0 AND  VO_ID=T.VO_ID AND CREDITED_DATE BETWEEN '$FROM_DATE' AND '$TO_DATE' ),0) -SUM(COLL)>0 THEN ISNULL((SELECT SUM(PAID_AMOUNT) FROM  SN.SN_RECOVERY  WHERE STATUS=0 AND  VO_ID=T.VO_ID AND CREDITED_DATE BETWEEN '$FROM_DATE' AND '$TO_DATE' ),0) -SUM(COLL) ELSE 0 END  + SUM(COLL) UCOLL  FROM SN.SHG_DETAILS_YEAR_FUN  ('$FROM_DATE','$TO_DATE')  T  WHERE VO_ID ='$void_code' GROUP BY DISTRICT_ID,MANDAL_ID,VO_ID)T"));
$cif_recovery=$cif['RECOVERY'];	
*/
//	}

///no cif validation for SCSP Loan

//$cif=mssql_fetch_array(mssql_query("select  * from SAMSNBSTG.SN.VO_RECOVERY_STATUS where VO_ID='$void_code'"));
//$cif_recovery=$cif['RECOVERY'];
	
$shg_overdue_array=mssql_fetch_array(mssql_query("select OVERDUE from SAMSNBSTG.SN.shg_report_overdue(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code'"));
$shg_overdue=$shg_overdue_array['OVERDUE'];

if($shg_overdue > 0){
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue_scsp_1";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($shg_code_rs,$agi);
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue_scsp_2";
	$agi-> stream_file($wfile, '#');
			  if($x >= 1){
			$x=$x-1;  
		  $this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		  }else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		  }
		  $agi->Hangup();
}

///no cif validation for SCSP Loan

	//if($cif_recovery<90)
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cif_recovery";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}



//	$shg_grade_dist_code=substr($void_code,2,2);
//	$shg_grade_mandal_code=substr($void_code,4,2);
//	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
//	$mms_grade=$vo_grade_rs['GRADE'];
	
///no cif validation for SCSP Loan	
	/*	
	if($mms_grade=='E')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_egrade";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($mms_grade=='F')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	*/
	
	
	///no Grades validation for SCSP Loan
//    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select GRADE,ACTUAL_CREDIT_LIMIT from VO_CREDIT_LIMIT where VO_ID='$void_code'"));
//	$vo_grade=$vo_grade_rs_rej['GRADE'];
//	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
//	
//		if($vo_grade=='E')
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
//	
//	if($vo_grade=='F')
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
//	if($vo_actual_credit=='0')
//	{
//	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	
//		}
//
//	if($mms_grade=='E')
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_egrade";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
//	
//	if($mms_grade=='F' && ($vo_grade=='B'||$vo_grade=='C'||$vo_grade=='D'||$vo_grade=='F'))
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
//		
	///no Grades validation for SCSP Loan		

		$x=3;
		//current
		
	  $shg_mem_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where   SHG_ID='$shg_code' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'");
	  $total_loans_applied=mssql_num_rows($shg_mem_rs);	
	  
	  $shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code' and  IVRS_ID='$ivr_call_id'");
	  $total_loans_applied_live=mssql_num_rows($shg_mem_rs_live);
	  
	 $shg_mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and  SHG_ID='$shg_code'");
$total_loans_rejected=mssql_num_rows($shg_mem_rej_rs);

		$loans_applied=$total_loans_applied+$total_loans_applied_live-$total_loans_rejected;
		
		$total_shg_members_rs=mssql_query("select VO_ID from SHG_MEMBER_INFO(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code'");
		$total_shg_members=mssql_num_rows($total_shg_members_rs);

		$member_limit=$total_shg_members-$loans_applied;

		if($loans_applied > $total_shg_members){
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loans_applied_greater_members";
		$agi-> stream_file($wfile, '#');
		  if($x >= 1){
			$x=$x-1;  
		  $this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		  }else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		  }
		  $agi->Hangup();
		}

//other

	 // $short_term_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE where  VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE  not in ('1')");
//	  $short_term_members=mssql_num_rows($short_term_rs);

	  /*
     $shg_mem_rej_rs=mssql_query("select VO_ID from IVRS_VO_REINITIATED_CREDITS where  VO_ID='$void_code' and  SHG_ID='$shg_code'");
	  $mem_rejected=mssql_num_rows($shg_mem_rej_rs);	
	  */
	
	//total  in ('1','11','8','9','16')
	  

	  
//	 $total_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE where  VO_ID='$void_code' and  SHG_ID='$shg_code'");
//	  $total_members=mssql_num_rows($total_rs);
//	  
//	  $mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and VO_ID='$void_code' and  SHG_ID='$shg_code'");
//$mem_rejected_tot=mssql_num_rows($mem_rej_rs);
//	  
//	  if($mem_pending_live>=0 and $short_term_members=='0')
//	  {
//		  
//	  if($mem_pending>$mem_pending_live)
//	  {
//	    $member_limit=6-$mem_pending+$mem_rejected;
//	  } 
//	  else
//	   {
//		$member_limit=6-$mem_pending_live+$mem_rejected;   
//	   }
//	   
//	  }
//	   if($mem_pending_live>=0 and $short_term_members>'0')
//	  {
//		  
//		  
//	  if($mem_pending>$mem_pending_live)
//	  {
//	    $member_limit=6-$mem_pending+$mem_rejected;
//	  } 
//	  else
//	   {
//		$member_limit=6-$mem_pending_live+$mem_rejected;   
//	   }
//	   
//	   
//	   $total_remain=9-$total_members+$mem_rejected_tot;
//	   
//	   if($total_remain<=$member_limit)
//	   {
//		  $member_limit=$total_remain;
//	   }else
//	   {
//		 $member_limit=$member_limit;  
//	   }
//	   
//	   
//	  }
	  
	   
	   
	    
	//	if($member_limit>=1)
	if($loans_applied < $total_shg_members)
	
		  {
		$amt_stat='Y';
	$this->SCSP_TSP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs);
	       }
		   else
		   {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loans_applied_greater_members";
	      $agi-> stream_file($wfile, '#');
		  //$x=3;
		  
		  if($x >= 1){
			$x=$x-1;  
		  $this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		  }else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		  }
		  $agi->Hangup();
		  }	
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	     
		}
	
		}
	
		if($status=="2")
		{
		$this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
	}		




function SCSP_TSP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs)
	{	
	$test_vonumber=$GLOBALS['test_vonumber'];
	
	
	$vo_credit_SCSP_TSP_rs=mssql_fetch_array(mssql_query("select actual_credit_limit from vo_sp_credit_limit(nolock) where vo_id='$void_code' and SP_ACTIVITY='$SC_T_type'"));
    $vo_actual_credit_lt_SCSP_TSP = $vo_credit_SCSP_TSP_rs['actual_credit_limit'];
	

	
$vo_SCSP_TSP_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE='$SC_T_type' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'  "));

		$vo_SCSP_TSP_applied=$vo_SCSP_TSP_applied_rs[0];


$vo_SCSP_TSP_applied_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE='$SC_T_type'  and IVRS_ID='$ivr_call_id'"));

                $vo_SCSP_TSP_applied_live=$vo_SCSP_TSP_applied_live_rs[0];

		//$vo_krushe_applied=$vo_krushe_applied+$loan_amount;
		
//adding rejected amount
		
	
$SCSP_TSP_rej_id=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE='$SC_T_type' and shg_id in ($vo_shgs)"));
$SCSP_TSP_rej_amt_t2=$SCSP_TSP_rej_id[0];	
	
	$vo_SCSP_TSP_applied_reinitiated=$vo_SCSP_TSP_applied-$SCSP_TSP_rej_amt_t2;
	
	$vo_credit_lt_SCSP_TSP=$vo_actual_credit_lt_SCSP_TSP-$vo_SCSP_TSP_applied_reinitiated-$vo_SCSP_TSP_applied_live;
	
	
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/SCSP_TSP_credit_limit";
    $agi-> stream_file($wfile, '#');
	
	$this->play_amount($vo_credit_lt_SCSP_TSP,$agi);	
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
    $agi-> stream_file($wfile, '#');
		
	//scsp credit limit
	
	
	$length='2';
	$play_msg='two_digit_member_id';
	$type='SCP_MEMBER';
	$x='3';
	$db_filed=$SC_T_type;
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,MOBILE_NUM from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
   $member_mobile_num=$member_id_rs['MOBILE_NUM'];
	
	
	//$vo_id_mandal=substr($void_code,0,6);

	
	$member_prev_loan_count_array = mssql_fetch_array(mssql_query("select count(VO_ID) as app_loans from SHG_LOAN_APPLICATION(nolock) where SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and STATUS_ID != '11'"));
	$member_prev_loan_count=$member_prev_loan_count_array['app_loans'];
	
	$member_prev_loan_live_count_array = mssql_fetch_array(mssql_query("select count(VO_ID) as live_loans from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and IVRS_ID='$ivr_call_id' and MEMBER_ID='$member_id'"));
	$member_prev_loan_live_count=$member_prev_loan_live_count_array['live_loans'];
	
	$member_prev_loan_count=$member_prev_loan_count+$member_prev_loan_live_count;
	
	
	
	if($member_prev_loan_count>=1)
	{
	
if($x>='1')
		{
		$member_prev_loan_count_sthreenidhi = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and STATUS_ID != '11' and PROJECT_TYPE not in ('26','27')"));
		
		$member_prev_loan_live_count_sthreenidhi = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE not in ('26','27')"));
		
		if($member_prev_loan_count_sthreenidhi >= "1" || $member_prev_loan_live_count_sthreenidhi >= "1"){
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/already_sthreenidhi_loan";
		$agi-> stream_file($wfile, '#');
		}else{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
		$agi-> stream_file($wfile, '#');
		}
		 $x=$x-1;
		$this->SCSP_TSP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }

	
	$length='3';
	$play_msg='three_digit_family_baseline_id';
	$type='BASE';
	$x='3';
	$db_filed=$SC_T_type;
	$upper_limit=$member_short_code;
	$family_baseline_id=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
	
	
    	if(strlen($member_mobile_num) == 10){
		
	}else{
	$length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
		mssql_close($link);
	$agi->Hangup();	
	exit;	
	}
	
	}
	
				$db_filed="reason";
		$type='why_loan';
		$length='5';
		$play_msg="SCSP_TSP_activity_code";
		$x='3';
 $reason_loan_code=$this->SCSP_TSP_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
  $hlp_loan_rs=mssql_fetch_array(mssql_query("SELECT PURPOSE_NAME FROM  SP_PURPOSE_MASTER(nolock) where PURPOSE_ID='$reason_loan_code'"));
 $reason_loan=$hlp_loan_rs['PURPOSE_NAME'];
 
 
 	 if(($reason_loan_code >= "001" && $reason_loan_code <= "010" ) || $reason_loan_code == "127" || $reason_loan_code == "128" ){
		 $SCSP_quantity_type="count";
		 $play_msg="how_many";
		 $SCSP_quantity_limit=15;
	 }elseif($reason_loan_code == "012" || $reason_loan_code == "013" || $reason_loan_code == "025"){
		 $SCSP_quantity_type="acres";
		 $play_msg="how_many_acres";	 			 
 		 $SCSP_quantity_limit=5;
   	 }
	
	if($SCSP_quantity_type != ''){
	 $SCSP_quantity=$this->SCSP_quantity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code,$SCSP_quantity_type,$SCSP_quantity_limit);
		
		
		}
 
 
 if($reason_loan_code == "025" || $reason_loan_code == "061" || $reason_loan_code == "153"){
	 $SCSP_TSP_min_amt="1000";	 
	 $SCSP_TSP_max_amt="100000";	 
	 }else{
	$SCSP_TSP_min_amt="1000";	 	 
	$SCSP_TSP_max_amt="50000";	 
	}
	
//	025--land purchase,061-auto purchase
	
	//if($loan_request=='YES')
	//{
  $db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->SCSP_TSP_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code);
	 
	 
	 	 
	//}
//KRUSHE_AMOUNT

 //echo $member_short_code."prev --".$member_prev_loan_count."loan--".$loan_amount."krushe".$KRUSHE_AMOUNT;
 if(strlen($member_short_code)==2 && $member_prev_loan_count=='0' && $loan_amount<=$SCSP_TSP_max_amt  && $loan_amount>$SCSP_TSP_min_amt )
 {
	 
	 //reason master data
    //$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select CURRENT_CREDIT_LIMIT from IVRS_VO_POP_CREDIT_LIMIT  where vo_id='$void_code'"));
    //$vo_credit_lt_pop = $vo_credit_pop_rs['CURRENT_CREDIT_LIMIT'];
/*	
if($krushe_type=='MART')
{
$PROJECT_TYPE='$ACTIVITY';	
}
if($krushe_type=='PRODUCER')
{
$PROJECT_TYPE='$ACTIVITY';
}
*/


$PROJECT_TYPE=$SC_T_type;

	
	$vo_credit_SCSP_TSP_rs=mssql_fetch_array(mssql_query("select actual_credit_limit from vo_sp_credit_limit(nolock) where vo_id='$void_code' and SP_ACTIVITY='$SC_T_type'"));
    $vo_actual_credit_lt_SCSP_TSP = $vo_credit_SCSP_TSP_rs['actual_credit_limit'];
	

	
$vo_SCSP_TSP_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE='$SC_T_type' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' "));

		$vo_SCSP_TSP_applied=$vo_SCSP_TSP_applied_rs[0];


$vo_SCSP_TSP_applied_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE='$SC_T_type'  and IVRS_ID='$ivr_call_id' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));

                $vo_SCSP_TSP_applied_live=$vo_SCSP_TSP_applied_live_rs[0];

		//$vo_krushe_applied=$vo_krushe_applied+$loan_amount;
		
//adding rejected amount
		
	
$SCSP_TSP_rej_id=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE='$SC_T_type' and shg_id in ($vo_shgs)"));
$SCSP_TSP_rej_amt_t2=$SCSP_TSP_rej_id[0];	
	
	$vo_SCSP_TSP_applied_reinitiated=$vo_SCSP_TSP_applied-$SCSP_TSP_rej_amt_t2;
	
	$vo_credit_lt_SCSP_TSP=$vo_actual_credit_lt_SCSP_TSP-$vo_SCSP_TSP_applied_reinitiated-$vo_SCSP_TSP_applied_live;
	
	
	
	
	$vo_SCSP_credit_with_loan_amt=$vo_SCSP_TSP_applied+$vo_SCSP_TSP_applied_live+$loan_amount-$SCSP_TSP_rej_amt_t2;
	
	
	
//commented by ravi
	
	/*
$SCSP_TSP_rej_shg=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('$SC_T_type') and VO_ID='$void_code' and SHG_ID='$shg_code'"));
$rej_amt_shg_SCSP_TSP=$SCSP_TSP_rej_shg[0];	
	 
$shg_SCSP_TSP_lt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('$SC_T_type')  and  IVRS_ID='$ivr_call_id'"));

		$shg_SCSP_TSP_lt_max=$shg_SCSP_TSP_lt_rs[0];
		$shg_SCSP_TSP_lt_max=$shg_SCSP_TSP_lt_max+$loan_amount-$rej_amt_shg_SCSP_TSP;
			
$shg_SCSP_TSP_lt_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('$SC_T_type')"));

		$shg_SCSP_TSP_lt_tcs=$shg_SCSP_TSP_lt_tcs_rs[0];
		$shg_SCSP_TSP_lt_tcs=$shg_SCSP_TSP_lt_tcs+$loan_amount-$rej_amt_shg_SCSP_TSP;
		*/
 //$duration='24';
 
if($loan_amount >= 1000 && $loan_amount <= 25000){
	$duration="24";
	}elseif($loan_amount >= 25001 && $loan_amount <= 50000){
	$duration="36";	
	}elseif($loan_amount >= 50001 && $loan_amount <= 100000){
	$duration="60";	
	}

			

 $etime=date('Y-m-d H:i:s');	
// $PROJECT_TYPE='2';	  
 $member_type='N';
 //&& $vo_credit_lt_sn>=$fund_sn
 
 	  $shg_mem_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where   SHG_ID='$shg_code' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'");
	  $total_loans_applied=mssql_num_rows($shg_mem_rs);	
	  
	  $shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code'  and  IVRS_ID='$ivr_call_id'");
	  $total_loans_applied_live=mssql_num_rows($shg_mem_rs_live);
	  
	 $shg_mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and  SHG_ID='$shg_code'");
$total_loans_rejected=mssql_num_rows($shg_mem_rej_rs);

		$loans_applied=$total_loans_applied+$total_loans_applied_live-$total_loans_rejected;
		
		$total_shg_members_rs=mssql_query("select VO_ID from SHG_MEMBER_INFO(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code'");
		$total_shg_members=mssql_num_rows($total_shg_members_rs);
 
 
 $member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and STATUS_ID != '11'"));
 
 $member_prev_loan_live_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id'"));
	
	$member_prev_loan_count=$member_prev_loan_count+$member_prev_loan_live_count;
	
 
// $ivr_log_loan="IVRS ID: $ivr_call_id ,vo_id: $void_code ,shg_id: $shg_code, mem_short: $member_short_code,member_id: $member_id , loan_amount: $loan_amount, vo_actual_credit_lt_SCSP_TSP: $vo_actual_credit_lt_SCSP_TSP,vo_SCSP_TSP_applied: $vo_SCSP_TSP_applied,vo_SCSP_TSP_applied_live: $vo_SCSP_TSP_applied_live,SCSP_TSP_rej_amt_t2: $SCSP_TSP_rej_amt_t2, vo_credit_lt_SCSP_TSP: $vo_credit_lt_SCSP_TSP, vo_SCSP_credit_with_loan_amt: $vo_SCSP_credit_with_loan_amt, vo_actual_credit_lt_SCSP_TSP: $vo_actual_credit_lt_SCSP_TSP , live_query : select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID=$void_code and PROJECT_TYPE=$SC_T_type  and IVRS_ID=$ivr_call_id";

$ivr_log_loan="IVRS ID: $ivr_call_id ,vo_id: $void_code ,shg_id: $shg_code, mem_short: $member_short_code,member_id: $member_id , loan_amount: $loan_amount, vo_actual_credit_lt_SCSP_TSP: $vo_actual_credit_lt_SCSP_TSP,vo_SCSP_TSP_applied: $vo_SCSP_TSP_applied,vo_SCSP_TSP_applied_live: $vo_SCSP_TSP_applied_live,SCSP_TSP_rej_amt_t2: $SCSP_TSP_rej_amt_t2, vo_credit_lt_SCSP_TSP: $vo_credit_lt_SCSP_TSP, vo_SCSP_credit_with_loan_amt: $vo_SCSP_credit_with_loan_amt, vo_actual_credit_lt_SCSP_TSP: $vo_actual_credit_lt_SCSP_TSP ,reason_loan: $reason_loan ,reason_loan_code:  $reason_loan_code ,Quantity: $SCSP_quantity ,SCSP_TSP_min_amt :$SCSP_TSP_min_amt ,SCSP_TSP_max_amt: $SCSP_TSP_max_amt ,member_prev_loan_count: $member_prev_loan_count, loans_applied: $loans_applied, total_shg_members:$total_shg_members";

//, live_query : select SUM LOAN_AMOUNT from IVRS_LOAN_REQUEST_LIVE where VO_ID=$void_code and PROJECT_TYPE=$SC_T_type  and IVRS_ID=$ivr_call_id
if($loan_amount>=$SCSP_TSP_min_amt && $loan_amount<=$SCSP_TSP_max_amt && $vo_credit_lt_SCSP_TSP>=$loan_amount && $member_prev_loan_count==0  && $loans_applied < $total_shg_members && $vo_SCSP_credit_with_loan_amt <= $vo_actual_credit_lt_SCSP_TSP)
  {
	  $ivr_log_loan=$ivr_log_loan." ::: Success";
	}else{
	  $ivr_log_loan=$ivr_log_loan." ::: Fail";
		}



$touch_cmd="/usr/bin/touch /var/log/ivrs/".$ivr_call_id.".log";
exec($touch_cmd);

$ivr_log_loan_cmd="/bin/echo $ivr_log_loan >> /var/log/ivrs/".$ivr_call_id.".log";
exec($ivr_log_loan_cmd);
 
 
 
   if($loan_amount>=$SCSP_TSP_min_amt && $loan_amount<=$SCSP_TSP_max_amt && $vo_credit_lt_SCSP_TSP>=$loan_amount && $member_prev_loan_count==0  && $loans_applied < $total_shg_members && $vo_SCSP_credit_with_loan_amt <= $vo_actual_credit_lt_SCSP_TSP)
  {
 //$marriage_amt='0';
  $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select SHG_IVRS_LOAN_NO from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE in ('$SC_T_type')  and IVRS_ID='$ivr_call_id' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;
 // $reason_loan_code has to be added
 
//echo "insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,COMBINED_ID,HLP_CODE,IS_OVERDUE,OVERDUE_ID,SHORT_CODE,MEMBER_ID,FUND_SN,FUND_POP,baseline_id) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$ca_id_new','$reason_loan_code','$is_over_due','$due_id_lst','$member_short_code','$member_id','$fund_sn','$fund_pop','$family_baseline_id')"; 
 
if($caller!=$test_vonumber)
{
mssql_query("insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,COMBINED_ID,HLP_CODE,IS_OVERDUE,OVERDUE_ID,SHORT_CODE,MEMBER_ID,FUND_SN,FUND_POP,BASELINE_ID,QUANTITY) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$ca_id_new','$reason_loan_code','$is_over_due','$due_id_lst','$member_short_code','$member_id','$fund_sn','$fund_pop','$family_baseline_id','$SCSP_quantity')");
}

$current_limit=$vo_credit_lt_SCSP_TSP-$loan_amount;
//mssql_query("update IVRS_VO_POP_CREDIT_LIMIT set CURRENT_CREDIT_LIMIT='$current_limit'  where vo_id='$void_code'");
$vo_credit_limit=$current_limit;


  $member_limit=$member_limit-1; 
   if($member_limit>=1 && $vo_credit_limit>=1000)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{  
	$this->SCSP_TSP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs);
	}
	else
	{
	  if($vo_credit_limit>=1000)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{	
	$this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_vo_ac";		
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}


/*
 $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
  if(intval($value)=='1')
	{
  $this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
	}
	else
	{
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	if(intval($value_shg)=='1')
	{
	$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	
	}
	*/
	   

 }
 else
 {
	   if($x>='1')
		{
			if($loan_amount>50000 && ( $reason_loan_code != "025" && $reason_loan_code != "061"))		
			{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_50000";
	      $agi-> stream_file($wfile, '#');	
			}elseif($loan_amount>100000 && ( $reason_loan_code == "025" || $reason_loan_code == "061"))		
			{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_100000";
	      $agi-> stream_file($wfile, '#');	
			}else
			{
 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
			}
			$x=$x-1;
	$this->SCSP_TSP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 
   }
	
 }
 else
 {
	    if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
			$x=$x-1;
	$this->SCSP_TSP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 }
 
 
		



		
	}



function SCSP_quantity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code,$SCSP_quantity_type,$SCSP_quantity_limit){
		$test_vonumber=$GLOBALS['test_vonumber'];
	
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, 5);
	$value=$res_dtmf ["result"];
	if($value == '-1' || $value == ' ' || $value == '' ||$value == '0' || strlen($value)<1 ||$value > $SCSP_quantity_limit) 
{
if($x>1)
  {
   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->SCSP_quantity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code,$SCSP_quantity_type,$SCSP_quantity_limit);
   return $value;
   }
   else
   {
		mssql_close($link);
    $agi->Hangup();
	exit;
   }
}else{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_for";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	if($SCSP_quantity_type == "acres"){
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/telugu/$SCSP_quantity_type";
	$agi-> stream_file($wfile, '#');
	}
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/SP_TSP/$reason_loan_code";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/applying";
	$agi-> stream_file($wfile, '#');
	
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
     $value=$this->SCSP_quantity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code,$SCSP_quantity_type,$SCSP_quantity_limit);
	 return $value;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->SCSP_quantity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code,$SCSP_quantity_type,$SCSP_quantity_limit);
   return $value;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		$agi->Hangup();
	         }
	   
	}
	}
	
	}




function SCSP_TSP_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code)
{
	$test_vonumber=$GLOBALS['test_vonumber'];
	if($reason_loan_code == "025" || $reason_loan_code == "061"){
	 $SCSP_TSP_min_amt="1000";	 
	 $SCSP_TSP_max_amt="100000";
	 $SCSP_TSP_less_min="loan_morethan_50000";
	 $SCSP_TSP_greater_max="loan_morethan_10000";	 	 
	 }else{
	$SCSP_TSP_min_amt="1000";	 	 
	$SCSP_TSP_max_amt="50000";	 
    $SCSP_TSP_less_min="loan_morethan_1000";
    //$SCSP_TSP_greater_max="loan_morethan_50000";	 	 
	$SCSP_TSP_greater_max="member_loan_more_50000";
	}
	
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	if($value>='0')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	$agi-> stream_file($wfile, '#');
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 5000, 1);
	$status=$res_dtmf["result"];
	
		if($status=='1')
		{
		$diff_loan_amt=intval(substr($value,-2,2));
		if($diff_loan_amt=='0')
		{
			
		 if($value<=$SCSP_TSP_max_amt && $value>=$SCSP_TSP_min_amt)
		     {
		return $value;
		           }
				   else
				   {
					if($value>$SCSP_TSP_max_amt)
					{
					$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/$SCSP_TSP_greater_max";
	                $agi-> stream_file($wfile, '#');
					 }
					 if($value<$SCSP_TSP_min_amt)
					 {
					 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/$SCSP_TSP_less_min";
	                $agi-> stream_file($wfile, '#');
					 }
					$x=$x-1;
		      $loan_amount=$this->SCSP_TSP_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code);
			  return  $loan_amount;
			  
				  }
				  }
				   else
				 {
				 if($x>='1')
		 		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_multiples_100";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->SCSP_TSP_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
				 }
	
			
		}
		
		if($status=="2")
		{
		$loan_amount=$this->SCSP_TSP_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code);
		return $loan_amount;
		}
		
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->SCSP_TSP_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
		}
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->SCSP_TSP_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		}
	
		
	
}


function SCSP_TSP_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg)
{
    $test_vonumber=$GLOBALS['test_vonumber'];
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, 3);
	$value=$res_dtmf ["result"];
	$SCSP_codes=mssql_num_rows(mssql_query("SELECT *  FROM  SP_PURPOSE_MASTER(nolock)  where PURPOSE_ID='$value'"));
	if($SCSP_codes == "0"){
		$value='';
		}
if($value == '-1' || $value == ' ' || $value == '' ||$value == '0' || intval($value)>153 || strlen($value)<1) 
{
if($x>1)
  {
   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->SCSP_TSP_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
   return $value;
   }
   else
   {
		mssql_close($link);
    $agi->Hangup();
	exit;
   }
}
else
{
	$hlp_details_rs=mssql_fetch_array(mssql_query("SELECT *  FROM  SP_PURPOSE_MASTER(nolock)  where PURPOSE_ID='$value'"));
	$PURPOSE_Code=$hlp_details_rs['PURPOSE_ID'];
    $PURPOSE_Name=$hlp_details_rs['PURPOSE_NAME'];
    //$SN_GROUPING=$hlp_details_rs['SN_GROUPING'];
	
	  $PURPOSE_Name=str_replace('-','',$PURPOSE_Name);
	  $PURPOSE_Name=str_replace('/','',$PURPOSE_Name);
	  $PURPOSE_Name=str_replace('(','',$PURPOSE_Name);
	  $PURPOSE_Name=str_replace(')','',$PURPOSE_Name);
	  $PURPOSE_Name=str_replace(' ','_',$PURPOSE_Name);
      $PURPOSE_Name=str_replace('.','_',$PURPOSE_Name);    
   	  $PURPOSE_Name=str_replace(' ','_',$PURPOSE_Name); 
   	  $PURPOSE_Name=str_replace('__','_',$PURPOSE_Name);
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_for";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/SP_TSP/$value";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/applying";
		$agi-> stream_file($wfile, '#');
	
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
     $value=$this->SCSP_TSP_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
	 return $value;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->SCSP_TSP_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
   return $value;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		$agi->Hangup();
	         }
	   
	}
	}
	


}


/*function SCSP_quantity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code,$SCSP_quantity_type){
	
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, 5);
	$value=$res_dtmf ["result"];
	if($value == '-1' || $value == ' ' || $value == '' ||$value == '0' || strlen($value)<1) 
{
if($x>1)
  {
   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->SCSP_quantity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code,$SCSP_quantity_type);
   return $value;
   }
   else
   {
		mssql_close($link);
    $agi->Hangup();
	exit;
   }
}else{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_for";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	if($SCSP_quantity_type == "acres"){
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/SP_TSP/$SCSP_quantity_type";
	$agi-> stream_file($wfile, '#');
	}
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/SP_TSP/$reason_loan_code";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/applying";
	$agi-> stream_file($wfile, '#');
	
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
     $value=$this->SCSP_quantity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code,$SCSP_quantity_type);
	 return $value;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->SCSP_quantity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code,$SCSP_quantity_type);
   return $value;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		$agi->Hangup();
	         }
	   
	}
	}
	
	}*/

function project_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	//	$shg_grade_dist_code=substr($void_code,2,2);
//	$shg_grade_mandal_code=substr($void_code,4,2);
//	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
//	$mms_grade=$vo_grade_rs['GRADE'];
//	
//	if($mms_grade=='E')
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_egrade";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
//	
//	if($mms_grade=='F')
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
	
	//change table here
    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select CREDIT_LIMIT from PROJECT_BASED_VO_CREDIT_LIMIT(nolock) where VO_ID='$void_code'"));
	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
	
	if($vo_actual_credit=='0')
	{
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	
		}	

	  //$member_limit=6;
	 
		 
	      if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}

//no bank linkage	
//	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES where SHG_ID='$shg_code' group by SHG_ID"));
//	$overdue_amt=$overdue_rs[0];
//	$bank_name_rs= mssql_fetch_array(mssql_query("select Bank_Code from SHG_OVER_DUES where SHG_ID='$shg_code'"));
//	$bank_name=$bank_name_rs['Bank_Code'];	
//
//	if($overdue_amt>0 && $overdue_amt<=10000)
//	{
//	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
//		$agi-> stream_file($wfile, '#');
//		$this->play_amount($overdue_amt,$agi);
//		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
//	    $agi-> stream_file($wfile, '#');
//$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
//	    $agi-> stream_file($wfile, '#');
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
//		$agi-> stream_file($wfile, '#');
//		$is_over_due='Y';	
//	}
//	 
//	
//	  if($overdue_amt>10000)
//	{	
//	if($x>='1')
//		{
//			
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
//		$agi-> stream_file($wfile, '#');
//		$this->play_amount($overdue_amt,$agi);
//		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
//	    $agi-> stream_file($wfile, '#');
//$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
//	    $agi-> stream_file($wfile, '#');
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
//		$agi-> stream_file($wfile, '#');
//		/*
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/proceed_through_option8";
//		$agi-> stream_file($wfile, '#');
//		*/
//		$x=$x-1;
//		$this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
//		mssql_close($link);
//		$agi->Hangup();
//		  exit;
//		   }else
//		   {
//		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
//		    $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//		   }
//	
//	}

    
//no bank linkage	
	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  
	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  }
		if($status=="1")
		{	
		
			$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
    $shg_active_stat=$shg_active_rs['IS_VALID'];
	if($shg_active_stat=='Y')
	{
	
	}
	else
	{
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied_old";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		   $this->project_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         
	
	}	
	
	$total_deposit_rs=mssql_fetch_array(mssql_query("select sum(DEPOSITED_AMOUNT) as TOTAL_SUM from SHG_DEPOSIT_INFO(nolock) where SHG_ID='$shg_code' and DEPOSIT_TYPE='1'"));
					$shg_total_deposit=$total_deposit_rs['TOTAL_SUM'];			
					//$etime=date('Y-m-d');		  
					$startdate = '2012-11';
					$enddate = date('Y-m'); 
					$timestamp_start = strtotime($startdate);
					$timestamp_end = strtotime($enddate);
					$difference = abs($timestamp_end - $timestamp_start);
					$months = floor($difference/(60*60*24*30));
					$months_to_validate=$months-2;
					$shg_to_deposit=$months_to_validate*100;
					
					if($shg_total_deposit>=$shg_to_deposit)
					{
	
					}
				else{
	  				 if($x>='1')
					{
						$pending_samrudhi_amount=$shg_to_deposit-$shg_total_deposit;
						$pending_samrudhi_amount=$pending_samrudhi_amount+200;
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_amount";
						$agi-> stream_file($wfile, '#');
						$this->play_amount($shg_total_deposit,$agi);
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_deposited";
						$agi-> stream_file($wfile, '#');
						$this->play_amount($pending_samrudhi_amount,$agi);
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_pending";
						$agi-> stream_file($wfile, '#');
						 $x=$x-1;
			           $this->project_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
						$agi->Hangup();
		 				 exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	}
	
		
	//$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE from vo_info where TRANS_VO_ID='$void_code'"));
    //$vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	if( $shg_active_stat=='Y')
	{


//current type

$shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE in ('25')");
$mem_pending_live=mssql_num_rows($shg_mem_rs_live);
	  
$shg_mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('25') and  SHG_ID='$shg_code'");
$mem_rejected=mssql_num_rows($shg_mem_rej_rs);


//other types
	  $shg_mem_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where   SHG_ID='$shg_code' and PROJECT_TYPE not in  ('25') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'");
	  $mem_pending=mssql_num_rows($shg_mem_rs);	

	  $shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE not in ('25')");
	  $mem_pending_live_sn=mssql_num_rows($shg_mem_rs_live);
	  
	 $mem_rej_others=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE not in  ('25') and  SHG_ID='$shg_code'");
	  $mem_rejected_others=mssql_num_rows($mem_rej_others);
	  
	   $mem_pending=$mem_pending-$mem_rejected_others;
	   $mem_pending_live_sn=$mem_pending_live_sn-$mem_rejected_others;
	   
//total
$mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and  SHG_ID='$shg_code'");
$mem_rejected_tot=mssql_num_rows($mem_rej_rs);

	  $total_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where   SHG_ID='$shg_code'");
	  $total_members=mssql_num_rows($total_rs);
	  
	  if($mem_pending_live>=0 && ($mem_pending==0||$mem_pending_live_sn==0))
	  {	 
	  $member_limit=9-$mem_pending_live+$mem_rejected; 
	  }
	  else
	  {
		$member_limit=9-$mem_pending_live+$mem_rejected;
		$total_remain=9-$total_members+$mem_rejected_tot; 
		
		 if($total_remain<=$member_limit)
	   {
		  $member_limit=$total_remain;
	   }else
	   {
		 $member_limit=$member_limit;  
	   }
	   
	  }		
		
/*
	  $x=3;
	$shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE where  VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE in ('8','9')");
	  $mem_pending_live=mssql_num_rows($shg_mem_rs_live);
	  	  
$shg_mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('8','9') and VO_ID='$void_code' and  SHG_ID='$shg_code'");
	  $mem_rejected=mssql_num_rows($shg_mem_rej_rs);	 
	  $member_limit=6-$mem_pending_live+$mem_rejected;   
	  */
	 	
	if($member_limit>=1)
	{	
	$this->project_request_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	 $agi->Hangup();
	exit;
	}
	else{
		  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_6_loans", 5000, 1);
	      $more_res=$res_dtmf ["result"];
		  if($more_res==1)
		  {
		  $x=3;
		  $this->project_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		    }
			else
			{
		mssql_close($link);
			 $agi->Hangup();
			 $agi->Hangup();
			 exit;
			}
		   
		   	
	}
	
 		
	
	
			
			}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->project_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	     
		}
	
		}
	
		if($status=="2")
		{
		$this->project_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->project_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->project_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
		
	

	}
	

function project_request_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs)
{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$length='2';
	$play_msg='two_digit_member_id';
	$type='project_vle';
	$x='3';
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,ACTIVITY,KRUSHE_AMOUNT,SP_ACTIVITY,MOBILE_NUM from SHG_MEMBER_INFO(nolock)  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $ACTIVITY=$member_id_rs['ACTIVITY'];
 $KRUSHE_AMOUNT=$member_id_rs['KRUSHE_AMOUNT'];
   $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 
 $SP_ACTIVITY=$member_id_rs['SP_ACTIVITY'];	
// if($SP_ACTIVITY == "26" || $SP_ACTIVITY == "27" || $SP_ACTIVITY == "36" || $SP_ACTIVITY == "37" ){
//    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/eligible_for_other_sthreenidhi";
//	$agi-> stream_file($wfile, '#');
//	$this->project_request_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
//		mssql_close($link);
//	$agi->Hangup();
//	exit;	
//	
// }
 
 
 if($ACTIVITY=='25')
 {
	 //change prompt
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/project_loan_vle";
$agi-> stream_file($wfile, '#');
 }
 else
 {
	 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/not_eligible_for_project_loan_vle";
	$agi-> stream_file($wfile, '#');
	$this->project_request_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;	 
 }
 
  	$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and PROJECT_TYPE='25'"));
	
	if($member_prev_loan_count>=1)
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$this->project_request_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;	
	}
  
  
//  SHG_MEMBER_MCP_INFO,SAMSNBSTG.SN.SHG_MEMBER_LOAN_DEMAND_STATUS

$shg_member_overdue_array=mysql_fetch_array(mysql_query("select a.BAL as member_overdue  from SHG_MEMBER_LOAN_DEMAND_STATUS(NOLOCK) a,SHG_MEMBER_MCP_INFO(NOLOCK) b where a.SHG_MEMBER_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.SHG_ID='$shg_code' and b.MEMBER_LONG_CODE='$member_id' and a.PROJECT_TYPE not in ('26','27')"));
$shg_member_overdue=$shg_member_overdue_array['member_overdue'];
	if($shg_member_overdue > 0){
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_vle_overdue";
	$agi-> stream_file($wfile, '#');
	$this->project_request_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;	
		}
  
  
  
  
    if(strlen($member_mobile_num) == 10){
		
	}else{
	$length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
		mssql_close($link);
	$agi->hangup();	
	exit;
	}
	
	}
	
  
//$db_filed="reason";
//		$type='project';
//		$length='5';
//		//$play_msg="member_loan_reason_new";
//		$play_msg="project_loan_reason";
//		$x='3';
//$reason_loan =$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);	
	
	
	
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member";
$agi-> stream_file($wfile, '#');
$this->play_amount($KRUSHE_AMOUNT,$agi);
$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
$agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/applying_loan";
$agi-> stream_file($wfile, '#');

$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/vle_amount_confirm", 8000, 1);
$krushe_req_amount=$res_dtmf ["result"];
if($krushe_req_amount=='1')
{
$loan_amount=$KRUSHE_AMOUNT;	
}else{

		if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->project_request_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }	
				
			}	
	
	
	//$vo_id_mandal=substr($void_code,0,6);
	

//	if($loan_request=='YES')
//	{
//  $db_filed="required_loan";
//		$type="amount";
//		$length="9";
//		$play_msg="member_required_loan";
//		$x='3';
//	 $loan_amount=$this->krushe_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$KRUSHE_AMOUNT);
//	}
		
	

		
 
 //$hlp_loan_rs=mssql_fetch_array(mssql_query("SELECT ACTIVITY_NAME FROM  KRUSHE_ACTIVITY_MASTER where ACTIVITY_ID='$reason_loan_code'"));
 //$reason_loan=$hlp_loan_rs['ACTIVITY_NAME'];	

	


//KRUSHE_AMOUNT
 //echo $member_short_code."prev --".$member_prev_loan_count."loan--".$loan_amount."krushe".$KRUSHE_AMOUNT;
 if(strlen($member_short_code)==2 && $member_prev_loan_count=='0' && $loan_amount<=$KRUSHE_AMOUNT && $loan_amount>0 )
 {
	 
	 //reason master data
    //$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select CURRENT_CREDIT_LIMIT from IVRS_VO_POP_CREDIT_LIMIT  where vo_id='$void_code'"));
    //$vo_credit_lt_pop = $vo_credit_pop_rs['CURRENT_CREDIT_LIMIT'];
/*	
if($krushe_type=='MART')
{
$PROJECT_TYPE='$ACTIVITY';	
}
if($krushe_type=='PRODUCER')
{
$PROJECT_TYPE='$ACTIVITY';
}
*/


$PROJECT_TYPE=$ACTIVITY;

	
//	$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select CREDIT_LIMIT from PROJECT_BASED_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
//    $vo_actual_credit_lt_krushe = $vo_credit_pop_rs['CREDIT_LIMIT'];
//	
//$vo_krushe_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code'  and PROJECT_TYPE in ('25')"));
//
//		$vo_krushe_applied=$vo_krushe_applied_rs[0];
//		//$vo_krushe_applied=$vo_krushe_applied+$loan_amount;
//		
////adding rejected amount
//		
//	
//$rej_id=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('25') and VO_ID='$void_code'"));
//$rej_amt_t2=$rej_id[0];	
//	
//	$vo_krushe_applied=$vo_krushe_applied-$rej_amt_t2;
//	
//	$vo_credit_lt_krushe=$vo_actual_credit_lt_krushe-$vo_krushe_applied;
	
	
	
//$rej_shg=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('25') and VO_ID='$void_code' and SHG_ID='$shg_code'"));
//$rej_amt_shg_krushe=$rej_shg[0];	
//	 
//$shg_krushe_lt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('25')"));
//
//		$shg_krushe_lt_max=$shg_krushe_lt_rs[0];
//		$shg_krushe_lt_max=$shg_krushe_lt_max+$loan_amount-$rej_amt_shg_krushe;
//			
//$shg_krushe_lt_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('25')"));
//
//		$shg_krushe_lt_tcs=$shg_krushe_lt_tcs_rs[0];
//		$shg_krushe_lt_tcs=$shg_krushe_lt_tcs+$loan_amount-$rej_amt_shg_krushe;


//$mms_dist_code=substr($void_code,2,2);
//      $mms_mandal_code=substr($void_code,4,2);
//	  $mms_search=substr($void_code,0,6);
//	  
//	  
//	  $mms_total_credit_rs=mssql_fetch_array(mssql_query("select TOTAL_FUND from MMS_CREDIT_LIMIT where DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code'"));
//    $mms_total_credit=$mms_total_credit_rs[0];
//		
//		
//	   $mms_amt_to_add_rs=mssql_fetch_array(mssql_query("select sum(Amount_ADD) from IVRS_VO_CREDIT_LIMIT  where vo_id like '$mms_search%'"));
//       $mms_amt_to=$mms_amt_to_add_rs[0];
//	   
//	   $mms_total_credit=$mms_total_credit+$mms_amt_to;
//
//
//	$mms_applied_additional=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID like '$mms_search%' and PROJECT_TYPE='25' and IS_ADDITIONAL_AMT='Y'"));
//	$mms_additional_amt=$mms_applied_additional[0];
//	
//	
//	
//	if($mms_additional_amt>=2500000)
//	   {
//		$mms_add_applied=2500000;
//		}
//		else if($mms_additional_amt>0 && $mms_additional_amt<2500000)
//		{
//		$mms_add_applied=$mms_additional_amt;
//		}
//		else
//		{
//		$mms_add_applied=0;
//		}
//
//	   
//$extra_credit_rs=mssql_fetch_array(mssql_query("select IS_ADDITIONAL_AMT  from VO_CREDIT_LIMIT  where  VO_ID='$void_code'"));	
//$is_eligible=$extra_credit_rs['IS_ADDITIONAL_AMT'];
//
//$extra_mms_amt=mssql_fetch_array(mssql_query("select ADDITIONAL_AMT  from MMS_CREDIT_LIMIT  where  DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code'"));
//$extra_amt_mms=$extra_mms_amt['ADDITIONAL_AMT'];
//
//
//		
//		$mms_lt_max_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST where VO_ID like '$mms_search%' and PROJECT_TYPE='25'"));
//		$mms_lt_max_tcs=$mms_lt_max_tcs_rs[0];
//		
//		$mms_lt_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID like '$mms_search%' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='25'"));
//		
//		$mms_lt_live=$mms_lt_live_rs[0];
//		
//		$mms_code=substr($void_code,2,4);
//		
//		$mms_repaid_total_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_MMS from SN.SHG_MEMBER_REPAY_VIEW  where MS_CODE='$mms_code'  and PROJECT_TYPE='1'"));
//        $mms_repaid_total = $mms_repaid_total_rs['AMT_REPAID_MMS'];
//		$mms_repaid_total=intval($mms_repaid_total);
//		
//		$mms_credit_max_tcs=$mms_lt_max_tcs+$mms_lt_live+$loan_amount-$mms_repaid_total;
//		
//		
//		
//if($is_eligible=='Y')
//{
//$mms_total_credit=$mms_total_credit+$extra_amt_mms;
//	}
//	else
//	{
//	$mms_credit_max_tcs=$mms_credit_max_tcs-$mms_add_applied;
//	}


			
 $duration='36';
 $etime=date('Y-m-d H:i:s');	
// $PROJECT_TYPE='2';	  
 $member_type='N';
 //&& $vo_credit_lt_sn>=$fund_sn
 
 //$shg_krushe_actual_lt=mssql_fetch_array(mssql_query("select SUM(KRUSHE_AMOUNT) from  SHG_MEMBER_INFO  where SHG_ID='$shg_code'"));
// $shg_krushe_lt=$shg_krushe_actual_lt[0];

//mms credit limit removed
 //if($loan_amount>=1000 && $loan_amount<=$KRUSHE_AMOUNT && $mms_credit_max_tcs<=$mms_total_credit )
  if($loan_amount>=1000 && $loan_amount<=$KRUSHE_AMOUNT  )
  {   
 //$marriage_amt='0';
  $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select SHG_IVRS_LOAN_NO from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE in ('25') order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;
 // $reason_loan_code has to be added
 
if($caller!=$test_vonumber)
{ 
mssql_query("insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,COMBINED_ID,HLP_CODE,IS_OVERDUE,OVERDUE_ID,SHORT_CODE,MEMBER_ID,FUND_SN,FUND_POP) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$ca_id_new','$reason_loan_code','$is_over_due','$due_id_lst','$member_short_code','$member_id','$fund_sn','$fund_pop')");
}

$current_limit=$vo_credit_lt_krushe-$loan_amount;
//mssql_query("update IVRS_VO_POP_CREDIT_LIMIT set CURRENT_CREDIT_LIMIT='$current_limit'  where vo_id='$void_code'");
$vo_credit_limit=$current_limit;


  $member_limit=$member_limit-1; 
   //if($member_limit>=1 && $vo_credit_limit>=1000)
   if($member_limit>=1)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan_project", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{  
	$this->project_request_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	}
	else
	{
//	  if($vo_credit_limit>=1000)
//	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
//	  }
//	  else
//	  {
//	  $value_shg=2;
//	  }
	if(intval($value_shg)=='1')
	{	
	$this->project_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
		mssql_close($link);
	//mssql_close($link1);
	$agi->Hangup();
	exit;
	}
	}


/*
 $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
  if(intval($value)=='1')
	{
  $this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
	}
	else
	{
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	if(intval($value_shg)=='1')
	{
	$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	
	}
	*/
	   

 }
 else
 {
	   if($x>='1')
		{
			if($loan_amount>50000)		
			{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_50000";
	      $agi-> stream_file($wfile, '#');	
			}
			else
			{
 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
			}
			$x=$x-1;
	$this->project_request_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 
   }
	
 }
 else
 {
	    if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
			$x=$x-1;
	$this->project_request_loan_vle($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 }	
}
		

function CMSA_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	  //$member_limit=6;
	  
	  $scsp_ineligible=mssql_num_rows(mssql_query("SELECT * FROM SP_VO_MOBILE(nolock) where VO_ID='$void_code'"));
	  if($scsp_ineligible == 1){
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/SCSP_apply_from_mms";
   	      $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		  }
	  
	  $CMSA_array=mssql_query("SELECT VO_ID FROM SHG_MEMBER_INFO WITH(NOLOCK) WHERE IS_CMSC='Y' AND VO_ID='$void_code' AND SP_ACTIVITY=$SC_T_type");
	  $CMSA_status=mssql_num_rows($CMSA_array);	
	  if($CMSA_status < 1){
	  	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_CMSA_not_eligible";
		  $agi-> stream_file($wfile, '#');
		  $x=$x-1;
		  $this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		mssql_close($link);
		  $agi->Hangup();
		  exit;		  	  	  	
	  }
	  
	  
	  
	   $VO_query=mssql_query("SELECT SP_ACTIVITY,IS_ACTIVE from VO_INFO(nolock) where TRANS_VO_ID='$void_code'");
	  $VO_array=mssql_fetch_array($VO_query);
	  $VO_IS_ACTIVE=$VO_array['IS_ACTIVE'];	  
	  
	  if($VO_IS_ACTIVE != "Y"){
	  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		mssql_close($link);
		$agi->Hangup();
		  exit;
	  }
//	  if($CMSA_status > 1){
//		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_scsp_tsp";
//		  $agi-> stream_file($wfile, '#');	
//		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_SCSP_not_eligible";
//		  $agi-> stream_file($wfile, '#');
//		  $x=$x-1;
//		  $this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
//		mssql_close($link);
//		  $agi->Hangup();
//		  exit;		  	  
//		  }
//	  
//	  if($SCSP_activity != $SC_T_type){
//		  if($SC_T_type == "26" && ($SCSP_activity == "27" || $SCSP_activity == "")){
//		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_SCSP_not_eligible";
//		  $agi-> stream_file($wfile, '#');
//		  }elseif($SC_T_type == "27" && ($SCSP_activity == "26" || $SCSP_activity == "")){
//		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_TSP_not_eligible";
//		  $agi-> stream_file($wfile, '#');	  
//		  }
//		  $x=$x-1;
//		  $this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
//		mssql_close($link);
//		  $agi->Hangup();
//		  exit;
//		  }
	  
	 
		 
	      if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  
	  if($shg_code_rs=='00')
	  {
		 $link = mysql_connect("localhost","root", "evol@9944")
    or die("Data base connection failed");
   mysql_select_db("shtri_nidhi")
    or die("data base open failed");
	$file=$this->record_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$record_confirm,$record_propmt,$language); 
//echo "insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())";
	if($caller!=$test_vonumber)
	{
	mysql_query("insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())");
	}
	
	mysql_close($link);
		mssql_close($link);
		 $agi->Hangup();
		  exit;
		  }
		  
	  /*
	  $vo_shg_count = mssql_num_rows(mssql_query("select * from SHG_INFO  where VO_ID='$void_code'"));
	  if($vo_shg_count>99)
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='00'.$shg_code_rs;
	  }
	  if(strlen($shg_code_rs)=='2')
	  {
      $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
	  $shg_code_short=$shg_code_rs;
	  }
	  
	  }
	  else
	  {
	  if(strlen($shg_code_rs)=='1')
	  {
	  $shg_code_short='0'.$shg_code_rs;
	  }
	  else
	  {
      $shg_code_short=$shg_code_rs;
	  }
	 }
	 */
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}

//no bank linkage for scsp	
	//$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES where SHG_ID='$shg_code' group by SHG_ID"));
//	$overdue_amt=$overdue_rs[0];
//	$bank_name_rs= mssql_fetch_array(mssql_query("select Bank_Code from SHG_OVER_DUES where SHG_ID='$shg_code'"));
//	$bank_name=$bank_name_rs['Bank_Code'];
//
//
//	if($overdue_amt>0 && $overdue_amt<=10000)
//	{
//	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
//		$agi-> stream_file($wfile, '#');
//		$this->play_amount($overdue_amt,$agi);
//		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
//	    $agi-> stream_file($wfile, '#');
//$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
//	    $agi-> stream_file($wfile, '#');
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
//		$agi-> stream_file($wfile, '#');
//		$is_over_due='Y';	
//	}
//	 
//	
//	  if($overdue_amt>10000)
//	{	
//	if($x>='1')
//		{
//			
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
//		$agi-> stream_file($wfile, '#');
//		$this->play_amount($overdue_amt,$agi);
//		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
//	    $agi-> stream_file($wfile, '#');
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
//	    $agi-> stream_file($wfile, '#');
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
//		$agi-> stream_file($wfile, '#');
//		/*
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/proceed_through_option8";
//		$agi-> stream_file($wfile, '#');
//		*/
//		$x=$x-1;
//		$this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
//		mssql_close($link);
//		$agi->Hangup();
//		  exit;
//		   }else
//		   {
//		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
//		    $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//		   }
//	
//	}	
//no bank linkage for scsp
	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  
		  
	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  
		  }
		if($status=="1")
		{
			$status_pop_fund=2;
			/*
			$pop_stat_rs=mssql_query("select TRANS_VO_ID from vo_info where TRANS_VO_ID='$void_code' and IS_POP='Y'");
			$stat_pop=mssql_num_rows($pop_stat_rs);
			$pop_stat_shg_rs=mssql_query("select TRANS_SHG_ID from SHG_INFO where TRANS_SHG_ID='$shg_code' and IS_POP='Y'");
			$pop_stat_shg=mssql_num_rows($pop_stat_shg_rs);
		if($stat_pop>=1 && $pop_stat_shg>=1)
   		{
 		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_for_popfund", 8000, 1);
	    $status_pop_fund=$res_dtmf ["result"];
			}else

			{
				$status_pop_fund=2;
			}
			*/
			
			if($status_pop_fund=='1')
			{
				
				//$x=3;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/stop_pop_loan";
		$agi-> stream_file($wfile, '#');
		//$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
		mssql_close($link);
			$agi->Hangup();
			exit;	
			}
			elseif($status_pop_fund=='2')
			{
				//TRANS_VO_ID
	$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE,CIF_RECOVERY_PERCENTAGE from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
    $vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	/*
	if($vo_active_stat=='Y')
	{
	
	}
	else
	{
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         
	
	}
	*/
	
//No SHG account validation for SCSP_TSP Loan
	
//				
//	$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
//    $shg_active_stat=$shg_active_rs['IS_VALID'];
//	if($shg_active_stat=='Y')
//	{
//	
//	}
//	else
//	{
//	   if($x>='1')
//		{
//		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied_old";
//		$agi-> stream_file($wfile, '#');
//		 $x=$x-1;
//		$this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
//		mssql_close($link);
//		$agi->Hangup();
//		  exit;
//		
//		   }else
//		   {
//		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
//		    $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//		   }
//	
//	         
//	
//	}
	
//No SHG account validation for SCSP_TSP Loan

	
//$cif_recovery=$vo_active_rs['CIF_RECOVERY_PERCENTAGE'];

///no cif validation for SCSP Loan
//$cif_count_res=mssql_fetch_array(mssql_query("select count(*) from SAMSNBSTG.SN.SHG_MEMBERLOAN with (nolock) where vo_id='$void_code' and d_status=0"));
//	$cif_count=$cif_count_res[0];
//	if($cif_count==0)
//	{
//	$cif_recovery=100;	
//	}else
//	{
//$cif=mssql_fetch_array(mssql_query("select  * from SAMSNBSTG.SN.VO_RECOVERY_STATUS where VO_ID='$void_code'"));
//$cif_recovery=$cif['RECOVERY'];
//if($cif_recovery>0 && $cif_recovery<90)
//{
//
//}
//
//if($cif_recovery=='')
//	{
// $cif_recovery=100;
//	
//}	

/*
	//$TO_DATE=date('d-M-Y');
	$TO_DATE='31-Mar-2013';
	$FROM_DATE='01-Apr-2012';

$cif=mssql_fetch_array(mssql_query("SELECT  DISTRICT_ID,MANDAL_ID,VO_ID,( ( CASE WHEN UCOLL >DMD THEN DMD ELSE UCOLL END /DMD) * 100) RECOVERY FROM ( SELECT   DISTRICT_ID,MANDAL_ID,VO_ID,SUM(DMD) DMD,SUM(COLL) COLL,CASE WHEN ISNULL((SELECT SUM(PAID_AMOUNT) FROM  SN.SN_RECOVERY  WHERE STATUS=0 AND  VO_ID=T.VO_ID AND CREDITED_DATE BETWEEN '$FROM_DATE' AND '$TO_DATE' ),0) -SUM(COLL)>0 THEN ISNULL((SELECT SUM(PAID_AMOUNT) FROM  SN.SN_RECOVERY  WHERE STATUS=0 AND  VO_ID=T.VO_ID AND CREDITED_DATE BETWEEN '$FROM_DATE' AND '$TO_DATE' ),0) -SUM(COLL) ELSE 0 END  + SUM(COLL) UCOLL  FROM SN.SHG_DETAILS_YEAR_FUN  ('$FROM_DATE','$TO_DATE')  T  WHERE VO_ID ='$void_code' GROUP BY DISTRICT_ID,MANDAL_ID,VO_ID)T"));
$cif_recovery=$cif['RECOVERY'];	
*/
//	}

///no cif validation for SCSP Loan

//$cif=mssql_fetch_array(mssql_query("select  * from SAMSNBSTG.SN.VO_RECOVERY_STATUS where VO_ID='$void_code'"));
//$cif_recovery=$cif['RECOVERY'];
	
$shg_overdue_array=mssql_fetch_array(mssql_query("select OVERDUE from SAMSNBSTG.SN.shg_report_overdue(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code'"));
$shg_overdue=$shg_overdue_array['OVERDUE'];

if($shg_overdue > 0){
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue_scsp_1";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($shg_code_rs,$agi);
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue_scsp_2";
	$agi-> stream_file($wfile, '#');
			  if($x >= 1){
			$x=$x-1;  
		  $this->CMSA_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type);
		  }else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		  }
		  $agi->Hangup();
}

///no cif validation for SCSP Loan

	//if($cif_recovery<90)
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cif_recovery";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}



//	$shg_grade_dist_code=substr($void_code,2,2);
//	$shg_grade_mandal_code=substr($void_code,4,2);
//	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
//	$mms_grade=$vo_grade_rs['GRADE'];
	
///no cif validation for SCSP Loan	
	/*	
	if($mms_grade=='E')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_egrade";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($mms_grade=='F')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	*/
	
	
	///no Grades validation for SCSP Loan
//    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select GRADE,ACTUAL_CREDIT_LIMIT from VO_CREDIT_LIMIT where VO_ID='$void_code'"));
//	$vo_grade=$vo_grade_rs_rej['GRADE'];
//	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
//	
//		if($vo_grade=='E')
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
//	
//	if($vo_grade=='F')
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
//	if($vo_actual_credit=='0')
//	{
//	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	
//		}
//
//	if($mms_grade=='E')
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_egrade";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
//	
//	if($mms_grade=='F' && ($vo_grade=='B'||$vo_grade=='C'||$vo_grade=='D'||$vo_grade=='F'))
//	{
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
//		
	///no Grades validation for SCSP Loan		

		$x=3;
		//current
		
	  $shg_mem_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where   SHG_ID='$shg_code' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'");
	  $total_loans_applied=mssql_num_rows($shg_mem_rs);	
	  
	  $shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where   SHG_ID='$shg_code' and  IVRS_ID='$ivr_call_id'");
	  $total_loans_applied_live=mssql_num_rows($shg_mem_rs_live);
	  
	 $shg_mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and  SHG_ID='$shg_code'");
$total_loans_rejected=mssql_num_rows($shg_mem_rej_rs);

		$loans_applied=$total_loans_applied+$total_loans_applied_live-$total_loans_rejected;
		
		$total_shg_members_rs=mssql_query("select VO_ID from SHG_MEMBER_INFO(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code'");
		$total_shg_members=mssql_num_rows($total_shg_members_rs);

		$member_limit=$total_shg_members-$loans_applied;

		if($loans_applied > $total_shg_members){
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loans_applied_greater_members";
		$agi-> stream_file($wfile, '#');
		  if($x >= 1){
			$x=$x-1;  
		  $this->CMSA_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type);
		  }else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		  }
		  $agi->Hangup();
		}

//other

	 // $short_term_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE where  VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE  not in ('1')");
//	  $short_term_members=mssql_num_rows($short_term_rs);

	  /*
     $shg_mem_rej_rs=mssql_query("select VO_ID from IVRS_VO_REINITIATED_CREDITS where  VO_ID='$void_code' and  SHG_ID='$shg_code'");
	  $mem_rejected=mssql_num_rows($shg_mem_rej_rs);	
	  */
	
	//total  in ('1','11','8','9','16')
	  

	  
//	 $total_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE where  VO_ID='$void_code' and  SHG_ID='$shg_code'");
//	  $total_members=mssql_num_rows($total_rs);
//	  
//	  $mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and VO_ID='$void_code' and  SHG_ID='$shg_code'");
//$mem_rejected_tot=mssql_num_rows($mem_rej_rs);
//	  
//	  if($mem_pending_live>=0 and $short_term_members=='0')
//	  {
//		  
//	  if($mem_pending>$mem_pending_live)
//	  {
//	    $member_limit=6-$mem_pending+$mem_rejected;
//	  } 
//	  else
//	   {
//		$member_limit=6-$mem_pending_live+$mem_rejected;   
//	   }
//	   
//	  }
//	   if($mem_pending_live>=0 and $short_term_members>'0')
//	  {
//		  
//		  
//	  if($mem_pending>$mem_pending_live)
//	  {
//	    $member_limit=6-$mem_pending+$mem_rejected;
//	  } 
//	  else
//	   {
//		$member_limit=6-$mem_pending_live+$mem_rejected;   
//	   }
//	   
//	   
//	   $total_remain=9-$total_members+$mem_rejected_tot;
//	   
//	   if($total_remain<=$member_limit)
//	   {
//		  $member_limit=$total_remain;
//	   }else
//	   {
//		 $member_limit=$member_limit;  
//	   }
//	   
//	   
//	  }
	  
	   
	   
	    
	//	if($member_limit>=1)
	if($loans_applied < $total_shg_members)
	
		  {
		$amt_stat='Y';
	$this->CMSA_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type);
	       }
		   else
		   {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loans_applied_greater_members";
	      $agi-> stream_file($wfile, '#');
		  //$x=3;
		  
		  if($x >= 1){
			$x=$x-1;  
		  $this->CMSA_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type);
		  }else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		  }
		  $agi->Hangup();
		  }	
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->CMSA_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	     
		}
	
		}
	
		if($status=="2")
		{
		$this->CMSA_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->CMSA_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->CMSA_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
	}		

function CMSA_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type)
{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$length='2';
	$play_msg='two_digit_member_id';
	$type='CMSA_member';
	$db_filed=$SC_T_type;
	$x='3';
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,ACTIVITY,KRUSHE_AMOUNT,SP_ACTIVITY,MOBILE_NUM from SHG_MEMBER_INFO(nolock)  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $ACTIVITY=$member_id_rs['SP_ACTIVITY'];
 //$ACTIVITY=$member_id_rs['ACTIVITY'];
 $KRUSHE_AMOUNT=$member_id_rs['KRUSHE_AMOUNT'];
   $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 
 $SP_ACTIVITY=$member_id_rs['SP_ACTIVITY'];	
// if($SP_ACTIVITY == "26" || $SP_ACTIVITY == "27" ){
//    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/eligible_for_other_sthreenidhi";
//	$agi-> stream_file($wfile, '#');
//	$this->CMSA_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type);
//		mssql_close($link);
//	$agi->Hangup();
//	exit;	
//	
// }
 
 
 if($ACTIVITY=='36' || $ACTIVITY=='37' )
 {
	 //change prompt
//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/applying_for_CMSA";
//$agi-> stream_file($wfile, '#');
 }
 else
 {
	 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_eligible_for_CMSA";
	$agi-> stream_file($wfile, '#');
	$this->CMSA_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type);
		mssql_close($link);
	$agi->Hangup();
	exit;	 
 }
 
  	$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' "));
  	
	
	if($member_prev_loan_count>=1)
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$this->CMSA_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type);
		mssql_close($link);
	$agi->Hangup();
	exit;	
	}
  
    if(strlen($member_mobile_num) == 10){
		
	}else{
	$length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
		mssql_close($link);
	$agi->hangup();	
	exit;
	}
	
	}
	
  
//$db_filed="reason";
//		$type='project';
//		$length='5';
//		//$play_msg="member_loan_reason_new";
//		$play_msg="project_loan_reason";
//		$x='3';
//$reason_loan =$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);	
	
	
	
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/CMSA_member";
$agi-> stream_file($wfile, '#');
$this->play_amount($KRUSHE_AMOUNT,$agi);
$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
$agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/applying_loan";
$agi-> stream_file($wfile, '#');

$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/krushe_amount_confirm", 8000, 1);
$krushe_req_amount=$res_dtmf ["result"];
if($krushe_req_amount=='1')
{
$loan_amount=$KRUSHE_AMOUNT;	
}else{

		if($x>='1')
		{
		 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->CMSA_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }	
				
			}	
	
	
	//$vo_id_mandal=substr($void_code,0,6);
	

//	if($loan_request=='YES')
//	{
//  $db_filed="required_loan";
//		$type="amount";
//		$length="9";
//		$play_msg="member_required_loan";
//		$x='3';
//	 $loan_amount=$this->krushe_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$KRUSHE_AMOUNT);
//	}
		
	

		
 
 //$hlp_loan_rs=mssql_fetch_array(mssql_query("SELECT ACTIVITY_NAME FROM  KRUSHE_ACTIVITY_MASTER where ACTIVITY_ID='$reason_loan_code'"));
 //$reason_loan=$hlp_loan_rs['ACTIVITY_NAME'];	

	


//KRUSHE_AMOUNT
 //echo $member_short_code."prev --".$member_prev_loan_count."loan--".$loan_amount."krushe".$KRUSHE_AMOUNT;
 if(strlen($member_short_code)==2 && $member_prev_loan_count=='0' && $loan_amount<=$KRUSHE_AMOUNT && $loan_amount>0 )
 {
	 
	 //reason master data
    //$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select CURRENT_CREDIT_LIMIT from IVRS_VO_POP_CREDIT_LIMIT  where vo_id='$void_code'"));
    //$vo_credit_lt_pop = $vo_credit_pop_rs['CURRENT_CREDIT_LIMIT'];
/*	
if($krushe_type=='MART')
{
$PROJECT_TYPE='$ACTIVITY';	
}
if($krushe_type=='PRODUCER')
{
$PROJECT_TYPE='$ACTIVITY';
}
*/


$PROJECT_TYPE=$ACTIVITY;

	
//	$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select CREDIT_LIMIT from PROJECT_BASED_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
//    $vo_actual_credit_lt_krushe = $vo_credit_pop_rs['CREDIT_LIMIT'];
//	
//$vo_krushe_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code'  and PROJECT_TYPE in ('25')"));
//
//		$vo_krushe_applied=$vo_krushe_applied_rs[0];
//		//$vo_krushe_applied=$vo_krushe_applied+$loan_amount;
//		
////adding rejected amount
//		
//	
//$rej_id=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('25') and VO_ID='$void_code'"));
//$rej_amt_t2=$rej_id[0];	
//	
//	$vo_krushe_applied=$vo_krushe_applied-$rej_amt_t2;
//	
//	$vo_credit_lt_krushe=$vo_actual_credit_lt_krushe-$vo_krushe_applied;
	
	
	
//$rej_shg=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('25') and VO_ID='$void_code' and SHG_ID='$shg_code'"));
//$rej_amt_shg_krushe=$rej_shg[0];	
//	 
//$shg_krushe_lt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('25')"));
//
//		$shg_krushe_lt_max=$shg_krushe_lt_rs[0];
//		$shg_krushe_lt_max=$shg_krushe_lt_max+$loan_amount-$rej_amt_shg_krushe;
//			
//$shg_krushe_lt_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('25')"));
//
//		$shg_krushe_lt_tcs=$shg_krushe_lt_tcs_rs[0];
//		$shg_krushe_lt_tcs=$shg_krushe_lt_tcs+$loan_amount-$rej_amt_shg_krushe;


//$mms_dist_code=substr($void_code,2,2);
//      $mms_mandal_code=substr($void_code,4,2);
//	  $mms_search=substr($void_code,0,6);
//	  
//	  
//	  $mms_total_credit_rs=mssql_fetch_array(mssql_query("select TOTAL_FUND from MMS_CREDIT_LIMIT where DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code'"));
//    $mms_total_credit=$mms_total_credit_rs[0];
//		
//		
//	   $mms_amt_to_add_rs=mssql_fetch_array(mssql_query("select sum(Amount_ADD) from IVRS_VO_CREDIT_LIMIT  where vo_id like '$mms_search%'"));
//       $mms_amt_to=$mms_amt_to_add_rs[0];
//	   
//	   $mms_total_credit=$mms_total_credit+$mms_amt_to;
//
//
//	$mms_applied_additional=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID like '$mms_search%' and PROJECT_TYPE='25' and IS_ADDITIONAL_AMT='Y'"));
//	$mms_additional_amt=$mms_applied_additional[0];
//	
//	
//	
//	if($mms_additional_amt>=2500000)
//	   {
//		$mms_add_applied=2500000;
//		}
//		else if($mms_additional_amt>0 && $mms_additional_amt<2500000)
//		{
//		$mms_add_applied=$mms_additional_amt;
//		}
//		else
//		{
//		$mms_add_applied=0;
//		}
//
//	   
//$extra_credit_rs=mssql_fetch_array(mssql_query("select IS_ADDITIONAL_AMT  from VO_CREDIT_LIMIT  where  VO_ID='$void_code'"));	
//$is_eligible=$extra_credit_rs['IS_ADDITIONAL_AMT'];
//
//$extra_mms_amt=mssql_fetch_array(mssql_query("select ADDITIONAL_AMT  from MMS_CREDIT_LIMIT  where  DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code'"));
//$extra_amt_mms=$extra_mms_amt['ADDITIONAL_AMT'];
//
//
//		
//		$mms_lt_max_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST where VO_ID like '$mms_search%' and PROJECT_TYPE='25'"));
//		$mms_lt_max_tcs=$mms_lt_max_tcs_rs[0];
//		
//		$mms_lt_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID like '$mms_search%' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='25'"));
//		
//		$mms_lt_live=$mms_lt_live_rs[0];
//		
//		$mms_code=substr($void_code,2,4);
//		
//		$mms_repaid_total_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_MMS from SN.SHG_MEMBER_REPAY_VIEW  where MS_CODE='$mms_code'  and PROJECT_TYPE='1'"));
//        $mms_repaid_total = $mms_repaid_total_rs['AMT_REPAID_MMS'];
//		$mms_repaid_total=intval($mms_repaid_total);
//		
//		$mms_credit_max_tcs=$mms_lt_max_tcs+$mms_lt_live+$loan_amount-$mms_repaid_total;
//		
//		
//		
//if($is_eligible=='Y')
//{
//$mms_total_credit=$mms_total_credit+$extra_amt_mms;
//	}
//	else
//	{
//	$mms_credit_max_tcs=$mms_credit_max_tcs-$mms_add_applied;
//	}


			
 $duration='12';
 $etime=date('Y-m-d H:i:s');	
// $PROJECT_TYPE='2';	  
 $member_type='N';
 //&& $vo_credit_lt_sn>=$fund_sn
 
 //$shg_krushe_actual_lt=mssql_fetch_array(mssql_query("select SUM(KRUSHE_AMOUNT) from  SHG_MEMBER_INFO  where SHG_ID='$shg_code'"));
// $shg_krushe_lt=$shg_krushe_actual_lt[0];
 
  if($loan_amount>=1000 && $loan_amount<=$KRUSHE_AMOUNT)
  {   
 //$marriage_amt='0';
  $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select SHG_IVRS_LOAN_NO from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE in ('$ACTIVITY') order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;
 // $reason_loan_code has to be added
 
if($caller!=$test_vonumber)
{ 
mssql_query("insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,COMBINED_ID,HLP_CODE,IS_OVERDUE,OVERDUE_ID,SHORT_CODE,MEMBER_ID,FUND_SN,FUND_POP) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$ca_id_new','$reason_loan_code','$is_over_due','$due_id_lst','$member_short_code','$member_id','$fund_sn','$fund_pop')");
}

$current_limit=$vo_credit_lt_krushe-$loan_amount;
//mssql_query("update IVRS_VO_POP_CREDIT_LIMIT set CURRENT_CREDIT_LIMIT='$current_limit'  where vo_id='$void_code'");
$vo_credit_limit=$current_limit;


  $member_limit=$member_limit-1; 
   //if($member_limit>=1 && $vo_credit_limit>=1000)
   if($member_limit>=1)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{  
	$this->CMSA_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type);
	}
	else
	{
//	  if($vo_credit_limit>=1000)
//	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
//	  }
//	  else
//	  {
//	  $value_shg=2;
//	  }
	if(intval($value_shg)=='1')
	{	
	$this->CMSA_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_vo_ac";	
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}


/*
 $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
  if(intval($value)=='1')
	{
  $this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
	}
	else
	{
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	if(intval($value_shg)=='1')
	{
	$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	
	}
	*/
	   

 }
 else
 {
	   if($x>='1')
		{
			if($loan_amount>50000)		
			{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_50000";
	      $agi-> stream_file($wfile, '#');	
			}
			else
			{
 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
			}
			$x=$x-1;
	$this->CMSA_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 
   }
	
 }
 else
 {
	    if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
			$x=$x-1;
	$this->CMSA_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 }	
}



function IWMP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	  //$member_limit=6;
	  
	  $scsp_ineligible=mssql_num_rows(mssql_query("SELECT * FROM SP_VO_MOBILE(nolock) where VO_ID='$void_code'"));
	  if($scsp_ineligible == 1){
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/SCSP_apply_from_mms";
   	      $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		  }
	  
	  
	  $IWMP_ineligible=mssql_num_rows(mssql_query("SELECT VO_ID FROM VO_IWMP_CREDIT_LIMIT(nolock) where VO_ID='$void_code'"));
	  if($IWMP_ineligible == "0"){
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/IWMP_ineligible";
   	      $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		  }
	  
	  $vo_array=mssql_query("select IS_ACTIVE from VO_INFO(nolock)  where  TRANS_VO_ID='$void_code' ");
	  $vo_name_array=mssql_fetch_array($vo_array);
	  $VO_IS_ACTIVE=$vo_name_array['IS_ACTIVE'];	  
	  
	  if($VO_IS_ACTIVE != "Y"){
	  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		mssql_close($link);
		$agi->Hangup();
		  exit;
	  }
	      if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  
	  if($shg_code_rs=='00')
	  {
		 $link = mysql_connect("localhost","root", "evol@9944")
    or die("Data base connection failed");
   mysql_select_db("shtri_nidhi")
    or die("data base open failed");
	$file=$this->record_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$record_confirm,$record_propmt,$language); 
//echo "insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())";
	if($caller!=$test_vonumber)
	{
	mysql_query("insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())");
	}
	
	mysql_close($link);
		mssql_close($link);
		 $agi->Hangup();
		  exit;
		  }
		  
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}

	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  
		  
	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  
		  }
		if($status=="1")
		{
			$status_pop_fund=2;
			/*
			$pop_stat_rs=mssql_query("select TRANS_VO_ID from vo_info where TRANS_VO_ID='$void_code' and IS_POP='Y'");
			$stat_pop=mssql_num_rows($pop_stat_rs);
			$pop_stat_shg_rs=mssql_query("select TRANS_SHG_ID from SHG_INFO where TRANS_SHG_ID='$shg_code' and IS_POP='Y'");
			$pop_stat_shg=mssql_num_rows($pop_stat_shg_rs);
		if($stat_pop>=1 && $pop_stat_shg>=1)
   		{
 		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_for_popfund", 8000, 1);
	    $status_pop_fund=$res_dtmf ["result"];
			}else

			{
				$status_pop_fund=2;
			}
			*/
			
			if($status_pop_fund=='1')
			{
				
				//$x=3;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/stop_pop_loan";
		$agi-> stream_file($wfile, '#');
		//$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
		mssql_close($link);
			$agi->Hangup();
			exit;	
			}
			elseif($status_pop_fund=='2')
			{
				//TRANS_VO_ID
	$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE,CIF_RECOVERY_PERCENTAGE from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
    $vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	
	
$shg_overdue_array=mssql_fetch_array(mssql_query("select OVERDUE from SAMSNBSTG.SN.shg_report_overdue(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code'"));
$shg_overdue=$shg_overdue_array['OVERDUE'];

if($shg_overdue > 0){
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue_scsp_1";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($shg_code_rs,$agi);
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue_scsp_2";
	$agi-> stream_file($wfile, '#');
			  if($x >= 1){
			$x=$x-1;  
		  $this->SCSP_TSP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		  }else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		  }
		  $agi->Hangup();
}

		$x=3;
		//current
		
	  $shg_mem_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where   SHG_ID='$shg_code' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'");
	  $total_loans_applied=mssql_num_rows($shg_mem_rs);	
	  
	  $shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code' and  IVRS_ID='$ivr_call_id'");
	  $total_loans_applied_live=mssql_num_rows($shg_mem_rs_live);
	  
	 $shg_mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and  SHG_ID='$shg_code'");
$total_loans_rejected=mssql_num_rows($shg_mem_rej_rs);

		$loans_applied=$total_loans_applied+$total_loans_applied_live-$total_loans_rejected;
		
		$total_shg_members_rs=mssql_query("select VO_ID from SHG_MEMBER_INFO(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code'");
		$total_shg_members=mssql_num_rows($total_shg_members_rs);

		$member_limit=$total_shg_members-$loans_applied;

		if($loans_applied > $total_shg_members){
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loans_applied_greater_members";
		$agi-> stream_file($wfile, '#');
		  if($x >= 1){
			$x=$x-1;  
		  $this->IWMP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		  }else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		  }
		  $agi->Hangup();
		}

	if($loans_applied < $total_shg_members)
	
		  {
		$amt_stat='Y';
	$this->IWMP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs);
	       }
		   else
		   {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loans_applied_greater_members";
	      $agi-> stream_file($wfile, '#');
		  //$x=3;
		  
		  if($x >= 1){
			$x=$x-1;  
		  $this->IWMP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		  }else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		  }
		  $agi->Hangup();
		  }	
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->IWMP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	     
		}
	
		}
	
		if($status=="2")
		{
		$this->IWMP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->IWMP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->IWMP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
	}		


function IWMP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs)
	{	
	$test_vonumber=$GLOBALS['test_vonumber'];
	
//	
//	$vo_credit_SCSP_TSP_rs=mssql_fetch_array(mssql_query("select actual_credit_limit from vo_sp_credit_limit where vo_id='$void_code' and SP_ACTIVITY='$SC_T_type'"));
//    $vo_actual_credit_lt_SCSP_TSP = $vo_credit_SCSP_TSP_rs['actual_credit_limit'];
//	
//
//	
//$vo_SCSP_TSP_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST where shg_id in ($vo_shgs) and PROJECT_TYPE='$SC_T_type'  "));
//
//		$vo_SCSP_TSP_applied=$vo_SCSP_TSP_applied_rs[0];
//
//
//$vo_SCSP_TSP_applied_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where shg_id in ($vo_shgs) and PROJECT_TYPE='$SC_T_type'  and IVRS_ID='$ivr_call_id'"));
//
//                $vo_SCSP_TSP_applied_live=$vo_SCSP_TSP_applied_live_rs[0];
//
//		//$vo_krushe_applied=$vo_krushe_applied+$loan_amount;
//		
////adding rejected amount
//		
//	
//$SCSP_TSP_rej_id=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE='$SC_T_type' and shg_id in ($vo_shgs)"));
//$SCSP_TSP_rej_amt_t2=$SCSP_TSP_rej_id[0];	
//	
//	$vo_SCSP_TSP_applied_reinitiated=$vo_SCSP_TSP_applied-$SCSP_TSP_rej_amt_t2;
//	
//	$vo_credit_lt_SCSP_TSP=$vo_actual_credit_lt_SCSP_TSP-$vo_SCSP_TSP_applied_reinitiated-$vo_SCSP_TSP_applied_live;
//	
//	
//	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/SCSP_TSP_credit_limit";
//    $agi-> stream_file($wfile, '#');
//	
//	$this->play_amount($vo_credit_lt_SCSP_TSP,$agi);	
//	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
//    $agi-> stream_file($wfile, '#');
//		
//	//scsp credit limit
	
	
	$length='2';
	$play_msg='two_digit_member_id';
	$type='IWMP_MEMBER';
	$x='3';
	$db_filed="IWMP";
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,MOBILE_NUM,SP_ACTIVITY from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
   $member_mobile_num=$member_id_rs['MOBILE_NUM'];
    $SC_T_type=$member_id_rs['SP_ACTIVITY'];
	
	
	//$vo_id_mandal=substr($void_code,0,6);

	
	$member_prev_loan_count_array = mssql_fetch_array(mssql_query("select count(VO_ID) as app_loans from SHG_LOAN_APPLICATION(nolock) where SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and STATUS_ID != '11'"));
	$member_prev_loan_count=$member_prev_loan_count_array['app_loans'];
	
	$member_prev_loan_live_count_array = mssql_fetch_array(mssql_query("select count(VO_ID) as live_loans from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and IVRS_ID='$ivr_call_id' and MEMBER_ID='$member_id'"));
	$member_prev_loan_live_count=$member_prev_loan_live_count_array['live_loans'];
	
	$member_prev_loan_count=$member_prev_loan_count+$member_prev_loan_live_count;
	
	
	
	if($member_prev_loan_count>=1)
	{
	
if($x>='1')
		{
		$member_prev_loan_count_sthreenidhi = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and STATUS_ID != '11' and PROJECT_TYPE not in ('38','39','40')"));
		
		$member_prev_loan_live_count_sthreenidhi = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE not in ('38','39','40')"));
		
		if($member_prev_loan_count_sthreenidhi >= "1" || $member_prev_loan_live_count_sthreenidhi >= "1"){
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/already_sthreenidhi_loan";
		$agi-> stream_file($wfile, '#');
		}else{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
		$agi-> stream_file($wfile, '#');
		}
		 $x=$x-1;
		$this->IWMP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }

	
	$length='3';
	$play_msg='three_digit_family_baseline_id';
	$type='BASE';
	$x='3';
	$db_filed=$SC_T_type;
	$upper_limit=$member_short_code;
	$family_baseline_id=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
	
	
    	if(strlen($member_mobile_num) == 10){
		
	}else{
	$length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{	
	if($mobile_count>'1'){
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_number_already_registered";
	$agi-> stream_file($wfile, '#');	
	}
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
	if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
			$x=$x-1;
			$SC_T_type="";
	$this->IWMP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
	//$agi->Hangup();	
	//exit;	
	}
	
	}
	
				$db_filed="reason";
		$type='why_loan';
		$length='5';
		$play_msg="SCSP_TSP_activity_code";
		$x='3';
 $reason_loan_code=$this->SCSP_TSP_activity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$type,$length,$play_msg);
  $hlp_loan_rs=mssql_fetch_array(mssql_query("SELECT PURPOSE_NAME FROM  SP_PURPOSE_MASTER(nolock) where PURPOSE_ID='$reason_loan_code'"));
 $reason_loan=$hlp_loan_rs['PURPOSE_NAME'];
 
 
 	 if(($reason_loan_code >= "001" && $reason_loan_code <= "010" ) || $reason_loan_code == "127" || $reason_loan_code == "128" ){
		 $SCSP_quantity_type="count";
		 $play_msg="how_many";
		 $SCSP_quantity_limit=15;
	 }elseif($reason_loan_code == "012" || $reason_loan_code == "013" || $reason_loan_code == "025"){
		 $SCSP_quantity_type="acres";
		 $play_msg="how_many_acres";	 			 
 		 $SCSP_quantity_limit=5;
   	 }
	
	if($SCSP_quantity_type != ''){
	 $SCSP_quantity=$this->SCSP_quantity($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code,$SCSP_quantity_type,$SCSP_quantity_limit);
		
		
		}
 
 
 if($reason_loan_code == "025" || $reason_loan_code == "061" || $reason_loan_code == "153"){
	 $SCSP_TSP_min_amt="1000";	 
	 $SCSP_TSP_max_amt="100000";	 
	 }else{
	$SCSP_TSP_min_amt="1000";	 	 
	$SCSP_TSP_max_amt="50000";	 
	}
	
//	025--land purchase,061-auto purchase
	
	//if($loan_request=='YES')
	//{
  $db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->SCSP_TSP_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$reason_loan_code);
	 
	 
	 	 
	//}
//KRUSHE_AMOUNT
 //echo $member_short_code."prev --".$member_prev_loan_count."loan--".$loan_amount."krushe".$KRUSHE_AMOUNT;
 if(strlen($member_short_code)==2 && $member_prev_loan_count=='0' && $loan_amount<=$SCSP_TSP_max_amt  && $loan_amount>$SCSP_TSP_min_amt )
 {
	 
	 //reason master data
    //$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select CURRENT_CREDIT_LIMIT from IVRS_VO_POP_CREDIT_LIMIT  where vo_id='$void_code'"));
    //$vo_credit_lt_pop = $vo_credit_pop_rs['CURRENT_CREDIT_LIMIT'];
/*	
if($krushe_type=='MART')
{
$PROJECT_TYPE='$ACTIVITY';	
}
if($krushe_type=='PRODUCER')
{
$PROJECT_TYPE='$ACTIVITY';
}
*/


$PROJECT_TYPE=$SC_T_type;

	
	$vo_credit_SCSP_TSP_rs=mssql_fetch_array(mssql_query("select actual_credit_limit from VO_IWMP_CREDIT_LIMIT(nolock)  where vo_id='$void_code' and SP_ACTIVITY='$SC_T_type'"));
    $vo_actual_credit_lt_SCSP_TSP = $vo_credit_SCSP_TSP_rs['actual_credit_limit'];
	

	
$vo_SCSP_TSP_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE='$SC_T_type' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' "));

		$vo_SCSP_TSP_applied=$vo_SCSP_TSP_applied_rs[0];


$vo_SCSP_TSP_applied_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE='$SC_T_type'  and IVRS_ID='$ivr_call_id'"));

                $vo_SCSP_TSP_applied_live=$vo_SCSP_TSP_applied_live_rs[0];

		//$vo_krushe_applied=$vo_krushe_applied+$loan_amount;
		
//adding rejected amount
		
	
$SCSP_TSP_rej_id=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE='$SC_T_type' and shg_id in ($vo_shgs)"));
$SCSP_TSP_rej_amt_t2=$SCSP_TSP_rej_id[0];	
	


$IWMP_repaid_array=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_POP from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='0' and PROJECT_TYPE='1'"));
        $vo_repaid = $IWMP_repaid_array['AMT_REPAID_POP'];	

	
	$vo_SCSP_TSP_applied_reinitiated=$vo_SCSP_TSP_applied-$SCSP_TSP_rej_amt_t2-$vo_repaid;
	
	$vo_credit_lt_SCSP_TSP=$vo_actual_credit_lt_SCSP_TSP-$vo_SCSP_TSP_applied_reinitiated-$vo_SCSP_TSP_applied_live;
	
	
	
	
	$vo_SCSP_credit_with_loan_amt=$vo_SCSP_TSP_applied+$vo_SCSP_TSP_applied_live+$loan_amount-$SCSP_TSP_rej_amt_t2;
	
	
	
//commented by ravi
	
	/*
$SCSP_TSP_rej_shg=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('$SC_T_type') and VO_ID='$void_code' and SHG_ID='$shg_code'"));
$rej_amt_shg_SCSP_TSP=$SCSP_TSP_rej_shg[0];	
	 
$shg_SCSP_TSP_lt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('$SC_T_type')  and  IVRS_ID='$ivr_call_id'"));

		$shg_SCSP_TSP_lt_max=$shg_SCSP_TSP_lt_rs[0];
		$shg_SCSP_TSP_lt_max=$shg_SCSP_TSP_lt_max+$loan_amount-$rej_amt_shg_SCSP_TSP;
			
$shg_SCSP_TSP_lt_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('$SC_T_type')"));

		$shg_SCSP_TSP_lt_tcs=$shg_SCSP_TSP_lt_tcs_rs[0];
		$shg_SCSP_TSP_lt_tcs=$shg_SCSP_TSP_lt_tcs+$loan_amount-$rej_amt_shg_SCSP_TSP;
		*/
 //$duration='24';
 
if($loan_amount >= 1000 && $loan_amount <= 25000){
	$duration="24";
	}elseif($loan_amount >= 25001 && $loan_amount <= 50000){
	$duration="36";	
	}elseif($loan_amount >= 50001 && $loan_amount <= 100000){
	$duration="60";	
	}

			

 $etime=date('Y-m-d H:i:s');	
// $PROJECT_TYPE='2';	  
 $member_type='N';
 //&& $vo_credit_lt_sn>=$fund_sn
 
 	  $shg_mem_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where   SHG_ID='$shg_code' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'");
	  $total_loans_applied=mssql_num_rows($shg_mem_rs);	
	  
	  $shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code'  and  IVRS_ID='$ivr_call_id'");
	  $total_loans_applied_live=mssql_num_rows($shg_mem_rs_live);
	  
	 $shg_mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and  SHG_ID='$shg_code'");
$total_loans_rejected=mssql_num_rows($shg_mem_rej_rs);

		$loans_applied=$total_loans_applied+$total_loans_applied_live-$total_loans_rejected;
		
		$total_shg_members_rs=mssql_query("select VO_ID from SHG_MEMBER_INFO(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code'");
		$total_shg_members=mssql_num_rows($total_shg_members_rs);
 
 
 $member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and STATUS_ID != '11'"));
 
 $member_prev_loan_live_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id'"));
	
	$member_prev_loan_count=$member_prev_loan_count+$member_prev_loan_live_count;
	
 
// $ivr_log_loan="IVRS ID: $ivr_call_id ,vo_id: $void_code ,shg_id: $shg_code, mem_short: $member_short_code,member_id: $member_id , loan_amount: $loan_amount, vo_actual_credit_lt_SCSP_TSP: $vo_actual_credit_lt_SCSP_TSP,vo_SCSP_TSP_applied: $vo_SCSP_TSP_applied,vo_SCSP_TSP_applied_live: $vo_SCSP_TSP_applied_live,SCSP_TSP_rej_amt_t2: $SCSP_TSP_rej_amt_t2, vo_credit_lt_SCSP_TSP: $vo_credit_lt_SCSP_TSP, vo_SCSP_credit_with_loan_amt: $vo_SCSP_credit_with_loan_amt, vo_actual_credit_lt_SCSP_TSP: $vo_actual_credit_lt_SCSP_TSP , live_query : select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID=$void_code and PROJECT_TYPE=$SC_T_type  and IVRS_ID=$ivr_call_id";

$ivr_log_loan="IVRS ID: $ivr_call_id ,vo_id: $void_code ,shg_id: $shg_code, mem_short: $member_short_code,member_id: $member_id , loan_amount: $loan_amount, vo_actual_credit_lt_SCSP_TSP: $vo_actual_credit_lt_SCSP_TSP,vo_SCSP_TSP_applied: $vo_SCSP_TSP_applied,vo_SCSP_TSP_applied_live: $vo_SCSP_TSP_applied_live,SCSP_TSP_rej_amt_t2: $SCSP_TSP_rej_amt_t2, vo_credit_lt_SCSP_TSP: $vo_credit_lt_SCSP_TSP, vo_SCSP_credit_with_loan_amt: $vo_SCSP_credit_with_loan_amt, vo_actual_credit_lt_SCSP_TSP: $vo_actual_credit_lt_SCSP_TSP ,reason_loan: $reason_loan ,reason_loan_code:  $reason_loan_code ,Quantity: $SCSP_quantity ,SCSP_TSP_min_amt :$SCSP_TSP_min_amt ,SCSP_TSP_max_amt: $SCSP_TSP_max_amt ,member_prev_loan_count: $member_prev_loan_count, loans_applied: $loans_applied, total_shg_members:$total_shg_members";

//, live_query : select SUM LOAN_AMOUNT from IVRS_LOAN_REQUEST_LIVE where VO_ID=$void_code and PROJECT_TYPE=$SC_T_type  and IVRS_ID=$ivr_call_id
if($loan_amount>=$SCSP_TSP_min_amt && $loan_amount<=$SCSP_TSP_max_amt && $vo_credit_lt_SCSP_TSP>=$loan_amount && $member_prev_loan_count==0  && $loans_applied < $total_shg_members && $vo_SCSP_credit_with_loan_amt <= $vo_actual_credit_lt_SCSP_TSP)
  {
	  $ivr_log_loan=$ivr_log_loan." ::: Success";
	}else{
	  $ivr_log_loan=$ivr_log_loan." ::: Fail";
		}



$touch_cmd="/usr/bin/touch /var/log/ivrs/".$ivr_call_id.".log";
exec($touch_cmd);

$ivr_log_loan_cmd="/bin/echo $ivr_log_loan >> /var/log/ivrs/".$ivr_call_id.".log";
exec($ivr_log_loan_cmd);
 
 
 
   if($loan_amount>=$SCSP_TSP_min_amt && $loan_amount<=$SCSP_TSP_max_amt && $vo_credit_lt_SCSP_TSP>=$loan_amount && $member_prev_loan_count==0  && $loans_applied < $total_shg_members && $vo_SCSP_credit_with_loan_amt <= $vo_actual_credit_lt_SCSP_TSP)
  {
 //$marriage_amt='0';
  $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select SHG_IVRS_LOAN_NO from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE in ('$SC_T_type')  and IVRS_ID='$ivr_call_id' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;
 // $reason_loan_code has to be added
 
//echo "insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,COMBINED_ID,HLP_CODE,IS_OVERDUE,OVERDUE_ID,SHORT_CODE,MEMBER_ID,FUND_SN,FUND_POP,baseline_id) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$ca_id_new','$reason_loan_code','$is_over_due','$due_id_lst','$member_short_code','$member_id','$fund_sn','$fund_pop','$family_baseline_id')"; 
 
if($caller!=$test_vonumber)
{
mssql_query("insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,COMBINED_ID,HLP_CODE,IS_OVERDUE,OVERDUE_ID,SHORT_CODE,MEMBER_ID,FUND_SN,FUND_POP,BASELINE_ID,QUANTITY) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$ca_id_new','$reason_loan_code','$is_over_due','$due_id_lst','$member_short_code','$member_id','$fund_sn','$fund_pop','$family_baseline_id','$SCSP_quantity')");
}

$current_limit=$vo_credit_lt_SCSP_TSP-$loan_amount;
//mssql_query("update IVRS_VO_POP_CREDIT_LIMIT set CURRENT_CREDIT_LIMIT='$current_limit'  where vo_id='$void_code'");
$vo_credit_limit=$current_limit;


  $member_limit=$member_limit-1; 
  $SC_T_type='';
   if($member_limit>=1 && $vo_credit_limit>=1000)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{  
	$this->IWMP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs);
	}
	else
	{
	  if($vo_credit_limit>=1000)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{	
	$this->IWMP_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$SC_T_type,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_vo_ac";		
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}


/*
 $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
  if(intval($value)=='1')
	{
  $this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
	}
	else
	{
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	if(intval($value_shg)=='1')
	{
	$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	
	}
	*/
	   

 }
 else
 {
	   if($x>='1')
		{
			if($loan_amount>50000 && ( $reason_loan_code != "025" && $reason_loan_code != "061"))		
			{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_50000";
	      $agi-> stream_file($wfile, '#');	
			}elseif($loan_amount>100000 && ( $reason_loan_code == "025" || $reason_loan_code == "061"))		
			{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_100000";
	      $agi-> stream_file($wfile, '#');	
			}else
			{
 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
			}
			$x=$x-1;
	$this->IWMP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 
   }
	
 }
 else
 {
	    if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
			$x=$x-1;
	$this->IWMP_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$SC_T_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 }
 
 
		



		
	}





function vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name)
	{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, $length);
	$value=$res_dtmf ["result"];
	//$value='101';
	if($value>='0')
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_entered";
	$agi-> stream_file($wfile, '#');
	$this->play_amount($value,$agi);
	$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	$agi-> stream_file($wfile, '#');
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 5000, 1);
	$status=$res_dtmf["result"];
	//$status='1';
		if($status=='1')
		{
		$diff_loan_amt=intval(substr($value,-2,2));
		if($diff_loan_amt=='0')
		{
		 //$credit_limit_rs= mysql_fetch_array(mysql_query("select credit_limit from shg_users where id='$shg_user_id'"));
		 //$credit_limit=$credit_limit_rs['credit_limit'];
		// echo  $credit_limit."fdsfdsfdsfdsfdsf";
		 if($value<=25000 && $value>=1000)
		     {
		 //echo "insert into shg_log(id,shg_code,required_loan,loan_repay_duration,members,date,mobile) values('','$shg_code','$value','','',now(),'$caller')";
		//mysql_query("insert into shg_log(shg_code,required_loan,current_loan,loan_repay_duration,date,mobile,shg_user_id,unique_id) values('$shg_code','$value','$value','12',now(),'$caller','$shg_user_id','$unique_id')");
		//$shg_log_id=mysql_insert_id();
		return $value;
		           }
				   else
				   {
				    //$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/credit_limit";
	                //$agi-> stream_file($wfile, '#');
					//$this->play_amount($credit_limit,$agi);
					if($value>25000)
					{
		            //$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_less_15000";
					$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_25000";
	                $agi-> stream_file($wfile, '#');
					 }
					 if($value<1000)
					 {
					 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_morethan_1000";
	                $agi-> stream_file($wfile, '#');
					 }
					$x=$x-1;
		      $loan_amount=$this->vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
			  return  $loan_amount;
			  
				  }
				  }
				   else
				 {
				 if($x>='1')
		 		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_multiples_100";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
				 }
	
			
		}
		
		if($status=="2")
		{
		$loan_amount=$this->vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		}
		
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		   $agi->Hangup();
		   exit;
		   }
		}
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$loan_amount=$this->vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
		return $loan_amount;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		}

	
	}
	

	function vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$members,$shg_member_log_id,$shg_member_code)
{
   $test_vonumber=$GLOBALS['test_vonumber'];
   $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/$play_msg", 5000, 1);
	$value=$res_dtmf ["result"];

if($value == '-1' || $value == ' ' || $value == '' || (($type=='why_loan')&&($value=='8'||$value=='9'||$value=='*' ))|| (($type=='mem_mode')&&($value=='3'|| $value=='4'||$value=='5'|| $value=='6'||$value=='7'||$value=='8'||$value=='9'||$value=='*' )) || (($type=='project') && ($value>'6'||$value=='*' )) ) 
{
if($x>1)
  {
   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$members,$shg_member_log_id,$shg_member_code);
   return $value;
   }
   else
   {
		mssql_close($link);
    $agi->Hangup();
	exit;
   }
}
else
{
	if($type=='why_loan')
	{
	  if(intval($value)=='1')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/crop";
	$agi-> stream_file($wfile, '#');
	$value='Agriculture';
	      }
		  if(intval($value)=='2')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/dairy";
	$agi-> stream_file($wfile, '#');
	$value='Dairy';
	      }
		   if(intval($value)=='3')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/weavers";
	$agi-> stream_file($wfile, '#');
	$value='Weavers';
	      }
		  
		  if(intval($value)=='4')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/IG-Activity";
	$agi-> stream_file($wfile, '#');
	$value='Income generation Activity';
	      }
		    if(intval($value)=='5')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/emergency";
	$agi-> stream_file($wfile, '#');
	$value='health';
	      }
		     if(intval($value)=='6')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/education";
	$agi-> stream_file($wfile, '#');
	$value='Education';
	      }
		  if(intval($value)=='7')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/marriage";
	$agi-> stream_file($wfile, '#');
	$value='Marriage';
	      }
		  
	}
	if($type=='project')
	{
		  if(intval($value)=='1')
	     { 
		 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/dairy_project";
	$agi-> stream_file($wfile, '#');
	$value='Dairy';
	
	      }
		  if(intval($value)=='2')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/crop_project";
	$agi-> stream_file($wfile, '#');
	$value='Agriculture';
	      }
		   if(intval($value)=='3')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/weavers_project";
	$agi-> stream_file($wfile, '#');
	$value='Cheneta';
	      }
		  
		  if(intval($value)=='4')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cattle_project";
	$agi-> stream_file($wfile, '#');
	$value='Sheep Goat and Hen';
	      }
		    if(intval($value)=='5')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/others_project";
	$agi-> stream_file($wfile, '#');
	$value='Others';
	      }	
		
	}
	
	if($type=='mem_mode')
	{

	if(intval($value)=='1')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/pop";
	$agi-> stream_file($wfile, '#');
	$value='Y';
	      }	  
		  if(intval($value)=='2')
	     { 
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/poor";
	$agi-> stream_file($wfile, '#');
	$value='N';
	      }
	}
	
	
	$get_confirmation = $this->verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language);	
	if($get_confirmation==2)
	{
	 $value=$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$members,$shg_member_log_id,$shg_member_code);
	 return $value;
	}
	if($get_confirmation==1)
	{
	return $value;
	}else
	{
	if($x>=1){
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
   $value=$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$members,$shg_member_log_id,$shg_member_code);
   return $value;
       }
	   else{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		$agi-> stream_file($wfile, '#');
		mssql_close($link);
		$agi->Hangup();
		exit;
	         }
	   
	}
	}
	


}
function verify_data($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language)
{
$test_vonumber=$GLOBALS['test_vonumber'];
$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	 $status=$res_dtmf ["result"];
	return $status;
}	

function verify_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language)
{
$test_vonumber=$GLOBALS['test_vonumber'];
$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2_info", 3000, 1);
	 $status=$res_dtmf ["result"];
	return $status;
}
		
	function play_digit($amount,$agi)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	 	$t=strlen($amount);
		$n=$amount;
			for($j=$t;$j>0;$j--)
			{
			$digit=substr($n,0,1);
			$file="/var/lib/asterisk/sounds/telugu_digits/".$digit;
			$agi-> stream_file($file, '#');
			$n=substr($n,1);			
			}
	}
	
	function play_amount($amount,$agi)
	{
		$test_vonumber=$GLOBALS['test_vonumber'];
	 	$t=strlen($amount);
		$n=$amount;
			for($j=$t;$j>0;$j--)
			{
			     	//echo $n;
			     	$d="1";
			     	if($j == '5' || $j == '7' || $j == '9')
			     	{
						$r=$j-1;
					}
					else
					{
						$r=$j;	
					}
			     	
					while($r-1 > 0)
					{
						$z.="0";
						$r--;
					}
					
					$d=$d.$z;
					
					$x=$n/$d;
					
					$a=floor($x);
					
					$play=$a*$d;
					 
					
					//echo $d;
					if($n > "10" && $n < "20")
					{
						$file="/var/lib/asterisk/sounds/telugu_digits/".$n;
						$agi-> stream_file($file, '#');
						$j--; 	
					}
					else if($d == "10" || $d == "1" || $a == "0")
					{
						$file="/var/lib/asterisk/sounds/telugu_digits/".$play;
						$agi-> stream_file($file, '#');
						
						//$res_dtmf=$agi->get_data('/var/lib/asterisk/sounds/kims/'.$play, $timeout, 1);
				    	//$packagedetail=$res_dtmf ["result"];
				    	if($n == $play)
				    	{
							$j--; 	
						}
				    	
					}
					else
					{
						$a_division=floor($a/10);
						if($a_division < "2" || $a == "20" || $a == "30" || $a == "40" || $a == "50" || $a == "60" || $a == "70" || $a == "80" || $a == "90" ){
						$file="/var/lib/asterisk/sounds/telugu_digits/".$a;
						$agi-> stream_file($file, '#');
						}else{
						$a_10=$a_division."0";
						$file="/var/lib/asterisk/sounds/telugu_digits/".$a_10;
						$agi-> stream_file($file, '#');
						$a_substract=$a-$a_10;
						$file="/var/lib/asterisk/sounds/telugu_digits/".$a_substract; 
						$agi-> stream_file($file, '#');
						}
						if($a == "1"){
						$file="/var/lib/asterisk/sounds/telugu_digits/".$d;
						}else{
						$file="/var/lib/asterisk/sounds/telugu_digits/".$d."la";
						}
						$agi-> stream_file($file, '#');
						//$res_dtmf=$agi->get_data('/var/lib/asterisk/sounds/kims/'.$a, 50, 1);
				    	//$packagedetail=$res_dtmf ["result"];
						//$res_dtmf=$agi->get_data('/var/lib/asterisk/sounds/kims/'.$d, $timeout, 1);
				    	//$packagedetail=$res_dtmf ["result"];	
					}
						
					
					
					 if($j == '5' || $j == '7' || $j == '9')
					 {
						$n=substr("$n",2,$j-1);
						$j--;	
					 }
					 else
					 {
						$n=substr("$n",1,$j-1);
					 }
					
					
					while(strlen($n) > 0)
					{
					if (substr($n, 0, 1) == '0') 
					{
						//echo "\n";
						$n = substr($n, 1,$j-1) ;
						$j--;
					}
					else
					{
						break;
					}
					}
					//echo "n".$n;
					$z='';				
			}
	}
	
	function play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$action,$project){
	$test_vonumber=$GLOBALS['test_vonumber'];
	$message="Prompting Credit limits ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	fwrite(STDERR,"\n--$caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$action,$project--\n");
	$search_purpose_ig="'Agriculture','Dairy','Income generation Activity','Weavers'";
	$search_purpose_non_ig="'Education','Marriage','health','Emergency Needs'";

	// START 06-08-18 fecthing the credit limits and drawing powers from VO_WISE_CCL_STATIC_TABLE
	//$CreditLimitsQry="select * from VO_WISE_CCL_STATIC_TABLE where TRANS_VO_ID='$void_code'";
	$CreditLimitsQry="select * from dp_calculation_ivrs() where TRANS_VO_ID='$void_code'";
	$vo_actual_credit_rs=mssql_fetch_array(mssql_query($CreditLimitsQry));

	$current_limit_pop_ig=$vo_actual_credit_rs["IGA_POP_DP"];
	$current_limit_pop_non_ig=$vo_actual_credit_rs["CONS_POP_DP"];
	$current_limit_nonpop_ig=$vo_actual_credit_rs["IGA_NONPOP_DP"];
	$current_limit_nonpop_non_ig=$vo_actual_credit_rs["CONS_NONPOP_DP"];

	$vo_credit_pop=$current_limit_pop_ig+$current_limit_pop_non_ig;
	$vo_credit_npop=$current_limit_nonpop_ig+$current_limit_nonpop_non_ig;

	$message="Credit Limits : POP: $vo_credit_pop,NON POP: $vo_credit_npop,POP IG: $current_limit_pop_ig,POP NON IG: $current_limit_pop_non_ig,NON POP IG: $current_limit_nonpop_ig,NON POP NON IG: $current_limit_nonpop_non_ig";
	$this->log_ivr($ivr_call_id,$message);
	// END 06-08-18 fecthing the credit limits and drawing powers from VO_WISE_CCL_STATIC_TABLE
/*
	
	$CreditLimitsQry="select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT,GEN_LOAN_PER from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'";
	$CreditLimitsQry="select A.ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT,GEN_LOAN_PER from VO_CREDIT_LIMIT(nolock) A where A.VO_ID ='$void_code'";
	 $vo_actual_credit_rs=mssql_fetch_array(mssql_query($CreditLimitsQry));
 $vo_actual_credit=$vo_actual_credit_rs['ACTUAL_CREDIT_LIMIT'];
 $vo_credit_pop=$vo_actual_credit_rs['POP_CREDIT_LIMIT'];
 $vo_credit_non_pop=$vo_actual_credit_rs['NONPOP_CREDIT_LIMIT'];
 $vo_gen_loan_per=$vo_actual_credit_rs['GEN_LOAN_PER']; 
 	$message="Fetching credit limits from TCS table VO_CREDIT_LIMIT , ACTUAL_CREDIT_LIMIT: $vo_actual_credit ,POP_CREDIT_LIMIT: $vo_credit_pop ,NONPOP_CREDIT_LIMIT: $vo_credit_non_pop ,GEN_LOAN_PER : $vo_gen_loan_per";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
 $vo_amt_to_add_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE IN ('1','5','53','62','71','72','74','79') and STATUS_ID='11' and PROJECT_TYPE<>'43' and REQUESTED_ID!='201314'"));
       $vo_amt_to=$vo_amt_to_add_rs[0];

 	$message="Rejected Amount SHG_LOAN_APPLICATION (select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE IN ('1','5','53','62','71','72','74','79') and STATUS_ID='11' and PROJECT_TYPE<>'43' and REQUESTED_ID!='201314') , vo_amt_to: $vo_amt_to  ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

		
		$vo_lt_max_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
		$vo_lt_max_tcs=$vo_lt_max_tcs_rs[0];
		
		 	$message="Applied Loan Amount from IVRS_LOAN_REQUEST (select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'  vo_lt_max_tcs :$vo_lt_max_tcs ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$vo_lt_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79')"));
		$vo_lt_live=$vo_lt_live_rs[0];

		 	$message="Applied Loan Amount from IVRS_LOAN_REQUEST_LIVE (select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79')  vo_lt_live :$vo_lt_live ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
	$vo_repaid_total_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_VO from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79')"));
        $vo_repaid_total = $vo_repaid_total_rs['AMT_REPAID_VO'];
		$vo_repaid_total=intval($vo_repaid_total);
		
	$message="Repaid Loan Amount from SN.SHG_MEMBER_REPAY_VIEW (select sum(PPR) as AMT_REPAID_VO from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79'))  vo_repaid_total :$vo_repaid_total ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		
		$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_live-$vo_repaid_total-$vo_amt_to;
		
		
			
	$message="VO outstanding  (vo_lt_max_tcs+vo_lt_live+loan_amount-vo_repaid_total-vo_amt_to)  vo_credit_max_tcs :$vo_credit_max_tcs";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		
		if($vo_credit_max_tcs < 0){
			$vo_credit_max_tcs=0;
			
	$message="VO Repaid AMT is greater than applied amt,resetting the outstanding to 0, vo_credit_max_tcs :$vo_credit_max_tcs";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					
		}
		
		
		
		$vo_current_credit_limit=$vo_actual_credit-$vo_credit_max_tcs;

	$message="VO Drawing power  (vo_actual_credit-vo_credit_max_tcs) vo_current_credit_limit :$vo_current_credit_limit ,vo_actual_credit=$vo_actual_credit,vo_credit_max_tcs=$vo_credit_max_tcs";
	$this->php_log_ivr($ivr_call_id,$message);
		$vo_current_credit_limit = $vo_current_credit_limit * $vo_gen_loan_per;
	$message="VO Drawing power |vo_current_credit_limit * vo_gen_loan_per  ($vo_current_credit_limit * $vo_gen_loan_per)";

	//$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);
	$this->php_log_ivr($ivr_call_id,$message);

if($vo_current_credit_limit <= 0 ){
	
	$vo_credit_pop=0;
	$vo_credit_non_pop=0;
	$current_limit_pop_ig=0;
	$current_limit_pop_non_ig=0;
	$current_limit_nonpop_ig=0;
	$current_limit_nonpop_non_ig=0;
	
}else{
	
	$message="Calculating POP And NON POP Limits ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
//$vo_credit_pop_ig=intval(ceil($vo_credit_pop*0.85));
//	 $vo_credit_pop_nonig=intval(floor($vo_credit_pop*0.15));
//	 $vo_credit_non_pop_ig=intval(ceil($vo_credit_non_pop*0.85));
//	 $vo_credit_non_pop_nonig=intval(floor($vo_credit_non_pop*0.15));
	 
$applied_pop_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
$applied_amt_pop=$applied_pop_rs['AMT'];

	$message="POP Applied AMT (select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79')) and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'  applied_amt_pop:$applied_amt_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);


$applied_rs_live=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='Y' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79')"));
        $applied_amt_pop_live=$applied_rs_live['AMT_LIVE'];
		$applied_amt_pop_live=intval($applied_amt_pop_live );
		
		
	$message="POP Applied LIVE AMT (select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='Y' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79')  applied_amt_pop_live:$applied_amt_pop_live ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	

////pop ig
//		
//$applied_ig_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST  where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_ig)"));
//$applied_amt_pop_ig = $applied_ig_rs['AMT'];		
//
//$applied_rs_live_ig=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE  where shg_id in ($vo_shgs) and IS_POP='Y' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_ig)"));
//        $applied_amt_live_pop_ig=$applied_rs_live_ig['AMT_LIVE'];
//		$applied_amt_live_pop_ig=intval($applied_amt_live_pop_ig);
//		
//
//		
//		
////pop non ig		
//		$applied_non_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST  where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_non_ig)"));
//$applied_amt_pop_nonig = $applied_non_rs['AMT'];		
//
//$applied_rs_live_nonig=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE  where shg_id in ($vo_shgs) and IS_POP='Y' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_non_ig)"));
//        $applied_amt_live_pop_nonig=$applied_rs_live_nonig['AMT_LIVE'];
//		$applied_amt_live_pop_nonig=intval($applied_amt_live_pop_nonig);
		



$vo_rej_amt_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79') and STATUS_ID='11' and REQUESTED_ID!='201314'"));
$vo_rej_amt_pop=$vo_rej_amt_rs['AMT_REJ'];
$vo_rej_amt_pop=intval($vo_rej_amt_pop);


	$message="POP Rejected AMT (select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79') and STATUS_ID='11')   vo_rej_amt_pop:$vo_rej_amt_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

//$vo_rej_amt_pop_ig_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_ig) and STATUS_ID='11' "));
//$vo_rej_amt_pop_ig=$vo_rej_amt_pop_ig_rs['AMT_REJ'];
//$vo_rej_amt_pop_ig=intval($vo_rej_amt_pop_ig);
//
//$vo_rej_amt_pop_nonig_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_non_ig) and STATUS_ID='11'"));
//$vo_rej_amt_pop_nonig=$vo_rej_amt_pop_nonig_rs['AMT_REJ'];
//$vo_rej_amt_pop_nonig=intval($vo_rej_amt_pop_nonig);
		

//non pop------
		

$applied_npop_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
$applied_amt_nonpop=$applied_npop_rs['AMT'];


	$message="NON POP Applied AMT (select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79')) and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' applied_amt_nonpop:$applied_amt_nonpop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);


$applied_rs_live_np=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='N' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79')"));
        $applied_amt_nonpop_live=$applied_rs_live_np['AMT_LIVE'];
		$applied_amt_nonpop_live=intval($applied_amt_nonpop_live);

	$message="NON POP Applied LIVE AMT (select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='N' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79')   applied_amt_nonpop_live:$applied_amt_nonpop_live ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
//
////non pop ig		
//	$applied_ig_nonpop_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST  where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_ig)"));
//$applied_amt_nonpop_ig =$applied_ig_nonpop_rs['AMT'];		
//
//$applied_rs_live_ig_nonpop=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE  where shg_id in ($vo_shgs) and IS_POP='N' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_ig)"));
//        $applied_amt_live_nonpop_ig=$applied_rs_live_ig_nonpop['AMT_LIVE'];
//		$applied_amt_live_nonpop_ig==intval($applied_amt_live_nonpop_ig);
//		
//	
//	
//	//non pop non ig	
//	$applied_nonig_nonpop_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST  where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_non_ig)"));
//$applied_amt_nonpop_nonig =$applied_nonig_nonpop_rs['AMT'];		
//
//$applied_rs_live_nonig_nonpop=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE  where shg_id in ($vo_shgs) and IS_POP='N' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_non_ig)"));
//        $applied_amt_live_nonpop_nonig=$applied_rs_live_nonig_nonpop['AMT_LIVE'];
//		$applied_amt_live_nonpop_nonig==intval($applied_amt_live_nonpop_nonig);	
			
	
	
	
	//rejected amount
	
$vo_rej_amt_nonpop_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79') and STATUS_ID='11' and REQUESTED_ID!='201314'"));
$vo_rej_amt_nonpop=$vo_rej_amt_nonpop_rs['AMT_REJ'];
$vo_rej_amt_nonpop=intval($vo_rej_amt_nonpop);

	$message="NON POP Rejected AMT (select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79') and STATUS_ID='11')   vo_rej_amt_nonpop:$vo_rej_amt_nonpop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
//$vo_rej_amt_nonpop_ig_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_ig) and STATUS_ID='11'"));
//$vo_rej_amt_nonpop_ig=$vo_rej_amt_nonpop_ig_rs['AMT_REJ'];
//$vo_rej_amt_nonpop_ig=intval($vo_rej_amt_nonpop_ig);
//
//$vo_rej_amt_nonpop_nonig_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE='1' and PURPOSE in ($search_purpose_non_ig) and STATUS_ID='11'"));
//$vo_rej_amt_nonpop_nonig=$vo_rej_amt_nonpop_nonig_rs['AMT_REJ'];
//$vo_rej_amt_nonpop_nonig=intval($vo_rej_amt_nonpop_nonig);	
			
$vo_repaid_pop_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_POP from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='0' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79')"));
        $vo_repaid_pop = $vo_repaid_pop_rs['AMT_REPAID_POP'];
		//$vo_repaid_pop_ig= intval(ceil($vo_repaid_pop*0.85));
		//$vo_repaid_pop_nonig= intval(floor($vo_repaid_pop*0.15));
		
	$message="POP Repaid AMT (select sum(PPR) as AMT_REPAID_POP from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='0' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79'))   vo_repaid_pop:$vo_repaid_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);		
		
		
		$vo_repaid_nonpop_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_NONPOP from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='1' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79')"));
        $vo_repaid_nonpop = $vo_repaid_nonpop_rs['AMT_REPAID_NONPOP'];
		//$vo_repaid_nonpop_ig=intval(ceil($vo_repaid_nonpop*0.85));
		//$vo_repaid_nonpop_nonig=intval(floor($vo_repaid_nonpop*0.15));
	$message="NONPOP Repaid AMT (select sum(PPR) as AMT_REPAID_NONPOP from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='1' and PROJECT_TYPE IN ('1','5','53','62','71','72','74','79'))   vo_repaid_nonpop:$vo_repaid_nonpop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	


$vo_applied_pop=$applied_amt_pop+$applied_amt_pop_live-$vo_rej_amt_pop-$vo_repaid_pop;


		
	

	$message="POP Outstanding(applied_amt_pop+applied_amt_pop_live-vo_rej_amt_pop-vo_repaid_pop)  vo_applied_pop:$vo_applied_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);


if($vo_applied_pop < 0){
	$vo_applied_pop=0;
}

//$vo_applied_pop_ig=$applied_amt_pop_ig+$applied_amt_live_pop_ig-$vo_rej_amt_pop_ig-$vo_repaid_pop_ig;
//$vo_applied_pop_nonig=$applied_amt_pop_nonig+$applied_amt_live_pop_nonig-$vo_rej_amt_pop_nonig-$vo_repaid_pop_nonig;	

	
$vo_applied_nonpop=$applied_amt_nonpop+$applied_amt_nonpop_live-$vo_rej_amt_nonpop-$vo_repaid_nonpop;	
	$message="NON POP Outstanding(applied_amt_nonpop+applied_amt_nonpop_live-vo_rej_amt_nonpop-vo_repaid_nonpop)   vo_applied_nonpop:$vo_applied_nonpop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
//$vo_applied_nonpop_ig=$applied_amt_nonpop_ig+$applied_amt_live_nonpop_ig-$vo_rej_amt_nonpop_ig-$vo_repaid_nonpop_ig; 
//$vo_applied_nonpop_nonig=$applied_amt_nonpop_nonig+$applied_amt_live_nonpop_nonig-$vo_rej_amt_nonpop_nonig-$vo_repaid_nonpop_nonig; 

if($vo_applied_nonpop < 0){
	$vo_applied_nonpop=0;
}		
	 

$vo_credit_pop=$vo_credit_pop-$vo_applied_pop;

	$message="POP Drawing Power (vo_credit_pop-vo_applied_pop)  vo_credit_pop:$vo_credit_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
$vo_credit_npop=$vo_credit_non_pop-$vo_applied_nonpop;

	$message="NONPOP Drawing Power (vo_credit_non_pop-vo_applied_nonpop)  vo_credit_npop:$vo_credit_npop";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
//$current_limit_pop_ig=$vo_credit_pop_ig-$vo_applied_pop_ig;
//$current_limit_pop_non_ig=$vo_credit_pop_nonig-$vo_applied_pop_nonig;
//$current_limit_nonpop_ig=$vo_credit_non_pop_ig-$vo_applied_nonpop_ig;
//$current_limit_nonpop_non_ig=$vo_credit_non_pop_nonig-$vo_applied_nonpop_nonig;


if($vo_credit_pop <= 0 && $vo_credit_npop > 0){
		
		$vo_credit_npop=$vo_credit_npop-(-($vo_credit_pop));
		
	$message="vo_credit_pop <= 0 && vo_credit_npop > 0 (vo_credit_npop=vo_credit_npop-(-(vo_credit_pop)))  vo_credit_npop:$vo_credit_npop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);		
		
	}
	
	if($vo_credit_npop <= 0 && $vo_credit_pop > 0){
		
		$vo_credit_pop=$vo_credit_pop-(-($vo_credit_npop));
		
	$message="vo_credit_npop <= 0 && vo_credit_pop > 0 (vo_credit_pop=vo_credit_pop-(-(vo_credit_npop)))   vo_credit_pop:$vo_credit_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);			
		
	}	

		$vo_credit_npop=floor($vo_credit_npop);
		$vo_credit_pop=floor($vo_credit_pop);

$current_limit_pop_ig=intval(ceil($vo_credit_pop*0.7));

$message="POP IG Drawing Power (vo_credit_pop*0.7)    current_limit_pop_ig:$current_limit_pop_ig ";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

$current_limit_pop_non_ig=intval(floor($vo_credit_pop*0.3));

$message="POP NON IG Drawing Power (vo_credit_pop*0.3)    current_limit_pop_non_ig:$current_limit_pop_non_ig ";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

$current_limit_nonpop_ig=intval(ceil($vo_credit_npop*0.7));

$message="NONPOP IG Drawing Power (vo_credit_npop*0.7)    current_limit_nonpop_ig:$current_limit_nonpop_ig ";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

$current_limit_nonpop_non_ig=intval(floor($vo_credit_npop*0.3));

$message="NONPOP NON IG Drawing Power (vo_credit_npop*0.3)    current_limit_nonpop_non_ig:$current_limit_nonpop_non_ig ";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);





}
*/
if($vo_credit_pop>0)
{
	$vo_credit_pop=$vo_credit_pop;
	}else{
		$vo_credit_pop=0;
	}
	
	
	if($vo_credit_npop>0)
{
	$vo_credit_npop=$vo_credit_npop;
	}else{
		$vo_credit_npop=0;
	}
	

	if($current_limit_pop_ig>0)
{
	$current_limit_pop_ig=$current_limit_pop_ig;
	}else{
		$current_limit_pop_ig=0;
	}
	
	
	if($current_limit_pop_non_ig>0) 
{
	$current_limit_pop_non_ig=$current_limit_pop_non_ig;
	}else{
		$current_limit_pop_non_ig=0;
	}
	
	
	if($current_limit_nonpop_ig>0)
{
	$current_limit_nonpop_ig=$current_limit_nonpop_ig;
	}else{
		$current_limit_nonpop_ig=0;
	}
	if($current_limit_nonpop_non_ig>0)
{
	$current_limit_nonpop_non_ig=$current_limit_nonpop_non_ig;
	}else{
		$current_limit_nonpop_non_ig=0;
	}			
	if($action == "play"){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_pop_credit_limit";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($vo_credit_pop,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	     $agi-> stream_file($wfile, '#');
		 
		 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_credit_ig";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($current_limit_pop_ig,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
		$agi-> stream_file($wfile, '#');
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_credit_non_ig";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($current_limit_pop_non_ig,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
		 $agi-> stream_file($wfile, '#');
		 
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_nonpop_credit_limit";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($vo_credit_npop,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	     $agi-> stream_file($wfile, '#');
		 
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_credit_ig";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($current_limit_nonpop_ig,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
		$agi-> stream_file($wfile, '#');
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_credit_non_ig";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($current_limit_nonpop_non_ig,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
		 $agi-> stream_file($wfile, '#');		
}else{
	return $$action;
}	

	}
	
function check_meeting_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs){

	$test_vonumber=$GLOBALS['test_vonumber'];
	//$tday=date('d');
	$mday=date('m');
	$yday=date('Y');
	
	
	if($yday == "2015" && $mday == "03"){
		$allow_loan=1;
	}else{
		
	
	$vo_meeting_date_rs=mssql_fetch_array(mssql_query("select MEETING_DAY_1,MEETING_DAY_2,MEETING_DAY,NEW_MEETING_DAY from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
		$meeting_date=$vo_meeting_date_rs['MEETING_DAY'];
		
		if(strlen($meeting_date) == "1"){
			$meeting_date="0".$meeting_date;
		}

		$edate=date('d');
	    
	    $new_meeting_date=$vo_meeting_date_rs['NEW_MEETING_DAY'];	
	    		
	    if(strlen($new_meeting_date) == "1"){
			$new_meeting_date="0".$new_meeting_date;
		}      
	    
	    $today_full=date('Y-m-d');
	  $full_meeting_date = date('Y')."-".date('m')."-".$meeting_date;
	 $full_new_meeting_date = date('Y')."-".date('m')."-".$new_meeting_date;
	 
 
		//$meeting_day_last_month_full=date('Y-m-d', strtotime($full_meeting_date. ' - 1 months'));
		
		//$meeting_day_last_month_full=date('Y', strtotime($full_meeting_date. ' - 1 months'))."-".date('m', strtotime($full_meeting_date. ' - 1 months'))."-".$meeting_date;
		if(date('m') == "02" && ($meeting_date == "29" || $meeting_date == "30" || $meeting_date == "31")){
			$meeting_day_last_month_full=date('Y')."-01-".$meeting_date;
		}else{
			$meeting_day_last_month_full=date('Y', strtotime($full_meeting_date. ' - 1 months'))."-".date('m', strtotime($full_meeting_date. ' - 1 months'))."-".$meeting_date;
		}
		
		
		$meeting_day_last_month_month=date('m',strtotime($meeting_day_last_month_full));
		$meeting_day_last_month_day=date('d',strtotime($meeting_day_last_month_full));
		
	//	$new_meeting_day_last_month_full=date('Y', strtotime($full_new_meeting_date. ' - 1 months'))."-".date('m', strtotime($full_new_meeting_date. ' - 1 months'))."-".$new_meeting_date;
	
			if(date('m') == "02" && ($new_meeting_date == "29" || $new_meeting_date == "30" || $new_meeting_date == "31")){
			$new_meeting_day_last_month_full=date('Y')."-01-".$new_meeting_date;
		}else{
			$new_meeting_day_last_month_full=date('Y', strtotime($full_new_meeting_date. ' - 1 months'))."-".date('m', strtotime($full_new_meeting_date. ' - 1 months'))."-".$new_meeting_date;
		}
		
		//$new_meeting_day_last_month_full=date('Y-m-d', strtotime($full_new_meeting_date. ' - 1 months'));
		$new_meeting_day_last_month_month=date('m',strtotime($new_meeting_day_last_month_full));
		$new_meeting_day_last_month_day=date('d',strtotime($new_meeting_day_last_month_full));
		
		//$full_meeting_date = date('Y')."-".$meeting_day_last_month_month."-".$meeting_date;
		//$full_new_meeting_date = date('Y')."-".$meeting_day_last_month_month."-".$new_meeting_date;
		
		$meeting_day_1_last_month_full=date('Y-m-d', strtotime($meeting_day_last_month_full. ' + 1 days'));
		$meeting_day_1_last_month_month=date('m',strtotime($meeting_day_1_last_month_full));
		$meeting_day_1_last_month_day=date('d',strtotime($meeting_day_1_last_month_full));
		
		
		$meeting_day_7_last_month_full=date('Y-m-d', strtotime($meeting_day_last_month_full. ' + 7 days'));
		$meeting_day_7_last_month_month=date('m',strtotime($meeting_day_7_last_month_full));
		$meeting_day_7_last_month_day=date('d',strtotime($meeting_day_7_last_month_full));
		
		$new_meeting_day_1_last_month_full=date('Y-m-d', strtotime($new_meeting_day_last_month_full. ' + 1 days'));
		$new_meeting_day_1_last_month_month=date('m',strtotime($new_meeting_day_1_last_month_full));
		$new_meeting_day_1_last_month_day=date('d',strtotime($new_meeting_day_1_last_month_full));
		
		$new_meeting_day_7_last_month_full=date('Y-m-d', strtotime($new_meeting_day_last_month_full. ' + 7 days'));
		$new_meeting_day_7_last_month_month=date('m',strtotime($new_meeting_day_7_last_month_full));
		$new_meeting_day_7_last_month_day=date('d',strtotime($new_meeting_day_7_last_month_full));
	
	    
	    
	    
	    
	    //$full_meeting_date = date('Y')."-".date('m')."-".$meeting_date;
	    $full_meeting_day_month=date('m',strtotime($full_meeting_date));
	    $full_meeting_day_day=date('d',strtotime($full_meeting_date));
				
		$meeting_day_1_full=date('Y-m-d', strtotime($full_meeting_date. ' + 1 days'));
		$meeting_day_1_day=date('d',strtotime($meeting_day_1_full));
		$meeting_day_1_month=date('m',strtotime($meeting_day_1_full));

		$meeting_day_7_full=date('Y-m-d', strtotime($full_meeting_date. ' + 7 days'));
		$meeting_day_7_day=date('d',strtotime($meeting_day_7_full));
		$meeting_day_7_month=date('m',strtotime($meeting_day_7_full));

		//$full_new_meeting_date = date('Y')."-".date('m')."-".$new_meeting_date;
		$full_new_meeting_day_month=date('m',strtotime($full_new_meeting_date));
	    $full_new_meeting_day_day=date('d',strtotime($full_new_meeting_date));
				
		$new_meeting_day_1_full=date('Y-m-d', strtotime($full_new_meeting_date. ' + 1 days'));
		$new_meeting_day_1_day=date('d',strtotime($new_meeting_day_1_full));
		$new_meeting_day_1_month=date('m',strtotime($new_meeting_day_1_full));

		$new_meeting_day_7_full=date('Y-m-d', strtotime($full_new_meeting_date. ' + 7 days'));
		$new_meeting_day_7_day=date('d',strtotime($new_meeting_day_7_full));
		$new_meeting_day_7_month=date('m',strtotime($new_meeting_day_7_full));
	    
	    
				if($caller == "9912390164"){
			
			//echo $full_meeting_date,"\n";
			//echo $full_new_meeting_date,"\n";
		}
		
		
		//$allow_loan=1;
		
		
		if($today_full == $full_meeting_date || $today_full == $full_new_meeting_date || $today_full == $meeting_day_last_month_full || $today_full == $new_meeting_day_last_month_full){
			$allow_loan=1;
		}
		
		
		
		if($allow_loan != 1){
			
			if($today_full == $meeting_day_1_full){
				
				$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION(nolock)  where vo_id='$void_code' and  cast(created_date as date)='$full_meeting_date' and status_id!='11'"));
				
				if($loans_on_meeting_date == 0){
					$allow_loan=1;
				}
				
				
			}
			
			
		}
		
		
		
		if($allow_loan != 1){
			
			if($today_full == $meeting_day_7_full){
				
				//$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION  where vo_id='$void_code' and  cast(created_date as date)='$meeting_day_1_full' and status_id!='11'"));
				
				//if($loans_on_meeting_date == 0){
					$allow_loan=1;
				//}
				
				
			}
			
			
		}
		
		
		if($allow_loan != 1){
			
			if($today_full == $new_meeting_day_1_full){
				
				$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION(nolock)  where vo_id='$void_code' and  cast(created_date as date)='$full_new_meeting_date' and status_id!='11'"));
				
				if($loans_on_meeting_date == 0){
					$allow_loan=1;
				}
				
				
			}
			
			
		}
		
		
		if($allow_loan != 1){
			
			if($today_full == $new_meeting_day_7_full){
				
				//$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION  where vo_id='$void_code' and  cast(created_date as date)='$new_meeting_day_1_full' and status_id!='11'"));
				
				//if($loans_on_meeting_date == 0){
					$allow_loan=1;
				//}
				
				
			}
			
			
		}		
		
		
		
		if($allow_loan != 1){
			
			if($today_full == $meeting_day_1_last_month_full){
				
				$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION(nolock)  where vo_id='$void_code' and  cast(created_date as date)='$meeting_day_last_month_full' and status_id!='11'"));
				
				if($loans_on_meeting_date == 0){
					$allow_loan=1;
				}
				
				
			}
			
			
		}
		
		if($allow_loan != 1){
			
			if($today_full == $meeting_day_7_last_month_full){
				
				//$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION  where vo_id='$void_code' and  cast(created_date as date)='$meeting_day_1_last_month_full' and status_id!='11'"));
				
				//if($loans_on_meeting_date == 0){
					$allow_loan=1;
				//}
				
				
			}
			
			
		}		
		
		

		
		if($allow_loan != 1){
			
			if($today_full == $new_meeting_day_1_last_month_full){
				
				$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION(nolock)  where vo_id='$void_code' and  cast(created_date as date)='$new_meeting_day_last_month_full' and status_id!='11'"));
				
				if($loans_on_meeting_date == 0){
					$allow_loan=1;
				}
				
				
			}
			
			
		}	
		
		
		if($allow_loan != 1){
			
			if($today_full == $new_meeting_day_7_last_month_full){
				
				//$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION  where vo_id='$void_code' and  cast(created_date as date)='$new_meeting_day_1_last_month_full' and status_id!='11'"));
				
				//if($loans_on_meeting_date == 0){
					$allow_loan=1;
				//}
				
				
			}
			
			
		}	


	    
	     
	        
	        
	     if($allow_loan != 1){   
		
		$check_vo_access_override=mssql_num_rows(mssql_query("select replace(convert(varchar, CREATED_DATE, 111),'/','-') from VO_LOAN_ACCESS(nolock) where replace(convert(varchar, CREATED_DATE, 111),'/','-')<=replace(convert(varchar,getdate() , 111),'/','-') and replace(convert(varchar, END_DATE, 111),'/','-')>=replace(convert(varchar,getdate() , 111),'/','-')  and vo_id='$void_code'"));
		if($check_vo_access_override > '0'){
			$allow_loan=1;			
			}
		}
		
		if($allow_loan == 1)
		{
			
		}else{
			
		$allow_loan=0;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_meeting_day_is_on";
		$agi-> stream_file($wfile, '#');
		
		if($meeting_date != ""){
		
			
		if($today_full < $full_meeting_date)	{
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$full_meeting_day_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($full_meeting_day_day,0,1) == "0"){
			$full_meeting_day_day=substr($full_meeting_day_day,1,1);
		}
		
		$this-> play_amount($full_meeting_day_day,$agi);
		sleep(1);
		}
		
		if($today_full < $meeting_day_1_full){
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_1_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_1_day,0,1) == "0"){
			$meeting_day_1_day=substr($meeting_day_1_day,1,1);
		}
		$this-> play_amount($meeting_day_1_day,$agi);
		sleep(1);
		}
		
		if($today_full < $meeting_day_7_full){
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_7_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_7_day,0,1) == "0"){
			$meeting_day_7_day=substr($meeting_day_7_day,1,1);
		}
		
		$this-> play_amount($meeting_day_7_day,$agi);
		sleep(1);
		}
		
		
		if($today_full < $meeting_day_last_month_full)	{
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_last_month_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($full_meeting_day_day,0,1) == "0"){
			$meeting_day_last_month_day=substr($meeting_day_last_month_day,1,1);
		}
		
		$this-> play_amount($meeting_day_last_month_day,$agi);
		sleep(1);
		}
		
		
		if($today_full < $meeting_day_1_last_month_full)	{
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_1_last_month_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_1_last_month_day,0,1) == "0"){
			$meeting_day_1_last_month_day=substr($meeting_day_1_last_month_day,1,1);
		}
		
		$this-> play_amount($meeting_day_1_last_month_day,$agi);
		sleep(1);
		}
		
	if($today_full < $meeting_day_7_last_month_full)	{
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_7_last_month_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_7_last_month_day,0,1) == "0"){
			$meeting_day_7_last_month_day=substr($meeting_day_7_last_month_day,1,1);
		}
		
		$this-> play_amount($meeting_day_7_last_month_day,$agi);
		sleep(1);
		}
		

		
		
		
		
		}
		
		
		if($new_meeting_date != ""){
			
		sleep(1);	
		
		if($today_full < $full_new_meeting_date){
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$full_new_meeting_day_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($full_new_meeting_day_day,0,1) == "0"){
			$full_new_meeting_day_day=substr($full_new_meeting_day_day,1,1);
		}
		
		$this-> play_amount($full_new_meeting_day_day,$agi);
		sleep(1);
		}
		
		if($today_full < $new_meeting_day_1_full){
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_1_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_1_day,0,1) == "0"){
			$new_meeting_day_1_day=substr($new_meeting_day_1_day,1,1);
		}
		
		$this-> play_amount($new_meeting_day_1_day,$agi);
		sleep(1);
		}
		
		if($today_full < $new_meeting_day_7_full){
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_7_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_7_day,0,1) == "0"){
			$new_meeting_day_7_day=substr($new_meeting_day_7_day,1,1);
		}
		
		$this-> play_amount($new_meeting_day_7_day,$agi);
		
		}
		
				//new meeting last month
		if($today_full < $new_meeting_day_last_month_full)	{
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_last_month_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_last_month_day,0,1) == "0"){
			$new_meeting_day_last_month_day=substr($new_meeting_day_last_month_day,1,1);
		}
		
		$this-> play_amount($new_meeting_day_last_month_day,$agi);
		sleep(1);
		}
		
		
		if($today_full < $new_meeting_day_1_last_month_full)	{
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_1_last_month_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_1_last_month_day,0,1) == "0"){
			$new_meeting_day_1_last_month_day=substr($new_meeting_day_1_last_month_day,1,1);
		}
		
		$this-> play_amount($new_meeting_day_1_last_month_day,$agi);
		sleep(1);
		}
		
	if($today_full < $new_meeting_day_7_last_month_full)	{
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_7_last_month_month;
		$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_7_last_month_day,0,1) == "0"){
			$new_meeting_day_7_last_month_day=substr($new_meeting_day_7_last_month_day,1,1);
		}
		
		$this-> play_amount($new_meeting_day_7_last_month_day,$agi);
		sleep(1);
		}
		
		
		}
		
		
		
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_only_on_meeting_day";
		$agi-> stream_file($wfile, '#');
		}
	}
	return $allow_loan;
	}	
	
	function check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs){
		$test_vonumber=$GLOBALS['test_vonumber'];
		$vo_samrudhi_validation=0;
		$vo_samrudhi=mssql_fetch_array(mssql_query("select vo_id from samrudhi_percentage() where samrudhi_percentage>=50 and VO_ID='$void_code'"));
		$vs_vo_id=$vo_samrudhi['vo_id'];
	
//		//if($vs_percentage>='80')	
//		if($vs_percentage>='50')

		if($vs_vo_id == $void_code)
			{
			$vo_samrudhi_validation=1;
			}
		else
		{
		
				
			if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_80";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->vo_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }	
				
				
		
			
		}
		return $vo_samrudhi_validation;
	}
	
	function check_shg_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code){
	$test_vonumber=$GLOBALS['test_vonumber'];	
	$shg_samrudhi_query="select * from SHG_SAMRUDHI_PERCENTAGE() where shg_id = '$shg_code'";
$total_deposit_rs=mssql_fetch_array(mssql_query($shg_samrudhi_query));
$check_shg_samrudhi=$total_deposit_rs['DEPOSIT_PER'];
			
		if($check_shg_samrudhi > 99)
		{
		$message="SHG SAMRUDHI SUCCESS";
                $this->log_ivr($ivr_call_id,$message);
		$shg_samrudhi_validation=1;
		}
		else{
		$shg_samrudhi_validation=0;	
			
		
			
			
//			if($x>='1')
//				{
//				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_denied";
//				$agi-> stream_file($wfile, '#');
//				$x=$x-1;
//				$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
//		mssql_close($link);
//				$agi->Hangup();
//				exit;
//
//				}else
//				{
//				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
//				$agi-> stream_file($wfile, '#');
//		mssql_close($link);
//				$agi->Hangup();
//				exit;
//				}
			}
			return $shg_samrudhi_validation;

	}	
	
function check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code){
			$test_vonumber=$GLOBALS['test_vonumber'];
			$override=0;
			$recovery_count=mssql_num_rows(mssql_query("select  * from SAMSNBSTG.SN.VO_RECOVERY_STATUS(nolock) where vo_id='$void_code' "));
			if($recovery_count >= 1){
			$message=" select  \* from SAMSNBSTG.SN.VO_RECOVERY_STATUS(nolock) where vo_id='$void_code' ";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
			$cif=mssql_fetch_array(mssql_query("select  * from SAMSNBSTG.SN.VO_RECOVERY_STATUS(nolock) where vo_id='$void_code' "));
			$cif_recovery=$cif['RECOVERY'];
			if($cif_recovery == 0){
				$cif_DMD=$cif['DMD'];
				if($cif_DMD == 0){
					$cif_recovery=100;
				}
			}
			
			}else{
				$override=1;
			}
			
			
			if($override == 1){
//Allowing vo if Record not found in VRSTATUS By Ashok
				
		/*$cif_count_res=mssql_fetch_array(mssql_query("select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where shg_id in ($vo_shgs) and IS_CLOSED=0"));
		$cif_count=$cif_count_res[0];
		$message="select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where shg_id in ($vo_shgs) and IS_CLOSED=0";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
			if($cif_count == 0){
				$cif_recovery=100;
				$message="recovery is set to 100";
				$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
			}*/

				$message="Record Not Found in SAMSNBSTG.SN.VO_RECOVERY_STATUS(nolock) where vo_id='$void_code'";
				$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

				$cif_recovery=100;
				
			}
			
			
			
			$message=" RECOVERY for $void_code: RECOVERY: $cif_recovery, DMD: $cif_DMD , cif_count:$cif_count, recovery_count:$recovery_count";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		return $cif_recovery;
		}
	
function log_ivr($ivr_call_id,$messgae){
	$test_vonumber=$GLOBALS['test_vonumber'];
	$ivr_log_loan_cmd="/bin/echo ".$messgae." >> /var/log/ivrs/".$ivr_call_id.".log";
	exec($ivr_log_loan_cmd);
		
		
	}
	
	
	function corpus_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs)
	{
	$test_vonumber=$GLOBALS['test_vonumber'];	 
	      if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  
	  if($shg_code_rs=='00')
	  {
		 $link = mysql_connect("localhost","root", "evol@9944")
    or die("Data base connection failed");
   mysql_select_db("shtri_nidhi")
    or die("data base open failed");
	$file=$this->record_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$record_confirm,$record_propmt,$language); 
//echo "insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())";
	if($caller!=$test_vonumber)
	{
	mysql_query("insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())");
	}
	
	mysql_close($link);
		mssql_close($link);
		 $agi->Hangup();
		  exit;
		  }
		  
		  
	  
	 
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  
	 $message="User Enetered SHG SHORT CODE  : $shg_code_short , SHG NAME : $shg_name ,SHG_ID: $shg_code";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	  
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	
	//STARTING CHECKING SHG OVER DUE By Ashok
	$triggered_loan_count_res=mssql_fetch_array(mssql_query("select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'"));
		$triggered_loan_count=$triggered_loan_count_res[0];
		$message="Triggered Loans: select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

			$curr_odos=0;
		
			if($triggered_loan_count > 0)
			{
				$shg_overdue_res=mssql_fetch_array(mssql_query("select OVERDUE,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
				$shg_ovrdue=$shg_overdue_res[0];
				if($shg_ovrdue>0)
				{
				   $message="SHG HAS OVERDUE AMT $shg_ovrdue is gretaer than 0 in SAMSNBSTG.SN.SHG_OVERDUE_STATUS,system checks shg OD AND OS In corpus loan";
				   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

				   $curr_shg_odos=0;
				   $curr_shg_odos=$shg_overdue_res[1]+$shg_overdue_res[2];
				      if($curr_shg_odos<=100)
				      {
					$curr_odos=$curr_shg_odos;
				  	$message="SHG HAS CURRENT_OD,CURRENT_OS:$curr_odos Less than 100, so for this shg we are allowing corpus loan. ";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
				      }
				     else
				      {
					if($x>='1')
					{
			
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_sn_overdue";
						$agi-> stream_file($wfile, '#');
						$this->play_amount($shg_ovrdue,$agi);
						$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    					$agi-> stream_file($wfile, '#');
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
						$agi-> stream_file($wfile, '#');
						$x=$x-1;
						$this->corpus_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
						mssql_close($link);
						$agi->Hangup();
		  				exit;
		   			}else
		   			{
		    				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    				$agi-> stream_file($wfile, '#');
						mssql_close($link);
		   				$agi->Hangup();
		   				exit;
		   			}
				     }
				}

			}
			
	//ENDING CHECKING SHG OVER DUE By Ashok
	
	
	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	$bank_name_rs= mssql_fetch_array(mssql_query("select Bank_Code from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code'"));
	$bank_name=$bank_name_rs['Bank_Code'];


	 $message="SHG OVERDUE AMT: $overdue_amt  , BANK NAME : $bank_name";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);


	if($overdue_amt>0 && $overdue_amt<=10000)
	{
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
		$agi-> stream_file($wfile, '#');
		$is_over_due='Y';	
		
	$message="SHG OVERDUE AMT: $overdue_amt is greates than 0 and lessthan or equal to 10000";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
	}
	 
	
	  if($overdue_amt>10000)
	{	
		
	$message="SHG OVERDUE AMT: $overdue_amt is greates than  10000 ,re-enter SHG CODE";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	if($x>='1')
		{
			
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
		$agi-> stream_file($wfile, '#');
		/*
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/proceed_through_option8";
		$agi-> stream_file($wfile, '#');
		*/
		$x=$x-1;
		$this->corpus_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	}	
	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  
		  
	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	

	
	if($list == $path)
  {
  	
  		$message="SHG AUIDO FILE : $path , AUDIO FILE EXISTS";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
  	
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	  	
	$message="SHG AUIDO FILE : $path , AUDIO FILE DOES NOT EXIST";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	  	
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  
		  }
		if($status=="1")
		{
			$status_pop_fund=2;
			/*
			$pop_stat_rs=mssql_query("select TRANS_VO_ID from vo_info where TRANS_VO_ID='$void_code' and IS_POP='Y'");
			$stat_pop=mssql_num_rows($pop_stat_rs);
			$pop_stat_shg_rs=mssql_query("select TRANS_SHG_ID from SHG_INFO where TRANS_SHG_ID='$shg_code' and IS_POP='Y'");
			$pop_stat_shg=mssql_num_rows($pop_stat_shg_rs);
		if($stat_pop>=1 && $pop_stat_shg>=1)
   		{
 		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_for_popfund", 8000, 1);
	    $status_pop_fund=$res_dtmf ["result"];
			}else
			{
				$status_pop_fund=2;
			}
			*/
			
			if($status_pop_fund=='1')
			{
				
				//$x=3;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/stop_pop_loan";
		$agi-> stream_file($wfile, '#');
		//$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
		mssql_close($link);
			$agi->Hangup();
			exit;	
			}
			elseif($status_pop_fund=='2')
			{
				//TRANS_VO_ID
	$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE,CIF_RECOVERY_PERCENTAGE from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
    $vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	
				
	$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
    $shg_active_stat=$shg_active_rs['IS_VALID'];
	if($shg_active_stat=='Y')
	{
	$message="SHG SB ACCOUNT STATUS(select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') : $shg_active_stat , VALID";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	}
	else
	{
		$message="SHG SB ACCOUNT STATUS(select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') : $shg_active_stat , INVALID";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied_old";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->corpus_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         
	
	}
	
//$cif_recovery=$vo_active_rs['CIF_RECOVERY_PERCENTAGE'];

//$total_deposit_rs=mssql_fetch_array(mssql_query("select sum(DEPOSITED_AMOUNT) as TOTAL_SUM from SHG_DEPOSIT_INFO where SHG_ID='$shg_code' and DEPOSIT_TYPE='1'"));

$check_shg_samrudhi=$this->check_shg_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);


					if($check_shg_samrudhi == 0)
					{
					$message="SHG SAMRUDHI IS PENDING ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	

	  				 if($x>='1')
					{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_denied";
						$agi-> stream_file($wfile, '#');
						 $x=$x-1;
						$this->corpus_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
						$agi->Hangup();
		 				 exit;
		
					   }else
					   {
					    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
					    $agi-> stream_file($wfile, '#');
		mssql_close($link);
					   $agi->Hangup();
					   exit;
					   }
	}else{
	$message="SHG SAMRUDHI PASSED ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	}






	$shg_grade_dist_code=substr($void_code,2,2);
	$shg_grade_mandal_code=substr($void_code,4,2);
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT(nolock) where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
	$mms_grade=$vo_grade_rs['GRADE'];
	
	
    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select A.GRADE,A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs_rej['GRADE'];
	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
	
	
	$message="VO GRADE: $vo_grade ,Mandal GRADE: $mms_grade,ACTUAL_CREDIT_LIMIT: $vo_actual_credit  ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	
	if($vo_grade=='E')
	{
		$message="VO GRADE: $vo_grade ,Rejected ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($vo_grade=='F')
	{
				$message="VO GRADE: $vo_grade ,Rejected ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
/*	if($vo_actual_credit=='0')
	{
	$message="VO ACTUAL_CREDIT_LIMIT($vo_actual_credit) Less than Zero ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	
	}
*/


	

		
		
		
///play credit limits before shg code 		


$search_purpose_ig="'Agriculture','Dairy','Income generation Activity','Weavers'";
$search_purpose_non_ig="'Education','Marriage','health','Emergency Needs'";
	
	
	

///play credit limits before SHG CODE				
				
		$x=3;
		//current
	 $msquery="select VO_ID from IVRS_LOAN_REQUEST(nolock) where   SHG_ID='$shg_code' and PROJECT_TYPE='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";	
	  $shg_mem_rs=mssql_query($msquery);
	  $mem_pending=mssql_num_rows($shg_mem_rs);	
	  
	  	$message="SHG APPLIED LOANS($msquery) mem_pending:$mem_pending ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	  
	  $msquery="select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where   SHG_ID='$shg_code' and PROJECT_TYPE='53' ";
	  $shg_mem_rs_live=mssql_query($msquery);
	  $mem_pending_live=mssql_num_rows($shg_mem_rs_live);
	  
	  	  	$message="SHG LIVE APPLIED LOANS($msquery) mem_pending_live:$mem_pending_live ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	  
	  $msquery="select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where   SHG_ID='$shg_code' and PROJECT_TYPE='53'  and IVRS_ID='$ivr_call_id'";
	  $shg_mem_rs_live_current=mssql_query($msquery);
	  $mem_pending_live_current=mssql_num_rows($shg_mem_rs_live_current);
	  
	$message="SHG LIVE APPLIED LOANS CURRENT($msquery) mem_pending_live_current:$mem_pending_live_current ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 
	  
	  $mem_pending=$mem_pending+$mem_pending_live_current;
	  
	  	  
	$message="SHG LOANS OUTSTANDING(mem_pending+mem_pending_live_current) mem_pending:$mem_pending ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 
	  
	  
	  
	   $msquery="select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE='53' and  SHG_ID='$shg_code'";
	 $shg_mem_rej_rs=mssql_query($msquery);
$mem_rejected=mssql_num_rows($shg_mem_rej_rs);

	$message="SHG REJECTED LOANS -  $msquery mem_rejected:$mem_rejected ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 

	  $msquery="select smlsn.VO_ID FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) smlsn,SHG_LOAN_APPLICATION(nolock) sla WHERE smlsn.SHG_ID='$shg_code' and smlsn.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type='53' and smlsn.IS_CLOSED='1'";
	  $repaid_loans_rs=mssql_query($msquery);
	  $repaid_loans_members=mssql_num_rows($repaid_loans_rs);

	$message="SHG REPAID LOANS -  $msquery repaid_loans_members:$repaid_loans_members ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 
		
		
	$member_limit=2-($mem_pending-$mem_rejected-$repaid_loans_members);
	  
	   	  	  
	$message="SHG LOANS AVAILABLE (mem_pending-mem_rejected-repaid_loans_members) member_limit:$member_limit ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 
	   
	    
		if($member_limit>=1)
		  {
		  
		  $message="SHG LOANS AVAILABLE GRATER THAN 1 :$member_limit ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 	
		  	
		$amt_stat='Y';
	$this->corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$curr_odos) ;
	       }
		   else
		   {
		  
	$message="SHG LOANS AVAILABLE LESS THAN 1 :$member_limit ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 	 	
		   	
		  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_2_loans", 5000, 1);
	      $more_res=$res_dtmf ["result"];
		  if($more_res==1)
		  {
		  $x=3;
		  $this->corpus_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		  $agi->Hangup();
		    }
			else
			{
		mssql_close($link);
			 $agi->Hangup();
			 $agi->Hangup();
			 exit;
			}
		   
		   }	
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->corpus_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	     
		}
	
		}
	
		if($status=="2")
		{
		$this->corpus_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->corpus_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->corpus_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
	}
	
	
			function corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$curr_odos) 	
{
	$test_vonumber=$GLOBALS['test_vonumber'];
     	$db_filed="category";
		$type='mem_mode';
		$length='5';

		$play_msg="mem_category";
		$x='3';
 $member_type=$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
	
	
			  
	$message="MEMBER TYPE :$member_type ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 
	
	$length='2';
	$play_msg='two_digit_member_id';
	$type='corpus_loan';
	$x='3';
	$db_filed=$member_type;
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
	
		$message="MEMBER SHORT CODE ENTERED :$member_short_code ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 
	
	$msquery="select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'";
$member_id_rs=mssql_fetch_array(mssql_query($msquery));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
 

		$message="MEMBER DETAILS($msquery)  member_id: $member_id,member_mobile_num:$member_mobile_num ,member_age:$member_age";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 

  if(($member_age =="" || $member_age=="NULL")){
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_not_update";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$curr_odos) ;
	mssql_close($link);
	$agi->Hangup();
	exit;
  }
 elseif($member_age >= 63 || $member_age <= 18)
 {
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_btw_1860";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$curr_odos) ;
	mssql_close($link);
	$agi->Hangup();
	exit;
 }

//Ashok Changed for Corpus Loan Allowance Start
/*
	$msquery="select VO_ID from IVRS_LOAN_REQUEST(nolock) where  SHG_ID='$shg_code' and ( MEMBER_ID='$member_id'  or SHORT_CODE='$member_short_code') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' ";
	$member_prev_loan_count = mssql_num_rows(mssql_query($msquery));
	
	$message="MEMBER APPLIED LOANS -  $msquery member_prev_loan_count:$member_prev_loan_count ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 
	
	$msquery="select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id' ";
	$member_prev_loan_count_live = mssql_num_rows(mssql_query($msquery));
	
		$message="MEMBER APPLIED LOANS LIVE -  $msquery member_prev_loan_count_live:$member_prev_loan_count_live ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message); 

$msquery="select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11'";
$member_rej_cnt_lng = mssql_num_rows(mssql_query($msquery));

		$message="MEMBER REJECTED LOANS -  $msquery member_rej_cnt_lng:$member_rej_cnt_lng ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

$msquery="select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED='1'";
$member_repaid_loans = mssql_num_rows(mssql_query($msquery));

		$message="MEMBER REPAID LOANS -  $msquery member_repaid_loans:$member_repaid_loans ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
*/

	$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
	
	$member_prev_loan_count_tcs = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and STATUS_ID!='99'"));
	
	$member_prev_loan_count_tcs_pending = mssql_num_rows(mssql_query("select VO_ID from VO_REQUEST_MESSAGES(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and IS_PROCESSED='N'"));
	
	$member_prev_loan_count_live = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id'"));

	$member_rej_cnt_lng = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and REQUESTED_ID!='201314' "));

	$member_rej_cnt_lng_tcs = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' "));

	$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));

	$member_other_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));


	$message="VO_ID='$void_code',SHG_ID='$shg_code',MEMBER_ID='$member_id',SHORT_CODE='$member_short_code':member_prev_loan_count=$member_prev_loan_count,member_prev_loan_count_tcs:$member_prev_loan_count_tcs,member_prev_loan_count_live=$member_prev_loan_count_live,member_rej_cnt_lng=$member_rej_cnt_lng,member_rej_cnt_lng_tcs=$member_rej_cnt_lng_tcs,member_repaid_loans=$member_repaid_loans,member_other_repaid_loans=$member_other_repaid_loans";

	$this->php_log_ivr($ivr_call_id,$message);

if($member_repaid_loans == 1){
	$msquery="select INST_NO from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code'";
		 $member_installments_rs=mssql_fetch_array(mssql_query($msquery));
 $member_installments=$member_installments_rs['INST_NO'];
 
// 		$message="MEMBER INSTALLMENT CNT  -  $msquery member_installments:$member_installments ";
//	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
// 
// if($member_installments < "20"){
// 	$member_repaid_loans=0;
// 	$message="MEMBER INSTALLMENT CNT  < 20 Resetting repaid LOANS to 0 ";
//	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
// }
 
	}
		if($member_prev_loan_count == 0 ){
		 $member_repaid_loans=0;
		}


	//$member_prev_loan_count=$member_prev_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans;

        $member_prev_loan_count=$member_prev_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans;

        $member_prev_loan_count_tcs=$member_prev_loan_count_tcs+$member_prev_loan_count_tcs_pending-$member_rej_cnt_lng_tcs+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans;

        $message="member_prev_loan_count=member_prev_loan_count-member_rej_cnt_lng+member_prev_loan_count_live-member_repaid_loans-member_other_repaid_loans:$member_prev_loan_count=$member_prev_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans";
	$this->php_log_ivr($ivr_call_id,$message);

	$message="member_prev_loan_count_tcs=member_prev_loan_count_tcs+member_prev_loan_count_tcs_pending-member_rej_cnt_lng_tcs+member_prev_loan_count_live-member_repaid_loans-member_other_repaid_loans:$member_prev_loan_count_tcs=$member_prev_loan_count_tcs+$member_prev_loan_count_tcs_pending-$member_rej_cnt_lng_tcs+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans";
	$this->php_log_ivr($ivr_call_id,$message);


	$message="AT Corpus: IVRS: ".$member_prev_loan_count." TCS:  ".$member_prev_loan_count_tcs;	
	$this->php_log_ivr($ivr_call_id,$message);
	if($member_prev_loan_count < $member_prev_loan_count_tcs){
		$message="Member loans less than TCS ".$member_prev_loan_count." less than ".$member_prev_loan_count_tcs;		
		$member_prev_loan_count=$member_prev_loan_count_tcs;
		$this->php_log_ivr($ivr_call_id,$message);
	}

//Ashok Changed for Corpus Loan Allowance End
	
	$message="MEMBER OUTSTANDING LOANS  (member_prev_loan_count-member_rej_cnt_lng+member_prev_loan_count_live-member_repaid_loans) member_prev_loan_count:$member_prev_loan_count ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	
	if($member_prev_loan_count >= 1){
		$msquery="select VO_ID from IVRS_LOAN_REQUEST(nolock) where  SHG_ID='$shg_code' and MEMBER_ID='$member_id' and project_type!='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
			$member_other_loans_applied=mssql_num_rows(mssql_query($msquery));
			
	 		$message="MEMBER OTHER LOANS APPLIED  -  $msquery member_other_loans_applied:$member_other_loans_applied ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);		
			
	
	$msquery="select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id' and project_type!='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
	$member_other_loans_applied_live=mssql_num_rows(mssql_query($msquery));
		 		
	$message="MEMBER OTHER LOANS APPLIED LIVE -  $msquery member_other_loans_applied_live:$member_other_loans_applied_live ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	
	$msquery="select VO_ID from shg_loan_application(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and project_type!='53' and STATUS_ID='11' ";
	$member_other_loans_rejected=mssql_num_rows(mssql_query($msquery));
	
	$message="MEMBER OTHER LOANS REJECTED -  $msquery member_other_loans_rejected:$member_other_loans_rejected ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	
	$member_other_loans_repaid=0;
	
	$message="MEMBER OTHER LOANS REPAID -  $msquery member_other_loans_repaid:$member_other_loans_repaid ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	//$member_other_loans_repaid=mssql_num_rows(mssql_query("select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED='1' "));
	
	$member_other_loans=$member_other_loans_applied+$member_other_loans_applied_live-$member_other_loans_rejected-$member_other_loans_repaid;
	
		$message="MEMBER OTHER LOANS OUTSTANDING (member_other_loans_applied+member_other_loans_applied_live-member_other_loans_rejected-member_other_loans_repaid) member_other_loans:$member_other_loans ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	

	
	if($member_other_loans == 1){
		$msquery="select OUTSTANDING from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code'";
		$member_outstanding_rs=mssql_fetch_array(mssql_query($msquery));
 $member_outstanding=$member_outstanding_rs['OUTSTANDING'];
 
 			$message="MEMBER OUTSTANDING  -  $msquery member_outstanding:$member_outstanding ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
 if($member_outstanding < 10000 ){
 	$member_other_loans=0;
 	
 	$message="MEMBER OUTSTANDING  Less than 10000 ,NOT eligible for CORPUS LOAN: $member_outstanding ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
 	
 }
	}		
	
	$message="MEMBER LOANS B4 OUTSTANDING ($member_prev_loan_count+$member_other_loans) ,member_prev_loan_count:$member_prev_loan_count ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

	
	$member_prev_loan_count=$member_prev_loan_count+$member_other_loans;	
	
	 	$message="MEMBER LOANS OUTSTANDING (member_prev_loan_count+member_other_loans) ,member_prev_loan_count:$member_prev_loan_count ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	
	}
	
	
	
//	if($member_prev_loan_count < 0 ){
//	$member_prev_loan_count=0;
//	}
	
//	if($member_prev_loan_count>=1 || $member_repaid_loans>=1)	
	if($member_prev_loan_count != 0 )
	{
		
	
	$message="MEMBER ALREADY HAVE OUTSTANDING LOAN,NOT ELIGIBLE FOR LOAN (member_prev_loan_count:$member_prev_loan_count,member_repaid_loans:$member_repaid_loans) : loan_already_applied";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
		
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$curr_odos) ;
		mssql_close($link);
	$agi->Hangup();
	exit;	
	}	

	if(strlen($member_mobile_num) == 10){
		$message="MEMBER MOBILE NUMBER PRESENT: $member_mobile_num";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	}else{
    $length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
	$message="MEMBER MOBILE NUMBER ENTERED: $member_mobile";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	$message="MEMBER MOBILE NUMBER UPDATED : $member_mobile";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	}
	else
	{
	
	$message="MEMBER MOBILE NUMBER NOT UPDATED : $member_mobile , SHG_MEMBER_INFO entries with the enetered mobile num: $mobile_count";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
	if($x>='1')
		{
	 $x=$x-1;
	 $this->corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$curr_odos) ;
		mssql_close($link);
	 $agi->Hangup();
	exit;
	 }else{
		mssql_close($link);
	$agi->Hangup();
	exit;		
	}
	
	}
	}
	
    if($amt_stat=='Y')
	{
		$db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
	 
	 	$message="MEMEBR REQUIRED LOAN AMT : $loan_amount";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	 
	 }
	 
	 $msquery="select ALLOCATED_AMOUNT from sc_member_credit_limit(nolock) where member_id='$member_id' and shg_id='$shg_code'";
	  $member_allocated_amt_rs=mssql_fetch_array(mssql_query($msquery));
$member_allocated_amt=$member_allocated_amt_rs['ALLOCATED_AMOUNT'];



	 	$message="ALLOCATED AMOUNT FOR MEMBER - ".$msquery." , member_allocated_amt:".$member_allocated_amt;
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	 
	 if($member_allocated_amt < $loan_amount){
	 	
	$message="ALLOCATED AMOUNT LESS THAN LOAN AMT , $member_allocated_amt < $loan_amount : member_credit_limit EXCEEDED ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	 	
	 	 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
		 $agi-> stream_file($wfile, '#');
		 if($x>='1')
	{
		
	 $x=$x-1;
	 $this->corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$curr_odos) ;
		mssql_close($link);
	 $agi->Hangup();
	exit;
	 }else{
		mssql_close($link);
	$agi->Hangup();
	exit;		
	}
	 }
	 
	 
	 
	 if($loan_amount>='1' && $member_allocated_amt >= $loan_amount )
	 {
		 
  //echo "came hetereerr";




$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/IG-Activity";
$agi-> stream_file($wfile, '#');

$reason_loan='Income generation Activity';


$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/krushe_amount_type", 8000, 1);
$krushe_req_amount=$res_dtmf ["result"];

		 	$message="COnfirmation for the applied loan USER ENTERED:$krushe_req_amount";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

if($krushe_req_amount=='1' )
{
$message="CONFIRMATION PASSED:$krushe_req_amount";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
//$loan_amount=$KRUSHE_AMOUNT;	
}else{
	$message="CONFIRMATION FAILED:$krushe_req_amount";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	if($x>='1')
	{
		
	 $x=$x-1;
	 $this->corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$curr_odos) ;
		mssql_close($link);
	 $agi->Hangup();
	exit;
	 }else{
		mssql_close($link);
	$agi->Hangup();
	exit;		
	}
}

  
  
   }
 
 if(($reason_loan=='Income generation Activity')&&($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && $member_prev_loan_count=='0' && strlen($member_id)>1))
 {
 
 	$message="VALIDATING THE LOAN DETAILS  PASSED: reason_loan:$reason_loan,member_type:$member_type,member_short_code:$member_short_code,$member_prev_loan_count:member_prev_loan_count,member_id:$member_id";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
 	
 	
 	
 $duration='24';
 $etime=date('Y-m-d H:i:s');
 
 if( $loan_amount>25000 )
{

		$message="LOAN AMT Greter Than 25000, FAILED:$loan_amount";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_less_25000";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$curr_odos) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
}else{
	
		$message="LOAN AMT Less Than 25000, PASSED:$loan_amount";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
}
 
 

 /*
 if(($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Emergency Needs/Health')&&$loan_amount>15000)
{
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_less_25000";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
}
*/

 //echo "insert into IVRS_LOAN_REQUEST(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,MMS_LOAN_ACC_NO,VO_LOAN_ACCNO,SHG_LOAN_ACC_NO,LOAN_SANCTIONED_DATE,LOAN_SANCTIONED_AMOUNT,LOAN_STATUS) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan', '$member_type','$unique_id','0','0','0','','0','open')";
 
 
 //echo "insert into IVRS_LOAN_REQUEST(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id')";
 
 $msquery="select ALLOCATED_AMOUNT from sc_vo_credit_limit(nolock) where VO_ID='$void_code'";
 $vo_actual_credit_rs=mssql_fetch_array(mssql_query($msquery));
 $vo_actual_credit=$vo_actual_credit_rs['ALLOCATED_AMOUNT'];
 
 
 	$message="ACTUAL CREDIT LIMT-".$msquery.", vo_actual_credit:".$vo_actual_credit;
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
 
 		$msquery="select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' ";
	$applied_rs=mssql_fetch_array(mssql_query($msquery)); 
	//removed and PURPOSE in ($search_purpose)
	
        $applied_amt = $applied_rs['AMT'];
		
			$message="APPLIED AMT -".$msquery.", applied_amt:".$applied_amt;
			$message=str_replace('(','^',$message);
			$message=str_replace(')','^',$message);
	$this->log_ivr($ivr_call_id,$message);
		
		
		$msquery="select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs)  and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
		$applied_rs_live=mssql_fetch_array(mssql_query($msquery));
        $applied_amt_live = $applied_rs_live['AMT_LIVE'];
		$applied_amt_live=intval($applied_amt_live);

			$message="APPILED AMT LIVE-".$msquery.", applied_amt_live:".$applied_amt_live;
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	//commented for automation		
		//$vo_rej_amt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) as AMT_REJ from IVRS_VO_REINITIATED_CREDITS where VO_ID='$void_code' and IS_POP='$member_type' and PURPOSE in ($search_purpose) "));
		
		$msquery="select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs)  and PROJECT_TYPE='53'  and STATUS_ID='11'";
		$vo_rej_amt_rs=mssql_fetch_array(mssql_query($msquery));		
$vo_rej_amt=$vo_rej_amt_rs['AMT_REJ'];
$vo_rej_amt=intval($vo_rej_amt);

			$message="REJECTED AMT -".$msquery."-, vo_rej_amt:".$vo_rej_amt;
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);


$applied_total=$applied_amt+$applied_amt_live-$vo_rej_amt;

			$message="AMT OUTSTANDING (applied_amt+applied_amt_live-vo_rej_amt), applied_total:$applied_total";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);


//commented for automation
/*
   $vo_fixed_credit_rs=mssql_fetch_array(mssql_query("select $search_cr_limit from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
       $vo_fixed_credit = $vo_fixed_credit_rs[$search_cr_limit];
	   */
	   $msquery="select sum(PPR) as AMT_REPAID from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE='53'";
	   $vo_repaid_cat_rs=mssql_fetch_array(mssql_query($msquery));
        $repaid_cat_total = $vo_repaid_cat_rs['AMT_REPAID'];
        			
     $message="REPAID AMT -".$msquery.", repaid_cat_total:".$repaid_cat_total;
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		//added for automation
		
		
		
		$vo_cat_limit=$vo_actual_credit-$applied_total+$repaid_cat_total;
		
					$message="VO DRAWING POWER (vo_actual_credit-applied_total+repaid_cat_total), vo_cat_limit:$vo_cat_limit";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		$vo_credit_lt=$vo_cat_limit;
			
		
	//$vo_credit_lt=$vo_fixed_credit-$applied_total+$repaid_cat_total;	
	
		
		//commented for automataion
		/*
		if($applied_total>$vo_fixed_credit && $loan_amount>$vo_credit_lt)
		{
			$extra_applied_amt=$applied_total-$vo_fixed_credit;
			$vo_credit_lt=$repaid_cat_total-$extra_applied_amt;
		}
		if($loan_amount<$vo_credit_lt)
		{
			
			$vo_credit_lt=$vo_credit_lt;
		}
		else
		{
			$vo_credit_lt=$repaid_cat_total+$vo_credit_lt;
		}
		*/	
		$msquery="select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
	$shg_lt_max_rs=mssql_fetch_array(mssql_query($msquery));
		$shg_lt_max=$shg_lt_max_rs[0];
		
		     $message="SHG APPLIED AMT -  $msquery, shg_lt_max:$shg_lt_max";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
	//commented for automation	
			//$shg_rejected_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_VO_REINITIATED_CREDITS where VO_ID='$void_code' and SHG_ID='$shg_code'"));
			$msquery="select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where  SHG_ID='$shg_code'  and PROJECT_TYPE='53' and STATUS_ID='11'";
$shg_rejected_live_rs=mssql_fetch_array(mssql_query($msquery));
			
$shg_live_rejected_amt=$shg_rejected_live_rs[0];

		     $message="SHG REJECTED AMT -  $msquery, shg_live_rejected_amt:$shg_live_rejected_amt";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

//$shg_repaid_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW where SHG_ID='$shg_code' and IS_CLOSED='1'"));
//$shg_repaid=$shg_repaid_rs[0];
$shg_repaid=0;

		     $message="SHG REPAID AMT (), shg_repaid:$shg_repaid";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);


$applied_actual=$shg_lt_max-$shg_live_rejected_amt-$shg_repaid;




if($applied_actual>50000)
{
	$amt_live_to_add=$applied_actual-50000;
	$amt_live_to_deduct=$shg_live_rejected_amt-$amt_live_to_add;
}
else
{
	$amt_live_to_deduct=$shg_live_rejected_amt+$shg_repaid;
}
	
		$shg_lt_max=$shg_lt_max+$loan_amount-$amt_live_to_deduct;
		
		
		
		
		//shg_amount
		$msquery="select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
		$shg_lt_max_tcs_rs=mssql_fetch_array(mssql_query($msquery));
		$shg_lt_max_tcs=$shg_lt_max_tcs_rs[0];
		
		$msquery="select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='53'";
		$shg_lt_live_rs=mssql_fetch_array(mssql_query($msquery));
		$shg_lt_live=$shg_lt_live_rs[0];
		
	//commented for automation	
//$shg_rejected_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_VO_REINITIATED_CREDITS where VO_ID='$void_code' and SHG_ID='$shg_code' "));
//$shg_rejected_amt=$shg_rejected_rs[0];

$shg_rejected_amt=$shg_live_rejected_amt;
$applied_actual_tcs=$shg_lt_max_tcs+$shg_lt_live-$shg_rejected_amt-$shg_repaid;


		     $message="SHG OUTSTANDING AMT (shg_lt_max_tcs+shg_lt_live-shg_rejected_amt-shg_repaid), applied_actual_tcs:$applied_actual_tcs";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);


if($applied_actual_tcs>50000)
{
	$amt_to_add=$applied_actual_tcs-50000;
	$amt_to_deduct=$shg_rejected_amt-$amt_to_add;
}
else
{
	$amt_to_deduct=$shg_rejected_amt+$shg_repaid;
}
	
		
		$shg_limit_max_tcs=$shg_lt_max_tcs+$shg_lt_live+$loan_amount-$amt_to_deduct;
		
		//vo_amount
//$vo_total_credit_rs=mssql_fetch_array(mssql_query("select  credit_limit_pop+credit_limit_nonpop from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        //$vo_total_credit=$vo_total_credit_rs[0];
        $msquery="select ALLOCATED_AMOUNT from sc_vo_credit_limit(nolock) where VO_ID='$void_code'";
         $vo_actual_credit_rs=mssql_fetch_array(mssql_query($msquery));
 $vo_total_credit=$vo_actual_credit_rs['ALLOCATED_AMOUNT'];
	
	 
			//commented for automation
	   //$vo_amt_to_add_rs=mssql_fetch_array(mssql_query("select Amount_ADD from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
	   
	   $msquery="select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE='53' and STATUS_ID='11'";
	    $vo_amt_to_add_rs=mssql_fetch_array(mssql_query($msquery));
       $vo_amt_to=$vo_amt_to_add_rs[0];
	   $vo_total_credit=$vo_total_credit+$vo_amt_to;
	   
		$msquery="select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
		$vo_lt_max_tcs_rs=mssql_fetch_array(mssql_query($msquery));
		$vo_lt_max_tcs=$vo_lt_max_tcs_rs[0];
		
		$msquery="select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='53'";
		$vo_lt_live_rs=mssql_fetch_array(mssql_query($msquery));
		$vo_lt_live=$vo_lt_live_rs[0];
		
		$msquery="select sum(PPR) as AMT_REPAID_VO from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE='53'";
	$vo_repaid_total_rs=mssql_fetch_array(mssql_query($msquery));
        $vo_repaid_total = $vo_repaid_total_rs['AMT_REPAID_VO'];
		$vo_repaid_total=intval($vo_repaid_total);
		
		$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_live+$loan_amount-$vo_repaid_total;

	$mms_dist_code=substr($void_code,2,2);
      $mms_mandal_code=substr($void_code,4,2);
	
		
			  
  //if($vo_credit_lt>=$loan_amount && $shg_lt_max<=150000 && $shg_limit_max_tcs<=150000 && $vo_credit_max_tcs<=$vo_total_credit && $mms_credit_max_tcs<=$mms_total_credit )
  
  //mms credit limit removed 
   if($vo_credit_lt>=$loan_amount && $shg_lt_max<=50000 && $shg_limit_max_tcs<=50000 && $vo_credit_max_tcs<=$vo_total_credit )
  { 
 $message="VALIDATING LOAN AMOUNT (vo_credit_lt:$vo_credit_lt >= loan_amount:$loan_amount,shg_lt_max:$shg_lt_max <= 50000,shg_limit_max_tcs:$shg_limit_max_tcs <= 50000 ,vo_credit_max_tcs:$vo_credit_max_tcs <= vo_total_credit:$vo_total_credit) : SUCCESS";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
 //exit;
 //$echo_cmd="/bin/echo ivr_id $ivr_call_id uni $unique_id  credit_limit $vo_credit_lt >> /tmp/ivr.txt";
 //exec($echo_cmd);
 
 
 $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select * from IVRS_LOAN_REQUEST_LIVE(nolock) where  SHG_ID='$shg_code' and PROJECT_TYPE='1' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;

//Ashok Adding Current od and os to this member Start
		
				if($curr_odos!=0)
				{
					$shg_pending_ods_count=mssql_num_rows(mssql_query("select TOP 1 1 from SHG_MEMBER_PENDING_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
					if($shg_pending_ods_count>=1)
					{
					  $curr_odos=0;
				   	  $message="Already Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS for shg $shg_code";
				   	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					}
				}

//Ashok Adding Current od and os to this member End
	 
 
	$PROJECT_TYPE='53';  

	$this->chk_ln_status($shg_code,$member_id,$PROJECT_TYPE,$ivr_call_id);

	$loan_amount=$loan_amount+$curr_odos;

 $loanRequestINsertQry="insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos')";	



if($caller!=$test_vonumber)
{
mssql_query($loanRequestINsertQry);

//Ashok Adding Current od and os to this member Start
		
				if($curr_odos!=0)
				{
					$shg_pending_ods_count=mssql_num_rows(mssql_query("select TOP 1 1 from SHG_MEMBER_PENDING_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
					if($shg_pending_ods_count>=1)
					{
					  $curr_odos=0;
				   	  $message="Already Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS for shg $shg_code";
				   	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					}
					else
					{
					  $shg_mem_ods_res=mssql_query("select SHG_ID,MEMBER_ID,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code' and CURRENT_OVERDUE>0");
					  $rec_count=0;
					  while($shg_mem_ods_row=mssql_fetch_array($shg_mem_ods_res))
					   {
						if($caller!=$test_vonumber)
						{

						   $pres_odos=$shg_mem_ods_row['CURRENT_OVERDUE']+$shg_mem_ods_row['CURRENT_OUTSTANDING'];
						   if($pres_odos<=100)
				      		    {
						     $shg_pending="INSERT INTO SHG_MEMBER_PENDING_OVERDUE_STATUS(SHG_ID,MEMBER_ID,CREATED_DATE,CURRENT_OVERDUE,CURRENT_OUTSTANDING,INSERTED_BY) VALUES ('".$shg_mem_ods_row['SHG_ID']."','".$shg_mem_ods_row['MEMBER_ID']."',GETDATE(),'".$shg_mem_ods_row['CURRENT_OVERDUE']."','".$shg_mem_ods_row['CURRENT_OUTSTANDING']."','IVRS')";			
						     
						      mssql_query($shg_pending);

						      $message="SHG HAS CURRENT_OD,CURRENT_OS as pres_odos :$pres_odos Less than 100, so for this shg we are inserting into at Corpus $shg_pending. ";
				   	              $this->php_log_ivr($ivr_call_id,$message);
						    }
						}
						$rec_count++;
					   }
					$message="Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS from SAMSNBSTG.SN.SHG_OVERDUE_STATUS where shg $shg_code : rec_count:$rec_count :$shg_pending";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);					
					}
				}

//Ashok Adding Current od and os to this member End


}
$curr_odos=0;

        $message="INSERTING Corpus LOAN REQUEST INTO IVR_LOAN_REQUEST_LIVE ($loanRequestINsertQry)";
        $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

/*
  $vo_credit_rs2=mssql_fetch_array(mssql_query("select $tbl_filed from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        $vo_credit_lt_prev = $vo_credit_rs2[$tbl_filed];
		$vo_credit_lt_prev=intval($vo_credit_lt_prev);
		
		if($vo_credit_lt_prev<$loan_amount)
		{
			$used_from_repay=$loan_amount-$vo_credit_lt_prev;
			$used_from_ivr=$loan_amount-$used_from_repay;
			$loan_amount=$used_from_ivr;
			$current_limit=$vo_credit_lt_prev-$used_from_ivr;
		}else
		{
		$current_limit=$vo_credit_lt-$loan_amount;
		}
		*/
$current_limit=$vo_credit_lt-$loan_amount;
//$current_limit=$vo_credit_lt-$loan_amount;
if($caller!=$test_vonumber)
{
mssql_query("update  IVRS_VO_CREDIT_LIMIT set $tbl_filed='$current_limit'  where vo_id='$void_code'");
}

if($member_type=='Y')
     {
	$pop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_pop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$pop_lmt=$pop_lmt_rs['current_limit_pop'];
	
		 if($pop_lmt>=$loan_amount)
		 {
	 if($caller!=$test_vonumber)
	 {
	 mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_pop=current_limit_pop-$loan_amount  where vo_id='$void_code'");
	 }
		 }
	 }
	 if($member_type=='N')
		{
			
	$nonpop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_nonpop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$nonpop_lmt=$nonpop_lmt_rs['current_limit_nonpop'];
	
	if($nonpop_lmt>=$loan_amount)
	{
		if($caller!=$test_vonumber)
		{
		mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_nonpop=current_limit_nonpop-$loan_amount  where vo_id='$void_code'");
		}
	}
	   }
	   
	   
		
 //echo  $shg_log_id.$reason_loan.$member_type;
 //$unique_id
 //$ivr_call_id
 
 
		/* 
   $txt=$shg_name."(".$member_cat."):". $loan_amount."/-";
   $demo="Your Loan request for ".$txt." is successful.
Thank you . --Sthri Nidhi";
	$finalmessage=$demo;
	$sms_url="http://www.9nodes.com/API/sendsms.php?username=praveenkumar9944@gmail.com&password=praveen&from=VCode&to=".$caller."&msg=".urlencode($finalmessage)."&type=1";
$h=fopen($sms_url,"r");
fclose($h);
*/

//mysql_query("update vo_info set credit_limit=credit_limit-$shg_amount where vo_id='$void_code'")
//$vo_credit_limit=16000;
$vo_credit_limit=$current_limit;

 $member_limit=$member_limit-1; 
   if($member_limit>=1 && $vo_credit_limit>=0)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{
	$amt_stat='Y';
	$this->corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$curr_odos) ;
	}
	else
	{
	  if($vo_credit_limit>=0)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{
			
	$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}
 }
 else
 {
 $message="VALIDATING LOAN AMOUNT (vo_credit_lt:$vo_credit_lt >= loan_amount:$loan_amount,shg_lt_max:$shg_lt_max <= 50000,shg_limit_max_tcs:$shg_limit_max_tcs <= 50000 ,vo_credit_max_tcs:$vo_credit_max_tcs <= vo_total_credit:$vo_total_credit) : FAILED";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
if($mms_credit_max_tcs>$mms_total_credit)
	 {
		$mms_dist_code=substr($void_code,2,2);
        $mms_mandal_code=substr($void_code,4,2); 
		$share = mssql_fetch_array(mssql_query("SELECT  SHARE_AMOUNT FROM SN.SHARE_BAL_FUN() where DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code' "));
		$share_amount=$share['SHARE_AMOUNT'];
		if($share_amount<1000000)
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/share_captial";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($share_amount,$agi);
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/paid_share_captial";
		$agi-> stream_file($wfile, '#');	
		}
		else
		{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
		}
	 }
	 else
	 {
    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
	 }

 //$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	
	$this->corpus_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$curr_odos) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
 
   }
	

 //echo  $loan_amount.$reason_loan.$member_type;
 
		}
		else
		  {
		  
		   	$message="VALIDATING THE LOAN DETAILS  FAILED: reason_loan:$reason_loan,member_type:$member_type,member_short_code:$member_short_code,$member_prev_loan_count:member_prev_loan_count,member_id:$member_id";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		 $this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		$agi->Hangup();
		    }
		


}


function project_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs)
	{
	$test_vonumber=$GLOBALS['test_vonumber'];		

	  //$member_limit=6;
	 
		 
	      if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}

	
	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	$bank_name_rs= mssql_fetch_array(mssql_query("select Bank_Code from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code'"));
	$bank_name=$bank_name_rs['Bank_Code'];	

	if($overdue_amt>0 && $overdue_amt<=10000)
	{
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
		$agi-> stream_file($wfile, '#');
		$is_over_due='Y';	
	}
	 
	
	  if($overdue_amt>10000)
	{	
	if($x>='1')
		{
			
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
		$agi-> stream_file($wfile, '#');
		/*
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/proceed_through_option8";
		$agi-> stream_file($wfile, '#');
		*/
		$x=$x-1;
		$this->project_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	}

    $shg_grade_dist_code=substr($void_code,2,2);
	$shg_grade_mandal_code=substr($void_code,4,2);
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT(nolock) where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
	$mms_grade=$vo_grade_rs['GRADE'];
	
	if($mms_grade=='E')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_egrade";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($mms_grade=='F')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	//change table here
    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select GRADE,CREDIT_LIMIT from PROJECT_VO_CREDIT_LIMIT(nolock) where VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs_rej['GRADE'];
	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
	
		if($vo_grade=='E')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	
	if($vo_grade=='F')
	{
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}
	if($vo_actual_credit=='0')
	{
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	
		}	
	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  
	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  }
		if($status=="1")
		{	
		
			$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
    $shg_active_stat=$shg_active_rs['IS_VALID'];
	if($shg_active_stat=='Y')
	{
	
	}
	else
	{
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied_old";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		   $this->project_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         
	
	}
	
$vo_name_array=mssql_query("select * from vo_info(nolock) where TRANS_VO_ID='$void_code' order by CREATED_DATE  desc");
$vo_name_array=mssql_fetch_array($vo_name_array);
$dist_id=$vo_name_array['DISTRICT_ID'];
$mandal_id=$vo_name_array['MANDAL_ID'];
	
$check_shg_samrudhi=$this->check_shg_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);

					if($check_shg_samrudhi == 0)
					{

	  				 if($x>='1')
					{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_denied";
						$agi-> stream_file($wfile, '#');
						 $x=$x-1;
						$this->project_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
						$agi->Hangup();
		 				 exit;
		
					   }else
					   {
					    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
					    $agi-> stream_file($wfile, '#');
		mssql_close($link);
					   $agi->Hangup();
					   exit;
					   }
	}		
		
	//$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE from vo_info where TRANS_VO_ID='$void_code'"));
    //$vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	if( $shg_active_stat=='Y')
	{


//current type

$shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where   SHG_ID='$shg_code' and PROJECT_TYPE in ('54')");
$mem_pending_live=mssql_num_rows($shg_mem_rs_live);
	  
$shg_mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('54') and  SHG_ID='$shg_code'");
$mem_rejected=mssql_num_rows($shg_mem_rej_rs);

$shg_mem_repaid_rs=mssql_query("select * from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where SHG_ID='$shg_code' and IS_CLOSED='1'");
$mem_repaid=mssql_num_rows($shg_mem_repaid_rs);


//other types
	  $shg_mem_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where   SHG_ID='$shg_code' and PROJECT_TYPE not in  ('54','53') and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'");
	  $mem_pending=mssql_num_rows($shg_mem_rs);	

	  $shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE not in ('54','53')");
	  $mem_pending_live_sn=mssql_num_rows($shg_mem_rs_live);
	  
	 $mem_rej_others=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE not in  ('54','53') and  SHG_ID='$shg_code'");
	  $mem_rejected_others=mssql_num_rows($mem_rej_others); 
	  
	   $mem_pending=$mem_pending-$mem_rejected_others;
	   $mem_pending_live_sn=$mem_pending_live_sn-$mem_rejected_others;
	   
//total
$mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and  SHG_ID='$shg_code' and PROJECT_TYPE!='53'");
$mem_rejected_tot=mssql_num_rows($mem_rej_rs);

	  $total_rs=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where  VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE!='53'");
	  $total_members=mssql_num_rows($total_rs);
	  
	  if($mem_pending_live>=0 && ($mem_pending==0||$mem_pending_live_sn==0))
	  {	 
	  $member_limit=9-$mem_pending_live+$mem_rejected+$mem_repaid; 
	  }
	  else
	  {
		$member_limit=9-$mem_pending_live+$mem_rejected+$mem_repaid;
		$total_remain=9-$total_members+$mem_rejected_tot+$mem_repaid; 
		
		 if($total_remain<=$member_limit)
	   {
		  $member_limit=$total_remain;
	   }else
	   {
		 $member_limit=$member_limit;  
	   }
	   
	  }		
	  
	  
//	  $member_limit=6-$mem_pending_live+$mem_rejected+$mem_repaid;
//		$total_remain=9-$total_members+$mem_rejected_tot+$mem_repaid; 
//		
//		 if($total_remain<=$member_limit)
//	   {
//		  $member_limit=$total_remain;
//	   }else
//	   {
//		 $member_limit=$member_limit;  
//	   }
		
/*
	  $x=3;
	$shg_mem_rs_live=mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE where  VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE in ('8','9')");
	  $mem_pending_live=mssql_num_rows($shg_mem_rs_live);
	  	  
$shg_mem_rej_rs=mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('8','9') and VO_ID='$void_code' and  SHG_ID='$shg_code'");
	  $mem_rejected=mssql_num_rows($shg_mem_rej_rs);	 
	  $member_limit=6-$mem_pending_live+$mem_rejected;   
	  */
	 	
	if($member_limit>=1)
	{	
	$this->project_request_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	 $agi->Hangup();
	exit;
	}
	else{
		  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_6_loans", 5000, 1);
	      $more_res=$res_dtmf ["result"];
		  if($more_res==1)
		  {
		  $x=3;
		  $this->project_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		    }
			else
			{
		mssql_close($link);
			 $agi->Hangup();
			 $agi->Hangup();
			 exit;
			}
		   
		   	
	}
	
 		
	
	
			
			}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->project_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	     
		}
	
		}
	
		if($status=="2")
		{
		$this->project_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->project_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->project_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
		
	

	}
	
	function project_request_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs)
{
	$test_vonumber=$GLOBALS['test_vonumber'];
	$length='2';
	$play_msg='two_digit_member_id';
	$type='project_others';
	$x='3';
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,ACTIVITY,KRUSHE_AMOUNT,SP_ACTIVITY,MOBILE_NUM,IS_POP_MEM from SHG_MEMBER_INFO(nolock)  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $ACTIVITY=$member_id_rs['ACTIVITY'];
 $KRUSHE_AMOUNT=$member_id_rs['KRUSHE_AMOUNT'];
  $member_mobile_num=$member_id_rs['MOBILE_NUM'];
    $member_type=$member_id_rs['IS_POP_MEM'];
 
 
 if($ACTIVITY=='54' )
 {
	 //change prompt
//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/project_loan_reason_others";
//$agi-> stream_file($wfile, '#');
 }
 else
 {
	 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/not_eligible_for_project_loan";
	$agi-> stream_file($wfile, '#');
	$this->project_request_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;	 
 }
 
 	$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and PROJECT_TYPE='54'"));
	
	if($member_prev_loan_count>=1)
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$this->project_request_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;	
	}
  
    	if(strlen($member_mobile_num) == 10){
		
	}else{
	$length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
		mssql_close($link);
	$agi->Hangup();	
	exit;	
	}
	
	}
	
  
$db_filed="reason";
		$type='project';
		$length='5';
		//$play_msg="member_loan_reason_new";
		$play_msg="project_loan_reason_others";
		$x='3';
//$reason_loan =$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);	
	
$reason_loan="Income generation Activity";
	
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member";
$agi-> stream_file($wfile, '#');

$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/petti_business_kosam";
$agi-> stream_file($wfile, '#');

$this->play_amount($KRUSHE_AMOUNT,$agi);
$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
$agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/applying_loan";
$agi-> stream_file($wfile, '#');

$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/krushe_amount_type", 8000, 1);
$krushe_req_amount=$res_dtmf ["result"];
if($krushe_req_amount=='1')
{
$loan_amount=$KRUSHE_AMOUNT;	
}
//elseif($krushe_req_amount=='2')
//{
//$loan_request='YES';	
//}
else{
		if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		$x=$x-1;
		$this->project_request_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }	
				
			}	
	
	
	//$vo_id_mandal=substr($void_code,0,6);
	

	if($loan_request=='YES')
	{
  $db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->krushe_loan_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$KRUSHE_AMOUNT);
	}
		
	

		
 
 //$hlp_loan_rs=mssql_fetch_array(mssql_query("SELECT ACTIVITY_NAME FROM  KRUSHE_ACTIVITY_MASTER where ACTIVITY_ID='$reason_loan_code'"));
 //$reason_loan=$hlp_loan_rs['ACTIVITY_NAME'];	

	


//KRUSHE_AMOUNT
 //echo $member_short_code."prev --".$member_prev_loan_count."loan--".$loan_amount."krushe".$KRUSHE_AMOUNT;
 if(strlen($member_short_code)==2 && $member_prev_loan_count=='0' && $loan_amount<=$KRUSHE_AMOUNT && $loan_amount>0 && strlen($reason_loan)!=0)
 {
	 
	 //reason master data
    //$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select CURRENT_CREDIT_LIMIT from IVRS_VO_POP_CREDIT_LIMIT  where vo_id='$void_code'"));
    //$vo_credit_lt_pop = $vo_credit_pop_rs['CURRENT_CREDIT_LIMIT'];
/*	
if($krushe_type=='MART')
{
$PROJECT_TYPE='$ACTIVITY';	
}
if($krushe_type=='PRODUCER')
{
$PROJECT_TYPE='$ACTIVITY';
}
*/


$PROJECT_TYPE=$ACTIVITY;

	
//	$vo_credit_pop_rs=mssql_fetch_array(mssql_query("select CREDIT_LIMIT from PROJECT_BASED_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
//    $vo_actual_credit_lt_krushe = $vo_credit_pop_rs['CREDIT_LIMIT'];
//	
////$vo_krushe_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code'  and PROJECT_TYPE in ('23')"));
//$vo_krushe_applied_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where shg_id in ($vo_shgs)  and PROJECT_TYPE in ('43')"));
//
//		$vo_krushe_applied=$vo_krushe_applied_rs[0];
//		//$vo_krushe_applied=$vo_krushe_applied+$loan_amount;
//		
////adding rejected amount
//		
//	
//$rej_id=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('43') and shg_id in ($vo_shgs)"));
//$rej_amt_t2=$rej_id[0];	
//	
//	$vo_krushe_applied=$vo_krushe_applied-$rej_amt_t2;
//	
//	$vo_credit_lt_krushe=$vo_actual_credit_lt_krushe-$vo_krushe_applied;
//	
//	
//$rej_shg=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where STATUS_ID='11' and PROJECT_TYPE in ('43')  and SHG_ID='$shg_code'"));
//$rej_amt_shg_krushe=$rej_shg[0];	
//	 
//$shg_krushe_lt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('43')"));
//
//		$shg_krushe_lt_max=$shg_krushe_lt_rs[0];
//		$shg_krushe_lt_max=$shg_krushe_lt_max+$loan_amount-$rej_amt_shg_krushe;
//			
//$shg_krushe_lt_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE in ('43')"));
//
//		$shg_krushe_lt_tcs=$shg_krushe_lt_tcs_rs[0];
//		$shg_krushe_lt_tcs=$shg_krushe_lt_tcs+$loan_amount-$rej_amt_shg_krushe;
//			
// $duration='36';
// $etime=date('Y-m-d H:i:s');	
//// $PROJECT_TYPE='2';	  
// $member_type='N';
// //&& $vo_credit_lt_sn>=$fund_sn
// 
// $shg_krushe_actual_lt=mssql_fetch_array(mssql_query("select SUM(KRUSHE_AMOUNT) from  SHG_MEMBER_INFO  where SHG_ID='$shg_code'"));
// $shg_krushe_lt=$shg_krushe_actual_lt[0];
// 
 // if($loan_amount>=1000 && $loan_amount<=$KRUSHE_AMOUNT && $shg_krushe_lt_max<=$shg_krushe_lt && $vo_credit_lt_krushe>=$loan_amount  && $shg_krushe_lt_tcs<=$shg_krushe_lt)
  
  
 // $duration='36';
 $etime=date('Y-m-d H:i:s');
  
  if($loan_amount>=1000 && $loan_amount<=$KRUSHE_AMOUNT )
  {   
 //$marriage_amt='0';
  $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select SHG_IVRS_LOAN_NO from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and  SHG_ID='$shg_code' and PROJECT_TYPE in ('54') order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;
 // $reason_loan_code has to be added
 
if($caller!=$test_vonumber)
{ 
mssql_query("insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,COMBINED_ID,HLP_CODE,IS_OVERDUE,OVERDUE_ID,SHORT_CODE,MEMBER_ID,FUND_SN,FUND_POP) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$ca_id_new','$reason_loan_code','$is_over_due','$due_id_lst','$member_short_code','$member_id','$fund_sn','$fund_pop')");
}

$current_limit=$vo_credit_lt_krushe-$loan_amount;
//mssql_query("update IVRS_VO_POP_CREDIT_LIMIT set CURRENT_CREDIT_LIMIT='$current_limit'  where vo_id='$void_code'");
$vo_credit_limit=$current_limit;


  $member_limit=$member_limit-1; 
   if($member_limit>=1 && $vo_credit_limit>=1000)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan_project", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{  
	$this->project_request_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
	}
	else
	{
	  if($vo_credit_limit>=1000)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{	
	$this->project_request_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$krushe_type,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}


/*
 $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
  if(intval($value)=='1')
	{
  $this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
	}
	else
	{
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	if(intval($value_shg)=='1')
	{
	$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
	$agi->Hangup();
		mssql_close($link);
	exit;
	}
	
	}
	*/
	   

 }
 else
 {
	   if($x>='1')
		{
			if($loan_amount>50000)		
			{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_50000";
	      $agi-> stream_file($wfile, '#');	
			}
			else
			{
 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
			}
			$x=$x-1;
	$this->project_request_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 
   }
	
 }
 else
 {
	    if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
			$x=$x-1;
	$this->project_request_loan_others($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst,$krushe_type,$vo_shgs);
		mssql_close($link);
	$agi->Hangup();
	exit;
		} else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	         }
 }	
}

function shg_member_limit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs){

                $test_vonumber=$GLOBALS['test_vonumber'];
                $message="Calculating outstanding loans of SHG in project $project  ";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

$shg_oustanding_project_rslt = mssql_query("SELECT COUNT(*) count FROM (
SELECT DISTINCT MEMBER_ID MEMBER_LONG_CODE  FROM EVGEN.IVRS_LOAN_REQUEST (NOLOCK) WHERE SHG_ID ='$shg_code'
AND PROJECT_TYPE IN($project) AND IS_PROCESSED='N'    UNION
SELECT DISTINCT MEMBER_LONG_CODE FROM VO_REQUEST_MESSAGES (NOLOCK) WHERE SHG_ID ='$shg_code'
AND PROJECT_TYPE IN($project) AND IS_PROCESSED='N'    UNION
SELECT DISTINCT MEMBER_ID MEMBER_LONG_CODE  FROM EVGEN.IVRS_LOAN_REQUEST_LIVE (NOLOCK) WHERE SHG_ID ='$shg_code'
AND PROJECT_TYPE IN($project) AND IVRS_ID='$ivr_call_id'   UNION
SELECT DISTINCT MEMBER_LONG_CODE   FROM SHG_MEMBER_MCP_INFO (NOLOCK) WHERE SHG_ID ='$shg_code'
AND PROJECT_TYPE IN($project) AND LOAN_STATUS ='OPEN' UNION
SELECT DISTINCT MEMBER_LONG_CODE  FROM SHG_LOAN_APPLICATION (NOLOCK) WHERE SHG_ID ='$shg_code'
AND PROJECT_TYPE IN($project) AND STATUS_ID NOT IN(5,11)) A");
$shg_oustanding_project_array = mssql_fetch_array($shg_oustanding_project_rslt);
                $message="shg_oustanding_project: $shg_oustanding_project_array[count]";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		if($project=='72')
                $shg_limit_project=$shg_max_loans_total-$shg_oustanding_project_array[count];
		else
                $shg_limit_project=$shg_max_loans_ivrs-$shg_oustanding_project_array[count];
                $shg_allowed_loans = $shg_limit_project ;

                if($shg_allowed_loans > 0 ){
                $message="SUCCESS: shg_allowed_loans: $shg_allowed_loans Greater than 0 AND TCS_shg_allowed_loans:$TCS_shg_allowed_loans Greater than 0";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
                return $shg_allowed_loans;
                }else{
                $message="FAIL: shg_allowed_loans: $shg_allowed_loans Less than 0 OR TCS_shg_allowed_loans:$TCS_shg_allowed_loans Less than 0";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
                return 0;
                }
}


	
function shg_member_limit_old($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs){
		
		
		$test_vonumber=$GLOBALS['test_vonumber'];
		$message="Calculating outstanding loans of SHG in project $project  ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$shg_applied_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from IVRS_LOAN_REQUEST(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
		$shg_applied_project=$shg_applied_project_rs[0];
		
		$shg_applied_project_live_rs=mssql_fetch_array(mssql_query("select count(vo_id) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and IVRS_ID='$ivr_call_id'"));
		$shg_applied_project_live=$shg_applied_project_live_rs[0];
		
		$shg_rejected_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and STATUS_ID='11'"));
		$shg_rejected_project=$shg_rejected_project_rs[0];
		
		$shg_repaid_project_rs=mssql_fetch_array(mssql_query("select count(smlsn.VO_ID) FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) smlsn,SHG_LOAN_APPLICATION(nolock) sla WHERE smlsn.SHG_ID='$shg_code' and smlsn.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type='$project' and smlsn.IS_CLOSED='1'"));
		$shg_repaid_project=$shg_repaid_project_rs[0];
		
		$shg_oustanding_project=$shg_applied_project+$shg_applied_project_live-$shg_rejected_project-$shg_repaid_project+$shg_applied_loans_tcs;
		
		$message="shg_oustanding_project: $shg_oustanding_project = shg_applied_project:$shg_applied_project + shg_applied_project_live:$shg_applied_project_live - shg_rejected_project:$shg_rejected_project - shg_repaid_project:$shg_repaid_project + shg_applied_loans_tcs : $shg_applied_loans_tcs";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$shg_limit_project=$shg_max_loans_ivrs-$shg_oustanding_project;
		
		$message="shg_limit_project: $shg_limit_project = shg_max_loans_ivrs: $shg_max_loans_ivrs - shg_oustanding_project: $shg_oustanding_project ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		$shg_allowed_loans=$shg_limit_project;
		
		$message="Calculating outstanding loans of SHG in project $project using TCS Tables only ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$TCS_shg_applied_project_rs_pending=mssql_fetch_array(mssql_query("select count(vo_id) from VO_REQUEST_MESSAGES(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and IS_PROCESSED='N'"));
		$TCS_shg_applied_project_pending=$TCS_shg_applied_project_rs_pending[0];
		
		$TCS_shg_applied_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project'"));
		$TCS_shg_applied_project=$TCS_shg_applied_project_rs[0];
		
		$TCS_shg_applied_project_live_rs=mssql_fetch_array(mssql_query("select count(vo_id) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and IVRS_ID='$ivr_call_id'"));
		$TCS_shg_applied_project_live=$TCS_shg_applied_project_live_rs[0];
		
		$TCS_shg_rejected_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and STATUS_ID='11'"));
		$TCS_shg_rejected_project=$TCS_shg_rejected_project_rs[0];
		
		$TCS_shg_repaid_project_rs=mssql_fetch_array(mssql_query("select count(smlsn.VO_ID) FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) smlsn,SHG_LOAN_APPLICATION(nolock) sla WHERE smlsn.SHG_ID='$shg_code' and smlsn.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type='$project' and smlsn.IS_CLOSED='1'"));
		$TCS_shg_repaid_project=$TCS_shg_repaid_project_rs[0];
		
		
		$TCS_shg_oustanding_project=$TCS_shg_applied_project+$TCS_shg_applied_project_pending+$TCS_shg_applied_project_live-$TCS_shg_rejected_project-$TCS_shg_repaid_project;
		
		$message="TCS_shg_oustanding_project: $TCS_shg_oustanding_project = TCS_shg_applied_project:$TCS_shg_applied_project + TCS_shg_applied_project_pending : $TCS_shg_applied_project_pending TCS_shg_applied_project_live:$TCS_shg_applied_project_live - TCS_shg_rejected_project:$TCS_shg_rejected_project - TCS_shg_repaid_project:$TCS_shg_repaid_project ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$TCS_shg_limit_project=$shg_max_loans_total-$TCS_shg_oustanding_project;
		$message="TCS_shg_limit_project: $TCS_shg_limit_project = shg_max_loans_total: $shg_max_loans_total - TCS_shg_oustanding_project: $TCS_shg_oustanding_project ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		$TCS_shg_allowed_loans=$TCS_shg_limit_project;
		
		if($shg_allowed_loans > 0 && $TCS_shg_allowed_loans > 0){
		$message="SUCCESS: shg_allowed_loans: $shg_allowed_loans Greater than 0 AND TCS_shg_allowed_loans:$TCS_shg_allowed_loans Greater than 0";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		return $shg_allowed_loans;
		}else{
		$message="FAIL: shg_allowed_loans: $shg_allowed_loans Less than 0 OR TCS_shg_allowed_loans:$TCS_shg_allowed_loans Less than 0";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		return 0;
		}
		
		
		
	}	
	
function smartphone_shg_member_limit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs){
		
		$test_vonumber=$GLOBALS['test_vonumber'];
		$message="Calculating outstanding loans of SHG in project $project  ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$loans=array();
		$rslt=mssql_query("select MEMBER_ID from IVRS_LOAN_REQUEST(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'");
		$shg_applied_project=mssql_num_rows($rslt);
		for($i=0;$i<$shg_applied_project;$i++)
		{
		$shg_applied_project_row=mssql_fetch_array($rslt);
		array_push($loans,$shg_applied_project_row[MEMBER_ID]);
		}
		

		$rslt=mssql_query("select * from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and IVRS_ID='$ivr_call_id'");
		$shg_applied_project_live=mssql_num_rows($rslt);

		for($i=0;$i<$shg_applied_project_live;$i++)
		{
		$shg_applied_project_row_live=mssql_fetch_array($rslt);
		array_push($loans,$shg_applied_project_row_live[MEMBER_ID]);
		}
		
		$rslt=mssql_query("select MEMBER_LONG_CODE from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and STATUS_ID='11'");
		$shg_rejected_project=mssql_num_rows($rslt);

		for($i=0;$i<$shg_rejected_project;$i++)
		{
		$shg_rejected_project_row=mssql_fetch_array($rslt);
		$key = array_search($shg_rejected_project_row[MEMBER_LONG_CODE],$loans);
		if($key!==false){
		    unset($loans[$key]);
			}
		}

		$rslt=mssql_query("select MEMBER_LONG_CODE FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) smlsn,SHG_LOAN_APPLICATION(nolock) sla WHERE smlsn.SHG_ID='$shg_code' and smlsn.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type='$project' and smlsn.IS_CLOSED='1'");
		$shg_repaid_project=mssql_num_rows($rslt);

		for($i=0;$i<$shg_repaid_project;$i++)
		{
		$shg_repaid_project_row=mssql_fetch_array($rslt);
		$key = array_search($shg_repaid_project_row[MEMBER_LONG_CODE],$loans);
		if($key!==false){
		    unset($loans[$key]);
			}
		}
		return $loans;
	}	
	
function shg_outstanding_amt($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code){
		$test_vonumber=$GLOBALS['test_vonumber'];
		$message="Calculating outstanding Amount of SHG in project $project  ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$shg_applied_project_rs=mssql_fetch_array(mssql_query("select sum(loan_amount) from IVRS_LOAN_REQUEST(nolock) where shg_id='$shg_code' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and PROJECT_TYPE not in(71,72,74,43)"));
		$shg_applied_project=$shg_applied_project_rs[0];
		
		$TCS_shg_applied_project_rs_pending=mssql_fetch_array(mssql_query("select sum(loan_amount) from VO_REQUEST_MESSAGES(nolock) where shg_id='$shg_code' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and IS_PROCESSED='N' and PROJECT_TYPE not in(71,72,74,43)"));
		$TCS_shg_applied_project_pending=$TCS_shg_applied_project_rs_pending[0];
		
		$TCS_shg_applied_project_rs=mssql_fetch_array(mssql_query("select sum(actual_amount) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and PROJECT_TYPE NOT IN ('71','72','74','43')"));
		$TCS_shg_applied_project=$TCS_shg_applied_project_rs[0];
		
		$shg_applied_project_live_rs=mssql_fetch_array(mssql_query("select sum(loan_amount) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id='$shg_code' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and PROJECT_TYPE NOT IN ('71','72','74','43') and IVRS_ID='$ivr_call_id'"));
		$shg_applied_project_live=$shg_applied_project_live_rs[0];
		
		$shg_rejected_project_rs=mssql_fetch_array(mssql_query("select sum(actual_amount) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and PROJECT_TYPE NOT IN ('71','72','74','43') and STATUS_ID='11' and PROJECT_TYPE !='43'"));
		$shg_rejected_project=$shg_rejected_project_rs[0];
		
		$shg_repaid_project_rs=mssql_fetch_array(mssql_query("select sum(sla.ACTUAL_AMOUNT) FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) smlsn,SHG_LOAN_APPLICATION(nolock) sla WHERE smlsn.SHG_ID='$shg_code' and smlsn.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and sla.PROJECT_TYPE NOT IN ('71','72','74','43') and smlsn.IS_CLOSED='1'"));
		$shg_repaid_project=$shg_repaid_project_rs[0];
		
		
		$shg_applied_project_tcs_rs=mssql_fetch_array(mssql_query("select sum(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and REQUESTED_ID='201314' and PROJECT_TYPE NOT IN ('71','72','74','43')"));
		$shg_applied_project_tcs=$shg_applied_project_tcs_rs[0];
		
		$shg_oustanding_project=$shg_applied_project+$shg_applied_project_live-$shg_rejected_project-$shg_repaid_project+$shg_applied_project_tcs;
		
		$message="shg_oustanding_project: $shg_oustanding_project = shg_applied_project:$shg_applied_project + shg_applied_project_live:$shg_applied_project_live - shg_rejected_project:$shg_rejected_project - shg_repaid_project:$shg_repaid_project + shg_applied_project_tcs: $shg_applied_project_tcs ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$TCS_shg_oustanding_project=$TCS_shg_applied_project+$TCS_shg_applied_project_pending+$shg_applied_project_live-$shg_rejected_project-$shg_repaid_project;
		
		$message="TCS shg_oustanding_project: $shg_oustanding_project = TCS_shg_applied_project:$TCS_shg_applied_project + shg_applied_project_live:$shg_applied_project_live - shg_rejected_project:$shg_rejected_project - shg_repaid_project:$shg_repaid_project ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		if($shg_oustanding_project > $TCS_shg_oustanding_project){
			return $TCS_shg_oustanding_project;
		}else{
			return $shg_oustanding_project;
		}
	}
		
function vo_outstanding($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project){
		$test_vonumber=$GLOBALS['test_vonumber'];
		//// get credit limits from play_credits function    
	    $vo_amt_to_add_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and STATUS_ID='11' and PROJECT_TYPE NOT IN ('71','72','74','43')"));
       $vo_amt_to=$vo_amt_to_add_rs[0];
	  // $vo_total_credit=$vo_total_credit+$vo_amt_to;
	   
		
		$vo_lt_max_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and PROJECT_TYPE NOT IN ('71','72','74','43')"));
		$vo_lt_max_tcs=$vo_lt_max_tcs_rs[0];
		
		$vo_lt_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and PROJECT_TYPE NOT IN ('71','72','74','43')"));
		$vo_lt_live=$vo_lt_live_rs[0];
		
	$vo_repaid_total_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_VO from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and PROJECT_TYPE NOT IN ('71','72','74','43')"));
        $vo_repaid_total = $vo_repaid_total_rs['AMT_REPAID_VO'];
		$vo_repaid_total=intval($vo_repaid_total);
		
		$applied_loans_tcs_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and REQUESTED_ID='201314' and PROJECT_TYPE NOT IN ('71','72','74','43')"));
       $applied_loans_tcs=$applied_loans_tcs_rs[0];
		
		$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_live-$vo_repaid_total-$vo_amt_to+$applied_loans_tcs;

		$message = "vo_credit_max_tcs=vo_lt_max_tcs+vo_lt_live-vo_repaid_total-vo_amt_to+applied_loans_tcs::$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_live-$vo_repaid_total-$vo_amt_to+$applied_loans_tcs\n";		
		$this->php_log_ivr($ivr_call_id,$message);

		return $vo_credit_max_tcs;
		}	
		
		function vo_outstanding_tcs($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project){
		$test_vonumber=$GLOBALS['test_vonumber'];
		//// get credit limits from play_credits function    
	    $vo_amt_to_add_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and STATUS_ID='11' and PROJECT_TYPE NOT IN ('71','72','74','43')"));
       $vo_amt_to=$vo_amt_to_add_rs[0];
	  // $vo_total_credit=$vo_total_credit+$vo_amt_to;
	   
		
		$vo_lt_max_tcs_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and PROJECT_TYPE NOT IN ('71','72','74','43')"));
		$vo_lt_max_tcs=$vo_lt_max_tcs_rs[0];
		
		$vo_lt_max_tcs_rs_pending=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from VO_REQUEST_MESSAGES(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and PROJECT_TYPE NOT IN ('71','72','74','43') and IS_PROCESSED='N'"));
		$vo_lt_max_tcs_pending=$vo_lt_max_tcs_rs_pending[0];
		
		$vo_lt_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and PROJECT_TYPE NOT IN ('71','72','74','43')"));
		$vo_lt_live=$vo_lt_live_rs[0];
		
	$vo_repaid_total_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_VO from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and PROJECT_TYPE NOT IN ('71','72','74','43')"));
        $vo_repaid_total = $vo_repaid_total_rs['AMT_REPAID_VO'];
		$vo_repaid_total=intval($vo_repaid_total);
		
		$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_max_tcs_pending+$vo_lt_live-$vo_repaid_total-$vo_amt_to;

		 $message = "vo_credit_max_tcs=vo_lt_max_tcs+vo_lt_live-vo_repaid_total-vo_amt_to::$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_live-$vo_repaid_total-$vo_amt_to\n";
                $this->php_log_ivr($ivr_call_id,$message);

		return $vo_credit_max_tcs;
		}		
	
		function shg_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code){
			
			$test_vonumber=$GLOBALS['test_vonumber'];
			$members_query="select MEMBER_ID from shg_member_info(nolock) where SHG_ID='$shg_code'";
			$shg_members=mssql_num_rows(mssql_query($members_query));
			$shg_limit_array=array();
			if($shg_members >= 14){
                                $shg_max_loans_total=11;
                                $shg_max_loans_ivrs=7;
                                $shg_max_credit_limit=375000;
                        }
			elseif($shg_members == 12 || $shg_members == 13){
				$shg_max_loans_total=9;
				$shg_max_loans_ivrs=6;
				$shg_max_credit_limit=300000;
			}elseif($shg_members == 10 || $shg_members == 11){
				$shg_max_loans_total=8;
				$shg_max_loans_ivrs=6;
				$shg_max_credit_limit=300000;
			}elseif($shg_members <= 9){
				
				$shg_members_75percent=ceil($shg_members*0.75);
				if($shg_members_75percent > 6){
					$shg_max_loans_total=$shg_members_75percent;
				}else{
					$shg_max_loans_total=6;
				}
				
				$shg_max_loans_ivrs=4;
				$shg_max_credit_limit=200000;
			}
			
			$shg_limit_array[$shg_code]['shg_max_loans_total']=$shg_max_loans_total;
			$shg_limit_array[$shg_code]['shg_max_loans_ivrs']=$shg_max_loans_ivrs;
			$shg_limit_array[$shg_code]['shg_max_credit_limit']=$shg_max_credit_limit;
			
			$message="SHG LIMITS in project $project :: shg_max_loans_total : $shg_max_loans_total ,shg_max_loans_ivrs: $shg_max_loans_ivrs, shg_max_credit_limit: $shg_max_credit_limit";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
			
			return $shg_limit_array;
		}
	
		function shg_limits_suvidha($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code){
			
			$test_vonumber=$GLOBALS['test_vonumber'];
			//$members_query="select MEMBER_ID from shg_member_info(nolock) where SHG_ID='$shg_code'";
			$members_query="Get_SHG_Eligibility_Info '$shg_code',0,0,'$project'";
			//$shg_members=mssql_num_rows(mssql_query($members_query));
			$shg_eligibility_row=mssql_fetch_array(mssql_query($members_query));
			$shg_limit_array=array();
			$shg_members=$shg_eligibility_row[MEMCOUNT];
			$message="no of members:$shg_members";
			$this->log_ivr($ivr_call_id,$message);
			$shg_applied_credit_limit=$shg_eligibility_row[WEB_LOAN_AMOUNT];
			//echo "shg_applied_credit_limit:$shg_applied_credit_limit";
			//exit;
			if($shg_members >= 14){
                                   $shg_max_loans_total=11;
                                   $shg_max_loans_ivrs=7;
                                   $shg_max_credit_limit=375000;
                       }
			elseif($shg_members == 12 || $shg_members == 13){
				$shg_max_loans_total=9;
				$shg_max_loans_ivrs=6;
				$shg_max_credit_limit=300000;
			}elseif($shg_members == 10 || $shg_members == 11){
				$shg_max_loans_total=8;
				$shg_max_loans_ivrs=6;
				$shg_max_credit_limit=300000;
				//echo "shg_max_credit_limit:$shg_max_credit_limit";
				//exit;
			}elseif($shg_members <= 9){
				
				$shg_members_75percent=ceil($shg_members*0.75);
				if($shg_members_75percent > 6){
					$shg_max_loans_total=$shg_members_75percent;
				}else{
					$shg_max_loans_total=6;
				}
				
				$shg_max_loans_ivrs=4;
				$shg_max_credit_limit=200000;
			}
			
			 // START Finding max ivrs loans considering Pragathi/Akshaya loans
           /* if($shg_eligibility_row[WEB_LOAN_COUNT]>($shg_max_loans_total-$shg_max_loans_ivrs))
            $shg_max_loans_ivrs = $shg_max_loans_ivrs-($shg_eligibility_row[WEB_LOAN_COUNT]-($shg_max_loans_total-$shg_max_loans_ivrs)); */
            // END Finding max ivrs loans considering Pragathi/Akshaya loans

			$shg_limit_array[$shg_code]['shg_max_loans_total']=$shg_max_loans_total;
			$shg_limit_array[$shg_code]['shg_max_loans_ivrs']=$shg_max_loans_ivrs;
			$shg_limit_array[$shg_code]['shg_max_credit_limit']=$shg_max_credit_limit;
			
			$message="SHG LIMITS in project $project from Get_SHG_Eligibility_Info::: shg_max_loans_total : $shg_max_loans_total ,shg_max_loans_ivrs: $shg_max_loans_ivrs, shg_max_credit_limit: $shg_max_credit_limit";
			$this->log_ivr($ivr_call_id,$message);
			
			return $shg_limit_array;
		}	
	
	
		
		function ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array){
			$test_vonumber=$GLOBALS['test_vonumber'];
			$prompt_path="/var/lib/asterisk/sounds/vo_ivrs";
			$menu_file=$ivrs_array['file'];
			$menu_max_digits=$ivrs_array['max_digits'];
			$menu_timeout=$ivrs_array['timeout'];
			$menu_invalid=$ivrs_array['no_input'];
			$menu_verification_string=$ivrs_array['verification_string'];
			
			$menu_file_path=$prompt_path."/".$language."/".$menu_file;
			if($menu_timeout == ""){
				$menu_timeout=5000;
			}
			$menu_retries=$ivrs_array['retries'];
			
			$retries=0;
			while($retries < $menu_retries){
			$res_dtmf=$agi->get_data($menu_file_path,$menu_timeout,$menu_max_digits,'#');
			$ivr_response=$res_dtmf ["result"];
			$response=$ivrs_array[$ivr_response];
					
			$message="Prompting user for $menu_file in project $project :: VO entered $ivr_response , IVRS response is $response";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
						
			if($response != '' ){
				break;
			}else{
				$invalid_file=$prompt_path."/".$language."/".$menu_invalid;
				$agi->stream_file($invalid_file,'#');
			}
			
			$retries++;
			}
			
			if(strlen($response) >= 1){
				$menu_verification_string=str_replace('response',$response,$menu_verification_string);
				$confirmation=$this->verification($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$menu_verification_string);
				if($confirmation == 1){
					return $response;
				}else{
					$response=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
					return $response;
				}
				
			}
			
		}
		
		function member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos){
			
		$test_vonumber=$GLOBALS['test_vonumber'];
			$project=1;
     		$db_filed="category";
		$type='mem_mode';
		$length='5';
		$play_msg="mem_category";
		$x='3';
 //$member_type=$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
 
 
 
 		$ivrs_array='';
		$ivrs_array=array();
		$ivrs_array['file']="mem_category";
		$ivrs_array['retries']=3;
		$ivrs_array['max_digits']=1;
		$ivrs_array['timeout']=5000;
		$ivrs_array['no_input']="invaliddata";
		$ivrs_array['verification_string']="A^response";
				
		$ivrs_array['1']="pop";
		$ivrs_array['2']="poor";
		
		$member_type=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
		
		
		if($member_type == ''){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
			$agi->Hangup();
			exit;
		}
	
	if($member_type == "pop"){
		$member_type="Y";
	}elseif($member_type == "poor"){
		$member_type="N";
	}
		
	$length='2';
	$play_msg='two_digit_member_id';
	$type='sthreenidhi';
	$x='3';
	$db_filed=$member_type;
	$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE,IS_POP_MEM from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
  $IS_POP_MEM=$member_id_rs['IS_POP_MEM'];

 				
$message="Member details:  MEMBER_ID: $member_id , Short Code:  $member_short_code,member_uid: $member_uid , member_age:$member_age , IS_POP_MEM:$IS_POP_MEM , member_mobile_num: $member_mobile_num";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
 				
 				if($IS_POP_MEM != $member_type){
					if($IS_POP_MEM =='N' && $member_type=='Y')
						{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_pop";
						$agi-> stream_file($wfile, '#');	
						
						}
						if($IS_POP_MEM == 'Y' && $member_type == 'N')
						{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_non_pop";
						$agi-> stream_file($wfile, '#');
						}
					
					if($x>='1')
					{
					$x=$x-1;
					$this->member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos);
		mssql_close($link);
					$agi->Hangup();
					exit;
					}else{
		mssql_close($link);
					$agi->Hangup();
					exit;		
					}
				
				}

############	Member Validation new Start project type 1
		$message="SHG LIMITS in project $project at 4th place";
		$this->log_ivr($ivr_call_id,$message);
		$project = '1';
		$shg_limit_array=$this->shg_limits_suvidha($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code);
		$shg_max_loans_total=$shg_limit_array[$shg_code]['shg_max_loans_total'];
		$shg_max_loans_ivrs=$shg_limit_array[$shg_code]['shg_max_loans_ivrs'];
		$shg_max_credit_limit=$shg_limit_array[$shg_code]['shg_max_credit_limit'];
		$message="SHG LIMITS in project $project at 4th place shg_max_loans_total:$shg_max_loans_total,shg_max_loans_ivrs:$shg_max_loans_ivrs,shg_max_credit_limit:$shg_max_credit_limit";
		$this->log_ivr($ivr_call_id,$message);
		
/*
$distinct_member_ids_query = "select distinct MEMBER_ID from (
select I.VO_ID,I.SHG_ID,I.IVRS_ID,I.PROJECT_TYPE,I.MEMBER_ID,I.SHORT_CODE,SLA.STATUS_ID,SMLS.IS_CLOSED,sla.MEMBER_SHORT_CODE
from evgen.IVRS_LOAN_REQUEST I (nolock) 
left join SHG_LOAN_APPLICATION SLA (nolock) on I.IVRS_ID=SLA.REQUESTED_ID and SLA.SHG_ID=I.SHG_ID and isnull(I.MEMBER_ID,'')=isnull(SLA.MEMBER_LONG_CODE,'')
left join SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) SMLS on SMLS.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type in ('1','72') and SMLS.IS_CLOSED='1'
where I.shg_id like '$shg_code' and I.PROJECT_TYPE in('1','72') and I.MEMBER_ID is not null
 and isnull(I.IS_PROCESSED,'NOTDUPLICATE') !='D'
 ) A where (A.STATUS_ID<>'11'or a.STATUS_ID is null) and (A.IS_CLOSED is null or A.IS_CLOSED<>'1') ";
$distinct_member_ids_result = mssql_query($distinct_member_ids_query);
$distinct_member_ids_count  = mssql_num_rows($distinct_member_ids_result);
		if($distinct_member_ids_count>=$shg_max_loans_ivrs)
		{
$distinct_member_ids_exist_query = "select distinct MEMBER_ID from (
select I.VO_ID,I.SHG_ID,I.IVRS_ID,I.PROJECT_TYPE,I.MEMBER_ID,I.SHORT_CODE,SLA.STATUS_ID,SMLS.IS_CLOSED,sla.MEMBER_SHORT_CODE
from evgen.IVRS_LOAN_REQUEST I (nolock) 
left join SHG_LOAN_APPLICATION SLA (nolock) on I.IVRS_ID=SLA.REQUESTED_ID and SLA.SHG_ID=I.SHG_ID and isnull(I.MEMBER_ID,'')=isnull(SLA.MEMBER_LONG_CODE,'')
left join SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) SMLS on SMLS.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type in ('1','72') and SMLS.IS_CLOSED='1'
where I.shg_id like '$shg_code' and I.member_id='$member_id' and I.PROJECT_TYPE in('72') and I.MEMBER_ID is not null
 and isnull(I.IS_PROCESSED,'NOTDUPLICATE') !='D'
 ) A where (A.STATUS_ID<>'11'or a.STATUS_ID is null) and (A.IS_CLOSED is null or A.IS_CLOSED<>'1') ";
*/
$distinct_member_ids_exist_query = "SP_SMARTPHONE_LOANSVALIDATION '$shg_code','$member_id','1','$ivr_call_id','$shg_max_loans_ivrs'";
$distinct_member_ids_exist_result = mssql_query($distinct_member_ids_exist_query);
//$distinct_member_ids_exist_count  = mssql_num_rows($distinct_member_ids_exist_result);
$distinct_member_ids_exist_row = mssql_fetch_array($distinct_member_ids_exist_result);
        $message="SP_SMARTPHONE_LOANSVALIDATION '$shg_code','$member_id','1','$ivr_call_id','$shg_max_loans_ivrs'--$distinct_member_ids_exist_row[STATUS]";
        $this->log_ivr($ivr_call_id,$message);
                                if($distinct_member_ids_exist_row[STATUS]=='LOAN ALLOWED')
				{


$member_overdue_query="select LOAN_DUE from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='0'";

$member_overdue_rslt =mssql_query($member_overdue_query);


$row_count = mssql_num_rows($member_overdue_rslt);
if($row_count>0)
	{
		$member_overdue_row = mssql_fetch_array($member_overdue_rslt);
$aa = "<pre>".print_r($member_overdue_row,1).count($member_overdue_row)."</pre>";
fwrite(STDERR,"\n--proceed1--(".$aa.")");
		if($member_overdue_row[LOAN_DUE]>0)
		{
				if($x>='1')
					{
			
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_sn_overdue";
						$agi-> stream_file($wfile, '#');
						$this->play_amount($member_overdue_row[LOAN_DUE],$agi);
						$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    					$agi-> stream_file($wfile, '#');
						#$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
						#$agi-> stream_file($wfile, '#');
						$x=$x-1;
						$this->member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos);
						mssql_close($link);
						$agi->Hangup();
		  				exit;
		   			}else
		   			{
		    				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    				$agi-> stream_file($wfile, '#');
						mssql_close($link);
		   				$agi->Hangup();
		   				exit;
		   			}
		}
	}
				}
				else
				{
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_".$shg_max_loans_ivrs."_loans", 5000, 1);
		$more_res=$res_dtmf ["result"];
		$this->smartphone_new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$status_pop_fund);
		mssql_close($link);
		$agi->Hangup();
				}
//		}
		
############	Member Validation new End project type 1


	$member_applied_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type not in('71','72','74','43')"));
	
	$member_prev_loan_count_tcs = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and STATUS_ID!='99' AND PROJECT_TYPE IN(SELECT * FROM GET_PTYPES (0)) and project_type not in('71','72','74','43')"));
	
	$member_prev_loan_count_tcs_pending = mssql_num_rows(mssql_query("select VO_ID from VO_REQUEST_MESSAGES(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' AND PROJECT_TYPE IN(SELECT * FROM GET_PTYPES (0)) and project_type not in('71','72','74','43') and IS_PROCESSED='N'"));
	
	$member_prev_loan_count_live = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id' and project_type not in('71','72','74','43')"));

$member_rej_cnt_lng = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and REQUESTED_ID!='201314' and project_type not in('71','72','74','43')"));

$member_rej_cnt_lng_tcs = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' AND PROJECT_TYPE IN(SELECT * FROM GET_PTYPES (0)) and project_type not in('71','72','74','43')"));

//$member_repaid_loans = mssql_num_rows(mssql_query("select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED='1'"));
$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1' and project_type  not in('71','72','74','43')"));

$member_other_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));

		if($member_applied_loan_count == 0  && $member_prev_loan_count_tcs ==0){
		 $member_repaid_loans=0;
		}


	$member_corpus_loans_applied=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and ( MEMBER_ID='$member_id'  or SHORT_CODE='$member_short_code') and project_type='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
	
	$member_corpus_loans_applied_live=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and ( MEMBER_ID='$member_id'  or SHORT_CODE='$member_short_code') and IVRS_ID='$ivr_call_id' and project_type='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
	
	$member_corpus_loans_rejected=mssql_num_rows(mssql_query("select VO_ID from shg_loan_application(nolock) where SHG_ID='$shg_code' and ( MEMBER_LONG_CODE='$member_id'  or MEMBER_SHORT_CODE='$member_short_code') and project_type='53' and STATUS_ID='11'"));
	
	$member_corpus_loans_rejected_tcs=mssql_num_rows(mssql_query("select VO_ID from shg_loan_application(nolock) where SHG_ID='$shg_code' and ( MEMBER_LONG_CODE='$member_id'  or MEMBER_SHORT_CODE='$member_short_code') and project_type='53' and STATUS_ID='11'"));
	
	//$member_corpus_loans_repaid=mssql_num_rows(mssql_query("select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED='1' "));
	$member_corpus_loans_repaid=0;
	
	
	$member_corpus_loans=$member_corpus_loans_applied+$member_corpus_loans_applied_live-$member_corpus_loans_rejected-$member_corpus_loans_repaid;
		
	if($member_corpus_loans == 1){
		$member_outstanding_rs=mssql_fetch_array(mssql_query("select OUTSTANDING from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code'"));
 $member_outstanding=$member_outstanding_rs['OUTSTANDING'];
 if($member_outstanding < 10000 ){
 	$member_corpus_loans=0;
 }
	}
	
	$message="member_applied_loan_count-member_rej_cnt_lng+member_prev_loan_count_live-member_repaid_loans-member_corpus_loans:$member_applied_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_corpus_loans:::member_prev_loan_count_tcs-member_rej_cnt_lng_tcs+member_prev_loan_count_live-member_repaid_loans-member_corpus_loans:$member_prev_loan_count_tcs-$member_rej_cnt_lng_tcs+$member_prev_loan_count_live-$member_repaid_loans-$member_corpus_loans ::member_other_repaid_loans:$member_other_repaid_loans";
        $this->log_ivr($ivr_call_id,$message);
	

        $member_prev_loan_count=$member_applied_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans;

        $member_prev_loan_count_tcs=$member_prev_loan_count_tcs+$member_prev_loan_count_tcs_pending-$member_rej_cnt_lng_tcs+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans;
	
	$message="AT SN: Evolgence: ".$member_prev_loan_count." TCS:  ".$member_prev_loan_count_tcs;	
	$this->log_ivr($ivr_call_id,$message);	
	if($member_prev_loan_count < $member_prev_loan_count_tcs){
		$message="Member loans less than TCS ".$member_prev_loan_count." less than ".$member_prev_loan_count_tcs;		
		$member_prev_loan_count=$member_prev_loan_count_tcs;
		$this->log_ivr($ivr_call_id,$message);	
	}
	
	//$message="Member loans ".$member_applied_loan_count."-".$member_rej_cnt_lng."+".$member_prev_loan_count_live."-".$member_repaid_loans."-".$member_corpus_loans." = ".$member_prev_loan_count;

	$message="Member loans ".$member_applied_loan_count."-".$member_rej_cnt_lng."+".$member_prev_loan_count_live."-".$member_repaid_loans."-".$member_other_repaid_loans." = ".$member_prev_loan_count;

	$this->log_ivr($ivr_call_id,$message);	
	
//	if($member_prev_loan_count < 0 ){
//	$member_prev_loan_count=0;
//	}

	$message="PREVIOUS LOANS OF PROJECT TYPE 1 WILL CHECK HERE";
	$this->log_ivr($ivr_call_id,$message);
	//MEMBER COUNT OF BOTH GENERAL AND MICRO/TINY START-21-09-18
	
	$member_count_query="select * from streenidhi_member_loan_count('$shg_code','$member_id')";
	$members_eligibility_row=mssql_fetch_array(mssql_query($member_count_query));
	
	$member_prev_loan_count=$members_eligibility_row[member_count];
	$message="member previous loan count from procedure streenidhi_member_loan_count ".$member_prev_loan_count;
	$this->log_ivr($ivr_call_id,$message);	
	//MEMBER COUNT OF BOTH GENERAL AND MICRO/TINY END-21-09-18

/*	if($member_prev_loan_count != 0)
	{
	
	$message="Member loan_already_applied : member_prev_loan_count:".$member_prev_loan_count." member_repaid_loans : ".$member_repaid_loans;
	$this->log_ivr($ivr_call_id,$message);	
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		mssql_close($link);
	$agi->Hangup();
	exit;	
	} */
	
	if($member_prev_loan_count == 1)
        {
        $project_type_query = "get_SNloans_project_type_ivrs '$shg_code','$member_id'";
        $project_type_exist_result = mssql_query($project_type_query);
        $row_count = mssql_num_rows($project_type_exist_result);
        if($row_count>0){
       	$project_type_exist_result_row = mssql_fetch_array($project_type_exist_result);
        $overdue = $project_type_exist_result_row[OVERDUE];
        $instno  = $project_type_exist_result_row[INSTNO];
        $message="OVERDUE:".$overdue." and INSTALLMENTS:".$instno;
        $this->log_ivr($ivr_call_id,$message);               
        if($overdue != 0 && $instno < 21||$overdue == 0 && $instno <21||$overdue != 0 && $instno>=21)
                {
          $message="Member has micro loan but might have overdue".$overdue." or not completed 21 installments".$instno;
	$this->log_ivr($ivr_call_id,$message);
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	mssql_close($link);
	$agi->Hangup();
	exit;	
         
         }
	}
         else{
          $message="Member Previous loan count is 1 but having loan other than micro and general:".$member_prev_loan_count;
	$this->log_ivr($ivr_call_id,$message);
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	mssql_close($link);
	$agi->Hangup();
	exit;	
	
         }
          }
     elseif($member_prev_loan_count>1){
     	$message="Member Previous loan count greater than 1 is:".$member_prev_loan_count;
	$this->log_ivr($ivr_call_id,$message);
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	mssql_close($link);
	$agi->Hangup();
	exit;		
          
         }
	else{
	}	

	
	
	
	
	

	if(strlen($member_mobile_num) == 10){
		
	}else{
    $length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
	if($x>='1')
		{
	 $x=$x-1;
	 $this->member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos);
		mssql_close($link);
	 $agi->Hangup();
	exit;
	 }else{
		mssql_close($link);
	$agi->Hangup();
	exit;		
	}
	
	}
	}
	

		$db_filed="reason";
		$type='why_loan';
		$length='5';
		$play_msg="member_loan_reason_new";
		$x='3';
		
//		$ivrs_array='';
//		$ivrs_array=array();
//		$ivrs_array['file']="IG_1_NONIG_2";
//		$ivrs_array['retries']=3;
//		$ivrs_array['max_digits']=1;
//		$ivrs_array['timeout']=5000;
//		$ivrs_array['no_input']="invaliddata";
//		$ivrs_array['verification_string']="A^response";
//				
//		$ivrs_array['1']="IG";
//		$ivrs_array['2']="NONIG";
//		
//		$loan_category=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
//		
//		if($loan_category == ''){
//			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
//		    $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//			$agi->Hangup();
//			exit;
//		}
		
		
		$ivrs_array='';
		$ivrs_array=array();
		$ivrs_array['file']="CONSUMPTION_1_IG_2_PRODUCER_3";
		$ivrs_array['retries']=3;
		$ivrs_array['max_digits']=1;
		$ivrs_array['timeout']=5000;
		$ivrs_array['no_input']="invaliddata";
		$ivrs_array['verification_string']="A^response";
		
		$ivrs_array['1']="CONSUMPTION";
		$ivrs_array['2']="IG";
		$ivrs_array['3']="PRODUCER";
		
		$loan_type=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
		
		if($loan_type == ''){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
			$agi->Hangup();
			exit;
		}
		
		if($loan_type == "CONSUMPTION"){
		$ivrs_array='';
		$ivrs_array=array();
		$ivrs_array['file']="health_1_education_2_marriage_3";
		$ivrs_array['retries']=3;
		$ivrs_array['max_digits']=1;
		$ivrs_array['timeout']=5000;
		$ivrs_array['no_input']="invaliddata";
		$ivrs_array['verification_string']="A^response";

		
		$ivrs_array['1']="health";
		$ivrs_array['2']="education";
		$ivrs_array['3']="marriage";
		$loan_category="NONIG";	
		}else{
		$ivrs_array='';
		$ivrs_array=array();
		$ivrs_array['file']="hlp_activity_code";
		$ivrs_array['retries']=3;
		$ivrs_array['max_digits']=3;
		$ivrs_array['timeout']=5000;
		$ivrs_array['no_input']="invaliddata";
		$ivrs_array['verification_string']="A^loan_for*A^loan_activities/response*A^applying";

		
		$ivrs_array['101']="101";
		$ivrs_array['102']="102";
		$ivrs_array['103']="103";
		$ivrs_array['104']="104";
		$ivrs_array['105']="105";
		$ivrs_array['106']="106";
		$ivrs_array['107']="107";
		$ivrs_array['108']="108";
		$ivrs_array['109']="109";
		$ivrs_array['110']="110";
		$ivrs_array['201']="201";
		$ivrs_array['202']="202";
		$ivrs_array['203']="203";
		$ivrs_array['204']="204";
		$ivrs_array['205']="205";
		$ivrs_array['206']="206";
		$ivrs_array['207']="207";
		$ivrs_array['208']="208";
		$ivrs_array['209']="209";
		$ivrs_array['210']="210";
		$ivrs_array['301']="301";
		$ivrs_array['302']="302";
		$ivrs_array['303']="303";
		$ivrs_array['304']="304";
		$ivrs_array['305']="305";
		$ivrs_array['306']="306";
		$ivrs_array['307']="307";
		$ivrs_array['308']="308";
		$ivrs_array['309']="309";
		$ivrs_array['310']="310";
		$ivrs_array['311']="311";
		$ivrs_array['312']="312";
		$ivrs_array['313']="313";
		$ivrs_array['314']="314";
		$ivrs_array['315']="315";
		$ivrs_array['316']="316";
		$ivrs_array['317']="317";
		$ivrs_array['318']="318";
		$ivrs_array['319']="319";
		$ivrs_array['320']="320";
		$ivrs_array['321']="321";
		$ivrs_array['401']="401";
		$ivrs_array['402']="402";
		$ivrs_array['403']="403";
		$ivrs_array['404']="404";
		$ivrs_array['405']="405";
		$ivrs_array['406']="406";
		$ivrs_array['407']="407";
		$ivrs_array['408']="408";
		$ivrs_array['409']="409";
		$ivrs_array['410']="410";
		$ivrs_array['411']="411";
		$ivrs_array['412']="412";
		$ivrs_array['413']="413";
		$ivrs_array['414']="414";
		$ivrs_array['501']="501";
		$ivrs_array['502']="502";
		$ivrs_array['503']="503";
		$ivrs_array['504']="504";
		$ivrs_array['505']="505";
		$ivrs_array['506']="506";
		$ivrs_array['507']="507";
		$ivrs_array['508']="508";
		$ivrs_array['509']="509";
		$ivrs_array['510']="510";
		$ivrs_array['511']="511";
		$ivrs_array['512']="512";
		$ivrs_array['513']="513";
		$ivrs_array['514']="514";
		$ivrs_array['515']="515";
		$ivrs_array['516']="516";
		$ivrs_array['517']="517";
		$ivrs_array['518']="518";
		$ivrs_array['519']="519";
		$ivrs_array['520']="520";
		$ivrs_array['521']="521";
		$ivrs_array['522']="522";
		$ivrs_array['523']="523";
		$ivrs_array['524']="524";
		$ivrs_array['525']="525";
		$ivrs_array['526']="526";
		$ivrs_array['527']="527";
		$ivrs_array['528']="528";		
		$loan_category="IG";
		
		}
		
		

		
		$reason_loan=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
		
		if($reason_loan == ''){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
			$agi->Hangup();
			exit;
		}
		
		
		if($loan_category == "IG"){
			$reason_loan_array=mssql_fetch_array(mssql_query("select PURPOSE_NAME from PURPOSE_MASTER_WEB(nolock) where PURPOSE_ID='$reason_loan' and GENERAL='Y'"));
			$reason_loan_text=$reason_loan_array['PURPOSE_NAME'];

//Ashok change for purpose general
			
			$message="Fetching LOAN purpose from PURPOSE_MASTER_WEB for IG loans :select PURPOSE_NAME from PURPOSE_MASTER_WEB where PURPOSE_ID='$reason_loan' and GENERAL='Y' :: ".$reason_loan_text;
	$this->log_ivr($ivr_call_id,$message);
			
			if($reason_loan_text != ""){
				$reason_loan=$reason_loan_text;
			}
			else 
			{ 
		  	   if($x>='1') 
			    { 
				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/notgeneral-purpose"; 
				$agi-> stream_file($wfile, '#'); 
		 		$x=$x-1; 
				$amt_stat='Y'; 
				$this->member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos);
				mssql_close($link); 
				$agi->Hangup(); 
				exit; 
		   	   }else 
		   	   { 
		   		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry"; 
		  		$agi-> stream_file($wfile, '#'); 
				mssql_close($link); 
		  		$agi->Hangup(); 
		  		exit; 
		   	   } 
			}	
		}
  //$reason_loan =$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);

	
	
	
	
    if($amt_stat=='Y')
	{
		$db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
	 }
	 
	 if($loan_amount>='1')
	 {
//		 if($health=='NO')
//		 {
//		$db_filed="reason";
//		$type='why_loan';
//		$length='5';
//		$play_msg="member_loan_reason_new";
//		$x='3';
//  $reason_loan =$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
//		 }
//		 else
//		 {
//			$reason_loan='Emergency Needs/Health';
//		 }
  //echo "came hetereerr";
 
  if($reason_loan=='Marriage')
   {
       //$db_filed="category";
		//$type='mem_mode';
		$length='5';
		$play_msg="get_marriage_amount";
		//$x='3';
  $marriage_amt=$this->get_marriage_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
  
  if($loan_amount<=$marriage_amt)
  {
  
  }
  else
  {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loanamt_greater_marriage_amt";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$amt_stat='Y';
	$this->member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos);
		mssql_close($link);
	$agi->Hangup();
	exit;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		
  
  
  }
  
   }
 
 if(($reason_loan!='')&&($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && ($member_prev_loan_count=='0'||$member_prev_loan_count=='1')&& strlen($member_id)>1))
 {
 
 
 $message="CONDITION 1 WILL CHECK HERE";
	$this->log_ivr($ivr_call_id,$message);
 
 $duration='12';
 $etime=date('Y-m-d H:i:s');
 

 if( $reason_loan == "health" && $loan_amount > 15000 )
{
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_less_25000";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos);
		mssql_close($link);
	$agi->Hangup();
	exit;
}

 /*
 if(($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Emergency Needs/Health')&&$loan_amount>15000)
{
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_less_25000";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
}
*/
if($loan_category == "NONIG")
{
$duration='24';
}


 $vo_actual_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
 $vo_actual_credit=$vo_actual_credit_rs['ACTUAL_CREDIT_LIMIT'];
 $vo_credit_pop=$vo_actual_credit_rs['POP_CREDIT_LIMIT'];
 $vo_credit_non_pop=$vo_actual_credit_rs['NONPOP_CREDIT_LIMIT']; 
 
 
 if($member_type=='Y')
     {
	 $member_cat='pop';
	 $search_cat='0';
	 $vo_cat_actual_limit=$vo_credit_pop;
	 //$tbl_filed='current_limit_pop';
	  //$vo_credit_pop=intval(ceil($vo_actual_credit/2));
	





	 	if($loan_category == "IG")
		{
		$vo_fixed_credit=intval(ceil($vo_credit_pop*0.85));
		$tbl_filed='current_limit_pop_ig';
		$search_purpose="'Agriculture','Dairy','Income generation Activity','Weavers'";
		$search_cr_limit='credit_limit_pop_ig';
		$credit_lt_type="current_limit_pop_ig";//IGA_POP_DP
		}
		if($loan_category == "NONIG")
        {
	  $vo_fixed_credit=intval(floor($vo_credit_pop*0.15));
	  $tbl_filed='current_limit_pop_non_ig';
	  $search_cr_limit='credit_limit_pop_non_ig';
	  $search_purpose="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
	  $credit_lt_type="current_limit_pop_non_ig";//CONS_POP_DP
        }
		
	    }
		if($member_type=='N')
		{
			
		//$vo_credit_non_pop=intval(floor($vo_actual_credit/2));	
		$member_cat='non-pop';
		$search_cat='1';
		$vo_cat_actual_limit=$vo_credit_non_pop;
		 //$tbl_filed='current_limit_nonpop';
		 
		 
		  if($loan_category == "IG")
		{
		$vo_fixed_credit=intval(ceil($vo_credit_non_pop*0.85));	
		$tbl_filed='current_limit_nonpop_ig';
		$search_cr_limit='credit_limit_nonpop_ig';
		$search_purpose="'Agriculture','Dairy','Income generation Activity','Weavers'";
		$credit_lt_type="current_limit_nonpop_ig";//IGA_NONPOP_DP
		}
		
		if($loan_category == "NONIG")
        {
		$vo_fixed_credit=intval(floor($vo_credit_non_pop*0.15));
	    $tbl_filed='current_limit_nonpop_non_ig';
		$search_cr_limit='credit_limit_nonpop_non_ig';
		$search_purpose="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
		$credit_lt_type="current_limit_nonpop_non_ig";//CONS_NONPOP_DP
        }
		
		 }
	

	$applied_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE='1' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'")); 

	
        $applied_amt = $applied_rs['AMT'];
		
		$applied_rs_live=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='1'"));
        $applied_amt_live = $applied_rs_live['AMT_LIVE'];
		$applied_amt_live=intval($applied_amt_live);

		
		$vo_rej_amt_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE='1'  and STATUS_ID='11'"));		
$vo_rej_amt=$vo_rej_amt_rs['AMT_REJ'];
$vo_rej_amt=intval($vo_rej_amt);



//commented for automation
/*
   $vo_fixed_credit_rs=mssql_fetch_array(mssql_query("select $search_cr_limit from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
       $vo_fixed_credit = $vo_fixed_credit_rs[$search_cr_limit];
	   */
	   
	   $vo_repaid_cat_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='$search_cat' and PROJECT_TYPE='1'"));
        $repaid_cat_total = $vo_repaid_cat_rs['AMT_REPAID'];
		//added for automation
		$applied_total=$applied_amt+$applied_amt_live-$vo_rej_amt-$repaid_cat_total;
		
	if($applied_total < 0){
			$applied_total=0;
			
	$message="VO Repaid AMT is greater than applied amt,resetting the outstanding to 0, applied_total :$applied_total";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					
		}
		
		
		$vo_cat_limit=$vo_cat_actual_limit-$applied_total;
			if($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Weavers')
		{
			// $repaid_cat_total=intval(ceil($repaid_cat_total*0.85));
			 //$repaid_cat_total=intval($repaid_cat_total);
			 $vo_credit_lt=intval(ceil($vo_cat_limit*0.85));
		}
		if($reason_loan=='Education'||$reason_loan=='Marriage'||$reason_loan=='Emergency Needs/Health')
		{
			 //$repaid_cat_total=intval(floor($repaid_cat_total*0.15));
			 //$repaid_cat_total=intval($repaid_cat_total);
			  $vo_credit_lt=intval(ceil($vo_cat_limit*0.15));
		}
		
		
		$message="shg_outstanding_amt in project $project at 4th place";
		$this->log_ivr($ivr_call_id,$message);
		
	//$tcs_shg_outstanding_amt=$this->shg_outstanding_amt($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code);
	
	$members_query="Get_SHG_Eligibility_Info '$shg_code',0,0,'$project'";
	$shg_eligibility_row=mssql_fetch_array(mssql_query($members_query));
	$shg_limit_array=array();
	$shg_members=$shg_eligibility_row[MEMCOUNT];
	$shg_applied_ivrs_limit=$shg_eligibility_row[IVRS_LOAN_AMOUNT];
	$shg_applied_web_limit=$shg_eligibility_row[WEB_LOAN_AMOUNT];
	$tcs_shg_outstanding_amt= $shg_applied_ivrs_limit+$shg_applied_web_limit;

$tcs_shg_drawing_power=$shg_max_credit_limit-$tcs_shg_outstanding_amt;

 $message="VALIDATING SHG Drawing power from Get_SHG_Eligibility_Info-- tcs_shg_drawing_power: $tcs_shg_drawing_power = shg_max_credit_limit: $shg_max_credit_limit  - tcs_shg_outstanding_amt: $tcs_shg_outstanding_amt";
	$this->log_ivr($ivr_call_id,$message);

		//vo_amount
//$vo_total_credit_rs=mssql_fetch_array(mssql_query("select  credit_limit_pop+credit_limit_nonpop from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        //$vo_total_credit=$vo_total_credit_rs[0];
        $vo_total_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	 $vo_total_credit=$vo_total_credit_rs['ACTUAL_CREDIT_LIMIT'];

 
//$vo_outstanding=$this->vo_outstanding($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'1');
	
//$vo_drawing_power=$vo_total_credit-$vo_outstanding;


//$vo_outstanding_tcs=$this->vo_outstanding_tcs($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'1');
	
//$vo_drawing_power_tcs=$vo_total_credit-$vo_outstanding_tcs;


//total drawing power from dp_calculation_ivrs() start
 $CreditLimitsQry="select * from dp_calculation_ivrs() where TRANS_VO_ID='$void_code'";
 $vo_actual_credit_rs=mssql_fetch_array(mssql_query($CreditLimitsQry));
 $current_limit_vo_dp=$vo_actual_credit_rs["VO_TOTAL_DP"];
 $vo_drawing_power=$current_limit_vo_dp;
//total drawing power from dp_calculation_ivrs() end
	
$vo_category_drawing_power=$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$credit_lt_type,$project);			

// $message="VALIDATING LOAN AMOUNT vo_credit_lt:$vo_category_drawing_power Greater than Equal loan_amount:$loan_amount,Loan amount $loan_amount LESS THAN EQUAL VO Drawing power $vo_drawing_power,VO drawing power from TCS Tables $vo_drawing_power_tcs Greater than Equal Loan AMT $loan_amount,loan_amount: $loan_amount LESS THAN EQUAL tcs_shg_drawing_power: $tcs_shg_drawing_power";

$message="VALIDATING LOAN AMOUNT vo_credit_lt:$vo_category_drawing_power Greater than Equal loan_amount:$loan_amount,Loan amount $loan_amount LESS THAN EQUAL VO Drawing power $vo_drawing_power,loan_amount: $loan_amount LESS THAN EQUAL tcs_shg_drawing_power: $tcs_shg_drawing_power";

	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
			  
  //if($vo_credit_lt>=$loan_amount && $shg_lt_max<=150000 && $shg_limit_max_tcs<=150000 && $vo_credit_max_tcs<=$vo_total_credit && $mms_credit_max_tcs<=$mms_total_credit )
  //{ 
 // if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount && $loan_amount <= $tcs_shg_drawing_power)
 if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $loan_amount <= $tcs_shg_drawing_power)
  {  

	$message="Drawing Power Validation SERP 4";
 	$this->log_ivr($ivr_call_id,$message); 
 
//exit;
 //$echo_cmd="/bin/echo ivr_id $ivr_call_id uni $unique_id  credit_limit $vo_credit_lt >> /tmp/ivr.txt";
 //exec($echo_cmd);
 
 
 $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select * from IVRS_LOAN_REQUEST_LIVE(nolock) where  SHG_ID='$shg_code' and PROJECT_TYPE='1' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;

//Ashok Adding Current od and os to this member Start
		
				if($curr_odos!=0)
				{
					$shg_pending_ods_count=mssql_num_rows(mssql_query("select TOP 1 1 from SHG_MEMBER_PENDING_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
					if($shg_pending_ods_count>=1)
					{
					  $curr_odos=0;
				   	  $message="Already Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS for shg $shg_code";
				   	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					}
				}

//Ashok Adding Current od and os to this member End
	 
 
	$PROJECT_TYPE='1';  

	$this->chk_ln_status($shg_code,$member_id,$PROJECT_TYPE,$ivr_call_id);

	$loan_amount=$loan_amount+$curr_odos;

	$mms_dist_code=substr($void_code,2,2);
      $mms_mandal_code=substr($void_code,4,2);

//Start-For IVRS loan request from tablet,assigning IVRS_CALL_ID with SEC_MOB_NO
	$vo_name_array_sec=mssql_query("select * from vo_info(nolock) where SEC_MOB_NO='$caller' and IS_ACTIVE!='N'");
		$status_valied_sec= mssql_num_rows($vo_name_array_sec);
		if($status_valied_sec>0){
		 $unique_id=$caller;
		}
	//End - For IVRS loan request from tablet

$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos')";	


if($caller!=$test_vonumber)
{
mssql_query($loanRequestInsertQry);


//Ashok Adding Current od and os to this member Start
		
				if($curr_odos!=0)
				{
					$shg_pending_ods_count=mssql_num_rows(mssql_query("select TOP 1 1 from SHG_MEMBER_PENDING_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
					if($shg_pending_ods_count>=1)
					{
					  $curr_odos=0;
				   	  $message="Already Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS for shg $shg_code";
				   	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					}
					else
					{
					  $shg_mem_ods_res=mssql_query("select SHG_ID,MEMBER_ID,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code' and CURRENT_OVERDUE>0");
					  $rec_count=0;
					  while($shg_mem_ods_row=mssql_fetch_array($shg_mem_ods_res))
					   {
						if($caller!=$test_vonumber)
						{
						   $pres_odos=$shg_mem_ods_row['CURRENT_OVERDUE']+$shg_mem_ods_row['CURRENT_OUTSTANDING'];
						   if($pres_odos<=100)
				      		    {
						     $shg_pending="INSERT INTO SHG_MEMBER_PENDING_OVERDUE_STATUS(SHG_ID,MEMBER_ID,CREATED_DATE,CURRENT_OVERDUE,CURRENT_OUTSTANDING,INSERTED_BY) VALUES ('".$shg_mem_ods_row['SHG_ID']."','".$shg_mem_ods_row['MEMBER_ID']."',GETDATE(),'".$shg_mem_ods_row['CURRENT_OVERDUE']."','".$shg_mem_ods_row['CURRENT_OUTSTANDING']."','IVRS')";
						     mssql_query($shg_pending);

				  		   $message="SHG HAS CURRENT_OD,CURRENT_OS as pres_odos :$pres_odos Less than 100, so for this shg we are inserting into at SN $shg_pending. ";
				   	           $this->php_log_ivr($ivr_call_id,$message);
						    }
						}
						$rec_count++;
					   }
					$message="Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS from SAMSNBSTG.SN.SHG_OVERDUE_STATUS where shg $shg_code : rec_count:$rec_count :$shg_pending";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);					
					}
				}

//Ashok Adding Current od and os to this member End

}
else
{
	$message="Test is success not inseted test num:caller=$caller test_vonumber:$test_vonumber";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
}

$curr_odos=0;

                $message="Insertion of SN Lead : $loanRequestInsertQry";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

/*
  $vo_credit_rs2=mssql_fetch_array(mssql_query("select $tbl_filed from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        $vo_credit_lt_prev = $vo_credit_rs2[$tbl_filed];
		$vo_credit_lt_prev=intval($vo_credit_lt_prev);
		
		if($vo_credit_lt_prev<$loan_amount)
		{
			$used_from_repay=$loan_amount-$vo_credit_lt_prev;
			$used_from_ivr=$loan_amount-$used_from_repay;
			$loan_amount=$used_from_ivr;
			$current_limit=$vo_credit_lt_prev-$used_from_ivr;
		}else
		{
		$current_limit=$vo_credit_lt-$loan_amount;
		}
		*/
$current_limit=$vo_drawing_power-$loan_amount;
//$current_limit=$vo_credit_lt-$loan_amount;
if($caller!=$test_vonumber)
{
mssql_query("update  IVRS_VO_CREDIT_LIMIT set $tbl_filed='$current_limit'  where vo_id='$void_code'");
}

if($member_type=='Y')
     {
	$pop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_pop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$pop_lmt=$pop_lmt_rs['current_limit_pop'];
	
		 if($pop_lmt>=$loan_amount)
		 {
	 if($caller!=$test_vonumber)
	 {
	 mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_pop=current_limit_pop-$loan_amount  where vo_id='$void_code'");
	 }
		 }
	 }
	 if($member_type=='N')
		{
			
	$nonpop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_nonpop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$nonpop_lmt=$nonpop_lmt_rs['current_limit_nonpop'];
	
	if($nonpop_lmt>=$loan_amount)
	{
		if($caller!=$test_vonumber)
		{
		mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_nonpop=current_limit_nonpop-$loan_amount  where vo_id='$void_code'");
		}
	}
	   }
	   

$vo_credit_limit=$current_limit;

 $member_limit=$member_limit-1; 
   if($member_limit>=1 && $vo_credit_limit>=0)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{
	$amt_stat='Y';
	$this->member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos);
	}
	else
	{
	  if($vo_credit_limit>=0)
	  {
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{
			
	$this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}
 }
 else
 {

if($mms_credit_max_tcs>$mms_total_credit)
	 {
		$mms_dist_code=substr($void_code,2,2);
        $mms_mandal_code=substr($void_code,4,2); 
		$share = mssql_fetch_array(mssql_query("SELECT  SHARE_AMOUNT FROM SN.SHARE_BAL_FUN() where DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code' "));
		$share_amount=$share['SHARE_AMOUNT'];
		if($share_amount<1000000)
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/share_captial";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($share_amount,$agi);
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/paid_share_captial";
		$agi-> stream_file($wfile, '#');	
		}
		else
		{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
		}
	 }
	 else
	 {
    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
	 }

 //$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	
	$this->member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos);
		mssql_close($link);
	$agi->Hangup();
	exit;
 
   }
	
 }
 //echo  $loan_amount.$reason_loan.$member_type;
 
		}
		else
		  {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		 $this->new_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		$agi->Hangup();
		    }
		



			
		}
		
		function verification($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$menu_verification_string){
		$test_vonumber=$GLOBALS['test_vonumber'];
		//$record_file="vo_ivrs/".$language."/".$menu_verification_string;
		$this->play_array($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$menu_verification_string);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
		$status=$res_dtmf ["result"];
		return $status;
			
		
		}	
		
		function play_array($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$record_file){
			$test_vonumber=$GLOBALS['test_vonumber'];
			$file_array=explode('*',$record_file);
			$file_count=count($file_array);	
			for($file_count_i=0;$file_count_i < $file_count;$file_count_i++){
			$vb_file=$file_array[$file_count_i];
			$vb_file_array=explode('^',$vb_file);
			//$vb_file_array_count=count($vb_file_array); 
			$vb_file_function=$vb_file_array[0];
			$vb_file_path=$vb_file_array[1];
			if($vb_file_function == "A"){
			$prompt="/var/lib/asterisk/sounds/vo_ivrs/".$language."/".$vb_file_path;
			//$prompt=$vb_file_path;
			$agi-> stream_file($prompt, '#');		
			}
			if($vb_file_function == "RS"){
			$this->play_amount($vb_file_path,$agi);
			$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
			$agi-> stream_file($wfile, '#');
			} 
			if($vb_file_function == "N"){
			$this->play_amount($vb_file_path,$agi);
			}
			if($vb_file_function == "ND"){
			$this->play_digits($vb_file_path,$agi);
			}				
			if($vb_file_function == "DT"){
			$this->play_date($vb_file_path,$agi);
			}	
			}

		}

function shg_overdue($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$shg_code){
		$test_vonumber=$GLOBALS['test_vonumber'];
			$shg_recovery_query="select RECOVERY from SAMSNBSTG.SN.SHG_OVERDUE_STATUS(nolock) where shg_id='$shg_code'";

		$shg_recovery_entry=mssql_num_rows(mssql_query($shg_recovery_query));
		if($shg_recovery_entry > 0){
			
		$message="Calculating shg recovery in VO : $void_code,SHG : $shg_code :: $shg_recovery_query";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
		$shg_recovery_array=mssql_fetch_array(mssql_query($shg_recovery_query));
		$shg_recovery=$shg_recovery_array['RECOVERY'];
		
		$message="shg recovery in SHG : $shg_code : $shg_recovery";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
			
		}else{
			$shg_recovery="no_loans";
		}
				
		
		
		return $shg_recovery;
}

function veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs)
	{
	 $test_vonumber=$GLOBALS['test_vonumber'];
	 $project=62;
	 	
		 
	      if($x=='3')
	     {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sgh_priority";
		  $agi-> stream_file($wfile, '#');
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/first_priority";
		  $agi-> stream_file($wfile, '#');
		     }
			 
	  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  
	  $message="Prompting caller to enter SHG short code ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	  
	  
	  if($shg_code_rs=='00')
	  {
		 $link = mysql_connect("localhost","root", "evol@9944")
    or die("Data base connection failed");
   mysql_select_db("shtri_nidhi")
    or die("data base open failed");
	$file=$this->record_shg_name($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$record_confirm,$record_propmt,$language); 
//echo "insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())";
	if($caller!=$test_vonumber)
	{
	mysql_query("insert into SHG_NAMES_RECORD(VO_ID,IVRS_ID,FILE,CREATED_DATE) VALUES ('$void_code','$ivr_call_id','$file',now())");
	}
	
	mysql_close($link);
		mssql_close($link);
		 $agi->Hangup();
		  exit;
		  }
		  
	 
	 $shg_code_short=$shg_code_rs;
	  $shg_dist_code=substr($void_code,2,2);
	  //$shg_table="shg_dist_".$shg_dist_code;
      $shg_mandal_code=substr($void_code,4,2);
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  SHORT_SHG_CODE='$shg_code_short'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  
	  
	  $message="SHG DETAILS: TRANS_SHG_ID: $shg_code ,  SHG_NAME: $shg_name, SHG_SHORT_CODE: $shg_code_short";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	  
	  $shg_name=str_replace(' ','_',$shg_name);
      $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	if($caller=='9494464446')
		{
	 $shg_name='bhagyasri';
		}

	//STARTING CHECKING SHG OVER DUE By Ashok
	$triggered_loan_count_res=mssql_fetch_array(mssql_query("select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'"));
		$triggered_loan_count=$triggered_loan_count_res[0];
		$message="Triggered Loans: select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

			$curr_odos=0;
		
			if($triggered_loan_count > 0)
			{
				$shg_overdue_res=mssql_fetch_array(mssql_query("select OVERDUE,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
				$shg_ovrdue=$shg_overdue_res[0];
				if($shg_ovrdue>0)
				{
				   $message="SHG HAS OVERDUE AMT $shg_ovrdue is gretaer than 0 in SAMSNBSTG.SN.SHG_OVERDUE_STATUS,system checks shg OD AND OS In veedi varthakula loan";
				   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

				   $curr_shg_odos=0;
				   $curr_shg_odos=$shg_overdue_res[1]+$shg_overdue_res[2];
				      if($curr_shg_odos<=100)
				      {
					$curr_odos=$curr_shg_odos;
				  	$message="SHG HAS CURRENT_OD,CURRENT_OS:$curr_odos Less than 100, so for this shg we are allowing Veedi Varhakula loan. ";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
				      }
				     else
				      {
					if($x>='1')
					{
			
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_sn_overdue";
						$agi-> stream_file($wfile, '#');
						$this->play_amount($shg_ovrdue,$agi);
						$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    					$agi-> stream_file($wfile, '#');
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
						$agi-> stream_file($wfile, '#');
						$x=$x-1;
						$this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
						mssql_close($link);
						$agi->Hangup();
		  				exit;
		   			}else
		   			{
		    				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    				$agi-> stream_file($wfile, '#');
						mssql_close($link);
		   				$agi->Hangup();
		   				exit;
		   			}
				     }
				}

			}
			
	//ENDING CHECKING SHG OVER DUE By Ashok
	
	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	$bank_name_rs= mssql_fetch_array(mssql_query("select Bank_Code from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code'"));
	$bank_name=$bank_name_rs['Bank_Code'];


	if($overdue_amt>0 && $overdue_amt<=10000)
	{
			$message="SHG OVERDUE AMT $overdue_amt is gretaer than 0 and lessthan or equal to 10000,Promting the caller and continue";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue_10000_limit";
		$agi-> stream_file($wfile, '#');
		$is_over_due='Y';	
	}
	 
	
	  if($overdue_amt>10000)
	{	
		$message="SHG OVERDUE AMT $overdue_amt is gretaer than 10000,Promting the caller and proceed to another SHG";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

	if($x>='1')
		{
			
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_overdue";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($overdue_amt,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/bank_names/$bank_name";
	    $agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_pay_overdue";
		$agi-> stream_file($wfile, '#');
		/*
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/proceed_through_option8";
		$agi-> stream_file($wfile, '#');
		*/
		$x=$x-1;
		$this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	}	
	
	 if($status>='1')
	 {
	 
	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	if($list == $path)
  {

	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
		  

	$shg_name=strtolower($shg_name);
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);	
	$shg_name=str_replace('/','_',$shg_name);
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";	
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
    $shg_name=str_replace('.','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace(' ','_',$shg_name);
	$shg_name=str_replace('/','_',$shg_name);
	$shg_name=str_replace(':','_',$shg_name);
	$shg_name=str_replace(')','_',$shg_name);
	$shg_name=str_replace('-','_',$shg_name);	
	$shg_name=str_replace('(','_',$shg_name);		
	$shg_name=str_replace(';','_',$shg_name);
	$shg_name=str_replace(',','_',$shg_name);	
	$shg_name=str_replace('___','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
	$shg_name=str_replace('__','_',$shg_name);
  
  }	
$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
$file_status=file_exists($path); 
  
  if($file_status != 'true')
  {
  $shg_name=preg_replace("![^a-z0-9]+!i", "_", $shg_name);  
  } 
  	 $path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	
	if($list == $path)
  {
  	  	$message="SUCCESS: SHG Audio file $path exists,Playing the SHG name ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_vo";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name";
		$agi-> stream_file($wfile, '#');
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sangham";
		$agi-> stream_file($wfile, '#');
	    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 3000, 1);
	    $status=$res_dtmf ["result"];
	  }
	  else
	  {
	  	$message="FAIL: NO SHG Audio file $path ,Playing the SHG SHORT CODE ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/you_have_entered";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($shg_code_rs,$agi);
		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_press2", 7000, 1);
	    $status=$res_dtmf ["result"];
	  }
		
	  
		  }
		if($status=="1")
		{
			$status_pop_fund=2;
			/*
			$pop_stat_rs=mssql_query("select TRANS_VO_ID from vo_info where TRANS_VO_ID='$void_code' and IS_POP='Y'");
			$stat_pop=mssql_num_rows($pop_stat_rs);
			$pop_stat_shg_rs=mssql_query("select TRANS_SHG_ID from SHG_INFO where TRANS_SHG_ID='$shg_code' and IS_POP='Y'");
			$pop_stat_shg=mssql_num_rows($pop_stat_shg_rs);
		if($stat_pop>=1 && $pop_stat_shg>=1)
   		{
 		$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/press1_for_popfund", 8000, 1);
	    $status_pop_fund=$res_dtmf ["result"];
			}else
			{
				$status_pop_fund=2;
			}
			*/
			
			if($status_pop_fund=='1')
			{
				
				//$x=3;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/stop_pop_loan";
		$agi-> stream_file($wfile, '#');
		//$this->pop_hh_request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$shg_code,$is_over_due,$due_id_lst);
		mssql_close($link);
			$agi->Hangup();
			exit;	
			}
			elseif($status_pop_fund=='2')
			{
				//TRANS_VO_ID
	$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE,CIF_RECOVERY_PERCENTAGE from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
    $vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	
				
	$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
    $shg_active_stat=$shg_active_rs['IS_VALID'];
	if($shg_active_stat=='Y')
	{
	
	$message="SUCCESS: SHG SB Account Details are valid (select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	}
	else
	{
		$message="FAIL: SHG SB Account Details are invalid (select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat ,prompting the caller and proceed to another SHG";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	   if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/sb_details_invalied_old";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
		$agi->Hangup();
		  exit;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         
	
	}
	
$check_shg_samrudhi=$this->check_shg_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);
					if($check_shg_samrudhi == 0)
					{
						$message="FAIL: SHG SAMRUDHI FAILED ";
						$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	  				 if($x>='1')
					{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/shg_samrudhi_denied";
						$agi-> stream_file($wfile, '#');
						 $x=$x-1;
						$this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		mssql_close($link);
						$agi->Hangup();
		 				 exit;
		
					   }else
					   {
					    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
					    $agi-> stream_file($wfile, '#');
		mssql_close($link);
					   $agi->Hangup();
					   exit;
					   }
	}else{
		$message="SUCCESS: SHG SAMRUDHI PASSED ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	}




	$shg_grade_dist_code=substr($void_code,2,2);
	$shg_grade_mandal_code=substr($void_code,4,2);
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT(nolock) where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
	$mms_grade=$vo_grade_rs['GRADE'];
	
	
    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select A.GRADE,A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs_rej['GRADE'];
	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
	$message="Validating VO GRADE and ACTUAL_CREDIT_LIMIT,MMS Grade , VO Grade: $vo_grade,VO ACTUAL_CREDIT_LIMIT : $vo_actual_credit,MMS Grade :$mms_grade ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
//		if($vo_grade=='E')
//	{
//		$message="VO grade $vo_grade ,disconnecting ";
//		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
//	
//	if($vo_grade=='F')
//	{
//		$message="VO grade $vo_grade ,disconnecting ";
//		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
	
	if($vo_actual_credit=='0')
	{
		$message="vo_actual_credit $vo_actual_credit ,disconnecting ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	
		}


	
//	if(($mms_grade=='E' || $mms_grade=='F') && ($vo_grade=='C'||$vo_grade=='D'||$vo_grade=='F')) 
//	{
//		$message="VO grade $vo_grade and MMS grade $mms_grade,disconnecting ";
//		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
//		
//	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
//		   $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//		   $agi->Hangup();
//		   exit;
//	}
		

	if($mms_grade=='E' || $mms_grade=='F' || $mms_grade=='C' || $mms_grade=='D' || $vo_grade=='C'||$vo_grade=='D'||$vo_grade=='E'|| $mms_grade=='F' ) 
	{
		$message="VO grade $vo_grade and MMS grade $mms_grade,disconnecting ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_in_fgrade";
		   $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
	}


$search_purpose_ig="'Agriculture','Dairy','Income generation Activity','Weavers'";
$search_purpose_non_ig="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
	

	   		$x=3;
		//current
		$message="SHG LIMITS in project $project at 5th place";
		$this->log_ivr($ivr_call_id,$message);
	
		$shg_limit_array=$this->shg_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code);
		$shg_max_loans_total=$shg_limit_array[$shg_code]['shg_max_loans_total'];
		$shg_max_loans_ivrs=$shg_limit_array[$shg_code]['shg_max_loans_ivrs'];
		$shg_max_credit_limit=$shg_limit_array[$shg_code]['shg_max_credit_limit'];
		
	   $member_limit=$this->shg_member_limit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs);
	   
	   
	      $message="member_limit: $member_limit";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	   
	    
		if($member_limit>=1)
		  {
		  	
		  $message="SUCCESS: member_limit: $member_limit Greater Than or Equal to 1";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
	   	
		$amt_stat='Y';
	$this->veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	       }
		   else
		   {
		   
		     $message="FAIL: member_limit: $member_limit Less Than 1 , Prompting morethan_".$shg_max_loans_ivrs."_loans ";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);	
		   	
		  $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_".$shg_max_loans_ivrs."_loans", 5000, 1);
	      $more_res=$res_dtmf ["result"];
		  if($more_res==1)
		  {
		  $x=3;
		  $this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		  $agi->Hangup();
		    }
			else
			{
		mssql_close($link);
			 $agi->Hangup();
			 $agi->Hangup();
			 exit;
			}
		   
		   }	
		}
		else
		{
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	     
		}
	
		}
	
		if($status=="2")
		{
		$this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		}
		else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		   $agi->Hangup();
		   exit;
		   }
	
	         }	
		
		
	}
	  else
	      {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
		    $agi->Hangup();
			exit;
		   }
	     }
	}

function veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos){
			
		$test_vonumber=$GLOBALS['test_vonumber'];
		$project=62;
     		$db_filed="category";
		$type='mem_mode';
		$length='5';
		$play_msg="mem_category";
		$x='3';
 //$member_type=$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
 
 
 
 		$ivrs_array='';
		$ivrs_array=array();
		$ivrs_array['file']="mem_category";
		$ivrs_array['retries']=3;
		$ivrs_array['max_digits']=1;
		$ivrs_array['timeout']=5000;
		$ivrs_array['no_input']="invaliddata";
		$ivrs_array['verification_string']="A^response";
				
		$ivrs_array['1']="pop";
		$ivrs_array['2']="poor";
		
		$member_type=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
		
		
		if($member_type == ''){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
			$agi->Hangup();
			exit;
		}
	
	if($member_type == "pop"){
		$member_type="Y";
	}elseif($member_type == "poor"){
		$member_type="N";
	}
		
	$length='2';
	$play_msg='two_digit_member_id';
	$type='sthreenidhi';
	$x='3';
	$db_filed=$member_type;
	
	
	$ivrs_array='';
		$ivrs_array=array();
		$ivrs_array['file']="two_digit_member_id";
		$ivrs_array['retries']=3;
		$ivrs_array['max_digits']=2;
		$ivrs_array['timeout']=5000;
		$ivrs_array['no_input']="invaliddata";
		$ivrs_array['verification_string']="A^you_entered*ND^response";
		$ivrs_array['response_type']="digits";
				
		$member_short_code=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
		
		
		if($member_short_code == ''){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
			$agi->Hangup();
			exit;
		}
	
	//$member_short_code=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE,IS_POP_MEM from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and SHORT_CODE='$member_short_code' "));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
  $IS_POP_MEM=$member_id_rs['IS_POP_MEM'];
  
  		if($member_id < 1){
			if($x > 1){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_id_wrong";
			$agi-> stream_file($wfile, '#');	
			$x=$x-1;
			$this->veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		mssql_close($link);
			$agi->Hangup();
			exit;
			}else{
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
			$agi->Hangup();
			exit;
			}
			}	
  
 				
 				if($IS_POP_MEM != $member_type){
					if($IS_POP_MEM =='N' && $member_type=='Y')
						{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_pop";
						$agi-> stream_file($wfile, '#');	
						
						}
						if($IS_POP_MEM == 'Y' && $member_type == 'N')
						{
						$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_not_non_pop";
						$agi-> stream_file($wfile, '#');
						}
					
					if($x>='1')
					{
					$x=$x-1;
					$this->veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		mssql_close($link);
					$agi->Hangup();
					exit;
					}else{
		mssql_close($link);
					$agi->Hangup();
					exit;		
					}
				
				}
	           	
 
/* if($member_age > 60 && $member_age != 0){
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_greater_60";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		mssql_close($link);
	$agi->Hangup();
	exit;	
 }*/

  if(($member_age =="" || $member_age=="NULL")){
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_not_update";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	mssql_close($link);
	$agi->Hangup();
	exit;
  }
 elseif($member_age >= 63 || $member_age <= 18)
 {
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_btw_1860";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	mssql_close($link);
	$agi->Hangup();
	exit;
 }
 
 
	$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and PROJECT_TYPE NOT IN ('71','72','74','43')"));
	
	$member_prev_loan_count_live = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE NOT IN ('71','72','74','43')"));

$member_rej_cnt_lng = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and PROJECT_TYPE NOT IN ('71','72','74','43')"));

//$member_repaid_loans = mssql_num_rows(mssql_query("select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED='1'"));
$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1' and PROJECT_TYPE NOT IN ('71','72','74','43')"));

		if($member_prev_loan_count == 0 ){
		 $member_repaid_loans=0;
		}


	$member_corpus_loans_applied=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and project_type='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
	
	$member_corpus_loans_applied_live=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id' and project_type='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
	
	$member_corpus_loans_rejected=mssql_num_rows(mssql_query("select VO_ID from shg_loan_application(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and project_type='53' and STATUS_ID='11' "));
	
	//$member_corpus_loans_repaid=mssql_num_rows(mssql_query("select VO_ID from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED='1' "));
	$member_corpus_loans_repaid=0;
	
	
	$member_corpus_loans=$member_corpus_loans_applied+$member_corpus_loans_applied_live-$member_corpus_loans_rejected-$member_corpus_loans_repaid;
		
	if($member_corpus_loans == 1){
		$member_outstanding_rs=mssql_fetch_array(mssql_query("select OUTSTANDING from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code'"));
 $member_outstanding=$member_outstanding_rs['OUTSTANDING'];
 if($member_outstanding < 10000 ){
 	$member_corpus_loans=0;
 }
	}
		
	$member_prev_loan_count=$member_prev_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_corpus_loans;
	
	$message="Member loans ".$member_prev_loan_count." : ".$member_rej_cnt_lng." : ".$member_prev_loan_count_live." : ".$member_repaid_loans.":".$member_corpus_loans;
	$this->log_ivr($ivr_call_id,$message);	


	if($member_prev_loan_count>=1)
	{
	$member_outstanding_query=mssql_query("select OUTSTANDING from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code'");
	if(mssql_num_rows($member_outstanding_query) > 0){
		
	
	$member_outstanding_rs=mssql_fetch_array($member_outstanding_query);
	$member_outstanding=$member_outstanding_rs['OUTSTANDING'];
	
	$message="Member Outstanding ".$member_outstanding;
	$this->log_ivr($ivr_call_id,$message);	

	
		 if($member_outstanding < 10000 ){
		 	$member_prev_loan_count=$member_prev_loan_count-1;
		 	$message="Member Outstanding less than 10000,  member  $member_id can apply one more loan.".$member_prev_loan_count;
			$this->log_ivr($ivr_call_id,$message);	

		 }
	}
	}

	
	if($member_prev_loan_count < 0 ){
	$member_prev_loan_count=0;
	}
	
	
	
	
	/*if($member_prev_loan_count>=1 || $member_repaid_loans>=1)*/
	if($member_prev_loan_count>=1)
	{
	
	$message="Member loan_already_applied : member_prev_loan_count:".$member_prev_loan_count." member_repaid_loans : ".$member_repaid_loans;
	$this->log_ivr($ivr_call_id,$message);	
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this-> veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		mssql_close($link);
	$agi->Hangup();
	exit;	
	}	

	if(strlen($member_mobile_num) == 10){
		
	}else{
    $length='10';
	$play_msg='mobile_number';
	$type='mobile';
	$x='3';
	$upper_limit='2';
	$db_filed=$member_type;
	$member_mobile=$this->get_digits($agi,$x,$language,$db_filed,$type,$length,$play_msg,$upper_limit,$void_code,$shg_code);
	
    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$member_mobile'"));
    $start_digit=substr($member_mobile,0,1);
	 	
	if($member_mobile>'1' && strlen($member_mobile)=='10' && $mobile_count<='1'  && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6') )
	{
		if($caller!=$test_vonumber)
		{
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$member_mobile'  where SHG_ID='$shg_code' and SHORT_CODE='$member_short_code'");	
		}
	}
	else
	{
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/mobile_not_registered";
	$agi-> stream_file($wfile, '#');
	if($x>='1')
		{
	 $x=$x-1;
	 $this->veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		mssql_close($link);
	 $agi->Hangup();
	exit;
	 }else{
		mssql_close($link);
	$agi->Hangup();
	exit;		
	}
	
	}
	}
	

		$db_filed="reason";
		$type='why_loan';
		$length='5';
		$play_msg="member_loan_reason_new";
		$x='3';
		
//		$ivrs_array='';
//		$ivrs_array=array();
//		$ivrs_array['file']="IG_1_NONIG_2";
//		$ivrs_array['retries']=3;
//		$ivrs_array['max_digits']=1;
//		$ivrs_array['timeout']=5000;
//		$ivrs_array['no_input']="invaliddata";
//		$ivrs_array['verification_string']="A^response";
//				
//		$ivrs_array['1']="IG";
//		$ivrs_array['2']="NONIG";
//		
//		$loan_category=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
//		
//		if($loan_category == ''){
//			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
//		    $agi-> stream_file($wfile, '#');
//		mssql_close($link);
//			$agi->Hangup();
//			exit;
//		}
		
		
		$ivrs_array='';
		$ivrs_array=array();
		$ivrs_array['file']="CONSUMPTION_1_IG_2_PRODUCER_3";
		$ivrs_array['retries']=3;
		$ivrs_array['max_digits']=1;
		$ivrs_array['timeout']=5000;
		$ivrs_array['no_input']="invaliddata";
		$ivrs_array['verification_string']="A^response";
		
		$ivrs_array['1']="emergency";
		$ivrs_array['2']="IG";
		$ivrs_array['3']="PRODUCER";
		
		$loan_type=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
		
		if($loan_type == ''){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
			$agi->Hangup();
			exit;
		}
		
		if($loan_type == "emergency"){
		$ivrs_array='';
		$ivrs_array=array();
		$ivrs_array['file']="health_1_education_2_marriage_3";
		$ivrs_array['retries']=3;
		$ivrs_array['max_digits']=1;
		$ivrs_array['timeout']=5000;
		$ivrs_array['no_input']="invaliddata";
		$ivrs_array['verification_string']="A^response";

		
		$ivrs_array['1']="health";
		$ivrs_array['2']="education";
		$ivrs_array['3']="marriage";
		$loan_category="NONIG";	
		}else{
		$ivrs_array='';
		$ivrs_array=array();
		$ivrs_array['file']="hlp_activity_code";
		$ivrs_array['retries']=3;
		$ivrs_array['max_digits']=3;
		$ivrs_array['timeout']=5000;
		$ivrs_array['no_input']="invaliddata";
		$ivrs_array['verification_string']="A^loan_for*A^loan_activities/response*A^applying";

		
		$ivrs_array['101']="101";
		$ivrs_array['102']="102";
		$ivrs_array['103']="103";
		$ivrs_array['104']="104";
		$ivrs_array['105']="105";
		$ivrs_array['106']="106";
		$ivrs_array['107']="107";
		$ivrs_array['108']="108";
		$ivrs_array['109']="109";
		$ivrs_array['201']="201";
		$ivrs_array['202']="202";
		$ivrs_array['203']="203";
		$ivrs_array['204']="204";
		$ivrs_array['205']="205";
		$ivrs_array['206']="206";
		$ivrs_array['207']="207";
		$ivrs_array['208']="208";
		$ivrs_array['209']="209";
		$ivrs_array['210']="210";
		$ivrs_array['301']="301";
		$ivrs_array['302']="302";
		$ivrs_array['303']="303";
		$ivrs_array['304']="304";
		$ivrs_array['305']="305";
		$ivrs_array['306']="306";
		$ivrs_array['307']="307";
		$ivrs_array['308']="308";
		$ivrs_array['309']="309";
		$ivrs_array['310']="310";
		$ivrs_array['311']="311";
		$ivrs_array['312']="312";
		$ivrs_array['313']="313";
		$ivrs_array['314']="314";
		$ivrs_array['315']="315";
		$ivrs_array['316']="316";
		$ivrs_array['317']="317";
		$ivrs_array['318']="318";
		$ivrs_array['319']="319";
		$ivrs_array['320']="320";
		$ivrs_array['321']="321";
		$ivrs_array['401']="401";
		$ivrs_array['402']="402";
		$ivrs_array['403']="403";
		$ivrs_array['404']="404";
		$ivrs_array['405']="405";
		$ivrs_array['406']="406";
		$ivrs_array['407']="407";
		$ivrs_array['408']="408";
		$ivrs_array['409']="409";
		$ivrs_array['410']="410";
		$ivrs_array['411']="411";
		$ivrs_array['412']="412";
		$ivrs_array['413']="413";
		$ivrs_array['414']="414";
		$ivrs_array['501']="501";
		$ivrs_array['502']="502";
		$ivrs_array['503']="503";
		$ivrs_array['504']="504";
		$ivrs_array['505']="505";
		$ivrs_array['506']="506";
		$ivrs_array['507']="507";
		$ivrs_array['508']="508";
		$ivrs_array['509']="509";
		$ivrs_array['510']="510";
		$ivrs_array['511']="511";
		$ivrs_array['512']="512";
		$ivrs_array['513']="513";
		$ivrs_array['514']="514";
		$ivrs_array['515']="515";
		$ivrs_array['516']="516";
		$ivrs_array['517']="517";
		$ivrs_array['518']="518";
		$ivrs_array['519']="519";
		$ivrs_array['520']="520";
		$ivrs_array['521']="521";
		$ivrs_array['522']="522";
		$ivrs_array['523']="523";
		$ivrs_array['524']="524";
		$ivrs_array['525']="525";
		$ivrs_array['526']="526";
		$ivrs_array['527']="527";
		$ivrs_array['528']="528";		
		$loan_category="IG";
		
		}
		
		

		
		$reason_loan=$this->ivrs_menu($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$ivrs_array);
		
		if($reason_loan == ''){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    $agi-> stream_file($wfile, '#');
		mssql_close($link);
			$agi->Hangup();
			exit;
		}
		
		
		if($loan_category == "IG"){
			$reason_loan_array=mssql_fetch_array(mssql_query("select PURPOSE_NAME from PURPOSE_MASTER_WEB(nolock) where PURPOSE_ID='$reason_loan' and GENERAL='Y'"));
			$reason_loan_text=$reason_loan_array['PURPOSE_NAME'];
			
			$message="Fetching LOAN purpose from PURPOSE_MASTER_WEB for IG loans :select PURPOSE_NAME from PURPOSE_MASTER_WEB where PURPOSE_ID='$reason_loan' and GENERAL='Y' :: ".$reason_loan_text;
	$this->log_ivr($ivr_call_id,$message);
			
			if($reason_loan_text != ""){
				$reason_loan=$reason_loan_text;
			}
			else 
			{ 
		  	   if($x>='1') 
			    { 
				$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/notgeneral-purpose"; 
				$agi-> stream_file($wfile, '#'); 
		 		$x=$x-1; 
				$amt_stat='Y'; 
				$this->veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ; 
				mssql_close($link); 
				$agi->Hangup(); 
				exit; 
		   	   }else 
		   	   { 
		   		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry"; 
		  		$agi-> stream_file($wfile, '#'); 
				mssql_close($link); 
		  		$agi->Hangup(); 
		  		exit; 
		   	   } 
			}
		}
  //$reason_loan =$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);

	
	
	
	
    if($amt_stat=='Y')
	{
		$db_filed="required_loan";
		$type="amount";
		$length="9";
		$play_msg="member_required_loan";
		$x='3';
	 $loan_amount=$this->vo_details($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
	 }
	 
	 if($loan_amount>='1')
	 {
//		 if($health=='NO')
//		 {
//		$db_filed="reason";
//		$type='why_loan';
//		$length='5';
//		$play_msg="member_loan_reason_new";
//		$x='3';
//  $reason_loan =$this->vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
//		 }
//		 else
//		 {
//			$reason_loan='Emergency Needs/Health';
//		 }
  //echo "came hetereerr";
 
  if($reason_loan=='Marriage')
   {
       //$db_filed="category";
		//$type='mem_mode';
		$length='5';
		$play_msg="get_marriage_amount";
		//$x='3';
  $marriage_amt=$this->get_marriage_amount($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);
  
  if($loan_amount<=$marriage_amt)
  {
  
  }
  else
  {
		  if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loanamt_greater_marriage_amt";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		$amt_stat='Y';
	$this->veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
		   }else
		   {
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		  $agi-> stream_file($wfile, '#');
		mssql_close($link);
		  $agi->Hangup();
		  exit;
		   }
		
  
  
  }
  
   }
 
 if(($reason_loan!='')&&($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && $member_prev_loan_count=='0' && strlen($member_id)>1))
 {
  $message="CONDITION 2 WILL CHECK HERE";
	$this->log_ivr($ivr_call_id,$message);
 
 $duration='12';
 $etime=date('Y-m-d H:i:s');
 

 if( $reason_loan == "health" && $loan_amount > 15000 )
{
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_less_25000";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
}

 /*
 if(($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Emergency Needs/Health')&&$loan_amount>15000)
{
$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_less_25000";
	$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	$this->request_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
}
*/
if($loan_category == "NONIG")
{
$duration='24';
}


 $vo_actual_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
 $vo_actual_credit=$vo_actual_credit_rs['ACTUAL_CREDIT_LIMIT'];
 $vo_credit_pop=$vo_actual_credit_rs['POP_CREDIT_LIMIT'];
 $vo_credit_non_pop=$vo_actual_credit_rs['NONPOP_CREDIT_LIMIT']; 
 
 
 if($member_type=='Y')
     {
	 $member_cat='pop';
	 $search_cat='0';
	 $vo_cat_actual_limit=$vo_credit_pop;
	 //$tbl_filed='current_limit_pop';
	  //$vo_credit_pop=intval(ceil($vo_actual_credit/2));
	
	 
	 	if($loan_category == "IG")
		{
		$vo_fixed_credit=intval(ceil($vo_credit_pop*0.85));
		$tbl_filed='current_limit_pop_ig';
		$search_purpose="'Agriculture','Dairy','Income generation Activity','Weavers'";
		$search_cr_limit='credit_limit_pop_ig';
		$credit_lt_type="current_limit_pop_ig";
		}
		if($loan_category == "NONIG")
        {
	  $vo_fixed_credit=intval(floor($vo_credit_pop*0.15));
	  $tbl_filed='current_limit_pop_non_ig';
	  $search_cr_limit='credit_limit_pop_non_ig';
	  $search_purpose="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
	  $credit_lt_type="current_limit_pop_non_ig";
        }
		
	    }
		if($member_type=='N')
		{
			
		//$vo_credit_non_pop=intval(floor($vo_actual_credit/2));	
		$member_cat='non-pop';
		$search_cat='1';
		$vo_cat_actual_limit=$vo_credit_non_pop;
		 //$tbl_filed='current_limit_nonpop';
		 
		 
		  if($loan_category == "IG")
		{
		$vo_fixed_credit=intval(ceil($vo_credit_non_pop*0.85));	
		$tbl_filed='current_limit_nonpop_ig';
		$search_cr_limit='credit_limit_nonpop_ig';
		$search_purpose="'Agriculture','Dairy','Income generation Activity','Weavers'";
		$credit_lt_type="current_limit_nonpop_ig";
		}
		
		if($loan_category == "NONIG")
        {
		$vo_fixed_credit=intval(floor($vo_credit_non_pop*0.15));
	    $tbl_filed='current_limit_nonpop_non_ig';
		$search_cr_limit='credit_limit_nonpop_non_ig';
		$search_purpose="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
		$credit_lt_type="current_limit_nonpop_non_ig";
        }
		
		 }
	

	$applied_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE='1' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' ")); 

	
        $applied_amt = $applied_rs['AMT'];
		
		$applied_rs_live=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='1'"));
        $applied_amt_live = $applied_rs_live['AMT_LIVE'];
		$applied_amt_live=intval($applied_amt_live);

		
		$vo_rej_amt_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE='1'  and STATUS_ID='11'"));		
$vo_rej_amt=$vo_rej_amt_rs['AMT_REJ'];
$vo_rej_amt=intval($vo_rej_amt);



//commented for automation
/*
   $vo_fixed_credit_rs=mssql_fetch_array(mssql_query("select $search_cr_limit from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
       $vo_fixed_credit = $vo_fixed_credit_rs[$search_cr_limit];
	   */
	   
	   $vo_repaid_cat_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='$search_cat' and PROJECT_TYPE='1'"));
        $repaid_cat_total = $vo_repaid_cat_rs['AMT_REPAID'];
		//added for automation
		$applied_total=$applied_amt+$applied_amt_live-$vo_rej_amt-$repaid_cat_total;
		
	if($applied_total < 0){
			$applied_total=0;
			
	$message="VO Repaid AMT is greater than applied amt,resetting the outstanding to 0, applied_total :$applied_total";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					
		}
		
		
		$vo_cat_limit=$vo_cat_actual_limit-$applied_total;
			if($reason_loan=='Agriculture'||$reason_loan=='Dairy'||$reason_loan=='Income generation Activity'||$reason_loan=='Weavers')
		{
			// $repaid_cat_total=intval(ceil($repaid_cat_total*0.85));
			 //$repaid_cat_total=intval($repaid_cat_total);
			 $vo_credit_lt=intval(ceil($vo_cat_limit*0.85));
		}
		if($reason_loan=='Education'||$reason_loan=='Marriage'||$reason_loan=='Emergency Needs/Health')
		{
			 //$repaid_cat_total=intval(floor($repaid_cat_total*0.15));
			 //$repaid_cat_total=intval($repaid_cat_total);
			  $vo_credit_lt=intval(ceil($vo_cat_limit*0.15));
		}
		
		$message="shg_outstanding_amt in project $project at 5th place";
		$this->log_ivr($ivr_call_id,$message);
				
	$tcs_shg_outstanding_amt=$this->shg_outstanding_amt($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code);

$tcs_shg_drawing_power=$shg_max_credit_limit-$tcs_shg_outstanding_amt;

 $message="VALIDATING SHG Drawing power tcs_shg_drawing_power: $tcs_shg_drawing_power = shg_max_credit_limit: $shg_max_credit_limit  - tcs_shg_outstanding_amt: $tcs_shg_outstanding_amt";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

		//vo_amount
//$vo_total_credit_rs=mssql_fetch_array(mssql_query("select  credit_limit_pop+credit_limit_nonpop from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        //$vo_total_credit=$vo_total_credit_rs[0];
        $vo_total_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	 $vo_total_credit=$vo_total_credit_rs['ACTUAL_CREDIT_LIMIT'];

 
$vo_outstanding=$this->vo_outstanding($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'62');
	
$vo_drawing_power=$vo_total_credit-$vo_outstanding;


$vo_outstanding_tcs=$this->vo_outstanding_tcs($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'62');
	
$vo_drawing_power_tcs=$vo_total_credit-$vo_outstanding_tcs;
	$project=62;
$vo_category_drawing_power=$this->play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$credit_lt_type,$project);			

 $message="VALIDATING LOAN AMOUNT vo_credit_lt:$vo_category_drawing_power Greater than Equal loan_amount:$loan_amount,Loan amount $loan_amount LESS THAN EQUAL VO Drawing power $vo_drawing_power,VO drawing power from TCS Tables $vo_drawing_power_tcs Greater than Equal Loan AMT $loan_amount,loan_amount: $loan_amount LESS THAN EQUAL tcs_shg_drawing_power: $tcs_shg_drawing_power";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
		
			  
  //if($vo_credit_lt>=$loan_amount && $shg_lt_max<=150000 && $shg_limit_max_tcs<=150000 && $vo_credit_max_tcs<=$vo_total_credit && $mms_credit_max_tcs<=$mms_total_credit )
  //{ 
  if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount && $loan_amount <= $tcs_shg_drawing_power)
  {

	$message="Drawing Power Validation SERP 5";
 	$this->log_ivr($ivr_call_id,$message);  
 
 //exit;
 //$echo_cmd="/bin/echo ivr_id $ivr_call_id uni $unique_id  credit_limit $vo_credit_lt >> /tmp/ivr.txt";
 //exec($echo_cmd);
 
 
 $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select * from IVRS_LOAN_REQUEST_LIVE(nolock) where  SHG_ID='$shg_code' and PROJECT_TYPE='62' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;

//Ashok Adding Current od and os to this member Start
		
				if($curr_odos!=0)
				{
					$shg_pending_ods_count=mssql_num_rows(mssql_query("select TOP 1 1 from SHG_MEMBER_PENDING_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
					if($shg_pending_ods_count>=1)
					{
					  $curr_odos=0;
				   	  $message="Already Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS for shg $shg_code";
				   	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					}
				}

//Ashok Adding Current od and os to this member End

	  
	$PROJECT_TYPE='62'; 

	$this->chk_ln_status($shg_code,$member_id,$PROJECT_TYPE,$ivr_call_id);
 
	$loan_amount=$loan_amount+$curr_odos;
	
$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST_LIVE(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivr_call_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos')";	

if($caller!=$test_vonumber)
{
mssql_query($loanRequestInsertQry);


//Ashok Adding Current od and os to this member Start
		
				if($curr_odos!=0)
				{
					$shg_pending_ods_count=mssql_num_rows(mssql_query("select TOP 1 1 from SHG_MEMBER_PENDING_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
					if($shg_pending_ods_count>=1)
					{
					  $curr_odos=0;
				   	  $message="Already Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS for shg $shg_code";
				   	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
					}
					else
					{
					  $shg_mem_ods_res=mssql_query("select SHG_ID,MEMBER_ID,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code' and CURRENT_OVERDUE>0");
					  $rec_count=0;
					  while($shg_mem_ods_row=mssql_fetch_array($shg_mem_ods_res))
					   {
						if($caller!=$test_vonumber)
						{
						   $pres_odos=$shg_mem_ods_row['CURRENT_OVERDUE']+$shg_mem_ods_row['CURRENT_OUTSTANDING'];
						   if($pres_odos<=100)
				      		    {
						      $shg_pending="INSERT INTO SHG_MEMBER_PENDING_OVERDUE_STATUS(SHG_ID,MEMBER_ID,CREATED_DATE,CURRENT_OVERDUE,CURRENT_OUTSTANDING,INSERTED_BY) VALUES ('".$shg_mem_ods_row['SHG_ID']."','".$shg_mem_ods_row['MEMBER_ID']."',GETDATE(),'".$shg_mem_ods_row['CURRENT_OVERDUE']."','".$shg_mem_ods_row['CURRENT_OUTSTANDING']."','IVRS')";
						     mssql_query($shg_pending);

  						     $message="SHG HAS CURRENT_OD,CURRENT_OS as pres_odos :$pres_odos Less than 100, so for this shg we are inserting into at Veedhi Varthakula $shg_pending. ";
				   	             $this->php_log_ivr($ivr_call_id,$message);  
						    }
						}
						$rec_count++;
					   }
					$message="Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS from SAMSNBSTG.SN.SHG_OVERDUE_STATUS where shg $shg_code : rec_count:$rec_count :$shg_pending";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);					
					}
				}

//Ashok Adding Current od and os to this member End


}
$curr_odos=0;

        $message="INSERT Veedi varthakula Lead data to IVRS_LOAN_REQEUEST_LIVE : $loanRequestInsertQry";
        $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);

/*
  $vo_credit_rs2=mssql_fetch_array(mssql_query("select $tbl_filed from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        $vo_credit_lt_prev = $vo_credit_rs2[$tbl_filed];
		$vo_credit_lt_prev=intval($vo_credit_lt_prev);
		
		if($vo_credit_lt_prev<$loan_amount)
		{
			$used_from_repay=$loan_amount-$vo_credit_lt_prev;
			$used_from_ivr=$loan_amount-$used_from_repay;
			$loan_amount=$used_from_ivr;
			$current_limit=$vo_credit_lt_prev-$used_from_ivr;
		}else
		{
		$current_limit=$vo_credit_lt-$loan_amount;
		}
		*/
$current_limit=$vo_drawing_power-$loan_amount;


//$current_limit=$vo_credit_lt-$loan_amount;
if($caller!=$test_vonumber)
{
mssql_query("update  IVRS_VO_CREDIT_LIMIT set $tbl_filed='$current_limit'  where vo_id='$void_code'");
}

if($member_type=='Y')
     {
	$pop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_pop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$pop_lmt=$pop_lmt_rs['current_limit_pop'];
	
		 if($pop_lmt>=$loan_amount)
		 {
	 if($caller!=$test_vonumber)
	 {
	 mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_pop=current_limit_pop-$loan_amount  where vo_id='$void_code'");
	 }
		 }
	 }
	 if($member_type=='N')
		{
			
	$nonpop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_nonpop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$nonpop_lmt=$nonpop_lmt_rs['current_limit_nonpop'];
	
	if($nonpop_lmt>=$loan_amount)
	{
		if($caller!=$test_vonumber)
		{
		mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_nonpop=current_limit_nonpop-$loan_amount  where vo_id='$void_code'");
		}
	}
	   }
	   

$vo_credit_limit=$current_limit;

 $member_limit=$member_limit-1; 
 
 $message="VO Drawing Power after Loan request : $vo_credit_limit , Loans available in this SHG : $member_limit ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
   if($member_limit>=1 && $vo_credit_limit>=0)
   {
    $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_member_loan", 5000, 1);
	$value=$res_dtmf ["result"];
	
	$message="Promting for One more loan in this SHG, user entered  : $value";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	
	}else
	{
	$value=2;
	}
	if(intval($value)=='1')
	{
	$amt_stat='Y';
	$this->veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	}
	else
	{
	  if($vo_credit_limit>=0)
	  {
	  	
	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/one_more_shg_loan", 5000, 1);
	$value_shg=$res_dtmf ["result"];
	$message="Promting for more loans in Another SHG user entered  : $value_shg";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);$this->log_ivr($ivr_call_id,$message);
	  }
	  else
	  {
	  $value_shg=2;
	  }
	if(intval($value_shg)=='1')
	{
			
	$this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	
	}
	
	else
	{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/success";
	$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_in_shg_ac";
	$agi-> stream_file($wfile, '#');
	//$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/call_for_info";
	//$agi-> stream_file($wfile, '#');
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/thanku";
	$agi-> stream_file($wfile, '#');
	//mssql_close($link1);
		mssql_close($link);
	$agi->Hangup();
	exit;
	}
	}
 }
 else
 {

if($mms_credit_max_tcs>$mms_total_credit)
	 {
		$mms_dist_code=substr($void_code,2,2);
        $mms_mandal_code=substr($void_code,4,2); 
		$share = mssql_fetch_array(mssql_query("SELECT  SHARE_AMOUNT FROM SN.SHARE_BAL_FUN() where DISTRICT_ID='$mms_dist_code' and MANDAL_ID='$mms_mandal_code' "));
		$share_amount=$share['SHARE_AMOUNT'];
		if($share_amount<1000000)
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/share_captial";
		$agi-> stream_file($wfile, '#');
		$this->play_amount($share_amount,$agi);
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/paid_share_captial";
		$agi-> stream_file($wfile, '#');	
		}
		else
		{
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
		}
	 }
	 else
	 {
    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	$agi-> stream_file($wfile, '#');
	 }

 //$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_credit_limit";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	
	$this->veedi_varthakula_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		mssql_close($link);
	$agi->Hangup();
	exit;
 
   }
	
 }
 //echo  $loan_amount.$reason_loan.$member_type;
 
		}
		else
		  {
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/invaliddata";
		$agi-> stream_file($wfile, '#');
		 $x=$x-1;
		 $this->veedi_varthakula_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
		$agi->Hangup();
		    }
		



			
		}

  function chk_ln_status($shg_code,$member_id,$PROJECT_TYPE,$ivr_call_id)
  {

//Ashok start adding log for checking loan status b4 allowing
	$sla_ntsp_qry="select a.CREATED_DATE,a.VO_ID,a.STATUS_ID,a.SHG_MEM_LOAN_ACCNO,b.OUTSTANDING,b.IS_CLOSED from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO ";
	//$sla_ntsp_qry="select a.CREATED_DATE,a.VO_ID,a.STATUS_ID,a.SHG_MEM_LOAN_ACCNO,b.OUTSTANDING,b.IS_CLOSED from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED!='1' ";

	$sla_ntsp_res=mssql_query($sla_ntsp_qry);
	$sla_ntspno=mssql_num_rows($sla_ntsp_res);
	if($sla_ntspno>0)
	{
	  while($sla_ntsp_row=mssql_fetch_array($sla_ntsp_res))
	  {
		$myfile = fopen("/var/log/IVRS_FLOW_LOG/lnstatus_".date('Y-m-d').".log", "a");
		$msg_txt = "Date :".date('Y-m-d H:i:s')."\tivr_call_id:$ivr_call_id\tPROJECT_TYPE:$PROJECT_TYPE\tNTSP:\ta.CREATED_DATE:$sla_ntsp_row[CREATED_DATE]\ta.VO_ID:$sla_ntsp_row[VO_ID]\ta.SHG_ID:$shg_code\ta.MEMBER_LONG_CODE:$member_id\ta.STATUS_ID:$sla_ntsp_row[STATUS_ID]\ta.SHG_MEM_LOAN_ACCNO:$sla_ntsp_row[SHG_MEM_LOAN_ACCNO]\tb.OUTSTANDING:$sla_ntsp_row[OUTSTANDING]\tb.IS_CLOSED:$sla_ntsp_row[IS_CLOSED]";
		fwrite($myfile, $msg_txt);
		fwrite($myfile, "\n\n");
		fclose($myfile);
	  }
	}
	else
	{
	$sla_tsp_qry="select a.CREATED_DATE,a.VO_ID,a.STATUS_ID,a.SHG_MEM_LOAN_ACCNO,b.OUTSTANDING,b.IS_CLOSED from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED!='1' ";

	$sla_tsp_res=mssql_query($sla_tsp_qry);
	$sla_tspno=mssql_num_rows($sla_tsp_res);
	if($sla_tspno>0)
	{
	  while($sla_tsp_row=mssql_fetch_array($sla_tsp_res))
	  {
		$myfile = fopen("/var/log/IVRS_FLOW_LOG/lnstatus_".date('Y-m-d').".log", "a");
		$msg_txt = "Date :".date('Y-m-d H:i:s')."\tivr_call_id:$ivr_call_id\tPROJECT_TYPE:$PROJECT_TYPE\tTSP:\ta.CREATED_DATE:$sla_tsp_row[CREATED_DATE]\ta.VO_ID:$sla_tsp_row[VO_ID]\ta.SHG_ID:$shg_code\ta.MEMBER_LONG_CODE:$member_id\ta.STATUS_ID:$sla_tsp_row[STATUS_ID]\ta.SHG_MEM_LOAN_ACCNO:$sla_tsp_row[SHG_MEM_LOAN_ACCNO]\tb.OUTSTANDING:$sla_tsp_row[OUTSTANDING]\tb.IS_CLOSED:$sla_tsp_row[IS_CLOSED]";
		fwrite($myfile, $msg_txt);
		fwrite($myfile, "\n\n");
		fclose($myfile);
	  }
	}
	}

//Ashok end adding log for checking loan status b4 allowing


  }

  function php_log_ivr($ivr_call_id,$message)
  {
	$myfile = fopen("/var/log/ivrs/".$ivr_call_id.".log", "a");
	fwrite($myfile, $message);
	fwrite($myfile, "\n");
	fclose($myfile);
  }

}
?>
