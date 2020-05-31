 <fieldset>
   <input type="hidden" name="serendipity[pikkuveli_noid]" value="<?php echo $hasid;?>"/>
 	<legend><?php echo PLUGIN_PIKKUVELI_TITLE;?></legend>
	<input type="hidden" name="serendipity[pikkuveli_start]" 
		value="<?php echo $ts;?>"/>
	<?php if($hasid=="yup") {?>
		<label><?php echo PLUGIN_PIKKUVELI_BEGIN;?><input type="text" 
			name="serendipity[pikkuveli_begin]" value="" size="20" maxlength="20"/>
		</label>
		<label><?php echo PLUGIN_PIKKUVELI_END;?><input type="text" 
			name="serendipity[pikkuveli_end]" value="" size="20" maxlength="20"/></label>
		<br/><br/>
		<label><?php echo PLUGIN_PIKKUVELI_COMMENT;?><input type="text" 
			name="serendipity[pikkuveli_comment]" value="" size="60" 
			maxlength="255"/></label><br/><br/>
		<?php
		if(!is_null($result['rivit'])) {
		?>
			<label style="display: block;margin:0;float:left;">
			<?php echo PLUGIN_PIKKUVELI_DELETE;?></label><select name="serendipity[pikkuveli_oldones]"  
				size="10"><?php foreach($result['rivit'] as $avain=>$rivi) {
					?>
					<option label="<?php echo $rivi;?>" value="<?php echo 
						$rivi;?>"><?php 
						echo $rivi;?>
				<?php }?>
				</select><br/>
				<?php echo PLUGIN_PIKKUVELI_TOTAL;?><input type="text" 
						name="yhteensa" readonly="readonly" 
						value="<?php echo $this->ToHours($result['summa']);?>"/>
			<?php }	?>
		<?php } ?>
		<label><?php echo PLUGIN_PIKKUVELI_DONT_STAMP;?><input type="checkbox" name="serendipity[pikkuveli_stampit]"/></label>
		<?php if($this->pikkuveliMessages!="") {?>
			<textarea disabled="disabled" cols="60" rows="3"><?php echo $this->pikkuveliMessages;?></textarea>
			<?php }?>
	</fieldset>
		