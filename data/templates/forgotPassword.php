<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<meta http–equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http–equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0 " />
<style type="text/css">
body { 
    font-family: Arial, Tahoma; 
    font-size: 14px; 
}
th, td {
  	padding: 6px;
}
</style>
</head>
<body>
<table align="center" width="60%" style="border-collapse: collapse;border: 1px solid #e8e8e8;">
	<tr>
		<td style="padding:40px;">
			<h2 style="color: <?php echo $themeColor ?>;">Demo App</h2>
			<hr color="<?php echo $themeColor ?>" size="2" style="border-top: 2px solid <?php echo $themeColor ?>;" />
			<table border="0" width="100%">
				<tr>
					<td style="padding:20px;">
						<table border="0" width="100%" style="border-spacing: 0;border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
							<tr>
								<td colspan="2" align="center" style="border: none;">
									<h2 style="color: <?php echo $themeColor ?>;"><?php echo $translator->translate('Forgotten Password', 'templates')?></h2>
								</td>
							</tr>
						</table>
						<p style="line-height: 1.8; mso-line-height-rule: exactly;line-height:130%;font-size:14px;"><?php 
						$header = $translator->translate('Hello, we received a password reset request from you. You can recreate your password by clicking the following link', 'templates');
						echo $header ?>.</p>
						<br />
						<span style="font-size:14px;"><a href="<?php echo $resetPasswordLink ?>" style="color: <?php echo $themeColor ?>;text-decoration: underline;"><?php echo $resetPasswordLink ?></a><span>
					</td>
				</tr>
			</table>
			<hr color="<?php echo $themeColor ?>" size="2" style="border-top: 2px solid <?php echo $themeColor ?>;" />
			<br />
			<p style="text-align: center;font-size:12px;color:gray;line-height: 1.4"><?php
			$footer = $translator->translate('This e-mail was sent to {EMAIL} following your membership', 'templates').'. ';
			$footer.= $translator->translate('Please do not reply to this e-mail', 'templates').'. ';
			echo str_replace('{EMAIL}', '<a href="mailto:'.$email.'" style="color:'.$themeColor.';text-decoration:underline;">'.$email.'</a>', $footer); ?></p>
		</td>
	</tr>
</table>
</body>
</html>