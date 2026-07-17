<?php foreach (\Loom73\Woodframe\Flash::all() as $flash): ?>
    <div class="flash flash--<?php echo htmlspecialchars($flash['type']) ?>" data-dismissable>
        <?php flashIcon($flash['type']); ?>
        <?php echo htmlspecialchars($flash['message']['message']) ?>
        <button class="dismiss" role="button" data-dismiss><span class="sr-only">Dismiss message</span><i class="icon times"></i></button>

        <?php if($_SERVER['DEBUG'] && !empty($flash['message']['error'])): ?>
            <?php prettyPrint($flash['message']['error']); ?>
            <?php prettyPrint($flash['message']['data']); ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
