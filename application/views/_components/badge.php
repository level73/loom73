<?php if(!$set_options): ?>
<span class="badge default">
<?php echo $set_label; ?>
</span>
<?php else:
    $key = array_key_first($set_options);
    $value = $set_options[$key];
?>
<span class="badge badge-<?php echo $key; ?> <?php echo $key . '-' . $value; ?>">
    <?php echo $set_label; ?>
</span>
<?php endif; ?>
