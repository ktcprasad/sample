<?php

session_start();
if(!isset($_SESSION['TRANS_VO_ID']) && !isset($_SESSION['desk_deo_id']))
{
	exit("Validation: Session Expired, Please login again");
}

	 $shg_code= trim($_REQUEST[shg_id]);
	if($_SERVER[REMOTE_ADDR]=='182.19.66.187' || $_SERVER[REMOTE_ADDR]== '202.56.197.65')
	log_ivr($ivr_call_id,"------TEST---Project 74---".date("Y-m-d H:i:s a"));
	else	
	log_ivr($ivr_call_id,"----LIVE---Project 74---".date("Y-m-d H:i:s a"));

require_once 'common.php';
if($_REQUEST[Action]=='ValidateVO')
{	
	extract($_REQUEST);
	$caller= $mobile_number;
//sleep(5);
//$vo_name_array=mssql_query("select * from vo_info(nolock) where PRY_MOB_NO='$caller' and IS_ACTIVE!='N'");
$vo_id = $_SESSION['desk_TRANS_VO_ID'];

//Checking OB's registration 28-11-18 start
if(strlen($_SESSION['desk_IMEI_NO'])==15){
$vo_ob_registered_result=mssql_num_rows(mssql_query("GET_IVRS_OBS '$vo_id'"));
if($vo_ob_registered_result ==0)
{
echo $message="Validation: OBs registration is mandatory for loan request";exit;
}
}
//Checking OB's registration 28-11-18 end


///////////start vo meeting dae validation start 16-08-2017
$total_deposit_rs=mssql_fetch_array(mssql_query("VO_MEETING_DAY_VALIDATION '$vo_id'"));
		if($total_deposit_rs[FLAG]=='Y')
	{ echo $message="Validation:  Please update meeting date.";exit;}
///////////start vo meeting dae validation end 16-08-2017

///Allowing loans only to the VO'S  who applied tablet loan 22-11-2018
$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE,LOAN_ELIGBLE from VO_CREDIT_LIMIT where VO_ID='$vo_id'"));
$vo_grade=$vo_grade_rs['GRADE'];
if($vo_grade == 'A' || $vo_grade == 'B' || $vo_grade == 'C')
{
$vo_grade_array=mssql_query("VO_ELIGIBLE_GRADE_VALIDATION '$vo_id'");
$vo_grade_valid=mssql_num_rows($vo_grade_array);
if($vo_grade_valid==0){
echo $message="Validation:  Please apply VO TABLET loan.";exit;
}
///Allowing loans only to the VO'S  who applied tablet loan 22-11-2018
//Disabling web screen to A,B and C grade VO's after 15 days of dispatch of tablet PC start 26-11-2018
//echo $message="Validation:".$_SESSION['desk_IMEI_NO']." is IMEI";
log_ivr($ivr_call_id,$message);
if(strlen($_SESSION['desk_IMEI_NO'])!=15){
$vo_tablet_aquire_details=mssql_fetch_array(mssql_query("USP_GETTABLETACQUIRINGDETAILS '$vo_id'"));
$vo_tablet_aquire_days = $vo_tablet_aquire_details['DAYCOUNT'];
if($vo_tablet_aquire_days > 15)
{
echo $message="Validation:15 days time limit completed. VO can no longer apply loans through website. Please use tablet availed for appyling loan.";exit;
}
}
//Disabling web screen to A,B and C grade VO's after 15 days of dispatch of tablet PC end 26-11-2018
}


$vo_name_array=mssql_query("select * from vo_info(nolock) where TRANS_VO_ID='$vo_id' and IS_ACTIVE!='N'");
		$status_valied= mssql_num_rows($vo_name_array);

		$vo_name_array=mssql_fetch_array($vo_name_array);
		$void_code=$vo_name_array['TRANS_VO_ID'];
		$is_mepma=$vo_name_array['IS_MEPMA'];
		$DISTRICT_ID=$vo_name_array['DISTRICT_ID'];
		$MANDAL_ID=$vo_name_array['MANDAL_ID'];
		
		//Start Putting conditions for Documents Verification By Ashok
		$ms_qry="select IS_VERIFIED from MS_DOCS_VERIFIED where DISTRICT_ID='$DISTRICT_ID' and MANDAL_ID='$MANDAL_ID'";
		$ms_qry_res=mssql_query($ms_qry);
		$ms_qry_row=mssql_fetch_array($ms_qry_res);

		$vo_qry="select IS_VERIFIED from VO_DOCS_VERIFIED where VO_ID='$void_code'";
		$vo_qry_res=mssql_query($vo_qry);
		$vo_qry_row=mssql_fetch_array($vo_qry_res);
		//echo "SUCCESS";$_SESSION['vo_id']="$void_code";exit;
//echo "<br>".$message="ms_qry:$ms_qry\nvo_qry:$vo_qry\nDocuments submmition at ms and vo level. DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED],vo_IS_VERIFIED=$vo_qry_row[IS_VERIFIED]\n";	


		if($ms_qry_row[IS_VERIFIED]!='Y' && $vo_qry_row[IS_VERIFIED]!='Y')
		{
		echo $message="Validation: Documents not submmited at ms and vo level. DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED],vo_IS_VERIFIED=$vo_qry_row[IS_VERIFIED]\n";	
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		exit;
	
		}
		else
		{
		  if($ms_qry_row[IS_VERIFIED]!='Y')
		   {
			echo $message="Validation: Documents not submmited at ms level. ";//DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED]\n";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			exit;

		   }
		  if($vo_qry_row[IS_VERIFIED]!='Y')
		   {
			echo $message="Validation: Documents not submmited at vo level. ";//DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED],vo_IS_VERIFIED=$vo_qry_row[IS_VERIFIED]\n";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			exit;

		   }
		}
		//End Putting conditions for Documents Verification By Ashok


		if($status_valied>='1')
		{

		$message=" $caller Authenticated in VO_INFO  ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		}
		else
		{

		echo $message="Validation:  $caller is not present in VO_INFO ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		exit;
		}
		
		/*
		$vo_name_pin_stat=mssql_query("select * from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code' and PIN='$vo_pin'");
		$stat_pin= mssql_num_rows($vo_name_pin_stat);
		
		if($stat_pin=='0')
		{
		
		$valid_pin_rs=mssql_fetch_array(mssql_query("select PIN from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
		$valied_pin=$valid_pin_rs['PIN'];
		
		$message="VO PIN is  $valied_pin , Caller entered $vo_pin ";
		echo "Validation: Wrong PIN Entered";exit;
		
		}
		elseif($stat_pin=='1')
		{
		   //Vo:Spandana,khammam Urban,8186969919
		  //if($void_code != '012236010010101' )
		   {
		$check_urban=mssql_num_rows(mssql_query("select VO_ID from VO_RURALTOURBAN(nolock) where VO_ID='$void_code'"));
                if($check_urban == "1"){
                $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rural_to_urban";
                
                echo $message="Validation: VO_RURALTOURBAN ,Hangup the call  ";
                
		//mssql_close($link);
                exit;
                }
		    } //END Of Escape RU Condition
		}*/

		$check_urban=mssql_num_rows(mssql_query("select VO_ID from VO_RURALTOURBAN(nolock) where VO_ID='$void_code'"));
                if($check_urban == "1"){echo $message="Validation: VO_RURALTOURBAN ,Hangup the call";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
                 exit;  }
	################################## check_meeting_date ###########################################

	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE,LOAN_ELIGBLE from VO_CREDIT_LIMIT where VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs['GRADE'];
        $vo_loan_eligble=$vo_grade_rs['LOAN_ELIGBLE'];
	
	    $vo_dist_code=substr($void_code,2,2);
$allow_loan=0;
if($vo_grade == 'A' || $vo_grade == 'B' || $vo_grade == 'C')
{
$allow_loan=1;
}
else{
			$allow_loan=check_meeting_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}

		if($allow_loan == 0){
		exit("Validation: No access today");
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/no_access_today";			
		}

function check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs){
		$test_vonumber=$GLOBALS['test_vonumber'];
		/*$vo_samrudhi_validation=0;
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
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_50";
		$x=$x-1;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		   // mssql_close($link);
		  // exit;
		   }	
		}
		return $vo_samrudhi_validation;*/
$vo_samrudhi=mssql_fetch_array(mssql_query("select vo_id,samrudhi_percentage from samrudhi_percentage() where VO_ID='$void_code'"));
		$vs_vo_id=$vo_samrudhi['vo_id'];
		$vo_samrudhi_percentage=$vo_samrudhi['samrudhi_percentage'];
		return $vo_samrudhi_percentage;
	}


		$vo_samrudhi_percentage=check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);

		if($vo_samrudhi_percentage<50)
		{
		
		//echo $message="Validation: vo_samrudhi_below_50";
		echo $message="Validation: VO samrudhi percentage is :$vo_samrudhi_percentage below 50";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_50";
		//$agi-> stream_file($wfile, '#');
		//mssql_close($link);
		//$agi->Hangup();
		exit;
		}
		/*else{
			echo '<br>'.$message="vo_samrudhi SUCCESS : $vo_samrudhi_validation ";
		//$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		}*/
			
