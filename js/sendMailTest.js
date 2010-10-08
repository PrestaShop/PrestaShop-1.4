//constant
verifMailREGEX = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/;

function verifyMail(testMsg, testSubject)
{
	$("#mailResultCheck").removeClass("ok").removeClass('fail').html('<img src="../img/admin/ajax-loader.gif" alt="" />');
	$("#mailResultCheck").slideDown("slow");

	//local verifications
	if ($("#testEmail[value=]").length > 0)
	{
		$("#mailResultCheck").addClass("fail").removeClass("ok").removeClass('userInfos').html(errorMail);
		return false;
	}
	else if (!verifMailREGEX.test( $("#testEmail").val() ))
	{ 
		$("#mailResultCheck").addClass("fail").removeClass("ok").removeClass('userInfos').html(errorMail);
		return false;
	}
	else
	{
		//external verifications and sets
		$.ajax(
		{
		   url: "ajax_send_mail_test.php",
		   cache: false,
		   data:
				"mailMethod="+(($("input#PS_MAIL_METHOD").val() == 2) ? "smtp" : "native")+
				"&smtpSrv="+ $("input#PS_MAIL_SERVER").val()+
				"&testEmail="+ $("#testEmail").val()+
		   		"&smtpLogin="+ $("input#PS_MAIL_USER").val()+
		   		"&smtpPassword="+ $("input#PS_MAIL_PASSWD").val()+
				"&smtpPort="+ $("input#PS_MAIL_SMTP_PORT").val()+
				"&smtpEnc="+ $("select#PS_MAIL_SMTP_ENCRYPTION").val()+
				"&testMsg="+textMsg+
				"&testSubject="+textSubject
			,
		   success: function(ret)
		   {
				if (ret == "ok")
				{
					$("#mailResultCheck").addClass("ok").removeClass("fail").removeClass('userInfos').html(textSendOk);
					mailIsOk = true;
				}
				else
				{
					mailIsOk = false;
					$("#mailResultCheck").addClass("fail").removeClass("ok").removeClass('userInfos').html(textSendError);
				}
		   }
		 }
		 );
	}
}