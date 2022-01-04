<?php defined("_BOARD_VALID_") or die("Direct Access to this location is not allowed."); ?>
<div id="information"><?php echo isset($information) ? $information : "";?></div>
<form action="" method="post" >
	<br />
	<br />
	<table width="100%" id="param">
		<?php
			$tr = 1;
			$query = $db->query("SELECT * FROM ".TABLES_PREFIX."_settings ORDER BY settings_ordering");
			while($row = $db->fetch($query)) {
				$tr = 1 - $tr;
				?>
					<tr class='row_<?php echo $tr;?>'>
						<td><span class="tooltip"><?php echo __($row['settings_title']); ?><span><?php echo __($row['settings_description']); ?></span></span></td>
						<td>
							<?php if($row['settings_type'] == "text"): ?>
								<input type="text" name="<?php echo $row['settings_name']; ?>" value="<?php echo $row['settings_value']; ?>" />
							<?php elseif($row['settings_type'] == "textarea"): ?>
								<textarea name="<?php echo $row['settings_name']; ?>" ><?php echo $row['settings_value']; ?></textarea>
							<?php
								elseif($row['settings_type'] == "radio"):
								$settings_attributes = unserialize($row['settings_attributes']);
								foreach($settings_attributes as $key => $attribute):
							?>
								<input type="radio" name="<?php echo $row['settings_name']; ?>" value="<?php echo $key; ?>" <?php echo $key == $row['settings_value'] ? "checked='checked'" : "";?> /> <?php echo __($attribute); ?>
								<?php endforeach; ?>
							<?php elseif($row['settings_type'] == "select"):
								$settings_attributes = unserialize($row['settings_attributes']);
							?>
								<select name="<?php echo $row['settings_name']; ?>">
									<?php foreach($settings_attributes as $key => $attribute): ?>
										<option value="<?php echo $key; ?>" <?php echo $key == $row['settings_value'] ? "selected='selected'" : "";?> > <?php echo __($attribute); ?></option>
									<?php endforeach; ?>
								</select>
							<?php endif;?>
						</td>
					</tr>
				<?php
			}
		?>
		<tr>
			<td></td>
			<td><input type="submit" name="save_parameters" value="<?php echo __("Save"); ?>" /></td>
			<td></td>
		</tr>
	</table>
</form>