function check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code){

			$test_vonumber=$GLOBALS['test_vonumber'];
			$override=0;
			$recovery_count=mssql_num_rows(mssql_query("select  * from SAMSNBSTG.SN.VO_RECOVERY_STATUS(nolock) where vo_id='$void_code' "));
			if($recovery_count >= 1){
			$message=" select  * from SAMSNBSTG.SN.VO_RECOVERY_STATUS(nolock) where vo_id='$void_code' ";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
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
				//echo $message="Validation: Record Not Found in SAMSNBSTG.SN.VO_RECOVERY_STATUS(nolock) where vo_id='$void_code'";
				//$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
				$cif_recovery=100;
				//exit;
			}
			
		return $cif_recovery;
		}

	
	$cif_recovery=check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);
		
		if($cif_recovery<90)
		{
		
		echo $message="Validation: VO Recovery less than 90 : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	
			
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cif_recovery";
			  //$agi-> stream_file($wfile, '#');
			   //mssql_close($link);
			   //$agi->Hangup();
			   exit;
		}else{
		//echo "<br>".$message=" cif_recovery SUCCESS : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			//checking of NPA at VO level By Ashok
			//$npa_qry="SELECT top 1 1 FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_DEMAND_STATUS WHERE FYEAR='2018' and BAL>0 and  DATEDIFF(dd,OVERDUE_SINCE,getdate()-1)>90 AND VO_ID='$void_code'";
			$npa_cnt=0;
//			$npa_cnt=mssql_num_rows(mssql_query($npa_qry));
			// New NPA Validation on 11-09-2017 START
			//$npa_qry="SELECT ISNULL(CONVERT(NUMERIC(10,2),((SUM(CASE WHEN DATEDIFF(DAY,ISNULL(OVERDUE_SINCE ,getdate()),getdate()) > 90 THEN OUTSTANDING ELSE 0 END))/(nullif(SUM(OUTSTANDING),0)))*100),0) NPA_PER FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_DEMAND_STATUS WHERE  VO_ID='$void_code' AND FYEAR=YEAR(dateadd(month, 9, GETDATE()))-1";
			$npa_qry = "USP_GET_IVRS_NPA '$void_code'";
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
			echo $message="Validation: This VO is having NPA above 0.5% ";//:$npa_qry  ";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		   	  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_npa";
			 //$agi-> stream_file($wfile, '#');
			  //mssql_close($link);
			 //$agi->Hangup();
			  exit;
			} 
		}

	list_SHGs($void_code);
} 
if($_REQUEST[Action]=='ValidateSHG')
{
$shg_id= trim($_REQUEST[shg_id]);
$void_code = $_REQUEST[vo_id];
//echo "$shg_id---$vo_id";
	$test_vonumber=$GLOBALS['test_vonumber'];
	 $project=74;
	 // $res_dtmf=//$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  TRANS_SHG_ID='$shg_id'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $message="SHG DETAILS: TRANS_SHG_ID: $shg_code ,  SHG_NAME: $shg_name";
	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
//echo"Validation: $shg_id--$shg_code--select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  TRANS_SHG_ID='$shg_id'";	
	  $shg_name=str_replace(' ','_',$shg_name);
	  $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';
	$triggered_loan_count_res=mssql_fetch_array(mssql_query("select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'"));
		$triggered_loan_count=$triggered_loan_count_res[0];
		$message="Triggered Loans: $triggered_loan_count select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			$curr_odos=0;
			if($triggered_loan_count > 0)
			{
				$shg_overdue_res=mssql_fetch_array(mssql_query("select OVERDUE,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where OVERDUE>0 and SHG_ID='$shg_code'"));
				$shg_ovrdue=$shg_overdue_res[0];
				if($shg_ovrdue>0)
				{
				   $message="SHG HAS OVERDUE AMT $shg_ovrdue is gretaer than 0 in SAMSNBSTG.SN.SHG_OVERDUE_STATUS,system checks shg OD AND OS in SN";
				   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
				   $curr_shg_odos=0;
				   $curr_shg_odos=$shg_overdue_res[1]+$shg_overdue_res[2];
				      if($curr_shg_odos<=100)
				      {
					$curr_odos=$curr_shg_odos;
				  	$message="SHG HAS CURRENT_OD,CURRENT_OS:$curr_odos Less than 100, so for this shg we are allowing SN loan. ";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
				      }
				      else
				      { 
						echo "Validation: SHG has overdue : $shg_ovrdue";
						//mssql_close($link);
		  				exit;

				     }
				
				}

			}
	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	if($overdue_amt>0 && $overdue_amt<=10000)
	{
	$is_over_due='Y';	
	}
	  if($overdue_amt>10000)
	{	
	echo $message="Validation: Bank Linkage AMT $overdue_amt is gretaer than 10000";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		exit;
	}
	$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE,CIF_RECOVERY_PERCENTAGE from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
    $vo_active_stat=$vo_active_rs['IS_ACTIVE'];
	$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
	$shg_active_stat=$shg_active_rs['IS_VALID'];
	if($shg_active_stat=='Y')
	{
	$message="SUCCESS: SHG SB Account Details are valid (select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	}
	else
	{
		echo "Validation: FAIL: SHG SB Account Details are invalid";
		$message="Validation: FAIL: SHG SB Account Details are invalid (select IS_VALID from SHG_INFO where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		exit;

	}

 $check_shg_samrudhi=check_shg_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);

					if($check_shg_samrudhi == 0)
					{

						echo $message="Validation: FAIL: SHG SAMRUDHI FAILED ";
						$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
						exit;
	}else{
		$message="SUCCESS: SHG SAMRUDHI PASSED ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	}

	$shg_grade_dist_code=substr($void_code,2,2);
	$shg_grade_mandal_code=substr($void_code,4,2);
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT(nolock) where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
	$mms_grade=$vo_grade_rs['GRADE'];
    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select A.GRADE,A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT,GEN_LOAN_PER from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs_rej['GRADE'];
	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
	$message="Validating VO GRADE and ACTUAL_CREDIT_LIMIT,MMS Grade , VO Grade: $vo_grade,VO ACTUAL_CREDIT_LIMIT : $vo_actual_credit,MMS Grade :$mms_grade ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		if($vo_grade=='E')
	{
	echo	$message="Validation: VO grade $vo_grade";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
		   //$agi-> stream_file($wfile, '#');
		//mssql_close($link);
		   //$agi->Hangup();
		   exit;
	}

	if($vo_grade=='F')
	{
	echo	$message="Validation: VO grade $vo_grade";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
		   exit;
	}
	if($vo_actual_credit=='0')
	{
	echo	$message="Validation:vo_actual_credit $vo_actual_credit";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   exit;
		}
	list_members($void_code,$shg_code);
/*
$search_purpose_ig="'Agriculture','Dairy','Income generation Activity','Weavers'";
$search_purpose_non_ig="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";

		$shg_limit_array=shg_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code);
		$shg_max_loans_total=$shg_limit_array[$shg_code]['shg_max_loans_total'];
		$shg_max_loans_ivrs=$shg_limit_array[$shg_code]['shg_max_loans_ivrs'];
		$shg_max_credit_limit=$shg_limit_array[$shg_code]['shg_max_credit_limit'];
	   $member_limit=shg_member_limit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs);
	      $message="member_limit: $member_limit";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	
		if($member_limit>=1)
		  {
		 $message="SUCCESS: member_limit: $member_limit Greater Than or Equal to 1";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	
		$amt_stat='Y';
	list_members($void_code,$shg_code);
	       }
		   else
		   {
		  //   $message="Validation: FAIL: member_limit: $member_limit Less Than 1 , Morethan ".$shg_max_loans_ivrs." loans ";
		  echo   $message="Validation: FAIL: Morethan ".$shg_max_loans_ivrs." loans ";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	
//		  	$res_dtmf=//$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_".$shg_max_loans_ivrs."_loans", 5000, 1);
	      $more_res=$res_dtmf ["result"];
			exit;

		   }*/
}
function list_members($void_code,$shg_code)
{

	$member_result=mssql_query("select * from shg_member_info(nolock) where VO_ID='$void_code' and SHG_ID = '$shg_code' and member_is_active='Y'");
	while($member_row=mssql_fetch_array($member_result))
	{
		if ($member_row[IS_POP_MEM]=='Y')
		{
			$member_row_1='pop';
		}
		else
		{
			$member_row_1='non pop';
		}
//		echo "@@".$member_row[MEMBER_NAME]." - ".$member_row[MEMBER_ID]." - ".$member_row_1;
//		echo "@@".$member_row[MEMBER_NAME]." - ".$member_row[MEMBER_ID]." - (".trim($member_row[SHORT_CODE]).")"." - ".$member_row_1;
		echo "@@".$member_row[MEMBER_NAME]." - ".$member_row[MEMBER_ID]." - (".trim($member_row[SHORT_CODE]).")"." - ".$member_row_1." - ".$member_row[FATHER_HUSBAND_NAME];
	}
	

}
function list_SHGs($void_code)
{

	$member_result=mssql_query("select * from shg_info(nolock) where VO_ID='$void_code' and VO_ID ! = TRANS_SHG_ID");
	while($member_row=mssql_fetch_array($member_result))
	{
		
//		echo "@@".$member_row[SHG_NAME]." - ".$member_row[TRANS_SHG_ID];
		echo "@@".$member_row[SHG_NAME]." - ".$member_row[TRANS_SHG_ID]." - (".trim($member_row[SHORT_SHG_CODE]).")";
	}

}

function check_shg_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code){
	
	/* $vo_name_array=mssql_query("select * from vo_info(nolock) where TRANS_VO_ID='$void_code' order by CREATED_DATE  desc");
$vo_name_array=mssql_fetch_array($vo_name_array);
$dist_id=$vo_name_array['DISTRICT_ID'];
$mandal_id=$vo_name_array['MANDAL_ID'];	
	 $shg_samrudhi_query="SELECT SHG_ID 
FROM (         
SELECT SI.VO_ID,SHG_ID,ISNULL(SI.SHG_REG_DATE,'2012-10-31 00:00:00.000') SHG_REG_DATE,SUM(DEPOSITED_AMOUNT) SAM_AMT                         
FROM SHG_DEPOSIT_INFO S (NOLOCK)                         
INNER JOIN SHG_INFO SI (NOLOCK) ON S.SHG_ID=SI.TRANS_SHG_ID                         
INNER JOIN BANKS_DEPOSIT_INFO B (NOLOCK) ON S.BANK_REF_NO=B.BANK_REF_NO                         
WHERE SI.TRANS_SHG_ID='$shg_code' AND B.SCHEME_ID='01' and DEPOSIT_STATUS='CLOSED' AND SI.IS_MEPMA='N'  --AND SI.DISTRICT_ID='$dist_id'                    
GROUP BY SI.VO_ID,SHG_ID,SI.SHG_REG_DATE                         
) D                         
 WHERE D.SAM_AMT>=case when d.SHG_REG_DATE<CONVERT(DATETIME,CONVERT(VARCHAR(10),'01/11/2012',103),103)                       
 then CAST(DATEDIFF(M,CONVERT(DATETIME,CONVERT(VARCHAR(10),'01/11/2012',103),103)  , 
   ( CASE WHEN  MONTH(GETDATE()) IN (4,5,10,11) THEN  DATEADD(Q,-1,DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)))-1 
          WHEN  MONTH(GETDATE()) IN (6,12) THEN DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)-1 
          WHEN MONTH(GETDATE()) IN (7,8,9,1,2,3) THEN DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0))  END  ) )  AS INT)*100     
          
          ELSE CAST ( DATEDIFF(M,d.SHG_REG_DATE, ( CASE WHEN  MONTH(GETDATE()) IN (4,5,10,11) THEN  DATEADD(Q,-1,DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)))-1 
          WHEN  MONTH(GETDATE()) IN (6,12) THEN DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)-1 
          WHEN MONTH(GETDATE()) IN (7,8,9,1,2,3) THEN DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0))  END  ) )  AS INT)*100 END 
UNION 
SELECT SHG_ID 
FROM ( 
SELECT SI.VO_ID,SHG_ID,ISNULL(SI.SHG_REG_DATE,'2012-10-31 00:00:00.000') SHG_REG_DATE,SUM(DEPOSITED_AMOUNT) SAM_AMT               
FROM SHG_DEPOSIT_INFO S (NOLOCK)                         
INNER JOIN SHG_INFO SI (NOLOCK) ON S.SHG_ID=SI.TRANS_SHG_ID                         
INNER JOIN BANKS_DEPOSIT_INFO B (NOLOCK) ON S.BANK_REF_NO=B.BANK_REF_NO                         
WHERE SI.TRANS_SHG_ID='$shg_code' AND B.SCHEME_ID='01' and DEPOSIT_STATUS='CLOSED' AND SI.IS_MEPMA='Y' -- AND SI.DISTRICT_ID='$dist_id'         
GROUP BY SI.VO_ID,SHG_ID,SI.SHG_REG_DATE 
) D 
 WHERE D.SAM_AMT>=case when d.SHG_REG_DATE<CONVERT(DATETIME,CONVERT(VARCHAR(10),'01/04/2013',103),103)                       
 then CAST(DATEDIFF(M,CONVERT(DATETIME,CONVERT(VARCHAR(10),'01/04/2013',103),103)  , 
   ( CASE WHEN  MONTH(GETDATE()) IN (4,5,10,11) THEN  DATEADD(Q,-1,DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)))-1 
          WHEN  MONTH(GETDATE()) IN (6,12) THEN DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)-1 
          WHEN MONTH(GETDATE()) IN (7,8,9,1,2,3) THEN DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0))  END  ) )  AS INT)*100     
          
          ELSE CAST ( DATEDIFF(M,d.SHG_REG_DATE, ( CASE WHEN  MONTH(GETDATE()) IN (4,5,10,11) THEN  DATEADD(Q,-1,DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)))-1 
          WHEN  MONTH(GETDATE()) IN (6,12) THEN DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)-1 
          WHEN MONTH(GETDATE()) IN (7,8,9,1,2,3) THEN DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0))  END  ) )  AS INT)*100     END
UNION 
select SHG_ID FROM SHG_DEPOSIT_INFO S (NOLOCK)                                  
INNER JOIN SHG_INFO SI (NOLOCK) ON S.SHG_ID=SI.TRANS_SHG_ID                                  
INNER JOIN BANKS_DEPOSIT_INFO B (NOLOCK) ON S.BANK_REF_NO=B.BANK_REF_NO                                  
WHERE B.SCHEME_ID='01' and DEPOSIT_STATUS='CLOSED' AND SI.TRANS_SHG_ID='$shg_code' and CAST(DEPOSITED_DATE AS DATE)>'2015-12-31'    
 AND SI.NEW_SAMRUDHI_ELIGBLE='Y'   AND SI.IS_MEPMA='N'   
group by SHG_ID having sum(DEPOSITED_AMOUNT)>=2400 

UNION 
select SHG_ID FROM SHG_DEPOSIT_INFO S (NOLOCK)                                  
INNER JOIN SHG_INFO SI (NOLOCK) ON S.SHG_ID=SI.TRANS_SHG_ID                                  
INNER JOIN BANKS_DEPOSIT_INFO B (NOLOCK) ON S.BANK_REF_NO=B.BANK_REF_NO                                  
WHERE B.SCHEME_ID='01' and DEPOSIT_STATUS='CLOSED' AND SI.TRANS_SHG_ID='$shg_code' and CAST(DEPOSITED_DATE AS DATE)>'2015-12-31'    
 AND SI.NEW_SAMRUDHI_ELIGBLE='Y'   AND SI.IS_MEPMA='Y'   
group by SHG_ID having sum(DEPOSITED_AMOUNT)>=2400    "; */
$shg_samrudhi_query="select *,DEPOSIT_PER from SHG_SAMRUDHI_PERCENTAGE() where shg_id='$shg_code'";
$total_deposit_rs=mssql_fetch_array(mssql_query($shg_samrudhi_query));
$check_shg_samrudhi=$total_deposit_rs['DEPOSIT_PER'];
			
		if($check_shg_samrudhi > 99)
		{
		$shg_samrudhi_validation=1;
		}
		else{
		$shg_samrudhi_validation=0;	
			
			}
			return $shg_samrudhi_validation;

	}

