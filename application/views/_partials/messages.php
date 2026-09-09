<?php foreach (\Loom73\Woodframe\Flash::all() as $flash): ?>
    <div class="flash flash--<?php echo htmlspecialchars($flash['type']) ?>" data-dismissable role="<?php if($flash['type'] == 'danger' || $flash['type'] == 'warning'): echo 'alert'; else: echo 'status'; endif; ?>">
        <?php flashIcon($flash['type']); ?>
        <?php echo htmlspecialchars($flash['message']['message']) ?>
        <button class="dismiss" role="button" type="button" data-dismiss><span class="sr-only">Dismiss message</span><i class="stitch stitch--times" aria-hidden="true"></i></button>

        <?php if($_SERVER['DEBUG'] && !empty($flash['message']['error'])): ?>
            <?php prettyPrint($flash['message']['error']); ?>
            <?php prettyPrint($flash['message']['data']); ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
