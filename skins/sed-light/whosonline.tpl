<!-- BEGIN: MAIN -->
<div class="mboxHD">{WHOSONlINE_TITLE}</div>
<div class="mboxBody">
	{PHP.L.plu_mostonline} {WHOSONlINE_MAXUSERS}.<br />
	{PHP.L.plu_therescurrently} {WHOSONlINE_VISITORS} {PHP.L.plu_visitors} {WHOSONlINE_MEMBERS} {PHP.L.plu_members}<br />&nbsp;<br />
	<table class="cells">
	<tr>
		<td class="coltop">{WHOSONlINE_USER_AVATAR}</td>
		<td class="coltop">{PHP.L.User}</td>
		<td class="coltop">{PHP.L.Group}</td>
		<td class="coltop">{PHP.L.Country}</td>
		<td class="coltop">{PHP.L.plu_lastseen1}</td>
<!-- BEGIN: IS_ADMIN -->
		<td class="coltop">{WHOSONlINE_IN}</td>
		<td class="coltop">{WHOSONlINE_IP}</td>
<!-- END: IS_ADMIN -->
	</tr>
<!-- BEGIN: WHOSONlINE_ROW1 -->
	<tr>
		<td>{WHOSONlINE_ROW1_SHOWAVATARS}</td>
		<td>{WHOSONlINE_ROW1_USER}</td>
		<td><a href="{WHOSONlINE_ROW1_USER_MAINGRP_URL}">{WHOSONlINE_ROW1_USER_MAINGRP_TITLE}</a></td>
		<td style="text-align:center;">{WHOSONlINE_ROW1_USER_COUNTRY}</td>
		<td>{WHOSONlINE_ROW1_USER_ONLINE_LASTSEEN}</td>
<!-- BEGIN: WHOSONlINE_ROW1_IS_ADMIN -->
		<td>{WHOSONlINE_ROW1_USER_ONLINE_LOCATION}</td>
		<td style="text-align:center;">{WHOSONlINE_ROW1_USER_ONLINE_IP}</td>
<!-- END: WHOSONlINE_ROW1_IS_ADMIN -->
       </tr>
<!-- END: WHOSONlINE_ROW1 -->
<!-- BEGIN: WHOSONlINE_ROW2 -->
	<tr>
		<td>{WHOSONlINE_ROW2_SHOWAVATARS}</td>
		<td colspan="3">{WHOSONlINE_ROW2_USER}</td>
		<td>{WHOSONlINE_ROW2_USER_ONLINE_LASTSEEN}</td>
<!-- BEGIN: WHOSONlINE_ROW2_IS_ADMIN -->
		<td>{WHOSONlINE_ROW2_USER_ONLINE_LOCATION}</td>
		<td style="text-align:center;">{WHOSONlINE_ROW2_USER_ONLINE_IP}</td>
<!-- END: WHOSONlINE_ROW2_IS_ADMIN -->
       </tr>
<!-- END: WHOSONlINE_ROW2 -->
	</table>
</div>
<!-- END: MAIN -->