function shg_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code){
			
			$test_vonumber=$GLOBALS['test_vonumber'];
			$members_query="select MEMBER_ID from shg_member_info(nolock) where SHG_ID='$shg_code'";
			$shg_members=mssql_num_rows(mssql_query($members_query));
			$shg_limit_array=array();
			
			if($shg_members >= 12){
				$shg_max_loans_total=9;
				$shg_max_loans_ivrs=6;
				$shg_max_credit_limit=300000;
			}elseif($shg_members == 10 || $shg_members == 11){
				$shg_max_loans_total=8;
				$shg_max_loans_ivrs=6;
				$shg_max_credit_limit=250000;
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
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			
			return $shg_limit_array;
		}

	
function shg_member_limit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs){
		
		
		$test_vonumber=$GLOBALS['test_vonumber'];
		$message="Calculating outstanding loans of SHG in project $project  ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		$shg_applied_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from IVRS_LOAN_REQUEST(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
		$shg_applied_project=$shg_applied_project_rs[0];
		
/*		$shg_applied_project_live_rs=mssql_fetch_array(mssql_query("select count(vo_id) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and IVRS_ID='$ivr_call_id'"));
		$shg_applied_project_live=$shg_applied_project_live_rs[0];
*/		
		$shg_rejected_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and STATUS_ID='11'"));
		$shg_rejected_project=$shg_rejected_project_rs[0];
		$shg_repaid_project_rs=mssql_fetch_array(mssql_query("select count(smlsn.VO_ID) FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) smlsn,SHG_LOAN_APPLICATION(nolock) sla WHERE smlsn.SHG_ID='$shg_code' and smlsn.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type='$project' and smlsn.IS_CLOSED='1'"));
		$shg_repaid_project=$shg_repaid_project_rs[0];
		
	
		$shg_oustanding_project=$shg_applied_project+$shg_applied_project_live-$shg_rejected_project-$shg_repaid_project+$shg_applied_loans_tcs;
		
		$message="shg_oustanding_project: $shg_oustanding_project = shg_applied_project:$shg_applied_project + shg_applied_project_live:$shg_applied_project_live - shg_rejected_project:$shg_rejected_project - shg_repaid_project:$shg_repaid_project + shg_applied_loans_tcs : $shg_applied_loans_tcs";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		
		$shg_limit_project=$shg_max_loans_ivrs-$shg_oustanding_project;
		
		$message="shg_limit_project: $shg_limit_project = shg_max_loans_ivrs: $shg_max_loans_ivrs - shg_oustanding_project: $shg_oustanding_project ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		$shg_allowed_loans=$shg_limit_project;
		
		
		$message="Calculating outstanding loans of SHG in project $project using TCS Tables only ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		$TCS_shg_applied_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project'"));
		$TCS_shg_applied_project=$TCS_shg_applied_project_rs[0];
		
