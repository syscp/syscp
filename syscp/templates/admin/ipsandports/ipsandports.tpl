$header
	<form action="$filename?s=$s&amp;page=$page" method="post">
		<input type="hidden" name="token" value="{$userinfo['formtoken']}" />
		<table cellpadding="5" cellspacing="0" border="0" align="center" class="maintable">
			<tr>
				<td class="maintitle_search_left" colspan="4"><b><img src="images/title.gif" alt="" />&nbsp;{$lng['admin']['ipsandports']['ipsandports']}</b></td>
				<td class="maintitle_search_right" colspan="4">{$searchcode}</td>
			</tr>
			<tr>
				<td class="field_display_border_left">{$lng['admin']['ipsandports']['ip']}&nbsp;&nbsp;{$arrowcode['ip']}&nbsp;:&nbsp;{$lng['admin']['ipsandports']['port']}&nbsp;&nbsp;{$arrowcode['port']}</td>
				<td class="field_display">Listen</td>
				<td class="field_display">NameVirtualHost</td>
				<td class="field_display">vHost-Container</td>
				<td class="field_display">Specialsettings</td>
				<td class="field_display">ServerName</td>
				<td class="field_display_search" colspan="2">{$sortcode}</td>
			</tr>
			$ipsandports
			<if $pagingcode != ''>
			<tr>
				<td class="field_display_border_left" colspan="8" style=" text-align: center; ">{$pagingcode}</td>
			</tr>
			</if>
			<tr>
				<td class="field_display_border_left" colspan="8"><a href="$filename?page=$page&amp;action=add&amp;s=$s">{$lng['admin']['ipsandports']['add']}</a></td>
			</tr>
		</table>
	</form>
	<br />
	<br />
$footer