/*		$TCS_shg_applied_project_live_rs=mssql_fetch_array(mssql_query("select count(vo_id) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and IVRS_ID='$ivr_call_id'"));
		$TCS_shg_applied_project_live=$TCS_shg_applied_project_live_rs[0];
*/		
		$TCS_shg_rejected_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and STATUS_ID='11'"));
		$TCS_shg_rejected_project=$TCS_shg_rejected_project_rs[0];
		
		$TCS_shg_repaid_project_rs=mssql_fetch_array(mssql_query("select count(smlsn.VO_ID) FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) smlsn,SHG_LOAN_APPLICATION(nolock) sla WHERE smlsn.SHG_ID='$shg_code' and smlsn.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type='$project' and smlsn.IS_CLOSED='1'"));
		$TCS_shg_repaid_project=$TCS_shg_repaid_project_rs[0];
		
		
		$TCS_shg_oustanding_project=$TCS_shg_applied_project+$TCS_shg_applied_project_live-$TCS_shg_rejected_project-$TCS_shg_repaid_project;
		
		$message="TCS_shg_oustanding_project: $TCS_shg_oustanding_project = TCS_shg_applied_project:$TCS_shg_applied_project + TCS_shg_applied_project_live:$TCS_shg_applied_project_live - TCS_shg_rejected_project:$TCS_shg_rejected_project - TCS_shg_repaid_project:$TCS_shg_repaid_project ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		$TCS_shg_limit_project=$shg_max_loans_total-$TCS_shg_oustanding_project;
		$message="TCS_shg_limit_project: $TCS_shg_limit_project = shg_max_loans_total: $shg_max_loans_total - TCS_shg_oustanding_project: $TCS_shg_oustanding_project ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		$TCS_shg_allowed_loans=$TCS_shg_limit_project;

		
		if($shg_allowed_loans > 0 && $TCS_shg_allowed_loans > 0){
		$message="SUCCESS: shg_allowed_loans: $shg_allowed_loans Greater than 0 AND TCS_shg_allowed_loans:$TCS_shg_allowed_loans Greater than 0";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		return $shg_allowed_loans;
		}else{
		$message="FAIL: shg_allowed_loans: $shg_allowed_loans Less than 0 OR TCS_shg_allowed_loans:$TCS_shg_allowed_loans Less than 0";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		return 0;
		}
		
		
		
	}

if($_REQUEST[Action]=='GetMemberMobile')
{
$memberId=$_REQUEST[memberId];
$shgId = $_REQUEST[shgId];

$role_query = "select * from shg_member_info where member_ID='$memberId' and shg_id='$shgId'";
$role_res = mssql_query($role_query);
$row = mssql_fetch_array($role_res);
echo $row[MOBILE_NUM];exit;

}
if($_REQUEST[Action]=='ValidateMember')
{

$void_code=$_REQUEST[vo_id];
$shg_code = $_REQUEST[shg_id];

$member_type= $_REQUEST[member_type];
$memberId = $_REQUEST[memberId];
$loan_amount = $_REQUEST[loan_amount];
$memberMobNo = $_REQUEST[memberMobNo];
//Action=ValidateMember&vo_id=011916080130102&shg_id=011916080130101020&member_type=Y&memberId=01191608013010102004&loan_category=IG&reason_loan=104
$loan_type = $_REQUEST[loan_category];
$reason_loan = strtolower($_REQUEST[reason_loan]);
$is_over_due='N';
$caller = $_REQUEST[mobile_number];
$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	if($overdue_amt>0 && $overdue_amt<=10000)
	{
	$is_over_due='Y';	
	}

				$shg_overdue_res=mssql_fetch_array(mssql_query("select OVERDUE,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
				$shg_ovrdue=$shg_overdue_res[0];
				if($shg_ovrdue>0)
				{
				   $message="SHG HAS OVERDUE AMT $shg_ovrdue is gretaer than 0 in SAMSNBSTG.SN.SHG_OVERDUE_STATUS,system checks shg OD AND OS in SN";
				   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
				   $curr_shg_odos=0;
				   $curr_shg_odos=$shg_overdue_res[1]+$shg_overdue_res[2];
				      if($curr_shg_odos<=100)
				      {
					$curr_odos=$curr_shg_odos;
				  	$message="SHG HAS CURRENT_OD,CURRENT_OS:$curr_odos Less than 100, so for this shg we are allowing SN loan. ";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
				      }
				      else
				      { 
						echo "Validation: SHG has overdue : $shg_ovrdue";
						//mssql_close($link);
		  				exit;

				     }
				
				}


		$shg_id_query=mssql_query("select TRANS_SHG_ID from SHG_INFO(nolock) where  VO_ID='$void_code'");
$vo_shgs='';
while($shg_id_array=mssql_fetch_array($shg_id_query)){
    $vo_shg_id=$shg_id_array['TRANS_SHG_ID'];
    $vo_shgs.="'".$vo_shg_id."'".",";
	}
	$vo_shgs=substr($vo_shgs,0,-1);
	
	$message="Fetching SHGs in VO $void_code :  $vo_shgs ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);



		$shg_limit_array=shg_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code);
		$shg_max_loans_total=$shg_limit_array[$shg_code]['shg_max_loans_total'];
		$shg_max_loans_ivrs=$shg_limit_array[$shg_code]['shg_max_loans_ivrs'];
		$shg_max_credit_limit=$shg_limit_array[$shg_code]['shg_max_credit_limit'];

$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE,IS_POP_MEM from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and MEMBER_ID='$memberId'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
  $member_type=$member_id_rs['IS_POP_MEM'];
 				
$message="Member details:  MEMBER_ID: $member_id , Short Code:  $memberId,member_uid: $member_uid , member_age:$member_age , IS_POP_MEM:$IS_POP_MEM , member_mobile_num: $member_mobile_num";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);


//Member Mobile Number Updation START 01-08-2017
	
//    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$memberMobNo'"));
    $start_digit=substr($memberMobNo,0,1);

	if(trim($memberMobNo)=='')	 	
	{echo $message="Validation: Please enter Mobile Number";$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);exit;}
	
	if($memberMobNo>'1' && strlen($memberMobNo)=='10' && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6'))
	{
//	echo $message="Validation: "."UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$memberMobNo'  where SHG_ID='$shg_code' and member_id='$memberId'";exit;
        if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65'&& $_SERVER[REMOTE_ADDR]!= '125.16.9.129')
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$memberMobNo'  where SHG_ID='$shg_code' and member_id='$memberId'");
	}
	else
	{
	echo $message="Validation: Please enter Valid Mobile Number";$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);exit;
	}
//Member Mobile Number Updation END 01-08-2017


member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$memberId,$member_type,$loan_type,$reason_loan,$loan_amount);
}

function member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$memberId,$member_type,$loan_type,$reason_loan,$loan_amount){
			
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE,IS_POP_MEM,SHORT_CODE from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and MEMBER_ID='$memberId'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
  $IS_POP_MEM=$member_id_rs['IS_POP_MEM'];
$member_short_code = str_replace(" ","",$member_id_rs['SHORT_CODE']);
 		
//echo "Validation:".$message="Member details:  MEMBER_ID: $member_id , Short Code:  $member_short_code,member_uid: $member_uid , member_age:$member_age , IS_POP_MEM:$IS_POP_MEM , member_mobile_num: $member_mobile_num";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

	$project = '74';
//START CHECKING MEMBER_OVERDUE
$member_overdue_res=mssql_fetch_array(mssql_query("select LOAN_DUE from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED=0"));

if($member_overdue_res['LOAN_DUE']>0)
{
		exit("Validation: Member has overdue : $member_overdue_res[LOAN_DUE]");
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_sn_overdue"; //member overdue
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
}
//END CHECKING MEMBER_OVERDUE
 if($member_age > 63 && $member_age != 0){
	echo "Validation: Member Age is Greater than 63 ";
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_greater_60";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	exit;	
 }
 

	$member_applied_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type='74'"));
	
	$member_prev_loan_count_live = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id' and project_type='74'"));

$member_rej_cnt_lng = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and REQUESTED_ID!='201314' and project_type='74'"));

$member_rej_cnt_lng_tcs = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and project_type='74'"));

$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1' and a.project_type='74'"));

$member_other_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));

		if($member_applied_loan_count == 0 && $member_prev_loan_count_tcs ==0){
		 $member_repaid_loans=0;
		}

	
	$message="member_applied_loan_count-member_rej_cnt_lng+member_prev_loan_count_live-member_repaid_loans-member_corpus_loans:$member_applied_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_corpus_loans:::member_prev_loan_count_tcs-member_rej_cnt_lng_tcs+member_prev_loan_count_live-member_repaid_loans-member_corpus_loans:$member_prev_loan_count_tcs-$member_rej_cnt_lng_tcs+$member_prev_loan_count_live-$member_repaid_loans-$member_corpus_loans ::member_other_repaid_loans:$member_other_repaid_loans";
        $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
        $member_prev_loan_count=$member_applied_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans;

	$message="Member loans ".$member_applied_loan_count."-".$member_rej_cnt_lng."+".$member_prev_loan_count_live."-".$member_repaid_loans."-".$member_other_repaid_loans." = ".$member_prev_loan_count;

	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

	if($member_prev_loan_count != 0)
	{
	
	echo "Validation: Member loan already applied ";
	//echo "Validation: ".$message="Member loan_already_applied : member_prev_loan_count:".$member_prev_loan_count." member_repaid_loans : ".$member_repaid_loans;
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	// bicycle_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$status_pop_fund) ;
		//mssql_close($link);
	//$agi->Hangup();
	exit;	
	}

		$loan_type = "CONSUMPTION";
		$loan_category="NONIG";	
		$reason_loan="bicycle";
		
	if($loan_amount=='')
	{
	echo "Validation: Please enter Loan Amount";
	exit;
	}

$diff_loan_amt=intval(substr($loan_amount,-2,2));
if($diff_loan_amt=='0')
		{
		 if($loan_amount<=5000 && $loan_amount>=3000)
		     {
		//return $loan_amount;
		           }
				   else
				   {
					if($loan_amount>5000)
					{
					echo "Validation: Loan amount should not be more than 5000";exit;
					$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_25000";
	                		//$agi-> stream_file($wfile, '#');
					 }
					 if($loan_amount<3000)
					 {
					echo "Validation: Loan amount should not be less than 3000";exit;
					 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_morethan_1000";
	                		//$agi-> stream_file($wfile, '#');
					 }
				  }
				  }
				   else
				 {
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_multiples_100";
		//$agi-> stream_file($wfile, '#');
		echo "Validation: Please enter Loan amount in multiples of 100";exit;
				 }



	 if($loan_amount>='1')
	 {
// echo "Validation:dasdasdasd--$reason_loan--$member_type---".strlen($member_short_code)."---$member_prev_loan_count--$member_id--$loan_category"	;
 if(($reason_loan!='')&&($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && $member_prev_loan_count=='0' && strlen($member_id)>1))
 {
 $duration='12';
 $etime=date('Y-m-d H:i:s');

 $vo_actual_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT,GEN_LOAN_PER from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
 $vo_actual_credit=$vo_actual_credit_rs['ACTUAL_CREDIT_LIMIT'];
 $vo_credit_pop=$vo_actual_credit_rs['POP_CREDIT_LIMIT'];
 $vo_credit_non_pop=$vo_actual_credit_rs['NONPOP_CREDIT_LIMIT']; 
 $vo_gen_loan_per=$vo_actual_credit_rs['GEN_LOAN_PER'];  
 
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
	
	$applied_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'")); 

	
        $applied_amt = $applied_rs['AMT'];
		
/*		$applied_rs_live=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) "));
        $applied_amt_live = $applied_rs_live['AMT_LIVE'];
		$applied_amt_live=intval($applied_amt_live);
*/
		
		$vo_rej_amt_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))   and STATUS_ID='11'"));		
$vo_rej_amt=$vo_rej_amt_rs['AMT_REJ'];
$vo_rej_amt=intval($vo_rej_amt);



//commented for automation
/*
   $vo_fixed_credit_rs=mssql_fetch_array(mssql_query("select $search_cr_limit from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
       $vo_fixed_credit = $vo_fixed_credit_rs[$search_cr_limit];
	   */
	   
	   $vo_repaid_cat_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='$search_cat' and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) "));
        $repaid_cat_total = $vo_repaid_cat_rs['AMT_REPAID'];
		//added for automation
		//$applied_total=$applied_amt+$applied_amt_live-$vo_rej_amt-$repaid_cat_total;
		$applied_total=$applied_amt-$vo_rej_amt-$repaid_cat_total;
		
	if($applied_total < 0){
			$applied_total=0;
			
	$message="VO Repaid AMT is greater than applied amt,resetting the outstanding to 0, applied_total :$applied_total";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
					
		}
		
		$project=74;
		$vo_cat_limit=$vo_cat_actual_limit-$applied_total;
	$tcs_shg_outstanding_amt=shg_outstanding_amt($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code);

$tcs_shg_drawing_power=$shg_max_credit_limit-$tcs_shg_outstanding_amt;

  $message="Validation: VALIDATING SHG Drawing power tcs_shg_drawing_power: $tcs_shg_drawing_power = shg_max_credit_limit: $shg_max_credit_limit  - tcs_shg_outstanding_amt: $tcs_shg_outstanding_amt";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

		//vo_amount
//$vo_total_credit_rs=mssql_fetch_array(mssql_query("select  credit_limit_pop+credit_limit_nonpop from IVRS_VO_CREDIT_LIMIT  where vo_id='$void_code'"));
        //$vo_total_credit=$vo_total_credit_rs[0];
        $vo_total_credit_rs=mssql_fetch_array(mssql_query("select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT,GEN_LOAN_PER from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	 $vo_total_credit=$vo_total_credit_rs['ACTUAL_CREDIT_LIMIT'];

 
//$vo_outstanding=vo_outstanding($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'1');
	
//$vo_drawing_power=$vo_total_credit-$vo_outstanding;

//total drawing power from dp_calculation_ivrs() start
 $CreditLimitsQry="select * from dp_calculation_ivrs() where TRANS_VO_ID='$void_code'";
 $vo_actual_credit_rs=mssql_fetch_array(mssql_query($CreditLimitsQry));
 $current_limit_vo_dp=$vo_actual_credit_rs["VO_TOTAL_DP"];
 $vo_drawing_power=$current_limit_vo_dp;
//total drawing power from dp_calculation_ivrs() end


//$vo_outstanding_tcs=vo_outstanding_tcs($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,'1');
	
//$vo_drawing_power_tcs=$vo_total_credit-$vo_outstanding_tcs;

$vo_category_drawing_power=play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$credit_lt_type,$project);			

// echo "Validation:".$message="VALIDATING LOAN AMOUNT vo_credit_lt:$vo_category_drawing_power Greater than Equal loan_amount:$loan_amount,Loan amount $loan_amount LESS THAN EQUAL VO Drawing power $vo_drawing_power,VO drawing power from TCS Tables $vo_drawing_power_tcs Greater than Equal Loan AMT $loan_amount,loan_amount: $loan_amount LESS THAN EQUAL tcs_shg_drawing_power: $tcs_shg_drawing_power";

 $message =  "Validation: vo_drawing_power from dp_calculation_ivrs(): $vo_drawing_power\n Validation:vo_category_drawing_power >= loan_amount && loan_amount<=vo_drawing_power  && loan_amount <= tcs_shg_drawing_power \n $vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power && $loan_amount <= $tcs_shg_drawing_power";

	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
			  
  //if($vo_credit_lt>=$loan_amount && $shg_lt_max<=150000 && $shg_limit_max_tcs<=150000 && $vo_credit_max_tcs<=$vo_total_credit && $mms_credit_max_tcs<=$mms_total_credit )
  //{ 

//print "Validation:$vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount && $loan_amount <= $tcs_shg_drawing_power";

  //if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount && $loan_amount <= $tcs_shg_drawing_power)
 // if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power  && $vo_drawing_power_tcs >= $loan_amount )
	if($vo_category_drawing_power >= $loan_amount && $loan_amount<=$vo_drawing_power)
  {  

 $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select * from IVRS_LOAN_REQUEST(nolock) where  SHG_ID='$shg_code' and PROJECT_TYPE='74' order by CREATED_DATE  desc "));
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
				   	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
					}
					else
					{
					  $shg_mem_ods_res=mssql_query("select SHG_ID,MEMBER_ID,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code' and CURRENT_OVERDUE>0");
					  $rec_count=0;
					  while($shg_mem_ods_row=mssql_fetch_array($shg_mem_ods_res))
					   {
					if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
						{
						   $pres_odos=$shg_mem_ods_row['CURRENT_OVERDUE']+$shg_mem_ods_row['CURRENT_OUTSTANDING'];
						   if($pres_odos<=100)
				      		    {
						     $shg_pending="INSERT INTO SHG_MEMBER_PENDING_OVERDUE_STATUS(SHG_ID,MEMBER_ID,CREATED_DATE,CURRENT_OVERDUE,CURRENT_OUTSTANDING,INSERTED_BY) VALUES ('".$shg_mem_ods_row['SHG_ID']."','".$shg_mem_ods_row['MEMBER_ID']."',GETDATE(),'".$shg_mem_ods_row['CURRENT_OVERDUE']."','".$shg_mem_ods_row['CURRENT_OUTSTANDING']."','IVRS')";
if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
				 mssql_query($shg_pending);

	  		   $message="SHG HAS CURRENT_OD,CURRENT_OS as pres_odos :$pres_odos Less than 100, so for this shg we are inserting into at SN $shg_pending. ";
			   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
						    }
						}
						$rec_count++;
					   }
					$message="Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS from SAMSNBSTG.SN.SHG_OVERDUE_STATUS where shg $shg_code : rec_count:$rec_count :$shg_pending";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);					
					}
				}

//Ashok Adding Current od and os to this member End

	$PROJECT_TYPE='74';  

	//chk_ln_status($shg_code,$member_id,$PROJECT_TYPE,$ivr_call_id);

	$loan_amount=$loan_amount+$curr_odos;	



############START
	$mms_dist_code=substr($void_code,2,2);
      $mms_mandal_code=substr($void_code,4,2);
if($_SESSION[USER_NAME]==NULL && strlen($_SESSION[desk_IMEI_NO])==15)
{
$_SESSION[USER_NAME]=$_SESSION[desk_IMEI_NO];
}

                                $paidDate =date('Y-m-d H:i:s');
                                $rand_number=rand(1,12000);
                                $insertIvrsCallInfoQry ="INSERT INTO IVRS_CALL_INFO (CALLER,CREATED_DATE,EXTENSION,CALL_STATUS,UNIQUE_ID,REQ_TYPE) VALUES ('".$caller."','".$paidDate."','".$caller."','".$_SESSION[USER_NAME]."','".$rand_number."','W')";
if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
                                mssql_query("$insertIvrsCallInfoQry");

$res=mssql_query("SELECT ID FROM IVRS_CALL_INFO(NOLOCK) WHERE CALLER='".$caller."' AND UNIQUE_ID='".$rand_number."' AND REQ_TYPE='W' ORDER BY CREATED_DATE DESC");
$row = mssql_fetch_array($res);
$ivrs_id =$row['ID'];
if($unique_id==NULL && strlen($_SESSION[desk_IMEI_NO])==15)
{
$unique_id=$_SESSION[desk_IMEI_NO];
}

$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivrs_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos.0')";
#############END

//$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','web - $_SESSION[USER_NAME]','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos.0')";	

if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65'&& $_SERVER[REMOTE_ADDR]!= '125.16.9.129')
{
mssql_query($loanRequestInsertQry);
//echo "Validation: $loanRequestInsertQry";
}

$curr_odos=0;

                $message="Insertion of SN Lead : $loanRequestInsertQry";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);


$current_limit=$vo_drawing_power-$loan_amount;

if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
{
mssql_query("update  IVRS_VO_CREDIT_LIMIT set $tbl_filed='$current_limit'  where vo_id='$void_code'");
}

if($member_type=='Y')
     {
	$pop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_pop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$pop_lmt=$pop_lmt_rs['current_limit_pop'];
	
		 if($pop_lmt>=$loan_amount)
		 {
	if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
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
	if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
		{
		mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_nonpop=current_limit_nonpop-$loan_amount  where vo_id='$void_code'");
		}
	}
	   }
	
 }else{
echo "Validation: Loan amount is more than credit limit";exit;
}

echo "Loan Applied Successfully";
	
 }
 //echo  $loan_amount.$reason_loan.$member_type;
 
		}

else
{
 echo "Validation:dasdasdasd--$reason_loan--$member_type---".strlen($member_short_code)."---$member_prev_loan_count--$member_id--$loan_category";
		
}

			
		}


function shg_outstanding_amt($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code){
		$message="Calculating outstanding Amount of SHG in project $project  ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

		
		$shg_applied_project_rs=mssql_fetch_array(mssql_query("select sum(loan_amount) from IVRS_LOAN_REQUEST(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
		$shg_applied_project=$shg_applied_project_rs[0];
		
		$TCS_shg_applied_project_rs=mssql_fetch_array(mssql_query("select sum(actual_amount) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' "));
		$TCS_shg_applied_project=$TCS_shg_applied_project_rs[0];
		
		/*$shg_applied_project_live_rs=mssql_fetch_array(mssql_query("select sum(loan_amount) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id='$shg_code' and PROJECT_TYPE ='$project' and IVRS_ID='$ivr_call_id'"));
		$shg_applied_project_live=$shg_applied_project_live_rs[0];
		*/
		$shg_rejected_project_rs=mssql_fetch_array(mssql_query("select sum(actual_amount) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and STATUS_ID='11'"));
		$shg_rejected_project=$shg_rejected_project_rs[0];
		
		$shg_repaid_project_rs=mssql_fetch_array(mssql_query("select sum(sla.ACTUAL_AMOUNT) FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) smlsn,SHG_LOAN_APPLICATION(nolock) sla WHERE smlsn.SHG_ID='$shg_code' and smlsn.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type ='$project' and smlsn.IS_CLOSED='1'"));
		$shg_repaid_project=$shg_repaid_project_rs[0];
		
		
		$shg_applied_project_tcs_rs=mssql_fetch_array(mssql_query("select sum(loan_amount) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE ='$project'  and REQUESTED_ID='201314'"));
		$shg_applied_project_tcs=$shg_applied_project_tcs_rs[0];
		
		//$shg_oustanding_project=$shg_applied_project+$shg_applied_project_live-$shg_rejected_project-$shg_repaid_project+$shg_applied_project_tcs;
		$shg_oustanding_project=$shg_applied_project-$shg_rejected_project-$shg_repaid_project+$shg_applied_project_tcs;

		$message="shg_oustanding_project: $shg_oustanding_project = shg_applied_project:$shg_applied_project + shg_applied_project_live:$shg_applied_project_live - shg_rejected_project:$shg_rejected_project - shg_repaid_project:$shg_repaid_project + shg_applied_project_tcs: $shg_applied_project_tcs ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		$TCS_shg_oustanding_project=$TCS_shg_applied_project+$shg_applied_project_live-$shg_rejected_project-$shg_repaid_project;
		
		$message="TCS shg_oustanding_project: $shg_oustanding_project = TCS_shg_applied_project:$TCS_shg_applied_project + shg_applied_project_live:$shg_applied_project_live - shg_rejected_project:$shg_rejected_project - shg_repaid_project:$shg_repaid_project ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		if($shg_oustanding_project > $TCS_shg_oustanding_project){
			return $TCS_shg_oustanding_project;
		}else{
			return $shg_oustanding_project;
		}
	}


function vo_outstanding($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project){
		$test_vonumber=$GLOBALS['test_vonumber'];
		//// get credit limits from play_credits function    
	    $vo_amt_to_add_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and STATUS_ID='11'"));
       $vo_amt_to=$vo_amt_to_add_rs[0];
	  // $vo_total_credit=$vo_total_credit+$vo_amt_to;
	   
		
		$vo_lt_max_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
		$vo_lt_max_tcs=$vo_lt_max_tcs_rs[0];
		
/*		$vo_lt_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) "));
		$vo_lt_live=$vo_lt_live_rs[0];
*/		
	$vo_repaid_total_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_VO from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) "));
        $vo_repaid_total = $vo_repaid_total_rs['AMT_REPAID_VO'];
		$vo_repaid_total=intval($vo_repaid_total);
		
		$applied_loans_tcs_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and REQUESTED_ID='201314'"));
       $applied_loans_tcs=$applied_loans_tcs_rs[0];
		
		$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_live-$vo_repaid_total-$vo_amt_to+$applied_loans_tcs;

		$message = "vo_credit_max_tcs=vo_lt_max_tcs+vo_lt_live-vo_repaid_total-vo_amt_to+applied_loans_tcs::$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_live-$vo_repaid_total-$vo_amt_to+$applied_loans_tcs\n";		
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

		return $vo_credit_max_tcs;
		}	
		
		function vo_outstanding_tcs($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project){
		$test_vonumber=$GLOBALS['test_vonumber'];
		//// get credit limits from play_credits function    
	    $vo_amt_to_add_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and STATUS_ID='11' and project_type not in('71','72','74','43')"));
       $vo_amt_to=$vo_amt_to_add_rs[0];
	  // $vo_total_credit=$vo_total_credit+$vo_amt_to;
	   
		
		$vo_lt_max_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and project_type not in('71','72','74','43')"));
		$vo_lt_max_tcs=$vo_lt_max_tcs_rs[0];

		$vo_lt_max_tcs_rs_pending=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from VO_REQUEST_MESSAGES(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and project_type not in('71','72','74','43') and is_processed='N'"));
		$vo_lt_max_tcs_pending=$vo_lt_max_tcs_rs_pending[0];
	
/*		$vo_lt_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and IVRS_ID='$ivr_call_id' and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) "));
		$vo_lt_live=$vo_lt_live_rs[0];
*/		
	$vo_repaid_total_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_VO from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and project_type not in('71','72','74','43')"));
        $vo_repaid_total = $vo_repaid_total_rs['AMT_REPAID_VO'];
		$vo_repaid_total=intval($vo_repaid_total);
		
		$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_max_tcs_rs_pending+$vo_lt_live-$vo_repaid_total-$vo_amt_to;

		 $message = "vo_credit_max_tcs=vo_lt_max_tcs+vo_lt_max_tcs_rs_pending+vo_lt_live-vo_repaid_total-vo_amt_to::$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_max_tcs_rs_pending+$vo_lt_live-$vo_repaid_total-$vo_amt_to\n";
              	 $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		return $vo_credit_max_tcs;
		}	

	function play_credit_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$action,$project){
	$test_vonumber=$GLOBALS['test_vonumber'];
	$message="Prompting Credit limits ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	
	// START 06-08-18 fecthing the credit limits and drawing powers from VO_WISE_CCL_STATIC_TABLE
//	$CreditLimitsQry="select * from VO_WISE_CCL_STATIC_TABLE where TRANS_VO_ID='$void_code'";
	$CreditLimitsQry="select * from dp_calculation_ivrs() where TRANS_VO_ID='$void_code'";
	$vo_actual_credit_rs=mssql_fetch_array(mssql_query($CreditLimitsQry));
	
	$current_limit_pop_ig=$vo_actual_credit_rs["IGA_POP_DP"];
	$current_limit_pop_non_ig=$vo_actual_credit_rs["CONS_POP_DP"];
	$current_limit_nonpop_ig=$vo_actual_credit_rs["IGA_NONPOP_DP"];
	$current_limit_nonpop_non_ig=$vo_actual_credit_rs["CONS_NONPOP_DP"];
	
	$vo_credit_pop=$current_limit_pop_ig+$current_limit_pop_non_ig;
	$vo_credit_npop=$current_limit_nonpop_ig+$current_limit_nonpop_non_ig;

	$message="Credit Limits : POP: $vo_credit_pop,NON POP: $vo_credit_npop,POP IG: $current_limit_pop_ig,POP NON IG: $current_limit_pop_non_ig,NON POP IG: $current_limit_nonpop_ig,NON POP NON IG: $current_limit_nonpop_non_ig";
	log_ivr($ivr_call_id,$message);
	// END 06-08-18 fecthing the credit limits and drawing powers from VO_WISE_CCL_STATIC_TABLE
/*
	
	$search_purpose_ig="'Agriculture','Dairy','Income generation Activity','Weavers'";
	$search_purpose_non_ig="'Education','Marriage','health','Emergency Needs'";
	
	
	$CreditLimitsQry="select A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT,GEN_LOAN_PER from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'";
	 $vo_actual_credit_rs=mssql_fetch_array(mssql_query($CreditLimitsQry));
 $vo_actual_credit=$vo_actual_credit_rs['ACTUAL_CREDIT_LIMIT'];
 $vo_credit_pop=$vo_actual_credit_rs['POP_CREDIT_LIMIT'];
 $vo_credit_non_pop=$vo_actual_credit_rs['NONPOP_CREDIT_LIMIT'];
 $vo_gen_loan_per=$vo_actual_credit_rs['GEN_LOAN_PER']; 

 
 	$message="Fetching credit limits from TCS table VO_CREDIT_LIMIT , ACTUAL_CREDIT_LIMIT: $vo_actual_credit ,POP_CREDIT_LIMIT: $vo_credit_pop ,NONPOP_CREDIT_LIMIT: $vo_credit_non_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	

 $vo_amt_to_add_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and STATUS_ID='11'"));
       $vo_amt_to=$vo_amt_to_add_rs[0];

 	$message="Rejected Amount SHG_LOAN_APPLICATION (select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and STATUS_ID='11') , vo_amt_to: $vo_amt_to  ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

		
		$vo_lt_max_tcs_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
		$vo_lt_max_tcs=$vo_lt_max_tcs_rs[0];
		
		 	$message="Applied Loan Amount from IVRS_LOAN_REQUEST (select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) ) and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'  vo_lt_max_tcs :$vo_lt_max_tcs ";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
/*		$vo_lt_live_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) "));
		$vo_lt_live=$vo_lt_live_rs[0];

		 	$message="Applied Loan Amount from IVRS_LOAN_REQUEST_LIVE (select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) )  vo_lt_live :$vo_lt_live ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
* /		
	$vo_repaid_total_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_VO from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) "));
        $vo_repaid_total = $vo_repaid_total_rs['AMT_REPAID_VO'];
		$vo_repaid_total=intval($vo_repaid_total);
		
	$message="Repaid Loan Amount from SN.SHG_MEMBER_REPAY_VIEW (select sum(PPR) as AMT_REPAID_VO from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) )  vo_repaid_total :$vo_repaid_total ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		
		$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_live-$vo_repaid_total-$vo_amt_to;
		
		
			
	$message="VO outstanding  (vo_lt_max_tcs+vo_lt_live+loan_amount-vo_repaid_total-vo_amt_to)  vo_credit_max_tcs :$vo_credit_max_tcs";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		
		if($vo_credit_max_tcs < 0){
			$vo_credit_max_tcs=0;
			
	$message="VO Repaid AMT is greater than applied amt,resetting the outstanding to 0, vo_credit_max_tcs :$vo_credit_max_tcs";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
					
		}
		
		
		
		$vo_current_credit_limit=$vo_actual_credit-$vo_credit_max_tcs;

	$message="VO Drawing power  (vo_actual_credit-vo_credit_max_tcs) vo_current_credit_limit :$vo_current_credit_limit ,vo_actual_credit=$vo_actual_credit,vo_credit_max_tcs=$vo_credit_max_tcs";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
$vo_current_credit_limit = $vo_current_credit_limit * $vo_gen_loan_per;	
if($vo_current_credit_limit <= 0 ){
	
	$vo_credit_pop=0;
	$vo_credit_non_pop=0;
	$current_limit_pop_ig=0;
	$current_limit_pop_non_ig=0;
	$current_limit_nonpop_ig=0;
	$current_limit_nonpop_non_ig=0;
	
}else{
	
	$message="Calculating POP And NON POP Limits ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	
//$vo_credit_pop_ig=intval(ceil($vo_credit_pop*0.85));
//	 $vo_credit_pop_nonig=intval(floor($vo_credit_pop*0.15));
//	 $vo_credit_non_pop_ig=intval(ceil($vo_credit_non_pop*0.85));
//	 $vo_credit_non_pop_nonig=intval(floor($vo_credit_non_pop*0.15));
	 
$applied_pop_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
$applied_amt_pop=$applied_pop_rs['AMT'];

	$message="POP Applied AMT (select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) ) and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'  applied_amt_pop:$applied_amt_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);


/*
$applied_rs_live=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='Y' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) "));
        $applied_amt_pop_live=$applied_rs_live['AMT_LIVE'];
		$applied_amt_pop_live=intval($applied_amt_pop_live );
		
		
	$message="POP Applied LIVE AMT (select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='Y' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) )  applied_amt_pop_live:$applied_amt_pop_live ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
*/	

////pop ig

$vo_rej_amt_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and STATUS_ID='11'"));
$vo_rej_amt_pop=$vo_rej_amt_rs['AMT_REJ'];
$vo_rej_amt_pop=intval($vo_rej_amt_pop);


	$message="POP Rejected AMT (select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='Y' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) and STATUS_ID='11')   vo_rej_amt_pop:$vo_rej_amt_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);


//non pop------
		

$applied_npop_rs=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
$applied_amt_nonpop=$applied_npop_rs['AMT'];


	$message="NON POP Applied AMT (select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) ) and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' applied_amt_nonpop:$applied_amt_nonpop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

/*
$applied_rs_live_np=mssql_fetch_array(mssql_query("select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='N' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) "));
        $applied_amt_nonpop_live=$applied_rs_live_np['AMT_LIVE'];
		$applied_amt_nonpop_live=intval($applied_amt_nonpop_live);

	$message="NON POP Applied LIVE AMT (select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs) and IS_POP='N' and IVRS_ID='$ivr_call_id' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) )   applied_amt_nonpop_live:$applied_amt_nonpop_live ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
* /
	
	//rejected amount
	
$vo_rej_amt_nonpop_rs=mssql_fetch_array(mssql_query("select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and STATUS_ID='11'"));
$vo_rej_amt_nonpop=$vo_rej_amt_nonpop_rs['AMT_REJ'];
$vo_rej_amt_nonpop=intval($vo_rej_amt_nonpop);

	$message="NON POP Rejected AMT (select SUM(ACTUAL_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs) and IS_POP='N' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))  and STATUS_ID='11')   vo_rej_amt_nonpop:$vo_rej_amt_nonpop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

			
$vo_repaid_pop_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_POP from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='0' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) "));
        $vo_repaid_pop = $vo_repaid_pop_rs['AMT_REPAID_POP'];
		
	$message="POP Repaid AMT (select sum(PPR) as AMT_REPAID_POP from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='0' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) )   vo_repaid_pop:$vo_repaid_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);		
		
		
		$vo_repaid_nonpop_rs=mssql_fetch_array(mssql_query("select sum(PPR) as AMT_REPAID_NONPOP from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='1' and PROJECT_TYPE IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0))"));
        $vo_repaid_nonpop = $vo_repaid_nonpop_rs['AMT_REPAID_NONPOP'];
	$message="NONPOP Repaid AMT (select sum(PPR) as AMT_REPAID_NONPOP from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs) and IS_POP='1' and PROJECT_TYPE  IN (SELECT PROJECT_TYPE FROM GET_PTYPES(0)) )   vo_repaid_nonpop:$vo_repaid_nonpop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	


	$vo_applied_pop=$applied_amt_pop+$applied_amt_pop_live-$vo_rej_amt_pop-$vo_repaid_pop;


		
	

	$message="POP Outstanding(applied_amt_pop+applied_amt_pop_live-vo_rej_amt_pop-vo_repaid_pop)  vo_applied_pop:$vo_applied_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);


if($vo_applied_pop < 0){
	$vo_applied_pop=0;
}

//$vo_applied_pop_ig=$applied_amt_pop_ig+$applied_amt_live_pop_ig-$vo_rej_amt_pop_ig-$vo_repaid_pop_ig;
//$vo_applied_pop_nonig=$applied_amt_pop_nonig+$applied_amt_live_pop_nonig-$vo_rej_amt_pop_nonig-$vo_repaid_pop_nonig;	

	
$vo_applied_nonpop=$applied_amt_nonpop+$applied_amt_nonpop_live-$vo_rej_amt_nonpop-$vo_repaid_nonpop;	
	$message="NON POP Outstanding(applied_amt_nonpop+applied_amt_nonpop_live-vo_rej_amt_nonpop-vo_repaid_nonpop)   vo_applied_nonpop:$vo_applied_nonpop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
//$vo_applied_nonpop_ig=$applied_amt_nonpop_ig+$applied_amt_live_nonpop_ig-$vo_rej_amt_nonpop_ig-$vo_repaid_nonpop_ig; 
//$vo_applied_nonpop_nonig=$applied_amt_nonpop_nonig+$applied_amt_live_nonpop_nonig-$vo_rej_amt_nonpop_nonig-$vo_repaid_nonpop_nonig; 

if($vo_applied_nonpop < 0){
	$vo_applied_nonpop=0;
}		
	 

$vo_credit_pop=$vo_credit_pop-$vo_applied_pop;

	$message="POP Drawing Power (vo_credit_pop-vo_applied_pop)  vo_credit_pop:$vo_credit_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	
$vo_credit_npop=$vo_credit_non_pop-$vo_applied_nonpop;

	$message="NONPOP Drawing Power (vo_credit_non_pop-vo_applied_nonpop)  vo_credit_npop:$vo_credit_npop";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);


if($vo_credit_pop <= 0 && $vo_credit_npop > 0){
		
		$vo_credit_npop=$vo_credit_npop-(-($vo_credit_pop));
		
	$message="vo_credit_pop <= 0 && vo_credit_npop > 0 (vo_credit_npop=vo_credit_npop-(-(vo_credit_pop)))  vo_credit_npop:$vo_credit_npop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);		
		
	}
	if($vo_credit_npop <= 0 && $vo_credit_pop > 0){
		
		$vo_credit_pop=$vo_credit_pop-(-($vo_credit_npop));
		
	$message="vo_credit_npop <= 0 && vo_credit_pop > 0 (vo_credit_pop=vo_credit_pop-(-(vo_credit_npop)))   vo_credit_pop:$vo_credit_pop ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);			
		
	}	


$current_limit_pop_ig=intval(ceil($vo_credit_pop*0.7));

$message="POP IG Drawing Power (vo_credit_pop*0.7)    current_limit_pop_ig:$current_limit_pop_ig ";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

$current_limit_pop_non_ig=intval(floor($vo_credit_pop*0.3));

$message="POP NON IG Drawing Power (vo_credit_pop*0.3)    current_limit_pop_non_ig:$current_limit_pop_non_ig ";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

$current_limit_nonpop_ig=intval(ceil($vo_credit_npop*0.7));

$message="NONPOP IG Drawing Power (vo_credit_npop*0.7)    current_limit_nonpop_ig:$current_limit_nonpop_ig ";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

$current_limit_nonpop_non_ig=intval(floor($vo_credit_npop*0.3));

$message="NONPOP NON IG Drawing Power (vo_credit_npop*0.3)    current_limit_nonpop_non_ig:$current_limit_nonpop_non_ig ";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);





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
		//$agi-> stream_file($wfile, '#');
		play_amount($vo_credit_pop,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	     //$agi-> stream_file($wfile, '#');
		 
		 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_credit_ig";
		//$agi-> stream_file($wfile, '#');
		play_amount($current_limit_pop_ig,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
		//$agi-> stream_file($wfile, '#');
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_credit_non_ig";
		//$agi-> stream_file($wfile, '#');
		play_amount($current_limit_pop_non_ig,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
		 //$agi-> stream_file($wfile, '#');
		 
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/your_nonpop_credit_limit";
		//$agi-> stream_file($wfile, '#');
		play_amount($vo_credit_npop,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
	     //$agi-> stream_file($wfile, '#');
		 
		  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_credit_ig";
		//$agi-> stream_file($wfile, '#');
		play_amount($current_limit_nonpop_ig,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
		//$agi-> stream_file($wfile, '#');
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_credit_non_ig";
		//$agi-> stream_file($wfile, '#');
		play_amount($current_limit_nonpop_non_ig,$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
		 //$agi-> stream_file($wfile, '#');		
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
		//$agi-> stream_file($wfile, '#');
		
		if($meeting_date != ""){
		
			
		if($today_full < $full_meeting_date)	{
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$full_meeting_day_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($full_meeting_day_day,0,1) == "0"){
			$full_meeting_day_day=substr($full_meeting_day_day,1,1);
		}
		
		// play_amount($full_meeting_day_day,$agi);
		sleep(1);
		}
		$meeting_dates = '';
		if($today_full < $meeting_day_1_full){
		$meeting_dates .= "$meeting_day_1_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_1_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_1_day,0,1) == "0"){
			$meeting_day_1_day=substr($meeting_day_1_day,1,1);
		}
		// play_amount($meeting_day_1_day,$agi);
		sleep(1);
		}

		if($today_full < $meeting_day_7_full){
		$meeting_dates .= "$meeting_day_7_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_7_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_7_day,0,1) == "0"){
			$meeting_day_7_day=substr($meeting_day_7_day,1,1);
		}
		
		// play_amount($meeting_day_7_day,$agi);
		sleep(1);
		}
		
		
		if($today_full < $meeting_day_last_month_full)	{
		$meeting_dates .= "$meeting_day_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($full_meeting_day_day,0,1) == "0"){
			$meeting_day_last_month_day=substr($meeting_day_last_month_day,1,1);
		}
		
		// play_amount($meeting_day_last_month_day,$agi);
		sleep(1);
		}
		
		
		if($today_full < $meeting_day_1_last_month_full)	{
		$meeting_dates .= "$meeting_day_1_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_1_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_1_last_month_day,0,1) == "0"){
			$meeting_day_1_last_month_day=substr($meeting_day_1_last_month_day,1,1);
		}
		
		// play_amount($meeting_day_1_last_month_day,$agi);
		sleep(1);
		}
		
	if($today_full < $meeting_day_7_last_month_full)	{
		$meeting_dates .= "$meeting_day_7_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_7_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_7_last_month_day,0,1) == "0"){
			$meeting_day_7_last_month_day=substr($meeting_day_7_last_month_day,1,1);
		}
		
		// play_amount($meeting_day_7_last_month_day,$agi);
		sleep(1);
		}
		}

		$new_meeting_dates = "";
		if($new_meeting_date != ""){
			
		sleep(1);	
		
		if($today_full < $full_new_meeting_date){
		$new_meeting_dates .= "$full_new_meeting_date,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$full_new_meeting_day_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($full_new_meeting_day_day,0,1) == "0"){
			$full_new_meeting_day_day=substr($full_new_meeting_day_day,1,1);
		}
		
		// play_amount($full_new_meeting_day_day,$agi);
		sleep(1);
		}
		
		if($today_full < $new_meeting_day_1_full){
		$new_meeting_dates .= "$new_meeting_day_1_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_1_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_1_day,0,1) == "0"){
			$new_meeting_day_1_day=substr($new_meeting_day_1_day,1,1);
		}
		
		// play_amount($new_meeting_day_1_day,$agi);
		sleep(1);
		}
		
		if($today_full < $new_meeting_day_7_full){
		$new_meeting_dates .= "$new_meeting_day_7_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_7_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_7_day,0,1) == "0"){
			$new_meeting_day_7_day=substr($new_meeting_day_7_day,1,1);
		}
		
		// play_amount($new_meeting_day_7_day,$agi);
		
		}
		
				//new meeting last month
		if($today_full < $new_meeting_day_last_month_full)	{
		$new_meeting_dates .= "$new_meeting_day_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_last_month_day,0,1) == "0"){
			$new_meeting_day_last_month_day=substr($new_meeting_day_last_month_day,1,1);
		}
		
		// play_amount($new_meeting_day_last_month_day,$agi);
		sleep(1);
		}
		
		
		if($today_full < $new_meeting_day_1_last_month_full)	{
		$new_meeting_dates .= "$new_meeting_day_1_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_1_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_1_last_month_day,0,1) == "0"){
			$new_meeting_day_1_last_month_day=substr($new_meeting_day_1_last_month_day,1,1);
		}
		
		// play_amount($new_meeting_day_1_last_month_day,$agi);
		sleep(1);
		}
		
	if($today_full < $new_meeting_day_7_last_month_full)	{
		$new_meeting_dates .= "$new_meeting_day_7_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_7_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_7_last_month_day,0,1) == "0"){
			$new_meeting_day_7_last_month_day=substr($new_meeting_day_7_last_month_day,1,1);
		}
		
		// play_amount($new_meeting_day_7_last_month_day,$agi);
		sleep(1);
		}
		
		
		}
		
		
		exit("Validation: Loan Can be applied on below Meeting Dates : $meeting_dates $new_meeting_dates");
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_only_on_meeting_day";
		//$agi-> stream_file($wfile, '#');
		}
	}		
	return $allow_loan;
	}


function log_ivr($ivr_call_id,$messgae){
	$shg_code= trim($_REQUEST[shg_id]);
        $fp = fopen("Logs/$shg_code.txt","a");
        fwrite($fp,"\n message: $messgae");
        fclose($fp);
	$ivr_call_id='test';
	$test_vonumber=$GLOBALS['test_vonumber'];
	$ivr_log_loan_cmd="/bin/echo ".$messgae." >> /var/log/ivrs/".$ivr_call_id.".log";
	exec($ivr_log_loan_cmd);
		
		
	}

?>

